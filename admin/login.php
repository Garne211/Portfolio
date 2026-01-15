<?php
// Configuration de la session AVANT session_start()
ini_set('session.gc_maxlifetime', 180); // 3 minutes en secondes
session_set_cookie_params([
    'lifetime' => 180, // 3 minutes
    'path' => '/',
    'httponly' => true,
    'secure' => false, // À mettre à true en HTTPS
    'samesite' => 'Strict'
]);

session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// Si déjà connecté, rediriger vers dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

// Rate limiting - Empêcher les attaques par force brute
function checkRateLimit($ip, $maxAttempts = 5, $timeWindow = 900) { // 15 minutes
    $rateLimitFile = __DIR__ . '/../uploads/.rate_limit_' . md5($ip) . '.json';
    
    $data = ['attempts' => 0, 'first_attempt' => time()];
    if (file_exists($rateLimitFile)) {
        $data = json_decode(file_get_contents($rateLimitFile), true) ?? $data;
    }
    
    // Réinitialiser si la fenêtre de temps a expiré
    if (time() - $data['first_attempt'] > $timeWindow) {
        $data = ['attempts' => 0, 'first_attempt' => time()];
    }
    
    // Vérifier si le limite est atteinte
    if ($data['attempts'] >= $maxAttempts) {
        return false;
    }
    
    return true;
}

function recordFailedAttempt($ip) {
    $rateLimitFile = __DIR__ . '/../uploads/.rate_limit_' . md5($ip) . '.json';
    
    $data = ['attempts' => 0, 'first_attempt' => time()];
    if (file_exists($rateLimitFile)) {
        $data = json_decode(file_get_contents($rateLimitFile), true) ?? $data;
    }
    
    $data['attempts']++;
    file_put_contents($rateLimitFile, json_encode($data), LOCK_EX);
}

function clearRateLimit($ip) {
    $rateLimitFile = __DIR__ . '/../uploads/.rate_limit_' . md5($ip) . '.json';
    if (file_exists($rateLimitFile)) {
        unlink($rateLimitFile);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    // Vérifier le rate limit
    if (!checkRateLimit($ip)) {
        $error = 'Trop de tentatives de connexion. Veuillez réessayer plus tard.';
        http_response_code(429);
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'Veuillez remplir tous les champs';
            recordFailedAttempt($ip);
        } else {
            try {
                $db = getDBConnection();
                $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ? LIMIT 1");
                $stmt->execute([$username]);
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($admin && password_verify($password, $admin['password'])) {
                    // Connexion réussie
                    clearRateLimit($ip); // Effacer le rate limit
                    
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_role'] = $admin['role'] ?? 'admin'; // Stocker le rôle
                    
                    // Générer les tokens
                    $sessionToken = bin2hex(random_bytes(32));
                    $refreshToken = bin2hex(random_bytes(32));
                    $expiresAt = date('Y-m-d H:i:s', time() + 180); // 3 minutes
                    
                    $_SESSION['session_token'] = $sessionToken;
                    $_SESSION['refresh_token'] = $refreshToken;
                    $_SESSION['session_expires_at'] = $expiresAt;
                    
                    // Stocker les tokens en base de données
                    $insertStmt = $db->prepare("
                        INSERT INTO admin_sessions 
                        (admin_id, session_token, refresh_token, expires_at, ip_address, user_agent)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $insertStmt->execute([
                        $admin['id'],
                        $sessionToken,
                        $refreshToken,
                        $expiresAt,
                        $_SERVER['REMOTE_ADDR'] ?? '',
                        $_SERVER['HTTP_USER_AGENT'] ?? ''
                    ]);
                    
                    // Mettre à jour la dernière connexion
                    $updateStmt = $db->prepare("UPDATE admin_users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
                    $updateStmt->execute([$admin['id']]);
                    
                    header('Location: dashboard.php');
                    exit();
                } else {
                    $error = 'Identifiants incorrects';
                    recordFailedAttempt($ip); // Enregistrer la tentative échouée
                }
            } catch (Exception $e) {
                error_log("Login error: " . $e->getMessage());
                $error = 'Erreur de connexion à la base de données';
                recordFailedAttempt($ip); // Enregistrer la tentative échouée
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Administration</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <div class="login-logo">
                    <span class="logo__bracket">&lt;</span>
                    <span class="logo__name">ADMIN</span>
                    <span class="logo__bracket">/&gt;</span>
                </div>
                <h1>Connexion Administration</h1>
                <p>Portfolio LEBEHOT Hery</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert--error">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Identifiant</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-control"
                        required 
                        autofocus
                        autocomplete="username"
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control"
                        required
                        autocomplete="current-password"
                    >
                </div>
                
                <button type="submit" class="btn btn--primary btn--block">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <span>Se connecter</span>
                </button>
            </form>
            
            <div class="login-footer">
                <a href="/" class="back-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    <span>Retour au site</span>
                </a>
            </div>
        </div>
    </div>
</body>
</html>