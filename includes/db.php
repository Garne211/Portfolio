<?php
/**
 * Connexion à la base de données MySQL
 * Utilise les variables d'environnement ou les valeurs par défaut
 */

function getDBConnection() {
    // Configuration MySQL
    $host = getenv('DB_HOST') ?: 'localhost';
    $user = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';
    $database = getenv('DB_NAME') ?: 'portfolio';
    $port = (int) (getenv('DB_PORT') ?: 3306);
    
    try {
        $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false, // Utiliser les prepares réelles
            PDO::ATTR_PERSISTENT => false, // Pas de connexion persistante par défaut
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ]);
        
        return $pdo;
    } catch (PDOException $e) {
        // Logger l'erreur réelle
        error_log('Database Connection Error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
        
        // Retourner une erreur générique à l'utilisateur
        http_response_code(500);
        die('Erreur de connexion à la base de données. Veuillez contacter l\'administrateur.');
    }
}