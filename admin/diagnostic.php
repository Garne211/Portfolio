<?php
/**
 * Diagnostic Login - Vérifier pourquoi la connexion ne fonctionne pas
 */

echo "<h1>🔍 Diagnostic Connexion Admin</h1>";

// Vérifier la BD
$db_path = __DIR__ . '/../databases/portfolio.db';
echo "<h3>1. Base de Données</h3>";
if (file_exists($db_path)) {
    echo "✅ Fichier portfolio.db existe<br>";
} else {
    echo "❌ Fichier portfolio.db MANQUANT<br>";
    echo "<a href='../install.html'>➡️ Initialiser la BD</a><br>";
    exit;
}

// Vérifier la connexion
try {
    require_once __DIR__ . '/../includes/db.php';
    $pdo = getDBConnection();
    echo "✅ Connexion à la BD réussie<br>";
} catch (Exception $e) {
    echo "❌ Erreur de connexion: " . $e->getMessage() . "<br>";
    exit;
}

// Vérifier la table admin_users
echo "<h3>2. Table admin_users</h3>";
try {
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='admin_users'");
    $table = $stmt->fetch();
    
    if ($table) {
        echo "✅ Table admin_users existe<br>";
    } else {
        echo "❌ Table admin_users MANQUANTE<br>";
        echo "<a href='../install.html'>➡️ Initialiser la BD</a><br>";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "<br>";
    exit;
}

// Vérifier les colonnes
echo "<h3>3. Colonnes de la Table</h3>";
try {
    $stmt = $pdo->query("PRAGMA table_info(admin_users)");
    $columns = $stmt->fetchAll();
    
    if (empty($columns)) {
        echo "❌ Aucune colonne trouvée<br>";
    } else {
        echo "✅ Colonnes détectées:<br>";
        foreach ($columns as $col) {
            echo "  - " . $col['name'] . " (" . $col['type'] . ")<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "<br>";
}

// Vérifier les données
echo "<h3>4. Utilisateurs Enregistrés</h3>";
try {
    $stmt = $pdo->query("SELECT id, username, email FROM admin_users");
    $users = $stmt->fetchAll();
    
    if (empty($users)) {
        echo "❌ AUCUN UTILISATEUR ENREGISTRÉ<br>";
        echo "<strong>Solution:</strong> Réinitialisez la BD<br>";
        echo "<a href='../install.html' style='display:inline-block; padding:10px 20px; background:#dc2626; color:white; text-decoration:none; border-radius:6px; margin-top:10px;'>Initialiser la BD</a><br>";
    } else {
        echo "✅ " . count($users) . " utilisateur(s) trouvé(s):<br>";
        foreach ($users as $user) {
            echo "  - Username: <strong>" . htmlspecialchars($user['username']) . "</strong><br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "<br>";
}

// Vérifier les fichiers
echo "<h3>5. Fichiers Requis</h3>";
$files = [
    __DIR__ . '/../includes/db.php' => 'includes/db.php',
    __DIR__ . '/../includes/helpers.php' => 'includes/helpers.php',
    __DIR__ . '/login.php' => 'admin/login.php',
    __DIR__ . '/assets/css/admin.css' => 'admin/assets/css/admin.css'
];

foreach ($files as $path => $name) {
    if (file_exists($path)) {
        echo "✅ $name<br>";
    } else {
        echo "❌ $name MANQUANT<br>";
    }
}

// Test de connexion
echo "<h3>6. Test de Connexion</h3>";
echo "<form method='POST' style='margin-top:10px;'>";
echo "  <label>Username: <input type='text' name='test_user' placeholder='admin'></label><br>";
echo "  <label>Password: <input type='password' name='test_pass' placeholder='admin123456'></label><br>";
echo "  <button type='submit'>Tester</button>";
echo "</form>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $test_user = trim($_POST['test_user'] ?? '');
    $test_pass = $_POST['test_pass'] ?? '';
    
    echo "<h4>Résultat du Test</h4>";
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ? LIMIT 1");
        $stmt->execute([$test_user]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$admin) {
            echo "❌ Utilisateur '<strong>$test_user</strong>' non trouvé<br>";
            echo "Utilisateurs disponibles:<br>";
            $all = $pdo->query("SELECT username FROM admin_users");
            $list = $all->fetchAll();
            foreach ($list as $u) {
                echo "  - " . htmlspecialchars($u['username']) . "<br>";
            }
        } else {
            echo "✅ Utilisateur '<strong>" . htmlspecialchars($admin['username']) . "</strong>' trouvé<br>";
            
            // Vérifier le mot de passe
            if (password_verify($test_pass, $admin['password'])) {
                echo "✅ Mot de passe CORRECT<br>";
                echo "🎉 <strong style='color:green;'>La connexion devrait fonctionner!</strong><br>";
                echo "<a href='login.php' style='display:inline-block; padding:10px 20px; background:#22c55e; color:white; text-decoration:none; border-radius:6px; margin-top:10px;'>Aller à la page de connexion</a>";
            } else {
                echo "❌ Mot de passe INCORRECT<br>";
                echo "Mot de passe par défaut: <code>admin123456</code><br>";
            }
        }
    } catch (Exception $e) {
        echo "❌ Erreur: " . $e->getMessage() . "<br>";
    }
}

echo "<hr>";
echo "<h3>7. Actions Recommandées</h3>";
echo "<ul>";
echo "  <li><a href='../install.html'>Réinitialiser la base de données</a></li>";
echo "  <li><a href='../status.php'>Voir le diagnostic système</a></li>";
echo "  <li><a href='login.php'>Retour à la page de connexion</a></li>";
echo "</ul>";

?>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f5f5f5;
    padding: 20px;
}
h1 { color: #333; }
h3 { color: #666; margin-top: 20px; }
input { padding: 5px; margin: 5px 0; }
button { padding: 8px 16px; background: #dc2626; color: white; border: none; cursor: pointer; border-radius: 4px; }
button:hover { background: #991b1b; }
a { color: #3b82f6; }
code { background: #ddd; padding: 2px 4px; border-radius: 3px; }
</style>
