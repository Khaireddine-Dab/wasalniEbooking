<?php
/**
 * Page de confirmation
 * Cette page affiche la confirmation de la réservation après paiement
 */

// Inclure les fonctions communes
require_once __DIR__ . '../functions.php';

// Vérifier si l'utilisateur est connecté
$isLoggedIn = isLoggedIn();

// Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
if (!$isLoggedIn) {
    header('Location: login.php');
    exit;
}

// Vérifier si une confirmation est disponible
if (!isset($_SESSION['confirmation'])) {
    header('Location: confirmation.php');
    exit;
}

// Récupérer les données de confirmation
$confirmation = $_SESSION['confirmation'];
$reservation = $confirmation['reservation'];

// Formater la date pour l'affichage
$formattedDate = date('d/m/Y', strtotime($reservation['departDate']));

// Définir le titre de la page
$pageTitle = "Confirmation - Wasalni Ebooking";

// Inclure l'en-tête
include_once __DIR__ . '../header.php';
?>

<!-- Main Content -->
<div class="container py-5">
    <!-- Progress Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-bold text-success">Sélection</span>
                        <span class="fw-bold text-success">Détails</span>
                        <span class="fw-bold text-success">Paiement</span>
                        <span class="fw-bold text-success">Confirmation</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar" role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card text-center mb-4">
                <div class="card-body p-5">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                    </div>
                    <h2 class="card-title mb-3">Réservation Confirmée!</h2>
                    <p class="card-text mb-4">Votre paiement a été traité avec succès et votre réservation est confirmée.</p>
                    
                    <div class="alert alert-success mb-4">
                        <h5 class="mb-2">Numéro de Réservation</h5>
                        <h3 class="mb-0"><?php echo escape($confirmation['number']); ?></h3>
                    </div>
                    
                    <p class="text-muted mb-4">Un email de confirmation a été envoyé à votre adresse email avec tous les détails de votre réservation.</p>
                    
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Détails de la Réservation</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6 text-md-end fw-bold">Itinéraire:</div>
                                <div class="col-md-6 text-md-start"><?php echo escape($reservation['fromCity']); ?> → <?php echo escape($reservation['toCity']); ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6 text-md-end fw-bold">Date et Heure:</div>
                                <div class="col-md-6 text-md-start"><?php echo $formattedDate; ?> à <?php echo escape($reservation['departTime']); ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6 text-md-end fw-bold">Nombre de Passagers:</div>
                                <div class="col-md-6 text-md-start"><?php echo escape($reservation['passengers']); ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6 text-md-end fw-bold">Type de Louage:</div>
                                <div class="col-md-6 text-md-start"><?php echo escape($reservation['louageType']); ?></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 text-md-end fw-bold">Montant Total:</div>
                                <div class="col-md-6 text-md-start"><?php echo number_format($reservation['grandTotal'], 3); ?> DT</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning mb-4">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Veuillez présenter votre numéro de réservation lors de votre départ.
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="mesReservations.php" class="btn btn-primary">Voir mes réservations</a>
                        <a href="codeHtmlPrincipale.html" class="btn btn-outline-secondary">Retour à l'accueil</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border-radius: 15px;
        border: none;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    }
    .card-header {
        border-radius: 15px 15px 0 0 !important;
        background-color: white;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    .progress-bar {
        background-color: #198754;
    }
</style>

<?php
// Supprimer les données de confirmation de la session après affichage
unset($_SESSION['confirmation']);

// Inclure le pied de page
include_once __DIR__ . '../footer.php';
?>
