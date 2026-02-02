# 🎯 Portfolio Hery LEBEHOT - Résumé Complet

## 📌 Vue d'Ensemble

Portfolio web professionnel multilingue (FR/EN) avec interface d'administration complète, système de gestion de contenu, et APIs REST. Déployable sur VPS Debian avec configuration automatisée.

**Status**: 🟢 **PRODUCTION READY**

---

## 🏗️ Architecture du Projet

### Structure principale

```
Portfolio/
├── admin/              # Interface d'administration complète
│   ├── dashboard.php   # Tableau de bord
│   ├── login.php       # Authentification
│   ├── profile.php     # Gestion profil
│   ├── projects.php    # Gestion projets
│   ├── education.php   # Gestion formations
│   ├── experiences.php # Gestion expériences
│   ├── languages.php   # Gestion compétences
│   ├── messages.php    # Messages de contact
│   └── assets/         # CSS/JS admin
│
├── api/                # APIs REST JSON
│   ├── profile.php     # Données profil
│   ├── languages.php   # Compétences/langages
│   ├── projects.php    # Portfolio projets
│   ├── education.php   # Formations
│   ├── experiences.php # Expériences pro
│   ├── contents.php    # Traductions i18n
│   ├── contact.php     # Formulaire contact
│   └── download-cv.php # Téléchargement CV
│
├── config/             # Configuration
│   ├── config.example.php  # Template
│   └── config.php      # Config réelle (ignoré Git)
│
├── database/           # Migrations
│   ├── migrate.php     # Script migration
│   └── migrations/     # Fichiers de migration
│
├── databases/          # Base de données SQLite
├── dev-tools/          # Outils développement (ignoré Git)
├── docs/               # Documentation complète
│   ├── QUICKSTART.md
│   ├── DEPLOYMENT_VPS.md
│   ├── SECURITY.md
│   └── STRUCTURE.md
│
├── includes/           # Fichiers communs PHP
│   ├── db.php          # Connexion BD
│   ├── helpers.php     # Fonctions utilitaires
│   ├── csrf.php        # Protection CSRF
│   ├── logger.php      # Système de logs
│   └── security-headers.php
│
├── public/             # Point d'entrée public
│   ├── index.php       # Page d'accueil
│   ├── index-fr.html   # Version FR
│   ├── index-en.html   # Version EN
│   └── assets/
│       ├── css/        # Styles
│       ├── js/         # JavaScript
│       └── img/        # Images
│
├── scripts/            # Scripts utilitaires
│   └── deploy/         # Déploiement VPS
│       ├── deploy-vps.sh       # Déploiement auto
│       ├── deploy-quick.sh     # Déploiement rapide
│       └── install-manual.sh   # Installation manuelle
│
├── uploads/            # Fichiers uploadés (ignoré Git)
│   ├── avatars/        # Photos profil
│   ├── cv/             # CVs
│   ├── projects/       # Images projets
│   └── languages/      # Icônes compétences
│
└── tests/              # Tests automatisés
```

---

## 🎯 Fonctionnalités Principales

### 🌐 Frontend
- ✅ **Design responsive** - Mobile-first, adaptatif
- ✅ **Multilingue** - Français/Anglais avec switch dynamique
- ✅ **Navigation fluide** - Menu responsive avec animations
- ✅ **Clavier interactif** - Affichage des compétences techniques
- ✅ **Portfolio projets** - Grille dynamique avec filtres
- ✅ **Formulaire contact** - Validation temps réel
- ✅ **Animations** - Scroll reveals et transitions smooth
- ✅ **SEO optimisé** - Meta tags et structure sémantique

### ⚙️ Backend
- ✅ **APIs REST** - 8 endpoints JSON bien documentés
- ✅ **Base de données** - SQLite avec migrations versionnées
- ✅ **Authentification** - Sessions sécurisées avec timeout
- ✅ **CRUD complet** - Gestion de tous les contenus
- ✅ **Multi-roles** - Admin/Super-admin
- ✅ **Upload fichiers** - CV, avatars, images projets
- ✅ **Logs système** - Traçabilité des actions
- ✅ **Protection CSRF** - Tokens sur tous les formulaires

### 🔐 Sécurité
- ✅ **Headers sécurisés** - CSP, X-Frame-Options, HSTS
- ✅ **PDO préparé** - Protection injection SQL
- ✅ **Échappement XSS** - Validation entrées/sorties
- ✅ **Rate limiting** - Protection contre spam
- ✅ **Sessions sécurisées** - Timeout et régénération
- ✅ **Permissions fichiers** - Configuration appropriée

### 📱 Responsive Design
- 📱 **Mobile** (320px - 767px) - Navigation hamburger
- 📲 **Tablette** (768px - 1023px) - Layout adapté
- 💻 **Desktop** (1024px+) - Pleine largeur

---

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

## � Installation Locale (Développement)

### Prérequis
- PHP 8.0+ avec extensions : `pdo_sqlite`, `gd`, `mbstring`, `fileinfo`
- SQLite3
- Apache/Nginx
- Composer (optionnel)

### Étapes d'Installation

#### 1. Cloner le projet
```bash
git clone https://votre-repo.git portfolio
cd portfolio
```

#### 2. Configuration
```bash
# Copier le fichier de configuration
cp config/config.example.php config/config.php

# Éditer avec vos paramètres
nano config/config.php
```

#### 3. Initialiser la base de données
```bash
# Exécuter les migrations
php database/migrate.php

# Ou utiliser l'interface web
http://localhost/Portfolio/dev-tools/install.html
```

#### 4. Configurer les permissions
```bash
# Linux/Mac
chmod 755 public/
chmod 775 databases/ uploads/
chmod 644 config/config.php

# Windows - Vérifier les permissions du dossier
```

#### 5. Créer un compte admin
```bash
php dev-tools/setup_admin.php
```

#### 6. Accéder au site
- **Frontend** : `http://localhost/Portfolio/public/`
- **Admin** : `http://localhost/Portfolio/admin/`
- **Diagnostic** : `http://localhost/Portfolio/dev-tools/status.php`

---

## 💾 Base de Données

### Tables Principales
| Table | Description | Colonnes clés |
|-------|-------------|---------------|
| `profile` | Informations personnelles | name, email, github, linkedin |
| `languages` | Compétences techniques | name, percentage, icon, active |
| `projects` | Portfolio projets | title, description, image, url |
| `education` | Formations/diplômes | degree, school, year, description |
| `experiences` | Expériences pro | title, company, start_date, end_date |
| `contents` | Traductions i18n | section, key, lang, value |
| `admin_users` | Comptes admin | username, password, role |
| `admin_sessions` | Sessions actives | token, user_id, expires_at |
| `messages` | Formulaire contact | name, email, message, read |

### Migrations
```bash
# Exécuter toutes les migrations
php database/migrate.php

# Les migrations sont versionnées et traçables
```

---

## 🔧 Configuration Locale (Développement)

### 1. Fichier config/config.php

Créez votre configuration depuis le template :

```php
<?php
// config/config.php
define('DB_PATH', __DIR__ . '/../databases/portfolio.db');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('BASE_URL', 'http://localhost/Portfolio/');
define('ADMIN_SESSION_TIMEOUT', 1800); // 30 minutes
define('MAX_UPLOAD_SIZE', 5242880); // 5 MB
```

### 2. Structure .htaccess (Apache)

```apache
# public/.htaccess
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Headers de sécurité
Header set X-Frame-Options "SAMEORIGIN"
Header set X-Content-Type-Options "nosniff"
Header set X-XSS-Protection "1; mode=block"
```

### 3. Nginx (Alternative)

```nginx
server {
    listen 80;
    server_name localhost;
    root /var/www/portfolio/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}
```

---

## 📡 APIs Disponibles

| Endpoint | Méthode | Description | Authentification |
|----------|---------|-------------|------------------|
| `/api/profile.php` | GET | Informations profil | Non |
| `/api/languages.php` | GET | Compétences techniques | Non |
| `/api/projects.php` | GET | Liste des projets | Non |
| `/api/education.php` | GET | Formations | Non |
| `/api/experiences.php` | GET | Expériences pro | Non |
| `/api/contents.php?lang=fr` | GET | Traductions | Non |
| `/api/contact.php` | POST | Envoi message | Non |
| `/api/download-cv.php` | GET | Télécharger CV | Non |

### Exemple d'utilisation

```javascript
// Récupérer les compétences
fetch('/api/languages.php')
    .then(res => res.json())
    .then(data => console.log(data));

// Envoyer un message
fetch('/api/contact.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        name: 'John Doe',
        email: 'john@example.com',
        message: 'Hello!'
    })
});
```

---

## 🛠️ Outils de Développement

| Outil | Chemin | Description |
|-------|--------|-------------|
| Installation | `/dev-tools/install.html` | Interface GUI d'installation |
| Diagnostic | `/dev-tools/status.php` | Vérification système complète |
| Test API | `/dev-tools/test-api.html` | Testeur d'APIs interactif |
| Setup Admin | `/dev-tools/setup_admin.php` | Créer compte admin |
| Migration | `/database/migrate.php` | Exécuter migrations |

---

## 🎨 Stack Technique

### Frontend
- **HTML5** - Structure sémantique
- **CSS3** - Flexbox, Grid, animations
- **JavaScript ES6+** - Vanilla JS, Fetch API
- **Responsive** - Mobile-first approach

### Backend
- **PHP 8+** - POO, namespaces
- **SQLite** - Base de données légère
- **PDO** - Requêtes préparées
- **Sessions** - Authentification sécurisée

### DevOps
- **Git** - Contrôle de version
- **Nginx/Apache** - Serveur web
- **Let's Encrypt** - SSL gratuit
- **Bash** - Scripts de déploiement

---

## 📚 Documentation Complète

| Document | Description |
|----------|-------------|
| [QUICKSTART.md](docs/QUICKSTART.md) | Guide démarrage rapide |
| [DEPLOYMENT_VPS.md](docs/DEPLOYMENT_VPS.md) | Déploiement sur VPS |
| [SECURITY.md](docs/SECURITY.md) | Bonnes pratiques sécurité |
| [STRUCTURE.md](docs/STRUCTURE.md) | Architecture détaillée |
| [TROUBLESHOOTING.md](docs/TROUBLESHOOTING.md) | Résolution de problèmes |
| [ADMIN_CREDENTIALS.md](docs/ADMIN_CREDENTIALS.md) | Gestion des accès |

---

## ✅ Checklist Post-Installation

- [ ] Configuration créée (`config/config.php`)
- [ ] Base de données initialisée
- [ ] Migrations exécutées
- [ ] Compte admin créé
- [ ] Permissions configurées
- [ ] Frontend accessible
- [ ] Admin accessible
- [ ] APIs fonctionnelles
- [ ] Formulaire contact opérationnel
- [ ] Upload de fichiers testé
- [ ] SSL configuré (production)
- [ ] Sauvegardes configurées (production)

---

## 🐛 Dépannage Rapide

### Erreur 500
```bash
# Vérifier les logs
tail -f /var/log/nginx/error.log
tail -f /var/log/php8.1-fpm.log

# Vérifier permissions
ls -la databases/ uploads/
```

### Base de données verrouillée
```bash
# Vérifier processus
lsof databases/portfolio.db

# Recréer si nécessaire
php database/migrate.php
```

### APIs ne répondent pas
```bash
# Test direct
curl http://localhost/Portfolio/api/profile.php

# Vérifier PHP
php -v
php -m | grep pdo_sqlite
```

---

## 🚀 Commandes Utiles

```bash
# Développement
php -S localhost:8000 -t public/  # Serveur dev PHP

# Tests
php tests/index.php                # Tests unitaires

# Déploiement
./scripts/deploy/deploy-quick.sh   # Déploiement rapide
./scripts/deploy/deploy-vps.sh     # Déploiement VPS complet

# Maintenance
php database/migrate.php           # Migrations
php dev-tools/status.php           # Diagnostic
```

---

## 📊 Statistiques du Projet

| Métrique | Valeur |
|----------|--------|
| **Fichiers PHP** | 50+ |
| **APIs REST** | 8 |
| **Tables BD** | 9 |
| **Traductions** | 45+ clés |
| **Migrations** | 6 |
| **Pages admin** | 10+ |
| **Lignes de code** | ~5000+ |

---

## 🏆 Bonnes Pratiques Implémentées

✅ **Code**
- PSR-12 coding standards
- Séparation des responsabilités
- DRY (Don't Repeat Yourself)
- Commentaires descriptifs

✅ **Sécurité**
- Protection CSRF sur tous les formulaires
- PDO avec requêtes préparées
- Échappement XSS
- Validation serveur stricte
- Sessions sécurisées

✅ **Performance**
- Cache HTTP headers
- Compression Gzip
- Optimisation images
- Lazy loading

✅ **SEO**
- URLs propres
- Meta tags complets
- Sitemap XML
- Schema.org markup

---

## 📞 Support

Pour toute question ou problème :

1. Consulter la [documentation](docs/)
2. Vérifier les [issues GitHub](https://github.com/votre-repo/issues)
3. Exécuter le [diagnostic](dev-tools/status.php)
4. Consulter les logs système

---

## 📝 Changelog

### v1.0.0 (Février 2026)
- ✅ Structure complète du projet
- ✅ Système d'administration
- ✅ APIs REST
- ✅ Multilingue FR/EN
- ✅ Migrations de base de données
- ✅ Scripts de déploiement VPS
- ✅ Documentation complète

---

## 📜 Licence

Ce projet est privé. Tous droits réservés.

---

## 👨‍💻 Auteur

**Hery LEBEHOT**
- GitHub: [@hlebehot](https://github.com/hlebehot)
- Portfolio: [herylebehot.com](https://herylebehot.com)

---

**Dernière mise à jour** : Février 2, 2026  
**Version** : 1.0.0  
**Status** : 🟢 Production Ready

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
