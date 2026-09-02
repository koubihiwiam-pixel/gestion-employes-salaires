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

// Recherche par CIN
$cin_search = '';
if (isset($_POST['cin_search'])) {
    $cin_search = $_POST['cin_search'];
}

// Traitement pour archiver une nouvelle fiche de paie
if (isset($_POST['generate_pdf'])) {
    // Récupérer l'ID de l'employé et le mois
    $employe_id = $_POST['employe_id'];
    $mois = date('F_Y'); // Exemple : "May 2025"

    // Vérifier si une fiche pour ce mois existe déjà pour cet employé
    $check_query = "SELECT COUNT(*) AS count FROM fiches_de_paie WHERE employe_id = '$employe_id' AND mois = '$mois'";
    $check_result = mysqli_query($data, $check_query);
    $check_row = mysqli_fetch_assoc($check_result);

    if ($check_row['count'] > 0) {
        // Si la fiche existe déjà pour ce mois, afficher un message d'erreur ou ne rien faire
        echo "La fiche de paie pour ce mois a déjà été archivée.";
    } else {
        // Si la fiche n'existe pas, insérer une nouvelle fiche de paie
        $file_path = 'archives/Fiche_de_Paie_' . $employe_id . '_' . $mois . '.pdf';  // Exemple de chemin de fichier
        $insert_query = "INSERT INTO fiches_de_paie (employe_id, file_path, mois, date_archivage) 
                         VALUES ('$employe_id', '$file_path', '$mois', NOW())";
        $insert_result = mysqli_query($data, $insert_query);

        if (!$insert_result) {
            die("Erreur dans l'insertion de la fiche de paie : " . mysqli_error($data));
        } else {
            echo "Fiche de paie archivée avec succès.";
        }
    }
}

// Requête pour récupérer les fiches de paie archivées avec les informations de l'employé
$query = "SELECT f.id, e.Nom, e.Prenom, e.CIN, f.file_path, f.mois, f.date_archivage
          FROM fiches_de_paie f
          JOIN Employes e ON f.employe_id = e.Id";

// Si un CIN est spécifié, ajouter une condition pour la recherche
if (!empty($cin_search)) {
    $query .= " WHERE e.CIN LIKE '%$cin_search%'";
}

$fiches_result = mysqli_query($data, $query);

if (!$fiches_result) {
    die("Erreur dans la requête SQL pour récupérer les fiches de paie : " . mysqli_error($data));
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
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
            position: absolute;
            top: 5px;
            right: 10px;
        }

        /* Pour les petites tailles d'écran */
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

        /* Toggle button */
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

        /* Content */
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

        /* Header */
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
            <h1>Liste des Fiches de Paie Archivées</h1>
            <a href="logout.php" class="btn btn-danger logout-button">Se Déconnecter</a>
        </div>


    <!-- Formulaire de recherche par CIN -->
    <form method="POST" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <input type="text" name="cin_search" class="form-control" placeholder="Rechercher par CIN" value="<?= htmlspecialchars($cin_search) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Rechercher</button>
            </div>
        </div>
    </form>

    <table class="table table-bordered mt-4">
        <thead class="table-dark">
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>CIN</th>
                <th>Mois</th>
                <th>Date d'archivage</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($fiche = mysqli_fetch_assoc($fiches_result)) : ?>
                <tr>
                    <td><?= $fiche['Nom'] ?></td>
                    <td><?= $fiche['Prenom'] ?></td>
                    <td><?= $fiche['CIN'] ?></td>
                    <td><?= $fiche['mois'] ?></td>
                    <td><?= $fiche['date_archivage'] ?></td>
                    <td>
                        <!-- Lien pour télécharger la fiche de paie -->
                        <a href="<?= $fiche['file_path'] ?>" target="_blank" class="btn btn-primary">Télécharger</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>


</body>
</html>
