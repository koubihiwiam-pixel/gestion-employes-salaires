<?php
// Inclure la connexion à la base de données
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

// Récupérer l'ID de l'employé via la méthode GET
$employe_id = isset($_GET['employe_id']) ? $_GET['employe_id'] : null;

// Affichage de l'ID pour débogage (affiche l'ID de l'employé)
echo "ID Employé: " . $employe_id . "<br>";  // Message de débogage

// Si l'ID n'est pas passé dans l'URL, afficher un message d'erreur et arrêter le script
if ($employe_id === null) {
    die("L'ID de l'employé est manquant dans l'URL.");
}

// Récupérer la liste des types de congé
$types_conge = ['Congé payé', 'Congé maladie', 'Congé sans solde', 'Congé maternité', 'Congé exceptionnel'];

// Initialisation du message de confirmation
$message = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $type_conge = $_POST['type_conge']; // Type de congé
    $date_debut = $_POST['date_debut']; // Date de début
    $date_fin = $_POST['date_fin']; // Date de fin

    // Vérification de l'existence de l'employé dans la table Employes
    $check_emp_sql = "SELECT id FROM Employes WHERE id = '$employe_id'";
    $check_emp_result = mysqli_query($data, $check_emp_sql);
    
    // Si l'employé n'existe pas, afficher un message d'erreur
    if (mysqli_num_rows($check_emp_result) == 0) {
        $message = "L'employé avec l'ID $employe_id n'existe pas.";
    } else {
        // Insérer la demande de congé dans la base de données
        $sql = "INSERT INTO conges (employe_id, type_conge, date_debut, date_fin, statut) 
                VALUES ('$employe_id', '$type_conge', '$date_debut', '$date_fin', 'En attente')";
        
        if (mysqli_query($data, $sql)) {
            $message = "Demande de congé enregistrée avec succès.";
        } else {
            $message = "Erreur lors de l'insertion dans la base de données : " . mysqli_error($data);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Demande de Congé</title>
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
        /* Style pour afficher le message */
        .alert {
            margin-top: 20px;
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
    <button class="btn btn-primary" onclick="window.location.href='conge.php?employe_id=<?php echo $employe_id; ?>'">Demander un congé</button>
    <button class="btn btn-primary" onclick="window.location.href='consulter_conge.php?employe_id=<?php echo $employe_id; ?>'">Consulter mes congés</button>
    <button class="btn btn-primary" onclick="window.location.href='employe_fiches_paie.php?employe_id=<?php echo $employe_id; ?>'">Fiche Paie</button>
    <a href="logout.php" class="btn btn-primary">Se déconnecter</a>
</div>

<!-- Content -->
<div class="content">
<img src="logo/img.png" alt="FST ProGestion Logo" class="logo">
    <div class="header">
        <h1>Demande de Congé pour l'Employé ID: <?php echo $employe_id; ?></h1>
    </div>

    <!-- Affichage du message de confirmation ou d'erreur -->
    <?php if ($message): ?>
        <div class="alert alert-info">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <!-- Formulaire de demande de congé -->
    <form method="POST" action="">
        <div class="form-group">
            <label for="type_conge">Type de Congé:</label>
            <select name="type_conge" id="type_conge" class="form-control" required>
                <?php foreach ($types_conge as $type): ?>
                    <option value="<?php echo $type; ?>"><?php echo $type; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="date_debut">Date de début:</label>
            <input type="date" id="date_debut" name="date_debut" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="date_fin">Date de fin:</label>
            <input type="date" id="date_fin" name="date_fin" class="form-control" required><br><br>
        </div>
        <button type="submit" class="btn btn-success">Envoyer la demande</button>
    </form>

   
</div>

</body>
</html>
