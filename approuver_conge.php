<?php
include 'ConnectDb.php';

// Vérifier si l'ID de la demande est passé dans l'URL
if (isset($_GET['id'])) {
    $conge_id = $_GET['id'];

    // Mettre à jour le statut de la demande de congé à "Approuvé"
    $sql = "UPDATE conges SET statut = 'Approuvé' WHERE id = ?";
    $stmt = $data->prepare($sql);
    $stmt->bind_param("i", $conge_id);

    if ($stmt->execute()) {
        // Rediriger vers la page de gestion des congés après approbation
        header("Location: gestion_conge.php?status=success");
        exit();
    } else {
        // Si une erreur se produit, afficher un message d'erreur
        echo "Erreur lors de l'approbation de la demande de congé.";
    }
} else {
    echo "ID de congé manquant.";
}
?>
