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
  background: #000d30;
    color:rgb(247, 248, 253);
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
    border: 1px solid rgb(215, 219, 223); /* Agrega un borde sutil */
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
              <h5 class="card-title">Pending</h5>
              <p class="fs-2"><?= $stats['pendientes'] ?></p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card card-futuristic h-100">
            <div class="card-body">
              <h5 class="card-title">In process</h5>
              <p class="fs-2"><?= $stats['en_proceso'] ?></p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card card-futuristic h-100">
            <div class="card-body">
              <h5 class="card-title">finished</h5>
              <p class="fs-2"><?= $stats['finalizados'] ?></p>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabla de solicitudes pendientes -->
      <div class="card card-futuristic">
        <div class="card-body">
          <h5 class="card-title mb-3">Pending Requests</h5>
          <div class="table-responsive">
            <table class="table table-dark table-striped align-middle">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Applicant</th>
                  <th>Candidate</th>
                  <th>Status</th>
                  <th>Date created</th>
                  <th>Date to be delivered</th>
                  <th>Priority Level</th>
                  <th>Action</th>
                </tr>
              </thead>
            <tbody>
  <?php while ($row = $result->fetch_assoc()) { ?>
    <tr>
    <td><?= $row['id']; ?></td>
    <td><?= $row['solicitante']; ?></td>
    <td><?= $row['candidate_name']; ?></td>
    <td>
        <span class="badge bg-warning text-dark"><?= $row['estado']; ?></span>
    </td>
    <td><?= date("Y-m-d H:i:s", strtotime($row['fecha_creacion'])); ?></td> <!-- Fecha de creación -->
    <td><?= date("Y-m-d H:i:s", strtotime($row['delivery_time'])); ?></td> <!-- Fecha de entrega -->
    
    <!-- Nueva columna de Nivel de Prioridad -->
    <td>
        <span class="badge 
            <?= ($row['nivel_prioridad'] == 'Very high') ? 'bg-danger' : 
                (($row['nivel_prioridad'] == 'High') ? 'bg-warning' : 'bg-success'); ?>">
            <?= $row['nivel_prioridad']; ?>
        </span>
    </td>

    <!-- Botón Reply -->
    <td>
        <button 
            class="btn btn-sm btn-outline-light" 
            data-bs-toggle="modal" 
            data-bs-target="#respuestaModal" 
            onclick="cargarDatos(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>)">
            Reply
        </button>
    </td>
</tr>

  <?php } ?>
</tbody>

            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL PARA RESPONDER SOLICITUD -->
  <div class="modal fade" id="respuestaModal" tabindex="-1" aria-labelledby="respuestaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content bg-dark text-white">
        <div class="modal-header">
          <h5 class="modal-title" id="respuestaModalLabel">Details</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="respuestaForm" enctype="multipart/form-data">
            <input type="hidden" name="id" id="solicitudId">

            <p><strong>Applicant:</strong> <span id="solicitanteNombre"></span></p>
            <p><strong>Candidate:</strong> <span id="candidateNombre"></span></p>
            <p><strong>Status:</strong> <span id="estadoActual"></span></p>
            <p><strong>Feedback:</strong> <span id="comments"></span></p>
            <p><strong>attachments:</strong> <span id="attachments"></span></p>

            <hr>

            <div class="mb-3">
              <label for="estadoNuevo" class="form-label">Update Status</label>
              <select class="form-select" name="estado" id="estadoNuevo">
                <option value="Pendiente">Pending</option>
                <option value="En proceso">In process</option>
                <option value="Finalizado">Finished</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Attach response</label>
              <input type="file" class="form-control" name="response_attachment">
            </div>

            <div class="mb-3">
              <label class="form-label">Add comments</label>
              <textarea class="form-control" name="response_comments" rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-primary w-100">Save changes</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    function cargarDatos(data) {
      // Llenamos los campos del modal con la información de la solicitud
      document.getElementById('solicitudId').value = data.id;
      document.getElementById('solicitanteNombre').innerText = data.solicitante;
      document.getElementById('candidateNombre').innerText = data.candidate_name;
      document.getElementById('estadoActual').innerText = data.estado;
      document.getElementById('comments').innerText = data.comments || 'No comments';

      // Mostrar archivos adjuntos si existen
      let attachmentsHTML = 'There are no attachments';
      if (data.attachments) {
        const files = data.attachments.split(',');
        if (files.length > 0 && files[0] !== '') {
          attachmentsHTML = files.map(file => {
            return `<a href="${file}" target="_blank" class="btn btn-outline-light btn-sm me-1 mb-1">Ver Archivo</a>`;
          }).join('');
        }
      }
      document.getElementById('attachments').innerHTML = attachmentsHTML;

      // Preseleccionar estado actual en el dropdown
      document.getElementById('estadoNuevo').value = data.estado;
    }

    // Al enviar el formulario, se llama a update_solicitud.php
    document.getElementById('respuestaForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);

      fetch('update_solicitud.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(data => {
        alert(data);
        // Cerrar el modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('respuestaModal'));
        modal.hide();
        // Recargar la página o la tabla
        window.location.reload();
      })
      .catch(error => console.error('Error:', error));
    });
  </script>
  <script>
    function triggerFileInput() {
        document.getElementById('profilePictureInput').click();
    }
</script>
</body>
</html>
