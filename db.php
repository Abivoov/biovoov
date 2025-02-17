<?php
$host = "localhost"; // En XAMPP siempre es "localhost"
$user = "root"; // Usuario por defecto de MySQL en XAMPP
$password = ""; // Sin contraseña por defecto en XAMPP
$database = "biodata"; // Asegúrate de que este nombre es correcto y coincide con phpMyAdmin

$conn = new mysqli($host, $user, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    die("❌ Error de conexión: " . $conn->connect_error);
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Activar errores detallados

?>
