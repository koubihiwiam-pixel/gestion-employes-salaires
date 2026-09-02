<?php
session_start();
include 'ConnectDb.php';

// Récupérer la liste des employés avec leur CIN
$employes_sql = "SELECT Id, CIN, Nom, Prenom FROM Employes";
$employes_result = mysqli_query($data, $employes_sql);

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
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employe_id = $_POST["employe_id"];
    $salaire_base = $_POST["salaire_base"];
    $Avantages_financiers = isset($_POST["avantages_financiers"]) ? implode(", ", $_POST["avantages_financiers"]) : "";
    $Avantages_sociaux = isset($_POST["avantages_sociaux"]) ? implode(", ", $_POST["avantages_sociaux"]) : "";
    $Avantages_professionnels = isset($_POST["avantages_professionnels"]) ? implode(", ", $_POST["avantages_professionnels"]) : "";
    $date_debut = $_POST["date_debut"];
    $date_fin = $_POST["date_fin"] ?? NULL;
    $type_contrat = $_POST["type_contrat"];

    $checkEmployeSql = "SELECT * FROM employes WHERE Id = '$employe_id'";
    $checkEmployeResult = mysqli_query($data, $checkEmployeSql);

    if (mysqli_num_rows($checkEmployeResult) == 0) {
        $_SESSION['message'] = "<div class='alert alert-danger'>L'employé avec l'ID $employe_id n'existe pas.</div>";
    } else {
        $checkContratSql = "SELECT * FROM contrats WHERE employe_id = '$employe_id'";
        $checkContratResult = mysqli_query($data, $checkContratSql);

        if (mysqli_num_rows($checkContratResult) > 0) {
            $_SESSION['message'] = "<div class='alert alert-danger'>Cet employé a déjà un contrat assigné.</div>";
        } else {
            $sql = "INSERT INTO contrats (employe_id, salaire_base, avantages_financiers, avantages_sociaux, avantages_professionnels, date_debut, date_fin, type_contrat) 
                    VALUES ('$employe_id', '$salaire_base', '$Avantages_financiers','$Avantages_sociaux', '$Avantages_professionnels', '$date_debut', '$date_fin', '$type_contrat')";

            if (mysqli_query($data, $sql)) {
                $_SESSION['message'] = "<div class='alert alert-success'>Le contrat a été assigné avec succès.</div>";
            } else {
                $_SESSION['message'] = "<div class='alert alert-danger'>Erreur : " . mysqli_error($data) . "</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Assigner un Contrat</title>
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
            <h1>📄 Assigner un Contrat</h1>
            <a href="logout.php" class="btn btn-danger logout-button">Se Déconnecter</a>
        </div>
    <?php
    if (isset($_SESSION['message'])) {
        echo $_SESSION['message'];
        unset($_SESSION['message']);
    }
    ?>

    <form method="POST">
        <div class="mb-3">
            <label for="employe_id" class="form-label">Choisir un Employé</label>
            <select name="employe_id" class="form-control" required>
                <option value="" disabled selected>Sélectionner un employé</option>
                <?php while ($row = mysqli_fetch_assoc($employes_result)): ?>
                    <option value="<?php echo $row['Id']; ?>"><?php echo $row['CIN'] . ' - ' . $row['Nom'] . ' ' . $row['Prenom']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="salaire_base" class="form-label">Salaire de Base</label>
            <input type="number" class="form-control" name="salaire_base" step="0.01" required>
        </div>

        <!-- Avantages financiers, sociaux et professionnels -->
        <div class="mb-3">
            <label class="form-label"><b>Avantages financiers</b></label><br>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="avantages_financiers[]" value="Salaire_compétitif" id="salaire">
                <label class="form-check-label" for="salaire">Salaire compétitif</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="avantages_financiers[]" value="Primes" id="primes">
                <label class="form-check-label" for="primes">Primes (de performance, d’ancienneté, etc.)</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="avantages_financiers[]" value="Bonus_annuels" id="bonus">
                <label class="form-check-label" for="bonus">Bonus annuels</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="avantages_financiers[]" value="13e_mois" id="13e_mois">
                <label class="form-check-label" for="13e_mois">13e mois</label>
            </div>
            <div class="mb-3">
    <label class="form-label"><b>Avantages sociaux </b></label><br>

    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="avantages_sociaux[]" value="mutuelle" id="salaire">
        <label class="form-check-label" for="mutuelle">Assurance maladie / mutuelle</label>
    </div>

    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="avantages_sociaux[]" value="Assurance_vie" id="primes">
        <label class="form-check-label" for="assurance_vie">Assurance vie</label>
    </div>

    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="avantages_sociaux[]" value="Retraite_complémentaire" id="bonus">
        <label class="form-check-label" for="retraite">Retraite complémentaire</label>
    </div>
</div>
<div class="mb-3">
    <label class="form-label"><b>Avantages professionnels</b></label><br>

    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="avantages_professionnels[]" value="Formations" id="formations">
        <label class="form-check-label" for="formations">Formations prises en charge</label>
    </div>

    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="avantages_professionnels[]" value="Evolution_carriere" id="evolution">
        <label class="form-check-label" for="evolution">Possibilités d’évolution de carrière</label>
    </div>

    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="avantages_professionnels[]" value="Mobilite_interne" id="mobilite">
        <label class="form-check-label" for="mobilite">Mobilité interne</label>
    </div>

    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="avantages_professionnels[]" value="Mentorat" id="mentorat">
        <label class="form-check-label" for="mentorat">Coaching ou mentorat</label>
    </div>
</div>
        </div>

        <!-- Autres sections... -->
        <div class="mb-3">
            <label for="date_debut" class="form-label">Date de Début</label>
            <input type="date" class="form-control" name="date_debut" required>
        </div>

        <div class="mb-3">
            <label for="date_fin" class="form-label">Date de Fin (optionnelle)</label>
            <input type="date" class="form-control" name="date_fin">
        </div>

        <div class="mb-3">
            <label for="type_contrat" class="form-label">Type de Contrat</label>
            <select name="type_contrat" class="form-control" required>
                <option value="CDD">Contrat à Durée Déterminée (CDD)</option>
                <option value="CDI_Temps_Partiel">CDI - Temps Partiel</option>
                <option value="Travail_Temporaire">Travail Temporaire</option>
                <option value="Travail_Intermittent">Travail Intermittent</option>
                <option value="Apprentissage">Apprentissage</option>
                <option value="Professionnalisation">Professionnalisation</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Assigner le Contrat</button>
    </form>
</div>

<!-- JS Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Fermer la connexion
mysqli_close($data);
?>
