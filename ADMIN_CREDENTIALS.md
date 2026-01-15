# 🔐 Identifiants Admin Portfolio

## ✅ Compte Admin Par Défaut

### Après l'initialisation de la BD:

```
Username: admin
Password: admin123456
```

---

## 🚀 Comment Se Connecter

1. **Aller à l'admin**: `http://localhost/Portfolio/admin/`
2. **Entrer les identifiants**:
   - Identifiant: `admin`
   - Mot de passe: `admin123456`
3. **Cliquer sur "Se connecter"**

---

## ⚠️ IMPORTANT - Sécurité

### ❌ Ne pas garder ces identifiants par défaut

1. **Après la première connexion**, allez à:
   - ⚙️ Admin Panel → Profil → Changer le mot de passe

2. **Créez un nouveau mot de passe**:
   - ✅ Fort (min 12 caractères)
   - ✅ Avec majuscules, minuscules, chiffres, symboles
   - ✅ Unique et difficile à deviner

---

## 🔑 Changer le Mot de Passe

### Via l'interface admin:
1. Connectez-vous avec `admin`/`admin123456`
2. Cliquez sur votre profil
3. Sélectionnez "Changer le mot de passe"
4. Entrez l'ancien et le nouveau mot de passe
5. Validez

### Via la BD (si oublié):
```php
// Script pour réinitialiser le mot de passe
php -r "
\$hash = password_hash('nouveau_motdepasse', PASSWORD_BCRYPT);
echo 'Hash: ' . \$hash;
"
```

Puis exécutez dans SQLite:
```sql
UPDATE admin_users SET password = '[HASH]' WHERE username = 'admin';
```

---

## 📋 Vérifier la Création du Compte

Après initialisation, vérifiez que le compte a bien été créé:

1. Allez à: `http://localhost/Portfolio/install.html`
2. Cliquez sur "Initialiser la BD"
3. Vous devriez voir:
   ```
   ✅ Base de données initialisée avec succès!
   
   Compte Admin créé:
   Username: admin
   Password: admin123456
   ⚠️ Changez ce mot de passe après votre première connexion!
   ```

---

## 🆘 Problèmes Courants

### "Identifiants incorrects"
- ✓ Vérifiez l'orthographe (majuscules/minuscules)
- ✓ Assurez-vous que la BD a été initialisée
- ✓ Vérifiez que la table `admin_users` existe

### "Erreur de connexion à la base de données"
- ✓ Vérifiez que `databases/portfolio.db` existe
- ✓ Vérifiez les permissions (chmod 775)
- ✓ Exécutez `init_database.php` à nouveau

### "Table admin_users manquante"
- ✓ Visitez: `http://localhost/Portfolio/install.html`
- ✓ Cliquez sur "Initialiser la BD"
- ✓ Les tables seront créées automatiquement

---

## 🔒 Bonnes Pratiques de Sécurité

1. ✅ **Mot de passe fort**: min 12 caractères
2. ✅ **Unique**: Ne pas réutiliser avec d'autres services
3. ✅ **Changement régulier**: Chaque 3 mois environ
4. ✅ **HTTPS**: Utilisez SSL/TLS en production
5. ✅ **2FA**: Envisager l'authentification 2 facteurs

---

**Dernière mise à jour**: Janvier 2026
