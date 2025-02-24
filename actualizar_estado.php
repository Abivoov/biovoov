<?php
require 'auth.php';
require 'db.php';
checkRole(['Admin']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $nuevo_estado = $_POST['estado'];

    $estados_validos = ['Pendiente', 'En proceso', 'Finalizado'];
    if (!in_array($nuevo_estado, $estados_validos)) {
        die("Estado inválido.");
    }

    // Actualizar el estado y marcar la solicitud como "notificada"
    $sql = "UPDATE solicitudes SET estado = ?, fecha_actualizacion = NOW(), notificado = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nuevo_estado, $id);
    
    if ($stmt->execute()) {
        header("Location: admin.php?success=Estado actualizado correctamente");
    } else {
        echo "Error al actualizar el estado.";
    }

    $stmt->close();
    $conn->close();
}
?>
