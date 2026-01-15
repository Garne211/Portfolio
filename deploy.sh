#!/bin/bash

# =====================================
# Script de Déploiement Portfolio
# Pour VPS OVH ou similaire
# =====================================

set -e

echo "🚀 Déploiement Portfolio"
echo "========================"

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Configuration
DOMAIN=${1:-portfolio.local}
ADMIN_EMAIL=${2:-admin@portfolio.local}
VPS_IP=${3:-0.0.0.0}

echo "Domain: $DOMAIN"
echo "Admin Email: $ADMIN_EMAIL"
echo "VPS IP: $VPS_IP"
echo ""

# 1. Créer le dossier
echo -e "${YELLOW}[1/8]${NC} Création du dossier..."
mkdir -p /var/www/$DOMAIN
cd /var/www/$DOMAIN

# 2. Définir les permissions
echo -e "${YELLOW}[2/8]${NC} Configuration des permissions..."
chown -R www-data:www-data .
chmod -R 755 .
chmod -R 777 uploads/
chmod 600 .env 2>/dev/null || true

# 3. Créer le fichier .env
echo -e "${YELLOW}[3/8]${NC} Configuration .env..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "✓ Fichier .env créé"
else
    echo "ℹ️ Fichier .env existe déjà"
fi

# 4. Installer les dépendances (si composer)
echo -e "${YELLOW}[4/8]${NC} Vérification des dépendances..."
if [ -f composer.json ]; then
    composer install --no-dev --optimize-autoloader
    echo "✓ Composer packages installés"
else
    echo "ℹ️ Pas de composer.json"
fi

# 5. Exécuter les migrations
echo -e "${YELLOW}[5/8]${NC} Exécution des migrations..."
php database/migrate.php

# 6. Configurer Apache
echo -e "${YELLOW}[6/8]${NC} Configuration Apache..."
cat > /etc/apache2/sites-available/$DOMAIN.conf << EOF
<VirtualHost *:80>
    ServerName $DOMAIN
    ServerAlias www.$DOMAIN
    DocumentRoot /var/www/$DOMAIN/public
    
    <Directory /var/www/$DOMAIN/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    <Directory /var/www/$DOMAIN>
        Options -Indexes
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/$DOMAIN-error.log
    CustomLog \${APACHE_LOG_DIR}/$DOMAIN-access.log combined
</VirtualHost>
EOF

a2ensite $DOMAIN.conf
a2enmod rewrite
apache2ctl configtest && systemctl reload apache2
echo "✓ VirtualHost Apache configuré"

# 7. SSL Let's Encrypt
echo -e "${YELLOW}[7/8]${NC} Configuration SSL..."
if command -v certbot &> /dev/null; then
    certbot --apache -d $DOMAIN -d www.$DOMAIN --non-interactive --agree-tos -m $ADMIN_EMAIL || true
    echo "✓ Certificate SSL configuré"
else
    echo "⚠️ Certbot non installé - HTTPS non configuré"
fi

# 8. Résumé
echo ""
echo -e "${GREEN}=====================================
✅ Déploiement Complété!
=====================================${NC}"
echo ""
echo "🌐 Accédez à votre portfolio:"
echo "   https://$DOMAIN"
echo ""
echo "🔐 Panel Admin:"
echo "   https://$DOMAIN/admin/"
echo ""
echo "📝 Prochaines étapes:"
echo "   1. Vérifier les permissions des uploads/"
echo "   2. Configurer le fichier .env"
echo "   3. Créer le compte administrateur"
echo "   4. Vérifier les logs Apache"
echo ""
echo "📁 Fichiers importants:"
echo "   .env              - Configuration (à sécuriser)"
echo "   database/         - Migrations et dumps"
echo "   uploads/          - Fichiers uploadés"
echo "   admin/            - Panel d'administration"
echo ""

exit 0
