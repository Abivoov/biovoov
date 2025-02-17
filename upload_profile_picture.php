<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user'])) {
    $user_id = $_SESSION['user']['id'];

    // Verificar si se subió un archivo
    if (!empty($_FILES['profile_picture']['name'])) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . "_" . basename($_FILES['profile_picture']['name']);
        $targetFile = $uploadDir . $fileName;

        // Validar que sea una imagen
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (in_array($_FILES['profile_picture']['type'], $allowedTypes)) {
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
                // Guardar la ruta en la base de datos
                $sql = "UPDATE usuarios SET profile_picture = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $targetFile, $user_id);

                if ($stmt->execute()) {
                    $_SESSION['user']['profile_picture'] = $targetFile;
                    header("Location: admin.php"); // Recargar la página para mostrar la nueva imagen
                    exit;
                } else {
                    echo "Error updating profile picture: " . $stmt->error;
                }
            } else {
                echo "Error uploading file.";
            }
        } else {
            echo "Invalid file type. Only JPG, JPEG, and PNG are allowed.";
        }
    }
}

$conn->close();
?>
