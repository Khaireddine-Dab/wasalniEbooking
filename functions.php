<?php
/**
 * Fichier d'inclusion pour les fonctions communes
 * Ce fichier contient les fonctions utilitaires et les configurations communes
 */

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure la configuration de la base de données
require_once __DIR__ . '../db_config.php';

/**
 * Fonction pour vérifier si l'utilisateur est connecté
 * @return bool Retourne true si l'utilisateur est connecté, false sinon
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Fonction pour vérifier si l'utilisateur est un administrateur
 * @return bool Retourne true si l'utilisateur est un administrateur, false sinon
 */
function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

/**
 * Fonction pour rediriger vers une autre page
 * @param string $page La page vers laquelle rediriger
 */
function redirect($page) {
    header("Location: $page");
    exit;
}

/**
 * Fonction pour échapper les données avant affichage
 * @param string $data Les données à échapper
 * @return string Les données échappées
 */
function escape($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Fonction pour générer un token CSRF
 * @return string Le token CSRF généré
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Fonction pour vérifier un token CSRF
 * @param string $token Le token à vérifier
 * @return bool Retourne true si le token est valide, false sinon
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Fonction pour afficher un message d'erreur
 * @param string $message Le message d'erreur à afficher
 */
function displayError($message) {
    return '<div class="alert alert-danger">' . $message . '</div>';
}

/**
 * Fonction pour afficher un message de succès
 * @param string $message Le message de succès à afficher
 */
function displaySuccess($message) {
    return '<div class="alert alert-success">' . $message . '</div>';
}
