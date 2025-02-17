<?php
session_start();
include 'db.php'; // Conexión a la base de datos

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $solicitante = $_POST['solicitante'];
    $candidate_name = $_POST['candidate_name'];
    $department = $_POST['department'];
    $position = $_POST['position'];
    $delivery_time = $_POST['delivery_time'];
    $nivel_prioridad = $_POST['nivel_prioridad'];
    $comments = $_POST['comments'];

    // Manejo de archivos adjuntos
    $uploadDir = 'uploads/';
    $attachmentPaths = [];

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    foreach ($_FILES['attachments']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['attachments']['name'][$key]) {
            $fileName = time() . "_" . basename($_FILES['attachments']['name'][$key]);
            $targetFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['attachments']['tmp_name'][$key], $targetFile)) {
                $attachmentPaths[] = $targetFile;
            }
        }
    }

    $attachments = implode(",", $attachmentPaths); // Guardar múltiples archivos en un string

    // Insertar la solicitud en la base de datos
    $sql = "INSERT INTO solicitudes (solicitante, candidate_name, department, position, delivery_time, nivel_prioridad, attachments, comments, estado) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssss", $solicitante, $candidate_name, $department, $position, $delivery_time, $nivel_prioridad, $attachments, $comments);
    
    if ($stmt->execute()) {
        echo "Application updated successfully.";
    } else {
        echo "Error updating request: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
