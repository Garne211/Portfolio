<?php
/**
 * Test: Vérifier les fichiers CV uploadés
 */

require_once __DIR__ . '/../config/config.php';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test CV Upload</title>
    <style>
        body {
            font-family: Arial;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
        }
        h2 {
            color: #333;
            border-bottom: 2px solid #dc2626;
            padding-bottom: 10px;
        }
        .file-list {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .file-item {
            padding: 8px;
            margin: 5px 0;
            background: white;
            border-left: 3px solid #dc2626;
        }
        .success { color: green; }
        .error { color: red; }
        code {
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New';
        }
    </style>
</head>
<body>
    <h2>📁 Fichiers dans /uploads</h2>
    <div class="file-list">
        <?php
        $uploads = glob(UPLOADS_DIR . '/*');
        if (empty($uploads)) {
            echo '<p class="error">Aucun fichier uploadé</p>';
        } else {
            foreach ($uploads as $file) {
                if (!is_dir($file)) {
                    $size = round(filesize($file) / 1024 / 1024, 2);
                    echo '<div class="file-item">' . basename($file) . " ({$size}MB)</div>";
                }
            }
        }
        ?>
    </div>
    
    <h2>📊 Données en Base de Données</h2>
    <div class="file-list">
        <?php
        try {
            $db = getDBConnection();
            $profile = $db->query("SELECT id, name, cv_path FROM profile LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            
            if ($profile) {
                echo '<p><strong>ID:</strong> ' . $profile['id'] . '</p>';
                echo '<p><strong>Nom:</strong> ' . $profile['name'] . '</p>';
                
                if ($profile['cv_path']) {
                    echo '<p><strong>CV Path:</strong> <code>' . $profile['cv_path'] . '</code></p>';
                    
                    $full_path = UPLOADS_DIR . $profile['cv_path'];
                    if (file_exists($full_path)) {
                        echo '<p class="success">✅ Fichier EXISTS</p>';
                        echo '<p><strong>Chemin complet:</strong> <code>' . $full_path . '</code></p>';
                    } else {
                        echo '<p class="error">❌ Fichier NOT FOUND</p>';
                        echo '<p><strong>Recherché à:</strong> <code>' . $full_path . '</code></p>';
                    }
                } else {
                    echo '<p class="error">❌ Aucun CV uploadé</p>';
                }
            } else {
                echo '<p class="error">❌ Pas de profil trouvé</p>';
            }
        } catch (Exception $e) {
            echo '<p class="error">❌ Erreur: ' . $e->getMessage() . '</p>';
        }
        ?>
    </div>
    
    <h2>📂 Structure des uploads</h2>
    <div class="file-list">
        <p>Dossiers créés:</p>
        <ul>
            <li><code>/uploads/</code> - Uploads généraux</li>
            <li><code>/uploads/cv/</code> - CVs</li>
            <li><code>/uploads/avatars/</code> - Avatars profil</li>
            <li><code>/uploads/projects/</code> - Images projets</li>
            <li><code>/uploads/languages/</code> - Icons langages</li>
        </ul>
    </div>
    
    <p style="margin-top: 40px; text-align: center;">
        <a href="index.php" style="color: #dc2626;">← Retour aux tests</a>
    </p>
</body>
</html>
