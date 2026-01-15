/**
 * Gestion automatique du renouvellement de session
 * Renouvelle la session avant qu'elle n'expire (3 minutes)
 */

class SessionManager {
    constructor() {
        this.refreshInterval = null;
        this.warningTimeout = null;
        this.sessionDuration = 3 * 60 * 1000; // 3 minutes en millisecondes
        this.warningTime = 1 * 60 * 1000; // Avertir 1 minute avant l'expiration
        this.refreshTime = 2 * 60 * 1000; // Renouveler à 2 minutes
    }

    /**
     * Démarrer la gestion de la session
     */
    start() {
        // Renouveler la session régulièrement (toutes les 2 minutes)
        this.refreshInterval = setInterval(() => {
            this.refreshSession();
        }, this.refreshTime);

        // Avertir avant l'expiration
        this.warningTimeout = setTimeout(() => {
            this.showWarning();
        }, this.warningTime);

        console.log('[SessionManager] Started - Session expires in 3 minutes');
    }

    /**
     * Arrêter la gestion de la session
     */
    stop() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }
        if (this.warningTimeout) {
            clearTimeout(this.warningTimeout);
        }
        console.log('[SessionManager] Stopped');
    }

    /**
     * Renouveler la session via API
     */
    async refreshSession() {
        try {
            const response = await fetch('/Portfolio/api/admin/refresh-session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                console.log('[SessionManager] Session renewed - New expiry:', data.new_expiry);
                
                // Redémarrer les timers
                this.stop();
                this.start();
                
                return true;
            } else if (response.status === 401) {
                // Session expirée, redirection vers login
                console.log('[SessionManager] Session expired, redirecting to login');
                this.redirectToLogin();
                return false;
            }
        } catch (error) {
            console.error('[SessionManager] Refresh failed:', error);
            return false;
        }
    }

    /**
     * Afficher un avertissement avant l'expiration
     */
    showWarning() {
        const message = 'Votre session expire dans 1 minute. Cliquez pour continuer.';
        
        // Créer une notification
        const notification = document.createElement('div');
        notification.className = 'session-warning';
        notification.innerHTML = `
            <div class="session-warning__content">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <span>${message}</span>
                <button class="session-warning__close">&times;</button>
            </div>
        `;
        
        document.body.appendChild(notification);

        // Renouveler au clic
        notification.addEventListener('click', (e) => {
            if (e.target.className !== 'session-warning__close') {
                this.refreshSession();
                notification.remove();
            }
        });

        // Fermer en cliquant sur X
        notification.querySelector('.session-warning__close').addEventListener('click', () => {
            notification.remove();
        });

        // Auto-supprimer après 30 secondes
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 30000);
    }

    /**
     * Rediriger vers la page de login
     */
    redirectToLogin() {
        this.stop();
        window.location.href = '/Portfolio/admin/login.php?redirect=true&reason=expired';
    }
}

// Initialiser le gestionnaire au chargement de la page
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        const sessionManager = new SessionManager();
        sessionManager.start();
    });
} else {
    const sessionManager = new SessionManager();
    sessionManager.start();
}
