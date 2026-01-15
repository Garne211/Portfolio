<?php
/**
 * Initialisation de la base de données via l'interface web
 * Accessible via navigateur pour éviter les problèmes de PATH PHP en CLI
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

// Empêcher l'accès en production
if (getenv('APP_ENV') === 'production') {
    die('Accès interdit en production. Utilisez la CLI.');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Initialisation Base de Données - Portfolio</title>
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
            max-width: 800px;
            margin: 0 auto;
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 12px;
            padding: 40px;
        }
        h1 {
            color: #dc2626;
            margin-bottom: 30px;
            text-align: center;
        }
        .result {
            background: #0a0a0a;
            border: 1px solid #2a2a2a;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            line-height: 1.6;
            white-space: pre-wrap;
        }
        .success {
            color: #22c55e;
        }
        .error {
            color: #dc2626;
        }
        .warning {
            color: #f59e0b;
        }
        .info {
            color: #3b82f6;
        }
        .btn {
            background: #dc2626;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .btn:hover {
            background: #991b1b;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Initialisation de la Base de Données</h1>
        
        <div class="result">
<?php
if (isset($_GET['run']) && $_GET['run'] === '1') {
    echo "🔄 Exécution des migrations...\n\n";
    
    $migrationsDir = ROOT_DIR . '/database/migrations/';
    
    if (!is_dir($migrationsDir)) {
        echo "<span class='error'>❌ Dossier migrations non trouvé: $migrationsDir</span>\n";
        exit(1);
    }
    
    $migrations = glob($migrationsDir . '*.php');
    
    if (empty($migrations)) {
        echo "<span class='warning'>⚠️ Aucune migration trouvée</span>\n";
        exit(0);
    }
    
    try {
        $db = getDBConnection();
        
        // Créer la table de suivi des migrations
        $db->exec("CREATE TABLE IF NOT EXISTS migrations (
            id INT PRIMARY KEY AUTO_INCREMENT,
            filename VARCHAR(255) NOT NULL UNIQUE,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        echo "<span class='success'>✓ Table migrations prête</span>\n\n";
        
        // Exécuter les migrations
        sort($migrations);
        $executed = 0;
        
        foreach ($migrations as $migrationFile) {
            $filename = basename($migrationFile);
            
            // Vérifier si déjà exécutée
            $stmt = $db->prepare("SELECT id FROM migrations WHERE filename = ?");
            $stmt->execute([$filename]);
            
            if ($stmt->fetch()) {
                echo "<span class='info'>⊘ Ignorée (déjà exécutée): $filename</span>\n";
                continue;
            }
            
            // Exécuter la migration
            try {
                ob_start();
                require_once $migrationFile;
                $output = ob_get_clean();
                
                // Marquer comme exécutée
                $stmt = $db->prepare("INSERT INTO migrations (filename) VALUES (?)");
                $stmt->execute([$filename]);
                
                echo "<span class='success'>✓ Exécutée: $filename</span>\n";
                if ($output) {
                    echo "  " . str_replace("\n", "\n  ", trim($output)) . "\n";
                }
                $executed++;
            } catch (Exception $e) {
                echo "<span class='error'>❌ Erreur: $filename\n  " . $e->getMessage() . "</span>\n";
            }
        }
        
        echo "\n<span class='success'>🎉 Migrations terminées: $executed exécutée(s)</span>\n";
        
    } catch (Exception $e) {
        echo "<span class='error'>❌ Erreur de connexion à la base de données:\n" . $e->getMessage() . "</span>\n";
        echo "\n<span class='warning'>⚠️ Assurez-vous que :</span>\n";
        echo "  - MySQL/MariaDB est démarré (WAMP/XAMPP)\n";
        echo "  - La base de données 'portfolio' existe\n";
        echo "  - Les identifiants sont corrects (root/vide par défaut)\n";
    }
} else {
    echo "<span class='info'>Prêt à initialiser la base de données.</span>\n\n";
    echo "<span class='warning'>⚠️ Cette action va :</span>\n";
    echo "  - Créer les tables manquantes\n";
    echo "  - Exécuter les migrations non appliquées\n";
    echo "  - Ne PAS supprimer les données existantes\n\n";
    echo "<span class='info'>Cliquez sur le bouton ci-dessous pour commencer.</span>\n";
}
?>
        </div>
        
        <?php if (!isset($_GET['run'])): ?>
        <a href="?run=1" class="btn">🚀 Lancer l'initialisation</a>
        <?php else: ?>
        <a href="init-database.php" class="btn">🔄 Relancer</a>
        <a href="admin/" class="btn" style="background:#22c55e;">✓ Aller à l'admin</a>
        <?php endif; ?>
    </div>
</body>
</html>
