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
  <meta charset="UTF-8" />
  <title>Login - VOOV BIO</title>
  <link 
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" 
    rel="stylesheet"
  />
  <style>
    body {
      background: linear-gradient(to bottom, #000000, #2a2a2a);
      color: #fff;
      height: 100vh;
      margin: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Roboto', sans-serif;
    }
    .login-container {
      background-color: #121212;
      padding: 2rem;
      border-radius: 9px;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 0 15px rgba(0,0,1,5.5);
    }
    .login-title {
      font-size: 1rem;
      margin-bottom: 1.2rem;
      text-align: center;
      font-weight: 700;
      margin-top: 2rem;  /* Aumenta la separación desde el logo */
    }
    .form-label {
      color: #ccc;
    }
    .form-control {
      background-color: #222;
      border: 1px solid #444;
      color: #fff;
    }
    .form-control:focus {
      background-color: #222;
      color: #fff;
      outline: none;
      box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
    }
    .btn-primary {
      background-color: #1067b6;
      border: none;
      
    }
    .btn-primary:hover {
      
      background-color: #61CE70;
    }
    .error-msg {
      color: #f66;
      margin-top: 1rem;
      text-align: center;
    }
    /* Barra de progreso en overlay */
    .progress-screen {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.8);
      display: none; 
      align-items: center; 
      justify-content: center;
      flex-direction: column;
      z-index: 9999;
    }
    .progress-screen h2 {
      margin-bottom: 0.5rem;
      color: #fff;
    }
    .progress-screen h4 {
      margin-bottom: 1rem;
      color: #fff;
    }
    .progress {
      width: 75%;
      max-width: 600px;
    }
    
   .logo1 {
    display: block;
    margin: 0 auto;
    width: 100px;
    height: 100px;
    max-width: 100%; /* Asegura que no exceda el ancho */
}
  </style>
</head>
<body>
  <!-- Div de overlay para la barra de progreso -->
  <div class="progress-screen" id="progressScreen">
  <img src="img/Logo.png" alt="VOOV BIO Logo" style="max-width: 200px;" />
  <h3>Welcome to VOOV BIO</h3>
  <h4>Cargando...</h4>
  <div class="progress">
    <div 
      class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
      role="progressbar" 
      id="progressBar" 
      style="width: 0%"
    ></div>
  </div>
</div>


<?php if (!$loginSuccess) : ?>
  <!-- FORMULARIO DE LOGIN (se muestra si NO hubo login exitoso) -->
  <div class="login-container">
      <img class="logo1" src="img/Logo.png" alt="VOOV BIO Logo"  />
    <h6 class="login-title">Hello, Identify yourself.</h6>
    <form method="POST" action="">
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input 
          type="email" 
          class="form-control" 
          id="email" 
          name="email" 
          placeholder="ej. admin@ejemplo.com" 
          required 
        />
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input 
          type="password" 
          class="form-control" 
          id="password" 
          name="password" 
          placeholder="********" 
          required 
        />
      </div>
      <button type="submit" name="login" class="btn btn-primary w-100">
        Access
      </button>
      <?php if($error): ?>
        <div class="error-msg"><?php echo $error; ?></div>
      <?php endif; ?>
    </form>
  </div>
<?php else: ?>
  <!-- Si el login fue exitoso, mostramos la pantalla de progreso y luego redirigimos por JS -->
  <script>
    // Mostramos la pantalla de carga
    const progressScreen = document.getElementById('progressScreen');
    progressScreen.style.display = 'flex';

    let progress = 0;
    const progressBar = document.getElementById('progressBar');
    const rolUsuario = "<?php echo $rolUsuario; ?>"; 

    // Animar la barra de progreso durante ~3s
    const interval = setInterval(() => {
      progress += 1; // 1% cada 25ms => ~3s total
      progressBar.style.width = progress + '%';

      if (progress >= 100) {
        clearInterval(interval);
        // Redirigir según el rol
        if (rolUsuario === 'Manager') {
          window.location.href = 'Managers.php';
        } else if (rolUsuario === 'Admin') {
          window.location.href = 'admin.php';
        } else {
          // Rol desconocido, fallback
          alert('Rol desconocido. Contacte con el administrador.');
          window.location.href = 'index.php';
        }
      }
    }, 20);
  </script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
