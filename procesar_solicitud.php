<?php
session_start();
include 'db.php'; // Conexión a la base de datos

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Asegurar que la sesión contiene user_id
    if (!isset($_SESSION['user_id'])) {
        die("Error: No user ID found in session.");
    }
    
    $user_id = $_SESSION['user_id'];
    $candidate_name = $_POST['candidate_name'] ?? '';
    $department = $_POST['department'] ?? '';
    $position = $_POST['position'] ?? '';
    $delivery_time = $_POST['delivery_time'] ?? '';
    $nivel_prioridad = $_POST['nivel_prioridad'] ?? '';
    $comments = $_POST['comments'] ?? '';

    // Manejo de archivos adjuntos
    $uploadDir = 'uploads/';
    $attachmentPaths = [];

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Verificar si hay archivos antes de procesarlos
    if (!empty($_FILES['attachments']['name'][0])) {
        foreach ($_FILES['attachments']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['attachments']['name'][$key]) {
                $fileName = time() . "_" . basename($_FILES['attachments']['name'][$key]);
                $targetFile = $uploadDir . $fileName;
                $relativePath = "uploads/" . $fileName; // Guarda la ruta relativa correcta
                
                if (move_uploaded_file($_FILES['attachments']['tmp_name'][$key], $targetFile)) {
                    $attachmentPaths[] = $relativePath; // Guardar la ruta relativa en la BD
                }
            }
        }
    }

    $attachments = implode(",", $attachmentPaths); // Guardar múltiples archivos en un string

    // Insertar la solicitud en la base de datos sin la columna solicitante
    $sql = "INSERT INTO solicitudes (user_id, candidate_name, department, position, delivery_time, nivel_prioridad, attachments, comments, estado) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente')";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        die("Error en la preparación de la consulta: " . $conn->error);
    }

    $stmt->bind_param("isssssss", $user_id, $candidate_name, $department, $position, $delivery_time, $nivel_prioridad, $attachments, $comments);
    
    if ($stmt->execute()) {
        echo "Solicitud creada con éxito.";
    } else {
        echo "Error al insertar la solicitud: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
