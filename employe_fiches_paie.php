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

// Récupérer l'ID de l'employé depuis l'URL ou la session
$employe_id = isset($_GET['employe_id']) ? $_GET['employe_id'] : $_SESSION['user_id'];

// Vérification de la connexion à la base de données
if ($data->connect_error) {
    die("La connexion à la base de données a échoué : " . $data->connect_error);
}

// Requête pour récupérer toutes les fiches de paie de l'employé, en formatant la date de création
$query = "SELECT id, DATE_FORMAT(date_creation, '%d-%m-%Y') AS date_creation, 
                 file_path, mois, YEAR(date_creation) AS annee 
          FROM fiches_de_paie 
          WHERE employe_id = ?";

$stmt = $data->prepare($query);

// Vérification si la préparation de la requête a échoué
if ($stmt === false) {
    die('Erreur dans la préparation de la requête : ' . $data->error);
}

// Lier les paramètres (l'ID de l'employé est un entier)
$stmt->bind_param("i", $employe_id);

// Exécuter la requête
$stmt->execute();

// Récupérer les résultats
$result = $stmt->get_result();

// Vérification si l'employé a des fiches de paie
if ($result->num_rows === 0) {
    $no_records_message = "Aucune fiche de paie trouvée pour cet employé.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Fiches de Paie de l'Employé</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7fc;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
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
        .content {
            margin-left: 240px;
            padding: 40px;
            margin-top: 80px;
            width: calc(100% - 240px);
            box-sizing: border-box;
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

        .alert-custom {
            background-color: #e74c3c; /* Red for no records */
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 16px;
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
    <button class="btn btn-primary" onclick="window.location.href='conge.php?employe_id=<?php echo $employe_id; ?>'">Demander un congé</button>
    <button class="btn btn-primary" onclick="window.location.href='consulter_conge.php?employe_id=<?php echo $employe_id; ?>'">Consulter mes congés</button>
    <button class="btn btn-primary" onclick="window.location.href='employe_fiches_paie.php?employe_id=<?php echo $employe_id; ?>'">Fiche Paie</button>
    <a href="logout.php" class="btn btn-primary">Se déconnecter</a>
</div>
<!-- Content -->
<div class="content">
    <div class="header">
        <h1>Fiches de Paie de l'Employé ID: <?php echo $employe_id; ?></h1>
    </div>

    <!-- Affichage du message si aucune fiche de paie trouvée -->
    <?php if (isset($no_records_message)) { ?>
        <div class="alert-custom">
            <?php echo $no_records_message; ?>
        </div>
    <?php } ?>

    <!-- Tableau des fiches de paie -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date de Paie</th>
                <th>Fichier de la Fiche</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($fiche = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $fiche['date_creation']; ?></td>
                    <td><a class="btn btn-primary" href="<?php echo $fiche['file_path']; ?>" target="_blank">Voir Fiche</a></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
</body>
</html>