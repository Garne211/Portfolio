<?php
/**
 * API : Contenus et traductions
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// Toujours répondre en JSON, même si Accept n'est pas application/json
header('Content-Type: application/json; charset=utf-8');

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    $db = getDBConnection();
    
    // Récupérer tous les contenus
    $stmt = $db->query("SELECT content_key, value_fr, value_en, category FROM contents");
    $contents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Organiser en structure hiérarchique
    $translations = [
        'fr' => [],
        'en' => []
    ];
    
    foreach ($contents as $content) {
        $keys = explode('.', $content['content_key']);
        
        // Construire l'arborescence pour FR
        $currentFr = &$translations['fr'];
        foreach ($keys as $i => $key) {
            if ($i === count($keys) - 1) {
                $currentFr[$key] = $content['value_fr'];
            } else {
                if (!isset($currentFr[$key])) {
                    $currentFr[$key] = [];
                }
                $currentFr = &$currentFr[$key];
            }
        }
        
        // Construire l'arborescence pour EN
        $currentEn = &$translations['en'];
        foreach ($keys as $i => $key) {
            if ($i === count($keys) - 1) {
                $currentEn[$key] = $content['value_en'];
            } else {
                if (!isset($currentEn[$key])) {
                    $currentEn[$key] = [];
                }
                $currentEn = &$currentEn[$key];
            }
        }
    }
    
    jsonSuccess([
        'translations' => $translations
    ]);
    
} catch (Exception $e) {
    jsonError('Erreur serveur: ' . $e->getMessage(), 500);
}