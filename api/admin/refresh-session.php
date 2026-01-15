<?php
/**
 * API: Renouveler la session admin
 * Endpoint: /api/admin/refresh-session.php
 */

session_start();
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';

header('Content-Type: application/json');

// Vérifier la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Vérifier si l'utilisateur est authentifié
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Essayer de renouveler la session
if (refreshSession()) {
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Session renewed',
        'new_expiry' => $_SESSION['session_expires_at']
    ]);
} else {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to refresh session'
    ]);
}
