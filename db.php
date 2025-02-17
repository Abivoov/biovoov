<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Activa los errores detallados

$host = "193.203.166.183"; // Asegúrate de que Hostinger usa "localhost" o revisa el host correcto en hPanel
$user = "u161864769_admin_bio"; // Nombre de usuario correcto de la base de datos
$password = "Rufito2019."; // Asegúrate de que la contraseña es correcta
$database = "u161864769_Bio_DataBase"; // Nombre exacto de tu base de datos en Hostinger

// Crear conexión
$conn = new mysqli($host, $user, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
    
    try {
    $conn = new mysqli($host, $user, $password, $database);
    echo "✅ Connection successful";
} catch (mysqli_sql_exception $e) {
    die("❌ Connection failed: " . $e->getMessage());
}
}
?>
