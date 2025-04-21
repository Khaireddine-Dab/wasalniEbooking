<?php
/**
 * Configuration de la base de données
 * Ce fichier contient les paramètres de connexion à la base de données
 */

// Paramètres de connexion
define('DB_HOST', 'localhost');
define('DB_NAME', 'wasalni_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Connexion à la base de données avec PDO
function getDbConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // En production, il faudrait logger l'erreur plutôt que de l'afficher
        die("Erreur de connexion à la base de données: " . $e->getMessage());
    }
}
