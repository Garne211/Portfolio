<?php
function escape($data) {
    if (is_array($data)) {
        return array_map('escape', $data);
    }
    if (empty($data) && $data !== 0 && $data !== '0') {
        return '';
    }
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        if (!preg_match('#^/[a-zA-Z0-9/_.-]*$#', $url)) {
            $url = '/admin/dashboard.php';
        }
    } else {
        $urlHost = parse_url($url, PHP_URL_HOST);
        $currentHost = $_SERVER['HTTP_HOST'];
        if ($urlHost && $urlHost !== $currentHost && $urlHost !== 'localhost') {
            $url = '/admin/dashboard.php';
        }
    }
    header("Location: $url", true, 302);
    exit();
}

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}

function jsonError($message, $statusCode = 400) {
    jsonResponse(['success' => false, 'error' => $message], $statusCode);
}

function jsonSuccess($data = [], $message = null) {
    $response = ['success' => true];
    if ($message) {
        $response['message'] = $message;
    }
    $response = array_merge($response, $data);
    jsonResponse($response);
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validateUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

function generateSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9]+/', '-', $string);
    $string = trim($string, '-');
    return $string;
}

function formatDate($date, $format = 'd/m/Y') {
    if (empty($date)) return '';
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    return date($format, $timestamp);
}

function truncate($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true && isset($_SESSION['session_token']) && isset($_SESSION['session_expires_at']);
}

function checkAndRefreshSession() {
    if (!isLoggedIn()) {
        return false;
    }
    if (strtotime($_SESSION['session_expires_at']) < time()) {
        return refreshSession();
    }
    return true;
}

function refreshSession() {
    if (!isset($_SESSION['refresh_token']) || !isset($_SESSION['admin_id'])) {
        return false;
    }
    try {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT * FROM admin_sessions WHERE refresh_token = ? AND admin_id = ? AND expires_at > NOW() LIMIT 1");
        $stmt->execute([$_SESSION['refresh_token'], $_SESSION['admin_id']]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$session) {
            session_destroy();
            return false;
        }
        $newSessionToken = bin2hex(random_bytes(32));
        $newRefreshToken = bin2hex(random_bytes(32));
        $newExpiresAt = date('Y-m-d H:i:s', time() + 180);
        $updateStmt = $db->prepare("UPDATE admin_sessions SET session_token = ?, refresh_token = ?, expires_at = ?, last_activity = CURRENT_TIMESTAMP WHERE id = ?");
        $updateStmt->execute([$newSessionToken, $newRefreshToken, $newExpiresAt, $session['id']]);
        $_SESSION['session_token'] = $newSessionToken;
        $_SESSION['refresh_token'] = $newRefreshToken;
        $_SESSION['session_expires_at'] = $newExpiresAt;
        return true;
    } catch (Exception $e) {
        error_log("Session refresh error: " . $e->getMessage());
        return false;
    }
}

function requireAuth() {
    if (!checkAndRefreshSession()) {
        redirect('/admin/login.php');
    }
}

function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return trim(strip_tags($data));
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function getUserRole() {
    return $_SESSION['admin_role'] ?? 'admin';
}

function canEdit() {
    return getUserRole() !== 'viewer';
}

function isAdmin() {
    return getUserRole() === 'admin';
}

function requireEditAccess() {
    if (!canEdit()) {
        include __DIR__ . '/../admin/includes/access-denied.php';
        exit();
    }
}
