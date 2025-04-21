<?php
/**
 * Fichier d'inclusion pour l'en-tête du site
 * Ce fichier contient l'en-tête HTML commun à toutes les pages
 */

// Inclure les fonctions communes
require_once __DIR__ . '../functions.php';

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? escape($pageTitle) : 'Wasalni Ebooking'; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #E63946;
            --secondary-color: #457B9D;
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover {
            background-color: #d32836;
            border-color: #d32836;
        }
        .text-primary {
            color: var(--primary-color) !important;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-car-front me-2"></i>
                wasalni Ebooking
            </a>
          
          <!-- Hamburger button for mobile -->
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                  aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          
          <!-- Navbar links -->
          <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
              <li class="nav-item">
                <a class="nav-link active" href="codeHtmlPrincipale.html">Home</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="mesReservations.php">Mes Réservations</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="contact.php">Contact Us</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="privacy.php">Privacy</a>
              </li>
            </ul>
            
            <!-- User menu and buttons -->
            <div class="d-flex align-items-center gap-2">
              <!-- User dropdown -->
              <div class="dropdown d-none d-lg-block">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" 
                        id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-person"></i> Mon Profil
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                  <li><a class="dropdown-item" href="monProfile.php">Mon Profil</a></li>
                  <li><a class="dropdown-item" href="mesReservations.php">Mes Réservations</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item text-danger " href="logout.php">Déconnexion</a></li>
                </ul>
              </div>
              
              <!-- Login button -->
              <a href="logIn.php" class="btn btn-outline-danger">
                <i class="bi bi-box-arrow-in-right"></i> LogIn
              </a>
              
              <!-- Reserve button -->
              <a href="reservation.php" class="btn btn-danger ms-2">Réserver</a>
            </div>
          </div>
        </div>
      </nav>
