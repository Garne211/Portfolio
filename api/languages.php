<?php
/**
 * API : Langages et Compétences
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// Toujours répondre en JSON, même si Accept n'est pas application/json
header('Content-Type: application/json; charset=utf-8');

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    $db = getDBConnection();
    
    // Récupérer tous les langages actifs
    $stmt = $db->prepare("
        SELECT 
            id,
            slug,
            name_fr,
            name_en,
            percentage,
            description_fr,
            description_en,
            icon_path,
            active
        FROM languages 
        WHERE active = 1
        ORDER BY percentage DESC
    ");
    $stmt->execute();
    $languages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    jsonResponse([
        'success' => true,
        'languages' => $languages
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
