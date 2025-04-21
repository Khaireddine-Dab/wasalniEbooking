<?php
/**
 * Page d'inscription
 * Cette page permet aux utilisateurs de créer un compte
 */

// Inclure les fonctions communes
require_once __DIR__ . '../functions.php';

// Initialiser les variables
$error = '';
$success = '';
$userType = 'client'; // Par défaut, l'onglet client est actif
$formData = [
    'firstName' => '',
    'lastName' => '',
    'email' => '',
    'phone' => '',
    'company' => '',
    'password' => '',
    'confirmPassword' => ''
];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $userType = $_POST['user_type'] ?? 'client';
    $formData = [
        'firstName' => $_POST['firstName'] ?? '',
        'lastName' => $_POST['lastName'] ?? '',
        'email' => $_POST['email'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'company' => $_POST['company'] ?? '',
        'password' => $_POST['password'] ?? '',
        'confirmPassword' => $_POST['confirmPassword'] ?? ''
    ];

    // Validation de base
    if (empty($formData['firstName']) || empty($formData['lastName']) || empty($formData['email']) || 
        empty($formData['phone']) || empty($formData['password']) || empty($formData['confirmPassword'])) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } elseif (!preg_match('/^[A-Za-zÀ-ÖØ-öø-ÿ]+$/', $formData['firstName'])) {
        $error = "Le prénom doit contenir uniquement des caractères alphabétiques.";
    } elseif (!preg_match('/^[A-Za-zÀ-ÖØ-öø-ÿ]+$/', $formData['lastName'])) {
        $error = "Le nom doit contenir uniquement des caractères alphabétiques.";
    } elseif (!preg_match('/^\d{8}$/', $formData['phone'])) {
        $error = "Le numéro de téléphone doit contenir exactement 8 chiffres.";
    } elseif ($formData['password'] !== $formData['confirmPassword']) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif ($userType === 'admin' && empty($formData['company'])) {
        $error = "Le nom de l'entreprise est obligatoire pour les administrateurs.";
    } elseif ($userType === 'admin' && (!isset($_POST['authCode']) || $_POST['authCode'] !== '123456')) {
        // Code d'autorisation admin (à remplacer par une vérification plus sécurisée)
        $error = "Code d'autorisation admin invalide.";
    } else {
        // Connexion à la base de données
        $db = getDbConnection();
        
        try {
            // Vérifier si l'email existe déjà
            $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->bindParam(':email', $formData['email']);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                $error = "Cette adresse email est déjà utilisée.";
            } else {
                // Hacher le mot de passe
                $hashedPassword = password_hash($formData['password'], PASSWORD_DEFAULT);
                
                // Préparer la requête d'insertion
                $stmt = $db->prepare("
                    INSERT INTO users (first_name, last_name, email, password, phone, company, user_type, created_at) 
                    VALUES (:firstName, :lastName, :email, :password, :phone, :company, :userType, NOW())
                ");
                
                // Lier les paramètres
                $stmt->bindParam(':firstName', $formData['firstName']);
                $stmt->bindParam(':lastName', $formData['lastName']);
                $stmt->bindParam(':email', $formData['email']);
                $stmt->bindParam(':password', $hashedPassword);
                $stmt->bindParam(':phone', $formData['phone']);
                $stmt->bindParam(':company', $formData['company']);
                $stmt->bindParam(':userType', $userType);
                
                // Exécuter la requête
                if ($stmt->execute()) {
                    $success = "Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.";
                    // Réinitialiser le formulaire
                    $formData = [
                        'firstName' => '',
                        'lastName' => '',
                        'email' => '',
                        'phone' => '',
                        'company' => '',
                        'password' => '',
                        'confirmPassword' => ''
                    ];
                } else {
                    $error = "Une erreur est survenue lors de la création du compte. Veuillez réessayer.";
                }
            }
        } catch (PDOException $e) {
            $error = "Une erreur est survenue lors de la création du compte. Veuillez réessayer.";
            // En production, il faudrait logger l'erreur
        }
    }
}

// Définir le titre de la page
$pageTitle = "Inscription - Wasalni Ebooking";

// Inclure l'en-tête
include_once __DIR__ . '../header.php';
?>

<div class="signup-container">
    <!-- Logo -->
    <div class="logo">
        <i class="bi bi-car-front-fill me-2"></i>wasalni Ebooking
    </div>

    <!-- Signup Card -->
    <div class="signup-card">
        <h2 class="text-center mb-4">Inscription</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo escape($error); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo escape($success); ?></div>
        <?php endif; ?>

        <!-- User Type Tabs -->
        <ul class="nav nav-pills mb-4 justify-content-center" id="userType" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo $userType === 'client' ? 'active' : ''; ?>" id="client-tab" data-bs-toggle="pill" data-bs-target="#client" type="button" role="tab">Client</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo $userType === 'admin' ? 'active' : ''; ?>" id="admin-tab" data-bs-toggle="pill" data-bs-target="#admin" type="button" role="tab">Admin</button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="userTypeContent">
            <!-- Client Registration -->
            <div class="tab-pane fade <?php echo $userType === 'client' ? 'show active' : ''; ?>" id="client" role="tabpanel">
                <!-- Social Signup -->
                <div class="d-grid gap-2 mb-3">
                    <button class="btn btn-google" type="button">
                        <i class="bi bi-google me-2"></i>S'inscrire avec Google
                    </button>
                    <button class="btn btn-facebook" type="button">
                        <i class="bi bi-facebook me-2"></i>S'inscrire avec Facebook
                    </button>
                </div>

                <div class="divider">
                    <span>Ou inscrivez-vous avec votre e-mail</span>
                </div>

                <form id="clientForm" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <input type="hidden" name="user_type" value="client">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="clientFirstName" class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="clientFirstName" name="firstName" value="<?php echo escape($formData['firstName']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="clientLastName" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="clientLastName" name="lastName" value="<?php echo escape($formData['lastName']); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="clientEmail" class="form-label">Adresse e-mail</label>
                        <input type="email" class="form-control" id="clientEmail" name="email" value="<?php echo escape($formData['email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="clientPhone" class="form-label">Numéro de téléphone</label>
                        <input type="tel" class="form-control" id="clientPhone" name="phone" value="<?php echo escape($formData['phone']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="clientPassword" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="clientPassword" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="clientConfirmPassword" class="form-label">Confirmer le mot de passe</label>
                        <input type="password" class="form-control" id="clientConfirmPassword" name="confirmPassword" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="clientTerms" name="terms" required>
                        <label class="form-check-label" for="clientTerms">
                            J'accepte les <a href="privacy.php" class="text-decoration-none">conditions d'utilisation</a> et la 
                            <a href="privacy.php" class="text-decoration-none">politique de confidentialité</a>
                        </label>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-danger">S'inscrire</button>
                    </div>
                </form>
            </div>

            <!-- Admin Registration -->
            <div class="tab-pane fade <?php echo $userType === 'admin' ? 'show active' : ''; ?>" id="admin" role="tabpanel">
                <form id="adminForm" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <input type="hidden" name="user_type" value="admin">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="adminFirstName" class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="adminFirstName" name="firstName" value="<?php echo escape($formData['firstName']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="adminLastName" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="adminLastName" name="lastName" value="<?php echo escape($formData['lastName']); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="adminEmail" class="form-label">Adresse e-mail professionnelle</label>
                        <input type="email" class="form-control" id="adminEmail" name="email" value="<?php echo escape($formData['email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="adminPhone" class="form-label">Numéro de téléphone professionnel</label>
                        <input type="tel" class="form-control" id="adminPhone" name="phone" value="<?php echo escape($formData['phone']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="adminCompany" class="form-label">Nom de l'entreprise</label>
                        <input type="text" class="form-control" id="adminCompany" name="company" value="<?php echo escape($formData['company']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="adminPassword" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="adminPassword" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="adminConfirmPassword" class="form-label">Confirmer le mot de passe</label>
                        <input type="password" class="form-control" id="adminConfirmPassword" name="confirmPassword" required>
                    </div>
                    <div class="mb-3">
                        <label for="adminAuthCode" class="form-label">Code d'autorisation admin</label>
                        <input type="text" class="form-control" id="adminAuthCode" name="authCode" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="adminTerms" name="terms" required>
                        <label class="form-check-label" for="adminTerms">
                            J'accepte les <a href="privacy.php" class="text-decoration-none">conditions d'utilisation</a> et la 
                            <a href="privacy.php" class="text-decoration-none">politique de confidentialité</a>
                        </label>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-danger">S'inscrire en tant qu'admin</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Login Link -->
        <div class="text-center mt-4">
            <p class="mb-0">Vous avez déjà un compte ?</p>
            <a href="logIn.php" class="text-decoration-none">Se connecter</a>
        </div>
    </div>

    <!-- Copyright -->
    <div class="text-center text-muted mt-4">
        <small>© 2025 wasalni Ebooking TN. Tous droits réservés.</small>
    </div>
</div>
<script>

// Validate forms for both admin and client
document.addEventListener('DOMContentLoaded', function () {
    // Function to validate alphabetic names
    function isAlphabetic(value) {
        return /^[A-Za-zÀ-ÖØ-öø-ÿ]+$/.test(value);
    }

    // Function to validate an 8-digit phone number
    function isValidPhoneNumber(value) {
        return /^\d{8}$/.test(value);
    }

    // Validate client form
    const clientForm = document.getElementById('clientForm');
    if (clientForm) {
        clientForm.addEventListener('submit', function (event) {
            const firstName = document.getElementById('clientFirstName').value.trim();
            const lastName = document.getElementById('clientLastName').value.trim();
            const phone = document.getElementById('clientPhone').value.trim();

            let isValid = true;

            if (!isAlphabetic(firstName)) {
                alert("❌ Name must contain only alphabetic characters.");
                isValid = false;
            }

            if (!isAlphabetic(lastName)) {
                alert("❌ firstName must contain only alphabetic characters.");
                isValid = false;
            }

            if (!isValidPhoneNumber(phone)) {
                alert("❌ Phone Number must contains 8 numbres.");
                isValid = false;
            }


        });
    }

    // Validate admin form
    const adminForm = document.getElementById('adminForm');
    if (adminForm) {
        adminForm.addEventListener('submit', function (event) {
            const firstName = document.getElementById('adminFirstName').value.trim();
            const lastName = document.getElementById('adminLastName').value.trim();
            const phone = document.getElementById('adminPhone').value.trim();

            let isValid = true;

            if (!isAlphabetic(firstName)) {
                alert("❌ Name must contain only alphabetic characters.");
            
                isValid = false;
            }

            if (!isAlphabetic(lastName)) {
                alert("❌ firstName must contain only alphabetic characters.");
                isValid = false;
            }

            if (!isValidPhoneNumber(phone)) {
                alert("❌ Phone Number must contain only alphabetic characters.");
                isValid = false;
            }

            if (!isValid) {
                event.preventDefault(); // Prevent form submission
            }
        });
    }
});

// Validate that the name fields are alphabetic
function isAlphabetic(value) {
    const regex = /^[A-Za-zÀ-ÖØ-öø-ÿ]+$/; // Supports accented characters
    return regex.test(value);
}

// Validate that the phone number is exactly 8 digits
function isValidPhoneNumber(value) {
    const regex = /^\d{8}$/;
    return regex.test(value);
}

// Validate the client form
function validateClientForm(event) {
    const firstName = document.getElementById('clientFirstName').value.trim();
    const lastName = document.getElementById('clientLastName').value.trim();
    const phone = document.getElementById('clientPhone').value.trim();

    let isValid = true;
    let errorMessage = '';

    if (!isAlphabetic(firstName)) {
        isValid = false;
        errorMessage += 'Le prénom doit être alphabétique.\n';
    }

    if (!isAlphabetic(lastName)) {
        isValid = false;
        errorMessage += 'Le nom doit être alphabétique.\n';
    }

    if (!isValidPhoneNumber(phone)) {
        isValid = false;
        errorMessage += 'Le numéro de téléphone doit contenir exactement 8 chiffres.\n';
    }


}

// Validate the admin form
function validateAdminForm(event) {
    const firstName = document.getElementById('adminFirstName').value.trim();
    const lastName = document.getElementById('adminLastName').value.trim();
    const phone = document.getElementById('adminPhone').value.trim();

    let isValid = true;
    let errorMessage = '';

    if (!isAlphabetic(firstName)) {
        isValid = false;
        errorMessage += 'Le prénom doit être alphabétique.\n';
    }

    if (!isAlphabetic(lastName)) {
        isValid = false;
        errorMessage += 'Le nom doit être alphabétique.\n';
    }

    if (!isValidPhoneNumber(phone)) {
        isValid = false;
        errorMessage += 'Le numéro de téléphone doit contenir exactement 8 chiffres.\n';
    }


}

// Attach validation to form submissions
document.getElementById('clientForm').addEventListener('submit', validateClientForm);
document.getElementById('adminForm').addEventListener('submit', validateAdminForm);
</script>

<style>
body {
    background-color: #f8f9fa;
    min-height: 100vh;
    padding: 20px;
}
.signup-container {
    max-width: 500px;
    margin: 0 auto;
}
.logo {
    color: #dc3545;
    font-size: 24px;
    font-weight: bold;
    text-align: center;
    margin-bottom: 20px;
}
.signup-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    padding: 30px;
}
.btn-google {
    background-color: #dc3545;
    color: white;
}
.btn-google:hover {
    background-color: #c82333;
    color: white;
}
.btn-facebook {
    background-color: #3b5998;
    color: white;
}
.btn-facebook:hover {
    background-color: #2d4373;
    color: white;
}
.divider {
    text-align: center;
    margin: 20px 0;
    position: relative;
}
.divider::before,
.divider::after {
    content: '';
    position: absolute;
    top: 50%;
    width: 45%;
    height: 1px;
    background-color: #dee2e6;
}
.divider::before {
    left: 0;
}
.divider::after {
    right: 0;
}
.divider span {
    background-color: white;
    padding: 0 10px;
    color: #6c757d;
    font-size: 0.9rem;
}
.nav-pills .nav-link.active {
    background-color: #dc3545;
}
.nav-pills .nav-link {
    color: #dc3545;
}
</style>

<script>
// Activer l'onglet approprié en fonction du type d'utilisateur
document.addEventListener('DOMContentLoaded', function() {
    const userType = '<?php echo $userType; ?>';
    if (userType === 'admin') {
        document.getElementById('admin-tab').click();
    } else {
        document.getElementById('client-tab').click();
    }
});
</script>

<?php
// Inclure le pied de page
include_once __DIR__ . '../footer.php';
?>
