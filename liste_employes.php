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
// Vérifier si le formulaire de suppression a été soumis
if (isset($_POST['btn_delete'])) {
    $delete_id = mysqli_real_escape_string($data, $_POST['delete_id']);
    
    // Récupérer les informations de l'employé avant suppression pour l'historique
    $select_sql = "SELECT Nom, Prenom, CIN, Poste FROM Employes WHERE Id = '$delete_id'";
    $select_result = mysqli_query($data, $select_sql);
    
    if (!$select_result) {
        echo "Erreur SQL : " . mysqli_error($data); // Affiche l'erreur si la requête échoue
        exit();
    }

    $employee = mysqli_fetch_assoc($select_result);
    
    if (!$employee) {
        echo "Aucun employé trouvé avec cet ID.";
        exit();
    }
    
    // Commencer la transaction
    mysqli_begin_transaction($data);
    
    try {
        // Supprimer les enregistrements liés dans les autres tables
        $delete_conges_sql = "DELETE FROM conges WHERE employe_id = '$delete_id'";
        mysqli_query($data, $delete_conges_sql);
        
        $delete_presence_sql = "DELETE FROM presence WHERE employe_id = '$delete_id'";
        mysqli_query($data, $delete_presence_sql);
        
        $delete_heures_supp_sql = "DELETE FROM heures_supp WHERE employe_id = '$delete_id'";
        mysqli_query($data, $delete_heures_supp_sql);
        
        $delete_primes_sql = "DELETE FROM primes WHERE employe_id = '$delete_id'";
        mysqli_query($data, $delete_primes_sql);
        
        $delete_fiches_de_paie_sql = "DELETE FROM fiches_de_paie WHERE employe_id = '$delete_id'";
        mysqli_query($data, $delete_fiches_de_paie_sql);
        
        $delete_contrat_sql = "DELETE FROM contrats WHERE employe_id = '$delete_id'";
        mysqli_query($data, $delete_contrat_sql);
        
        // Supprimer l'employé de la table Employes
        $delete_employe_sql = "DELETE FROM Employes WHERE Id = '$delete_id'";
        mysqli_query($data, $delete_employe_sql);
        
        // Ajouter l'employé dans la table employe_supprime pour l'historique
        $raison = "Suppression définitive de l'employé pour raisons administratives.";
        // Échappement des valeurs pour éviter les erreurs de syntaxe
        $insert_employe_supprime_sql = "INSERT INTO employe_supprime (employe_id, nom, prenom, cin, poste, raison, date_suppression) 
                                       VALUES ('$delete_id', '" . mysqli_real_escape_string($data, $employee['Nom']) . "', '" . mysqli_real_escape_string($data, $employee['Prenom']) . "', '" . mysqli_real_escape_string($data, $employee['CIN']) . "', '" . mysqli_real_escape_string($data, $employee['Poste']) . "', '" . mysqli_real_escape_string($data, $raison) . "', NOW())";
        if (!mysqli_query($data, $insert_employe_supprime_sql)) {
            echo "Erreur lors de l'insertion dans employe_supprime: " . mysqli_error($data);
            exit();
        }
        
        // Enregistrer cette suppression dans l'historique
        $insert_historique_sql = "INSERT INTO historique_suppression (employe_id, nom, prenom, raison) 
                                  VALUES ('$delete_id', '" . mysqli_real_escape_string($data, $employee['Nom']) . "', '" . mysqli_real_escape_string($data, $employee['Prenom']) . "', '" . mysqli_real_escape_string($data, $raison) . "')";
        mysqli_query($data, $insert_historique_sql);
        
        // Valider la transaction
        mysqli_commit($data);
        
    } catch (Exception $e) {
        // Si une erreur se produit, annuler la transaction
        mysqli_roll_back($data);
        $_SESSION['message'] = "❌ Une erreur est survenue lors de la suppression.";
    }

    // Redirection après suppression
    header("Location: liste_employes.php");
    exit();
}

// Recherche par CIN si paramètre 'search_CIN' est défini
$search_CIN = isset($_GET['search_CIN']) ? $_GET['search_CIN'] : '';

// SQL pour compter le nombre d'employés
$count_sql = "SELECT COUNT(*) AS total FROM Employes";
if ($search_CIN != '') {
    $count_sql .= " WHERE CIN LIKE '%$search_CIN%'";
}
$count_result = mysqli_query($data, $count_sql);
$total_employees = mysqli_fetch_assoc($count_result)['total'];


?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Liste des Employés</title>
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
            <h1>📋 Liste des Employés</h1>
            <a href="logout.php" class="btn btn-danger logout-button">Se Déconnecter</a>
    </div>


    <!-- Formulaire de recherche par CIN -->

    
 <form method="GET" action="liste_employes.php" class="search-box d-flex align-items-center mb-4">
            <input type="text" class="form-control me-2" name="search_CIN" placeholder="Rechercher par CIN" value="<?php echo htmlspecialchars($search_CIN); ?>">
            <button type="submit" class="btn btn-primary">Rechercher</button>
        </form>


    <?php
    // Recherche par CIN si paramètre 'search_CIN' est défini
    $search_CIN = isset($_GET['search_CIN']) ? $_GET['search_CIN'] : '';

    // SQL pour compter le nombre d'employés
    $count_sql = "SELECT COUNT(*) AS total FROM Employes";
    if ($search_CIN != '') {
        $count_sql .= " WHERE CIN LIKE '%$search_CIN%'";
    }
    $count_result = mysqli_query($data, $count_sql);
    $total_employees = mysqli_fetch_assoc($count_result)['total'];
    ?>

    <h5>Total Employés : <?php echo $total_employees; ?></h5>

    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle text-center">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>CIN</th>
            <th>Poste</th>
            <th colspan="3">Actions</th>
          </tr>
        </thead>
        <tbody>
       <?php
              // SQL pour récupérer les employés avec recherche par CIN
              $sql = "SELECT * FROM Employes";
              if ($search_CIN != '') {
                  $sql .= " WHERE CIN LIKE '%$search_CIN%'";
              }
              $result = mysqli_query($data, $sql);

              if (mysqli_num_rows($result) > 0) {
                  while ($row = mysqli_fetch_assoc($result)) {
                      echo '<tr>';
                      echo '<td>' . $row['Id'] . '</td>';
                      echo '<td>' . $row['Nom'] . '</td>';
                      echo '<td>' . $row['Prenom'] . '</td>';
                      echo '<td>' . $row['CIN'] . '</td>';
                      echo '<td>' . $row['Poste'] . '</td>';
                      // Voir plus
                      echo '<td>
                              <a href="details_employe.php?id=' . $row['Id'] . '" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i> Voir plus
                              </a>
                            </td>';
                      // Modifier
                      echo '<td>
                              <a href="Ajouter_employes.php?edit=' . $row['Id'] . '" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> Modifier
                              </a>
                            </td>';
                      // Supprimer
                      echo '<td>
                              <form method="POST" onsubmit="return confirm(\'Voulez-vous vraiment supprimer cet employé ?\');">
                                <input type="hidden" name="delete_id" value="' . $row['Id'] . '">
                                <button type="submit" name="btn_delete" class="btn btn-danger btn-sm">
                                  <i class="fas fa-trash-alt"></i> Supprimer
                                </button>
                              </form>
                            </td>';
                      echo '</tr>';
                  }
              } else {
                  echo '<tr><td colspan="7">Aucun employé trouvé.</td></tr>';
              }
              ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- JS Bootstrap + Icons -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
