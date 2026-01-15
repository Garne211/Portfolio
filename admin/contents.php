<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/csrf.php';

requireAuth();

$db = getDBConnection();
$success = '';
$error = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireEditAccess(); // Vérifier les permissions
    
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de sécurité invalide';
    } else {
        try {
            $db->beginTransaction();
            
            foreach ($_POST as $key => $value) {
                if ($key === 'csrf_token') continue;
                
                // Format : content_key_lang (ex: nav.home_fr)
                if (preg_match('/^(.+)_(fr|en)$/', $key, $matches)) {
                    $contentKey = $matches[1];
                    $lang = $matches[2];
                    $column = "value_$lang";
                    
                    $stmt = $db->prepare("
                        INSERT INTO contents (content_key, $column) 
                        VALUES (?, ?)
                        ON DUPLICATE KEY UPDATE $column = ?
                    ");
                    $stmt->execute([$contentKey, $value, $value]);
                }
            }
            
            $db->commit();
            $success = 'Contenus mis à jour avec succès';
        } catch (Exception $e) {
            $db->rollBack();
            $error = 'Erreur : ' . $e->getMessage();
        }
    }
}

// Récupération des contenus
$contents = $db->query("SELECT * FROM contents ORDER BY category, content_key")->fetchAll(PDO::FETCH_ASSOC);

// Organiser par catégorie
$contentsByCategory = [];
foreach ($contents as $content) {
    $category = $content['category'] ?? 'general';
    $contentsByCategory[$category][] = $content;
}

$pageTitle = 'Gérer les contenus';
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

<div class="card">
    <div class="card-header">
        <h2>Textes et traductions</h2>
        <p class="card-subtitle">Gérez tous les textes affichés sur le site (FR / EN)</p>
    </div>
    
    <form method="POST" class="card-body">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        
        <?php foreach ($contentsByCategory as $category => $items): ?>
            <div class="content-category">
                <h3 class="category-title"><?php echo ucfirst($category); ?></h3>
                
                <?php foreach ($items as $item): ?>
                    <div class="content-item">
                        <label class="content-label"><?php echo escape($item['content_key']); ?></label>
                        <div class="content-inputs">
                            <div class="input-with-flag">
                                <span class="flag">🇫🇷 FR</span>
                                <input 
                                    type="text" 
                                    name="<?php echo escape($item['content_key']); ?>_fr" 
                                    class="form-control"
                                    value="<?php echo escape($item['value_fr'] ?? ''); ?>"
                                >
                            </div>
                            <div class="input-with-flag">
                                <span class="flag">🇬🇧 EN</span>
                                <input 
                                    type="text" 
                                    name="<?php echo escape($item['content_key']); ?>_en" 
                                    class="form-control"
                                    value="<?php echo escape($item['value_en'] ?? ''); ?>"
                                >
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        
        <div class="form-actions">
            <button type="submit" class="btn btn--primary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                <span>Enregistrer tous les contenus</span>
            </button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>