# 🎯 GUIDE DE MISE EN SERVICE - Git & SSH

## ✅ ÉTAPE 1 : Configuration Git (FAIT ✓)

Votre code est maintenant versionné avec Git :
- ✅ 2 commits créés avec tous vos changements
- ✅ Données sensibles protégées par .gitignore
- ✅ Prêt pour le push vers GitHub/GitLab

### Vérifier l'état Git

```bash
cd c:\wamp64\www\Portfolio
git status
git log --oneline -5
```

## 📤 ÉTAPE 2 : Push vers GitHub (À faire)

### Option A : Vous avez déjà un dépôt GitHub

```bash
# Le remote est déjà configuré, juste push
git push origin main
```

### Option B : Créer un nouveau dépôt GitHub

1. **Sur GitHub.com** :
   - Cliquez sur "New repository"
   - Nom : `Portfolio` (ou votre choix)
   - ⚠️ **NE PAS** cocher "Initialize with README"
   - Cliquez "Create repository"

2. **Dans votre terminal** :
```bash
cd c:\wamp64\www\Portfolio

# Si vous n'avez pas encore de remote
git remote add origin https://github.com/VOTRE-USERNAME/Portfolio.git

# Ou avec SSH (recommandé)
git remote add origin git@github.com:VOTRE-USERNAME/Portfolio.git

# Push le code
git push -u origin main
```

## 🚀 ÉTAPE 3 : Déploiement SSH vers votre serveur

### Configuration du script

1. **Éditez** le fichier `scripts/deploy/deploy-ssh.sh` :

```bash
# Ligne 14-17 : Modifier ces valeurs
SERVER_USER="votre-user"          # Ex: ubuntu, admin, root
SERVER_HOST="votre-serveur.com"   # Ex: 192.168.1.100 ou monsite.com
SERVER_PORT="22"                   # Port SSH (généralement 22)
REMOTE_PATH="/var/www/portfolio"  # Chemin sur le serveur
```

2. **Exemple de configuration** :
```bash
SERVER_USER="ubuntu"
SERVER_HOST="192.168.1.50"
SERVER_PORT="22"
REMOTE_PATH="/var/www/portfolio"
```

### Préparation du serveur

**Connectez-vous en SSH à votre serveur** :

```bash
ssh votre-user@votre-serveur.com

# Une fois connecté, exécutez :
sudo apt update
sudo apt install -y nginx php8.1-fpm php8.1-sqlite3 php8.1-gd php8.1-mbstring

# Créer les dossiers
sudo mkdir -p /var/www/portfolio
sudo mkdir -p /var/www/backups/portfolio
sudo chown -R $USER:$USER /var/www/portfolio
sudo chown -R $USER:$USER /var/www/backups/portfolio

# Déconnexion
exit
```

### Lancer le déploiement

**Sur Windows avec Git Bash** :

```bash
cd c:\wamp64\www\Portfolio\scripts\deploy
bash deploy-ssh.sh
```

**Ou avec WSL (Windows Subsystem for Linux)** :

```bash
cd /mnt/c/wamp64/www/Portfolio/scripts/deploy
bash deploy-ssh.sh
```

## 🔐 PROTECTION DES DONNÉES

### ✅ Ces fichiers sont AUTOMATIQUEMENT préservés :

Le script **NE TOUCHE JAMAIS** à ces fichiers/dossiers :
- ✅ `databases/portfolio.db` - Votre base de données
- ✅ `uploads/avatars/*` - Photos de profil
- ✅ `uploads/cv/*` - CVs uploadés
- ✅ `uploads/projects/*` - Images de projets
- ✅ `uploads/languages/*` - Icônes de langages
- ✅ `config/config.php` - Configuration avec mots de passe

### 📦 Sauvegarde automatique

À chaque déploiement, le script crée une sauvegarde complète :
- Emplacement : `/var/www/backups/portfolio/portfolio_backup_YYYYMMDD_HHMMSS/`
- Contenu : BD, uploads, config
- Conservation : À gérer manuellement (recommandé : 30 jours)

## 📋 CE QUI SE PASSE LORS DU DÉPLOIEMENT

```
1. ✓ Vérification connexion SSH
2. ✓ Sauvegarde des données existantes
3. ✓ Push du code vers Git (si configuré)
4. ✓ Synchronisation des fichiers (rsync)
5. ✓ Configuration des permissions
6. ✓ Vérification post-déploiement
```

## 🎯 WORKFLOW DE DÉVELOPPEMENT

### Développement local

```bash
# 1. Faire vos modifications
# 2. Tester localement : http://localhost/Portfolio/

# 3. Commiter
git add -A
git commit -m "Description des changements"

# 4. Push vers GitHub
git push origin main
```

### Déploiement vers le serveur

```bash
# Lancer le déploiement
cd scripts/deploy
bash deploy-ssh.sh
```

Le script va :
1. Sauvegarder vos données sur le serveur
2. Synchroniser uniquement le code
3. Préserver TOUTES vos données existantes

## ⚡ DÉPLOIEMENT RAPIDE (après première config)

Une fois tout configuré, déployer devient très simple :

```bash
# Faire vos changements
git add -A
git commit -m "Nouvelles fonctionnalités"

# Déployer (1 commande)
bash scripts/deploy/deploy-ssh.sh
```

## 🔍 VÉRIFICATION POST-DÉPLOIEMENT

### Sur le serveur

```bash
ssh votre-user@votre-serveur.com

# Vérifier les fichiers
ls -la /var/www/portfolio/

# Vérifier les données
ls -la /var/www/portfolio/databases/
ls -la /var/www/portfolio/uploads/

# Voir les backups
ls -la /var/www/backups/portfolio/
```

### Sur le web

- ✅ Frontend FR : `http://votre-serveur/`
- ✅ Frontend EN : `http://votre-serveur/index-en.html`
- ✅ Admin : `http://votre-serveur/admin/`

## 🐛 RÉSOLUTION DE PROBLÈMES

### "Permission denied" lors du push Git

```bash
# Utiliser HTTPS avec token
git remote set-url origin https://github.com/USER/REPO.git

# Ou configurer SSH
ssh-keygen -t rsa -b 4096 -C "votre-email@gmail.com"
# Ajouter la clé publique sur GitHub
```

### "Cannot connect to server" lors du déploiement

```bash
# Tester la connexion SSH
ssh votre-user@votre-serveur.com

# Vérifier le port
ssh -p 22 votre-user@votre-serveur.com
```

### Le site ne s'affiche pas

```bash
# Sur le serveur, vérifier Nginx
sudo nginx -t
sudo systemctl status nginx
sudo systemctl restart nginx

# Vérifier les logs
sudo tail -f /var/log/nginx/error.log
```

## 📚 DOCUMENTATION COMPLÈTE

Pour plus de détails, consultez :
- [DEPLOYMENT_SSH.md](../docs/DEPLOYMENT_SSH.md) - Guide complet SSH
- [DEPLOYMENT_VPS.md](../docs/DEPLOYMENT_VPS.md) - Configuration VPS
- [SECURITY.md](../docs/SECURITY.md) - Sécurité

## ✅ CHECKLIST FINALE

- [ ] Git configuré et commits créés
- [ ] Dépôt GitHub créé et lié
- [ ] Code pushé vers GitHub
- [ ] Serveur VPS préparé (Nginx, PHP)
- [ ] Script `deploy-ssh.sh` configuré
- [ ] Première sauvegarde créée
- [ ] Site accessible en ligne
- [ ] Données locales préservées
- [ ] SSL configuré (optionnel)

---

## 🎉 RÉSUMÉ - VOUS ÊTES PRÊT !

Votre portfolio est maintenant :
- ✅ **Versionné** avec Git
- ✅ **Sauvegardé** sur GitHub
- ✅ **Déployable** en 1 commande
- ✅ **Sécurisé** - données préservées
- ✅ **Professionnel** - workflow moderne

**Prochaine étape** : Lancez `bash scripts/deploy/deploy-ssh.sh` ! 🚀
