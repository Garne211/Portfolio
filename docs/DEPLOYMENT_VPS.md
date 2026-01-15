# Guide de Déploiement VPS - Portfolio

## 📋 Table des matières
1. [Prérequis](#prérequis)
2. [Préparation du VPS](#préparation-du-vps)
3. [Déploiement automatique](#déploiement-automatique)
4. [Déploiement manuel](#déploiement-manuel)
5. [Configuration post-déploiement](#configuration-post-déploiement)
6. [Maintenance](#maintenance)
7. [Dépannage](#dépannage)

---

## 🔧 Prérequis

### Sur votre VPS Debian
- Debian 10, 11 ou 12
- Accès root ou sudo
- Au moins 1 GB de RAM
- 10 GB d'espace disque
- Connexion Internet

### Logiciels requis (installés automatiquement)
- Nginx
- PHP 8.1+ (php-fpm)
- MariaDB/MySQL
- Git
- Certbot (pour SSL)

### Sur votre machine locale
- Git installé
- Accès SSH au VPS

---

## 🚀 Préparation du VPS

### 1. Connexion SSH au VPS

```bash
ssh root@votre-ip-vps
# ou avec un utilisateur sudo
ssh votre-utilisateur@votre-ip-vps
```

### 2. Mise à jour du système

```bash
sudo apt update
sudo apt upgrade -y
```

### 3. Configuration du pare-feu (recommandé)

```bash
# Installer UFW
sudo apt install ufw -y

# Autoriser SSH, HTTP et HTTPS
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Activer le pare-feu
sudo ufw enable
sudo ufw status
```

---

## 🤖 Déploiement automatique

### Méthode 1 : Script de déploiement complet

1. **Cloner le dépôt sur le VPS**

```bash
cd /tmp
git clone https://github.com/votre-username/portfolio.git
cd portfolio
```

2. **Exécuter le script de déploiement**

```bash
sudo chmod +x scripts/deploy/deploy-vps.sh
sudo ./scripts/deploy/deploy-vps.sh
```

3. **Suivre les instructions**

Le script vous demandera :
- Nom de la base de données
- Utilisateur de la base de données
- Mot de passe de la base de données
- URL du dépôt Git
- Nom de domaine
- Email pour SSL (optionnel)

---

## 🔨 Déploiement manuel

Si vous préférez configurer manuellement :

### 1. Installer les dépendances

```bash
# Nginx
sudo apt install nginx -y

# PHP et extensions
sudo apt install php8.1-fpm php8.1-mysql php8.1-mbstring \
    php8.1-xml php8.1-curl php8.1-zip php8.1-gd php8.1-intl -y

# MariaDB
sudo apt install mariadb-server mariadb-client -y

# Git
sudo apt install git -y

# Certbot
sudo apt install certbot python3-certbot-nginx -y
```

### 2. Sécuriser MySQL

```bash
sudo mysql_secure_installation
```

Répondez aux questions :
- Set root password? **Y**
- Remove anonymous users? **Y**
- Disallow root login remotely? **Y**
- Remove test database? **Y**
- Reload privilege tables? **Y**

### 3. Créer la base de données

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE portfolio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'portfolio_user'@'localhost' IDENTIFIED BY 'votre_mot_de_passe_fort';
GRANT ALL PRIVILEGES ON portfolio_db.* TO 'portfolio_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 4. Cloner le projet

```bash
sudo mkdir -p /var/www
cd /var/www
sudo git clone https://github.com/votre-username/portfolio.git portfolio
```

### 5. Configurer les permissions

```bash
sudo chown -R www-data:www-data /var/www/portfolio
sudo chmod -R 755 /var/www/portfolio

# Permissions spéciales pour les dossiers d'upload
sudo mkdir -p /var/www/portfolio/uploads/{avatars,cv,projects,languages}
sudo mkdir -p /var/www/portfolio/databases
sudo mkdir -p /var/www/portfolio/logs

sudo chown -R www-data:www-data /var/www/portfolio/uploads
sudo chown -R www-data:www-data /var/www/portfolio/databases
sudo chown -R www-data:www-data /var/www/portfolio/logs

sudo chmod -R 775 /var/www/portfolio/uploads
sudo chmod -R 775 /var/www/portfolio/databases
sudo chmod -R 775 /var/www/portfolio/logs
```

### 6. Configurer la base de données

Créez le fichier `/var/www/portfolio/includes/db.php` :

```php
<?php
$host = 'localhost';
$dbname = 'portfolio_db';
$username = 'portfolio_user';
$password = 'votre_mot_de_passe';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log("Erreur de connexion à la base de données: " . $e->getMessage());
    die("Erreur de connexion à la base de données");
}
```

Sécuriser le fichier :
```bash
sudo chown www-data:www-data /var/www/portfolio/includes/db.php
sudo chmod 600 /var/www/portfolio/includes/db.php
```

### 7. Configurer Nginx

Créez `/etc/nginx/sites-available/portfolio` :

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name votre-domaine.com www.votre-domaine.com;
    
    root /var/www/portfolio/public;
    index index.php index.html;
    
    # Logs
    access_log /var/log/nginx/portfolio_access.log;
    error_log /var/log/nginx/portfolio_error.log;
    
    # Sécurité
    server_tokens off;
    client_max_body_size 20M;
    
    # Bloquer l'accès aux fichiers sensibles
    location ~ /\. {
        deny all;
    }
    
    location ~ /(config|dev-tools|database|databases|logs|scripts) {
        deny all;
        return 404;
    }
    
    # Cache pour fichiers statiques
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # Route principale
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # API routes
    location /api/ {
        try_files $uri $uri/ /api/index.php?$query_string;
    }
    
    # Admin routes
    location /admin/ {
        try_files $uri $uri/ =404;
    }
    
    # PHP-FPM
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 300;
    }
}
```

Activer le site :

```bash
sudo ln -s /etc/nginx/sites-available/portfolio /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
sudo systemctl enable nginx
```

### 8. Configurer SSL avec Let's Encrypt

```bash
sudo certbot --nginx -d votre-domaine.com -d www.votre-domaine.com
```

Suivez les instructions et choisissez de rediriger HTTP vers HTTPS.

### 9. Initialiser la base de données

```bash
cd /var/www/portfolio
sudo -u www-data php database/migrate.php
```

---

## ⚙️ Configuration post-déploiement

### 1. Créer un compte administrateur

```bash
cd /var/www/portfolio
sudo -u www-data php dev-tools/setup_admin.php
```

Ou via l'interface web : `https://votre-domaine.com/admin/login.php`

### 2. Vérifier les permissions

```bash
# Vérifier que tous les fichiers appartiennent à www-data
ls -la /var/www/portfolio/uploads/
ls -la /var/www/portfolio/config/
```

### 3. Configurer les sauvegardes automatiques

Créer un script de sauvegarde `/usr/local/bin/backup-portfolio.sh` :

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/portfolio"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="portfolio_db"
DB_USER="portfolio_user"
DB_PASS="votre_mot_de_passe"

mkdir -p "$BACKUP_DIR"

# Sauvegarde base de données
mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" | gzip > "$BACKUP_DIR/db_$DATE.sql.gz"

# Sauvegarde fichiers uploads
tar -czf "$BACKUP_DIR/uploads_$DATE.tar.gz" /var/www/portfolio/uploads/

# Supprimer les sauvegardes de plus de 7 jours
find "$BACKUP_DIR" -type f -mtime +7 -delete

echo "Sauvegarde terminée: $DATE"
```

Rendre exécutable et ajouter au cron :

```bash
sudo chmod +x /usr/local/bin/backup-portfolio.sh
sudo crontab -e
```

Ajouter cette ligne pour une sauvegarde quotidienne à 2h du matin :
```
0 2 * * * /usr/local/bin/backup-portfolio.sh
```

### 4. Optimiser PHP-FPM

Éditer `/etc/php/8.1/fpm/pool.d/www.conf` :

```ini
pm = dynamic
pm.max_children = 20
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 10
pm.max_requests = 500
```

Redémarrer PHP-FPM :
```bash
sudo systemctl restart php8.1-fpm
```

---

## 🔄 Maintenance

### Mettre à jour l'application

```bash
cd /var/www/portfolio
sudo -u www-data git pull origin main
sudo systemctl restart php8.1-fpm
```

### Vérifier les logs

```bash
# Logs Nginx
sudo tail -f /var/log/nginx/portfolio_error.log

# Logs PHP
sudo tail -f /var/www/portfolio/logs/php-error.log

# Logs système
sudo journalctl -u nginx -f
sudo journalctl -u php8.1-fpm -f
```

### Monitoring

Installer un outil de monitoring basique :

```bash
sudo apt install htop -y
htop
```

---

## 🔍 Dépannage

### Erreur 502 Bad Gateway

```bash
# Vérifier que PHP-FPM fonctionne
sudo systemctl status php8.1-fpm
sudo systemctl restart php8.1-fpm

# Vérifier les permissions du socket
ls -la /var/run/php/
```

### Erreur 403 Forbidden

```bash
# Vérifier les permissions
sudo chown -R www-data:www-data /var/www/portfolio
sudo chmod -R 755 /var/www/portfolio
```

### Erreur de connexion à la base de données

```bash
# Vérifier que MySQL fonctionne
sudo systemctl status mariadb

# Tester la connexion
mysql -u portfolio_user -p portfolio_db
```

### Uploads ne fonctionnent pas

```bash
# Vérifier les permissions d'upload
sudo chmod -R 775 /var/www/portfolio/uploads
sudo chown -R www-data:www-data /var/www/portfolio/uploads

# Vérifier la configuration PHP
php -i | grep upload_max_filesize
php -i | grep post_max_size
```

### Renouvellement SSL automatique

```bash
# Tester le renouvellement
sudo certbot renew --dry-run

# Vérifier le timer certbot
sudo systemctl status certbot.timer
```

---

## 📊 Checklist de déploiement

- [ ] VPS configuré avec Debian
- [ ] Pare-feu configuré (UFW)
- [ ] Nginx installé et configuré
- [ ] PHP-FPM installé et configuré
- [ ] MariaDB installé et sécurisé
- [ ] Base de données créée
- [ ] Application clonée depuis Git
- [ ] Permissions configurées correctement
- [ ] Configuration de la base de données
- [ ] Nginx configuré avec le domaine
- [ ] SSL/TLS configuré avec Let's Encrypt
- [ ] Migrations de base de données exécutées
- [ ] Compte administrateur créé
- [ ] Sauvegardes automatiques configurées
- [ ] Tests de fonctionnement effectués
- [ ] Monitoring en place

---

## 🔐 Sécurité

### Recommandations importantes

1. **Changer tous les mots de passe par défaut**
2. **Désactiver l'accès root SSH** (utiliser un utilisateur sudo)
3. **Configurer fail2ban** pour bloquer les tentatives de connexion
4. **Mettre à jour régulièrement** le système et les applications
5. **Utiliser des mots de passe forts** (minimum 16 caractères)
6. **Activer l'authentification à deux facteurs** si possible
7. **Limiter les connexions SSH** à des IP spécifiques si possible

### Installation de fail2ban

```bash
sudo apt install fail2ban -y
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

---

## 📞 Support

Pour toute question ou problème :
- Consultez les logs : `/var/log/nginx/` et `/var/www/portfolio/logs/`
- Vérifiez la documentation dans `/var/www/portfolio/docs/`
- Consultez le fichier [TROUBLESHOOTING.md](TROUBLESHOOTING.md)

---

**Dernière mise à jour :** Janvier 2026
