<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Función para validar si el usuario tiene el rol adecuado
function checkRole($allowed_roles) {
    if (!in_array($_SESSION['rol'], $allowed_roles)) {
        header("Location: unauthorized.php"); // Redirige a una página de acceso denegado
        exit();
    }
}

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) { // 30 minutos
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

?>
