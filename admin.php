<?php
require 'auth.php';
require 'db.php';

checkRole(['Admin']); // Asegura que solo los Admin accedan

// Verificar si la sesión contiene el ID del usuario
if (!isset($_SESSION['user_id'])) {
    die("Error: No hay una sesión activa. Inicia sesión nuevamente.");
}

$user_id = $_SESSION['user_id']; // Asegurar que estamos usando el nombre correcto

// Obtener datos del usuario desde la base de datos
$stmt = $conn->prepare("SELECT username, email, profile_picture FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email, $profile_picture);
$stmt->fetch();
$stmt->close();

// Verificar conexión
if (!$conn) {
  die("Error: No se pudo conectar a la base de datos.");
}

// Obtener todas las solicitudes pendientes
$sql = "SELECT * FROM solicitudes WHERE estado IN ('Pendiente', 'En proceso') ORDER BY fecha_creacion DESC";
$result = $conn->query($sql);
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

// Depuración: Mostrar la sesión del usuario (Descomentar para pruebas)
// print_r($_SESSION['user']); exit;
// Obtener estadísticas

$sql_stats = "SELECT 
    COUNT(*) AS total,
    SUM(estado = 'Pendiente') AS pendientes,
    SUM(estado = 'En proceso') AS en_proceso,
    SUM(estado = 'Finalizado') AS finalizados
FROM solicitudes";
$stats_result = $conn->query($sql_stats);
$stats = $stats_result->fetch_assoc();
?>





<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Admin Panel - Dashboard Futurista</title>

  <!-- Bootstrap 5 (CDN) -->
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
      background: #ffffff;
      color:rgb(39, 38, 38);
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
      background-color: #000d30;
      min-height: 100vh;
      padding: 1rem 0;
    }
    .sidebar .logo {
      font-size: 1.5rem;
      font-weight: 700;
      text-align: center;
      margin-bottom: 2rem;
      color: #1ac6ff;
      align-items: center;
      justify-content: center;
    }
    .sidebar a {
      display: block;
      padding: 0.75rem 1.5rem;
      color: #ccc;
      text-decoration: none;
    }
    .sidebar a:hover, .sidebar .active {
      background-color: #ffffff;
      color: #000d30;;
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
      
    }
    

    .card-body {
  background:rgb(222, 221, 221);
    color: #000d30;
}
.table-responsive {
  border-radius: 15px;
  overflow: hidden; /* Evitar que las celdas sobresalgan */
}
/* Aplicar bordes redondeados a la tabla */
.rounded-table {
    border-radius: 12px; /* Bordes redondeados */
    border-collapse: separate !important; /* Necesario para que border-radius funcione */
    border-spacing: 0; /* Asegura que no haya espacios entre las celdas */
    overflow: hidden;
}

/* Bordes redondeados en las esquinas superiores */
.rounded-table thead tr:first-child th {
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
}

/* Bordes redondeados en las esquinas inferiores */
.rounded-table tbody tr:last-child td {
    border-bottom-left-radius: 12px;
    border-bottom-right-radius: 12px;
}

/* Asegurar que la tabla no sobresalga */
.table-responsive {
    border-radius: 15px;
    overflow: hidden;
    border: 1px solid rgb(185, 186, 187); /* Agrega un borde sutil */
}


    .profile-container {
      display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 15px;

}

.profile-pic {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid rgb(242, 245, 241); /* Verde neón */
    margin-bottom: 10px;
}

.username {
    color: #ccc;
    font-weight: bold;
    font-size: 14px;
    margin-bottom: 10px;
}

  </style>
</head>
<body>
  <div class="container-main">
    <!-- SIDEBAR -->
    <div class="sidebar">
      <div class="logo">
        <img class="logoimg" src="img/Logo.png" alt="">
      </div>
      
      <!-- User Profile -->
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

<script>
    function triggerFileInput() {
        document.getElementById('profilePictureInput').click();
    }
</script>

      <a href="#" class="active">
        <i class="bi bi-card-list me-2"></i> Requests
      </a>
      <hr />
      <a href="Feedback.php">
    <i class="bi bi-chat-left-text me-2"></i> Feedback
</a>
      <a href="logout.php">
        <i class="bi bi-box-arrow-right me-2"></i> Log Out
      </a>
    </div>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="main-content">
      <h2 class="mb-4">Admin Panel</h2>
      
      <!-- Tarjetas con estadísticas -->
      <div class="row g-4 mb-4">
        <div class="col-md-4">
          <div class="card card-futuristic h-100">
            <div class="card-body">
              <h5 class="card-title"><i class="bi bi-bell"></i>
              Pending</h5>
              <p class="fs-2"><?= $stats['pendientes'] ?></p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card card-futuristic h-100">
            <div class="card-body">
              <h5 class="card-title"><i class="bi bi-arrow-repeat"></i>
              In process</h5>
              <p class="fs-2"><?= $stats['en_proceso'] ?></p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card card-futuristic h-100">
            <div class="card-body">
              <h5 class="card-title"><i class="bi bi-check-circle-fill text-success"></i>
              finished</h5>
              <p class="fs-2"><?= $stats['finalizados'] ?></p>
            </div>
          </div>
        </div>
      </div>

      <div class="container mt-4">
    <h2>Pending Requests</h2>
    <table class="table table-dark table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Candidate</th>
          <th>Status</th>
          <th>Date Created</th>
          <th>Delivery Time</th>
          <th>Priority Level</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()) { ?>
          <tr>
            <td><?= $row['id']; ?></td>
            <td><?= $row['candidate_name']; ?></td>
            <td><span class="badge bg-warning text-dark"><?= $row['estado']; ?></span></td>
            <td><?= date("Y-m-d H:i:s", strtotime($row['fecha_creacion'])); ?></td>
            <td><?= date("Y-m-d H:i:s", strtotime($row['delivery_time'])); ?></td>
            <td>
              <span class="badge 
                <?= ($row['nivel_prioridad'] == 'Very high') ? 'bg-danger' : 
                    (($row['nivel_prioridad'] == 'High') ? 'bg-warning' : 'bg-success'); ?>">
                <?= $row['nivel_prioridad']; ?>
              </span>
            </td>
            <td>
              <button class="btn btn-sm btn-outline-light btn-reply" 
                data-bs-toggle="modal" 
                data-bs-target="#respuestaModal" 
                data-info='<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?>'>
                Reply
              </button>
<!-- MODAL PARA RESPONDER SOLICITUD -->
  <div class="modal fade" id="respuestaModal" tabindex="-1" aria-labelledby="respuestaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content bg-dark text-white">
        <div class="modal-header">
          <h5 class="modal-title">Edit Request</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="updateForm">
            <input type="hidden" name="id" id="solicitudId">
            
            <div class="mb-3">
              <label class="form-label">Candidate Name</label>
              <input type="text" class="form-control" name="candidate_name" id="candidateNombre">
            </div>
            <div class="mb-3">
              <label class="form-label">Comments</label>
              <textarea class="form-control" name="comments" id="comments"></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Delivery Time</label>
              <input type="datetime-local" class="form-control" name="delivery_time" id="deliveryTime">
            </div>
            <div class="mb-3">
              <label class="form-label">Department</label>
              <select class="form-select" name="department" id="department">
                <option value="HR">HR</option>
                <option value="IT">IT</option>
                <option value="Finance">Finance</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Position</label>
              <input type="text" class="form-control" name="position" id="position">
            </div>
            <div class="mb-3">
              <label class="form-label">Status</label>
              <select class="form-select" name="estado" id="estadoNuevo">
                <option value="Pendiente">Pending</option>
                <option value="En proceso">In Process</option>
                <option value="Finalizado">Finished</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Priority Level</label>
              <select class="form-select" name="nivel_prioridad" id="nivelPrioridad">
                <option value="Normal">Normal</option>
                <option value="High">High</option>
                <option value="Very high">Very High</option>
              </select>
            </div>
            <button type="submit" class="btn btn-primary w-100">Save Changes</button>
          </form>
        </div>
      </div>
    </div>
  </div>            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>

  <script>
    document.addEventListener("click", function(event) {
        let button = event.target.closest(".btn-reply");
        if (button) {
            let data = JSON.parse(button.getAttribute("data-info"));
            cargarDatos(data);
        }
    });

    function cargarDatos(data) {
        document.getElementById('solicitudId').value = data.id;
        document.getElementById('candidateNombre').value = data.candidate_name;
        document.getElementById('comments').value = data.comments || '';
        document.getElementById('deliveryTime').value = data.delivery_time;
        document.getElementById('department').value = data.department;
        document.getElementById('position').value = data.position;
        document.getElementById('estadoNuevo').value = data.estado;
        document.getElementById('nivelPrioridad').value = data.nivel_prioridad;
    }

    document.getElementById("updateForm").addEventListener("submit", function(event) {
        event.preventDefault();
        let formData = new FormData(this);

        fetch('update_request.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            alert(data);
            let modal = bootstrap.Modal.getInstance(document.getElementById("respuestaModal"));
            modal.hide();
            location.reload(); // Recargar página
        })
        .catch(error => console.error("Error updating request:", error));
    });
  </script>

  <!-- Bootstrap JS -->
  <!-- Cargar Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Cargar Bootstrap JS (Bundle con Popper incluido) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>




  <script>
    function triggerFileInput() {
        document.getElementById('profilePictureInput').click();
    }
</script>
</body>
</html>
