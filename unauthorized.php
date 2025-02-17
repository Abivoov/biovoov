<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Denegado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #1a1a2e;
            color: #ffffff;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .card {
            background-color: #16213e;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0px 0px 15px rgba(255, 255, 255, 0.2);
        }
        .btn-custom {
            background-color: #e94560;
            color: white;
            border: none;
        }
        .btn-custom:hover {
            background-color: #ff2e63;
        }

        .lead {
            color: #ffffff;
        }
    </style>
    <script>
        setTimeout(() => {
            window.location.href = "index.php"; // Redirige al login después de 10 segundos
        }, 10000);
    </script>
</head>
<body>

    <div class="card">
        <h1 class="mb-3" style="color:rgb(244, 236, 237);">🚫 Acceso Denegado 🚫</h1>
        <p class="lead">Lo sentimos, pero no tienes permiso para acceder a esta página.</p>
        <p style="color:rgb(244, 236, 237);">Si crees que esto es un error, contacta al administrador del sistema o vuelve a la pagina de inicio.</p>
        <a href="index.php" class="btn btn-custom mt-3">Volver al Login</a>
    </div>

</body>
</html>