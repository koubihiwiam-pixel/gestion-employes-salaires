<?php
// Vérifier si la session est déjà démarrée
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'ConnectDb.php';

// Requête pour compter le nombre de demandes en attente
$count_sql = "SELECT COUNT(*) AS count FROM conges WHERE statut = 'En attente'";
$count_result = mysqli_query($data, $count_sql);
$count_row = mysqli_fetch_assoc($count_result);
$pending_requests = $count_row['count']; // Nombre de demandes en attente

// Vérification de l'authentification de l'utilisateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');  // Rediriger vers la page de connexion si non autorisé
    exit;
}

// 1. Nombre total d'employés
$sql_total = "SELECT COUNT(*) AS total_employes FROM Employes";
$result_total = mysqli_query($data, $sql_total);
$total_employes = mysqli_fetch_assoc($result_total)['total_employes'];

// 2. Ancienneté moyenne des employés
$sql_anciennete = "SELECT AVG(DATEDIFF(CURDATE(), date_debut) / 365) AS anciennete_moyenne FROM contrats WHERE date_debut IS NOT NULL";

$result_anciennete = mysqli_query($data, $sql_anciennete);
$anciennete_moyenne = mysqli_fetch_assoc($result_anciennete)['anciennete_moyenne'];

// 3. Calcul du taux d'absences
$sql_absences = "SELECT COUNT(*) AS total_absences FROM presence WHERE statut = '❌'";
$result_absences = mysqli_query($data, $sql_absences);
$total_absences = mysqli_fetch_assoc($result_absences)['total_absences'];
$taux_absences = ($total_absences / $total_employes) * 100;

// 4. Calcul du taux de salaire moyen
$sql_salaire_moyen = "SELECT AVG(salaire_base) AS salaire_moyen FROM contrats WHERE salaire_base IS NOT NULL";
$result_salaire_moyen = mysqli_query($data, $sql_salaire_moyen);
$salaire_moyen = mysqli_fetch_assoc($result_salaire_moyen)['salaire_moyen'];

// 5. Récupérer tous les salaires des employés
$sql_salaire = "SELECT salaire_base FROM contrats";
$result_salaire = mysqli_query($data, $sql_salaire);
$salaires = [];
while($row = mysqli_fetch_assoc($result_salaire)) {
    $salaires[] = $row['salaire_base'];
}

// 6. Répartition des employés par département
$sql_departements = "SELECT DEPARTEMENT, COUNT(*) AS nombre_employes FROM Employes GROUP BY DEPARTEMENT";
$result_departements = mysqli_query($data, $sql_departements);

// 7. Répartition des âges des employés
$sql_age_distribution = "SELECT FLOOR(DATEDIFF(CURDATE(), Date_de_naissance) / 365 / 10) * 10 AS age_group, COUNT(*) AS number_of_employees
                         FROM Employes WHERE Date_de_naissance IS NOT NULL GROUP BY age_group";
$result_age_distribution = mysqli_query($data, $sql_age_distribution);

// Préparer les tranches d'âge et le nombre d'employés par tranche
$age_groups = [];
$age_counts = [];
while($row = mysqli_fetch_assoc($result_age_distribution)) {
    $age_groups[] = $row['age_group'] . ' - ' . ($row['age_group'] + 9) . ' ans';
    $age_counts[] = $row['number_of_employees'];
}

// 8. Répartition des congés
$sql_conges = "SELECT statut, COUNT(*) AS count_conges FROM conges GROUP BY statut";
$result_conges = mysqli_query($data, $sql_conges);

// Préparer les statuts des congés et leur nombre
$conges_status = [];
$conges_count = [];
while($row = mysqli_fetch_assoc($result_conges)) {
    $conges_status[] = $row['statut'];
    $conges_count[] = $row['count_conges'];
}

// Récupérer les départements et leur nombre d'employés pour le graphique Pie
$departements = [];
$nombre_employes = [];
while($row = mysqli_fetch_assoc($result_departements)) {
    $departements[] = $row["DEPARTEMENT"];
    $nombre_employes[] = $row["nombre_employes"];
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Tableau de Bord des Employés</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    /* Styles pour la sidebar */
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

    /* Styles pour le contenu */
    .content {
      margin-left: 250px;
      padding: 40px;
      margin-top: 80px;
      width: calc(100% - 250px);
      box-sizing: border-box;
    }

    .charts-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
      margin-bottom: 20px;
    }

    .chart-container {
      padding: 10px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
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

    .logout-button {
      font-size: 16px;
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

    <!-- Content -->
    <div class="content">
    <img src="logo/img.png" alt="FST ProGestion Logo" class="logo">
        <div class="header">
            <h1>📊 Tableau de Bord des Employés</h1>
            <a href="logout.php" class="btn btn-danger logout-button">Se Déconnecter</a>
        </div>

        <div class="charts-container">
            <!-- Graphique Total des Employés -->
            <div class="chart-container">
                <canvas id="totalEmployesChart"></canvas>
            </div>

            <!-- Graphique Répartition par Département -->
            <div class="chart-container">
                <canvas id="departementChart"></canvas>
            </div>

            <!-- Ancienneté Moyenne -->
            <div class="chart-container">
                <h2>Ancienneté Moyenne des Employés: <?php echo round($anciennete_moyenne, 2); ?> ans</h2>
                <canvas id="ancienneteChart"></canvas>
            </div>
            
       
        </div>


        <div class="charts-container">
            <!-- Taux d'Absences -->
            <div class="chart-container">
                <h2>Taux d'Absences: <?php echo round($taux_absences, 2); ?>%</h2>
                <canvas id="absenceChart"></canvas>
            </div>

            <!-- Taux de Salaire Moyen -->
            <div class="chart-container">
                <h2>Salaire Moyen des Employés: <?php echo round($salaire_moyen, 2); ?> MAD</h2>
                <canvas id="salaireChart"></canvas>
            </div>

            <!-- Répartition des âges -->
            <div class="chart-container">
                <h2>Répartition des âges des Employés</h2>
                <canvas id="ageDistributionChart"></canvas>
            </div>
        </div>
        <div class="charts-container">
            <!-- Répartition des congés -->
            <div class="chart-container">
                <h2>Répartition des Congés</h2>
                <canvas id="congesChart"></canvas>
            </div>
        </div>

    </div>

    <!-- JS Bootstrap + Chart.js -->
    <script>
        var ctx1 = document.getElementById('totalEmployesChart').getContext('2d');
        var totalEmployesChart = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: ['Total des Employés'],
                datasets: [{
                    label: 'Nombre d\'Employés',
                    data: [<?php echo $total_employes; ?>],
                    backgroundColor: '#2980b9',
                    borderColor: '#1f6391',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        var ctx2 = document.getElementById('departementChart').getContext('2d');
        var departementChart = new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($departements); ?>,
                datasets: [{
                    data: <?php echo json_encode($nombre_employes); ?>,
                    backgroundColor: ['#FF6347', '#FFD700', '#32CD32', '#20B2AA', '#FF4500', '#8A2BE2'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });

        // Ancienneté Moyenne
        var ctx3 = document.getElementById('ancienneteChart').getContext('2d');
        var ancienneteChart = new Chart(ctx3, {
            type: 'bar',
            data: {
                labels: ['Ancienneté Moyenne'],
                datasets: [{
                    label: 'Ancienneté Moyenne (en années)',
                    data: [<?php echo round($anciennete_moyenne, 2); ?>],
                    backgroundColor: '#FF6347',
                    borderColor: '#D32F2F',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
 // Répartition des Âges
        var ctx6 = document.getElementById('ageDistributionChart').getContext('2d');
        var ageDistributionChart = new Chart(ctx6, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($age_groups); ?>,
                datasets: [{
                    label: 'Nombre d\'Employés par Tranche d\'Âge',
                    data: <?php echo json_encode($age_counts); ?>,
                    backgroundColor: '#20B2AA',
                    borderColor: '#2E8B57',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        // Taux d'Absences
        var ctx4 = document.getElementById('absenceChart').getContext('2d');
        var absenceChart = new Chart(ctx4, {
            type: 'doughnut',
            data: {
                labels: ['Absences', 'Présences'],
                datasets: [{
                    data: [<?php echo $taux_absences; ?>, 100 - <?php echo $taux_absences; ?>],
                    backgroundColor: ['#FF6347', '#32CD32'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });

        // Salaire Moyen
        var ctx5 = document.getElementById('salaireChart').getContext('2d');
        var salaireChart = new Chart(ctx5, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(range(1, count($salaires))); ?>,
                datasets: [{
                    label: 'Salaires des Employés',
                    data: <?php echo json_encode($salaires); ?>,
                    backgroundColor: '#FF6347',
                    borderColor: '#D32F2F',
                    borderWidth: 1
                }, {
                    label: 'Salaire Moyen',
                    data: Array(<?php echo count($salaires); ?>).fill(<?php echo round($salaire_moyen, 2); ?>),
                    backgroundColor: '#FFD700',
                    borderColor: '#FF8C00',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

       

        // Répartition des Congés
        var ctx7 = document.getElementById('congesChart').getContext('2d');
        var congesChart = new Chart(ctx7, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($conges_status); ?>,
                datasets: [{
                    data: <?php echo json_encode($conges_count); ?>,
                    backgroundColor: ['#FF6347', '#FFD700', '#32CD32'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
    </script>
</body>
</html>
