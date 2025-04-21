<?php
/**
 * Page de profil utilisateur
 * Cette page permet aux utilisateurs de visualiser et modifier leurs informations personnelles
 */

// Inclure les fonctions communes
require_once __DIR__ . '../functions.php';


// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
    redirect('logIn.php');
}

// Initialiser les variables
$error = '';
$success = '';
$userData = [];

// Récupérer les informations de l'utilisateur depuis la base de données
try {
    $db = getDbConnection();
    $userId = $_SESSION['user_id'];
    
    $stmt = $db->prepare("SELECT * FROM users WHERE id = :userId");
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    
    if ($userData = $stmt->fetch()) {
        // Formater la date de création
        $userData['created_at_formatted'] = date('F Y', strtotime($userData['created_at']));
    } else {
        $error = "Impossible de récupérer les informations de l'utilisateur.";
    }
} catch (PDOException $e) {
    $error = "Une erreur est survenue lors de la récupération des informations.";
    // En production, il faudrait logger l'erreur
}

// Traitement du formulaire de mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    
    // Validation de base
    if (empty($phone)) {
        $error = "Le numéro de téléphone est obligatoire.";
    } else {
        try {
            // Mettre à jour les informations de l'utilisateur
            $stmt = $db->prepare("UPDATE users SET phone = :phone, address = :address WHERE id = :userId");
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':userId', $userId);
            
            if ($stmt->execute()) {
                $success = "Vos informations ont été mises à jour avec succès.";
                
                // Mettre à jour les données affichées
                $userData['phone'] = $phone;
                $userData['address'] = $address;
            } else {
                $error = "Une erreur est survenue lors de la mise à jour des informations.";
            }
        } catch (PDOException $e) {
            $error = "Une erreur est survenue lors de la mise à jour des informations.";
            // En production, il faudrait logger l'erreur
        }
    }
}

// Définir le titre de la page
$pageTitle = "Mon Profil - Wasalni Ebooking";

// Inclure l'en-tête
include_once __DIR__ . '../header.php';
?>

<div class="container my-5">
    <h1 class="mb-4">Profil Utilisateur</h1>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo escape($error); ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo escape($success); ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h3><?php echo escape($userData['first_name'] . ' ' . $userData['last_name']); ?></h3>
                    <p class="text-muted">Membre depuis <?php echo escape($userData['created_at_formatted']); ?></p>
                    <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#editProfileForm" aria-expanded="false" aria-controls="editProfileForm">
                        Modifier le profil
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Informations personnelles</h3>
                    
                    <div class="collapse" id="editProfileForm">
                        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" value="<?php echo escape($userData['email']); ?>" readonly>
                                <small class="text-muted">L'adresse email ne peut pas être modifiée.</small>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo escape($userData['phone']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Adresse</label>
                                <input type="text" class="form-control" id="address" name="address" value="<?php echo escape($userData['address'] ?? ''); ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">Sauvegarder les modifications</button>
                        </form>
                    </div>
                    
                    <div class="mt-4">
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Nom complet:</div>
                            <div class="col-md-8"><?php echo escape($userData['first_name'] . ' ' . $userData['last_name']); ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Email:</div>
                            <div class="col-md-8"><?php echo escape($userData['email']); ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Téléphone:</div>
                            <div class="col-md-8"><?php echo escape($userData['phone']); ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Adresse:</div>
                            <div class="col-md-8"><?php echo escape($userData['address'] ?? 'Non spécifiée'); ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Type de compte:</div>
                            <div class="col-md-8"><?php echo $userData['user_type'] === 'admin' ? 'Administrateur' : 'Client'; ?></div>
                        </div>
                        <?php if ($userData['user_type'] === 'admin' && !empty($userData['company'])): ?>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Entreprise:</div>
                            <div class="col-md-8"><?php echo escape($userData['company']); ?></div>
                        </div>
                        <?php endif; ?>
                        <div class="row mb-3">
                            <div class="col-md-4 fw-bold">Membre depuis:</div>
                            <div class="col-md-8"><?php echo escape($userData['created_at_formatted']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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

<?php
// Inclure le pied de page
include_once __DIR__ . '../footer.php';
?>
