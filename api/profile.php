<?php
/**
 * API : Profil utilisateur
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// Toujours répondre en JSON, même si Accept n'est pas application/json
header('Content-Type: application/json; charset=utf-8');

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    $db = getDBConnection();
    
    // Récupérer le profil (ID 1 par défaut)
    $stmt = $db->prepare("
        SELECT 
            id,
            name,
            email,
            github,
            linkedin,
            tagline_fr,
            tagline_en,
            bio_fr,
            bio_en,
            avatar_path,
            avatar_centered,
            cv_path,
            created_at,
            updated_at
        FROM profile 
        WHERE id = 1
    ");
    $stmt->execute();
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$profile) {
        jsonResponse([
            'success' => false,
            'error' => 'Profil non trouvé'
        ], 404);
        exit;
    }
    
    jsonResponse([
        'success' => true,
        'profile' => $profile
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
