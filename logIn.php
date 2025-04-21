<?php
/**
 * Page de connexion
 * Cette page permet aux utilisateurs de se connecter au système
 */

// Inclure les fonctions communes
require_once __DIR__ . '../functions.php';

// Initialiser les variables
$error = '';
$email = '';
$userType = 'client'; // Par défaut, l'onglet client est actif

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? true : false;
    $userType = $_POST['user_type'] ?? 'client';
    
    // Validation de base
    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        // Connexion à la base de données
        $db = getDbConnection();
        
        try {
            // Préparer la requête pour récupérer l'utilisateur
            $stmt = $db->prepare("SELECT id, first_name, last_name, email, password, user_type FROM users WHERE email = :email AND user_type = :user_type");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':user_type', $userType);
            $stmt->execute();
            
            if ($user = $stmt->fetch()) {
                // Vérifier le mot de passe
                if (password_verify($password, $user['password'])) {
                    // Connexion réussie, créer la session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                    $_SESSION['user_type'] = $user['user_type'];
                    
                    // Rediriger vers la page d'accueil
                    redirect('codeHtmlPrincipale.html');
                } else {
                    $error = "Email ou mot de passe incorrect.";
                }
            } else {
                $error = "Email ou mot de passe incorrect.";
            }
        } catch (PDOException $e) {
            $error = "Une erreur est survenue lors de la connexion. Veuillez réessayer.";
            // En production, il faudrait logger l'erreur
        }
    }
}

// Définir le titre de la page
$pageTitle = "Connexion - Wasalni Ebooking";

// Inclure l'en-tête
include_once __DIR__ . '../header.php';
?>

<div class="logo">
    <i class="bi bi-car-front-fill me-2"></i>wasalni Ebooking
</div>

<div class="login-card">
    <div class="card-body p-4">
        <h2 class="text-center mb-4">Connexion</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo escape($error); ?></div>
        <?php endif; ?>
        
        <!-- Tabs -->
        <ul class="nav nav-tabs mb-4" id="loginTabs" role="tablist">
            <li class="nav-item w-50" role="presentation">
                <button class="nav-link <?php echo $userType === 'client' ? 'active' : ''; ?> w-100" id="client-tab" data-bs-toggle="tab" data-bs-target="#client" type="button" role="tab" aria-controls="client" aria-selected="<?php echo $userType === 'client' ? 'true' : 'false'; ?>">Client</button>
            </li>
            <li class="nav-item w-50" role="presentation">
                <button class="nav-link <?php echo $userType === 'admin' ? 'active' : ''; ?> w-100" id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin" type="button" role="tab" aria-controls="admin" aria-selected="<?php echo $userType === 'admin' ? 'true' : 'false'; ?>">Admin</button>
            </li>
        </ul>
        
        <!-- Tab Content -->
        <div class="tab-content" id="loginTabsContent">
            <!-- Client Login -->
            <div class="tab-pane fade <?php echo $userType === 'client' ? 'show active' : ''; ?>" id="client" role="tabpanel" aria-labelledby="client-tab">
                <form id="clientLoginForm" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <input type="hidden" name="user_type" value="client">
                    <div class="mb-3">
                        <label for="clientEmail" class="form-label">Adresse e-mail</label>
                        <input type="email" class="form-control" id="clientEmail" name="email" value="<?php echo $userType === 'client' ? escape($email) : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="clientPassword" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="clientPassword" name="password" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="clientRemember" name="remember">
                        <label class="form-check-label" for="clientRemember">Se souvenir de moi</label>
                    </div>
                    <button type="submit" class="btn btn-login-client text-white w-100">Se connecter</button>
                </form>
                
                <div class="text-center my-3">
                    <a href="forgot_password.php" class="text-decoration-none text-primary">Mot de passe oublié ?</a>
                </div>
                
                <div class="divider">
                    <span>Ou connectez-vous avec :</span>
                </div>
                
                <div class="d-grid gap-2 mb-3">
                    <button class="btn btn-google" type="button">
                        <i class="bi bi-google me-2"></i>Se connecter avec Google
                    </button>
                    <button class="btn btn-facebook" type="button">
                        <i class="bi bi-facebook me-2"></i>Se connecter avec Facebook
                    </button>
                </div>
            </div>
            
            <!-- Admin Login -->
            <div class="tab-pane fade <?php echo $userType === 'admin' ? 'show active' : ''; ?>" id="admin" role="tabpanel" aria-labelledby="admin-tab">
                <form id="adminLoginForm" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <input type="hidden" name="user_type" value="admin">
                    <div class="mb-3">
                        <label for="adminEmail" class="form-label">Adresse e-mail (Admin)</label>
                        <input type="email" class="form-control" id="adminEmail" name="email" value="<?php echo $userType === 'admin' ? escape($email) : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="adminPassword" class="form-label">Mot de passe (Admin)</label>
                        <input type="password" class="form-control" id="adminPassword" name="password" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="adminRemember" name="remember">
                        <label class="form-check-label" for="adminRemember">Se souvenir de moi</label>
                    </div>
                    <button type="submit" class="btn btn-login-admin text-white w-100">Se connecter (Admin)</button>
                </form>
                
                <div class="text-center my-3">
                    <a href="forgot_password.php" class="text-decoration-none text-primary">Mot de passe oublié ?</a>
                </div>
            </div>
        </div>
        
        <!-- Sign Up Section -->
        <div class="text-center mt-4 pt-3 border-top">
            <p class="text-muted mb-2">Vous n'avez pas de compte ?</p>
            <a href="sign_up.php" class="btn btn-outline-primary">S'inscrire</a>
        </div>
    </div>
</div>

<style>
body {
    background-color: #f8f9fa;
    min-height: 100vh;
    padding: 20px;
}

.login-card {
    max-width: 450px;
    width: 100%;
    background: white;
    margin: 0 auto;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.divider {
    display: flex;
    align-items: center;
    text-align: center;
    margin: 20px 0;
}

.divider::before,
.divider::after {
    content: '';
    flex: 1;
    border-bottom: 1px solid #dee2e6;
}

.divider span {
    padding: 0 10px;
    color: #6c757d;
    font-size: 0.875rem;
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

.nav-tabs .nav-link {
    color: #495057;
}

.nav-tabs .nav-link.active {
    font-weight: 500;
}

.btn-login-client {
    background-color: #dc3545;
    border-color: #dc3545;
}

.btn-login-client:hover {
    background-color: #c82333;
    border-color: #bd2130;
}

.btn-login-admin {
    background-color: #6f42c1;
    border-color: #6f42c1;
}

.btn-login-admin:hover {
    background-color: #5e37a6;
    border-color: #59339d;
}

.logo {
    color: #dc3545;
    font-size: 24px;
    font-weight: bold;
    text-align: center;
    margin-bottom: 20px;
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
