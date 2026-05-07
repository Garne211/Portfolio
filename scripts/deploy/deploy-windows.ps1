# Script de déploiement Windows pour Portfolio VPS
# Préserve les données existantes (BD, uploads, config)

$ErrorActionPreference = "Stop"

# Configuration VPS
$SERVER_USER = "debian"
$SERVER_HOST = "137.74.41.200"
$SERVER_PORT = "22"
$REMOTE_PATH = "/var/www/portfolio"
$BACKUP_DIR = "/var/www/backups/portfolio"

Write-Host "`n========================================" -ForegroundColor Green
Write-Host "  DÉPLOIEMENT PORTFOLIO VPS" -ForegroundColor Green
Write-Host "========================================`n" -ForegroundColor Green

Write-Host "Configuration:" -ForegroundColor Yellow
Write-Host "  Serveur: $SERVER_USER@$SERVER_HOST" -ForegroundColor Cyan
Write-Host "  Destination: $REMOTE_PATH`n" -ForegroundColor Cyan

$confirm = Read-Host "Voulez-vous continuer le déploiement ? (O/N)"
if ($confirm -ne "O" -and $confirm -ne "o") {
    Write-Host "`n❌ Déploiement annulé" -ForegroundColor Yellow
    exit
}

Write-Host "`n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Green
Write-Host "1/5 - Vérification de la connexion SSH" -ForegroundColor Green
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor Green

try {
    $result = ssh -p $SERVER_PORT "$SERVER_USER@$SERVER_HOST" "echo 'OK'"
    if ($result -eq "OK") {
        Write-Host "✓ Connexion SSH établie`n" -ForegroundColor Green
    }
} catch {
    Write-Host "❌ Impossible de se connecter au serveur" -ForegroundColor Red
    exit 1
}

Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Green
Write-Host "2/5 - Sauvegarde des données existantes" -ForegroundColor Green
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor Green

$BACKUP_NAME = "portfolio_backup_$(Get-Date -Format 'yyyyMMdd_HHmmss')"

$backupScript = @"
set -e
REMOTE_PATH='$REMOTE_PATH'
BACKUP_DIR='$BACKUP_DIR'
BACKUP_NAME='$BACKUP_NAME'

mkdir -p `$BACKUP_DIR

if [ -d "`$REMOTE_PATH" ]; then
    echo "📦 Sauvegarde des données..."
    mkdir -p `$BACKUP_DIR/`$BACKUP_NAME
    
    if [ -d "`$REMOTE_PATH/databases" ]; then
        cp -r `$REMOTE_PATH/databases `$BACKUP_DIR/`$BACKUP_NAME/
        echo "  ✓ Base de données sauvegardée"
    fi
    
    if [ -d "`$REMOTE_PATH/uploads" ]; then
        cp -r `$REMOTE_PATH/uploads `$BACKUP_DIR/`$BACKUP_NAME/
        echo "  ✓ Fichiers uploadés sauvegardés"
    fi
    
    if [ -f "`$REMOTE_PATH/config/config.php" ]; then
        mkdir -p `$BACKUP_DIR/`$BACKUP_NAME/config
        cp `$REMOTE_PATH/config/config.php `$BACKUP_DIR/`$BACKUP_NAME/config/
        echo "  ✓ Configuration sauvegardée"
    fi
    
    echo "✓ Sauvegarde créée: `$BACKUP_NAME"
else
    echo "ℹ️  Première installation - pas de sauvegarde nécessaire"
fi
"@

ssh -p $SERVER_PORT "$SERVER_USER@$SERVER_HOST" $backupScript

Write-Host ""

Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Green
Write-Host "3/5 - Synchronisation des fichiers" -ForegroundColor Green
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor Green

Write-Host "📤 Transfert des fichiers via rsync...`n" -ForegroundColor Cyan

# Liste des exclusions
$excludes = @(
    ".git",
    "config/config.php",
    "databases/*",
    "!databases/.gitkeep",
    "uploads/avatars/*",
    "!uploads/avatars/.gitkeep",
    "uploads/cv/*",
    "!uploads/cv/.gitkeep",
    "uploads/projects/*",
    "!uploads/projects/.gitkeep",
    "uploads/languages/*",
    "!uploads/languages/.gitkeep",
    "*.log",
    ".vscode",
    ".idea",
    "node_modules",
    "vendor",
    "dev-tools",
    "tests"
)

# Construire la commande rsync
$excludeArgs = $excludes | ForEach-Object { "--exclude=$_" }

rsync -avz --progress `
    -e "ssh -p $SERVER_PORT" `
    $excludeArgs `
    --delete `
    ./ "$SERVER_USER@$SERVER_HOST`:$REMOTE_PATH/"

Write-Host "`n✓ Fichiers synchronisés`n" -ForegroundColor Green

Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Green
Write-Host "4/5 - Configuration des permissions" -ForegroundColor Green
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor Green

$permScript = @"
set -e
cd $REMOTE_PATH

echo "🔐 Configuration des permissions..."

find . -type d -exec chmod 755 {} \;
echo "  ✓ Permissions des dossiers: 755"

find . -type f -exec chmod 644 {} \;
echo "  ✓ Permissions des fichiers: 644"

if [ -d "scripts/deploy" ]; then
    chmod +x scripts/deploy/*.sh
    echo "  ✓ Scripts de déploiement exécutables"
fi

chmod 775 databases/ uploads/ uploads/*/
echo "  ✓ Dossiers d'écriture: 775"

if [ -f "databases/portfolio.db" ]; then
    chmod 664 databases/portfolio.db
    echo "  ✓ Base de données: 664"
fi

echo "✓ Permissions configurées"
"@

ssh -p $SERVER_PORT "$SERVER_USER@$SERVER_HOST" $permScript

Write-Host ""

Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Green
Write-Host "5/5 - Vérification post-déploiement" -ForegroundColor Green
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor Green

$verifyScript = @"
set -e
cd $REMOTE_PATH

echo "🔍 Vérification de l'installation..."

ALL_OK=true

# Vérifier les fichiers critiques
for file in public/index.php public/index-fr.html api/profile.php admin/dashboard.php; do
    if [ -f "`$file" ]; then
        echo "  ✓ `$file"
    else
        echo "  ❌ `$file - MANQUANT"
        ALL_OK=false
    fi
done

# Vérifier les dossiers
for dir in databases uploads/avatars uploads/cv config; do
    if [ -d "`$dir" ]; then
        echo "  ✓ `$dir/"
    else
        echo "  ❌ `$dir/ - MANQUANT"
        ALL_OK=false
    fi
done

if [ "`$ALL_OK" = true ]; then
    echo "✓ Installation vérifiée avec succès"
else
    echo "⚠️  Certains fichiers manquent"
fi
"@

ssh -p $SERVER_PORT "$SERVER_USER@$SERVER_HOST" $verifyScript

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "  ✓ DÉPLOIEMENT TERMINÉ AVEC SUCCÈS" -ForegroundColor Green
Write-Host "========================================`n" -ForegroundColor Green

Write-Host "📊 Résumé:" -ForegroundColor Yellow
Write-Host "  • Code synchronisé vers le serveur" -ForegroundColor Cyan
Write-Host "  • Données existantes préservées" -ForegroundColor Cyan
Write-Host "  • Permissions configurées" -ForegroundColor Cyan
Write-Host "  • Backup créé: $BACKUP_NAME`n" -ForegroundColor Cyan

Write-Host "🔗 Accès au site:" -ForegroundColor Yellow
Write-Host "  http://$SERVER_HOST`n" -ForegroundColor Cyan

Write-Host "💡 Backups disponibles dans:" -ForegroundColor Yellow
Write-Host "  $BACKUP_DIR`n" -ForegroundColor Cyan
