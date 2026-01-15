<?php
require_once __DIR__ . '/includes/db.php';

try {
    require __DIR__ . '/database/migrations/006_add_education_translations.php';
    echo "<br><br>✅ Migration terminée !<br>";
    echo "<a href='admin/education.php' style='padding: 10px 20px; background: #dc2626; color: #fff; text-decoration: none; border-radius: 4px;'>Retour à l'admin</a>";
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage();
}
