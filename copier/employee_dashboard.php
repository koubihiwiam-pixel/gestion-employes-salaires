<?php
session_start();
include('ConnectDb.php');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");  // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
    exit();
}

// Récupérer les informations de l'employé connecté
$user_id = $_SESSION['user_id']; // ID de l'utilisateur stocké en session
echo "User ID: " . $user_id . "<br>"; // Afficher l'ID de l'utilisateur pour le débogage

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
    <style>
        body {
            background-color: #f4f7fc;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
        }

        .header {
            background-color: #004c99;
            color: white;
            text-align: center;
            padding: 15px;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
        }

        .header h1 {
            font-size: 24px;
        }

        .form-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 50px auto;
        }

        .btn-primary {
            width: 100%;
            font-size: 16px;
            padding: 12px;
            background-color: #007bff;
            border: none;
            border-radius: 8px;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .sidebar {
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
            padding-left: 20px;
        }

        .sidebar h2 {
            color: white;
            font-size: 20px;
        }

        .sidebar .btn {
           width: 85%;
           margin-bottom: 10px;
           font-size: 16px;
           padding: 10px;
        }

        .sidebar .btn:hover {
            background-color: #0056b3;
        }

        .footer {
            background-color: #343a40;
            color: white;
            text-align: center;
            padding: 15px;
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
        }

        .main-content {
            margin-left: 270px;
            padding: 20px;
        }

        /* Style de la table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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

    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Menu</h2>
        </br>
        </br>
        <button class="btn btn-primary" onclick="window.location.href='employee_dashboard.php'">Mon Profil</button>
        <button class="btn btn-primary" onclick="window.location.href='conge.php?employe_id=<?php echo $user['Id']; ?>'">Demande un congé</button>

        <a href="logout.php" class="btn btn-primary">Se déconnecter</a>
    </div>

    <!-- Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <h1>Tableau de bord de l'Employé</h1>
        </div>

        <div class="form-container">
            <h2 class="text-center mb-4">Informations de l'Employé</h2>

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
                    <td><?php echo $user['role']; ?></td>  <!-- Affichage du rôle -->
                </tr>
            </table>

            
        </div>
    </div>
</body>
</html>
