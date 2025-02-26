<?php
require 'auth.php';
require 'db.php'; // Conexión a la base de datos
checkRole(['Admin']); // Solo el Admin puede acceder

// Obtener todas las solicitudes
$sql = "SELECT * FROM solicitudes ORDER BY fecha_creacion DESC";
$result = $conn->query($sql);

// Consulta para obtener la cantidad de solicitudes por departamento
$sql_departments = "SELECT department, COUNT(*) as total FROM solicitudes GROUP BY department";
$result_departments = $conn->query($sql_departments);

// Crear un array con los resultados
$department_counts = [
    'ISA' => 0,
    'MKTG' => 0,
    'VA' => 0
];

while ($row = $result_departments->fetch_assoc()) {
    $department_counts[$row['department']] = $row['total'];
}

// Obtener solo las solicitudes finalizadas
$sql_completed = "SELECT * FROM solicitudes WHERE estado = 'Finalizado' ORDER BY fecha_creacion DESC";
$result_completed = $conn->query($sql_completed);

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
/* Estilos para el modal de responder */
.custom-modal {
    border-radius: 15px; /* Bordes redondeados */
    overflow: hidden; /* Para que las esquinas redondeadas sean efectivas */
    background: #f8f9fa; /* Color de fondo suave */
    color: #000d30; /* Texto oscuro */
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

.modal-header {
    background-color: #000d30 !important;

}
/* Cuerpo del modal */
.custom-modal .modal-body {
    padding: 20px;
    background: #ffffff; /* Blanco puro para resaltar los campos */
    border-bottom-left-radius: 15px;
    border-bottom-right-radius: 15px;
}

/* Mejoras en los botones */
.custom-modal .btn-success {
    background-color: #28a745;
    border: none;
    transition: all 0.3s ease-in-out;
}

.custom-modal .btn-success:hover {
    background-color: #218838;
    transform: scale(1.05);
}

/* Input y selects mejorados */
.custom-modal .form-control,
.custom-modal .form-select {
    border-radius: 8px;
    padding: 10px;
    font-size: 1rem;
    border: 1px solid #ced4da;
}

/* Labels estilizados */
.custom-modal .form-label {
    font-weight: bold;
    color: #000d30;
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

      
      <button class="btn btn-light w-100 mt-2" data-bs-toggle="modal" data-bs-target="#RsolicitudModal">
        <i class="bi bi-file-earmark-plus me-2"></i> New Requests
      </button>
      <hr />
      <a href="foro.php">
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
      <h4 class="mb-4">Admin</h4>
      <div class="container mt-4">
    <div class="row">
        <!-- Tarjeta para ISA -->
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3 shadow">
                <div class="card-header text-center"><i class="bi bi-people"></i> ISA Department</div>
                <div class="card-body text-center">
                    <h3 class="card-title"><?= $department_counts['ISA'] ?></h3>
                    <p class="card-text">Total Requests</p>
                </div>
            </div>
        </div>

        <!-- Tarjeta para MKTG -->
        <div class="col-md-4">
            <div class="card text-dark bg-warning mb-3 shadow">
                <div class="card-header text-center"><i class="bi bi-bar-chart"></i> MKTG Department</div>
                <div class="card-body text-center">
                    <h3 class="card-title"><?= $department_counts['MKTG'] ?></h3>
                    <p class="card-text">Total Requests</p>
                </div>
            </div>
        </div>

        <!-- Tarjeta para VA -->
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3 shadow">
                <div class="card-header text-center"><i class="bi bi-laptop"></i> VA Department</div>
                <div class="card-body text-center">
                    <h3 class="card-title"><?= $department_counts['VA'] ?></h3>
                    <p class="card-text">Total Requests</p>
                </div>
            </div>
        </div>
    </div>
</div>
      <div class="container mt-4">
      <div class="row">
    

        
   
   <!-- Tabla de Solicitudes Completadas -->
   <div class="card shadow-lg border-0 rounded-4 mt-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="bi bi-check-circle"></i> Completed Requests</h5>
    </div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table id="tablaCompletadas" class="table table-hover align-middle text-center">
                <thead class="table-success">
                    <tr>
                        <th>ID</th>
                        <th>Candidato</th>
                        <th>Departamento</th>
                        <th>Posición</th>
                        <th>Prioridad</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_completed->fetch_assoc()): ?>
                    <tr class="bg-light">
                        <td class="fw-bold"><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['candidate_name']) ?></td>
                        <td><?= htmlspecialchars($row['department']) ?></td>
                        <td><?= htmlspecialchars($row['position']) ?></td>
                        <td>
                            <span class="badge 
                                <?= ($row['nivel_prioridad'] == 'Very high') ? 'bg-danger' : 
                                    (($row['nivel_prioridad'] == 'High') ? 'bg-warning' : 'bg-success'); ?>">
                                <?= htmlspecialchars($row['nivel_prioridad']) ?>
                            </span>
                        </td>
                        <td><?= date("Y-m-d H:i", strtotime($row['fecha_creacion'])) ?></td>
                        <td>
                            <button class="btn btn-outline-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalDetalles<?= $row['id'] ?>">
                                <i class="bi bi-eye"></i> Ver Detalles
                            </button>
                        </td>
                    </tr>

                    <!-- Modal para ver detalles -->
                    <div class="modal fade" id="modalDetalles<?= $row['id'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header text-white" style="background-color: #198754;">
                                    <h5 class="modal-title"><i class="bi bi-card-list"></i> Detalles de Solicitud #<?= $row['id'] ?></h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body bg-light p-4">
                                    <ul class="list-group">
                                        <li class="list-group-item"><strong>Candidato:</strong> <?= htmlspecialchars($row['candidate_name']) ?></li>
                                        <li class="list-group-item"><strong>Departamento:</strong> <?= htmlspecialchars($row['department']) ?></li>
                                        <li class="list-group-item"><strong>Posición:</strong> <?= htmlspecialchars($row['position']) ?></li>
                                        <li class="list-group-item"><strong>Prioridad:</strong> <?= htmlspecialchars($row['nivel_prioridad']) ?></li>
                                        <li class="list-group-item"><strong>Fecha de Creación:</strong> <?= date("Y-m-d H:i", strtotime($row['fecha_creacion'])) ?></li>
                                        
                                        <!-- Archivo Adjunto -->
                                        <li class="list-group-item">
                                            <strong>Archivo Adjunto:</strong> 
                                            <?php if (!empty($row['response_attachment'])): ?>
                                                <a href="<?= htmlspecialchars($row['response_attachment']) ?>" target="_blank" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-file-earmark-arrow-down"></i> Descargar
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">No hay archivos adjuntos</span>
                                            <?php endif; ?>
                                        </li>
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
<div class="card card-futuristic">
    <table class="table table-bordered">
        <thead class="table-dark">
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
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['candidate_name']) ?></td>
                <td><?= htmlspecialchars($row['department']) ?></td>
                <td><?= htmlspecialchars($row['position']) ?></td>
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
                    <button class="btn btn-outline-success btn-sm px-3" 
                        data-bs-toggle="modal" data-bs-target="#modalResponder<?= $row['id'] ?>">
                        <i class="bi bi-reply"></i> Responder
                    </button>
                </td>
            </tr>

            <!-- MODAL PARA RESPONDER A LA SOLICITUD -->
            <!-- MODAL PARA RESPONDER A LA SOLICITUD -->
<div class="modal fade" id="modalResponder<?= $row['id'] ?>" tabindex="-1" aria-labelledby="modalResponder<?= $row['id'] ?>" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg- text-white">
                <h5 class="modal-title"><i class="bi bi-chat-dots"></i> Responder Solicitud #<?= $row['id'] ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="procesar_respuesta.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">

                    <!-- Estado de la solicitud -->
                    <div class="mb-3">
                        <label class="form-label"><strong>Actualizar Estado</strong></label>
                        <select class="form-select" name="estado">
                            <option value="Pendiente" <?= ($row['estado'] == 'Pendiente') ? 'selected' : '' ?>>Pendiente</option>
                            <option value="En proceso" <?= ($row['estado'] == 'En proceso') ? 'selected' : '' ?>>En proceso</option>
                            <option value="Finalizado" <?= ($row['estado'] == 'Finalizado') ? 'selected' : '' ?>>Finalizado</option>
                        </select>
                    </div>

                    <!-- Comentarios de Respuesta -->
                    <div class="mb-3">
                        <label class="form-label"><strong>Comentarios de Respuesta</strong></label>
                        <textarea class="form-control" name="response_comments" rows="4" placeholder="Escribe la respuesta..." required></textarea>
                    </div>

                    <!-- Adjuntar Archivo -->
                    <div class="mb-3">
                        <label class="form-label"><strong>Adjuntar Archivo (PDF)</strong></label>
                        <input type="file" class="form-control" name="response_attachment" accept=".pdf" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-send"></i> Enviar Respuesta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
    function triggerFileInput() {
        document.getElementById('profilePictureInput').click();
    }
</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>