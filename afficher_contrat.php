<?php
session_start();
include 'ConnectDb.php';

// Requête pour compter le nombre de demandes en attente
$count_sql = "SELECT COUNT(*) AS count FROM conges WHERE statut = 'En attente'";
$count_result = mysqli_query($data, $count_sql);
$count_row = mysqli_fetch_assoc($count_result);
$pending_requests = $count_row['count']; // Nombre de demandes en attente

// Exécute la requête pour récupérer les contrats
$sql = "SELECT c.contrat_id, e.Nom, e.Prenom, c.salaire_base, c.avantages_financiers, c.avantages_sociaux, c.avantages_professionnels, c.date_debut, c.date_fin, c.type_contrat
        FROM contrats c
        JOIN employes e ON c.employe_id = e.Id";
$result = mysqli_query($data, $sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Contrats Assignés</title>
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
            position: fixed; /* pour positionner le logo */
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
            <h1>📄 Contrats Assignés</h1>
            <a href="logout.php" class="btn btn-danger logout-button">Se Déconnecter</a>
        </div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID Contrat</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Salaire de Base</th>
                <th>Avantages financiers</th>
                <th>Avantages sociaux</th>
                <th>Avantages professionnels</th>
                <th>Date de Début</th>
                <th>Date de Fin</th>
                <th>Type de Contrat</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo '<tr>';
                    echo '<td>' . $row['contrat_id'] . '</td>';
                    echo '<td>' . $row['Nom'] . '</td>';
                    echo '<td>' . $row['Prenom'] . '</td>';
                    echo '<td>' . $row['salaire_base'] . '</td>';
                    echo '<td>' . $row['avantages_financiers'] . '</td>';
                    echo '<td>' . $row['avantages_sociaux'] . '</td>';
                    echo '<td>' . $row['avantages_professionnels'] . '</td>';
                    echo '<td>' . $row['date_debut'] . '</td>';
                    echo '<td>' . ($row['date_fin'] ? $row['date_fin'] : 'N/A') . '</td>';
                    echo '<td>' . $row['type_contrat'] . '</td>';
                    echo '<td><button class="btn btn-primary btn-sm" onclick="imprimerContrat(this)" 
                    data-id="' . $row['contrat_id'] . '" 
                    data-nom="' . htmlspecialchars($row['Nom']) . '" 
                    data-prenom="' . htmlspecialchars($row['Prenom']) . '" 
                    data-salaire="' . $row['salaire_base'] . '" 
                    data-avantagesf="' . htmlspecialchars($row['avantages_financiers']) . '" 
                    data-avantagess="' . htmlspecialchars($row['avantages_sociaux']) . '" 
                    data-avantagesp="' . htmlspecialchars($row['avantages_professionnels']) . '" 
                    data-debut="' . $row['date_debut'] . '" 
                    data-fin="' . ($row['date_fin'] == '0000-00-00' ? '--' : $row['date_fin']) . '" 
                    data-type="' . $row['type_contrat'] . '">
                    <i class="fa fa-print"></i> Imprimer
                  </button></td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="8">Aucun contrat trouvé.</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>

<!-- JS Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
<script>
function imprimerContrat(button) {
    const id = button.dataset.id;
    const nom = button.dataset.nom;
    const prenom = button.dataset.prenom;
    const salaire = button.dataset.salaire;
    const avantagesf = button.dataset.avantagesf;
    const avantagess = button.dataset.avantagess;
    const avantagesp = button.dataset.avantagesp;
    const debut = button.dataset.debut;
    const fin = button.dataset.fin;
    const type = button.dataset.type;

    const nouvelleFenetre = window.open('', '', 'height=800,width=600');
    nouvelleFenetre.document.write(`
        <html>
        <head>
            <title>Fiche Contrat</title>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
            <style>
                @page { size: A4 portrait; margin: 20mm; }
                body { font-family: Arial, sans-serif; padding: 20px; }
                h2 { text-align: center; margin-bottom: 30px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #000; padding: 10px; text-align: left; }
                .logo { text-align: center; margin-bottom: 20px; }
            </style>
        </head>
        <body>
        <div style="text-align: center; margin-top: 20px;">
            <strong>FST ProGestion</strong>
        </div>

            <h2>Fiche du Contrat</h2>
            <table>
                <tr><th>ID Contrat</th><td>${id}</td></tr>
                <tr><th>Nom</th><td>${nom}</td></tr>
                <tr><th>Prénom</th><td>${prenom}</td></tr>
                <tr><th>Salaire de Base</th><td>${salaire}</td></tr>
                <tr><th>Avantages Financiers</th><td>${avantagesf}</td></tr>
                <tr><th>Avantages Sociaux</th><td>${avantagess}</td></tr>
                <tr><th>Avantages Professionnels</th><td>${avantagesp}</td></tr>
                <tr><th>Date de Début</th><td>${debut}</td></tr>
                <tr><th>Date de Fin</th><td>${fin}</td></tr>
                <tr><th>Type de Contrat</th><td>${type}</td></tr>
            </table>
            <br><p style="text-align:center;">Signature Responsable: ____________________</p>
        </body>
        </html>
    `);
    nouvelleFenetre.document.close();
    nouvelleFenetre.print();
}
</script>



</html>
