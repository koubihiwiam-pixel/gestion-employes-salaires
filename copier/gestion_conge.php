<?php
include 'ConnectDb.php';

// Requête pour récupérer toutes les demandes de congé
$sql = "SELECT conges.id, conges.employe_id, conges.type_conge, conges.date_debut, conges.date_fin, conges.statut, Employes.Nom, Employes.Prenom
        FROM conges
        INNER JOIN Employes ON conges.employe_id = Employes.Id";
$result = mysqli_query($data, $sql);

// Requête pour compter le nombre de demandes en attente
$count_sql = "SELECT COUNT(*) AS count FROM conges WHERE statut = 'En attente'";
$count_result = mysqli_query($data, $count_sql);
$count_row = mysqli_fetch_assoc($count_result);
$pending_requests = $count_row['count']; // Nombre de demandes en attente

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Congés</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
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
        .content {
            margin-left: 240px;
            padding: 40px;
            margin-top: 80px;
            width: calc(100% - 240px);
            box-sizing: border-box;
        }
        .sidebar {
            position: fixed;
            top: 80px;
            left: 0;
            width: 220px;
            height: 100vh;
            background-color: #002a56;
            color: white;
            padding-top: 40px;
            padding-left: 20px;
            border-radius: 0 20px 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .sidebar button {
            width: 80%;
            margin-bottom: 10px;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }
        table {
            width: 100%;
            margin-top: 20px;
        }
        table th, table td {
            text-align: center;
            vertical-align: middle;
        }
        .alert-badge {
        background-color: red;
        color: white;
        padding: 3px 8px;
        font-size: 12px;
        border-radius: 50%;
        margin-left: 8px;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2>Menu</h2>
    
    <button class="btn btn-primary" onclick="window.location.href='Ajouter_employes.php'">Ajouter Employé</button>
    <button class="btn btn-primary" onclick="window.location.href='liste_employes.php'">Liste des Employés</button>
    <button onclick="window.location.href='contrat.php'">Assigner Contrat</button>
    <button onclick="window.location.href='afficher_contrat.php'">Les contrats</button>
    <button onclick="window.location.href='suivi_presence.php'">Suivi Présence</button>

   <!-- Bouton de gestion des congés avec badge si des demandes en attente -->
   <button onclick="window.location.href='gestion_conge.php'" class="btn btn-primary position-relative">
    Les demandes
    <?php if ($pending_requests > 0): ?>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            <?php echo $pending_requests; ?>
            <span class="visually-hidden">demandes non lues</span>
        </span>
    <?php endif; ?>
</div>

<!-- Content -->
<div class="content">
    <div class="header">
        <h1>Gestion des Congés</h1>
    </div>

    <!-- Table des demandes de congé -->
    <table class="table table-bordered">
        <thead class="table-dark">
        <tr>
          <th>Nom</th>
          <th>Prénom</th>
          <th>Type de Congé</th>
          <th>Date de début</th>
          <th>Date de fin</th>
          <th>Nombre de jours</th> 
          <th>Statut</th>
          <th>Actions</th>
        </tr>

        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): 
    // Calculer le nombre de jours
    $dateDebut = new DateTime($row['date_debut']);
    $dateFin = new DateTime($row['date_fin']);
    $interval = $dateDebut->diff($dateFin);
    $nombre_jours = $interval->days + 1; // +1 pour inclure le jour de début
?>
    <tr>
    <td><?php echo htmlspecialchars($row['Nom']); ?></td>
    <td><?php echo htmlspecialchars($row['Prenom']); ?></td>
    <td><?php echo htmlspecialchars($row['type_conge']); ?></td>
    <td><?php echo htmlspecialchars($row['date_debut']); ?></td>
    <td><?php echo htmlspecialchars($row['date_fin']); ?></td>
    <td><?php echo $nombre_jours . " jours"; ?></td> <!-- nombre de jours هنا -->
    <td><?php echo htmlspecialchars($row['statut']); ?></td> <!-- statut -->
    <td> <!-- actions ديال Approuver/Refuser -->
        <?php if ($row['statut'] == 'En attente'): ?>
            <form action="approuver_conge.php" method="get" style="display: inline;">
                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                <button type="submit" class="btn btn-primary btn-sm">Approuver</button>
            </form>
            <form action="refuser_conge.php" method="get" style="display: inline;">
                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                <button type="submit" class="btn btn-danger btn-sm">Refuser</button>
            </form>
        <?php else: ?>
            <span class="badge bg-secondary">Statut finalisé</span>
        <?php endif; ?>
    </td>
</tr>

            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
