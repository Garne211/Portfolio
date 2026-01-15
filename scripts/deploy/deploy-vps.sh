#!/bin/bash

###############################################################################
# Script de déploiement pour Portfolio - VPS Debian
# Ce script configure automatiquement l'application sur un serveur Debian
###############################################################################

set -e  # Arrêter en cas d'erreur

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration par défaut
APP_NAME="Portfolio"
APP_DIR="/var/www/portfolio"
NGINX_AVAILABLE="/etc/nginx/sites-available"
NGINX_ENABLED="/etc/nginx/sites-enabled"
PHP_VERSION="8.1"  # Ajuster selon votre version PHP

# Fonction d'affichage
print_message() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

# Vérifier si l'utilisateur est root ou a les privilèges sudo
check_privileges() {
    if [[ $EUID -ne 0 ]]; then
       print_error "Ce script doit être exécuté avec les privilèges root (sudo)"
       exit 1
    fi
}

# Mettre à jour le système
update_system() {
    print_message "Mise à jour du système..."
    apt-get update
    apt-get upgrade -y
}

# Installer les dépendances nécessaires
install_dependencies() {
    print_message "Installation des dépendances..."
    
    # Nginx
    if ! command -v nginx &> /dev/null; then
        print_message "Installation de Nginx..."
        apt-get install -y nginx
    fi
    
    # PHP et extensions
    print_message "Installation de PHP et ses extensions..."
    apt-get install -y \
        php${PHP_VERSION}-fpm \
        php${PHP_VERSION}-mysql \
        php${PHP_VERSION}-mbstring \
        php${PHP_VERSION}-xml \
        php${PHP_VERSION}-curl \
        php${PHP_VERSION}-zip \
        php${PHP_VERSION}-gd \
        php${PHP_VERSION}-intl
    
    # MySQL/MariaDB
    if ! command -v mysql &> /dev/null; then
        print_message "Installation de MariaDB..."
        apt-get install -y mariadb-server mariadb-client
        systemctl enable mariadb
        systemctl start mariadb
    fi
    
    # Git
    if ! command -v git &> /dev/null; then
        print_message "Installation de Git..."
        apt-get install -y git
    fi
    
    # Certbot pour SSL (optionnel)
    print_message "Installation de Certbot pour SSL..."
    apt-get install -y certbot python3-certbot-nginx
}

# Créer la base de données
setup_database() {
    print_message "Configuration de la base de données..."
    
    read -p "Nom de la base de données [portfolio_db]: " DB_NAME
    DB_NAME=${DB_NAME:-portfolio_db}
    
    read -p "Utilisateur de la base de données [portfolio_user]: " DB_USER
    DB_USER=${DB_USER:-portfolio_user}
    
    read -sp "Mot de passe de la base de données: " DB_PASS
    echo
    
    mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
    mysql -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"
    mysql -e "FLUSH PRIVILEGES;"
    
    print_message "Base de données créée avec succès"
}

# Cloner ou mettre à jour le dépôt
setup_application() {
    print_message "Configuration de l'application..."
    
    read -p "URL du dépôt Git: " GIT_REPO
    
    if [ -d "$APP_DIR" ]; then
        print_warning "Le répertoire existe déjà. Mise à jour..."
        cd "$APP_DIR"
        git pull
    else
        print_message "Clonage du dépôt..."
        git clone "$GIT_REPO" "$APP_DIR"
    fi
    
    # Configurer les permissions
    print_message "Configuration des permissions..."
    chown -R www-data:www-data "$APP_DIR"
    chmod -R 755 "$APP_DIR"
    
    # Créer les dossiers nécessaires avec les bonnes permissions
    mkdir -p "$APP_DIR/uploads/"{avatars,cv,projects,languages}
    mkdir -p "$APP_DIR/databases"
    mkdir -p "$APP_DIR/logs"
    
    chown -R www-data:www-data "$APP_DIR/uploads"
    chown -R www-data:www-data "$APP_DIR/databases"
    chown -R www-data:www-data "$APP_DIR/logs"
    
    chmod -R 775 "$APP_DIR/uploads"
    chmod -R 775 "$APP_DIR/databases"
    chmod -R 775 "$APP_DIR/logs"
}

# Créer le fichier de configuration
create_config() {
    print_message "Création du fichier de configuration..."
    
    cat > "$APP_DIR/config/config.php" <<EOF
<?php
/**
 * Configuration pour l'environnement de production
 */

// Définir le répertoire racine
define('ROOT_DIR', dirname(__DIR__));
define('INCLUDES_DIR', ROOT_DIR . '/includes');
define('ADMIN_DIR', ROOT_DIR . '/admin');
define('API_DIR', ROOT_DIR . '/api');
define('PUBLIC_DIR', ROOT_DIR . '/public');
define('UPLOADS_DIR', ROOT_DIR . '/uploads');
define('DATABASE_DIR', ROOT_DIR . '/database');

// Fuseau horaire
date_default_timezone_set('Europe/Paris');

// Désactiver l'affichage des erreurs en production
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', ROOT_DIR . '/logs/php-error.log');

// Headers de sécurité
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}
EOF
    
    chown www-data:www-data "$APP_DIR/config/config.php"
    chmod 600 "$APP_DIR/config/config.php"
}

# Configurer Nginx
setup_nginx() {
    print_message "Configuration de Nginx..."
    
    read -p "Nom de domaine (ex: portfolio.example.com): " DOMAIN_NAME
    
    cat > "${NGINX_AVAILABLE}/portfolio" <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN_NAME};
    
    root ${APP_DIR}/public;
    index index.php index.html;
    
    # Logs
    access_log /var/log/nginx/portfolio_access.log;
    error_log /var/log/nginx/portfolio_error.log;
    
    # Sécurité - Cacher la version de Nginx
    server_tokens off;
    
    # Taille maximale des uploads
    client_max_body_size 20M;
    
    # Bloquer l'accès aux fichiers sensibles
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    location ~ /(config|dev-tools|database|databases|logs|scripts) {
        deny all;
        return 404;
    }
    
    # Servir les fichiers statiques
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # Route principale
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    
    # API routes
    location /api/ {
        try_files \$uri \$uri/ /api/index.php?\$query_string;
    }
    
    # Admin routes
    location /admin/ {
        try_files \$uri \$uri/ =404;
    }
    
    # PHP-FPM
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php${PHP_VERSION}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
        
        # Sécurité PHP
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 300;
    }
}
EOF
    
    # Activer le site
    ln -sf "${NGINX_AVAILABLE}/portfolio" "${NGINX_ENABLED}/portfolio"
    
    # Tester la configuration
    nginx -t
    
    # Redémarrer Nginx
    systemctl restart nginx
    systemctl enable nginx
    
    print_message "Nginx configuré avec succès"
}

# Configurer SSL avec Let's Encrypt
setup_ssl() {
    print_message "Configuration SSL..."
    
    read -p "Voulez-vous configurer SSL avec Let's Encrypt? (o/n): " SETUP_SSL
    
    if [[ "$SETUP_SSL" == "o" || "$SETUP_SSL" == "O" ]]; then
        read -p "Email pour Let's Encrypt: " SSL_EMAIL
        certbot --nginx -d "$DOMAIN_NAME" --non-interactive --agree-tos -m "$SSL_EMAIL"
        print_message "SSL configuré avec succès"
    fi
}

# Initialiser la base de données
init_database() {
    print_message "Initialisation de la base de données..."
    
    read -p "Voulez-vous exécuter les migrations de base de données? (o/n): " RUN_MIGRATIONS
    
    if [[ "$RUN_MIGRATIONS" == "o" || "$RUN_MIGRATIONS" == "O" ]]; then
        cd "$APP_DIR"
        php database/migrate.php
        print_message "Migrations exécutées avec succès"
    fi
}

# Configurer les tâches cron (si nécessaire)
setup_cron() {
    print_message "Configuration des tâches cron..."
    
    # Exemple: sauvegarde quotidienne
    (crontab -l 2>/dev/null; echo "0 2 * * * /usr/bin/mysqldump -u ${DB_USER} -p${DB_PASS} ${DB_NAME} > ${APP_DIR}/databases/backup-\$(date +\%Y\%m\%d).sql") | crontab -
    
    print_message "Tâches cron configurées"
}

# Afficher les informations finales
show_final_info() {
    echo ""
    echo "======================================================================"
    echo -e "${GREEN}Déploiement terminé avec succès!${NC}"
    echo "======================================================================"
    echo ""
    echo "Informations importantes:"
    echo "- Répertoire de l'application: $APP_DIR"
    echo "- URL: http://${DOMAIN_NAME}"
    echo "- Base de données: ${DB_NAME}"
    echo "- Utilisateur DB: ${DB_USER}"
    echo ""
    echo "Prochaines étapes:"
    echo "1. Mettez à jour le fichier includes/db.php avec vos identifiants DB"
    echo "2. Créez un compte administrateur avec dev-tools/setup_admin.php"
    echo "3. Vérifiez les permissions des dossiers uploads/"
    echo "4. Configurez les sauvegardes régulières"
    echo ""
    echo "======================================================================"
}

# Menu principal
main() {
    clear
    echo "======================================================================"
    echo "          Script de déploiement - Portfolio VPS Debian"
    echo "======================================================================"
    echo ""
    
    check_privileges
    
    read -p "Voulez-vous continuer avec l'installation complète? (o/n): " CONTINUE
    if [[ "$CONTINUE" != "o" && "$CONTINUE" != "O" ]]; then
        print_warning "Installation annulée"
        exit 0
    fi
    
    update_system
    install_dependencies
    setup_database
    setup_application
    create_config
    setup_nginx
    setup_ssl
    init_database
    setup_cron
    show_final_info
}

# Exécuter le script principal
main
