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
// Obtener todas las solicitudes
$sql = "SELECT id, candidate_name, department, position, nivel_prioridad, estado, fecha_creacion FROM solicitudes WHERE user_id = ? ORDER BY fecha_creacion DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();



// Conteo de solicitudes hechas hoy
$sql_today = "SELECT COUNT(*) as total FROM solicitudes WHERE user_id = ? AND DATE(fecha_creacion) = CURDATE()";
$stmt_today = $conn->prepare($sql_today);
$stmt_today->bind_param("i", $user_id);
$stmt_today->execute();
$result_today = $stmt_today->get_result();
$row_today = $result_today->fetch_assoc();
$total_today = $row_today['total'];
$stmt_today->close();

// Conteo de solicitudes hechas esta semana
$sql_week = "SELECT COUNT(*) as total FROM solicitudes WHERE user_id = ? AND YEARWEEK(fecha_creacion, 1) = YEARWEEK(CURDATE(), 1)";
$stmt_week = $conn->prepare($sql_week);
$stmt_week->bind_param("i", $user_id);
$stmt_week->execute();
$result_week = $stmt_week->get_result();
$row_week = $result_week->fetch_assoc();
$total_week = $row_week['total'];
$stmt_week->close();

// Conteo de solicitudes hechas este mes
$sql_month = "SELECT COUNT(*) as total FROM solicitudes WHERE user_id = ? AND MONTH(fecha_creacion) = MONTH(CURDATE()) AND YEAR(fecha_creacion) = YEAR(CURDATE())";
$stmt_month = $conn->prepare($sql_month);
$stmt_month->bind_param("i", $user_id);
$stmt_month->execute();
$result_month = $stmt_month->get_result();
$row_month = $result_month->fetch_assoc();
$total_month = $row_month['total'];
$stmt_month->close();



$conn->close();
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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

  <style>
    /* Fondo oscuro futurista */
    body {
      margin: 0;
      padding: 0;
      background: #ffffff;
      color: #000d30;
      min-height: 100vh;
      display: flex;
    }
    /* Contenedor de la tabla */
.container {
    max-width: 90%;
    margin: auto;
}


/* Estilos personalizados para la tabla */
.custom-table {
    background-color: #000d30 !important; /* Color de fondo */
    border-radius: 12px; /* Bordes redondeados */
    overflow: hidden; /* Asegura que las esquinas se mantengan redondeadas */
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3); /* Efecto de sombra */
    border-collapse: separate;
    border-spacing: 0;
}

/* Encabezados */
.custom-table thead {
    background-color: #000d30 !important;
}

.custom-table thead th {
  background-color: #000d30 !important;
    color: #ffffff !important;
    text-align: center;
    padding: 12px;
}

/* Celdas del cuerpo */
.custom-table tbody tr td {
  background:rgb(241, 242, 244)!important; /* Color de fondo oscuro */
    color: #000d30 !important;
    text-align: center;
    padding: 10px;
    border-bottom: 1px solid #444 !important;
}

/* Bordes redondeados en esquinas superiores */
.custom-table thead tr:first-child th:first-child {
    border-top-left-radius: 12px;
}
.custom-table thead tr:first-child th:last-child {
    border-top-right-radius: 12px;
}

/* Bordes redondeados en esquinas inferiores */
.custom-table tbody tr:last-child td:first-child {
    border-bottom-left-radius: 12px;
}
.custom-table tbody tr:last-child td:last-child {
    border-bottom-right-radius: 12px;
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

  .profile-container {
  top: 20px; /* Ajusta la posición desde la parte superior */
  text-align: center;
  padding: 15px;
  position: absolute;
    top: 0; /* La pega a la parte superior */
    right: 0; /* La pega al lado derecho */
    margin-top: 20px;
}

.profile-pic {
  width: 43px;
  height: 43px;
  border-radius: 50%;
  object-fit: cover;
  border: 1px solid rgb(230, 235, 229); /* Verde neón */
  margin-bottom: 10px;
  cursor: pointer;
}

.username {
  color:rgb(106, 107, 109);
  font-weight: bold;
  font-size: 10px;
  margin-bottom: 10px;
  text-align: center;
}
/* Estilos para el modal */
.custom-modal {
    border-radius: 15px; /* Bordes redondeados */
    overflow: hidden; /* Para que las esquinas redondeadas sean efectivas */
    background:rgb(241, 242, 244); /* Color de fondo oscuro */
    color: #ffffff; /* Texto en blanco */
    box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.3); /* Sombra elegante */
}

/* Encabezado del modal */
.custom-modal .modal-header {
  background: #000d30;
    color: white;
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
    padding: 12px 16px;
}

/* Botón de cierre */
.custom-modal .btn-close {
    filter: invert(1); /* Hace el botón de cierre blanco */
}

/* Cuerpo del modal */
.custom-modal .modal-body {
    padding: 20px;
    background:rgb(240, 241, 245);
    border-bottom-left-radius: 15px;
    border-bottom-right-radius: 15px;
}

/* Estilos para la tabla dentro del modal */
.custom-table {
  background:rgb(222, 229, 250);
    border-radius: 10px;
    overflow: hidden;
}

.custom-table th {
  background:rgb(240, 241, 245);
    color: #000d30;
    padding: 12px;
    text-align: left;
}

.custom-table td {
    background:rgb(248, 248, 250); /* Azul oscuro */
    color: #ddd;
    padding: 12px;
}

/* Estilos generales para las tarjetas */
.conteo1, .conteo2, .conteo3 {
    background-color: #17a2b8; /* Color de fondo */
    border-radius: 8px; /* Bordes más pequeños */
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
    padding: 10px; /* Menos espacio interno */
    width: 90%; /* Disminuir el ancho */
    height: 100px; /* Altura reducida */
    text-align: center;
    margin: auto;
}
/* Colores personalizados para cada tarjeta */
.conteo1 {
    background: linear-gradient(135deg, #17a2b8, #138496); /* Azul */
    color: white;
}

.conteo2 {
    background: linear-gradient(135deg, #ffc107, #d39e00); /* Amarillo */
    color: white;
}

.conteo3 {
    background: linear-gradient(135deg, #28a745, #1e7e34); /* Verde */
    color: white;
}
/* Estilos específicos para cada tarjeta
.conteo1 { background-color: #17a2b8 !important; } /* Azul 
.conteo2 { background-color: #ffc107 !important; } /* Amarillo 
.conteo3 { background-color: #28a745 !important; } /* Verde */
/*

/* Reducir tamaño del texto */
.card-header {
    font-size: 1rem; /* Título más pequeño */
    font-weight: bold;
    padding: 5px;
}

.card-title {
    font-size: 1.5rem; /* Número más pequeño */
    font-weight: bold;
}

/* Opcional: Ajustar íconos */
.card-header i {
    font-size: 1rem; /* Ícono más pequeño */
    margin-right: 5px;
}

#tablaSoliGlobal {
  background-color: #000d30 !important; /* Color de fondo del encabezado */
    border-collapse: separate;
    border-spacing: 0;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2); /* Sombra suave */
}

#tablaSoliGlobal thead tr:first-child th:first-child {
    border-top-left-radius: 12px;
}

#tablaSoliGlobal thead tr:first-child th:last-child {
    border-top-right-radius: 12px;
}

#tablaSoliGlobal tbody tr:last-child td:first-child {
    border-bottom-left-radius: 12px;
}

#tablaSoliGlobal tbody tr:last-child td:last-child {
    border-bottom-right-radius: 12px;
}

#tablaSoliGlobal thead {
    background-color: #000d30 !important; /* Color de fondo del encabezado */
    color: white !important; /* Color del texto */
}

#tablaSoliGlobal th, 
#tablaSoliGlobal td {
    padding: 12px;
    text-align: center;
}

#tablaSoliGlobal tr {
    background-color: white;
}

#tablaSoliGlobal tr:nth-child(even) {
    background-color: #f8f9fa; /* Alternar colores de filas */
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

      
      <button class="btn btn-light w-100 mt-2" data-bs-toggle="modal" data-bs-target="#solicitudModal">
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
      <h4 class="mb-4">Manager </h4>
      <div class="container mt-4">
      <div class="row">
    <!-- Tarjeta de Hoy (conteo1) -->
    <div class="col-md-4">
        <div class="card text-white bg-info mb-3 conteo1">
            <div class="card-header"><i class="bi bi-calendar-day"></i>Made Today</div>
            <div class="card-body">
                <h5 class="card-title text-center"><?= $total_today ?></h5>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Esta Semana (conteo2) -->
    <div class="col-md-4">
        <div class="card text-white bg-warning mb-3 conteo2">
            <div class="card-header"><i class="bi bi-calendar-week"></i> Made This Week</div>
            <div class="card-body">
                <h5 class="card-title text-center"><?= $total_week ?></h5>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Este Mes (conteo3) -->
    <div class="col-md-4">
        <div class="card text-white bg-success mb-3 conteo3">
            <div class="card-header"><i class="bi bi-calendar-month"></i> Made This Month</div>
            <div class="card-body">
                <h5 class="card-title text-center"><?= $total_month ?></h5>
            </div>
        </div>
    </div>
</div>

        
    <h4><i class="bi bi-check-circle" style="font-size: 24px; color: green;"></i>
    Completed Requests</h4>
    <table class="table table-striped custom-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Candidate</th>
                <th>Department</th>
                <th>Position</th>
                <th>Response</th>
                <th>Updated</th>
                <th>Actions</th> <!-- Nueva columna -->
            </tr>
        </thead>
        <tbody id="tablaSolicitudesFinalizadas">
            <tr>
                <td colspan="7" class="text-center">Loading...</td>
            </tr>
        </tbody>
    </table>

   <!-- Tabla con todas las solicitudes -->
<div class="card shadow-lg border-0 rounded-4">
    
    <div class="card-body p-4">
        <div class="table-responsive">
            <table id="tablaSoliGlobal" class="table table-hover align-middle text-center">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Candidato</th>
                        <th>Departamento</th>
                        <th>Posición</th>
                        <th>Prioridad</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr class="bg-light">
                        <td class="fw-bold"><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['candidate_name']) ?></td>
                        <td><?= isset($row['department']) ? htmlspecialchars($row['department']) : 'N/A' ?></td>
                        <td><?= isset($row['position']) ? htmlspecialchars($row['position']) : 'N/A' ?></td>
                        <td>
                            <span class="badge 
                                <?= ($row['nivel_prioridad'] == 'Very high') ? 'bg-danger' : 
                                    (($row['nivel_prioridad'] == 'High') ? 'bg-warning' : 'bg-success'); ?>">
                                <?= htmlspecialchars($row['nivel_prioridad']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge 
                                <?= ($row['estado'] == 'Pendiente') ? 'bg-warning text-dark' : 
                                    (($row['estado'] == 'En proceso') ? 'bg-primary' : 'bg-success'); ?>">
                                <?= htmlspecialchars($row['estado']) ?>
                            </span>
                        </td>
                        <td><?= date("Y-m-d H:i", strtotime($row['fecha_creacion'])) ?></td>
                        <td>
                            <button class="btn btn-outline-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalResponder<?= $row['id'] ?>">
                                <i class="bi bi-eye"></i> Ver Detalles
                            </button>
                        </td>
                    </tr>

                    <!-- Modal para ver detalles -->
                    <div class="modal fade" id="modalResponder<?= $row['id'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header text-white" style="background-color: #000d30;">
                                    <h5 class="modal-title"><i class="bi bi-card-list"></i> Detalles de Solicitud #<?= $row['id'] ?></h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body bg-light p-4">
                                    <ul class="list-group">
                                        <li class="list-group-item"><strong>Candidato:</strong> <?= htmlspecialchars($row['candidate_name']) ?></li>
                                        <li class="list-group-item"><strong>Departamento:</strong> <?= htmlspecialchars($row['department']) ?></li>
                                        <li class="list-group-item"><strong>Posición:</strong> <?= htmlspecialchars($row['position']) ?></li>
                                        <li class="list-group-item"><strong>Prioridad:</strong> <?= htmlspecialchars($row['nivel_prioridad']) ?></li>
                                        <li class="list-group-item"><strong>Estado:</strong> <?= htmlspecialchars($row['estado']) ?></li>
                                        <li class="list-group-item"><strong>Fecha de Creación:</strong> <?= date("Y-m-d H:i", strtotime($row['fecha_creacion'])) ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
 
</div>

  <!-- MODAL PARA NUEVA SOLICITUD -->
  <div class="modal fade" id="solicitudModal" tabindex="-1" aria-labelledby="solicitudModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content bg-dark text-white">
        <div class="modal-header">
          <h5 class="modal-title">New Request</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="solicitudForm" enctype="multipart/form-data">
            <div class="mb-3">
              
            </div>
            <div class="mb-3">
              <label class="form-label">Candidate Name</label>
              <input type="text" class="form-control" name="candidate_name" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Application Department</label>
              <select class="form-select" name="department">
                <option value="HR">ISA</option>
                <option value="IT">MKTG</option>
                <option value="Finance">VA</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Position</label>
              <input type="text" class="form-control" name="position" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Estimated Delivery Time</label>
              <input type="datetime-local" class="form-control" name="delivery_time" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Nivel de Prioridad</label>
              <select class="form-select" name="nivel_prioridad">
                <option value="Normal" selected>Normal</option>
                <option value="High">High</option>
                <option value="Very high">Very High</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Attachments (PDF, DOC, XLSX)</label>
              <input type="file" class="form-control" name="attachments[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx">
            </div>
            <div class="mb-3">
              <label class="form-label">Comments</label>
              <textarea class="form-control" name="comments" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">Send</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL PARA VER DETALLES -->
<!-- MODAL PARA VER DETALLES -->
<!-- MODAL PARA VER DETALLES -->
<div class="modal fade" id="modalDetalles" tabindex="-1" aria-labelledby="modalDetallesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content custom-modal"> <!-- Aplicamos clase personalizada -->
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-card-list"></i> Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered custom-table">
                    <tr>
                        <th><i class="bi bi-hash"></i> ID:</th>
                        <td id="detalle-id"></td>
                    </tr>
                    <tr>
                        <th><i class="bi bi-person"></i> Candidate:</th>
                        <td id="detalle-candidato"></td>
                    </tr>
                    <tr>
                        <th><i class="bi bi-building"></i> Department:</th>
                        <td id="detalle-departamento"></td>
                    </tr>
                    <tr>
                        <th><i class="bi bi-briefcase"></i> Position:</th>
                        <td id="detalle-position"></td>
                    </tr>
                    <tr>
                        <th><i class="bi bi-exclamation-circle"></i> Priority Level:</th>
                        <td id="detalle-prioridad"></td>
                    </tr>
                    <tr>
                        <th><i class="bi bi-calendar"></i> Delivery Time:</th>
                        <td id="detalle-fecha"></td>
                    </tr>
                    <tr>
                        <th><i class="bi bi-chat-left-text"></i> Comments:</th>
                        <td id="detalle-comentarios"></td>
                    </tr>
                    <tr>
                        <th><i class="bi bi-paperclip"></i> Attachments:</th>
                        <td id="detalle-attachments"></td>
                    </tr>
                    <tr>
                        <th><i class="bi bi-reply"></i> Response:</th>
                        <td id="detalle-respuesta"></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>




  <script>
    document.getElementById("solicitudForm").addEventListener("submit", function(event) {
    event.preventDefault();
    
    let formData = new FormData(this);
    
    fetch('procesar_solicitud.php', {
        method: 'POST',
        body: formData
    }).then(response => response.text())
      .then(data => {
          alert(data);
          var solicitudModal = bootstrap.Modal.getInstance(document.getElementById("solicitudModal"));
          solicitudModal.hide();
          document.getElementById("solicitudForm").reset();
          cargarSolicitudes(); // 🔹 Recargar la tabla automáticamente
      }).catch(error => console.error("Error:", error));
});

function cargarSolicitudes() {
    fetch('cargar_solicitudes.php')
      .then(response => response.text())
      .then(data => {
          document.getElementById("tablaSolicitudes").innerHTML = data;
      });
}

cargarSolicitudes();


  </script>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function triggerFileInput() {
        document.getElementById('profilePictureInput').click();
    }
</script>
<script>
  function verDetalles(id) {
    fetch('obtener_solicitud.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            console.log("Datos recibidos:", data); // Depuración

            if (data.error) {
                alert("Error: " + data.error);
                return;
            }

            document.getElementById("detalle-id").innerText = data.id || "N/A";
            document.getElementById("detalle-candidato").innerText = data.candidate_name || "N/A";
            document.getElementById("detalle-departamento").innerText = data.department || "N/A";
            document.getElementById("detalle-position").innerText = data.position || "N/A";
            document.getElementById("detalle-prioridad").innerText = data.nivel_prioridad || "N/A";
            document.getElementById("detalle-fecha").innerText = data.delivery_time || "N/A";
            document.getElementById("detalle-comentarios").innerText = data.comments || "No comments";
            document.getElementById("detalle-respuesta").innerText = data.response_comments || "No response";

            // Procesar archivos adjuntos
            let attachmentsHTML = "No attachments";
            if (data.attachments) {
                let files = data.attachments.split(",");
                attachmentsHTML = "";
                files.forEach(file => {
                    let fileName = file.split('/').pop(); // Obtener solo el nombre del archivo
                    attachmentsHTML += `<a href="${file}" target="_blank" class="btn btn-sm btn-primary m-1">
                                            <i class="bi bi-file-earmark-arrow-down"></i> ${fileName}
                                        </a><br>`;
                });
            }
            document.getElementById("detalle-attachments").innerHTML = attachmentsHTML;
        })
        .catch(error => console.error("Error al cargar detalles de la solicitud:", error));
}

</script>


<script>
function cargarSolicitudesFinalizadas() {
    fetch('cargar_solicitudes_finalizadas.php')
        .then(response => response.text())
        .then(data => {
            document.getElementById("tablaSolicitudesFinalizadas").innerHTML = data;
        })
        .catch(error => console.error("Error loading completed requests:", error));
}

// Cargar solicitudes al cargar la página
document.addEventListener("DOMContentLoaded", cargarSolicitudesFinalizadas);
</script>

</body>
</html>