# 🔧 Dépannage Connexion Admin

## ❌ Vous ne pouvez pas vous connecter?

Suivez ces étapes pour diagnostiquer le problème :

---

## 📋 Checklist Diagnostic

### 1. Vérifier que la BD a été initialisée
```
✓ Aller à: http://localhost/Portfolio/install.html
✓ Cliquer sur "Initialiser la BD"
✓ Attendre le message de succès
```

### 2. Vérifier les identifiants
```
Username: admin
Password: admin123456
```

### 3. Utiliser la page de diagnostic
```
🔍 Allez à: http://localhost/Portfolio/admin/diagnostic.php
```

Cette page vous montrera:
- ✓ Si la BD existe
- ✓ Si la table admin_users existe
- ✓ Si des utilisateurs sont enregistrés
- ✓ Vous pouvez tester les identifiants directement

---

## 🚀 Solution Rapide

1. **Ouvrir**: http://localhost/Portfolio/admin/diagnostic.php
2. **Tester les identifiants** dans le formulaire
3. **Voir le message** qui indique le problème

---

## ❓ Problèmes Courants

### Erreur: "Identifiants incorrects"
```
Causes possibles:
❌ La BD n'a pas été initialisée
❌ Mauvais mot de passe (vérifiez la casse)
❌ L'utilisateur admin n'existe pas
❌ La table admin_users est vide

Solution:
✅ Réinitialisez la BD: http://localhost/Portfolio/install.html
✅ Utilisez admin123456 (respectez la casse)
```

### Erreur: "Erreur de connexion à la base de données"
```
Causes possibles:
❌ Le fichier portfolio.db n'existe pas
❌ Permissions insuffisantes
❌ La BD est corrompue

Solution:
✅ Supprimer: databases/portfolio.db
✅ Réinitialiser: http://localhost/Portfolio/install.html
✅ Vérifier permissions: chmod 775 databases/
```

### La page de diagnostic montre "0 utilisateurs"
```
Causes possibles:
❌ L'initialisation n'a pas inséré le compte admin
❌ La BD a été réinitialisée sans utilisateur

Solution:
✅ Exécutez init_database.php directement
✅ Ou via: http://localhost/Portfolio/install.html
```

---

## 🛠️ Réinitialisation Complète

Si rien ne fonctionne, réinitialisez tout:

```bash
# 1. Supprimer la BD
rm databases/portfolio.db

# 2. Vérifier les permissions
chmod 775 databases/

# 3. Réinitialiser via le web
# Ouvrir: http://localhost/Portfolio/install.html
# Cliquer: "Initialiser la BD"
```

---

## 📞 Questions Fréquentes

### Q: Où est le mot de passe par défaut?
```
R: admin123456
   (créé lors de l'initialisation de la BD)
```

### Q: Comment changer le mot de passe?
```
R: Après connexion:
   Admin Panel → Profil → Changer le mot de passe
```

### Q: Oublié le mot de passe?
```
R: Réinitialisez la BD pour retrouver les identifiants par défaut
   http://localhost/Portfolio/install.html
```

### Q: Plusieurs utilisateurs?
```
R: Actuellement, seul l'utilisateur "admin" est créé par défaut
   Vous pouvez ajouter d'autres utilisateurs dans l'admin panel
```

---

## ✅ Après Connexion Réussie

Une fois connecté:

1. ✓ Allez à votre profil
2. ✓ **Changez le mot de passe par défaut**
3. ✓ Mettez à jour votre email
4. ✓ Configurez vos liens sociaux

---

## 📊 Vérifier l'Installation

Utilisez: http://localhost/Portfolio/status.php

Cela vous montrera:
- ✓ Version PHP
- ✓ Extensions requises
- ✓ Fichiers présents
- ✓ État de la BD

---

**Besoin d'aide?** Consultez le fichier diagnostic.php
