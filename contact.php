<?php
/**
 * Page de contact
 * Cette page permet aux utilisateurs d'envoyer des messages via le formulaire de contact
 */

// Inclure les fonctions communes
require_once __DIR__ . '../functions.php';

// Initialiser les variables
$error = '';
$success = '';
$formData = [
    'name' => '',
    'email' => '',
    'subject' => '',
    'message' => ''
];

// Traitement du formulaire de contact
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $formData = [
        'name' => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'subject' => $_POST['subject'] ?? '',
        'message' => $_POST['message'] ?? ''
    ];
    $name1=$_POST['name'];
    function isAlphabetic($name) {
        return preg_match("/^[a-zA-ZÀ-ÿ]+$/", $name);
    }
    
   


    

    
    // Validation de base
    if (empty($formData['name']) || empty($formData['email']) || empty($formData['subject']) || empty($formData['message'])) {
        $error = "Veuillez remplir tous les champs.";
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $error = "Veuillez entrer une adresse email valide.";
    }     elseif (isAlphabetic($name1)===0) {
        $error= ("❌ Name must contain only alphabetic characters.");
   
    }
    else {
        // Connexion à la base de données
        $db = getDbConnection();
        
        try {
            // Préparer la requête d'insertion
            $stmt = $db->prepare("
                INSERT INTO contacts (name, email, subject, message, created_at) 
                VALUES (:name, :email, :subject, :message, NOW())
            ");
            
            // Lier les paramètres
            $stmt->bindParam(':name', $formData['name']);
            $stmt->bindParam(':email', $formData['email']);
            $stmt->bindParam(':subject', $formData['subject']);
            $stmt->bindParam(':message', $formData['message']);
            
            // Exécuter la requête
            if ($stmt->execute()) {
                $success = "Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.";
                // Réinitialiser le formulaire
                $formData = [
                    'name' => '',
                    'email' => '',
                    'subject' => '',
                    'message' => ''
                ];
            } else {
                $error = "Une erreur est survenue lors de l'envoi du message. Veuillez réessayer.";
            }
        } catch (PDOException $e) {
            $error = "Une erreur est survenue lors de l'envoi du message. Veuillez réessayer.";
            // En production, il faudrait logger l'erreur
        }
    }
}

// Définir le titre de la page
$pageTitle = "Contact - Wasalni Ebooking";

// Inclure l'en-tête
include_once __DIR__ . '../header.php';
?>

<div class="container my-5">
    <h1 class="mb-4">Contactez-nous</h1>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo escape($error); ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo escape($success); ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6">
            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <div class="mb-3">
                    <label for="name" class="form-label">Nom</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo escape($formData['name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo escape($formData['email']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="subject" class="form-label">Sujet</label>
                    <input type="text" class="form-control" id="subject" name="subject" value="<?php echo escape($formData['subject']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="message" class="form-label">Message</label>
                    <textarea class="form-control" id="message" name="message" rows="5" required><?php echo escape($formData['message']); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Envoyer</button>
            </form>
        </div>
        <div class="col-md-6">
            <h3>Nos coordonnées</h3>
            <p><i class="bi bi-geo-alt me-2"></i>TUNISIA MEDNINE DJERBA</p>
            <p><i class="bi bi-telephone me-2"></i>+216 93 249 073</p>
            <p><i class="bi bi-envelope me-2"></i>contact@wasalniEbookingTN.com</p>
        </div>
    </div>
</div>

<?php
// Inclure le pied de page
include_once __DIR__ . '../footer.php';
?>
