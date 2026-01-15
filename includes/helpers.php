<?php
/**
 * =====================================================
 * FONCTIONS HELPER
 * =====================================================
 */

/**
 * Échappe les données HTML
 */
function escape($data) {
    if (is_array($data)) {
        return array_map('escape', $data);
    }
    // Gérer les valeurs null ou vides
    if (empty($data) && $data !== 0 && $data !== '0') {
        return '';
    }
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Redirige vers une URL (avec vérification de sécurité)
 */
function redirect($url) {
    // Éviter les redirections ouvertes (open redirect)
    // Vérifier que l'URL est relative ou du même domaine
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        // Si ce n'est pas une URL valide, vérifier si c'est un chemin relatif
        if (!preg_match('#^/[a-zA-Z0-9/_.-]*$#', $url)) {
            // Redirection par défaut si l'URL est suspecte
            $url = '/Portfolio/admin/dashboard.php';
        }
    } else {
        // Vérifier que le domaine correspond au domaine actuel
        $urlHost = parse_url($url, PHP_URL_HOST);
        $currentHost = $_SERVER['HTTP_HOST'];
        if ($urlHost && $urlHost !== $currentHost && $urlHost !== 'localhost') {
            // Redirection suspecte, rediriger vers la page par défaut
            $url = '/Portfolio/admin/dashboard.php';
        }
    }
    
    header("Location: $url", true, 302);
    exit();
}

/**
 * Retourne une réponse JSON
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}

/**
 * Retourne une erreur JSON
 */
function jsonError($message, $statusCode = 400) {
    jsonResponse([
        'success' => false,
        'error' => $message
    ], $statusCode);
}

/**
 * Retourne un succès JSON
 */
function jsonSuccess($data = [], $message = null) {
    $response = ['success' => true];
    
    if ($message) {
        $response['message'] = $message;
    }
    
    $response = array_merge($response, $data);
    jsonResponse($response);
}

/**
 * Valide un email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valide une URL
 */
function validateUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Génère un slug à partir d'une chaîne
 */
function generateSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9]+/', '-', $string);
    $string = trim($string, '-');
    return $string;
}

/**
 * Upload sécurisé de fichier avec protection contre les attaques
 */
function uploadFile($file, $allowedTypes = [], $maxSize = 5242880, $uploadDir = '../uploads/') {
    // Vérifier si le fichier existe
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => false, 'error' => 'Aucun fichier fourni'];
    }
    
    // Vérifier les erreurs d'upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'Fichier dépasse la limite du serveur',
            UPLOAD_ERR_FORM_SIZE => 'Fichier dépasse la limite du formulaire',
            UPLOAD_ERR_PARTIAL => 'Fichier partiellement uploadé',
            UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
            UPLOAD_ERR_CANT_WRITE => 'Impossible d\'écrire le fichier',
            UPLOAD_ERR_EXTENSION => 'Extension de fichier non autorisée'
        ];
        $error = $errors[$file['error']] ?? 'Erreur d\'upload inconnue';
        return ['success' => false, 'error' => $error];
    }
    
    // Vérifier la taille
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'Fichier trop volumineux (max ' . ($maxSize / 1024 / 1024) . 'MB)'];
    }
    
    // Vérifier le type MIME
    if (function_exists('finfo_file')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!empty($allowedTypes) && !in_array($mimeType, $allowedTypes)) {
            return ['success' => false, 'error' => 'Type de fichier non autorisé'];
        }
    }
    
    // Vérifier l'extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'svg', 'webp'];
    if (!in_array($extension, $allowed_extensions)) {
        return ['success' => false, 'error' => 'Extension de fichier non autorisée'];
    }
    
    // Générer un nom unique et sécurisé
    $filename = bin2hex(random_bytes(16)) . '.' . $extension;
    $destination = realpath($uploadDir) . '/' . $filename; // Éviter path traversal
    
    // Vérifier que la destination est bien dans le dossier uploads
    if (strpos($destination, realpath($uploadDir)) !== 0) {
        return ['success' => false, 'error' => 'Destination d\'upload invalide'];
    }
    
    // Créer le dossier si nécessaire
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Déplacer le fichier
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // Définir les permissions correctes
        chmod($destination, 0644);
        
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $destination,
            'url' => '/uploads/' . $filename
        ];
    }
    
    return ['success' => false, 'error' => 'Échec de l\'enregistrement du fichier'];
}

/**
 * Supprime un fichier uploadé (avec protection)
 */
function deleteUploadedFile($filename, $uploadDir = '../uploads/') {
    // Vérifier que le nom n'est pas vide et ne contient pas de chemins relatifs
    if (empty($filename) || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
        return false;
    }
    
    $filepath = realpath($uploadDir) . '/' . basename($filename);
    
    // Vérifier que le fichier est bien dans le dossier uploads
    if (strpos($filepath, realpath($uploadDir)) !== 0) {
        return false;
    }
    
    if (file_exists($filepath) && is_file($filepath)) {
        return unlink($filepath);
    }
    return false;
}

/**
 * Formatte une date
 */
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date)) return '';
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    return date($format, $timestamp);
}

/**
 * Tronque un texte
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * Vérifie si l'utilisateur est connecté
 */
function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && 
           $_SESSION['admin_logged_in'] === true &&
           isset($_SESSION['session_token']) &&
           isset($_SESSION['session_expires_at']);
}

/**
 * Vérifie la session et la renouvelle si nécessaire
 */
function checkAndRefreshSession() {
    if (!isLoggedIn()) {
        return false;
    }
    
    // Vérifier si la session a expiré
    if (strtotime($_SESSION['session_expires_at']) < time()) {
        // Session expirée, essayer de la renouveler
        return refreshSession();
    }
    
    // Session toujours valide
    return true;
}

/**
 * Renouvelle la session avec le refresh token
 */
function refreshSession() {
    if (!isset($_SESSION['refresh_token']) || !isset($_SESSION['admin_id'])) {
        return false;
    }
    
    try {
        $db = getDBConnection();
        
        // Vérifier si le refresh token existe et est valide
        $stmt = $db->prepare("
            SELECT * FROM admin_sessions 
            WHERE refresh_token = ? 
            AND admin_id = ?
            AND expires_at > NOW()
            LIMIT 1
        ");
        $stmt->execute([$_SESSION['refresh_token'], $_SESSION['admin_id']]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$session) {
            // Refresh token invalide ou expiré
            session_destroy();
            return false;
        }
        
        // Générer de nouveaux tokens
        $newSessionToken = bin2hex(random_bytes(32));
        $newRefreshToken = bin2hex(random_bytes(32));
        $newExpiresAt = date('Y-m-d H:i:s', time() + 180); // 3 minutes
        
        // Mettre à jour les tokens en base
        $updateStmt = $db->prepare("
            UPDATE admin_sessions 
            SET session_token = ?, 
                refresh_token = ?, 
                expires_at = ?,
                last_activity = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $updateStmt->execute([$newSessionToken, $newRefreshToken, $newExpiresAt, $session['id']]);
        
        // Mettre à jour la session PHP
        $_SESSION['session_token'] = $newSessionToken;
        $_SESSION['refresh_token'] = $newRefreshToken;
        $_SESSION['session_expires_at'] = $newExpiresAt;
        
        return true;
    } catch (Exception $e) {
        error_log("Session refresh error: " . $e->getMessage());
        return false;
    }
}

/**
 * Require admin authentifié
 */
function requireAuth() {
    // Vérifier et renouveler la session si nécessaire
    if (!checkAndRefreshSession()) {
        redirect('/Portfolio/admin/login.php');
    }
}

/**
 * Nettoie les données d'entrée
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return trim(strip_tags($data));
}

/**
 * Génère un token aléatoire
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Récupère le rôle de l'utilisateur connecté
 */
function getUserRole() {
    return $_SESSION['admin_role'] ?? 'admin';
}

/**
 * Vérifie si l'utilisateur peut modifier (pas un viewer)
 */
function canEdit() {
    return getUserRole() !== 'viewer';
}

/**
 * Vérifie si l'utilisateur est admin
 */
function isAdmin() {
    return getUserRole() === 'admin';
}

/**
 * Require édition - Vérifie que l'utilisateur n'est pas viewer
 */
function requireEditAccess() {
    if (!canEdit()) {
        // Inclure la page d'accès refusé
        include __DIR__ . '/../admin/includes/access-denied.php';
        exit();
    }
}