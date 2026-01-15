<?php
/**
 * Fichiers de test - À utiliser uniquement en développement
 * Accès: http://localhost/Portfolio/tests/
 */

// Lister les tests disponibles
$testsDir = __DIR__;
$tests = array_filter(glob($testsDir . '/*.php'), function($file) {
    return basename($file) !== 'index.php';
});

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tests - Portfolio</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #dc2626;
            padding-bottom: 10px;
        }
        .test-list {
            list-style: none;
            padding: 0;
        }
        .test-item {
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .test-item a {
            color: #dc2626;
            text-decoration: none;
            font-weight: bold;
        }
        .test-item a:hover {
            text-decoration: underline;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h1>🧪 Tests du Portfolio</h1>
    
    <div class="warning">
        ⚠️ <strong>Attention :</strong> Cette page est à usage de développement uniquement. 
        À désactiver ou protéger en production !
    </div>
    
    <p>Fichiers de test disponibles :</p>
    
    <?php if (empty($tests)): ?>
        <p style="color: #999;">Aucun fichier de test trouvé.</p>
    <?php else: ?>
        <ul class="test-list">
            <?php foreach ($tests as $test): ?>
                <li class="test-item">
                    <span><?php echo basename($test); ?></span>
                    <a href="<?php echo basename($test); ?>">▶ Lancer le test</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    
    <hr style="margin-top: 40px; border: none; border-top: 1px solid #ddd;">
    <p style="color: #666; font-size: 12px;">
        📁 Dossier: <code><?php echo __DIR__; ?></code>
    </p>
</body>
</html>
