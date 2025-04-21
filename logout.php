<?php
/**
 * Page de déconnexion
 * Cette page permet aux utilisateurs de se déconnecter du système
 */

// Inclure les fonctions communes
require_once __DIR__ . '../functions.php';

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Détruire la session
session_unset();
session_destroy();

// Rediriger vers la page d'accueil
redirect('codeHtmlPrincipale.html');
?>
