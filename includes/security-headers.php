<?php
/**
 * Headers de Sécurité
 * À appeler au début de chaque page
 */

function setSecurityHeaders() {
    // Empêcher le clickjacking
    header('X-Frame-Options: SAMEORIGIN', true);
    
    // Empêcher les attaques MIME sniffing
    header('X-Content-Type-Options: nosniff', true);
    
    // Empêcher les attaques XSS
    header('X-XSS-Protection: 1; mode=block', true);
    
    // Strict Transport Security (HTTPS only)
    // Décommenter en production avec HTTPS
    // header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload', true);
    
    // Content Security Policy
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self'", true);
    
    // Empêcher CORS
    header('Cross-Origin-Opener-Policy: same-origin', true);
    
    // Référrer Policy
    header('Referrer-Policy: strict-origin-when-cross-origin', true);
    
    // Permissions Policy (Feature Policy)
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()', true);
}

// Appel automatique
if (function_exists('header')) {
    setSecurityHeaders();
}
