<?php
/**
 * API : Éducation
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    $db = getDBConnection();
    
    $lang = $_GET['lang'] ?? 'fr';
    if (!in_array($lang, ['fr', 'en'])) {
        $lang = 'fr';
    }
    
    // Vérifier les colonnes disponibles
    $columns = $db->query("DESCRIBE education")->fetchAll(PDO::FETCH_COLUMN, 0);
    $hasTranslations = in_array('school_en', $columns);
    
    // Récupérer l'éducation (classé par date décroissante : plus récent en premier)
    if ($hasTranslations) {
        $stmt = $db->query("
            SELECT 
                id,
                school,
                school_en,
                degree,
                degree_en,
                field,
                field_en,
                description_" . ($lang === 'fr' ? 'fr' : 'en') . " as description,
                start_date,
                end_date,
                is_current
            FROM education 
            WHERE active = 1
            ORDER BY start_date DESC, display_order ASC
        ");
    } else {
        // Fallback si les colonnes de traduction n'existent pas encore
        $stmt = $db->query("
            SELECT 
                id,
                school,
                school as school_en,
                degree,
                degree as degree_en,
                field,
                field as field_en,
                description_" . ($lang === 'fr' ? 'fr' : 'en') . " as description,
                start_date,
                end_date,
                is_current
            FROM education 
            WHERE active = 1
            ORDER BY start_date DESC, display_order ASC
        ");
    }
    $education = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    jsonResponse([
        'success' => true,
        'education' => $education,
        'count' => count($education)
    ]);
    
} catch (PDOException $e) {
    jsonResponse([
        'success' => false,
        'error' => 'Erreur base de données: ' . $e->getMessage()
    ], 500);
} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'error' => $e->getMessage()
    ], 500);
}
?>
