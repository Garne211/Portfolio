<?php
/**
 * API : Formulaire de Contact
 * Reçoit les messages et les sauvegarde + envoie un email
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');

// Gérer les requêtes OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Vérifier que c'est une requête POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonError('Méthode non autorisée', 405);
    }

    // Récupérer et valider les données
    $data = [
        'name' => sanitizeInput($_POST['name'] ?? ''),
        'email' => sanitizeInput($_POST['email'] ?? ''),
        'subject' => sanitizeInput($_POST['subject'] ?? ''),
        'message' => sanitizeInput($_POST['message'] ?? ''),
    ];

    // Validations
    if (empty($data['name'])) {
        jsonError('Le nom est requis');
    }
    if (empty($data['email']) || !validateEmail($data['email'])) {
        jsonError('Email invalide');
    }
    if (empty($data['subject'])) {
        jsonError('Le sujet est requis');
    }
    if (empty($data['message'])) {
        jsonError('Le message est requis');
    }

    $db = getDBConnection();

    // Créer la table si elle n'existe pas
    $db->exec("CREATE TABLE IF NOT EXISTS messages (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        message LONGTEXT NOT NULL,
        is_read TINYINT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        replied_at TIMESTAMP NULL,
        reply_message LONGTEXT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Insérer le message dans la BD
    $stmt = $db->prepare("
        INSERT INTO messages (name, email, subject, message)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $data['name'],
        $data['email'],
        $data['subject'],
        $data['message']
    ]);
    
    $messageId = $db->lastInsertId();

    // Envoyer un email de confirmation à l'utilisateur
    $to = $data['email'];
    $subject = "Confirmation : Nous avons reçu votre message";
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@portfolio.local" . "\r\n";

    $emailBody = "
    <html>
        <body style='font-family: Arial; line-height: 1.6;'>
            <h2>Merci pour votre message ! 😊</h2>
            <p>Nous avons bien reçu votre demande et nous vous répondrons au plus vite.</p>
            
            <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
            
            <h3>Récapitulatif de votre message:</h3>
            <p><strong>Nom:</strong> " . htmlspecialchars($data['name']) . "</p>
            <p><strong>Email:</strong> " . htmlspecialchars($data['email']) . "</p>
            <p><strong>Sujet:</strong> " . htmlspecialchars($data['subject']) . "</p>
            <p><strong>Message:</strong></p>
            <p>" . nl2br(htmlspecialchars($data['message'])) . "</p>
            
            <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
            
            <p>Cordialement,<br>L'équipe</p>
        </body>
    </html>";

    // Essayer d'envoyer l'email (peut échouer en local)
    $emailSent = @mail($to, $subject, $emailBody, $headers);

    // Envoyer aussi un email à l'administrateur
    $adminEmail = 'admin@portfolio.local';
    $adminSubject = "Nouveau message reçu : " . $data['subject'];
    $adminBody = "
    <html>
        <body style='font-family: Arial; line-height: 1.6;'>
            <h2>Nouveau message reçu</h2>
            <p><strong>De:</strong> " . htmlspecialchars($data['name']) . " (" . htmlspecialchars($data['email']) . ")</p>
            <p><strong>Sujet:</strong> " . htmlspecialchars($data['subject']) . "</p>
            <p><strong>Message:</strong></p>
            <p>" . nl2br(htmlspecialchars($data['message'])) . "</p>
            
            <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
            
            <p><a href='http://localhost/Portfolio/admin/messages.php?id=$messageId'>Voir et répondre dans l'admin</a></p>
        </body>
    </html>";

    @mail($adminEmail, $adminSubject, $adminBody, $headers);

    jsonSuccess([
        'message_id' => $messageId,
        'message' => 'Votre message a été reçu avec succès!',
        'email_sent' => $emailSent
    ]);

} catch (Exception $e) {
    jsonError('Erreur serveur: ' . $e->getMessage(), 500);
}
?>
