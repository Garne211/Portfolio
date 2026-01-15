<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

requireAuth();

$db = getDBConnection();
$action = $_GET['action'] ?? 'list';
$messageId = (int)($_GET['id'] ?? 0);

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

// TRAITER LE POST AVANT D'INCLURE LE HEADER
if ($action === 'reply' && $messageId > 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireEditAccess(); // Vérifier les permissions
    
    $replyMessage = sanitizeInput($_POST['reply_message'] ?? '');
    
    if (!empty($replyMessage)) {
        $message = $db->query("SELECT email FROM messages WHERE id = $messageId")->fetch(PDO::FETCH_ASSOC);
        
        if ($message) {
            // Envoyer l'email de réponse
            $to = $message['email'];
            $subject = "Réponse à votre message";
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: noreply@portfolio.local\r\n";
            
            $emailBody = "
            <html>
                <body style='font-family: Arial; line-height: 1.6;'>
                    <h2>Nous avons une réponse pour vous!</h2>
                    <p>Voici la réponse à votre message:</p>
                    
                    <div style='background: #f5f5f5; padding: 15px; border-left: 3px solid #dc2626;'>
                        <p>" . nl2br(htmlspecialchars($replyMessage)) . "</p>
                    </div>
                    
                    <p>Merci d'avoir nous contacté!</p>
                </body>
            </html>";
            
            @mail($to, $subject, $emailBody, $headers);
        }
        
        // Mettre à jour la BD
        $stmt = $db->prepare("UPDATE messages SET reply_message = ?, replied_at = NOW() WHERE id = ?");
        $stmt->execute([$replyMessage, $messageId]);
        
        header("Location: ?action=view&id=$messageId");
        exit;
    }
}

$pageTitle = 'Messages de contact';
include 'includes/header.php';
?>

<div class="dashboard-grid">
    <?php if ($action === 'list'): ?>
        <!-- Carte : Messages -->
        <div class="dashboard-card" style="grid-column: 1 / -1;">
            <div class="card-header">
                <div class="card-icon card-icon--primary">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                </div>
                <div>
                    <h3>Messages de contact</h3>
                    <p class="card-subtitle">Tous les messages reçus</p>
                </div>
            </div>
            <div class="card-body">
                <?php
                $messages = $db->query("
                    SELECT * FROM messages 
                    ORDER BY created_at DESC
                ")->fetchAll(PDO::FETCH_ASSOC);

                if (empty($messages)): ?>
                    <div class="alert alert--info">
                        Aucun message reçu pour le moment.
                    </div>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Sujet</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($messages as $msg): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($msg['created_at'])); ?></td>
                                    <td><?php echo escape($msg['name']); ?></td>
                                    <td><?php echo escape($msg['email']); ?></td>
                                    <td><?php echo escape($msg['subject']); ?></td>
                                    <td>
                                        <?php if (!$msg['is_read']): ?>
                                            <span class="badge badge--danger">Non lu</span>
                                        <?php elseif ($msg['replied_at']): ?>
                                            <span class="badge badge--success">Répondu</span>
                                        <?php else: ?>
                                            <span class="badge badge--warning">En attente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="?action=view&id=<?php echo $msg['id']; ?>" class="btn btn--outline btn--sm">Voir</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

    <?php elseif ($action === 'view' && $messageId > 0): ?>
        <?php
        $message = $db->query(
            "SELECT * FROM messages WHERE id = $messageId"
        )->fetch(PDO::FETCH_ASSOC);

        if (!$message) {
            echo '<div class="alert alert--danger">Message non trouvé</div>';
        } else {
            // Marquer comme lu
            $db->exec("UPDATE messages SET is_read = 1 WHERE id = $messageId");
        ?>
            <div class="dashboard-card" style="grid-column: 1 / -1;">
                <div class="card-header">
                    <div class="card-icon card-icon--primary">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3><?php echo escape($message['subject']); ?></h3>
                        <p class="card-subtitle">De: <?php echo escape($message['name']); ?> &lt;<?php echo escape($message['email']); ?>&gt;</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <span class="info-label">Date:</span>
                        <span class="info-value"><?php echo date('d/m/Y à H:i:s', strtotime($message['created_at'])); ?></span>
                    </div>
                    <hr style="margin: 15px 0;">
                    
                    <h4>Message:</h4>
                    <div style="background: var(--admin-surface-light); padding: 15px; border-radius: 5px; margin: 15px 0;">
                        <p><?php echo nl2br(escape($message['message'])); ?></p>
                    </div>
                    
                    <?php if ($message['reply_message']): ?>
                        <hr style="margin: 15px 0;">
                        <h4>Votre réponse:</h4>
                        <div style="background: var(--admin-surface-light); padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 3px solid #22c55e;">
                            <p><?php echo nl2br(escape($message['reply_message'])); ?></p>
                            <small>Répondu le: <?php echo date('d/m/Y à H:i:s', strtotime($message['replied_at'])); ?></small>
                        </div>
                    <?php else: ?>
                        <hr style="margin: 15px 0;">
                        <h4>Répondre:</h4>
                        <form method="POST" action="?action=reply&id=<?php echo $messageId; ?>">
                            <div class="form-group">
                                <textarea 
                                    name="reply_message" 
                                    class="form-control"
                                    rows="6"
                                    placeholder="Votre réponse..."
                                    required></textarea>
                            </div>
                            <button type="submit" class="btn btn--primary">Envoyer la réponse</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="dashboard-card">
                <a href="?" class="btn btn--outline">← Retour aux messages</a>
            </div>
        <?php } ?>
    <?php endif; ?>
</div>

<style>
.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 0.875rem;
    font-weight: 500;
}

.badge--danger {
    background-color: rgba(220, 38, 38, 0.1);
    color: #dc2626;
}

.badge--warning {
    background-color: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
}

.badge--success {
    background-color: rgba(34, 197, 94, 0.1);
    color: #22c55e;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid var(--admin-border);
}

.table th {
    background-color: var(--admin-surface);
    font-weight: 600;
    color: var(--admin-text);
}

.table tbody tr:hover {
    background-color: var(--admin-surface-light);
}
</style>

<?php include 'includes/footer.php'; ?>
