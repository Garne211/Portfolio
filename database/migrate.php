<?php
/**
 * Gestionnaire de Migrations
 * Exécute tous les fichiers de migration dans database/migrations/
 */

require_once __DIR__ . '/../config/config.php';

echo "🔄 Exécution des migrations...\n\n";

$migrationsDir = ROOT_DIR . '/database/migrations/';

if (!is_dir($migrationsDir)) {
    echo "❌ Dossier migrations non trouvé: $migrationsDir\n";
    exit(1);
}

$migrations = glob($migrationsDir . '*.php');
$db = getDBConnection();

if (empty($migrations)) {
    echo "⚠️ Aucune migration trouvée\n";
    exit(0);
}

// Créer la table de suivi des migrations
try {
    $db->exec("CREATE TABLE IF NOT EXISTS migrations (
        id INT PRIMARY KEY AUTO_INCREMENT,
        filename VARCHAR(255) NOT NULL UNIQUE,
        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    echo "✓ Table migrations prête\n\n";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}

// Exécuter les migrations
sort($migrations);
$executed = 0;

foreach ($migrations as $migrationFile) {
    $filename = basename($migrationFile);
    
    // Vérifier si déjà exécutée
    $stmt = $db->prepare("SELECT id FROM migrations WHERE filename = ?");
    $stmt->execute([$filename]);
    
    if ($stmt->fetch()) {
        echo "⊘ Ignorée (déjà exécutée): $filename\n";
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
        
        echo "✓ Exécutée: $filename\n";
        if ($output) echo "  $output\n";
        
        $executed++;
    } catch (Exception $e) {
        echo "❌ Erreur dans $filename: " . $e->getMessage() . "\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "✅ $executed migration(s) exécutée(s)\n";
echo "=" . str_repeat("=", 49) . "\n";
