<?php
session_start();
include('ConnectDb.php');

$message = '';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Vérifier si les mots de passe correspondent
    if ($new_password == $confirm_password) {
        // Hacher le mot de passe
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // Mettre à jour le mot de passe dans la base de données
        $user_id = $_SESSION['user_id'];
        $sql = "UPDATE Employes SET Mot_de_pass = ?, is_first_login = 0 WHERE Id = ?";
        $stmt = $data->prepare($sql);
        $stmt->bind_param("si", $hashed_password, $user_id);
        $stmt->execute();

        // Rediriger vers la page de connexion ou tableau de bord
        header("Location: employee_dashboard.php");
        exit();
    } else {
        $message = "❌ Les mots de passe ne correspondent pas";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Changer le mot de passe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7fc;
            font-family: 'Arial', sans-serif;
        }

        .form-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 400px;
            margin: 100px auto;
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
        .logo {
            position: absolute; /* pour positionner le logo */
            top: 10px;
            left: 30px;
            max-width: 200px; /* largeur max */
        }
    </style>
</head>
<body>

    <!-- Formulaire de changement de mot de passe -->
    <div class="form-container">
        <h2 class="text-center mb-4">Changer votre mot de passe</h2>
        <img src="logo/img.png" alt="FST ProGestion Logo" class="logo">

        <!-- Message d'erreur -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-danger text-center">
                <?= $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="new_password" class="form-label">Nouveau mot de passe</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn btn-primary">Changer le mot de passe</button>
        </form>
    </div>

</body>
</html>
