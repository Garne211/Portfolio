#!/bin/bash

###############################################################################
# Installation manuelle PHP + Portfolio
###############################################################################

set -e

echo "======================================================================"
echo "Installation Portfolio - Méthode manuelle"
echo "======================================================================"

if [[ $EUID -ne 0 ]]; then
   echo "ERREUR: Exécutez avec sudo"
   exit 1
fi

echo "[1/8] Mise à jour système..."
apt-get update
apt-get upgrade -y

echo "[2/8] Installation des prérequis..."
apt-get install -y lsb-release ca-certificates apt-transport-https software-properties-common wget curl gnupg2

echo "[3/8] Ajout du dépôt Sury pour PHP..."
wget -qO - https://packages.sury.org/php/apt.gpg | apt-key add -
echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/sury-php.list

echo "[4/8] Mise à jour après ajout dépôt..."
apt-get update

echo "[5/8] Installation PHP 8.2 et extensions..."
apt-get install -y \
    php8.2-fpm \
    php8.2-mysql \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-curl \
    php8.2-zip \
    php8.2-gd \
    php8.2-intl \
    php8.2-cli

echo "[6/8] Installation Nginx..."
apt-get install -y nginx

echo "[7/8] Installation MariaDB..."
apt-get install -y mariadb-server mariadb-client
systemctl enable mariadb
systemctl start mariadb

echo "[8/8] Installation Git et Certbot..."
apt-get install -y git certbot python3-certbot-nginx

echo ""
echo "======================================================================"
echo "✅ Installation des dépendances terminée !"
echo "======================================================================"
echo ""
echo "Maintenant configurons votre site..."
echo ""

read -p "Nom de la base de données [portfolio_db]: " DB_NAME
DB_NAME=${DB_NAME:-portfolio_db}

read -p "Utilisateur DB [portfolio_user]: " DB_USER
DB_USER=${DB_USER:-portfolio_user}

read -sp "Mot de passe DB: " DB_PASS
echo ""

mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

echo "✅ Base de données créée"

APP_DIR="/var/www/portfolio"

if [ -d "$APP_DIR" ]; then
    rm -rf "$APP_DIR"
fi

cp -r /tmp/Portfolio "$APP_DIR"

echo "✅ Fichiers copiés dans $APP_DIR"

chown -R www-data:www-data "$APP_DIR"
chmod -R 755 "$APP_DIR"

mkdir -p "$APP_DIR/uploads/"{avatars,cv,projects,languages}
mkdir -p "$APP_DIR/databases"
mkdir -p "$APP_DIR/logs"

chown -R www-data:www-data "$APP_DIR/uploads" "$APP_DIR/databases" "$APP_DIR/logs"
chmod -R 775 "$APP_DIR/uploads" "$APP_DIR/databases" "$APP_DIR/logs"

echo "✅ Permissions configurées"

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
    error_log("Erreur DB: " . \$e->getMessage());
    die("Erreur de connexion à la base de données");
}
EOF

chown www-data:www-data "$APP_DIR/includes/db.php"
chmod 600 "$APP_DIR/includes/db.php"

echo "✅ Configuration DB créée"

read -p "Nom de domaine (ex: portfolio.example.com ou IP): " DOMAIN_NAME

cat > /etc/nginx/sites-available/portfolio <<'EOF'
server {
    listen 80;
    listen [::]:80;
    server_name DOMAIN_PLACEHOLDER;
    
    root /var/www/portfolio/public;
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
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }
}
EOF

sed -i "s/DOMAIN_PLACEHOLDER/${DOMAIN_NAME}/g" /etc/nginx/sites-available/portfolio

ln -sf /etc/nginx/sites-available/portfolio /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

nginx -t && systemctl restart nginx

echo "✅ Nginx configuré"

cd "$APP_DIR"
sudo -u www-data php database/migrate.php

echo ""
echo "======================================================================"
echo "✅ INSTALLATION TERMINÉE !"
echo "======================================================================"
echo ""
echo "Votre site est accessible sur: http://${DOMAIN_NAME}"
echo ""
echo "Prochaines étapes:"
echo "1. Créer un compte admin:"
echo "   cd /var/www/portfolio"
echo "   sudo -u www-data php dev-tools/setup_admin.php"
echo ""
echo "2. Pour activer SSL (HTTPS):"
echo "   certbot --nginx -d ${DOMAIN_NAME}"
echo ""
echo "======================================================================"
