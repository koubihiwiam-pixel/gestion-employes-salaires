<?php
session_start();
include 'ConnectDb.php'; // Connexion à la base de données
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Requête pour compter le nombre de demandes en attente
$count_sql = "SELECT COUNT(*) AS count FROM conges WHERE statut = 'En attente'";
$count_result = mysqli_query($data, $count_sql);
$count_row = mysqli_fetch_assoc($count_result);
$pending_requests = $count_row['count']; // Nombre de demandes en attente

// Vérification de l'authentification de l'utilisateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');  // Rediriger vers la page de connexion si non autorisé
    exit;
}

// Générer les dates pour le mois courant en utilisant la date actuelle du serveur
$dates = [];
$currentMonth = date('F'); // Nom complet du mois, par exemple, "April" ou "Avril"

// Créer un objet DateTime pour le premier jour du mois actuel
$start = new DateTime('first day of this month');
$end = new DateTime('last day of this month'); // Dernier jour du mois courant

// Re-générer les dates du mois courant
while ($start <= $end) {
    $dates[] = $start->format('Y-m-d');
    $start->modify('+1 day');
}

// Récupérer les employés
if (isset($_GET['cin_search']) && !empty($_GET['cin_search'])) {
    $cin_search = mysqli_real_escape_string($data, $_GET['cin_search']);
    $sql = "SELECT Id, Nom, Prenom, CIN FROM Employes WHERE CIN LIKE '%$cin_search%'";
} else {
    $sql = "SELECT Id, Nom, Prenom, CIN FROM Employes";
}

$result = mysqli_query($data, $sql);

// Enregistrement des absences (statut "❌" uniquement)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['presence'])) {
    // Supprimer les anciennes absences avant d'enregistrer les nouvelles
    foreach ($_POST['presence'] as $empId => $presenceData) {
        foreach ($presenceData as $date => $status) {
            // Ne traiter que les absences (❌)
            if ($status === '❌') {
                // Vérifier si l'absence existe déjà pour cette date et employé
                $sql_check = "SELECT * FROM presence WHERE employe_id = '$empId' AND date_presence = '$date'";
                $check_result = mysqli_query($data, $sql_check);
                if (mysqli_num_rows($check_result) == 0) {
                    // Enregistrer l'absence dans la table `presence`
                    $sql_insert = "INSERT INTO presence (employe_id, date_presence, statut) VALUES ('$empId', '$date', '$status')";
                    mysqli_query($data, $sql_insert);
                }
            }
        }
    }
}

// Calcul des absences pour chaque employé (pour chaque date)
$absences = [];
$absence_sql = "SELECT employe_id, date_presence, COUNT(*) AS absence_count 
                FROM presence 
                WHERE statut = '❌' 
                GROUP BY employe_id, date_presence";
$absence_result = mysqli_query($data, $absence_sql);
while ($row = mysqli_fetch_assoc($absence_result)) {
    $absences[$row['employe_id']][$row['date_presence']] = $row['absence_count'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suivi de présence</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* Styles de la Sidebar */
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

        .sidebar-body::-webkit-scrollbar {
            width: 8px;
        }

        .sidebar-body::-webkit-scrollbar-thumb {
            background-color: #34495E;
            border-radius: 5px;
        }

        .sidebar-body::-webkit-scrollbar-track {
            background-color: transparent;
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
            position: absolute;
            top: 5px;
            right: 10px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .toggle-btn {
                display: block;
            }

            .sidebar {
                width: 0;
                padding-top: 0;
            }
        }

        .toggle-btn {
            display: none;
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: #34495E;
            color: white;
            font-size: 20px;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
        }

        body {
            background-color: #f4f7fc;
            font-family: 'Arial', sans-serif;
        }

        .content {
            margin-left: 250px;
            padding: 40px;
            margin-top: 80px;
            width: calc(100% - 250px);
            box-sizing: border-box;
        }

        .form-container {
            padding: 30px;
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

        .menu-item.position-relative {
            position: relative;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: 10px;
        }

        /* Optimisation du formulaire de recherche */
        .search-box {
            max-width: 400px;
            margin-bottom: 20px;
        }

        .search-box input {
            width: 80%;
        }

        /* Optimisation du tableau de présence */
        .table th, .table td {
            padding: 12px 15px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 20px;
            }

            .table th, .table td {
                padding: 8px 10px;
            }

            .search-box {
                max-width: 100%;
            }
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

    <!-- Toggle Button -->
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
 <!-- Content -->
 <div class="content">
 <img src="logo/img.png" alt="FST ProGestion Logo" class="logo">
        <!-- Header Section -->
        <div class="header">
            <h1>📆 Suivi de présence - <?php echo $currentMonth; ?> 2025</h1>
            <a href="logout.php" class="btn btn-danger logout-button">Se Déconnecter</a>
        </div>

    <div class="search-box">
    <form method="get" action="suivi_presence.php" class="d-flex">
        <input type="text" name="cin_search" class="form-control me-2" placeholder="Rechercher par CIN" value="<?php echo isset($_GET['cin_search']) ? htmlspecialchars($_GET['cin_search']) : ''; ?>">
        <button type="submit" class="btn btn-primary">Rechercher</button>
    </form>
</div>
    <form action="suivi_presence.php" method="post">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>CIN</th>
                        <th>Absences</th>
                        <?php foreach ($dates as $date): ?>
                            <th><?php echo $date; ?></th>
                        <?php endforeach; ?>
                        
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['Nom']); ?></td>
                            <td><?php echo htmlspecialchars($row['Prenom']); ?></td>
                            <td><?php echo htmlspecialchars($row['CIN']); ?></td>
                            <td>
                                <?php
                                    // Calcul des absences de l'employé pour chaque date
                                    $absence_count = isset($absences[$row['Id']]) ? count($absences[$row['Id']]) : 0;
                                    echo $absence_count;
                                ?>
                            </td>
                            <?php foreach ($dates as $date): ?>
                                <td>
                                    <select name="presence[<?php echo $row['Id']; ?>][<?php echo $date; ?>]">
                                        <option value="✅">✅</option>
                                        <option value="❌" <?php echo (array_key_exists($row['Id'], $absences) && array_key_exists($date, $absences[$row['Id']]) ? 'selected' : ''); ?>>❌</option>
                                    </select>
                                </td>
                            <?php endforeach; ?>
                            
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <button type="submit" class="btn btn-success">Enregistrer les absences</button>
    </form>
</div>

<!-- JS Bootstrap + Icons -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
