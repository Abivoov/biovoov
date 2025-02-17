<?php
include 'db.php'; // Asegúrate de que este archivo tiene la conexión a la base de datos

// Define los datos del usuario a registrar
$email = "bertha.sanchez@voov.io";
$password = "admin2025**";
$rol = "Admin";

// Hashear la contraseña antes de almacenarla
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insertar el usuario en la base de datos
$stmt = $conn->prepare("INSERT INTO usuarios (email, password, rol) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $email, $hashed_password, $rol);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "Usuario registrado con éxito.";
} else {
    echo "Error al registrar el usuario.";
}

$stmt->close();
$conn->close();
?>
