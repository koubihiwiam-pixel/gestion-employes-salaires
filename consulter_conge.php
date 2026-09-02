<?php
session_start();
include 'ConnectDb.php';  // Connexion à la base de données
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Vérification de l'authentification de l'utilisateur
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');  // Rediriger vers la page de connexion si non autorisé
    exit;
}

// Récupérer l'ID de l'employé depuis la session
$user_id = $_SESSION['user_id']; 

// Récupérer l'ID de l'employé à partir de l'URL
$employe_id = $_GET['employe_id']; // Passé depuis l'URL

// Vérifier si l'utilisateur a accès à cette page
if ($user_id != $employe_id && $_SESSION['role'] != 'admin') {
    header("Location: login.php");  // Rediriger si l'employé essaie d'accéder à une page qu'il ne devrait pas
    exit();
}

// Requête SQL pour récupérer les demandes de congé de cet employé
$sql = "SELECT * FROM conges WHERE employe_id = ?";
$stmt = $data->prepare($sql);
$stmt->bind_param("i", $employe_id);  // Bind l'ID de l'employé
$stmt->execute();
$result = $stmt->get_result();

// Initialiser la variable pour le total des jours de congé approuvés
$total_jours_approuves = 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Consulter mes Congés</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7fc;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
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
        .sidebar {
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            background-color: #2c3e50; /* Dark blue-gray for sidebar */
            color: white;
            padding-top: 30px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            transition: 0.3s;
        }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 40px;
            font-size: 22px;
        }

        .sidebar .btn {
            width: 100%;
            margin-bottom: 10px;
            font-size: 16px;
            padding: 12px;
            color: white;
            background-color: #2980b9; /* Bright blue for buttons */
            border: none;
            border-radius: 5px;
            transition: 0.3s;
        }
        .sidebar .btn:hover {
            background-color: #3498db; /* Lighter blue on hover */
        }

        .sidebar .btn:focus {
            outline: none;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
        }
        .main-content {
            margin-left: 270px;
            padding: 20px;
            padding-top: 100px; 
        }
        .content {
            margin-left: 240px;
            padding: 40px;
            margin-top: 80px;
            width: calc(100% - 240px);
            box-sizing: border-box;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 80px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
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
    <h2>Menu</h2>
    </br>
    </br>
    <button class="btn btn-primary" onclick="window.location.href='employee_dashboard.php'">Mon Profil</button>
    <button class="btn btn-primary" onclick="window.location.href='conge.php?employe_id=<?php echo $user_id; ?>'">Demander un congé</button>
    <button class="btn btn-primary" onclick="window.location.href='consulter_conge.php?employe_id=<?php echo $user_id; ?>'">Consulter mes congés</button>
    <button class="btn btn-primary" onclick="window.location.href='employe_fiches_paie.php?employe_id=<?php echo $employe_id; ?>'">Fiche Paie</button>
    <a href="logout.php" class="btn btn-primary">Se déconnecter</a>
</div>

<!-- Content -->
<div class="content">
<img src="logo/img.png" alt="FST ProGestion Logo" class="logo">
    <div class="header">
        <h1>Consulter mes Demandes de Congé</h1>
    </div>
        
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Type de Congé</th>
                    <th>Date de Début</th>
                    <th>Date de Fin</th>
                    <th>Nombre de Jours</th>  <!-- Nouvelle colonne -->
                    <th>Statut</th>
                    <th>Date de Demande</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($conge = $result->fetch_assoc()): ?>
                    <?php 
                        // Calculer la différence en jours entre la date de début et de fin
                        $date_debut = strtotime($conge['date_debut']);
                        $date_fin = strtotime($conge['date_fin']);
                        $nb_jours = ($date_fin - $date_debut) / (60 * 60 * 24); // Différence en jours

                        // Ajouter le nombre de jours au total seulement si le congé est approuvé
                        if ($conge['statut'] == 'Approuvé') {
                            $total_jours_approuves += $nb_jours;
                        }

                        // Appliquer des couleurs en fonction du statut
                        $status_class = '';
                        if ($conge['statut'] == 'Approuvé') {
                            $status_class = 'bg-success text-white'; // Vert pour accepté
                        } elseif ($conge['statut'] == 'Refusé') {
                            $status_class = 'bg-danger text-white'; // Rouge pour refusé
                        }
                    ?>
                    <tr>
                        <td><?php echo $conge['type_conge']; ?></td>
                        <td><?php echo $conge['date_debut']; ?></td>
                        <td><?php echo $conge['date_fin']; ?></td>
                        <td><?php echo $nb_jours; ?> jour(s)</td>  <!-- Affichage du nombre de jours -->
                        <td class="<?php echo $status_class; ?>"><?php echo $conge['statut']; ?></td>  <!-- Couleur selon statut -->
                        <td><?php echo $conge['date_demande']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Affichage du total des jours de congé approuvés -->
        <div class="alert alert-info">
            <strong>Total des jours de congé approuvés : </strong> <?php echo $total_jours_approuves; ?> jour(s)
        </div>
   
</div>

</body>
</html>
