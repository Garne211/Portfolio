<?php
/**
 * API : Expériences et Stages
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
    
    // Récupérer les expériences
    $stmt = $db->query("
        SELECT 
            id,
            title,
            company,
            description_" . ($lang === 'fr' ? 'fr' : 'en') . " as description,
            start_date,
            end_date,
            is_current,
            type
        FROM experiences 
        WHERE active = 1
        ORDER BY display_order ASC, start_date DESC
    ");
    $experiences = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    jsonResponse([
        'success' => true,
        'experiences' => $experiences,
        'count' => count($experiences)
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
