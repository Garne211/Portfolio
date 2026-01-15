# 🔒 Documentation de Sécurité

## Améliorations de Sécurité Implémentées

### 1. **Connexion à la Base de Données** (includes/db.php)
✅ **Paramètres PDO Sécurisés:**
- `ATTR_EMULATE_PREPARES = false` - Utilise les vraies requêtes préparées
- `ATTR_PERSISTENT = false` - Pas de connexion persistante
- `MYSQL_ATTR_INIT_COMMAND` - Initialise le charset UTF-8

✅ **Gestion d'Erreurs:**
- Les erreurs ne sont PAS exposées aux utilisateurs
- Les détails d'erreur sont loggés côté serveur
- Message générique affiché à l'utilisateur

### 2. **Authentification & Sessions** (admin/login.php)
✅ **Cookies de Session:**
- `httponly = true` - Protège contre les attaques XSS
- `samesite = Strict` - Protège contre les attaques CSRF
- Durée: 3 minutes (short-lived tokens)
- Renouvellement automatique toutes les 2 minutes

✅ **Rate Limiting (Force Brute Protection):**
- Maximum 5 tentatives par IP en 15 minutes
- Temps d'attente forcé après dépassement
- Enregistrement de toutes les tentatives échouées

✅ **Tokens de Session:**
- `session_token` - Token d'accès actuel (32 octets aléatoires)
- `refresh_token` - Token pour renouveler (32 octets aléatoires)
- Stockés en base de données pour validation

### 3. **Upload de Fichiers** (includes/helpers.php)
✅ **Protections Contre Path Traversal:**
- Validation du chemin avec `realpath()`
- Vérification que le fichier est dans le dossier uploads
- Noms de fichiers générés aléatoirement avec `random_bytes(16)`

✅ **Validation MIME et Extensions:**
- Vérification du type MIME avec `finfo_file()`
- Liste blanche d'extensions: jpg, jpeg, png, gif, pdf, svg, webp
- Validation de la taille (max 5MB par défaut)

✅ **Permissions Fichier:**
- Permissions fixées à 0644 après upload
- Pas de permissions exécutables

### 4. **Redirections** (includes/helpers.php - function redirect)
✅ **Protection Contre Open Redirect:**
- Vérification que l'URL est relative ou du même domaine
- Blocage des redirections vers domaines externes
- Redirection par défaut vers /Portfolio/admin/dashboard.php

### 5. **Contrôle d'Accès**
✅ **Rôles Utilisateur:**
- `admin` - Accès complet
- `viewer` - Lecture seule (pas de modification)
- Fonction `requireEditAccess()` sur toutes les actions de modification

✅ **Autorisation sur les Pages:**
- Vérification sur CHAQUE POST request
- Vérification sur DELETE et UPDATE
- Page d'erreur 403 affichée 5 secondes avant redirection

### 6. **Protection CSRF**
✅ **Tokens CSRF:**
- Générés avec `random_bytes(32)`
- Validés sur chaque POST request
- Stockés en session

### 7. **Logging & Audit** (includes/logger.php)
✅ **Logging Centralisé:**
- 5 niveaux: ERROR, WARNING, INFO, DEBUG, SECURITY
- Logs séparés par jour et par niveau
- Enregistrement: timestamp, IP, utilisateur, action, contexte
- Logs stockés dans `/uploads/logs/`

✅ **Événements Loggés:**
- Tentatives de connexion (succès/échec)
- Actions de modification (CREATE, UPDATE, DELETE)
- Accès refusés
- Erreurs de base de données

---

## 🚀 Prochaines Améliorations Recommandées

### À Court Terme:
1. **HTTPS/TLS** - Activer en production (mettre `secure = true` dans cookies)
2. **CAPTCHA** - Sur le formulaire de connexion
3. **2FA** - Authentification multi-facteurs
4. **Content Security Policy (CSP)** - Header de sécurité

### À Moyen Terme:
1. **Password Policy** - Forcer changement de mot de passe
2. **Session Timeout Réel** - Logout à l'expiration
3. **Audit Trail Complet** - Tracker chaque modification
4. **Backup Automatique** - Sauvegardes régulières

### À Long Terme:
1. **WAF** - Web Application Firewall
2. **Monitoring** - Alertes sur événements suspects
3. **Pentesting** - Test de sécurité professionnel
4. **Certification SSL** - Certificat valide

---

## 📋 Checklist de Sécurité

- [x] Requêtes SQL préparées (PDO)
- [x] Échappement HTML (htmlspecialchars)
- [x] Protection CSRF (tokens)
- [x] Rate limiting (login)
- [x] Gestion d'erreurs sécurisée (pas de fuites)
- [x] Validation des uploads (MIME, extension, path)
- [x] Contrôle d'accès (rôles)
- [x] Logging & audit
- [x] Cookies sécurisés (httponly, samesite)
- [x] Sessions courtes (3 minutes)
- [x] Protection open redirect
- [ ] HTTPS (À faire)
- [ ] 2FA (À faire)
- [ ] CAPTCHA (À faire)
- [ ] CSP headers (À faire)

---

## 🔑 Identifiants de Test

**Admin:**
- Utilisateur: `admin` / Mot de passe: `admin`
- Accès complet au panel

**Guest (Lecture seule):**
- Utilisateur: `guest` / Mot de passe: `guest`
- Lecture seule, pas de modification

---

## 📞 Support

Pour toute question de sécurité:
1. Vérifier les logs dans `/uploads/logs/`
2. Consulter la documentation des fonctions
3. Tester en mode développement avant de déployer

