# 🚀 Guide de Démarrage - Portfolio Hery LEBEHOT

## 📌 Résumé des corrections

Tous les fichiers manquants ont été créés et configurés :

✅ **Fichier JavaScript principal** : `/public/assets/js/script.js`
✅ **APIs manquantes** : `/api/languages.php` et `/api/profile.php`
✅ **Configuration BD** : `/init_database.php`
✅ **Page d'installation** : `/install.html`
✅ **Fichier .htaccess** : `/public/.htaccess`
✅ **Documentation** : Ce fichier

---

## 🎯 Installation Rapide

### 1️⃣ Initialiser la Base de Données
```bash
# Via le navigateur
http://localhost/Portfolio/install.html
```

Cliquez sur **"Initialiser la BD"** pour créer la structure SQLite et les données par défaut.

### 2️⃣ Accéder au Portfolio
```
🌐 Frontend: http://localhost/Portfolio/public/
⚙️  Admin:    http://localhost/Portfolio/admin/
```

### 3️⃣ Personnaliser le Contenu
- Accédez à l'admin panel
- Configurez votre profil (nom, email, liens sociaux)
- Ajoutez vos projets
- Définissez vos compétences
- Personnalisez les traductions FR/EN

---

## 📂 Architecture du Projet

```
Portfolio/
│
├── 📄 install.html              ⭐ Installation rapide
├── 📄 init_database.php         ⭐ Initialisation BD
│
├── public/                       # 🌐 Frontend public
│   ├── index.php               # Page d'accueil principale
│   ├── .htaccess               # Configuration Apache
│   └── assets/
│       ├── css/style.css       # Styles CSS (1343 lignes)
│       └── js/script.js        # ⭐ JavaScript principal (CRÉÉ)
│
├── api/                         # 🔌 APIs JSON
│   ├── contents.php            # GET: Traductions FR/EN
│   ├── projects.php            # GET: Projets
│   ├── languages.php           # ⭐ GET: Compétences (CRÉÉ)
│   └── profile.php             # ⭐ GET: Profil (CRÉÉ)
│
├── admin/                       # ⚙️ Panneau d'administration
│   ├── dashboard.php
│   ├── login.php
│   ├── projects.php
│   ├── languages.php
│   ├── contents.php
│   ├── profile.php
│   ├── assets/
│   │   ├── css/admin.css
│   │   └── js/admin.js
│   └── includes/
│       ├── header.php
│       ├── footer.php
│       └── csrf.php
│
├── includes/                    # 📦 Includes partagés
│   ├── db.php                  # Connexion SQLite
│   ├── helpers.php             # Fonctions utilitaires
│   └── csrf.php                # Protection CSRF
│
├── databases/                   # 💾 Base de données
│   └── portfolio.db            # SQLite (créée lors de l'init)
│
└── CORRECTIONS.md              # 📋 Résumé des corrections
```

---

## 🛠️ Technologies Utilisées

| Layer | Technologie |
|-------|-------------|
| **Frontend** | HTML5, CSS3, Vanilla JavaScript (ES6+) |
| **Backend** | PHP 7.4+ |
| **Base de données** | SQLite3 |
| **Style** | CSS Grid, Flexbox, Animations CSS |
| **Responsive** | Mobile-first design |
| **Multilingue** | Système i18n (FR/EN) |

---

## ⚡ Fonctionnalités Principales

### 🌐 Frontend
- ✅ Page d'accueil responsive
- ✅ Navigation smooth
- ✅ Clavier interactif des compétences
- ✅ Grille des projets dynamique
- ✅ Formulaire de contact
- ✅ Animations au scroll
- ✅ Support multilingue (FR/EN)
- ✅ Mode sombre par défaut

### ⚙️ Backend
- ✅ APIs REST JSON
- ✅ Gestion multilingue des contenus
- ✅ SGBD SQLite intégré
- ✅ Système d'admin pour CRUD
- ✅ Protection CSRF

---

## 🔧 Configuration Serveur

### Permissions Recommandées
```bash
# Dossiers
chmod 755 public/
chmod 755 admin/
chmod 755 api/
chmod 775 databases/    # Important pour SQLite

# Fichiers
chmod 644 public/index.php
chmod 644 *.php
chmod 666 databases/portfolio.db
```

### Apache (httpd.conf ou .htaccess)
```apache
# Déjà configuré dans public/.htaccess
# Compression, cache, sécurité
```

### PHP (php.ini)
```ini
max_upload_size = 10M
memory_limit = 256M
```

---

## 📝 Variables d'Environnement

Créez un fichier `.env` (optionnel) :
```
DB_PATH=./databases/portfolio.db
API_BASE=/api
LANG_DEFAULT=fr
DEBUG=false
```

---

## 🚀 Premier Démarrage

1. **Accéder à l'installation**
   ```
   http://localhost/Portfolio/install.html
   ```

2. **Cliquer sur "Initialiser la BD"**
   - Les tables seront créées
   - Les données par défaut seront insérées

3. **Consulter le portfolio**
   ```
   http://localhost/Portfolio/public/
   ```

4. **Accéder à l'admin**
   ```
   http://localhost/Portfolio/admin/
   ```

---

## 📋 Checklist Post-Installation

- [ ] Base de données initialisée
- [ ] Portfolio accessible
- [ ] Admin panel accessible
- [ ] Profil configuré (nom, email, liens)
- [ ] Projets ajoutés
- [ ] Compétences configurées
- [ ] Traductions vérifiées
- [ ] Images ajoutées
- [ ] Liens sociaux testés
- [ ] Formulaire de contact testé
- [ ] Multilingue testé (FR/EN)
- [ ] Responsive testé (mobile/desktop)

---

## 🐛 Dépannage

### Erreur: "Cannot connect to database"
```php
// Vérifier dans includes/db.php
$dbPath = __DIR__ . '/../database/portfolio.db';
// et les permissions: chmod 775 databases/
```

### Erreur: "API not responding"
```javascript
// Vérifier dans script.js
const CONFIG = {
    API_BASE: '/api',  // Correct le chemin si nécessaire
    // ...
};
```

### Erreur: "File not found"
```
Vérifier que les fichiers existent:
- public/assets/js/script.js
- api/languages.php
- api/profile.php
```

### Styles ne s'appliquent pas
```html
<!-- Vérifier dans public/index.php -->
<link rel="stylesheet" href="assets/css/style.css">
```

---

## 📚 Documentation Supplémentaire

- **Script JS** : Voir commentaires dans `/public/assets/js/script.js`
- **PHP** : Voir commentaires dans les fichiers `.php`
- **CSS** : Voir variables dans `/public/assets/css/style.css`

---

## 👤 Infos Profil (Par Défaut)

| Champ | Valeur |
|-------|--------|
| **Nom** | Hery LEBEHOT |
| **Email** | contact@example.com |
| **GitHub** | https://github.com/hlebehot |
| **LinkedIn** | https://linkedin.com/in/hery-lebehot |

*(À personnaliser dans l'admin panel)*

---

## 🎨 Personnalisation

### Couleurs
Modifier dans `public/assets/css/style.css` :
```css
:root {
    --color-primary: #dc2626;      /* Rouge actuel */
    --color-primary-dark: #991b1b;
    --color-primary-light: #ef4444;
    /* ... */
}
```

### Polices
```css
--font-primary: 'Inter', sans-serif;
--font-mono: 'JetBrains Mono', monospace;
```

### Contenu
Tous les textes sont modifiables via l'admin panel (base de données)

---

## 📞 Support

Pour toute question ou problème:
1. Vérifier les logs PHP
2. Consulter la documentation dans les fichiers
3. Vérifier les permissions des dossiers
4. Tester avec le navigateur DevTools

---

**Dernière mise à jour**: Janvier 2026
**Status**: ✅ Prêt pour la production
