<?php
/**
 * Test de connexion MySQL
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Connexion MySQL</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #1a1a1a; color: #fff; }
        .box { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { background: #22c55e; color: #000; }
        .error { background: #dc2626; }
        .info { background: #3b82f6; }
        code { background: #333; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>🔍 Test Connexion MySQL</h1>
    
    <?php
    // Test 1: Connexion sans base de données
    echo '<div class="box info"><strong>1️⃣ Test connexion MySQL (sans base)</strong></div>';
    
    try {
        $pdo = new PDO('mysql:host=localhost', 'root', '');
        echo '<div class="box success">✅ Connexion MySQL OK!</div>';
        
        // Test 2: Lister les bases de données
        echo '<div class="box info"><strong>2️⃣ Bases de données existantes</strong></div>';
        $result = $pdo->query('SHOW DATABASES');
        $databases = $result->fetchAll();
        
        echo '<div class="box" style="background: #444;">';
        foreach ($databases as $db) {
            $name = $db['Database'];
            $highlight = ($name === 'portfolio') ? ' ⭐ (portfolio trouvée!)' : '';
            echo $name . $highlight . '<br>';
        }
        echo '</div>';
        
        // Test 3: Vérifier si portfolio existe
        $portfolio_exists = false;
        foreach ($databases as $db) {
            if ($db['Database'] === 'portfolio') {
                $portfolio_exists = true;
                break;
            }
        }
        
        if ($portfolio_exists) {
            echo '<div class="box success">✅ Base de données <code>portfolio</code> existe!</div>';
            
            // Test tables
            $pdo->exec("USE portfolio");
            $result = $pdo->query('SHOW TABLES');
            $tables = $result->fetchAll();
            
            echo '<div class="box info"><strong>3️⃣ Tables dans portfolio</strong></div>';
            echo '<div class="box" style="background: #444;">';
            foreach ($tables as $table) {
                $table_name = reset($table);
                echo '📋 ' . $table_name . '<br>';
            }
            echo '</div>';
        } else {
            echo '<div class="box error">❌ Base de données <code>portfolio</code> n\'existe pas!</div>';
            echo '<div class="box info" style="background: #1a5f7a;"><strong>Solution:</strong><br>';
            echo '1. Allez à <a href="init_database.php" style="color: #fff; text-decoration: underline;">init_database.php</a><br>';
            echo '2. Cliquez sur le bouton pour initialiser<br>';
            echo '3. Revenez ici pour vérifier</div>';
        }
        
    } catch (PDOException $e) {
        echo '<div class="box error">❌ Erreur: ' . $e->getMessage() . '</div>';
        echo '<div class="box info" style="background: #1a5f7a;"><strong>Vérifiez:</strong><br>';
        echo '1. MySQL est démarré dans WAMP<br>';
        echo '2. Les credentials sont: User=<code>root</code>, Password=<code>(vide)</code><br>';
        echo '3. Vérifiez dans phpMyAdmin</div>';
    }
    ?>
    
    <hr style="border-color: #444; margin-top: 30px;">
    
    <h2>📋 Prochaines étapes</h2>
    <ol>
        <li><a href="init_database.php" style="color: #0ea5e9;">Exécuter init_database.php</a> pour créer la BD</li>
        <li>Retour ici pour vérifier que tout fonctionne</li>
        <li>Aller à <a href="admin/login.php" style="color: #0ea5e9;">admin/login.php</a></li>
        <li>Se connecter avec: admin / admin123456</li>
    </ol>
</body>
</html>
