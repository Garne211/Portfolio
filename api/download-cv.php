<?php
/**
 * API : Télécharger le CV
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

function buildReadOnlyDocx($sourcePath) {
    if (!class_exists('ZipArchive')) {
        return null;
    }

    $tempBase = tempnam(sys_get_temp_dir(), 'cv_ro_');
    if ($tempBase === false) {
        return null;
    }

    $tempPath = $tempBase . '.docx';
    @unlink($tempBase);

    if (!copy($sourcePath, $tempPath)) {
        @unlink($tempPath);
        return null;
    }

    $zip = new ZipArchive();
    if ($zip->open($tempPath) !== true) {
        @unlink($tempPath);
        return null;
    }

    $settingsXml = $zip->getFromName('word/settings.xml');
    if ($settingsXml === false) {
        $settingsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:settings xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"></w:settings>';
    }

    $dom = new DOMDocument('1.0', 'UTF-8');
    if (!$dom->loadXML($settingsXml)) {
        $zip->close();
        @unlink($tempPath);
        return null;
    }

    $xpath = new DOMXPath($dom);
    $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

    foreach ($xpath->query('/w:settings/w:documentProtection') as $node) {
        $node->parentNode->removeChild($node);
    }

    $protection = $dom->createElementNS(
        'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
        'w:documentProtection'
    );
    $protection->setAttributeNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:edit', 'readOnly');
    $protection->setAttributeNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:enforcement', '1');
    $protection->setAttributeNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:formatting', '1');
    $dom->documentElement->appendChild($protection);

    if (!$zip->addFromString('word/settings.xml', $dom->saveXML())) {
        $zip->close();
        @unlink($tempPath);
        return null;
    }

    $zip->close();
    return $tempPath;
}

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
    
    // Vérifier que c'est un format de CV autorisé
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $cvPath);
    finfo_close($finfo);

    $allowedMimes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    
    if (!in_array($mime, $allowedMimes, true)) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'Le format de CV n\'est pas valide']);
        exit;
    }

    $extension = strtolower(pathinfo($cvPath, PATHINFO_EXTENSION));
    $downloadPath = $cvPath;

    if ($mime === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
        $lockedDocx = buildReadOnlyDocx($cvPath);
        if ($lockedDocx === null) {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['error' => 'Impossible de verrouiller le fichier DOCX']);
            exit;
        }

        $downloadPath = $lockedDocx;
        register_shutdown_function(static function() use ($lockedDocx) {
            if (is_file($lockedDocx)) {
                @unlink($lockedDocx);
            }
        });
    }

    if ($mime === 'application/msword') {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'Le format .doc ne permet pas un verrouillage fiable. Utilisez .docx ou PDF.']);
        exit;
    }

    $downloadName = 'CV.' . $extension;
    
    // Envoyer le fichier
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($downloadPath));
    header('Content-Disposition: attachment; filename="' . $downloadName . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: public');
    
    readfile($downloadPath);
    exit;
    
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
?>
