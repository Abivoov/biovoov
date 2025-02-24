<?php
require 'auth.php';
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $estado = isset($_POST['estado']) ? $_POST['estado'] : null;
    $response_comments = isset($_POST['response_comments']) ? $_POST['response_comments'] : '';

    if (!$id || !$estado) {
        die("Error: ID o estado no proporcionado.");
    }

    $sql = "UPDATE solicitudes SET estado = ?, response_comments = ?, fecha_actualizacion = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $estado, $response_comments, $id);
    
    if ($stmt->execute()) {
        echo "Solicitud actualizada correctamente.";
    } else {
        echo "Error al actualizar la solicitud: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo "No se recibió una solicitud POST.";
}



?>
