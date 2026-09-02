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
// Récupérer les informations de l'employé connecté
$user_id = $_SESSION['user_id']; // ID de l'utilisateur stocké en session
$sql = "SELECT * FROM Employes WHERE Id = ?";
$stmt = $data->prepare($sql);
$stmt->bind_param("i", $user_id);  // Bind the user ID
$stmt->execute();
$result = $stmt->get_result();

// Vérifier si l'employé existe dans la base de données
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();  // Récupérer les données de l'employé
} else {
    echo "Aucun employé trouvé avec cet ID.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord Employé</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* Global Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9; /* Light grey background for professional look */
            color: #495057; /* Dark grey text color for readability */
        }

        h1, h2 {
            font-family: 'Roboto', sans-serif;
            font-weight: bold;
        }

        /* Sidebar Styles */
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

        /* Main Content Styles */
        .main-content {
            margin-left: 230px;
            padding: 90px;
            min-height: 100vh;
        }

        

        .form-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            padding: 15px;
            text-align: left;
            border: 1px solid #dee2e6;
        }

        table th {
            background-color: #34495e; /* Dark gray for header */
            color: white;
        }

        table tr:nth-child(even) {
            background-color: #ecf0f1; /* Light gray for even rows */
        }

        table tr:hover {
            background-color: #bdc3c7; /* Lighter gray on hover */
            transition: background-color 0.3s ease;
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
        <button class="btn" onclick="window.location.href='employee_dashboard.php'">Mon Profil</button>
        <button class="btn" onclick="window.location.href='conge.php?employe_id=<?php echo $user['Id']; ?>'">Demander un congé</button>
        <button class="btn" onclick="window.location.href='consulter_conge.php?employe_id=<?php echo $user['Id']; ?>'">Consulter mes congés</button>
        <button class="btn" onclick="window.location.href='employe_fiches_paie.php?employe_id=<?php echo $user['Id']; ?>'">Fiche Paie</button>
        <a href="logout.php" class="btn">Se Déconnecter</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
    <img src="logo/img.png" alt="FST ProGestion Logo" class="logo">

        <div class="form-container">
            <h2>Informations de l'Employé</h2>
            <!-- Tableau des informations de l'employé -->
            <table>
                <tr>
                    <th>Nom</th>
                    <td><?php echo $user['Nom']; ?></td>
                </tr>
                <tr>
                    <th>Prénom</th>
                    <td><?php echo $user['Prenom']; ?></td>
                </tr>
                <tr>
                    <th>CIN</th>
                    <td><?php echo $user['CIN']; ?></td>
                </tr>
                <tr>
                    <th>Date de naissance</th>
                    <td><?php echo $user['Date_de_naissance']; ?></td>
                </tr>
                <tr>
                    <th>Adresse</th>
                    <td><?php echo $user['Adresse']; ?></td>
                </tr>
                <tr>
                    <th>Situation familiale</th>
                    <td><?php echo $user['Situation']; ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?php echo $user['Email']; ?></td>
                </tr>
                <tr>
                    <th>Téléphone</th>
                    <td><?php echo $user['Telephone']; ?></td>
                </tr>
                <tr>
                    <th>Poste</th>
                    <td><?php echo $user['Poste']; ?></td>
                </tr>
                <tr>
                    <th>Département</th>
                    <td><?php echo $user['DEPARTEMENT']; ?></td>
                </tr>
                <tr>
                    <th>RIB Bancaire</th>
                    <td><?php echo $user['RIB']; ?></td>
                </tr>
                <tr>
                    <th>Rôle</th>
                    <td><?php echo $user['role']; ?></td>
                </tr>
            </table>
        </div>
    </div>


    <!-- JS Bootstrap + Icons -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
