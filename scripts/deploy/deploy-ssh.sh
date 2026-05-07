#!/bin/bash
###############################################################################
# Script de déploiement SSH pour Portfolio
# Préserve les données existantes (BD, uploads, config)
###############################################################################

set -e  # Arrêt en cas d'erreur

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration - VPS Hery LEBEHOT
SERVER_USER="debian"
SERVER_HOST="137.74.41.200"
SERVER_PORT="22"
REMOTE_PATH="/var/www/portfolio"
BACKUP_DIR="/var/www/backups/portfolio"

echo -e "${GREEN}╔════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║   Déploiement SSH Portfolio - Mode Sécurisé       ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════╝${NC}"
echo ""

# Vérification des paramètres
if [ "$SERVER_USER" == "votre-user" ] || [ "$SERVER_HOST" == "votre-serveur.com" ]; then
    echo -e "${RED}❌ ERREUR: Vous devez configurer SERVER_USER et SERVER_HOST${NC}"
    echo -e "${YELLOW}Éditez le fichier: scripts/deploy/deploy-ssh.sh${NC}"
    exit 1
fi

echo -e "${YELLOW}📋 Configuration:${NC}"
echo "   Serveur: $SERVER_USER@$SERVER_HOST:$SERVER_PORT"
echo "   Destination: $REMOTE_PATH"
echo ""

# Confirmation
read -p "Voulez-vous continuer le déploiement ? (y/N) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${YELLOW}❌ Déploiement annulé${NC}"
    exit 0
fi

echo ""
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}1/6 - Vérification de la connexion SSH${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

if ! ssh -p $SERVER_PORT $SERVER_USER@$SERVER_HOST "echo 'Connexion OK'" > /dev/null 2>&1; then
    echo -e "${RED}❌ Impossible de se connecter au serveur${NC}"
    echo -e "${YELLOW}Vérifiez vos identifiants SSH${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Connexion SSH établie${NC}"
echo ""

echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}2/6 - Sauvegarde des données existantes${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

BACKUP_NAME="portfolio_backup_$(date +%Y%m%d_%H%M%S)"

ssh -p $SERVER_PORT $SERVER_USER@$SERVER_HOST << 'ENDSSH'
set -e

REMOTE_PATH="/var/www/portfolio"
BACKUP_DIR="/var/www/backups/portfolio"
BACKUP_NAME="portfolio_backup_$(date +%Y%m%d_%H%M%S)"

# Créer le dossier de backup si nécessaire
mkdir -p $BACKUP_DIR

if [ -d "$REMOTE_PATH" ]; then
    echo "📦 Sauvegarde des données..."
    
    # Créer un dossier pour cette backup
    mkdir -p $BACKUP_DIR/$BACKUP_NAME
    
    # Sauvegarder les données importantes UNIQUEMENT
    if [ -d "$REMOTE_PATH/databases" ]; then
        cp -r $REMOTE_PATH/databases $BACKUP_DIR/$BACKUP_NAME/
        echo "  ✓ Base de données sauvegardée"
    fi
    
    if [ -d "$REMOTE_PATH/uploads" ]; then
        cp -r $REMOTE_PATH/uploads $BACKUP_DIR/$BACKUP_NAME/
        echo "  ✓ Fichiers uploadés sauvegardés"
    fi
    
    if [ -f "$REMOTE_PATH/config/config.php" ]; then
        mkdir -p $BACKUP_DIR/$BACKUP_NAME/config
        cp $REMOTE_PATH/config/config.php $BACKUP_DIR/$BACKUP_NAME/config/
        echo "  ✓ Configuration sauvegardée"
    fi
    
    echo "✓ Sauvegarde créée: $BACKUP_NAME"
else
    echo "ℹ️  Première installation - pas de sauvegarde nécessaire"
fi
ENDSSH

echo ""

echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}3/6 - Push du code vers le dépôt Git${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

# Vérifier si on a un remote configuré
if ! git remote get-url origin > /dev/null 2>&1; then
    echo -e "${YELLOW}⚠️  Aucun remote Git configuré${NC}"
    echo -e "${YELLOW}Voulez-vous configurer un remote GitHub maintenant ? (y/N)${NC}"
    read -p "> " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        read -p "URL du dépôt GitHub (ex: git@github.com:user/repo.git): " REPO_URL
        git remote add origin $REPO_URL
        echo -e "${GREEN}✓ Remote configuré${NC}"
    else
        echo -e "${YELLOW}⚠️  Déploiement sans push Git - continuons avec rsync${NC}"
    fi
fi

# Push si remote existe
if git remote get-url origin > /dev/null 2>&1; then
    echo "📤 Push vers le dépôt Git..."
    git push origin main
    echo -e "${GREEN}✓ Code pushé vers Git${NC}"
fi

echo ""

echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}4/6 - Synchronisation des fichiers vers le serveur${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

echo "📤 Transfert des fichiers via rsync..."

# Utiliser rsync pour synchroniser, en excluant les données sensibles
rsync -avz --progress \
    -e "ssh -p $SERVER_PORT" \
    --exclude='.git' \
    --exclude='config/config.php' \
    --exclude='databases/*' \
    --exclude='!databases/.gitkeep' \
    --exclude='uploads/avatars/*' \
    --exclude='!uploads/avatars/.gitkeep' \
    --exclude='uploads/cv/*' \
    --exclude='!uploads/cv/.gitkeep' \
    --exclude='uploads/projects/*' \
    --exclude='!uploads/projects/.gitkeep' \
    --exclude='uploads/languages/*' \
    --exclude='!uploads/languages/.gitkeep' \
    --exclude='*.log' \
    --exclude='.vscode' \
    --exclude='.idea' \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='dev-tools' \
    --exclude='tests' \
    --delete \
    ./ $SERVER_USER@$SERVER_HOST:$REMOTE_PATH/

echo -e "${GREEN}✓ Fichiers synchronisés${NC}"
echo ""

echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}5/6 - Configuration des permissions${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

ssh -p $SERVER_PORT $SERVER_USER@$SERVER_HOST << 'ENDSSH'
set -e

REMOTE_PATH="/var/www/portfolio"

cd $REMOTE_PATH

echo "🔐 Configuration des permissions..."

# Permissions des dossiers
find . -type d -exec chmod 755 {} \;
echo "  ✓ Permissions des dossiers: 755"

# Permissions des fichiers
find . -type f -exec chmod 644 {} \;
echo "  ✓ Permissions des fichiers: 644"

# Permissions spéciales pour les scripts
if [ -d "scripts/deploy" ]; then
    chmod +x scripts/deploy/*.sh
    echo "  ✓ Scripts de déploiement exécutables"
fi

# Permissions pour les dossiers d'écriture
chmod 775 databases/ uploads/ uploads/*/
echo "  ✓ Dossiers d'écriture: 775"

# Permissions pour la base de données
if [ -f "databases/portfolio.db" ]; then
    chmod 664 databases/portfolio.db
    echo "  ✓ Base de données: 664"
fi

# Propriétaire (adapter selon votre configuration)
# sudo chown -R www-data:www-data $REMOTE_PATH
# echo "  ✓ Propriétaire: www-data"

echo "✓ Permissions configurées"
ENDSSH

echo ""

echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}6/6 - Vérification post-déploiement${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

ssh -p $SERVER_PORT $SERVER_USER@$SERVER_HOST << 'ENDSSH'
set -e

REMOTE_PATH="/var/www/portfolio"

cd $REMOTE_PATH

echo "🔍 Vérification de l'installation..."

# Vérifier les fichiers critiques
CRITICAL_FILES=(
    "public/index.php"
    "public/index-fr.html"
    "api/profile.php"
    "admin/dashboard.php"
)

ALL_OK=true
for file in "${CRITICAL_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "  ✓ $file"
    else
        echo "  ❌ $file - MANQUANT"
        ALL_OK=false
    fi
done

# Vérifier les dossiers
CRITICAL_DIRS=(
    "databases"
    "uploads/avatars"
    "uploads/cv"
    "config"
)

for dir in "${CRITICAL_DIRS[@]}"; do
    if [ -d "$dir" ]; then
        echo "  ✓ $dir/"
    else
        echo "  ❌ $dir/ - MANQUANT"
        ALL_OK=false
    fi
done

if [ "$ALL_OK" = true ]; then
    echo "✓ Installation vérifiée avec succès"
else
    echo "⚠️  Certains fichiers manquent"
fi
ENDSSH

echo ""
echo -e "${GREEN}╔════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║        ✓ DÉPLOIEMENT TERMINÉ AVEC SUCCÈS          ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${YELLOW}📊 Résumé:${NC}"
echo "   • Code synchronisé vers le serveur"
echo "   • Données existantes préservées"
echo "   • Permissions configurées"
echo "   • Backup créé: $BACKUP_NAME"
echo ""
echo -e "${YELLOW}🔗 Prochaines étapes:${NC}"
echo "   1. Vérifiez votre site: http://$SERVER_HOST"
echo "   2. Testez toutes les fonctionnalités"
echo "   3. Vérifiez les logs si nécessaire"
echo ""
echo -e "${YELLOW}💡 Conseils:${NC}"
echo "   • Les backups sont dans: $BACKUP_DIR"
echo "   • Pour configurer Nginx: sudo nano /etc/nginx/sites-available/portfolio"
echo "   • Pour voir les logs: sudo tail -f /var/log/nginx/error.log"
echo ""
