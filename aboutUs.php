<?php
/**
 * Page À propos de nous
 * Cette page contient les informations sur l'entreprise
 */

// Inclure les fonctions communes
require_once __DIR__ . '../functions.php';

// Définir le titre de la page
$pageTitle = "À propos de nous - Wasalni Ebooking";

// Inclure l'en-tête
include_once __DIR__ . '../header.php';
?>

<div class="container my-5">
    <h1 class="mb-4">À propos de Wasalni Ebooking</h1>
    
    <div class="row mb-5">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="card-title">Notre mission</h2>
                    <p class="card-text">Chez Wasalni Ebooking, notre mission est de simplifier et d'améliorer l'expérience de réservation de louages en Tunisie. Nous visons à connecter les voyageurs avec des services de transport fiables et confortables, tout en offrant une plateforme facile à utiliser pour planifier vos déplacements.</p>
                    <p class="card-text">Nous nous engageons à fournir un service de qualité, des prix transparents et une expérience client exceptionnelle pour tous vos besoins de déplacement en Tunisie.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="card-title">Notre histoire</h2>
                    <p class="card-text">Fondée en 2023, Wasalni Ebooking est née de la volonté de moderniser le système traditionnel de louages en Tunisie. Face aux défis rencontrés par les voyageurs pour trouver et réserver des louages fiables, notre équipe a décidé de créer une solution innovante.</p>
                    <p class="card-text">Depuis notre lancement, nous avons aidé des milliers de voyageurs à se déplacer facilement entre les villes tunisiennes, en leur offrant une alternative pratique aux méthodes de réservation traditionnelles.</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-5">
        <div class="card-body">
            <h2 class="card-title">Pourquoi choisir Wasalni Ebooking ?</h2>
            <div class="row g-4 mt-3">
                <div class="col-md-4">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="bi bi-check-circle-fill text-success fs-2 me-3"></i>
                        </div>
                        <div>
                            <h4>Réservation facile</h4>
                            <p>Notre plateforme intuitive vous permet de réserver votre louage en quelques clics, à tout moment et de n'importe où.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="bi bi-currency-exchange text-success fs-2 me-3"></i>
                        </div>
                        <div>
                            <h4>Prix transparents</h4>
                            <p>Nous affichons clairement tous les tarifs, sans frais cachés, pour vous permettre de planifier votre budget en toute confiance.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="bi bi-shield-check text-success fs-2 me-3"></i>
                        </div>
                        <div>
                            <h4>Sécurité garantie</h4>
                            <p>Tous nos partenaires sont soigneusement sélectionnés pour assurer votre sécurité et votre confort pendant le voyage.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="bi bi-clock-history text-success fs-2 me-3"></i>
                        </div>
                        <div>
                            <h4>Gain de temps</h4>
                            <p>Plus besoin de vous déplacer à la station de louage à l'avance pour réserver votre place.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="bi bi-star-fill text-success fs-2 me-3"></i>
                        </div>
                        <div>
                            <h4>Service client</h4>
                            <p>Notre équipe de support est disponible pour vous aider et répondre à toutes vos questions.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="bi bi-geo-alt-fill text-success fs-2 me-3"></i>
                        </div>
                        <div>
                            <h4>Large couverture</h4>
                            <p>Nous couvrons les principales villes et destinations touristiques de Tunisie.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-5">
        <div class="card-body">
            <h2 class="card-title">Notre équipe</h2>
            <p>Wasalni Ebooking est composée d'une équipe passionnée de professionnels tunisiens dédiés à améliorer l'expérience de transport en Tunisie. Notre équipe combine expertise technique, connaissance approfondie du secteur des transports et engagement envers l'excellence du service client.</p>
            
            <div class="row g-4 mt-3">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <img src="https://via.placeholder.com/150" alt="Photo de profil" class="rounded-circle mb-3">
                            <h4>Ahmed Charfi</h4>
                            <p class="text-muted">Fondateur & CEO</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <img src="https://via.placeholder.com/150" alt="Photo de profil" class="rounded-circle mb-3">
                            <h4>Sarra Ben Ali</h4>
                            <p class="text-muted">Directrice des Opérations</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <img src="https://via.placeholder.com/150" alt="Photo de profil" class="rounded-circle mb-3">
                            <h4>Mohamed Trabelsi</h4>
                            <p class="text-muted">Responsable Technique</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <h2 class="card-title">Contactez-nous</h2>
            <p>Vous avez des questions ou des suggestions ? N'hésitez pas à nous contacter :</p>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <h4><i class="bi bi-geo-alt me-2"></i>Adresse</h4>
                    <p>TUNISIA MEDNINE DJERBA</p>
                    
                    <h4><i class="bi bi-telephone me-2"></i>Téléphone</h4>
                    <p>+216 93 249 073</p>
                    
                    <h4><i class="bi bi-envelope me-2"></i>Email</h4>
                    <p>contact@wasalniEbookingTN.com</p>
                </div>
                <div class="col-md-6">
                    <a href="contact.php" class="btn btn-primary">Nous contacter</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Inclure le pied de page
include_once __DIR__ . '../footer.php';
?>
