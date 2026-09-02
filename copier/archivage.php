<?php
session_start();
include 'ConnectDb.php'; // Connexion à la base de données

// Vérification de l'authentification de l'utilisateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');  // Rediriger vers la page de connexion si non autorisé
    exit;
}

// Traitement pour archiver une nouvelle fiche de paie
if (isset($_POST['generate_pdf'])) {
    // Récupérer l'ID de l'employé et le mois
    $employe_id = $_POST['employe_id'];
    $mois = date('F Y'); // Exemple : "May 2025"

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
$query = "SELECT f.id, e.Nom, e.Prenom, f.file_path, f.mois, f.date_archivage
          FROM fiches_de_paie f
          JOIN Employes e ON f.employe_id = e.Id";
$fiches_result = mysqli_query($data, $query);

if (!$fiches_result) {
    die("Erreur dans la requête SQL pour récupérer les fiches de paie : " . mysqli_error($data));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Archivage des Fiches de Paie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Header -->
<div class="header" style="background-color: #004c99; color: white; text-align: center; padding: 15px;">
    <h1>Archivage des Fiches de Paie</h1>
</div>

<!-- Content -->
<div class="container mt-5">
    <h2>Liste des Fiches de Paie Archivées</h2>

    <table class="table table-bordered mt-4">
        <thead class="table-dark">
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
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
</div>

</body>
</html>
