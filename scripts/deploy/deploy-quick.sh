#!/bin/bash

###############################################################################
# Script de déploiement simplifié pour Portfolio - VPS Debian
# Détecte automatiquement la version PHP disponible
###############################################################################

set -e

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo "======================================================================"
echo "          Installation Portfolio - VPS Debian"
echo "======================================================================"
echo ""

# Vérifier les privilèges root
if [[ $EUID -ne 0 ]]; then
   echo -e "${RED}[ERREUR]${NC} Ce script doit être exécuté avec sudo"
   exit 1
fi

echo -e "${GREEN}[1/7]${NC} Mise à jour du système..."
apt-get update
apt-get upgrade -y

echo -e "${GREEN}[2/7]${NC} Installation de Nginx..."
apt-get install -y nginx

echo -e "${GREEN}[3/7]${NC} Détection et installation de PHP..."

# Vérifier quelle version de PHP est disponible
if apt-cache show php8.2-fpm &>/dev/null; then
    PHP_VERSION="8.2"
elif apt-cache show php8.1-fpm &>/dev/null; then
    PHP_VERSION="8.1"
elif apt-cache show php8.0-fpm &>/dev/null; then
    PHP_VERSION="8.0"
elif apt-cache show php7.4-fpm &>/dev/null; then
    PHP_VERSION="7.4"
else
    echo -e "${YELLOW}[INFO]${NC} Ajout du dépôt Sury pour PHP..."
    apt-get install -y lsb-release ca-certificates apt-transport-https software-properties-common curl
    curl -sSL https://packages.sury.org/php/apt.gpg -o /etc/apt/trusted.gpg.d/php.gpg
    echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list
    apt-get update
    PHP_VERSION="8.2"
fi

echo -e "${YELLOW}[INFO]${NC} Installation de PHP ${PHP_VERSION}..."

apt-get install -y \
    php${PHP_VERSION}-fpm \
    php${PHP_VERSION}-mysql \
    php${PHP_VERSION}-mbstring \
    php${PHP_VERSION}-xml \
    php${PHP_VERSION}-curl \
    php${PHP_VERSION}-zip \
    php${PHP_VERSION}-gd \
    php${PHP_VERSION}-intl

echo -e "${GREEN}[4/7]${NC} Installation de MariaDB..."
apt-get install -y mariadb-server mariadb-client
systemctl enable mariadb
systemctl start mariadb

echo -e "${GREEN}[5/7]${NC} Installation de Git et Certbot..."
apt-get install -y git certbot python3-certbot-nginx

echo -e "${GREEN}[6/7]${NC} Configuration de la base de données..."
read -p "Nom de la base de données [portfolio_db]: " DB_NAME
DB_NAME=${DB_NAME:-portfolio_db}

read -p "Utilisateur de la base de données [portfolio_user]: " DB_USER
DB_USER=${DB_USER:-portfolio_user}

read -sp "Mot de passe de la base de données: " DB_PASS
echo ""

mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

echo -e "${YELLOW}[INFO]${NC} Base de données créée avec succès"

echo -e "${GREEN}[7/7]${NC} Clonage du projet..."
read -p "URL du dépôt Git [https://github.com/Garne211/Portfolio.git]: " GIT_REPO
GIT_REPO=${GIT_REPO:-https://github.com/Garne211/Portfolio.git}

APP_DIR="/var/www/portfolio"

if [ -d "$APP_DIR" ]; then
    echo -e "${YELLOW}[WARNING]${NC} Le répertoire existe déjà. Mise à jour..."
    cd "$APP_DIR"
    git pull
else
    git clone "$GIT_REPO" "$APP_DIR"
fi

echo ""
echo -e "${GREEN}[INFO]${NC} Configuration des permissions..."
chown -R www-data:www-data "$APP_DIR"
chmod -R 755 "$APP_DIR"

mkdir -p "$APP_DIR/uploads/"{avatars,cv,projects,languages}
mkdir -p "$APP_DIR/databases"
mkdir -p "$APP_DIR/logs"

chown -R www-data:www-data "$APP_DIR/uploads" "$APP_DIR/databases" "$APP_DIR/logs"
chmod -R 775 "$APP_DIR/uploads" "$APP_DIR/databases" "$APP_DIR/logs"

echo ""
echo -e "${GREEN}[INFO]${NC} Création du fichier de configuration..."
cat > "$APP_DIR/includes/db.php" <<EOF
<?php
\$host = 'localhost';
\$dbname = '${DB_NAME}';
\$username = '${DB_USER}';
\$password = '${DB_PASS}';

try {
    \$pdo = new PDO(
        "mysql:host=\$host;dbname=\$dbname;charset=utf8mb4",
        \$username,
        \$password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException \$e) {
    error_log("Erreur de connexion à la base de données: " . \$e->getMessage());
    die("Erreur de connexion à la base de données");
}
EOF

chown www-data:www-data "$APP_DIR/includes/db.php"
chmod 600 "$APP_DIR/includes/db.php"

echo ""
echo -e "${GREEN}[INFO]${NC} Configuration de Nginx..."
read -p "Nom de domaine (ex: portfolio.example.com): " DOMAIN_NAME

cat > /etc/nginx/sites-available/portfolio <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN_NAME};
    
    root ${APP_DIR}/public;
    index index.php index.html;
    
    access_log /var/log/nginx/portfolio_access.log;
    error_log /var/log/nginx/portfolio_error.log;
    
    server_tokens off;
    client_max_body_size 20M;
    
    location ~ /\. {
        deny all;
    }
    
    location ~ /(config|dev-tools|database|databases|logs|scripts) {
        deny all;
        return 404;
    }
    
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    
    location /api/ {
        try_files \$uri \$uri/ =404;
    }
    
    location /admin/ {
        try_files \$uri \$uri/ =404;
    }
    
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php${PHP_VERSION}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 300;
    }
}
EOF

ln -sf /etc/nginx/sites-available/portfolio /etc/nginx/sites-enabled/
nginx -t
systemctl restart nginx
systemctl enable nginx

echo ""
echo -e "${GREEN}[INFO]${NC} Exécution des migrations..."
cd "$APP_DIR"
sudo -u www-data php database/migrate.php

echo ""
read -p "Voulez-vous configurer SSL avec Let's Encrypt? (o/n): " SETUP_SSL
if [[ "$SETUP_SSL" == "o" || "$SETUP_SSL" == "O" ]]; then
    read -p "Email pour Let's Encrypt: " SSL_EMAIL
    certbot --nginx -d "$DOMAIN_NAME" --non-interactive --agree-tos -m "$SSL_EMAIL"
fi

echo ""
echo "======================================================================"
echo -e "${GREEN}✅ Installation terminée avec succès!${NC}"
echo "======================================================================"
echo ""
echo "Informations importantes:"
echo "- PHP Version: ${PHP_VERSION}"
echo "- Répertoire: $APP_DIR"
echo "- URL: http://${DOMAIN_NAME}"
echo "- Base de données: ${DB_NAME}"
echo ""
echo "Prochaines étapes:"
echo "1. Créez un compte admin:"
echo "   cd $APP_DIR && sudo -u www-data php dev-tools/setup_admin.php"
echo ""
echo "2. Pointez votre DNS vers ce serveur"
echo ""
echo "3. Si vous n'avez pas configuré SSL, faites-le maintenant:"
echo "   certbot --nginx -d ${DOMAIN_NAME}"
echo ""
echo "======================================================================"
