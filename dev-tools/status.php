<?php
/**
 * Page de vérification du statut du portfolio
 * Affiche un rapport complet de l'installation
 */

$errors = [];
$warnings = [];
$success = [];

// Vérifier la version de PHP
if (version_compare(PHP_VERSION, '7.0.0', '<')) {
    $errors[] = 'PHP 7.0+ requis. Version actuelle: ' . PHP_VERSION;
} else {
    $success[] = 'PHP ' . PHP_VERSION . ' OK';
}

// Vérifier les extensions requises
$required_extensions = ['pdo', 'pdo_sqlite', 'json'];
foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $errors[] = "Extension PHP '$ext' manquante";
    } else {
        $success[] = "Extension '$ext' chargée";
    }
}

// Vérifier la base de données
$db_path = __DIR__ . '/databases/portfolio.db';
if (file_exists($db_path)) {
    $success[] = 'Base de données trouvée';
    
    // Vérifier la connexion
    try {
        $pdo = new PDO('sqlite:' . $db_path);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Vérifier les tables
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $required_tables = ['profile', 'languages', 'projects', 'contents', 'users'];
        foreach ($required_tables as $table) {
            if (in_array($table, $tables)) {
                $success[] = "Table '$table' existe";
            } else {
                $warnings[] = "Table '$table' manquante - Exécuter init_database.php";
            }
        }
    } catch (PDOException $e) {
        $errors[] = 'Erreur de connexion BD: ' . $e->getMessage();
    }
} else {
    $warnings[] = 'Base de données non trouvée - Exécuter init_database.php';
}

// Vérifier les fichiers critiques
$required_files = [
    'public/index.php',
    'public/assets/js/script.js',
    'public/assets/css/style.css',
    'api/contents.php',
    'api/languages.php',
    'api/profile.php',
    'api/projects.php',
    'includes/db.php',
    'includes/helpers.php',
    'includes/csrf.php'
];

foreach ($required_files as $file) {
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        $success[] = "Fichier '$file' OK";
    } else {
        $errors[] = "Fichier '$file' manquant";
    }
}

// Vérifier les permissions
if (is_writable(__DIR__ . '/databases')) {
    $success[] = 'Dossier databases/ accessible en écriture';
} else {
    $warnings[] = 'Dossier databases/ non accessible en écriture (chmod 775 nécessaire)';
}

// Vérifier la configuration Apache
if (file_exists(__DIR__ . '/public/.htaccess')) {
    $success[] = '.htaccess présent';
} else {
    $warnings[] = '.htaccess manquant dans public/';
}

// Générer le rapport HTML
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statut Portfolio - Hery LEBEHOT</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #141414 100%);
            color: #f5f5f5;
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        h1 {
            color: #dc2626;
            margin-bottom: 10px;
            text-align: center;
        }
        .subtitle {
            color: #a3a3a3;
            text-align: center;
            margin-bottom: 40px;
            font-size: 14px;
        }
        .status {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .status-card {
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 12px;
            padding: 20px;
        }
        .status-card h3 {
            margin-bottom: 15px;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .status-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
        .icon-success { background: #22c55e; color: white; }
        .icon-warning { background: #f59e0b; color: white; }
        .icon-error { background: #dc2626; color: white; }

        .items {
            margin-top: 20px;
        }
        .item {
            padding: 10px;
            margin-bottom: 8px;
            border-radius: 6px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .item-success {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
            border-left: 3px solid #22c55e;
        }
        .item-warning {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
            border-left: 3px solid #f59e0b;
        }
        .item-error {
            background: rgba(220, 38, 38, 0.1);
            color: #dc2626;
            border-left: 3px solid #dc2626;
        }
        .item-icon {
            font-weight: bold;
        }

        .actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 40px;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .btn-primary {
            background: #dc2626;
            color: white;
        }
        .btn-primary:hover {
            background: #991b1b;
            box-shadow: 0 0 20px rgba(220, 38, 38, 0.3);
        }
        .btn-secondary {
            background: #2a2a2a;
            color: #f5f5f5;
        }
        .btn-secondary:hover {
            background: #3a3a3a;
        }
        .summary {
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 40px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #2a2a2a;
        }
        .summary-item:last-child {
            border-bottom: none;
        }
        .summary-value {
            font-weight: bold;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Statut Portfolio</h1>
        <p class="subtitle">Rapport de diagnostic du système</p>

        <div class="summary">
            <div class="summary-item">
                <span>✅ Éléments OK:</span>
                <span class="summary-value" style="color: #22c55e;"><?php echo count($success); ?></span>
            </div>
            <div class="summary-item">
                <span>⚠️ Avertissements:</span>
                <span class="summary-value" style="color: #f59e0b;"><?php echo count($warnings); ?></span>
            </div>
            <div class="summary-item">
                <span>❌ Erreurs:</span>
                <span class="summary-value" style="color: #dc2626;"><?php echo count($errors); ?></span>
            </div>
        </div>

        <div class="status">
            <?php if (count($success) > 0): ?>
            <div class="status-card">
                <h3>
                    <span class="status-icon icon-success">✓</span>
                    Succès (<?php echo count($success); ?>)
                </h3>
                <div class="items">
                    <?php foreach ($success as $msg): ?>
                    <div class="item item-success">
                        <span class="item-icon">✓</span>
                        <span><?php echo htmlspecialchars($msg); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (count($warnings) > 0): ?>
            <div class="status-card">
                <h3>
                    <span class="status-icon icon-warning">⚠</span>
                    Avertissements (<?php echo count($warnings); ?>)
                </h3>
                <div class="items">
                    <?php foreach ($warnings as $msg): ?>
                    <div class="item item-warning">
                        <span class="item-icon">⚠</span>
                        <span><?php echo htmlspecialchars($msg); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (count($errors) > 0): ?>
            <div class="status-card">
                <h3>
                    <span class="status-icon icon-error">✗</span>
                    Erreurs (<?php echo count($errors); ?>)
                </h3>
                <div class="items">
                    <?php foreach ($errors as $msg): ?>
                    <div class="item item-error">
                        <span class="item-icon">✗</span>
                        <span><?php echo htmlspecialchars($msg); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="actions">
            <a href="install.html" class="btn btn-primary">
                🚀 Installation
            </a>
            <a href="public/" class="btn btn-secondary">
                👁️ Portfolio
            </a>
            <a href="admin/" class="btn btn-secondary">
                ⚙️ Admin
            </a>
        </div>
    </div>
</body>
</html>
