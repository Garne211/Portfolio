<?php
/**
 * Script: Renommer anonymous en guest
 */

require_once __DIR__ . '/includes/db.php';

try {
    $db = getDBConnection();
    
    // Vérifier si guest existe déjà
    $checkGuest = $db->prepare("SELECT id FROM admin_users WHERE username = 'guest' LIMIT 1");
    $checkGuest->execute();
    
    if ($checkGuest->rowCount() > 0) {
        echo "❌ L'utilisateur 'guest' existe déjà.\n";
        exit(1);
    }
    
    // Vérifier si anonymous existe
    $checkAnon = $db->prepare("SELECT id FROM admin_users WHERE username = 'anonymous' LIMIT 1");
    $checkAnon->execute();
    
    if ($checkAnon->rowCount() === 0) {
        echo "❌ L'utilisateur 'anonymous' n'existe pas.\n";
        exit(1);
    }
    
    // Renommer anonymous en guest
    $updateStmt = $db->prepare("UPDATE admin_users SET username = 'guest' WHERE username = 'anonymous'");
    $updateStmt->execute();
    
    echo "✅ Utilisateur renommé avec succès!\n";
    echo "📝 Nouveaux identifiants:\n";
    echo "   Utilisateur: guest\n";
    echo "   Mot de passe: guest\n";
    echo "   Rôle: viewer (lecture seule)\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
