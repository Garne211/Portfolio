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
            $stmt = $db->prepare("DELETE FROM experiences WHERE id = ?");
            $stmt->execute([$editId]);
            $success = 'Expérience supprimée avec succès';
            $action = 'list';
        } catch (Exception $e) {
            $error = 'Erreur lors de la suppression : ' . $e->getMessage();
        }
    }
}

// Traitement TOGGLE ACTIVE
if ($action === 'toggle' && $editId) {
    try {
        $stmt = $db->prepare("UPDATE experiences SET active = NOT active WHERE id = ?");
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
            'title' => sanitizeInput($_POST['title'] ?? ''),
            'company' => sanitizeInput($_POST['company'] ?? ''),
            'description_fr' => sanitizeInput($_POST['description_fr'] ?? ''),
            'description_en' => sanitizeInput($_POST['description_en'] ?? ''),
            'start_date' => $_POST['start_date'] ?? '',
            'end_date' => $_POST['end_date'] ?? '',
            'is_current' => isset($_POST['is_current']) ? 1 : 0,
            'type' => in_array($_POST['type'] ?? 'experience', ['experience', 'stage']) ? $_POST['type'] : 'experience',
            'display_order' => (int)($_POST['display_order'] ?? 0),
            'active' => isset($_POST['active']) ? 1 : 0,
        ];
        
        // Validation
        if (empty($data['title']) || empty($data['company']) || empty($data['start_date'])) {
            $error = 'Les champs titre, entreprise et date de début sont requis';
        } else {
            try {
                if ($action === 'add') {
                    $stmt = $db->prepare("
                        INSERT INTO experiences (title, company, description_fr, description_en, start_date, end_date, is_current, type, display_order, active)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $data['title'],
                        $data['company'],
                        $data['description_fr'],
                        $data['description_en'],
                        $data['start_date'],
                        $data['end_date'] ?: null,
                        $data['is_current'],
                        $data['type'],
                        $data['display_order'],
                        $data['active']
                    ]);
                    $success = 'Expérience ajoutée avec succès';
                } else {
                    $stmt = $db->prepare("
                        UPDATE experiences SET
                            title = ?,
                            company = ?,
                            description_fr = ?,
                            description_en = ?,
                            start_date = ?,
                            end_date = ?,
                            is_current = ?,
                            type = ?,
                            display_order = ?,
                            active = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $data['title'],
                        $data['company'],
                        $data['description_fr'],
                        $data['description_en'],
                        $data['start_date'],
                        $data['end_date'] ?: null,
                        $data['is_current'],
                        $data['type'],
                        $data['display_order'],
                        $data['active'],
                        $editId
                    ]);
                    $success = 'Expérience modifiée avec succès';
                }
                
                $action = 'list';
            } catch (Exception $e) {
                $error = 'Erreur lors de l\'enregistrement : ' . $e->getMessage();
            }
        }
    }
}

// Récupérer les données pour le formulaire
$formData = [];
if ($action === 'edit' && $editId) {
    $stmt = $db->prepare("SELECT * FROM experiences WHERE id = ?");
    $stmt->execute([$editId]);
    $formData = $stmt->fetch(PDO::FETCH_ASSOC) ?? [];
}

// Récupérer la liste
$experiences = [];
if ($action === 'list') {
    $stmt = $db->query("SELECT * FROM experiences ORDER BY display_order ASC, created_at DESC");
    $experiences = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$pageTitle = 'Gérer les expériences et stages';
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
    <div class="dashboard-card">
        <div class="card-header">
            <h2>Expériences et Stages</h2>
            <a href="?action=add" class="btn btn--primary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Ajouter une expérience</span>
            </a>
        </div>

        <?php if (empty($experiences)): ?>
            <div class="card-body">
                <p style="text-align: center; color: var(--color-text-secondary); padding: 40px 20px;">
                    Aucune expérience pour le moment. 
                    <a href="?action=add">Ajouter la première</a>
                </p>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Entreprise</th>
                        <th>Type</th>
                        <th>Période</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($experiences as $exp): ?>
                        <tr>
                            <td>
                                <strong><?php echo escape($exp['title']); ?></strong>
                            </td>
                            <td><?php echo escape($exp['company']); ?></td>
                            <td>
                                <span class="badge <?php echo $exp['type'] === 'stage' ? 'badge--warning' : 'badge--success'; ?>">
                                    <?php echo ucfirst($exp['type']); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo date('m/Y', strtotime($exp['start_date'])); ?>
                                <?php echo $exp['end_date'] ? ' - ' . date('m/Y', strtotime($exp['end_date'])) : ' - Actuellement'; ?>
                            </td>
                            <td>
                                <?php if ($exp['active']): ?>
                                    <span class="badge badge--success">Actif</span>
                                <?php else: ?>
                                    <span class="badge badge--danger">Inactif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 10px;">
                                    <a href="?action=edit&id=<?php echo $exp['id']; ?>" class="btn btn--outline" style="padding: 8px 12px; font-size: 12px;">
                                        ✏️ Éditer
                                    </a>
                                    <a href="?action=toggle&id=<?php echo $exp['id']; ?>" class="btn btn--outline" style="padding: 8px 12px; font-size: 12px;">
                                        👁️ <?php echo $exp['active'] ? 'Masquer' : 'Afficher'; ?>
                                    </a>
                                    <a href="?action=delete&id=<?php echo $exp['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>" class="btn btn--danger" style="padding: 8px 12px; font-size: 12px;" onclick="return confirm('Confirmer la suppression ?')">
                                        🗑️ Supprimer
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

<?php else: ?>
    <!-- FORMULAIRE ADD/EDIT -->
    <div class="card">
        <div class="card-header">
            <h2><?php echo $action === 'add' ? 'Ajouter une expérience' : 'Modifier une expérience'; ?></h2>
        </div>
        
        <form method="POST" class="card-body">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="title" class="required">Titre du poste</label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        class="form-control"
                        value="<?php echo escape($formData['title'] ?? ''); ?>"
                        placeholder="Développeur Web, Stagiaire..."
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="company" class="required">Entreprise/École</label>
                    <input 
                        type="text" 
                        id="company" 
                        name="company" 
                        class="form-control"
                        value="<?php echo escape($formData['company'] ?? ''); ?>"
                        placeholder="Nom de l'entreprise ou école"
                        required
                    >
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="type" class="required">Type</label>
                    <select id="type" name="type" class="form-control" required>
                        <option value="experience" <?php echo ($formData['type'] ?? 'experience') === 'experience' ? 'selected' : ''; ?>>Expérience</option>
                        <option value="stage" <?php echo ($formData['type'] ?? '') === 'stage' ? 'selected' : ''; ?>>Stage</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="start_date" class="required">Date de début</label>
                    <input 
                        type="date" 
                        id="start_date" 
                        name="start_date" 
                        class="form-control"
                        value="<?php echo escape($formData['start_date'] ?? ''); ?>"
                        required
                    >
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="end_date">Date de fin</label>
                    <input 
                        type="date" 
                        id="end_date" 
                        name="end_date" 
                        class="form-control"
                        value="<?php echo escape($formData['end_date'] ?? ''); ?>"
                    >
                </div>
                
                <div class="form-group">
                    <label for="display_order">Ordre d'affichage</label>
                    <input 
                        type="number" 
                        id="display_order" 
                        name="display_order" 
                        class="form-control"
                        value="<?php echo $formData['display_order'] ?? 0; ?>"
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
                    placeholder="Description de l'expérience..."
                ><?php echo escape($formData['description_fr'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="description_en">Description (Anglais)</label>
                <textarea 
                    id="description_en" 
                    name="description_en" 
                    class="form-control"
                    rows="4"
                    placeholder="Description in English..."
                ><?php echo escape($formData['description_en'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_current" <?php echo ($formData['is_current'] ?? 0) ? 'checked' : ''; ?>>
                        Expérience actuelle
                    </label>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="active" <?php echo ($formData['active'] ?? 1) ? 'checked' : ''; ?>>
                        Afficher sur le portfolio
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
                    <span>Enregistrer</span>
                </button>
                
                <a href="?action=list" class="btn btn--outline">Annuler</a>
            </div>
        </form>
    </div>
<?php endif; ?>
