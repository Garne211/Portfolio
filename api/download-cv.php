<?php
/**
 * API : Télécharger le CV
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

try {
    $db = getDBConnection();
    
    // Récupérer le chemin du CV
    $profile = $db->query("SELECT cv_path FROM profile WHERE id = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    
    if (!$profile || empty($profile['cv_path'])) {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['error' => 'CV non disponible']);
        exit;
    }
    
    $cvPath = __DIR__ . '/..' . $profile['cv_path'];
    
    // Vérifier que le fichier existe
    if (!file_exists($cvPath) || !is_file($cvPath)) {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['error' => 'Fichier CV introuvable']);
        exit;
    }
    
    // Vérifier que c'est un PDF
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $cvPath);
    finfo_close($finfo);
    
    if ($mime !== 'application/pdf') {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'Le fichier n\'est pas un PDF valide']);
        exit;
    }
    
    // Envoyer le fichier
    header('Content-Type: application/pdf');
    header('Content-Length: ' . filesize($cvPath));
    header('Content-Disposition: attachment; filename="CV.pdf"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: public');
    
    readfile($cvPath);
    exit;
    
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
?>
