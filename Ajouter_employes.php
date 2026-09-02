<?php
session_start();
include 'ConnectDb.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialisation de la variable $editing
$editing = false;
$editData = [];

if (isset($_GET['edit'])) {
    $editing = true;
    $editId = mysqli_real_escape_string($data, $_GET['edit']);
    $sql = "SELECT * FROM Employes WHERE Id = '$editId'";
    $result = mysqli_query($data, $sql);
    if ($row = mysqli_fetch_assoc($result)) {
        $editData = $row;
    }
}
// Vérification de l'authentification de l'utilisateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');  // Rediriger vers la page de connexion si non autorisé
    exit;
}
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
// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nom = $_POST["Nom"];
    $prenom = $_POST["Prenom"];
    $CIN = $_POST["CIN"];
    $Date = $_POST["Date"];
    $Adresse = $_POST["Adresse"];
    $Situation = $_POST["Situation"];
    $Genre = $_POST["genre"];
    $Email = $_POST["Email"];
    $Telephone = $_POST["Telephone"];
    $poste = $_POST["poste"];
    $departement = $_POST["departement"];
    $role = $_POST["role"]; 
    $RIB = $_POST["RIB"];
    $password1 = $CIN . date("Y", strtotime($Date));
    $password = password_hash($password1, PASSWORD_BCRYPT);

    // Vérifier si le CIN existe déjà dans la base de données
    if ($editing) {
        // Si on est en mode édition, ne pas vérifier le CIN si c'est le même CIN de l'employé actuel
        $sql_check_cin = "SELECT * FROM Employes WHERE CIN = '$CIN' AND Id != '$editId'";
    } else {
        // Si on n'est pas en mode édition, vérifier que le CIN n'existe pas déjà
        $sql_check_cin = "SELECT * FROM Employes WHERE CIN = '$CIN'";
    }
    
    $result_check_cin = mysqli_query($data, $sql_check_cin);
    
    if (mysqli_num_rows($result_check_cin) > 0) {
        $message = "❌ Le CIN existe déjà. Veuillez utiliser un autre CIN.";
    } else {
        if ($editing) {
            // Mise à jour de l'employé
            $sql = "UPDATE Employes SET 
                    Nom='$nom',
                    Prenom='$prenom',
                    Genre='$Genre',
                    CIN='$CIN',
                    Adresse='$Adresse',
                    Situation='$Situation',
                    Date_de_naissance='$Date',
                    Email='$Email',
                    Telephone='$Telephone',
                    Poste='$poste',
                    DEPARTEMENT='$departement',
                    role='$role',
                    RIB='$RIB'
                 
                    WHERE Id='$editId'";

            if (mysqli_query($data, $sql)) {
                $_SESSION['message'] = "✅ Employé modifié avec succès !";
            } else {
                $_SESSION['message'] = "❌ Une erreur est survenue lors de la mise à jour.";
            }

            // Redirection vers la liste des employés après la mise à jour
            header("Location: Ajouter_employes.php?edit=$editId");
            exit(); // Toujours utiliser exit après header pour s'assurer que le script ne continue pas à s'exécuter
        } else {
            // Ajout d'un nouvel employé
            $sql = "INSERT INTO Employes (Nom, Prenom, Genre, CIN, Date_de_naissance, Email, Telephone, Poste, DEPARTEMENT, RIB, Mot_de_pass, Adresse, Situation, role)
                    VALUES ('$nom', '$prenom', '$Genre', '$CIN', '$Date', '$Email', '$Telephone', '$poste', '$departement', '$RIB', '$password', '$Adresse', '$Situation', '$role')";

            if (mysqli_query($data, $sql)) {
                $_SESSION['message'] = "✅ Employé ajouté avec succès !";
            } else {
                $_SESSION['message'] = "❌ Une erreur est survenue lors de l'ajout.";
            }
        }
    }
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
            <h1><?php 
    echo $editing ? "<strong>📝 Modifier un Employé</strong>" : "<strong>➕ Ajouter un Employé</strong>"; 
    ?></h1>
            <a href="logout.php" class="btn btn-danger logout-button">Se Déconnecter</a>
        </div>
 

<?php 
    // Message après l'ajout ou modification
    if (isset($_SESSION['message'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['message']; unset($_SESSION['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <!-- Message d'erreur si le CIN existe déjà -->
    <?php if (isset($message)): ?>
      <div class="alert alert-danger text-center">
        <?= $message; ?>
      </div>
    <?php endif; ?>

    <div class="form-container">
      <form method="POST">
        <div class="row">
          <div class="col-md-6">
            <div class="mb-3">
              <label>Nom</label>
              <input type="text" class="form-control" name="Nom" value="<?php echo $editData['Nom'] ?? ''; ?>" required>
            </div>
            <div class="mb-3">
              <label>Prénom</label>
              <input type="text" class="form-control" name="Prenom" value="<?php echo $editData['Prenom'] ?? ''; ?>" required>
            </div>
            <div class="mb-3">
              <label>CIN</label>
              <input type="text" class="form-control" name="CIN" value="<?php echo $editData['CIN'] ?? ''; ?>" required>
            </div>
            <div class="mb-3">
              <label>Date de naissance</label>
              <input type="date" class="form-control" name="Date" value="<?php echo $editData['Date_de_naissance'] ?? ''; ?>" required>
            </div>
            <div class="mb-3">
              <label>Adresse</label>
              <input type="text" class="form-control" name="Adresse" value="<?php echo $editData['Adresse'] ?? ''; ?>" required>
            </div>
            <div class="mb-3">
              <label>Situation familiale</label>
              <select name="Situation" class="form-control" required>
                <?php
                  $situations = ["Célibataire","Marié(e)","Divorcé(e)","Veuf/Veuve"];
                  foreach ($situations as $Situation) {
                      $selected = ($editData['Situation'] ?? '') == $Situation ? 'selected' : '';
                      echo "<option value=\"$Situation\" $selected>$Situation</option>";
                  }
                ?>
              </select>
            </div>
            <div class="mb-3">
              <label>Genre</label>
              <select name="genre" class="form-control" required>
                <?php
                  $genres = ["Femme", "Homme"];
                  foreach ($genres as $genre) {
                      $selected = ($editData['Genre'] ?? '') == $genre ? 'selected' : '';
                      echo "<option value=\"$genre\" $selected>$genre</option>";
                  }
                ?>
              </select>
            </div>
          </div>

          <div class="col-md-6">
            <div class="mb-3">
              <label>Email</label>
              <input type="email" class="form-control" name="Email" value="<?php echo $editData['Email'] ?? ''; ?>" required>
            </div>
            <div class="mb-3">
              <label>Téléphone</label>
              <input type="tel" class="form-control" name="Telephone" pattern="^0[5-7][0-9]{8}$"  value="<?php echo $editData['Telephone'] ?? ''; ?>" required>
            </div>
            <div class="mb-3">
              <label>Poste</label>
              <select name="poste" class="form-control" required>
                <?php
                  $postes = ["Directeur General", "Directeur Technique", "Directeur des ressources humaines", "Developpeur", "Ingenieur", "Chef de projet", "Securite informatique", "Marketing", "Support/Commercial"];
                  foreach ($postes as $poste) {
                      $selected = ($editData['Poste'] ?? '') == $poste ? 'selected' : '';
                      echo "<option value=\"$poste\" $selected>$poste</option>";
                  }
                ?>
              </select>
            </div>
            <div class="mb-3">
              <label>Département</label>
              <select name="departement" class="form-control" required>
                <?php
                  $departements = [
                    "Depatement informatique/developpement",
                    "Departement recherche & developpement",
                    "Departement infrastructure & reseau",
                    "Departement cybersecurite",
                    "Departement data",
                    "Departement ressources humaines",
                    "Departement management",
                    "Departement marketing & comminucation"
                  ];
                  foreach ($departements as $dep) {
                      $selected = ($editData['DEPARTEMENT'] ?? '') == $dep ? 'selected' : '';
                      echo "<option value=\"$dep\" $selected>$dep</option>";
                  }
                ?>
              </select>
            </div>

            <div class="mb-3">
              <label>Rôle</label>
              <select name="role" class="form-control" required>
                <option value="employee" <?php echo ($editData['role'] ?? '') == 'employee' ? 'selected' : ''; ?>>Employé</option>
                <option value="admin" <?php echo ($editData['role'] ?? '') == 'admin' ? 'selected' : ''; ?>>Administrateur</option>
              </select>
            </div>

            <div class="mb-3">
              <label>RIB Bancaire</label>
              <input type="text" class="form-control" name="RIB" value="<?php echo $editData['RIB'] ?? ''; ?>" required>
            </div>
          </div>
        </div>
        <div class="text-center mt-3">
          <button type="submit" class="btn btn-success">
            <?php echo $editing ? "Mettre à jour" : "Ajouter"; ?>
          </button>
        </div>
      </form>
    </div>
  </div>

</body>
</html>
