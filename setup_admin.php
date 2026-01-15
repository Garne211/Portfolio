<?php
/**
 * =====================================================
 * SCRIPT DE CONFIGURATION ADMIN + INITIALISATION DB
 * À EXÉCUTER UNE SEULE FOIS puis SUPPRIMER
 * =====================================================
 */

require_once __DIR__ . '/includes/db.php';

$username = 'HLEBEHOT';
$password = 'Lesperseverants1';

try {
    $db = getDBConnection();
    
    echo "<h2>🚀 Initialisation de la base de données</h2>";
    
    // Lire et exécuter le schéma SQL
    $sqlFile = __DIR__ . '/schema_sqlite.sql';
    
    if (!file_exists($sqlFile)) {
        die("❌ Fichier schema_sqlite.sql introuvable");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Exécuter le SQL (SQLite supporte plusieurs requêtes avec exec)
    $db->exec($sql);
    
    echo "✅ Tables créées avec succès<br>";
    echo "✅ Données initiales insérées<br><br>";
    
    // Vérifier si un admin existe déjà
    $stmt = $db->query("SELECT COUNT(*) FROM admin_users");
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        echo "<h3>⚠️ Compte administrateur existant</h3>";
        echo "Un admin existe déjà. Aucun nouveau compte créé.<br><br>";
    } else {
        // Créer l'administrateur
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $hashedPassword]);
        
        echo "<h3>✅ Compte administrateur créé !</h3>";
        echo "<strong>Identifiant :</strong> $username<br>";
        echo "<strong>Mot de passe :</strong> $password<br><br>";
    }
    
    echo "<div style='background: #fee; padding: 1rem; border: 2px solid #c00; border-radius: 8px; margin: 1rem 0;'>";
    echo "<strong>🔒 SÉCURITÉ CRITIQUE :</strong><br>";
    echo "Supprimez immédiatement ce fichier <code>setup_admin.php</code> !<br>";
    echo "<code>rm setup_admin.php</code> ou suppression manuelle";
    echo "</div>";
    
    echo '<a href="/admin/login.php" style="display: inline-block; padding: 0.75rem 1.5rem; background: #dc2626; color: white; text-decoration: none; border-radius: 8px; margin-top: 1rem;">→ Aller à la page de connexion</a>';
    
} catch (Exception $e) {
    die('❌ Erreur : ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Admin</title>
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: #0a0a0a;
            color: #f5f5f5;
            line-height: 1.6;
        }
        h2, h3 { color: #dc2626; }
        code {
            background: #1a1a1a;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>