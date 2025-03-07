<?php
session_start();
require 'db.php'; // Archivo de conexión a la base de datos

$loginSuccess = false;
$error = ""; // Inicializa la variable de error

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (!empty($email) && !empty($password)) {
        // Buscar usuario por email
        $stmt = $conn->prepare("SELECT id, password, rol, profile_picture FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $hashed_password, $rol, $profile_picture);
            $stmt->fetch();

            // Verificar contraseña
            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $user_id;
                $_SESSION['rol'] = $rol;
                $_SESSION['profile_picture'] = $profile_picture;

                // Redirigir según el rol
                switch ($rol) {
                    case 'Admin':
                        header("Location: admin.php");
                        exit();
                    case 'Manager':
                        header("Location: Managers.php");
                        exit();
                    case 'User':
                        header("Location: user_dashboard.php");
                        exit();
                    default:
                        $error = "Rol no válido.";
                        session_destroy();
                }
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "Correo no encontrado.";
        }

        $stmt->close();
    } else {
        $error = "Todos los campos son obligatorios.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Skill Cloud</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background:  rgba(0, 0, 0, 0.2);
        }
        .login-container {
            display: flex;
            width: 800px;
            height: 500px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.3);
        }
        .info-section {
            flex: 1;
            background: #000d30;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            padding: 30px;
        }
        .form-section {
            flex: 1;
            background:rgb(242, 243, 244);
            padding: 40px;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .form-section h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #000d30;
        }
        .form-control {
            background: transparent;
            border: 1px solid rgba(90, 89, 89, 0.3);
            color: #000d30;
        }
        .form-control::placeholder {
            color: #000d30;
        }
        .btn-login {
            background:rgb(57, 193, 152);
            border: none;
            padding: 10px;
            border-radius: 5px;
            width: 100%;
            margin-top: 15px;
            color: #000d30;
        }
        .btn-login:hover {
            background: #2a7a66;
        }
        .logovoov{
            width: 90px;
        }
    </style>
</head>
<body>
    <div class="login-container">
    
        <div class="info-section">
            <img class="logovoov"src="IMG/Logo.png" alt="">
            <h2>Welcome to Bio Management</h2>
        </div>
        <div class="form-section">
            <h2>Loging</h2>
            <form>
                <div class="mb-3">
                    <input type="email" class="form-control" placeholder="Email">
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control" placeholder="Password">
                </div>
                <button type="submit" class="btn btn-login">GO</button>
            </form>
        </div>
    </div>
</body>
</html>
