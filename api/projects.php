<?php
/**
 * API : Projets
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// Toujours répondre en JSON, même si Accept n'est pas application/json
header('Content-Type: application/json; charset=utf-8');

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    $db = getDBConnection();
    
    // Récupérer tous les projets visibles, triés par ordre
    $stmt = $db->prepare("
        SELECT 
            id,
            slug,
            title_fr,
            title_en,
            description_fr,
            description_en,
            technologies,
            github_url,
            demo_url,
            image_path,
            display_order,
            visible
        FROM projects 
        WHERE visible = 1 
        ORDER BY display_order ASC, created_at DESC
    ");
    
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Parser les technologies (JSON)
    foreach ($projects as &$project) {
        if ($project['technologies']) {
            $project['technologies'] = json_decode($project['technologies'], true);
        } else {
            $project['technologies'] = [];
        }
    }
    
    jsonSuccess([
        'projects' => $projects,
        'count' => count($projects)
    ]);
    
} catch (Exception $e) {
    jsonError('Erreur serveur: ' . $e->getMessage(), 500);
}