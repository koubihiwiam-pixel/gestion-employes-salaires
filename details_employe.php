<?php
session_start();
include 'ConnectDb.php';

// Récupérer l'ID de l'employé depuis l'URL
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($data, $_GET['id']);
    
    // Récupérer les données de l'employé
    $sql = "SELECT * FROM Employes WHERE Id = '$id'";
    $result = mysqli_query($data, $sql);
    $employee = mysqli_fetch_assoc($result);
} else {
    echo "Aucun employé trouvé.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Détails de l'Employé</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f4f7fc;
      font-family: 'Arial', sans-serif;
    }

    /* Content */
    .content {
      margin: 0 auto;
      padding: 40px;
      max-width: 800px;
      background-color: #fff;
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .table th, .table td {
      text-align: left;
      vertical-align: middle;
    }

     .table th {
      background-color: #2C3E50;
      color: white;
      font-weight: bold;
    }

    .btn-secondary {
      background-color: #2C3E50;
      border: none;
    }

    .btn-secondary:hover {
      background-color: #5a6268;
    }

    /* Styles pour l'impression */
    @media print {
      /* Masquer les éléments non nécessaires à l'impression */
      .btn-secondary, .btn-info {
        display: none;
      }

      /* Garder le contenu principal visible pendant l'impression */
      .content {
        padding: 0;
        margin: 0;
        box-shadow: none;
      }
    }
  </style>
</head>
<body>


  <div class="content">
    <h2 class="mb-4"><br><br>Détails de l'Employé</h2>

    <table class="table table-bordered">
      <tr><th>Nom</th><td><?php echo $employee['Nom']; ?></td></tr>
      <tr><th>Prénom</th><td><?php echo $employee['Prenom']; ?></td></tr>
      <tr><th>CIN</th><td><?php echo $employee['CIN']; ?></td></tr>
      <tr><th>Genre</th><td><?php echo $employee['Genre']; ?></td></tr>
      <tr><th>Date de naissance</th><td><?php echo $employee['Date_de_naissance']; ?></td></tr>
      <tr><th>Adresse</th><td><?php echo $employee['Adresse']; ?></td></tr>
      <tr><th>Téléphone</th><td><?php echo $employee['Telephone']; ?></td></tr>
      <tr><th>Email</th><td><?php echo $employee['Email']; ?></td></tr>
      <tr><th>Situation familiale</th><td><?php echo $employee['Situation']; ?></td></tr>
      <tr><th>Département</th><td><?php echo $employee['DEPARTEMENT']; ?></td></tr>
      <tr><th>Poste</th><td><?php echo $employee['Poste']; ?></td></tr>
      <tr><th>RIB Bancaire</th><td><?php echo $employee['RIB']; ?></td></tr>
    </table>

    <!-- Buttons -->
    <a href="liste_employes.php" class="btn btn-secondary">Retour à la Liste des Employés</a>
    <button class="btn btn-info" onclick="window.print()">Imprimer</button>
  </div>

   

</body>
</html>
