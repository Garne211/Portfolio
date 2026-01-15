<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

requireAuth();

$pageTitle = 'Paramètres de sécurité';
include 'includes/header.php';
?>

<div class="dashboard-grid">
    <!-- Carte : Mots de passe -->
    <div class="dashboard-card">
        <div class="card-header">
            <div class="card-icon card-icon--primary">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </div>
            <div>
                <h3>Changer le mot de passe</h3>
                <p class="card-subtitle">Sécurisez votre compte</p>
            </div>
        </div>
        <div class="card-body">
            <form id="password-form" method="POST">
                <div class="form-group">
                    <label for="old_password">Ancien mot de passe:</label>
                    <input type="password" id="old_password" name="old_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">Nouveau mot de passe:</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmer:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn--primary">Mettre à jour</button>
            </form>
        </div>
    </div>

    <!-- Carte : Conseils de sécurité -->
    <div class="dashboard-card">
        <div class="card-header">
            <div class="card-icon card-icon--success">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
            </div>
            <div>
                <h3>Conseils de sécurité</h3>
                <p class="card-subtitle">Bonnes pratiques</p>
            </div>
        </div>
        <div class="card-body">
            <ul class="security-tips">
                <li>✓ Utilisez un mot de passe fort (12+ caractères)</li>
                <li>✓ Mélangez majuscules, minuscules, chiffres et symboles</li>
                <li>✓ Ne réutilisez pas vos mots de passe</li>
                <li>✓ Changez régulièrement votre mot de passe</li>
                <li>✓ Déconnectez-vous après chaque session</li>
            </ul>
        </div>
    </div>

    <!-- Carte : Sessions actives -->
    <div class="dashboard-card">
        <div class="card-header">
            <div class="card-icon card-icon--info">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <div>
                <h3>Sessions actives</h3>
                <p class="card-subtitle">Votre dernière connexion</p>
            </div>
        </div>
        <div class="card-body">
            <div class="info-row">
                <span class="info-label">Utilisateur:</span>
                <span class="info-value"><?php echo escape($_SESSION['admin_username']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Connecté depuis:</span>
                <span class="info-value"><?php echo date('d/m/Y à H:i:s'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Statut:</span>
                <span class="info-value" style="color: #22c55e;">● Actif</span>
            </div>
        </div>
    </div>

    <!-- Carte : Durée du token de session -->
    <div class="dashboard-card">
        <div class="card-header">
            <div class="card-icon card-icon--warning">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <div>
                <h3>Durée du token de session</h3>
                <p class="card-subtitle">Configuration de l'expiration</p>
            </div>
        </div>
        <div class="card-body">
            <form id="token-duration-form" method="POST">
                <div class="form-group">
                    <label for="token_duration">Durée d'expiration (minutes):</label>
                    <input type="number" id="token_duration" name="token_duration" min="5" max="1440" value="30" required>
                    <small style="color: var(--admin-text-muted); display: block; margin-top: 0.5rem;">
                        Recommandé : 30 minutes. Max : 1440 minutes (24h)
                    </small>
                </div>
                <button type="submit" class="btn btn--primary">Mettre à jour</button>
            </form>
        </div>
    </div>
</div>

<style>
.security-tips {
    list-style: none;
    padding: 0;
}

.security-tips li {
    padding: 10px 0;
    border-bottom: 1px solid var(--admin-border);
    color: var(--admin-text-muted);
}

.security-tips li:last-child {
    border-bottom: none;
}
</style>

<?php include 'includes/footer.php'; ?>
