<?php
/**
 * Page de réservation
 * Cette page permet aux utilisateurs de réserver un louage
 */

// Inclure les fonctions communes
require_once __DIR__ . '../functions.php';

// Vérifier si l'utilisateur est connecté pour certaines fonctionnalités
$isLoggedIn = isLoggedIn();

// Initialiser les variables
$error = '';
$success = '';
$formData = [
    'fromCity' => '',
    'toCity' => '',
    'departDate' => '',
    'departTime' => '',
    'passengers' => 1,
    'louageType' => 'standard'
];

// Récupérer la liste des villes depuis la base de données
$cities = [];
try {
    $db = getDbConnection();
    $stmt = $db->query("SELECT id, name FROM cities WHERE is_active = 1 ORDER BY name");
    $cities = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des villes.";
}

// Traitement du formulaire de réservation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier si l'utilisateur est connecté
    if (!$isLoggedIn) {
        $error = "Vous devez être connecté pour effectuer une réservation. <a href='logIn.php'>Se connecter</a>";
    } else {
        // Récupérer les données du formulaire
        $formData = [
            'fromCity' => $_POST['fromCity'] ?? '',
            'toCity' => $_POST['toCity'] ?? '',
            'departDate' => $_POST['departDate'] ?? '',
            'departTime' => $_POST['departTime'] ?? '',
            'passengers' => intval($_POST['passengers'] ?? 1),
            'louageType' => $_POST['louageType'] ?? 'standard'
        ];
        
        // Validation de base
        if (empty($formData['fromCity']) || empty($formData['toCity']) || 
            empty($formData['departDate']) || empty($formData['departTime'])) {
            $error = "Veuillez remplir tous les champs obligatoires.";
        } elseif ($formData['fromCity'] === $formData['toCity']) {
            $error = "Les villes de départ et d'arrivée doivent être différentes.";
        } elseif (strtotime($formData['departDate']) < strtotime(date('Y-m-d'))) {
            $error = "La date de départ doit être ultérieure à aujourd'hui.";
        } elseif ($formData['passengers'] < 1 || $formData['passengers'] > 8) {
            $error = "Le nombre de passagers doit être compris entre 1 et 8.";
        } else {
            try {
                // Récupérer le prix en fonction des villes et du type de louage
                $stmt = $db->prepare("
                SELECT standard_price, comfort_price 
                FROM prices 
                WHERE from_city_id = (SELECT id FROM cities WHERE LOWER(name) = LOWER(:fromCity))
                AND to_city_id = (SELECT id FROM cities WHERE LOWER(name) = LOWER(:toCity))
            ");
                $stmt->bindParam(':fromCity', $formData['fromCity']);
                $stmt->bindParam(':toCity', $formData['toCity']);
                $stmt->execute();
                
                $priceData = $stmt->fetch();
                
                if (!$priceData) {
                    $error = "Aucun tarif n'est disponible pour cet itinéraire.";
                } else {
                    // Calculer le prix total
                    $basePrice = ($formData['louageType'] === 'standard') ? $priceData['standard_price'] : $priceData['comfort_price'];
                    $totalPrice = $basePrice * $formData['passengers'];
                    $serviceFee = $basePrice*0.1 * $formData['passengers']; // Frais de service fixes
                    $grandTotal = $totalPrice + $serviceFee;
                    
                    // Enregistrer la réservation
                    $stmt = $db->prepare("
                        INSERT INTO reservations (
                            user_id, from_city, to_city, depart_date, depart_time, 
                            passengers, louage_type, price, status, created_at
                        ) VALUES (
                            :userId, :fromCity, :toCity, :departDate, :departTime, 
                            :passengers, :louageType, :price, 'pending', NOW()
                        )
                    ");
                    
                    $userId = $_SESSION['user_id'];
                    $stmt->bindParam(':userId', $userId);
                    $stmt->bindParam(':fromCity', $formData['fromCity']);
                    $stmt->bindParam(':toCity', $formData['toCity']);
                    $stmt->bindParam(':departDate', $formData['departDate']);
                    $stmt->bindParam(':departTime', $formData['departTime']);
                    $stmt->bindParam(':passengers', $formData['passengers']);
                    $stmt->bindParam(':louageType', $formData['louageType']);
                    $stmt->bindParam(':price', $totalPrice);
                    
                    if ($stmt->execute()) {
                        $reservationId = $db->lastInsertId();
                        
                        // Stocker les données de réservation dans la session
                        $_SESSION['reservation'] = [
                            'id' => $reservationId,
                            'fromCity' => $formData['fromCity'],
                            'toCity' => $formData['toCity'],
                            'departDate' => $formData['departDate'],
                            'departTime' => $formData['departTime'],
                            'passengers' => $formData['passengers'],
                            'louageType' => $formData['louageType'],
                            'basePrice' => $basePrice,
                            'totalPrice' => $totalPrice,
                            'serviceFee' => $serviceFee,
                            'grandTotal' => $grandTotal
                        ];
                        
                        
                        // Rediriger vers la page de paiement
                        header('Location: payement.php');
                        exit;
                    } else {
                        $error = "Une erreur est survenue lors de l'enregistrement de la réservation.";
                    }
                }
            } catch (PDOException $e) {
                $error = "Une erreur est survenue lors de la réservation. Veuillez réessayer.";
                // En production, il faudrait logger l'erreur
            }
        }
    }
}

// Définir le titre de la page
$pageTitle = "Réservation - Wasalni Ebooking";

// Inclure l'en-tête
include_once __DIR__ . '../header.php';
?>

<div class="hero-section">
    <div class="container text-center">
        <h1 class="display-4">Réservez votre Louage en Tunisie</h1>
        <p class="lead">Voyagez confortablement et économiquement entre les villes tunisiennes</p>
    </div>
</div>

<div class="container my-5">
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card booking-card mb-4">
                <div class="card-body">
                    <h3 class="card-title">Réservez votre trajet</h3>
                    
                    <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label for="fromCity" class="form-label">Départ</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                    <select class="form-select" id="fromCity" name="fromCity" required>
                                        <option value="" selected disabled>Sélectionnez la ville de départ</option>
                                        <?php foreach ($cities as $city): ?>
                                            <option value="<?php echo escape($city['name']); ?>" <?php echo $formData['fromCity'] === $city['name'] ? 'selected' : ''; ?>>
                                                <?php echo escape($city['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label for="toCity" class="form-label">Arrivée</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-geo-alt-fill"></i></span>
                                    <select class="form-select" id="toCity" name="toCity" required>
                                        <option value="" selected disabled>Sélectionnez la ville d'arrivée</option>
                                        <?php foreach ($cities as $city): ?>
                                            <option value="<?php echo escape($city['name']); ?>" <?php echo $formData['toCity'] === $city['name'] ? 'selected' : ''; ?>>
                                                <?php echo escape($city['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-secondary h-100 w-100" id="swapCities">
                                    <i class="bi bi-arrow-left-right"></i>
                                </button>
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-4">
                                <label for="departDate" class="form-label">Date de Départ</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                                    <input type="date" class="form-control" id="departDate" name="departDate" 
                                           min="<?php echo date('Y-m-d'); ?>" 
                                           value="<?php echo escape($formData['departDate']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="departTime" class="form-label">Heure Préférée</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                    <select class="form-select" id="departTime" name="departTime" required>
                                        <option value="" selected disabled>Choisir une heure</option>
                                        <option value="06:00" <?php echo $formData['departTime'] === '06:00' ? 'selected' : ''; ?>>06:00</option>
                                        <option value="08:00" <?php echo $formData['departTime'] === '08:00' ? 'selected' : ''; ?>>08:00</option>
                                        <option value="10:00" <?php echo $formData['departTime'] === '10:00' ? 'selected' : ''; ?>>10:00</option>
                                        <option value="12:00" <?php echo $formData['departTime'] === '12:00' ? 'selected' : ''; ?>>12:00</option>
                                        <option value="14:00" <?php echo $formData['departTime'] === '14:00' ? 'selected' : ''; ?>>14:00</option>
                                        <option value="16:00" <?php echo $formData['departTime'] === '16:00' ? 'selected' : ''; ?>>16:00</option>
                                        <option value="18:00" <?php echo $formData['departTime'] === '18:00' ? 'selected' : ''; ?>>18:00</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="passengers" class="form-label">Nombre de Passagers</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-people"></i></span>
                                    <select class="form-select" id="passengers" name="passengers" required>
                                        <?php for ($i = 1; $i <= 8; $i++): ?>
                                            <option value="<?php echo $i; ?>" <?php echo intval($formData['passengers']) === $i ? 'selected' : ''; ?>>
                                                <?php echo $i; ?> <?php echo $i === 1 ? 'passager' : 'passagers'; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="form-label">Type de Louage</label>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check card p-3">
                                        <input class="form-check-input" type="radio" name="louageType" id="standardLouage" 
                                               value="standard" <?php echo $formData['louageType'] === 'standard' ? 'checked' : ''; ?> required>
                                        <label class="form-check-label" for="standardLouage">
                                            <h5>Standard</h5>
                                            <p class="text-muted mb-0">Transport économique entre les villes</p>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check card p-3">
                                        <input class="form-check-input" type="radio" name="louageType" id="comfortLouage" 
                                               value="confort" <?php echo $formData['louageType'] === 'confort' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="comfortLouage">
                                            <h5>Confort</h5>
                                            <p class="text-muted mb-0">Véhicules plus récents avec climatisation</p>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">Réserver maintenant</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title">Pourquoi choisir Wasalni?</h4>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Réservation rapide et facile</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Prix compétitifs</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Trajets directs entre villes</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Véhicules confortables</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Conducteurs expérimentés</li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Besoin d'aide?</h4>
                    <p>Notre équipe de support est disponible pour vous aider avec votre réservation.</p>
                    <a href="contact.php" class="btn btn-outline-primary">Contactez-nous</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
  document.getElementById("swapCities").addEventListener("click", function () {
      const from = document.getElementById("fromCity");
      const to = document.getElementById("toCity");
      const temp = from.value;
      from.value = to.value;
      to.value = temp;
  });
</script>

<style>
.hero-section {
    background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), 
                url('https://images.unsplash.com/photo-1528728329032-2972f65dfb3f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80');
    background-size: cover;
    background-position: center;
    color: white;
    padding: 100px 0;
}
.booking-card {
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}
.louage-card {
    transition: transform 0.3s;
}
.louage-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}
.amenity-icon {
    font-size: 1.2rem;
    margin-right: 10px;
}
.footer {
    background-color: #212529;
    color: white;
}
</style>

<?php
// Inclure le pied de page
include_once __DIR__ . '../footer.php';
?>
