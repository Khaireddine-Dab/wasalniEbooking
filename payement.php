<?php
/**
 * Page de paiement
 * Cette page permet aux utilisateurs de payer leur réservation
 */

// Inclure les fonctions communes
session_start();
require_once __DIR__ . '../functions.php';

// Vérifier si l'utilisateur est connecté
$isLoggedIn = isLoggedIn();

// Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
if (!$isLoggedIn) {
    header('Location: login.php?redirect=payement.php');
    exit;
}







// Vérifier si une réservation est en cours
if (!isset($_SESSION['reservation'])) {
    header('Location: reservation.php');
    exit;
}

// Récupérer les données de réservation
$reservation = $_SESSION['reservation'];

// Traitement du formulaire de paiement
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentMethod = $_POST['paymentMethod'] ?? '';
    
    if (empty($paymentMethod)) {
        $error = "Veuillez sélectionner une méthode de paiement.";
    } else {
        try {
            $db = getDbConnection();
            
            // Mettre à jour le statut de la réservation
            $stmt = $db->prepare("
                UPDATE reservations 
                SET status = 'paid', updated_at = NOW() 
                WHERE id = :reservationId
            ");
            $stmt->bindParam(':paymentMethod', $paymentMethod);
            $stmt->bindParam(':reservationId', $reservation['id']);
            
            if ($stmt->execute()) {
                // Créer une entrée dans la table des paiements
                $stmt = $db->prepare("
                    INSERT INTO payments (
                        reservation_id, user_id, amount, payment_method, status, created_at
                    ) VALUES (
                        :reservationId, :userId, :amount, :paymentMethod, 'completed', NOW()
                    )
                ");
                
                $userId = $_SESSION['user_id'];
                $stmt->bindParam(':reservationId', $reservation['id']);
                $stmt->bindParam(':userId', $userId);
                $stmt->bindParam(':amount', $reservation['grandTotal']);
                $stmt->bindParam(':paymentMethod', $paymentMethod);
                
                if ($stmt->execute()) {
                    // Générer un numéro de confirmation
                    $confirmationNumber = 'WAS-' . str_pad($reservation['id'], 6, '0', STR_PAD_LEFT);
                    
                    // Stocker le numéro de confirmation dans la session
                    $_SESSION['confirmation'] = [
                        'number' => $confirmationNumber,
                        'reservation' => $reservation
                    ];
                    
                    // Supprimer les données de réservation de la session
                    unset($_SESSION['reservation']);

                    if (headers_sent()) {
                        error_log("Erreur : Les en-têtes HTTP ont déjà été envoyés.");
                        exit;
                    }




                    
                    // Rediriger vers la page de confirmation
                    header('Location: confirmation.php');
                    exit;
                    if ($_SERVER["REQUEST_METHOD"] == "post") {
                        header('Location: confirmation.php');
                        exit();
                    }
                } else {
                    $error = "Une erreur est survenue lors de l'enregistrement du paiement.";
                }
            } else {
                $error = "Une erreur est survenue lors de la mise à jour de la réservation.";
            }
        } catch (PDOException $e) {
            $error = "Une erreur est survenue lors du traitement du paiement. Veuillez réessayer.";
            // En production, il faudrait logger l'erreur
        }
    }
}


// Formater la date pour l'affichage
$formattedDate = date('d/m/Y', strtotime($reservation['departDate']));


// Définir le titre de la page
$pageTitle = "Paiement - Wasalni Ebooking";


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
                        <span class="text-muted">Confirmation</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar" role="progressbar" style="width: 75%;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Payment Form -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header p-4">
                    <div class="d-flex align-items-center">
                        <div class="payment-icon me-3">
                            <i class="bi bi-credit-card"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Méthode de Paiement</h5>
                            <p class="text-muted mb-0">Choisissez votre méthode de paiement préférée</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="needs-validation" novalidate>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="card payment-method-card active p-3" data-payment="card">
                                    <div class="d-flex align-items-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="paymentMethod " id="cardPayment" value="card" >
                                            <label class="form-check-label" for="cardPayment"></label>
                                        </div>
                                        <div class="ms-2 d-flex align-items-center">
                                            <div class="payment-logo logo-card">
                                                <i class="bi bi-credit-card"></i>
                                            </div>
                                            <span>Carte Bancaire</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card payment-method-card p-3" data-payment="d17">
                                    <div class="d-flex align-items-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="paymentMethod" id="d17Payment" value="d17">
                                            <label class="form-check-label" for="d17Payment"></label>
                                        </div>
                                        <div class="ms-2 d-flex align-items-center">
                                            <div class="payment-logo logo-d17">D17</div>
                                            <span>D17</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card payment-method-card p-3" data-payment="wallet">
                                    <div class="d-flex align-items-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="paymentMethod" id="walletPayment" value="wallet">
                                            <label class="form-check-label" for="walletPayment"></label>
                                        </div>
                                        <div class="ms-2 d-flex align-items-center">
                                            <div class="payment-logo logo-wallet">
                                                <i class="bi bi-wallet2"></i>
                                            </div>
                                            <span>E-Wallet</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Payment Form -->
                        <div id="cardPaymentForm" class="payment-form-container">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="cardNumber" class="form-label">Numéro de Carte</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="cardNumber" placeholder="1234 5678 9012 3456" required>
                                        <span class="input-group-text bg-white">
                                            <i class="bi bi-credit-card text-success"></i>
                                        </span>
                                    </div>
                                    <div class="invalid-feedback">
                                        Veuillez entrer un numéro de carte valide.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="cardName" class="form-label">Nom sur la Carte</label>
                                    <input type="text" class="form-control" id="cardName" placeholder="Prénom Nom" required>
                                    <div class="invalid-feedback">
                                        Veuillez entrer le nom figurant sur la carte.
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="expiryDate" class="form-label">Date d'Expiration</label>
                                    <input type="text" class="form-control" id="expiryDate" placeholder="MM/AA" required>
                                    <div class="invalid-feedback">
                                        Veuillez entrer une date d'expiration valide.
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="cvv" class="form-label">CVV</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="cvv" placeholder="123" required>
                                        <span class="input-group-text bg-white" data-bs-toggle="tooltip" data-bs-placement="top" title="Le code de sécurité à 3 chiffres au dos de votre carte">
                                            <i class="bi bi-question-circle text-success"></i>
                                        </span>
                                    </div>
                                    <div class="invalid-feedback">
                                        Veuillez entrer un code CVV valide.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- D17 Payment Form -->
                        <div id="d17PaymentForm" class="payment-form-container d-none">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="alert alert-info d-flex align-items-center" role="alert">
                                        <i class="bi bi-info-circle-fill me-2"></i>
                                        <div>
                                            Vous allez être redirigé vers la plateforme D17 pour finaliser votre paiement.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="d17Phone" class="form-label">Numéro de téléphone D17</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">+216</span>
                                        <input type="tel" class="form-control" id="d17Phone" placeholder="XX XXX XXX" required>
                                    </div>
                                    <div class="invalid-feedback">
                                        Veuillez entrer un numéro de téléphone valide.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="d17Email" class="form-label">Email associé à D17</label>
                                    <input type="email" class="form-control" id="d17Email" placeholder="vous@exemple.com" required>
                                    <div class="invalid-feedback">
                                        Veuillez entrer une adresse email valide.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Wallet Payment Form -->
                        <div id="walletPaymentForm" class="payment-form-container d-none">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="walletId" class="form-label">Identifiant E-Wallet</label>
                                    <input type="text" class="form-control" id="walletId" placeholder="Votre identifiant" required>
                                    <div class="invalid-feedback">
                                        Veuillez entrer votre identifiant E-Wallet.
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="walletPassword" class="form-label">Mot de passe</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="walletPassword" placeholder="Votre mot de passe" required>
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback">
                                        Veuillez entrer votre mot de passe.
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                                        <i class="bi bi-shield-lock-fill me-2"></i>
                                        <div>
                                            Vos informations de connexion sont sécurisées et ne seront pas stockées.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="d-grid gap-2 mt-4">
                                <button  type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-lock-fill me-2"></i>Confirmer et Payer
                                </button>
                            </div>    
                    </form>
                
                    
                </div>
            </div>
        </div>



        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 20px; z-index: 1;">
                <div class="card-header p-4">
                    <h5 class="mb-0">Résumé de la Commande</h5>
                </div>
                <div class="card-body p-4">
                    <div class="ticket-info mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-geo-alt text-success me-2"></i>
                            <h6 class="mb-0">Itinéraire</h6>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <div>
                                <p class="mb-0 fw-bold"><?php echo escape($reservation['fromCity']); ?></p>
                                <small class="text-muted">Station Centrale</small>
                            </div>
                            <div class="text-center">
                                <i class="bi bi-arrow-right"></i>
                            </div>
                            <div class="text-end">
                                <p class="mb-0 fw-bold"><?php echo escape($reservation['toCity']); ?></p>
                                <small class="text-muted">Station Centrale</small>
                            </div>
                        </div>
                        <hr>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-calendar-date text-success me-2"></i>
                                    <div>
                                        <small class="text-muted d-block">Date</small>
                                        <span><?php echo $formattedDate; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-clock text-success me-2"></i>
                                    <div>
                                        <small class="text-muted d-block">Heure</small>
                                        <span><?php echo escape($reservation['departTime']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-people text-success me-2"></i>
                                    <div>
                                        <small class="text-muted d-block">Places</small>
                                        <span><?php echo escape($reservation['passengers']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-car-front text-success me-2"></i>
                                    <div>
                                        <small class="text-muted d-block">Type</small>
                                        <span><?php echo escape($reservation['louageType']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Prix unitaire</span>
                        <span><?php echo number_format($reservation['basePrice'], 3); ?> DT</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Nombre de places</span>
                        <span><?php echo escape($reservation['passengers']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Sous-total</span>
                        <span><?php echo number_format($reservation['totalPrice'], 3); ?> DT</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Frais de service</span>
                        <span><?php echo number_format($reservation['serviceFee'], 3); ?> DT</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold">Total</span>
                        <span class="fw-bold fs-4"><?php echo number_format($reservation['grandTotal'], 3); ?> DT</span>
                    </div>

                    <div class="d-flex justify-content-center">
                        <span class="secure-badge">
                            <i class="bi bi-shield-check me-1"></i>Paiement 100% Sécurisé
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>




<script>
    // Form validation script
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        
        // Payment method selection
        const paymentCards = document.querySelectorAll('.payment-method-card');
        const paymentRadios = document.querySelectorAll('input[name="paymentMethod"]');
        const paymentForms = {
            'card': document.getElementById('cardPaymentForm'),
            'd17': document.getElementById('d17PaymentForm'),
            'wallet': document.getElementById('walletPaymentForm')
        };
        
        // Function to show the selected payment form and hide others
        function showPaymentForm(paymentType) {
            for (const [type, form] of Object.entries(paymentForms)) {
                if (type === paymentType) {
                    form.classList.remove('d-none');
                } else {
                    form.classList.add('d-none');
                }
            }
        }
        
        paymentCards.forEach((card) => {
            card.addEventListener('click', () => {
                // Remove active class from all cards
                paymentCards.forEach(c => c.classList.remove('active'));
                // Add active class to clicked card
                card.classList.add('active');
                
                // Get payment type from data attribute
                const paymentType = card.dataset.payment;
                
                // Check the corresponding radio button
                document.getElementById(paymentType + 'Payment').checked = true;
                
                // Show the corresponding payment form
                showPaymentForm(paymentType);
            });
        });
        
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('walletPassword');
        
        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function () {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.querySelector('i').classList.toggle('bi-eye');
                this.querySelector('i').classList.toggle('bi-eye-slash');
            });
        }
    })()
</script>

<style>
    :root {
        --primary-color: #198754;
        --secondary-color: #f8f9fa;
    }
    body {
        background-color: #f0f2f5;
    }
    .navbar-brand {
        font-weight: 700;
        color: var(--primary-color);
    }
    .btn-success {
        background-color: var(--primary-color);
    }
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
    .form-control, .form-select {
        border-radius: 10px;
        padding: 12px;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
    }
    .payment-icon {
        font-size: 1.5rem;
        color: var(--primary-color);
    }
    .progress-bar {
        background-color: var(--primary-color);
    }
    .payment-method-card {
        cursor: pointer;
        transition: all 0.3s;
        border: 2px solid transparent;
        height: 100%;
    }
    .payment-method-card:hover, .payment-method-card.active {
        border-color: var(--primary-color);
        background-color: rgba(25, 135, 84, 0.05);
    }
    .ticket-info {
        background-color: rgba(25, 135, 84, 0.05);
        border-radius: 10px;
        padding: 15px;
    }
    .secure-badge {
        background-color: rgba(25, 135, 84, 0.1);
        color: var(--primary-color);
        border-radius: 50px;
        padding: 5px 15px;
    }
    .payment-logo {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        margin-right: 10px;
        color: white;
        font-weight: bold;
    }
    .logo-d17 {
        background-color: #FF6B00;
    }
    .logo-wallet {
        background-color: #6610f2;
    }
    .logo-card {
        background-color: #0d6efd;
    }
    .payment-form-container {
        transition: all 0.3s ease;
    }
</style>

<?php
// Inclure le pied de page
include_once __DIR__ . '../footer.php';
?>
