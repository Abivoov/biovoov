<?php
require 'auth.php';
require 'db.php'; // Asegura que la conexión a la base de datos esté disponible
checkRole(['Manager']); // Solo los managers pueden ver esta página


// Verificar si la sesión contiene el ID del usuario
if (!isset($_SESSION['user_id'])) {
  die("Error: No hay una sesión activa. Inicia sesión nuevamente.");
}

$user_id = $_SESSION['user_id'];
$sql_user = "SELECT username, profile_picture FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_user = $stmt->get_result();
$user_data = $result_user->fetch_assoc();

// Verificar si hay datos del usuario y asignar valores predeterminados si están vacíos
$_SESSION['user']['username'] = !empty($user_data['username']) ? $user_data['username'] : "Unknown User";
$_SESSION['user']['profile_picture'] = !empty($user_data['profile_picture']) ? $user_data['profile_picture'] : 'uploads/default-avatar.png';

$profile_picture = $_SESSION['user']['profile_picture'];
$stmt->close();
?>




<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Manager Panel - Dashboard Futurista</title>

  <!-- Bootstrap 5 -->
  <link 
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" 
    rel="stylesheet"
  />
  <link 
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css"
  />
  <link rel="stylesheet" href="estilo.css">
  <style>
    /* Fondo oscuro futurista */
    body {
      margin: 0;
      padding: 0;
      background: radial-gradient(circle at top left, #000, #0f0f0f 50%, #1a1a1a 100%);
      color: #fff;
      min-height: 100vh;
      display: flex;
    }

    /* Contenedor principal */
    .container-main {
      display: flex;
      flex: 1;
    }

    /* Sidebar */
    .sidebar {
      width: 250px;
      background-color: #111;
      min-height: 100vh;
      padding: 1rem 0;
    }
    .sidebar .logo {
      font-size: 1.5rem;
      font-weight: 700;
      text-align: center;
      margin-bottom: 2rem;
      color: #1ac6ff;
    }
    .sidebar a {
      display: block;
      padding: 0.75rem 1.5rem;
      color: #ccc;
      text-decoration: none;
    }
    .sidebar a:hover {
      background-color: #222;
      color: #fff;
    }
    .sidebar .active {
      background-color: #222;
      color: #fff;
    }
    .sidebar hr {
      border-color: #333;
      margin: 1rem 0;
    }

    /* Área de contenido */
    .main-content {
      flex: 1;
      padding: 2rem;
    }

    /* Tarjetas futuristas */
    .card-futuristic {
      background-color: #1a1a1a;
      border: none;
      position: relative;
      overflow: hidden;
      color: #fff;
    }
    .card-futuristic::before {
      content: "";
      position: absolute;
      inset: 0;
      border: 1px solid rgba(22, 178, 213, 0.5);
      border-radius: 6px;
      pointer-events: none;
      box-shadow: 0 0 20px rgba(26, 188, 156, 0.5);
    }

    /* Tabla oscura */
    .table-dark.table-striped > tbody > tr:nth-of-type(odd) > * {
      background-color: #2a2a2a;
    }
    .table-dark.table-striped > tbody > tr:nth-of-type(even) > * {
      background-color: #242424;
    }
    .profile-container {
  text-align: center;
  padding: 15px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.profile-pic {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
  border: 1px solid rgb(230, 235, 229); /* Verde neón */
  margin-bottom: 10px;
  cursor: pointer;
}

.username {
  color: #ccc;
  font-weight: bold;
  font-size: 14px;
  margin-bottom: 10px;
  text-align: center;
}
/* Contenedor principal de las tarjetas */
/* Contenedor principal de las tarjetas */
.abifront1 {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 10px;
}

/* Tarjetas pequeñas con diseño separado */
.abifront2 {
  background-color: #1a1a1a;
  border: 1px solid rgba(26, 188, 156, 0.3);
  border-radius: 6px;
  box-shadow: 0 0 10px rgba(26, 188, 156, 0.3);
  padding: 10px;
  text-align: center;
  color: #fff;
  min-width: 200px;
  max-width: 280px;
  height: 90px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

/* Títulos dentro de las tarjetas */
.abifront3 {
  font-size: 0.9rem;
  margin-bottom: 5px;
  font-weight: bold;
  color: #1ac6ff;
}

/* Números dentro de las tarjetas */
.abifront2 p {
  font-size: 1.4rem;
  font-weight: bold;
  margin: 0;
  color: #ffffff;
}



  </style>
</head>
<body>
  <div class="container-main">
    <!-- SIDEBAR -->
    <div class="sidebar">
      <div class="logo"><img class="logoimg" src="img/Logo.png" alt=""></div>
    
      <div class="profile-container">
    <form action="upload_profile_picture.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="profile_picture" id="profilePictureInput" style="display: none;" onchange="this.form.submit()">
        <img src="<?= isset($_SESSION['user']['profile_picture']) && !empty($_SESSION['user']['profile_picture']) 
                     ? htmlspecialchars($_SESSION['user']['profile_picture']) 
                     : 'uploads/default-avatar.png'; ?>" 
             alt="User Avatar" class="profile-pic" onclick="triggerFileInput()">
        <p class="username">
            <?= isset($_SESSION['user']['username']) ? htmlspecialchars($_SESSION['user']['username']) : 'Unknown User'; ?>
        </p>
    </form>
</div>

      <a href="#" class="active"><i class="bi bi-card-list me-2"></i> My Requests</a>
      <button class="btn btn-primary w-100 mt-2" data-bs-toggle="modal" data-bs-target="#solicitudModal">
        <i class="bi bi-file-earmark-plus me-2"></i> New Requests
      </button>
      <hr />
      <a href="Feedback.php">
    <i class="bi bi-chat-left-text me-2"></i> Feedback
</a>
<hr />

      <a href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> logout</a>
    </div>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="main-content">
      <h2 class="mb-4">Manager Panel</h2>
      
      
      



      <!-- Tabla de solicitudes -->
      <div class="card card-futuristic">
        <div class="card-body">
          <h5 class="card-title mb-3">My Requests</h5>
          <div class="table-responsive">
    <table class="table table-dark table-striped align-middle">
        <thead>
            <tr>
                <th>ID</th>
                <th>Applicant</th>
                <th>Candidate</th>
                <th>Nivel de Prioridad</th>
                <th>Estado</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody id="tablaSolicitudes">
            <?php
            include 'db.php'; // Conexión a la base de datos

            $sql = "SELECT id, solicitante, candidate_name, nivel_prioridad, estado, fecha_creacion FROM solicitudes";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0): 
                while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $row['id']; ?></td>
                        <td><?= $row['solicitante']; ?></td>
                        <td><?= $row['candidate_name']; ?></td>
                        <td>
                            <button class="btn btn-sm 
                                <?= ($row['nivel_prioridad'] == 'Very high') ? 'btn-danger' : 
                                    (($row['nivel_prioridad'] == 'High') ? 'btn-warning' : 'btn-success'); ?>">
                                <?= $row['nivel_prioridad']; ?>
                            </button>
                        </td>
                        <td>
                            <span class="badge 
                                <?= ($row['estado'] == 'En proceso') ? 'bg-warning text-dark' : 
                                    (($row['estado'] == 'Finalizado') ? 'bg-success' : 'bg-danger'); ?>">
                                <?= $row['estado']; ?>
                            </span>
                        </td>
                        <td><?= date("Y-m-d H:i:s", strtotime($row['fecha_creacion'])); ?></td>
                    </tr>
                <?php } 
            else: ?>
                <tr><td colspan="6" class="text-center">No records found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

        </div>
      </div>
    </div>
  </div>