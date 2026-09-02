<?php
session_start();
include('ConnectDb.php');  // Assurez-vous que ce fichier est bien inclus pour établir la connexion

// Initialisation des messages d'erreur
$message = '';

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cin = $_POST['cin'];
    $password = $_POST['password'];

    // Requête pour obtenir les informations de l'utilisateur en fonction du CIN
    $sql = "SELECT Id, Nom, Prenom, CIN, Mot_de_pass, role, is_first_login FROM Employes WHERE CIN = ?";
    $stmt = $data->prepare($sql);
    $stmt->bind_param("s", $cin);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Vérification du mot de passe avec password_verify
        if (password_verify($password, $user['Mot_de_pass'])) {
            // Authentification réussie
            $_SESSION['user_id'] = $user['Id'];
            $_SESSION['cin'] = $user['CIN'];
            $_SESSION['nom'] = $user['Nom'];
            $_SESSION['prenom'] = $user['Prenom'];
            $_SESSION['role'] = $user['role'];

            // Vérifier si c'est la première connexion
            if ($user['is_first_login'] == 1) {
                // Redirection vers la page de changement de mot de passe
                header("Location: change_password.php");
                exit();
            }

            // Redirection vers le tableau de bord de l'employé en fonction du rôle
            if ($_SESSION['role'] == 'admin') {
                // Redirige l'administrateur vers la page Ajouter Employé
                header("Location: dashbord.php");
            } else {
                // Redirige l'employé vers son tableau de bord
                header("Location: employee_dashboard.php");
            }
            exit();
        } else {
            $message = "❌ Mot de passe invalide";
        }
    } else {
        $message = "❌ Aucun utilisateur trouvé avec ce CIN";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Page de Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7fc;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
        }

        .header {
            background-color: #f4f7fc;
            color: white;
            text-align: left;
            padding: 2px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
        }

        .header img {
            width: 180px;
            margin-right: 20px;
        }

        .form-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 400px;
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

        .alert {
            margin-bottom: 20px;
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
        
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <img src="logo/img.png" alt="Logo FST ProGestion"> <!-- Logo à gauche -->
    </div>

    <!-- Formulaire de Connexion -->
    <div class="form-container">
        <h2 class="text-center mb-4">Se connecter</h2>

        <!-- Message d'erreur -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-danger text-center">
                <?= $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="cin" class="form-label">CIN</label>
                <input type="text" class="form-control" id="cin" name="cin" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary">Se connecter</button>
        </form>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; 2025 FST ProGestion. Tous droits réservés.</p>
    </div>

</body>
</html>
