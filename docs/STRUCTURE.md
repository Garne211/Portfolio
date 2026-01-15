# 📁 Structure du Portfolio

```
Portfolio/
├── 📂 public/                    # 🌐 Site public accessible
│   ├── index.php               # Page principale
│   └── 📂 assets/
│       ├── 📂 css/
│       │   ├── style.css       # Styles principaux
│       │   └── admin.css       # Styles admin
│       ├── 📂 js/
│       │   ├── script.js       # JavaScript principal
│       │   └── admin.js        # JavaScript admin
│       └── 📂 img/             # Images et SVG
│
├── 📂 admin/                    # 🔐 Panel d'administration
│   ├── dashboard.php           # Tableau de bord
│   ├── profile.php             # Gestion profil
│   ├── languages.php           # Gestion langages
│   ├── projects.php            # Gestion projets
│   ├── experiences.php         # Gestion expériences/stages
│   ├── contents.php            # Gestion contenus
│   ├── messages.php            # Gestion messages de contact
│   ├── security.php            # Sécurité (mot de passe)
│   ├── login.php               # Connexion
│   ├── logout.php              # Déconnexion
│   └── 📂 includes/
│       ├── header.php          # En-tête admin
│       ├── footer.php          # Pied de page admin
│       └── csrf.php            # Protection CSRF
│
├── 📂 api/                      # 🔌 API REST endpoints
│   ├── profile.php             # GET profil
│   ├── languages.php           # GET langages
│   ├── projects.php            # GET projets
│   ├── experiences.php         # GET expériences
│   ├── education.php           # GET formation
│   ├── contact.php             # POST contact
│   ├── contents.php            # GET contenus
│   └── download-cv.php         # GET/download CV
│
├── 📂 includes/                 # 📦 Includes réutilisables
│   ├── db.php                  # Connexion base de données
│   ├── helpers.php             # Fonctions utilitaires
│   ├── csrf.php                # Protection CSRF
│   └── bootstrap.php           # (optional) Initialisation
│
├── 📂 database/                 # 💾 Gestion base de données
│   ├── 📂 migrations/
│   │   ├── migrate_add_cv.php
│   │   ├── migrate_add_experiences.php
│   │   └── ...
│   └── 📂 dumps/               # Exports SQL
│
├── 📂 uploads/                  # 📥 Fichiers uploadés
│   ├── avatars/
│   ├── cv/
│   └── project_images/
│
├── 📂 tests/                    # 🧪 Fichiers de test
│   ├── test_cv.php
│   ├── test_mysql.php
│   ├── test_table_structure.php
│   └── ...
│
├── config.php                  # ⚙️ Configuration centralisée
├── .htaccess                   # Réécriture URL Apache
├── .env.example                # (optional) Variables d'env
└── README.md                   # Ce fichier
```

## 🚀 Utilisation

### Dans les fichiers PHP

Importer la configuration centralisée :
```php
<?php
require_once __DIR__ . '/../config.php';

// Maintenant disponible :
// - ROOT_DIR, PUBLIC_DIR, ADMIN_DIR, API_DIR, UPLOADS_DIR
// - getDBConnection(), escape(), jsonResponse(), etc.
```

### Upload de fichiers

```php
$upload = uploadFile(
    $_FILES['cv'],
    ['application/pdf'],
    5242880, // 5MB
    UPLOADS_DIR . '/cv/'  // Nouveau chemin
);
```

### Chemins relatifs

```php
// Avant
$path = '../uploads/' . $file;

// Après
$path = UPLOADS_DIR . '/' . $file;
```

## 📋 Checklist de nettoyage

- [x] Créer structure de dossiers
- [x] Config centralisée
- [ ] Déplacer tests dans /tests
- [ ] Déplacer migrations dans /database/migrations
- [ ] Créer .htaccess pour les URLs
- [ ] Créer .env.example pour variables sensibles
