<?php
require 'auth.php';
require 'db.php';
checkRole(['Admin']); // Solo el Admin puede acceder

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $response_comments = $_POST['response_comments'];
    
    // Manejo de archivo adjunto
    $response_attachment = NULL;
    if (isset($_FILES['response_attachment']) && $_FILES['response_attachment']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["response_attachment"]["name"]);
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Verificar que sea un archivo PDF
        if ($file_type != "pdf") {
            die("Solo se permiten archivos PDF.");
        }

        if (move_uploaded_file($_FILES["response_attachment"]["tmp_name"], $target_file)) {
            $response_attachment = $target_file;
        } else {
            die("Error al subir el archivo.");
        }
    }

    // Actualizar la base de datos con la respuesta y el archivo adjunto
    $sql = "UPDATE solicitudes SET response_comments = ?, response_attachment = ?, fecha_actualizacion = NOW(), estado = 'Finalizado' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $response_comments, $response_attachment, $id);
    
    if ($stmt->execute()) {
        header("Location: admin.php?success=Respuesta enviada correctamente");
    } else {
        echo "Error al guardar la respuesta.";
    }
    
    $stmt->close();
    $conn->close();
}
?>
