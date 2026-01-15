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
$editId = isset($_GET['id']) ? (int)$_GET['id'] : null;

// DEBUG - À supprimer après test
if ($action === 'edit') {
    error_log("DEBUG education.php - Action: $action, EditID: " . ($editId ?? 'NULL'));
}

// Traitement DELETE
if ($action === 'delete' && $editId) {
    requireEditAccess();
    if (!validateCSRFToken($_GET['csrf_token'] ?? '')) {
        $error = 'Token de sécurité invalide';
    } else {
        try {
            $stmt = $db->prepare("DELETE FROM education WHERE id = ?");
            $stmt->execute([$editId]);
            $success = 'Parcours supprimé avec succès';
            $action = 'list';
        } catch (Exception $e) {
            $error = 'Erreur lors de la suppression : ' . $e->getMessage();
        }
    }
}

// Traitement FORM (ADD/EDIT)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['add', 'edit'])) {
    requireEditAccess();
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de sécurité invalide';
    } else {
        $data = [
            'school' => sanitizeInput($_POST['school'] ?? ''),
            'school_en' => sanitizeInput($_POST['school_en'] ?? ''),
            'degree' => sanitizeInput($_POST['degree'] ?? ''),
            'degree_en' => sanitizeInput($_POST['degree_en'] ?? ''),
            'field' => sanitizeInput($_POST['field'] ?? ''),
            'field_en' => sanitizeInput($_POST['field_en'] ?? ''),
            'description_fr' => sanitizeInput($_POST['description_fr'] ?? ''),
            'description_en' => sanitizeInput($_POST['description_en'] ?? ''),
            'start_date' => $_POST['start_date'] ?? '',
            'end_date' => $_POST['end_date'] ?? '',
            'is_current' => isset($_POST['is_current']) ? 1 : 0,
            'display_order' => (int)($_POST['display_order'] ?? 0),
            'active' => isset($_POST['active']) ? 1 : 0,
        ];
        
        // Si formation en cours, vider la date de fin
        if ($data['is_current']) {
            $data['end_date'] = '';
        }
        
        if (empty($data['school']) || empty($data['degree']) || empty($data['start_date'])) {
            $error = 'Les champs école, diplôme et date de début sont requis';
        } else {
            try {
                if ($action === 'add') {
                    $stmt = $db->prepare("
                        INSERT INTO education (school, school_en, degree, degree_en, field, field_en, description_fr, description_en, start_date, end_date, is_current, display_order, active)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $data['school'],
                        $data['school_en'],
                        $data['degree'],
                        $data['degree_en'],
                        $data['field'],
                        $data['field_en'],
                        $data['description_fr'],
                        $data['description_en'],
                        $data['start_date'],
                        $data['end_date'] ?: null,
                        $data['is_current'],
                        $data['display_order'],
                        $data['active']
                    ]);
                    $success = 'Parcours ajouté avec succès';
                } elseif ($action === 'edit' && $editId) {
                    $stmt = $db->prepare("
                        UPDATE education SET
                            school = ?,
                            school_en = ?,
                            degree = ?,
                            degree_en = ?,
                            field = ?,
                            field_en = ?,
                            description_fr = ?,
                            description_en = ?,
                            start_date = ?,
                            end_date = ?,
                            is_current = ?,
                            display_order = ?,
                            active = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $data['school'],
                        $data['school_en'],
                        $data['degree'],
                        $data['degree_en'],
                        $data['field'],
                        $data['field_en'],
                        $data['description_fr'],
                        $data['description_en'],
                        $data['start_date'],
                        $data['end_date'] ?: null,
                        $data['is_current'],
                        $data['display_order'],
                        $data['active'],
                        $editId
                    ]);
                    $success = 'Parcours modifié avec succès';
                }
                $action = 'list';
            } catch (Exception $e) {
                $error = 'Erreur lors de l\'enregistrement : ' . $e->getMessage();
            }
        }
    }
}

// Récupération des parcours scolaires (classés par date décroissante : plus récent en premier)
$parcours = $db->query("SELECT * FROM education ORDER BY start_date DESC, display_order ASC")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>
<div class="main-content">
    <div class="content-header">
        <div>
            <h1 class="page-title">📚 Parcours scolaire</h1>
            <p class="page-subtitle">Gérez votre formation académique</p>
        </div>
        <a href="?action=add" class="btn btn--primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:0.5rem;">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Ajouter un parcours
        </a>
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert--success fade-in">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:0.75rem;">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert--danger fade-in">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:0.75rem;">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    
    <!-- DEBUG INFO -->
    <?php if ($action === 'edit' || $action === 'add'): ?>
        <div style="background: #333; color: #fff; padding: 10px; margin-bottom: 20px; border-radius: 4px; font-family: monospace; font-size: 12px;">
            <strong>DEBUG:</strong> Action = <?= htmlspecialchars($action) ?> | EditID = <?= htmlspecialchars($editId ?? 'NULL') ?>
        </div>
    <?php endif; ?>

    <?php if ($action === 'list'): ?>
        <?php if (empty($parcours)): ?>
            <div class="empty-state">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                </svg>
                <h3>Aucun parcours scolaire</h3>
                <p>Commencez par ajouter votre formation académique</p>
                <a href="?action=add" class="btn btn--primary">Ajouter un parcours</a>
            </div>
        <?php else: ?>
            <div class="card-grid">
                <?php foreach ($parcours as $row): ?>
                    <div class="education-card">
                        <div class="education-card__header">
                            <div class="education-card__icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                                    <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                                </svg>
                            </div>
                            <div class="education-card__info">
                                <h3><?= htmlspecialchars($row['school']) ?></h3>
                                <p class="education-card__degree"><?= htmlspecialchars($row['degree']) ?></p>
                            </div>
                            <div class="education-card__badge <?= $row['is_current'] ? 'badge--success' : 'badge--default' ?>">
                                <?= $row['is_current'] ? 'En cours' : 'Terminé' ?>
                            </div>
                        </div>
                        
                        <div class="education-card__body">
                            <?php if ($row['field']): ?>
                                <div class="education-card__field">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="16 18 22 12 16 6"></polyline>
                                        <polyline points="8 6 2 12 8 18"></polyline>
                                    </svg>
                                    <span><?= htmlspecialchars($row['field']) ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="education-card__dates">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                                <span><?= htmlspecialchars($row['start_date']) ?> - <?= $row['end_date'] ? htmlspecialchars($row['end_date']) : 'Aujourd\'hui' ?></span>
                            </div>
                        </div>
                        
                        <div class="education-card__actions">
                            <a href="?action=edit&id=<?= $row['id'] ?>" class="btn btn--sm btn--outline">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                                Modifier
                            </a>
                            <a href="?action=delete&id=<?= $row['id'] ?>&csrf_token=<?= generateCSRFToken() ?>" 
                               class="btn btn--sm btn--danger" 
                               onclick="return confirm('Supprimer ce parcours ?')">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                                Supprimer
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php elseif (in_array($action, ['add', 'edit'])): ?>
        <?php
        $row = [
            'school' => '', 'school_en' => '', 'degree' => '', 'degree_en' => '', 'field' => '', 'field_en' => '', 'description_fr' => '', 'description_en' => '', 'start_date' => '', 'end_date' => '', 'is_current' => 0, 'display_order' => 0, 'active' => 1
        ];
        if ($action === 'edit' && $editId) {
            $stmt = $db->prepare("SELECT * FROM education WHERE id = ?");
            $stmt->execute([$editId]);
            $fetchedRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($fetchedRow) {
                $row = $fetchedRow;
            } else {
                $error = "Parcours non trouvé (ID: $editId)";
                $action = 'list';
            }
        }
        ?>
        <?php if ($action !== 'list'): ?>
        <div class="form-card">
            <div class="form-card__header">
                <h2><?= $action === 'add' ? 'Nouveau parcours scolaire' : 'Modifier le parcours' ?></h2>
                <p>Remplissez les informations de votre formation</p>
            </div>
            
            <form method="post" class="form-card__body">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>École / Université (Français) *</label>
                        <input type="text" name="school" value="<?= htmlspecialchars($row['school'] ?? '') ?>" placeholder="Ex: Université de Paris" required>
                    </div>
                    <div class="form-group">
                        <label>École / Université (Anglais)</label>
                        <input type="text" name="school_en" value="<?= htmlspecialchars($row['school_en'] ?? '') ?>" placeholder="Ex: University of Paris">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Diplôme (Français) *</label>
                        <input type="text" name="degree" value="<?= htmlspecialchars($row['degree'] ?? '') ?>" placeholder="Ex: Master en Informatique" required>
                    </div>
                    <div class="form-group">
                        <label>Diplôme (Anglais)</label>
                        <input type="text" name="degree_en" value="<?= htmlspecialchars($row['degree_en'] ?? '') ?>" placeholder="Ex: Master in Computer Science">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Spécialité (Français)</label>
                        <input type="text" name="field" value="<?= htmlspecialchars($row['field'] ?? '') ?>" placeholder="Ex: Développement Web">
                    </div>
                    <div class="form-group">
                        <label>Spécialité (Anglais)</label>
                        <input type="text" name="field_en" value="<?= htmlspecialchars($row['field_en'] ?? '') ?>" placeholder="Ex: Web Development">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Description (Français)</label>
                    <textarea name="description_fr" rows="4" placeholder="Décrivez votre parcours et vos accomplissements..."><?= htmlspecialchars($row['description_fr'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Description (Anglais)</label>
                    <textarea name="description_en" rows="4" placeholder="Describe your academic path and achievements..."><?= htmlspecialchars($row['description_en'] ?? '') ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Date de début *</label>
                        <input type="date" name="start_date" value="<?= htmlspecialchars($row['start_date'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Date de fin</label>
                        <input type="date" name="end_date" value="<?= htmlspecialchars($row['end_date'] ?? '') ?>" <?= $row['is_current'] ? 'disabled' : '' ?> id="end_date">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_current" value="1" <?= $row['is_current'] ? 'checked' : '' ?> onchange="toggleEndDate(this)">
                            <span>Formation en cours</span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Ordre d'affichage</label>
                        <input type="number" name="display_order" value="<?= htmlspecialchars($row['display_order'] ?? '0') ?>" placeholder="0">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="active" value="1" <?= $row['active'] ? 'checked' : '' ?>>
                        <span>Visible sur le portfolio</span>
                    </label>
                </div>
                
                <script>
                function toggleEndDate(checkbox) {
                    const endDateInput = document.getElementById('end_date');
                    if (checkbox.checked) {
                        endDateInput.value = '';
                        endDateInput.disabled = true;
                    } else {
                        endDateInput.disabled = false;
                    }
                }
                </script>
                
                <div class="form-actions">
                    <a href="education.php" class="btn btn--outline">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        Annuler
                    </a>
                    <button type="submit" class="btn btn--primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
