<?php
/**
 * Page de gestion des réservations
 * Cette page permet aux utilisateurs de visualiser leurs réservations
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
$upcomingReservations = [];
$pastReservations = [];

// Récupérer les réservations de l'utilisateur depuis la base de données
try {
    $db = getDbConnection();
    $userId = $_SESSION['user_id'];
    
    // Récupérer les réservations à venir (statut pending ou confirmed)
    $stmt = $db->prepare("
        SELECT * FROM reservations 
        WHERE user_id = :userId 
        AND (status = 'pending' OR status = 'confirmed')
        AND depart_date >= CURDATE()
        ORDER BY depart_date ASC
    ");
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    $upcomingReservations = $stmt->fetchAll();
    
    // Récupérer les réservations passées (statut completed ou cancelled, ou date passée)
    $stmt = $db->prepare("
        SELECT * FROM reservations 
        WHERE user_id = :userId 
        AND (status = 'completed' OR status = 'cancelled' OR depart_date < CURDATE())
        ORDER BY depart_date DESC
    ");
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    $pastReservations = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Une erreur est survenue lors de la récupération des réservations.";
    // En production, il faudrait logger l'erreur
}

// Traitement de l'annulation d'une réservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $reservationId = $_POST['reservation_id'] ?? 0;
    
    if (empty($reservationId)) {
        $error = "Identifiant de réservation invalide.";
    } else {
        try {
            // Vérifier que la réservation appartient à l'utilisateur
            $stmt = $db->prepare("
                SELECT id FROM reservations 
                WHERE id = :reservationId AND user_id = :userId
            ");
            $stmt->bindParam(':reservationId', $reservationId);
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                // Mettre à jour le statut de la réservation
                $stmt = $db->prepare("
                    UPDATE reservations 
                    SET status = 'cancelled' 
                    WHERE id = :reservationId
                ");
                $stmt->bindParam(':reservationId', $reservationId);
                
                if ($stmt->execute()) {
                    $success = "La réservation a été annulée avec succès.";
                    
                    // Rafraîchir les listes de réservations
                    $stmt = $db->prepare("
                        SELECT * FROM reservations 
                        WHERE user_id = :userId 
                        AND (status = 'pending' OR status = 'confirmed')
                        AND depart_date >= CURDATE()
                        ORDER BY depart_date ASC
                    ");
                    $stmt->bindParam(':userId', $userId);
                    $stmt->execute();
                    $upcomingReservations = $stmt->fetchAll();
                    
                    $stmt = $db->prepare("
                        SELECT * FROM reservations 
                        WHERE user_id = :userId 
                        AND (status = 'completed' OR status = 'cancelled' OR depart_date < CURDATE())
                        ORDER BY depart_date DESC
                    ");
                    $stmt->bindParam(':userId', $userId);
                    $stmt->execute();
                    $pastReservations = $stmt->fetchAll();
                } else {
                    $error = "Une erreur est survenue lors de l'annulation de la réservation.";
                }
            } else {
                $error = "Vous n'êtes pas autorisé à annuler cette réservation.";
            }
        } catch (PDOException $e) {
            $error = "Une erreur est survenue lors de l'annulation de la réservation.";
            // En production, il faudrait logger l'erreur
        }
    }
}

// Fonction pour obtenir le badge de statut
function getStatusBadge($status) {
    switch ($status) {
        case 'pending':
            return '<span class="badge bg-warning text-dark">En attente</span>';
        case 'confirmed':
            return '<span class="badge bg-success">Confirmé</span>';
        case 'cancelled':
            return '<span class="badge bg-danger">Annulé</span>';
        case 'completed':
            return '<span class="badge bg-secondary">Terminé</span>';
        default:
            return '<span class="badge bg-secondary">Inconnu</span>';
    }
}

// Définir le titre de la page
$pageTitle = "Mes Réservations - Wasalni Ebooking";

// Inclure l'en-tête
include_once __DIR__ . '../header.php';
?>

<div class="container my-5">
    <h1 class="mb-4">Mes Réservations</h1>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo escape($error); ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo escape($success); ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Réservations à venir</h5>
            <?php if (empty($upcomingReservations)): ?>
                <p class="text-muted">Vous n'avez aucune réservation à venir.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Heure</th>
                                <th>De</th>
                                <th>À</th>
                                <th>Type de Louage</th>
                                <th>Passagers</th>
                                <th>Prix</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcomingReservations as $reservation): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($reservation['depart_date'])); ?></td>
                                    <td><?php echo $reservation['depart_time']; ?></td>
                                    <td><?php echo escape($reservation['from_city']); ?></td>
                                    <td><?php echo escape($reservation['to_city']); ?></td>
                                    <td><?php echo $reservation['louage_type'] === 'standard' ? 'Standard' : 'Confort'; ?></td>
                                    <td><?php echo $reservation['passengers']; ?></td>
                                    <td><?php echo number_format($reservation['price'], 2); ?> TND</td>
                                    <td><?php echo getStatusBadge($reservation['status']); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#detailsModal<?php echo $reservation['id']; ?>">Détails</button>
                                        <?php if ($reservation['status'] === 'pending' || $reservation['status'] === 'confirmed'): ?>
                                            <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="d-inline">
                                                <input type="hidden" name="action" value="cancel">
                                                <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')">Annuler</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                
                                <!-- Modal pour les détails de la réservation -->
                                <div class="modal fade" id="detailsModal<?php echo $reservation['id']; ?>" tabindex="-1" aria-labelledby="detailsModalLabel<?php echo $reservation['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="detailsModalLabel<?php echo $reservation['id']; ?>">Détails de la réservation</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Référence:</strong> #<?php echo $reservation['id']; ?></p>
                                                <p><strong>Date de départ:</strong> <?php echo date('d/m/Y', strtotime($reservation['depart_date'])); ?></p>
                                                <p><strong>Heure de départ:</strong> <?php echo $reservation['depart_time']; ?></p>
                                                <p><strong>De:</strong> <?php echo escape($reservation['from_city']); ?></p>
                                                <p><strong>À:</strong> <?php echo escape($reservation['to_city']); ?></p>
                                                <p><strong>Type de louage:</strong> <?php echo $reservation['louage_type'] === 'standard' ? 'Standard' : 'Confort'; ?></p>
                                                <p><strong>Nombre de passagers:</strong> <?php echo $reservation['passengers']; ?></p>
                                                <p><strong>Prix total:</strong> <?php echo number_format($reservation['price'], 2); ?> TND</p>
                                                <p><strong>Statut:</strong> <?php echo getStatusBadge($reservation['status']); ?></p>
                                                <p><strong>Date de réservation:</strong> <?php echo date('d/m/Y H:i', strtotime($reservation['created_at'])); ?></p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                                <?php if ($reservation['status'] === 'pending' || $reservation['status'] === 'confirmed'): ?>
                                                    <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                                                        <input type="hidden" name="action" value="cancel">
                                                        <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')">Annuler la réservation</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title">Historique des réservations</h5>
            <?php if (empty($pastReservations)): ?>
                <p class="text-muted">Vous n'avez aucune réservation passée.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Heure</th>
                                <th>De</th>
                                <th>À</th>
                                <th>Type de Louage</th>
                                <th>Passagers</th>
                                <th>Prix</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pastReservations as $reservation): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($reservation['depart_date'])); ?></td>
                                    <td><?php echo $reservation['depart_time']; ?></td>
                                    <td><?php echo escape($reservation['from_city']); ?></td>
                                    <td><?php echo escape($reservation['to_city']); ?></td>
                                    <td><?php echo $reservation['louage_type'] === 'standard' ? 'Standard' : 'Confort'; ?></td>
                                    <td><?php echo $reservation['passengers']; ?></td>
                                    <td><?php echo number_format($reservation['price'], 2); ?> TND</td>
                                    <td><?php echo getStatusBadge($reservation['status']); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#historyModal<?php echo $reservation['id']; ?>">Détails</button>
                                    </td>
                                </tr>
                                
                                <!-- Modal pour les détails de la réservation -->
                                <div class="modal fade" id="historyModal<?php echo $reservation['id']; ?>" tabindex="-1" aria-labelledby="historyModalLabel<?php echo $reservation['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="historyModalLabel<?php echo $reservation['id']; ?>">Détails de la réservation</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Référence:</strong> #<?php echo $reservation['id']; ?></p>
                                                <p><strong>Date de départ:</strong> <?php echo date('d/m/Y', strtotime($reservation['depart_date'])); ?></p>
                                                <p><strong>Heure de départ:</strong> <?php echo $reservation['depart_time']; ?></p>
                                                <p><strong>De:</strong> <?php echo escape($reservation['from_city']); ?></p>
                                                <p><strong>À:</strong> <?php echo escape($reservation['to_city']); ?></p>
                                                <p><strong>Type de louage:</strong> <?php echo $reservation['louage_type'] === 'standard' ? 'Standard' : 'Confort'; ?></p>
                                                <p><strong>Nombre de passagers:</strong> <?php echo $reservation['passengers']; ?></p>
                                                <p><strong>Prix total:</strong> <?php echo number_format($reservation['price'], 2); ?> TND</p>
                                                <p><strong>Statut:</strong> <?php echo getStatusBadge($reservation['status']); ?></p>
                                                <p><strong>Date de réservation:</strong> <?php echo date('d/m/Y H:i', strtotime($reservation['created_at'])); ?></p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
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
