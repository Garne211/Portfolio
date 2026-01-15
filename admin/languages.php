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

// Traitement DELETE
if ($action === 'delete' && $editId) {
    requireEditAccess(); // Vérifier les permissions
    
    if (!validateCSRFToken($_GET['csrf_token'] ?? '')) {
        $error = 'Token de sécurité invalide';
    } else {
        try {
            // Récupérer l'icône pour la supprimer
            $stmt = $db->prepare("SELECT icon_path FROM languages WHERE id = ?");
            $stmt->execute([$editId]);
            $lang = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($lang && $lang['icon_path']) {
                deleteUploadedFile(basename($lang['icon_path']));
            }
            
            $stmt = $db->prepare("DELETE FROM languages WHERE id = ?");
            $stmt->execute([$editId]);
            
            $success = 'Langage supprimé avec succès';
            $action = 'list';
        } catch (Exception $e) {
            $error = 'Erreur lors de la suppression : ' . $e->getMessage();
        }
    }
}

// Traitement TOGGLE ACTIVE
if ($action === 'toggle' && $editId) {
    try {
        $stmt = $db->prepare("UPDATE languages SET active = NOT active WHERE id = ?");
        $stmt->execute([$editId]);
        $success = 'Statut modifié avec succès';
        $action = 'list';
    } catch (Exception $e) {
        $error = 'Erreur : ' . $e->getMessage();
    }
}

// Traitement FORM (ADD/EDIT)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['add', 'edit'])) {
    requireEditAccess(); // Vérifier les permissions
    
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de sécurité invalide';
    } else {
        $data = [
            'slug' => sanitizeInput($_POST['slug'] ?? ''),
            'name_fr' => sanitizeInput($_POST['name_fr'] ?? ''),
            'name_en' => sanitizeInput($_POST['name_en'] ?? ''),
            'description_fr' => sanitizeInput($_POST['description_fr'] ?? ''),
            'description_en' => sanitizeInput($_POST['description_en'] ?? ''),
            'percentage' => max(0, min(100, (int)($_POST['percentage'] ?? 0))),
            'display_order' => (int)($_POST['display_order'] ?? 0),
            'active' => isset($_POST['active']) ? 1 : 0,
        ];
        
        // Validation
        if (empty($data['slug']) || empty($data['name_fr']) || empty($data['name_en'])) {
            $error = 'Les champs slug et noms sont requis';
        } else {
            // Gestion de l'upload d'icône
            $iconPath = null;
            if (!empty($_FILES['icon']['name'])) {
                $upload = uploadFile(
                    $_FILES['icon'],
                    ['image/png', 'image/svg+xml', 'image/webp', 'image/jpeg'],
                    2097152 // 2MB
                );
                
                if ($upload['success']) {
                    $iconPath = $upload['url'];
                    
                    // Supprimer l'ancienne icône si modification
                    if ($action === 'edit' && $editId) {
                        $stmt = $db->prepare("SELECT icon_path FROM languages WHERE id = ?");
                        $stmt->execute([$editId]);
                        $oldLang = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($oldLang && $oldLang['icon_path']) {
                            deleteUploadedFile(basename($oldLang['icon_path']));
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
                            INSERT INTO languages (slug, name_fr, name_en, description_fr, description_en, percentage, icon_path, active)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $data['slug'],
                            $data['name_fr'],
                            $data['name_en'],
                            $data['description_fr'],
                            $data['description_en'],
                            $data['percentage'],
                            $iconPath,
                            $data['active']
                        ]);
                        $success = 'Langage ajouté avec succès';
                    } else {
                        $sql = "
                            UPDATE languages SET
                                slug = ?,
                                name_fr = ?,
                                name_en = ?,
                                description_fr = ?,
                                description_en = ?,
                                percentage = ?,
                                active = ?
                        ";
                        $params = [
                            $data['slug'],
                            $data['name_fr'],
                            $data['name_en'],
                            $data['description_fr'],
                            $data['description_en'],
                            $data['percentage'],
                            $data['active']
                        ];
                        
                        if ($iconPath) {
                            $sql .= ", icon_path = ?";
                            $params[] = $iconPath;
                        }
                        
                        $sql .= " WHERE id = ?";
                        $params[] = $editId;
                        
                        $stmt = $db->prepare($sql);
                        $stmt->execute($params);
                        $success = 'Langage modifié avec succès';
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

// Récupération des données pour édition
$editData = null;
if ($action === 'edit' && $editId) {
    $stmt = $db->prepare("SELECT * FROM languages WHERE id = ?");
    $stmt->execute([$editId]);
    $editData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$editData) {
        $error = 'Langage non trouvé';
        $action = 'list';
    }
}

// Liste des langages
$languages = [];
if ($action === 'list') {
    $languages = $db->query("SELECT * FROM languages ORDER BY percentage DESC, name_fr ASC")->fetchAll(PDO::FETCH_ASSOC);
}

$pageTitle = $action === 'add' ? 'Ajouter un langage' : ($action === 'edit' ? 'Modifier un langage' : 'Gérer les langages');
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
            <span>Ajouter un langage</span>
        </a>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2>Liste des langages (<?php echo count($languages); ?>)</h2>
        </div>
        
        <?php if (empty($languages)): ?>
            <div class="card-body text-center">
                <p class="text-muted">Aucun langage pour le moment.</p>
                <a href="?action=add" class="btn btn--outline btn--sm">Ajouter le premier langage</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ordre</th>
                            <th>Icône</th>
                            <th>Nom FR</th>
                            <th>Nom EN</th>
                            <th>Maîtrise</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($languages as $lang): ?>
                            <tr>
                                <td>
                                    <?php if ($lang['icon_path']): ?>
                                        <img src="<?php echo escape($lang['icon_path']); ?>" alt="Icon" class="table-icon">
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo escape($lang['name_fr']); ?></strong></td>
                                <td><?php echo escape($lang['name_en']); ?></td>
                                <td>
                                    <div class="progress-inline">
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?php echo $lang['percentage']; ?>%"></div>
                                        </div>
                                        <span><?php echo $lang['percentage']; ?>%</span>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($lang['active']): ?>
                                        <span class="badge badge--success">Actif</span>
                                    <?php else: ?>
                                        <span class="badge badge--muted">Inactif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?action=toggle&id=<?php echo $lang['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>" 
                                           class="btn-icon" 
                                           title="<?php echo $lang['active'] ? 'Désactiver' : 'Activer'; ?>">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </a>
                                        
                                        <a href="?action=edit&id=<?php echo $lang['id']; ?>" class="btn-icon" title="Modifier">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                        </a>
                                        
                                        <a href="?action=delete&id=<?php echo $lang['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>" 
                                           class="btn-icon btn-icon--danger" 
                                           title="Supprimer"
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce langage ?')">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

<?php else: ?>
    <!-- FORMULAIRE ADD/EDIT -->
    <div class="card">
        <div class="card-header">
            <h2><?php echo $action === 'add' ? 'Ajouter' : 'Modifier'; ?> un langage</h2>
        </div>
        
        <form method="POST" enctype="multipart/form-data" class="card-body">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="slug" class="required">Slug (identifiant technique)</label>
                    <input 
                        type="text" 
                        id="slug" 
                        name="slug" 
                        class="form-control"
                        value="<?php echo escape($editData['slug'] ?? ''); ?>"
                        placeholder="html, css, php..."
                        pattern="[a-z0-9-]+"
                        required
                    >
                    <small class="form-help">Lettres minuscules et tirets uniquement</small>
                </div>
                
                <div class="form-group">
                    <label for="percentage" class="required">Maîtrise (%)</label>
                    <input 
                        type="number" 
                        id="percentage" 
                        name="percentage" 
                        class="form-control"
                        value="<?php echo escape($editData['percentage'] ?? 50); ?>"
                        min="0"
                        max="100"
                        required
                    >
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="name_fr" class="required">Nom (Français)</label>
                    <input 
                        type="text" 
                        id="name_fr" 
                        name="name_fr" 
                        class="form-control"
                        value="<?php echo escape($editData['name_fr'] ?? ''); ?>"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="name_en" class="required">Nom (Anglais)</label>
                    <input 
                        type="text" 
                        id="name_en" 
                        name="name_en" 
                        class="form-control"
                        value="<?php echo escape($editData['name_en'] ?? ''); ?>"
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
                    rows="3"
                ><?php echo escape($editData['description_fr'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="description_en">Description (Anglais)</label>
                <textarea 
                    id="description_en" 
                    name="description_en" 
                    class="form-control"
                    rows="3"
                ><?php echo escape($editData['description_en'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="icon">Icône (PNG, SVG, WebP, max 2MB)</label>
                <?php if ($editData && $editData['icon_path']): ?>
                    <div class="current-image">
                        <img src="<?php echo escape($editData['icon_path']); ?>" alt="Icon actuelle">
                        <p class="text-muted text-sm">Icône actuelle</p>
                    </div>
                <?php endif; ?>
                <input 
                    type="file" 
                    id="icon" 
                    name="icon" 
                    class="form-control"
                    accept="image/png,image/svg+xml,image/webp,image/jpeg"
                >
                <small class="form-help">Laissez vide pour conserver l'icône actuelle</small>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="checkbox-label">
                        <input 
                            type="checkbox" 
                            name="active" 
                            value="1"
                            <?php echo ($editData['active'] ?? 1) ? 'checked' : ''; ?>
                        >
                        <span>Actif (visible sur le site)</span>
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