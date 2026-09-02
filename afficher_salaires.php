<?php
session_start();
include 'ConnectDb.php'; // Connexion à la base de données
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Requête pour compter le nombre de demandes en attente
$count_sql = "SELECT COUNT(*) AS count FROM conges WHERE statut = 'En attente'";
$count_result = mysqli_query($data, $count_sql);
$count_row = mysqli_fetch_assoc($count_result);
$pending_requests = $count_row['count']; 

// Vérification de l'authentification de l'utilisateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');  // Rediriger vers la page de connexion si non autorisé
    exit;
}
// Initialisation des variables
$salaire_net = 0;
$salaire_base = 0;
$heures_supp = 0;
$prime_anciennete = 0;
$prime_performance = 0;
$total_absences = 0;
$montant_heures_supp = 0;
$retenue_absence = 0;
$assurance_maladie = 0; // Déduction assurance maladie
$autres_deductions = 0;  // Déductions supplémentaires (par exemple, impôts, autres cotisations)
// Recherche par CIN
$search_CIN = '';
if (isset($_POST['search_CIN'])) {
    $search_CIN = $_POST['search_CIN'];
}
// Requête pour récupérer les employés avec leur salaire brut depuis la table 'contrats'
$query = "SELECT e.Id, e.Nom, e.Prenom, e.CIN, c.salaire_base 
          FROM Employes e
          JOIN contrats c ON e.Id = c.employe_id";
// Si un CIN est spécifié, ajouter une condition pour la recherche
if (!empty($search_CIN)) {
    $query .= " WHERE e.CIN LIKE '%$search_CIN%'";
}
$employes_result = mysqli_query($data, $query);


// Vérifier si la requête a échoué
if (!$employes_result) {
    die("Erreur dans la requête SQL pour récupérer les employés : " . mysqli_error($data));
}

// Récupérer les données des employés et calculer le salaire
$employes = [];
while ($row = mysqli_fetch_assoc($employes_result)) {
    // Récupérer les informations de chaque employé
    $employe_id = $row['Id'];
    $nom = $row['Nom'];
    $prenom = $row['Prenom'];
    $cin = $row['CIN'];
    $salaire_base = $row['salaire_base'];

    // Heures supplémentaires
    $heures_supp_query = "SELECT nombre_heures, taux_horaire FROM heures_supp WHERE employe_id = '$employe_id'";
    $heures_supp_result = mysqli_query($data, $heures_supp_query);
    $heures_supp = mysqli_fetch_assoc($heures_supp_result);
    if ($heures_supp) {
        $montant_heures_supp = $heures_supp['nombre_heures'] * $heures_supp['taux_horaire'];
    } else {
        $montant_heures_supp = 0; // Si aucun résultat, pas d'heures supplémentaires
    }

    // Primes
    $primes_query = "SELECT prime_anciennete, prime_performance FROM primes WHERE employe_id = '$employe_id'";
    $primes_result = mysqli_query($data, $primes_query);
    $primes = mysqli_fetch_assoc($primes_result);
    if ($primes) {
        $prime_anciennete = $primes['prime_anciennete'];
        $prime_performance = $primes['prime_performance'];
    } else {
        $prime_anciennete = 0; // Si aucun résultat, pas de prime d'ancienneté
        $prime_performance = 0; // Si aucun résultat, pas de prime de performance
    }

    // Absences (compter les jours d'absence dans la table `presence` avec le statut '❌')
    $absences_query = "SELECT COUNT(*) AS absences FROM presence WHERE employe_id = '$employe_id' AND statut = '❌'";
    $absences_result = mysqli_query($data, $absences_query);
    $absences = mysqli_fetch_assoc($absences_result);
    if ($absences) {
        $total_absences = $absences['absences'];
    } else {
        $total_absences = 0; // Si aucun résultat, aucune absence
    }

    // Calcul de la retenue pour absences (supposons qu'une absence coûte une journée de salaire)
    $retenue_absence = ($salaire_base / 22) * $total_absences;

    // Dédiction d'assurance maladie par défaut (5% du salaire brut pour tous les employés)
    $assurance_maladie = $salaire_base * 0.05; // Exemple : 5% du salaire brut

    // Dédictions supplémentaires (par exemple, impôts, cotisations, etc.)
    // Exemple : Déduction de 3% pour les impôts
    $autres_deductions = $salaire_base * 0.03;

    // Calcul du salaire net
    $salaire_net = $salaire_base + $prime_anciennete + $prime_performance + $montant_heures_supp - $retenue_absence - $assurance_maladie - $autres_deductions;

    // Ajouter les informations de l'employé dans le tableau
    $employes[] = [
        'Id' => $employe_id,  // Ajouter l'ID pour l'identifier lors de la génération du PDF
        'Nom' => $nom,
        'Prenom' => $prenom,
        'CIN' => $cin,
        'Salaire brut' => number_format($salaire_base, 2),
        'Heures Supplémentaires' => number_format($montant_heures_supp, 2),
        "Prime d'ancienneté" => number_format($prime_anciennete, 2),
        'Prime de performance' => number_format($prime_performance, 2),
        'Absences' => $total_absences,
        'Assurance Maladie' => number_format($assurance_maladie, 2),
        'Autres Deductions' => number_format($autres_deductions, 2),
        'Salaire Net' => number_format($salaire_net, 2)
    ];
}

// Vérifier et créer le dossier "archives" si nécessaire
if (!file_exists('archives')) {
    mkdir('archives', 0777, true); // Créer le dossier avec les permissions appropriées
}

// Générer le PDF pour un employé spécifique
require_once('fpdf/fpdf.php');
$pdf = new FPDF();
$pdf->AddPage();

// Utilisez une police qui supporte UTF-8
$pdf->SetFont('Arial', '', 12); 

if (isset($_POST['generate_pdf'])) {
    $employe_id = $_POST['employe_id'];
    $employe = array_filter($employes, function($e) use ($employe_id) {
        return $e['Id'] == $employe_id;
    });
    $employe = reset($employe);  // Extraire l'employé correspondant à l'ID
    
    // Titre générique
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Fiche de Paie', 0, 1, 'C');

    // Ajouter la date du mois
    $pdf->SetFont('Arial', 'I', 12);
    $pdf->Cell(0, 10, 'Mois: ' . date('F Y'), 0, 1, 'C');  // Afficher le mois actuel

    // Mise en forme du tableau : centré et large
    $pdf->SetFont('Arial', '', 12);
    $cell_width = 90;
    $pdf->SetX(10); // Décalage pour centrer le tableau

    // Ajouter des grandes cellules
    $pdf->Cell($cell_width, 15, 'Nom', 1, 0, 'C');
    $pdf->Cell($cell_width, 15, utf8_decode($employe['Nom']), 1, 1, 'C');

    $pdf->Cell($cell_width, 15, utf8_decode('Prénom'), 1, 0, 'C');
    $pdf->Cell($cell_width, 15, utf8_decode($employe['Prenom']), 1, 1, 'C');

    $pdf->Cell($cell_width, 15, 'Salaire brut', 1, 0, 'C');
    $pdf->Cell($cell_width, 15, utf8_decode($employe['Salaire brut']) . ' DH', 1, 1, 'C');

    $pdf->Cell($cell_width, 15, utf8_decode('Heures Supplémentaires'), 1, 0, 'C');
    $pdf->Cell($cell_width, 15, utf8_decode($employe['Heures Supplémentaires']) . ' DH', 1, 1, 'C');

    $pdf->Cell($cell_width, 15, utf8_decode('Prime d\'ancienneté'), 1, 0, 'C');
    $pdf->Cell($cell_width, 15, utf8_decode($employe["Prime d'ancienneté"]) . ' DH', 1, 1, 'C');

    $pdf->Cell($cell_width, 15, 'Prime de performance', 1, 0, 'C');
    $pdf->Cell($cell_width, 15, utf8_decode($employe['Prime de performance']) . ' DH', 1, 1, 'C');

    $pdf->Cell($cell_width, 15, 'Absences', 1, 0, 'C');
    $pdf->Cell($cell_width, 15, utf8_decode($employe['Absences']) . ' jours', 1, 1, 'C');

    $pdf->Cell($cell_width, 15, 'Assurance Maladie', 1, 0, 'C');
    $pdf->Cell($cell_width, 15, utf8_decode($employe['Assurance Maladie']) . ' DH', 1, 1, 'C');

    $pdf->Cell($cell_width, 15, 'Autres Deductions', 1, 0, 'C');
    $pdf->Cell($cell_width, 15, utf8_decode($employe['Autres Deductions']) . ' DH', 1, 1, 'C');

    // Afficher le "Salaire Net" en gras
    $pdf->SetFont('Arial', 'B', 12);  // Passer à la police en gras pour le Salaire Net
    $pdf->Cell($cell_width, 15, 'Salaire Net', 1, 0, 'C');
    $pdf->Cell($cell_width, 15, utf8_decode($employe['Salaire Net']) . ' DH', 1, 1, 'C');

    // Vérification et insertion de la fiche de paie uniquement si elle n'existe pas déjà
    $mois = date('F_Y');
    $check_query = "SELECT COUNT(*) AS count FROM fiches_de_paie WHERE employe_id = '$employe_id' AND mois = '$mois'";
    $check_result = mysqli_query($data, $check_query);
    $check_row = mysqli_fetch_assoc($check_result);
    
    if ($check_row['count'] == 0) {
    // Enregistrer la fiche de paie dans la base de données
    $file_path = 'archives/Fiche_de_Paie_' . $employe['Id'] . '_' . $mois . '.pdf';
    $insert_query = "INSERT INTO fiches_de_paie (employe_id, file_path, mois) VALUES ('$employe_id', '$file_path', '$mois')";
    mysqli_query($data, $insert_query);
   }

    // Enregistrer le fichier PDF dans le dossier "archives"
    $file_path = 'archives/Fiche_de_Paie_' . $employe['Id'] . '_' . date('F_Y') . '.pdf';
    $pdf->Output('F', $file_path); // Sauvegarde du fichier sur le serveur

    // Affichage du PDF directement après la génération
    $pdf->Output('I', 'Fiche_de_Paie_' . $employe['Id'] . '.pdf');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
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
            
            top: 2px;
            left: 270px;
            max-width: 150px; /* largeur max */
            position: fixed;
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
            <h1> Situation Financieres</h1>
            <a href="logout.php" class="btn btn-danger logout-button">Se Déconnecter</a>
        </div>

        <form method="POST" class="search-box d-flex align-items-center mb-4">
    <input type="text" class="form-control me-2" name="search_CIN" placeholder="Rechercher par CIN" value="<?php echo htmlspecialchars($search_CIN); ?>">
    <button type="submit" class="btn btn-primary">Rechercher</button>
</form>



    <table class="table table-bordered mt-4">
        <thead class="table-dark">
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>CIN</th>
                <th>Salaire brut</th>
                <th>Heures Supplémentaires</th>
                <th>Prime d'ancienneté</th>
                <th>Prime de performance</th>
                <th>Absences</th>
                <th>Assurance Maladie</th>
                <th>Autres Deductions</th>
                <th>Salaire Net</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($employes as $employe) : ?>
                <tr>
                    <td><?= $employe['Nom'] ?></td>
                    <td><?= $employe['Prenom'] ?></td>
                    <td><?= $employe['CIN'] ?></td>
                    <td><?= $employe['Salaire brut'] ?> DH</td>
                    <td><?= $employe['Heures Supplémentaires'] ?> DH</td>
                    <td><?= $employe["Prime d'ancienneté"] ?> DH</td>
                    <td><?= $employe['Prime de performance'] ?> DH</td>
                    <td><?= $employe['Absences'] ?> jours</td>
                    <td><?= $employe['Assurance Maladie'] ?> DH</td>
                    <td><?= $employe['Autres Deductions'] ?> DH</td>
                    <td><strong><?= $employe['Salaire Net'] ?> DH</strong></td>
                    <td>
                        <!-- Formulaire pour générer le PDF pour cet employé -->
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="employe_id" value="<?= $employe['Id'] ?>">
                            <button type="submit" name="generate_pdf" class="btn btn-primary btn-sm">Générer PDF</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>


</body>
</html>
