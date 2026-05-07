# ============================================================
# Script de deploiement local - WAMP Server
# Copie les fichiers du projet vers un dossier propre WAMP
# et genere un config.php adapte a l'environnement local.
# ============================================================

$ErrorActionPreference = "Stop"

# ── Configuration ───────────────────────────────────────────
$SOURCE      = "C:\wamp64\www\Portfolio"
$DEST        = "C:\wamp64\www\portfolio-local"
$WAMP_URL    = "http://localhost/portfolio-local"
$BACKUP_DIR  = "C:\wamp64\www\_backups\portfolio-local"

# Dossiers et fichiers a exclure du deploiement
$EXCLUDES = @(
    ".git",
    ".vscode",
    ".idea",
    "tests",
    "dev-tools",
    "node_modules",
    "vendor",
    "scripts",
    "docs",
    "*.log",
    "config\config.php"   # regenere par ce script
)

# ── Banniere ────────────────────────────────────────────────
Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "   DEPLOIEMENT PORTFOLIO - WAMP LOCAL" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "  Source      : $SOURCE" -ForegroundColor Yellow
Write-Host "  Destination : $DEST" -ForegroundColor Yellow
Write-Host "  URL locale  : $WAMP_URL" -ForegroundColor Yellow
Write-Host ""

$confirm = Read-Host "Continuer le deploiement ? (O/N)"
if ($confirm -notin @("O","o")) {
    Write-Host "`n Deploiement annule." -ForegroundColor Yellow
    exit 0
}

# ── 1. Verification WAMP ────────────────────────────────────
Write-Host "`n[1/5] Verification de WAMP..." -ForegroundColor Green

if (-not (Test-Path "C:\wamp64\www")) {
    Write-Host " WAMP introuvable dans C:\wamp64\www" -ForegroundColor Red
    exit 1
}
Write-Host "  OK - WAMP detecte" -ForegroundColor Green

# ── 2. Backup de l'existant ─────────────────────────────────
Write-Host "`n[2/5] Sauvegarde des donnees existantes..." -ForegroundColor Green

if (Test-Path $DEST) {
    $stamp = Get-Date -Format "yyyyMMdd_HHmmss"
    $backupPath = "$BACKUP_DIR\backup_$stamp"
    New-Item -ItemType Directory -Path $backupPath -Force | Out-Null

    # Sauvegarder uniquement les donnees importantes
    foreach ($dir in @("databases", "uploads")) {
        $src = "$DEST\$dir"
        if (Test-Path $src) {
            Copy-Item $src "$backupPath\$dir" -Recurse -Force
            Write-Host "  Sauvegarde : $dir" -ForegroundColor Gray
        }
    }
    if (Test-Path "$DEST\config\config.php") {
        New-Item -ItemType Directory -Path "$backupPath\config" -Force | Out-Null
        Copy-Item "$DEST\config\config.php" "$backupPath\config\config.php" -Force
        Write-Host "  Sauvegarde : config.php" -ForegroundColor Gray
    }
    Write-Host "  OK - Backup cree : backup_$stamp" -ForegroundColor Green
} else {
    Write-Host "  Premiere installation - pas de sauvegarde" -ForegroundColor Gray
}

# ── 3. Copie des fichiers ────────────────────────────────────
Write-Host "`n[3/5] Copie des fichiers..." -ForegroundColor Green

# Creer la destination
New-Item -ItemType Directory -Path $DEST -Force | Out-Null

# Construire la liste d'exclusion pour robocopy
$robocopyExcludes = "/XD .git .vscode .idea tests dev-tools node_modules vendor scripts docs /XF *.log config.php"

$robocopyArgs = @(
    $SOURCE, $DEST,
    "/MIR",           # miroir (supprime les fichiers obsoletes)
    "/NP",            # pas de progression en %
    "/NFL",           # pas de liste de fichiers
    "/NDL",           # pas de liste de dossiers
    "/XD", ".git", ".vscode", ".idea", "tests", "dev-tools", "node_modules", "vendor", "scripts", "docs",
    "/XF", "*.log", "config.php"
)

& robocopy @robocopyArgs | Out-Null

# robocopy retourne 1 si des fichiers ont ete copies (succes), pas une erreur
if ($LASTEXITCODE -le 3) {
    Write-Host "  OK - Fichiers synchronises" -ForegroundColor Green
} else {
    Write-Host "  Erreur robocopy (code $LASTEXITCODE)" -ForegroundColor Red
    exit 1
}

# ── 4. Creation des dossiers requis ─────────────────────────
Write-Host "`n[4/5] Creation de la structure..." -ForegroundColor Green

$dirs = @(
    "$DEST\databases",
    "$DEST\uploads\avatars",
    "$DEST\uploads\cv",
    "$DEST\uploads\projects",
    "$DEST\uploads\languages",
    "$DEST\logs",
    "$DEST\config"
)
foreach ($d in $dirs) {
    New-Item -ItemType Directory -Path $d -Force | Out-Null
}
Write-Host "  OK - Dossiers crees" -ForegroundColor Green

# ── 5. Generation de config.php ─────────────────────────────
Write-Host "`n[5/5] Generation de config.php (environnement WAMP)..." -ForegroundColor Green

$configContent = @"
<?php
// config.php - Environnement WAMP local
// Genere automatiquement par deploy-wamp.ps1

define('ROOT_DIR',    dirname(__DIR__));
define('INCLUDES_DIR', ROOT_DIR . '/includes');
define('ADMIN_DIR',   ROOT_DIR . '/admin');
define('API_DIR',     ROOT_DIR . '/api');
define('PUBLIC_DIR',  ROOT_DIR . '/public');
define('UPLOADS_DIR', ROOT_DIR . '/uploads');
define('DATABASE_DIR', ROOT_DIR . '/databases');

define('APP_ENV', 'development');
define('BASE_URL', '$WAMP_URL');
define('APP_NAME', 'Portfolio');
define('APP_VERSION', '1.0.0');
define('CONTACT_EMAIL', 'herylebehot1@gmail.com');

date_default_timezone_set('Europe/Paris');

// Affichage des erreurs en dev
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Session
ini_set('session.name', 'PORTFOLIO_SESSION');
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_path', '/');

// Uploads
ini_set('upload_max_filesize', '20M');
ini_set('post_max_size', '20M');
ini_set('max_execution_time', '300');
ini_set('memory_limit', '256M');

// Constantes uploads
define('ALLOWED_AVATAR_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('MAX_AVATAR_SIZE', 5 * 1024 * 1024);
define('ALLOWED_CV_EXTENSIONS', ['pdf', 'doc', 'docx']);
define('MAX_CV_SIZE', 10 * 1024 * 1024);
define('ALLOWED_PROJECT_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('MAX_PROJECT_SIZE', 5 * 1024 * 1024);

// Securite
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_BLOCK_DURATION', 900);
define('SESSION_TIMEOUT', 1800);
define('SESSION_WARNING_TIME', 300);
define('CSRF_TOKEN_EXPIRE', 7200);

// Base de donnees
define('DB_TABLE_PREFIX', '');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATION', 'utf8mb4_unicode_ci');

// Cache desactive en dev
define('ENABLE_CACHE', false);
define('CACHE_DURATION', 3600);

// Debug actif en dev
define('DEBUG_SQL', true);
define('DEBUG_PERFORMANCE', true);
define('ENABLE_CUSTOM_LOGS', true);
define('LOG_LEVEL', 'debug');

// Email (dev : php mail natif)
define('EMAIL_METHOD', 'mail');
define('FROM_EMAIL', 'noreply@localhost');
define('FROM_NAME', 'Portfolio');

// Pagination
define('ADMIN_ITEMS_PER_PAGE', 20);
"@

$configContent | Out-File -FilePath "$DEST\config\config.php" -Encoding utf8
Write-Host "  OK - config.php genere" -ForegroundColor Green

# ── Recapitulatif ────────────────────────────────────────────
Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "   DEPLOIEMENT TERMINE AVEC SUCCES" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "  Site accessible sur : $WAMP_URL" -ForegroundColor Yellow
Write-Host "  Dossier deploye     : $DEST" -ForegroundColor Yellow
if (Test-Path $BACKUP_DIR) {
    Write-Host "  Backups disponibles : $BACKUP_DIR" -ForegroundColor Yellow
}
Write-Host ""
Write-Host "  Prochaines etapes :" -ForegroundColor Cyan
Write-Host "   1. Ouvrir $WAMP_URL dans le navigateur" -ForegroundColor White
Write-Host "   2. Verifier que PHP et Apache sont actifs dans WAMP" -ForegroundColor White
Write-Host "   3. Si premier lancement, initialiser la BD via l'admin" -ForegroundColor White
Write-Host ""
