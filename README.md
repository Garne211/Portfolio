# Portfolio - Guide de Démarrage Rapide

## 📁 Structure du Projet Organisée

Votre projet est maintenant organisé de manière professionnelle pour le déploiement sur VPS.

### Structure principale

```
Portfolio/
├── admin/              # Interface d'administration
├── api/               # Points d'API REST
├── config/            # Fichiers de configuration
│   ├── config.example.php  # Template de configuration
│   └── config.php     # Configuration réelle (ignoré par Git)
├── database/          # Migrations de base de données
├── databases/         # Sauvegardes DB (ignoré par Git)
├── dev-tools/         # Outils de développement et tests (ignoré par Git)
├── docs/              # Documentation complète
├── includes/          # Fichiers PHP communs
├── public/            # Point d'entrée public
├── scripts/           # Scripts utilitaires
│   └── deploy/        # Scripts de déploiement
├── src/               # Code source
├── tests/             # Tests automatisés
├── uploads/           # Fichiers uploadés (ignoré par Git)
└── .gitignore         # Fichiers à ignorer
```

## 🚀 Déploiement sur VPS Debian

### Option 1 : Déploiement Automatique

```bash
# Sur votre VPS Debian
git clone https://votre-repo.git portfolio
cd portfolio
sudo chmod +x scripts/deploy/deploy-vps.sh
sudo ./scripts/deploy/deploy-vps.sh
```

Le script configure automatiquement :

- ✅ Nginx
- ✅ PHP-FPM 8.1+
- ✅ MariaDB
- ✅ SSL avec Let's Encrypt
- ✅ Permissions et sécurité
- ✅ Base de données
- ✅ Sauvegardes automatiques

### Option 2 : Déploiement Manuel

Consultez le guide détaillé : [DEPLOYMENT_VPS.md](docs/DEPLOYMENT_VPS.md)

## 📋 Avant de Committer

### 1. Créer votre configuration

```bash
cd config
cp config.example.php config.php
# Éditez config.php avec vos paramètres
```

### 2. Vérifier .gitignore

Le fichier `.gitignore` exclut automatiquement :

- Configuration avec mots de passe (`config/config.php`)
- Fichiers uploadés (`uploads/`)
- Outils de développement (`dev-tools/`)
- Sauvegardes de base de données (`databases/`)
- Logs

### 3. Initialiser Git (si ce n'est pas déjà fait)

```bash
git init
git add .
git commit -m "Initial commit - Structure organisée pour VPS"
git branch -M main
git remote add origin https://github.com/votre-username/portfolio.git
git push -u origin main
```

## 🔧 Configuration Locale (Développement)

### 1. Configuration de la Base de Données

Créez `includes/db.php` :

```php
<?php
$host = 'localhost';
$dbname = 'portfolio_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion: " . $e->getMessage());
}
```

### 2. Exécuter les Migrations

```bash
php database/migrate.php
```

### 3. Créer un Compte Admin

```bash
php dev-tools/setup_admin.php
```

## 📦 Fichiers Importants

| Fichier                        | Description                       |
| ------------------------------ | --------------------------------- |
| `.gitignore`                   | Fichiers à exclure de Git         |
| `config/config.example.php`    | Template de configuration         |
| `scripts/deploy/deploy-vps.sh` | Script de déploiement automatique |
| `docs/DEPLOYMENT_VPS.md`       | Guide complet de déploiement      |
| `docs/SECURITY.md`             | Recommandations de sécurité       |

## 🔒 Sécurité

### Fichiers sensibles (ne jamais committer)

- ❌ `config/config.php` - Contient les paramètres sensibles
- ❌ `includes/db.php` - Contient les identifiants DB
- ❌ `uploads/*` - Fichiers uploadés par les utilisateurs
- ❌ `databases/*` - Sauvegardes de base de données

### Permissions recommandées sur VPS

```bash
# Fichiers de l'application
chown -R www-data:www-data /var/www/portfolio
chmod -R 755 /var/www/portfolio

# Dossiers d'upload
chmod -R 775 /var/www/portfolio/uploads

# Fichier de configuration
chmod 600 /var/www/portfolio/config/config.php
```

## 🌐 URLs Importantes

**Développement Local:**

- Site : http://localhost/Portfolio/public/
- Admin : http://localhost/Portfolio/admin/

**Production:**

- Site : https://votre-domaine.com/
- Admin : https://votre-domaine.com/admin/

## 🆘 Aide et Documentation

- 📖 [Guide de déploiement VPS](docs/DEPLOYMENT_VPS.md)
- 🔒 [Guide de sécurité](docs/SECURITY.md)
- 🏗️ [Structure du projet](docs/STRUCTURE.md)
- 🔧 [Dépannage](docs/TROUBLESHOOTING.md)

## ✅ Checklist de Déploiement

- [ ] Configuration créée (`config/config.php`)
- [ ] Base de données créée (`includes/db.php`)
- [ ] Migrations exécutées
- [ ] Compte admin créé
- [ ] `.gitignore` vérifié
- [ ] Commit initial effectué
- [ ] Dépôt Git créé
- [ ] VPS préparé
- [ ] Script de déploiement exécuté
- [ ] SSL configuré
- [ ] DNS pointé vers le VPS
- [ ] Sauvegardes configurées
- [ ] Tests effectués

---

**Prêt pour le déploiement !** 🚀

Pour toute question, consultez la documentation dans le dossier `docs/`.
