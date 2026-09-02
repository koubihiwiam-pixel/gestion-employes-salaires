<?php
session_start();
include 'ConnectDb.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Requête pour récupérer les employés
$query = "SELECT Id, Nom, Prenom FROM Employes";
$result = mysqli_query($data, $query);

// Vérification de l'authentification de l'utilisateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');  // Rediriger vers la page de connexion si non autorisé
    exit;
}
// Requête pour récupérer le nombre de demandes en attente de congé
$count_sql = "SELECT COUNT(*) AS count FROM conges WHERE statut = 'En attente'";
$count_result = mysqli_query($data, $count_sql);
$count_row = mysqli_fetch_assoc($count_result);
$pending_requests = $count_row['count']; // Nombre de demandes en attente

// Traitement du formulaire pour ajouter des primes
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sécuriser les données avant l'insertion pour éviter l'injection SQL
    $employe_id = mysqli_real_escape_string($data, $_POST["employe_id"]);
    $prime_anciennete = mysqli_real_escape_string($data, $_POST["prime_anciennete"]);
    $prime_performance = mysqli_real_escape_string($data, $_POST["prime_performance"]);

    $currentMonth = date('m'); // Utilisation uniquement du mois actuel

    // Vérification de l'existence de primes pour cet employé et ce mois
    $check_sql = "SELECT * FROM primes WHERE employe_id = '$employe_id' ";
    $check_result = mysqli_query($data, $check_sql);

    if (!$check_result) {
        die("Erreur de la requête de vérification: " . mysqli_error($data)); // Vérifier l'erreur SQL
    }

    if (mysqli_num_rows($check_result) > 0) {
        // Si l'enregistrement existe, mise à jour des primes
        $update_sql = "UPDATE primes 
                       SET prime_anciennete = '$prime_anciennete', prime_performance = '$prime_performance' ,mois=$currentMonth
                       WHERE employe_id = '$employe_id' ";
        $query_exec = mysqli_query($data, $update_sql);
    } else {
        // Si l'enregistrement n'existe pas, insertion d'un nouvel enregistrement
        $insert_sql = "INSERT INTO primes (employe_id, prime_anciennete, prime_performance, mois)
                       VALUES ('$employe_id', '$prime_anciennete', '$prime_performance', '$currentMonth')";
        $query_exec = mysqli_query($data, $insert_sql);
    }

    if ($query_exec) {
        $_SESSION['message'] = "✅ Primes ajoutées ou mises à jour avec succès !";
    } else {
        $_SESSION['message'] = "❌ Une erreur est survenue lors de l'ajout des primes. " . mysqli_error($data);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>Ajouter des Primes</title>
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
            <h1>Ajouter des Primes</h1>
            <a href="logout.php" class="btn btn-danger logout-button">Se Déconnecter</a>
        </div>
<!-- Affichage du message -->
<?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info mt-4">
                <?= $_SESSION['message']; ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        <!-- Ton formulaire ici -->
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="employe_id" class="form-label">Sélectionner un employé</label>
                        <select name="employe_id" id="employe_id" class="form-control" required>
                            <option value="">Choisir un employé</option>
                            <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                                <option value="<?= $row['Id'] ?>"><?= $row['Nom'] ?> <?= $row['Prenom'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="prime_anciennete" class="form-label">Prime d'ancienneté</label>
                        <input type="number" name="prime_anciennete" id="prime_anciennete" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="prime_performance" class="form-label">Prime de performance</label>
                        <input type="number" name="prime_performance" id="prime_performance" class="form-control" required>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Ajouter la Prime</button>
        </form>
    </div>

    <!-- Script pour le Toggle -->
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const width = sidebar.style.width === '0px' ? '250px' : '0px';
            sidebar.style.width = width;
        }
    </script>

</body>

</html>
