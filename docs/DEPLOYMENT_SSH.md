# 🚀 Guide de Déploiement SSH

## Configuration Rapide

### 1. Configurer le script de déploiement

Éditez `scripts/deploy/deploy-ssh.sh` et modifiez ces lignes :

```bash
SERVER_USER="votre-user"          # Ex: admin, ubuntu, root
SERVER_HOST="votre-serveur.com"   # Ex: 192.168.1.100 ou monsite.com
SERVER_PORT="22"                   # Port SSH (généralement 22)
REMOTE_PATH="/var/www/portfolio"   # Chemin sur le serveur
```

### 2. Configuration SSH (Optionnel mais recommandé)

Pour éviter de taper le mot de passe à chaque fois, configurez une clé SSH :

#### Windows (PowerShell)
```powershell
# Générer une clé SSH
ssh-keygen -t rsa -b 4096 -C "votre-email@example.com"

# Copier la clé publique vers le serveur
type $env:USERPROFILE\.ssh\id_rsa.pub | ssh user@serveur "cat >> ~/.ssh/authorized_keys"
```

#### Linux/Mac
```bash
# Générer une clé SSH
ssh-keygen -t rsa -b 4096 -C "votre-email@example.com"

# Copier la clé vers le serveur
ssh-copy-id user@serveur
```

### 3. Lancer le déploiement

#### Sur Linux/Mac
```bash
cd scripts/deploy
chmod +x deploy-ssh.sh
./deploy-ssh.sh
```

#### Sur Windows (Git Bash ou WSL)
```bash
cd scripts/deploy
bash deploy-ssh.sh
```

## 📋 Ce que le script fait

### ✅ Actions effectuées

1. **Sauvegarde automatique** des données existantes :
   - Base de données (`databases/`)
   - Fichiers uploadés (`uploads/`)
   - Configuration (`config/config.php`)

2. **Synchronisation** du code :
   - Upload uniquement des fichiers du code source
   - Exclusion des données sensibles (déjà dans `.gitignore`)
   - Suppression des fichiers obsolètes sur le serveur

3. **Configuration** des permissions :
   - Dossiers : `755`
   - Fichiers : `644`
   - Dossiers d'écriture : `775`
   - Scripts : exécutables

4. **Vérification** post-déploiement :
   - Présence des fichiers critiques
   - Structure des dossiers

### 🔒 Données préservées (JAMAIS écrasées)

- ✅ **Base de données** : `databases/portfolio.db`
- ✅ **Fichiers uploadés** : `uploads/**/*`
- ✅ **Configuration** : `config/config.php`
- ✅ **Logs** : `*.log`

## 🎯 Scénarios d'utilisation

### Déploiement initial (première fois)

```bash
./deploy-ssh.sh
```

Le script va :
- Créer la structure complète
- Installer le code
- Vous demander de créer `config/config.php` manuellement

### Mise à jour du code

```bash
# 1. Faire vos modifications en local
# 2. Commiter
git add -A
git commit -m "Nouvelle fonctionnalité"

# 3. Déployer
./deploy-ssh.sh
```

Le script va :
- Sauvegarder vos données existantes
- Mettre à jour uniquement le code
- Préserver toutes vos données

### Déploiement rapide (sans Git)

Si vous voulez juste synchroniser sans passer par Git :

```bash
# Le script détecte automatiquement l'absence de remote Git
# et propose de continuer avec rsync uniquement
./deploy-ssh.sh
```

## 🔧 Configuration serveur

### Prérequis sur le serveur

```bash
# Installer les dépendances
sudo apt update
sudo apt install -y nginx php8.1-fpm php8.1-sqlite3 php8.1-gd php8.1-mbstring

# Créer le dossier du site
sudo mkdir -p /var/www/portfolio
sudo chown -R $USER:$USER /var/www/portfolio

# Créer le dossier de backup
sudo mkdir -p /var/www/backups/portfolio
sudo chown -R $USER:$USER /var/www/backups/portfolio
```

### Configuration Nginx

Créez `/etc/nginx/sites-available/portfolio` :

```nginx
server {
    listen 80;
    server_name votre-domaine.com;
    root /var/www/portfolio/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Activer le site :

```bash
sudo ln -s /etc/nginx/sites-available/portfolio /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## 🐛 Dépannage

### Erreur de connexion SSH

```bash
# Tester la connexion
ssh -v user@serveur

# Vérifier les clés
ls -la ~/.ssh/

# Vérifier les permissions
chmod 700 ~/.ssh
chmod 600 ~/.ssh/id_rsa
chmod 644 ~/.ssh/id_rsa.pub
```

### Erreur de permissions

```bash
# Sur le serveur, corriger les permissions
cd /var/www/portfolio
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 775 databases uploads
```

### Rsync trop lent

```bash
# Utiliser la compression
rsync -avz --compress-level=9 ...

# Exclure plus de fichiers
# Éditez le script et ajoutez des exclusions
```

## 📊 Logs et monitoring

### Voir les logs de déploiement

Les logs sont affichés en temps réel pendant le déploiement.

### Logs du serveur

```bash
# Logs Nginx
sudo tail -f /var/log/nginx/access.log
sudo tail -f /var/log/nginx/error.log

# Logs PHP
sudo tail -f /var/log/php8.1-fpm.log
```

### Vérifier l'espace disque

```bash
# Sur le serveur
df -h
du -sh /var/www/portfolio
du -sh /var/www/backups/portfolio
```

## 🔄 Restauration depuis backup

Si besoin de restaurer une backup :

```bash
# Sur le serveur
cd /var/www/backups/portfolio

# Lister les backups
ls -lh

# Restaurer une backup
BACKUP_NAME="portfolio_backup_20260202_143000"
cp -r $BACKUP_NAME/databases/* /var/www/portfolio/databases/
cp -r $BACKUP_NAME/uploads/* /var/www/portfolio/uploads/
cp $BACKUP_NAME/config/config.php /var/www/portfolio/config/
```

## ✅ Checklist post-déploiement

- [ ] Site accessible : `http://votre-domaine.com`
- [ ] Version française : `http://votre-domaine.com/index-fr.html`
- [ ] Version anglaise : `http://votre-domaine.com/index-en.html`
- [ ] Admin accessible : `http://votre-domaine.com/admin/`
- [ ] Formulaire de contact fonctionne
- [ ] Upload de fichiers fonctionne
- [ ] Base de données accessible
- [ ] Toutes les données existantes préservées

## 💡 Bonnes pratiques

1. **Toujours tester en local** avant de déployer
2. **Faire des commits réguliers** avec des messages clairs
3. **Vérifier les logs** après chaque déploiement
4. **Conserver les backups** pendant au moins 30 jours
5. **Documenter** les changements importants
6. **Tester** après chaque déploiement

## 🆘 Support

En cas de problème :

1. Vérifiez les logs du script de déploiement
2. Consultez les logs serveur (Nginx, PHP)
3. Vérifiez que toutes les dépendances sont installées
4. Testez la connexion SSH manuellement
5. Vérifiez les permissions des fichiers

---

**Auteur** : Portfolio Deployment System  
**Version** : 1.0.0  
**Date** : Février 2026
