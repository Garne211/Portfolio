<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/csrf.php';

requireAuth();

$db = getDBConnection();
$success = '';
$error = '';
$action = $_GET['action'] ?? 'list';
$editId = $_GET['id'] ?? null;

// DELETE
if ($action === 'delete' && $editId) {
    requireEditAccess(); // Vérifier les permissions
    
    if (!validateCSRFToken($_GET['csrf_token'] ?? '')) {
        $error = 'Token de sécurité invalide';
    } else {
        try {
            $stmt = $db->prepare("SELECT image_path FROM projects WHERE id = ?");
            $stmt->execute([$editId]);
            $project = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($project && $project['image_path']) {
                deleteUploadedFile(basename($project['image_path']));
            }
            
            $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
            $stmt->execute([$editId]);
            
            $success = 'Projet supprimé avec succès';
            $action = 'list';
        } catch (Exception $e) {
            $error = 'Erreur lors de la suppression : ' . $e->getMessage();
        }
    }
}

// TOGGLE VISIBLE
if ($action === 'toggle' && $editId) {
    try {
        $stmt = $db->prepare("UPDATE projects SET visible = NOT visible WHERE id = ?");
        $stmt->execute([$editId]);
        $success = 'Visibilité modifiée avec succès';
        $action = 'list';
    } catch (Exception $e) {
        $error = 'Erreur : ' . $e->getMessage();
    }
}

// FORM SUBMIT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['add', 'edit'])) {
    requireEditAccess(); // Vérifier les permissions
    
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de sécurité invalide';
    } else {
        $data = [
            'slug' => generateSlug($_POST['title_fr'] ?? ''),
            'title_fr' => sanitizeInput($_POST['title_fr'] ?? ''),
            'title_en' => sanitizeInput($_POST['title_en'] ?? ''),
            'description_fr' => sanitizeInput($_POST['description_fr'] ?? ''),
            'description_en' => sanitizeInput($_POST['description_en'] ?? ''),
            'github_url' => sanitizeInput($_POST['github_url'] ?? ''),
            'demo_url' => sanitizeInput($_POST['demo_url'] ?? ''),
            'display_order' => (int)($_POST['display_order'] ?? 0),
            'visible' => isset($_POST['visible']) ? 1 : 0,
        ];
        
        // Technologies (tags multiples)
        $technologies = [];
        if (!empty($_POST['technologies'])) {
            $technologies = array_map('trim', explode(',', $_POST['technologies']));
            $technologies = array_filter($technologies);
        }
        $data['technologies'] = json_encode($technologies, JSON_UNESCAPED_UNICODE);
        
        // Validation
        if (empty($data['title_fr']) || empty($data['title_en'])) {
            $error = 'Les titres sont requis';
        } elseif (!empty($data['github_url']) && !validateUrl($data['github_url'])) {
            $error = 'URL GitHub invalide';
        } elseif (!empty($data['demo_url']) && !validateUrl($data['demo_url'])) {
            $error = 'URL démo invalide';
        } else {
            // Upload image
            $imagePath = null;
            if (!empty($_FILES['image']['name'])) {
                $upload = uploadFile(
                    $_FILES['image'],
                    ['image/png', 'image/jpeg', 'image/webp', 'image/gif'],
                    5242880 // 5MB
                );
                
                if ($upload['success']) {
                    $imagePath = $upload['url'];
                    
                    if ($action === 'edit' && $editId) {
                        $stmt = $db->prepare("SELECT image_path FROM projects WHERE id = ?");
                        $stmt->execute([$editId]);
                        $oldProject = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($oldProject && $oldProject['image_path']) {
                            deleteUploadedFile(basename($oldProject['image_path']));
                        }
                    }
                } else {
                    $error = $upload['error'];
                }
            }
            
            if (!$error) {
                try {
                    if ($action === 'add') {
                        $stmt = $db->prepare("
                            INSERT INTO projects (slug, title_fr, title_en, description_fr, description_en, 
                                                 technologies, github_url, demo_url, image_path, display_order, visible)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $data['slug'],
                            $data['title_fr'],
                            $data['title_en'],
                            $data['description_fr'],
                            $data['description_en'],
                            $data['technologies'],
                            $data['github_url'],
                            $data['demo_url'],
                            $imagePath,
                            $data['display_order'],
                            $data['visible']
                        ]);
                        $success = 'Projet ajouté avec succès';
                    } else {
                        $sql = "
                            UPDATE projects SET
                                slug = ?,
                                title_fr = ?,
                                title_en = ?,
                                description_fr = ?,
                                description_en = ?,
                                technologies = ?,
                                github_url = ?,
                                demo_url = ?,
                                display_order = ?,
                                visible = ?,
                                updated_at = NOW()
                        ";
                        $params = [
                            $data['slug'],
                            $data['title_fr'],
                            $data['title_en'],
                            $data['description_fr'],
                            $data['description_en'],
                            $data['technologies'],
                            $data['github_url'],
                            $data['demo_url'],
                            $data['display_order'],
                            $data['visible']
                        ];
                        
                        if ($imagePath) {
                            $sql .= ", image_path = ?";
                            $params[] = $imagePath;
                        }
                        
                        $sql .= " WHERE id = ?";
                        $params[] = $editId;
                        
                        $stmt = $db->prepare($sql);
                        $stmt->execute($params);
                        $success = 'Projet modifié avec succès';
                    }
                    
                    $action = 'list';
                    $editId = null;
                } catch (Exception $e) {
                    $error = 'Erreur : ' . $e->getMessage();
                }
            }
        }
    }
}

// Edit data
$editData = null;
if ($action === 'edit' && $editId) {
    $stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$editId]);
    $editData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$editData) {
        $error = 'Projet non trouvé';
        $action = 'list';
    } else {
        // Decoder technologies
        if ($editData['technologies']) {
            $techs = json_decode($editData['technologies'], true);
            $editData['technologies_string'] = implode(', ', $techs);
        }
    }
}

// Liste
$projects = [];
if ($action === 'list') {
    $projects = $db->query("SELECT * FROM projects ORDER BY display_order ASC, created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
}

$pageTitle = $action === 'add' ? 'Ajouter un projet' : ($action === 'edit' ? 'Modifier un projet' : 'Gérer les projets');
include 'includes/header.php';
?>

<?php if ($success): ?>
    <div class="alert alert--success">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
        <span><?php echo escape($success); ?></span>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert--error">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <span><?php echo escape($error); ?></span>
    </div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
    <div class="page-actions">
        <a href="?action=add" class="btn btn--primary">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Ajouter un projet</span>
        </a>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2>Liste des projets (<?php echo count($projects); ?>)</h2>
        </div>
        
        <?php if (empty($projects)): ?>
            <div class="card-body text-center">
                <p class="text-muted">Aucun projet pour le moment.</p>
                <a href="?action=add" class="btn btn--outline btn--sm">Ajouter le premier projet</a>
            </div>
        <?php else: ?>
            <div class="projects-grid">
                <?php foreach ($projects as $project): ?>
                    <div class="project-item <?php echo $project['visible'] ? '' : 'project-item--hidden'; ?>">
                        <?php if ($project['image_path']): ?>
                            <img src="<?php echo escape($project['image_path']); ?>" alt="<?php echo escape($project['title_fr']); ?>" class="project-image">
                        <?php else: ?>
                            <div class="project-image project-image--placeholder">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21 15 16 10 5 21"></polyline>
                                </svg>
                            </div>
                        <?php endif; ?>
                        
                        <div class="project-content">
                            <h3><?php echo escape($project['title_fr']); ?></h3>
                            <p class="project-description"><?php echo truncate($project['description_fr'], 100); ?></p>
                            
                            <?php if ($project['technologies']): ?>
                                <?php
                                $techs = json_decode($project['technologies'], true);
                                if ($techs):
                                ?>
                                    <div class="project-techs">
                                        <?php foreach (array_slice($techs, 0, 3) as $tech): ?>
                                            <span class="tech-tag"><?php echo escape($tech); ?></span>
                                        <?php endforeach; ?>
                                        <?php if (count($techs) > 3): ?>
                                            <span class="tech-tag">+<?php echo count($techs) - 3; ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="project-actions">
                            <?php if (!$project['visible']): ?>
                                <span class="badge badge--muted">Masqué</span>
                            <?php endif; ?>
                            
                            <a href="?action=toggle&id=<?php echo $project['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>" 
                               class="btn-icon" 
                               title="<?php echo $project['visible'] ? 'Masquer' : 'Afficher'; ?>">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </a>
                            
                            <a href="?action=edit&id=<?php echo $project['id']; ?>" class="btn-icon" title="Modifier">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </a>
                            
                            <a href="?action=delete&id=<?php echo $project['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>" 
                               class="btn-icon btn-icon--danger" 
                               title="Supprimer"
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce projet ?')">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

<?php else: ?>
    <!-- FORMULAIRE -->
    <div class="card">
        <div class="card-header">
            <h2><?php echo $action === 'add' ? 'Ajouter' : 'Modifier'; ?> un projet</h2>
        </div>
        
        <form method="POST" enctype="multipart/form-data" class="card-body">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="title_fr" class="required">Titre (Français)</label>
                    <input 
                        type="text" 
                        id="title_fr" 
                        name="title_fr" 
                        class="form-control"
                        value="<?php echo escape($editData['title_fr'] ?? ''); ?>"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="title_en" class="required">Titre (Anglais)</label>
                    <input 
                        type="text" 
                        id="title_en" 
                        name="title_en" 
                        class="form-control"
                        value="<?php echo escape($editData['title_en'] ?? ''); ?>"
                        required
                    >
                </div>
            </div>
            
            <div class="form-group">
                <label for="description_fr">Description (Français)</label>
                <textarea 
                    id="description_fr" 
                    name="description_fr" 
                    class="form-control"
                    rows="4"
                ><?php echo escape($editData['description_fr'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="description_en">Description (Anglais)</label>
                <textarea 
                    id="description_en" 
                    name="description_en" 
                    class="form-control"
                    rows="4"
                ><?php echo escape($editData['description_en'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="technologies">Technologies utilisées</label>
                <input 
                    type="text" 
                    id="technologies" 
                    name="technologies" 
                    class="form-control"
                    value="<?php echo escape($editData['technologies_string'] ?? ''); ?>"
                    placeholder="HTML, CSS, PHP, JavaScript..."
                >
                <small class="form-help">Séparez les technologies par des virgules</small>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="github_url">Lien GitHub</label>
                    <input 
                        type="url" 
                        id="github_url" 
                        name="github_url" 
                        class="form-control"
                        value="<?php echo escape($editData['github_url'] ?? ''); ?>"
                        placeholder="https://github.com/..."
                    >
                </div>
                
                <div class="form-group">
                    <label for="demo_url">Lien démo</label>
                    <input 
                        type="url" 
                        id="demo_url" 
                        name="demo_url" 
                        class="form-control"
                        value="<?php echo escape($editData['demo_url'] ?? ''); ?>"
                        placeholder="https://..."
                    >
                </div>
            </div>
            
            <div class="form-group">
                <label for="image">Image du projet (JPG, PNG, WebP, max 5MB)</label>
                <?php if ($editData && $editData['image_path']): ?>
                    <div class="current-image">
                        <img src="<?php echo escape($editData['image_path']); ?>" alt="Image actuelle">
                        <p class="text-muted text-sm">Image actuelle</p>
                    </div>
                <?php endif; ?>
                <input 
                    type="file" 
                    id="image" 
                    name="image" 
                    class="form-control"
                    accept="image/png,image/jpeg,image/webp,image/gif"
                >
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="display_order">Ordre d'affichage</label>
                    <input 
                        type="number" 
                        id="display_order" 
                        name="display_order" 
                        class="form-control"
                        value="<?php echo escape($editData['display_order'] ?? 0); ?>"
                        min="0"
                    >
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input 
                            type="checkbox" 
                            name="visible" 
                            value="1"
                            <?php echo ($editData['visible'] ?? 1) ? 'checked' : ''; ?>
                        >
                        <span>Visible sur le site</span>
                    </label>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn--primary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span><?php echo $action === 'add' ? 'Ajouter' : 'Mettre à jour'; ?></span>
                </button>
                
                <a href="?action=list" class="btn btn--outline">Annuler</a>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>