<?php
session_start();
include 'db.php'; // Conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $estado = $_POST['estado'];
    $response_comments = $_POST['response_comments'];

    // Manejo de archivo de respuesta
    $uploadDir = 'uploads/';
    $response_attachment = '';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Si el administrador adjunta un nuevo archivo
    if (!empty($_FILES['response_attachment']['name'])) {
        $fileName = time() . "_" . basename($_FILES['response_attachment']['name']);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['response_attachment']['tmp_name'], $targetFile)) {
            $response_attachment = $targetFile;
        }
    }

    // Actualizamos la solicitud en la base de datos
    // Asumiendo que tu tabla 'solicitudes' tiene las columnas 'response_comments' y 'response_attachment'
    // Si no las tiene, crea las columnas o ajusta el query en consecuencia
    $sql = "UPDATE solicitudes 
            SET estado = ?, response_comments = ?, response_attachment = ? 
            WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo "Error en la preparación de la consulta: " . $conn->error;
        exit;
    }

    $stmt->bind_param("sssi", $estado, $response_comments, $response_attachment, $id);

    if ($stmt->execute()) {
        echo "Application updated successfully.";
    } else {
        echo "Error updating request: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
