# 📦 ORGANISATION DU PORTFOLIO

## ✨ Nouvelle Structure Implémentée

### Dossiers Créés

```
✅ src/                      # Code backend réutilisable (futur)
✅ tests/                    # Fichiers de test
✅ database/migrations/      # Fichiers de migration
✅ uploads/avatars/          # Avatars utilisateurs
✅ uploads/cv/               # CVs
✅ uploads/projects/         # Images projets
✅ uploads/languages/        # Icons langages
```

### Fichiers de Configuration

```
✅ config.php               # Configuration centralisée
✅ .htaccess                # Sécurité Apache
✅ .env.example             # Variables d'environnement
```

### Fichiers de Gestion

```
✅ database/migrate.php     # Exécuteur de migrations
✅ tests/index.php          # Interface de test
✅ STRUCTURE.md             # Documentation structure
```

---

## 🚀 Améliorations Apportées

### 1. **Configuration Centralisée**

- `config.php` importe tous les includes nécessaires
- Constantes globales : `ROOT_DIR`, `PUBLIC_DIR`, `ADMIN_DIR`, etc.
- Un seul point d'import pour tout

### 2. **Migrations Organisées**

- `database/migrations/001_add_cv_path.php`
- `database/migrations/002_add_experiences_education.php`
- `database/migrate.php` gère l'exécution automatique

### 3. **Tests Structurés**

- `/tests/index.php` - Dashboard des tests
- `/tests/test_cv.php` - Test des uploads CV
- Facile d'ajouter d'autres tests

### 4. **Uploads Sécurisés**

- Dossiers par type : avatars/, cv/, projects/, languages/
- `.htaccess` empêche l'exécution PHP en /uploads/
- Validation MIME type et taille

### 5. **Sécurité Renforcée**

- Headers HTTP de sécurité dans `.htaccess`
- CSRF tokens sur tous les formulaires
- Validation des uploads
- Session timeout

---

## 💾 Comment Utiliser

### Importer la config dans un fichier PHP

```php
<?php
require_once __DIR__ . '/../config/config.php';

// Maintenant disponible :
// - getDBConnection()
// - escape(), sanitizeInput()
// - uploadFile(), deleteUploadedFile()
// - Constantes : ROOT_DIR, UPLOADS_DIR, etc.
```

### Accéder aux fichiers uploadés

```php
// Ancien way
$file = '../uploads/' . $filename;

// Nouveau way
$file = UPLOADS_DIR . '/cv/' . $filename;
```

### Exécuter les migrations

```bash
C:\wamp64\bin\php\php8.1.31\php.exe database/migrate.php
```

### Lancer les tests

```
http://localhost/Portfolio/tests/
```

---

## 📋 Checklist de Migration

- [x] Créer structure de dossiers
- [x] Config centralisée (config.php)
- [x] Migrations organisées
- [x] Tests centralisés
- [x] Uploads sécurisés
- [x] .htaccess pour sécurité
- [x] Documentation STRUCTURE.md
- [ ] Mettre à jour tous les require_once (facultatif, maintient la compatibilité)
- [ ] Créer .env fichier en production
- [ ] Tester en VPS OVH

---

## ⚡ Fonctionnalités Principales

### Contact Form

- ✅ Validation côté client
- ✅ Envoi API sécurisé
- ✅ **Nouveau**: Empêche les clics multiples
- ✅ Notifications email

### Projets

- ✅ **Nouveau**: Grille centrée et responsive
- ✅ Auto-adaptation au nombre de projets
- ✅ Cartes avec hover effects

### Education & Expériences

- ✅ **Nouveau**: Carrousel interactif
- ✅ Navigation précédent/suivant
- ✅ Indicateurs de position

### CV

- ✅ Upload sécurisé
- ✅ **Nouveau**: Téléchargement via API
- ✅ Sécurité MIME type

---

## 🔒 Sécurité

| Mesure         | Statut | Détail                        |
| -------------- | ------ | ----------------------------- |
| CSRF Tokens    | ✅     | Sur tous les formulaires      |
| Password Hash  | ✅     | bcrypt en admin               |
| SQL Injection  | ✅     | Prepared statements (PDO)     |
| XSS Protection | ✅     | htmlspecialchars() partout    |
| File Upload    | ✅     | Validation MIME + taille      |
| PHP en uploads | ✅     | .htaccess empêche exécution   |
| Headers HTTP   | ✅     | CSP, X-Frame-Options, etc.    |
| Session Auth   | ✅     | requireAuth() sur pages admin |

---

## 📞 Contacts

- 📧 Email : contact@portfolio.local
- 🐙 GitHub : [username]
- 💼 LinkedIn : [username]

---

**Dernière mise à jour**: 12 Janvier 2026
**Etat**: ✅ Production Ready (localement)
