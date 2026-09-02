<?php
session_start();
include 'ConnectDb.php'; // Connexion à la base de données
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Requête pour récupérer toutes les demandes de congé
$sql = "SELECT conges.id, conges.employe_id, conges.type_conge, conges.date_debut, conges.date_fin, conges.statut, Employes.Nom, Employes.Prenom
        FROM conges
        INNER JOIN Employes ON conges.employe_id = Employes.Id";
$result = mysqli_query($data, $sql);

// Vérification de l'authentification de l'utilisateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');  // Rediriger vers la page de connexion si non autorisé
    exit;
}

// Requête pour compter le nombre de demandes en attente
$count_sql = "SELECT COUNT(*) AS count FROM conges WHERE statut = 'En attente'";
$count_result = mysqli_query($data, $count_sql);
$count_row = mysqli_fetch_assoc($count_result);
$pending_requests = $count_row['count']; // Nombre de demandes en attente

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7fc;
            font-family: 'Arial', sans-serif;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 24px;
            font-weight: bold;
        }

        .logout-button {
            font-size: 16px;
        }
        .content {
            margin-left: 240px;
            padding: 40px;
            margin-top: 80px;
            width: calc(100% - 240px);
            box-sizing: border-box;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100%;
            background-color: #2C3E50;
            color: white;
            padding-top: 30px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            overflow-y: auto;
        }
        .sidebar-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .sidebar-header h2 {
            font-size: 22px;
            font-weight: bold;
        }

        .sidebar-body {
            padding-left: 15px;
            max-height: 90%;
            overflow-y: auto; 
       }
        /* Personnaliser la couleur de la barre de défilement dans la sidebar */
        .sidebar-body::-webkit-scrollbar {
            width: 8px; /* Largeur de la barre de défilement */
        }

        .sidebar-body::-webkit-scrollbar-thumb {
            background-color: #34495E; /* Couleur de la barre de défilement */
            border-radius: 5px;
        }

        .sidebar-body::-webkit-scrollbar-track {
            background-color: transparent; /* Couleur de la piste de la barre de défilement */
        }

        .sidebar-menu {
            list-style-type: none;
            padding: 0;
        }
        .menu-item {
            margin: 10px 0;
        }

        .menu-link {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: white;
            font-size: 16px;
            padding: 10px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .menu-link:hover {
            background-color: #34495E;
        }

        .menu-link i {
            margin-right: 10px;
        }
        .badge {
            font-size: 14px;
            top: 5px;
            right: 10px;
        }
        table {
            width: 100%;
            margin-top: 20px;
        }
        table th, table td {
            text-align: center;
            vertical-align: middle;
        }
        .alert-badge {
            background-color: red;
            color: white;
            padding: 3px 8px;
            font-size: 12px;
            border-radius: 50%;
            margin-left: 8px;
        }
        .menu-item.position-relative {
            position: relative;
        }
        .notification-badge {
            position: absolute;
            top: -5px;
            right: 10px;
        }
        .logo {
            position: absolute; /* pour positionner le logo */
            top: 2px;
            left: 270px;
            max-width: 150px; /* largeur max */
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
        <div class="sidebar-header">
            <h2>Menu</h2>
        </div>
        <div class="sidebar-body">
            <ul class="sidebar-menu">
            <li class="menu-item">
                    <a href="dashbord.php" class="menu-link">
                    <i class="fas fa-tachometer-alt"></i> Tableau de bord
                    </a>
                </li> 
                <li class="menu-item">
                    <a href="Ajouter_employes.php" class="menu-link">
                        <i class="fas fa-user-plus"></i> Ajouter Employé
                    </a>
                </li>
                <li class="menu-item">
                    <a href="liste_employes.php" class="menu-link">
                        <i class="fas fa-list"></i> Liste des Employés
                    </a>
                </li>
                <li class="menu-item">
                    <a href="contrat.php" class="menu-link">
                        <i class="fas fa-file-contract"></i> Assigner Contrat
                    </a>
                </li>
                <li class="menu-item">
                    <a href="afficher_contrat.php" class="menu-link">
                        <i class="fas fa-copy"></i> Les contrats
                    </a>
                </li>
                <li class="menu-item">
                    <a href="suivi_presence.php" class="menu-link">
                        <i class="fas fa-clock"></i> Suivi Présence
                    </a>
                </li>
                <li class="menu-item position-relative">
                    <a href="gestion_conge.php" class="menu-link">
                        <i class="fas fa-calendar-check"></i> Les demandes
                        <?php if ($pending_requests > 0): ?>
                        <span class="badge bg-danger notification-badge"><?= $pending_requests; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="ajouter_heures_supp.php" class="menu-link">
                        <i class="fas fa-clock"></i> Ajouter Heures Supplémentaires
                    </a>
                </li>
                <li class="menu-item">
                    <a href="ajouter_prime.php" class="menu-link">
                        <i class="fas fa-money-bill-alt"></i> Ajouter Prime
                    </a>
                </li>
                <li class="menu-item">
                    <a href="afficher_salaires.php" class="menu-link">
                        <i class="fas fa-credit-card"></i> Les salaires
                    </a>
                </li>
                <li class="menu-item">
                    <a href="archivage.php" class="menu-link">
                        <i class="fas fa-archive"></i> Paie Archivées
                    </a>
                </li>
                <li class="menu-item">
                    <a href="employes_supprimes.php" class="menu-link">
                        <i class="fas fa-trash"></i> Corbeille
                    </a>
                </li>
            </ul>
        </div>
    </div>


<!-- Content -->
<div class="content">
<img src="logo/img.png" alt="FST ProGestion Logo" class="logo">
    <div class="header">
        <h1>Gestion des Congés</h1>
        <a href="logout.php" class="btn btn-danger logout-button">Se Déconnecter</a>
    </div>

    <!-- Table des demandes de congé -->
    <table class="table table-bordered">
        <thead class="table-dark">
        <tr>
          <th>Nom</th>
          <th>Prénom</th>
          <th>Type de Congé</th>
          <th>Date de début</th>
          <th>Date de fin</th>
          <th>Nombre de jours</th> 
          <th>Statut</th>
          <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): 
    // Calculer le nombre de jours
    $dateDebut = new DateTime($row['date_debut']);
    $dateFin = new DateTime($row['date_fin']);
    $interval = $dateDebut->diff($dateFin);
    $nombre_jours = $interval->days ;
?>
    <tr>
    <td><?php echo htmlspecialchars($row['Nom']); ?></td>
    <td><?php echo htmlspecialchars($row['Prenom']); ?></td>
    <td><?php echo htmlspecialchars($row['type_conge']); ?></td>
    <td><?php echo htmlspecialchars($row['date_debut']); ?></td>
    <td><?php echo htmlspecialchars($row['date_fin']); ?></td>
    <td><?php echo $nombre_jours . " jours"; ?></td> <!-- nombre de jours -->
    <td>
        <?php 
            $statut = htmlspecialchars($row['statut']);
            if ($statut == 'Approuvé') {
                echo "<span class='badge bg-success'>$statut</span>"; // Green for Approved
            } elseif ($statut == 'Refusé') {
                echo "<span class='badge bg-danger'>$statut</span>"; // Red for Refused
            } else {
                echo "<span class='badge bg-warning'>$statut</span>"; // Yellow for Pending
            }
        ?>
    </td>
    <td>
        <?php if ($row['statut'] == 'En attente'): ?>
            <form action="approuver_conge.php" method="get" style="display: inline;">
                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                <button type="submit" class="btn btn-primary btn-sm">Approuver</button>
            </form>
            <form action="refuser_conge.php" method="get" style="display: inline;">
                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                <button type="submit" class="btn btn-danger btn-sm">Refuser</button>
            </form>
        <?php else: ?>
            <span class="badge bg-secondary">Statut finalisé</span>
        <?php endif; ?>
    </td>
</tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
