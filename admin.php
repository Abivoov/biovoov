<?php
require 'auth.php';
require 'db.php'; // Conexión a la base de datos
checkRole(['Admin']); // Solo el Admin puede acceder

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
$sql = "SELECT solicitudes.*, usuarios.username 
        FROM solicitudes 
        JOIN usuarios ON solicitudes.user_id = usuarios.id 
        ORDER BY solicitudes.fecha_creacion DESC";
$result = $conn->query($sql);


$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Admin-BIO</title>

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
    /* ================= GENERAL ================= */
body {
    margin: 0;
    padding: 0;
    background: #ffffff;
    color: #000d30;
    min-height: 100vh;
    display: flex;
}

/* ================= CONTENEDOR PRINCIPAL ================= */
.container-main {
    display: flex;
    flex: 1;
    
    margin: auto; /* Centra el contenedor en la página */
}
.row.mt-4 {
    margin-top: 90px; /* Ajusta el valor según sea necesario */
    
}
/* ================= boton de busquedas ================= */
/* Contenedor del input para centrar y dar estilo */
.search-container {
    display: flex;
    justify-content: center; /* Centra horizontalmente */
    align-items: center; /* Alinea verticalmente */
    margin-top: 20px; /* Ajusta según sea necesario */
    gap: 15px; /* Espacio entre el input y el botón */
}


/* Input de búsqueda con diseño moderno */
.custom-search-input {
    width: 30%;
    padding: 12px 40px; /* Espacio para el icono */
    font-size: 16px;
    border: 2px solid #000d30;
    border-radius: 30px;
    outline: none;
    background-color: #f8f9fa;
    transition: all 0.3s ease-in-out;
}

/* Efecto al enfocar */
.custom-search-input:focus {
    border-color:rgb(18, 117, 150);
    
}

/* Icono de búsqueda */
.search-icon {
    position: absolute;
    left: 15px;
    font-size: 18px;
    color: #000d30;
}
.resaltado {
    background-color: #ffc107 !important; /* Color amarillo para resaltar */
    transition: background-color 1s ease-in-out;
}



/* ================= SIDEBAR ================= */
.sidebar {
    width: 280px;
    background-color: #000d30;
    min-height: 100vh;
    padding: 1rem 0;
}
.sidebar .logo {
    font-size: 1.5rem;
    font-weight: 600;
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
.sidebar a:hover, .sidebar .active {
    background-color: #222;
    color: #fff;
}
.sidebar hr {
    border-color: #333;
    margin: 1rem 0;
}

/* ================= PERFIL (PARTE SUPERIOR) ================= */
.profile-container {
    text-align: center;
    padding: 15px;
    position: absolute;
    top: 0;
    right: 0;
    margin-top: 20px;
}
.profile-pic {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid rgb(230, 235, 229);
    margin-bottom: 10px;
    cursor: pointer;
}
.username {
    color: rgb(99, 99, 100);
    font-weight: bold;
    font-size: 10px;
    margin-bottom: 10px;
    text-align: center;
}

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
    color:rgb(7, 7, 40) !important;
    text-align: center;
    padding: 12px;
}

/* Celdas del cuerpo */
.custom-table tbody tr td {
 
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

.card{
        border-radius: 15px;
        box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.1);
        background-color: rgb(232, 231, 231);
}


/* ================= MODALES ================= */
.custom-modal {
    border-radius: 15px;
    overflow: hidden;
    background: rgb(241, 242, 244);
    color: #ffffff;
    box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.3);
}
.custom-modal .modal-header {
    background: #000d30;
    color: white;
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
    padding: 12px 16px;
}
.custom-modal .btn-close {
    filter: invert(1);
}
.custom-modal .modal-body {
    padding: 20px;
    background: rgb(240, 241, 245);
    border-bottom-left-radius: 15px;
    border-bottom-right-radius: 15px;
}

/* ================= TARJETAS ================= */
.conteo1, .conteo2, .conteo3 {
    border-radius: 15px;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
    padding: 10px;
    width: 70%;
    height: 100px;
    text-align: center;
}
.conteo1 {
    background-color: #000d30 !important;
    color: white;
}
.conteo2 {
    background-color: #000d30;
    color: black
}
.conteo3 {
    background-color: #000d30;
    color: white;
}

/* ================= BOTONES ================= */
.custom-modal .btn-success {
    background-color: #28a745;
    border: none;
    transition: all 0.3s ease-in-out;
}
.custom-modal .btn-success:hover {
    background-color: #218838;
    transform: scale(1.05);
}

/* ================= FORMULARIOS ================= */
.custom-modal .form-control,
.custom-modal .form-select {
    border-radius: 8px;
    padding: 10px;
    font-size: 1rem;
    border: 1px solid #ced4da;
}
.custom-modal .form-label {
    font-weight: bold;
    color: #000d30;
}

/* ================= ÍCONOS ================= */
.card-header {
    font-size: 1rem;
    font-weight: bold;
    padding: 5px;
}
.card-title {
    font-size: 1.5rem;
    font-weight: bold;
}
.card-header i {
    font-size: 1rem;
    margin-right: 5px;
}



.count-cards {
    margin-top: 80px !important; /* Ajusta este valor según lo necesites */
    border-radius: 15px;
}
.modal-content {
    border-radius: 30px; /* Bordes más redondeados */
    overflow: hidden; /* Evita que los bordes sean afectados por contenido */
}
.table-responsive {
    
    overflow-y: auto; /* Agrega scroll vertical */
   
}

#btnBuscar {
    background-color: #000d30;
    color: white;
    border-radius: 30px;
    padding: 6px 15px;
    font-size: 14px;
    font-weight: bold;
    transition: all 0.3s ease-in-out;
    border: 2px solid #000d30;
}

#btnBuscar:hover {
    background-color: white;
    color: #000d30;
    border: 2px solid #000d30;
    transform: scale(1.05)
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
      <h4  style="color: #000d30;">Admin</h4>
      <div class="search-container">
    <i class="bi bi-search search-icon"></i>
    <input type="text" id="searchQuery" class="custom-search-input" placeholder="Search by Manager or Candidate">

  <!-- Botón para abrir el modal con identificador único -->
<button id="btnBuscar" type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#searchModal">
    Search
</button>

<!-- Modal con resultados de búsqueda -->
<div class="modal fade" id="modalBusqueda" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="bi bi-search"></i> Resultados de la Búsqueda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul id="resultadosBusqueda" class="list-group">
                    <!-- Aquí se insertarán los resultados dinámicamente -->
                </ul>
            </div>
        </div>
    </div>
</div>



<!-- Modal -->
<div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #000d30; color: white;">
                <h5 class="modal-title" id="searchModalLabel">Buscar Solicitud</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="searchQuery" class="form-control mb-3" placeholder="Buscar por candidato o usuario...">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Candidato</th>
                                <th>Departamento</th>
                                <th>Posición</th>
                                <th>Prioridad</th>
                                <th>Usuario</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody id="searchResults">
                            <tr>
                                <td colspan="8" class="text-center text-muted">Ingresa un término de búsqueda...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

      <div class="row mb-3">
    
</div>

      <div class="container mt-4 count-cards">
    <div class="row">
        <!-- Tarjeta para ISA -->
        <div class="col-md-4">
            <div class="card text-black bg mb-3 shadow">
                <div class="card-header text-center"><img src="img/Group 82.png" alt=""></i> ISA Department</div>
                <div class="card-body text-center">
                    <h3 class="card-title"><?= $department_counts['ISA'] ?></h3>
                    <p class="card-text">Total Requests</p>
                </div>
            </div>
        </div>

        <!-- Tarjeta para MKTG -->
        <div class="col-md-4">
            <div class="card text-dark bg mb-3 shadow">
                <div class="card-header text-center"><img src="img/Group 84.png" alt=""> MKTG Department</div>
                <div class="card-body text-center">
                    <h3 class="card-title"><?= $department_counts['MKTG'] ?></h3>
                    <p class="card-text">Total Requests</p>
                </div>
            </div>
        </div>

        <!-- Tarjeta para VA -->
        <div class="col-md-4">
            <div class="card text-dark bgmb-3 ">
                <div class="card-header text-center"><img src="img/Group 83.png" alt=""> VA Department</div>
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
    

        
   
   <!-- Tabla de solicitudes finalizadas -->
    
   <div class="card shadow-lg border-0 ">
   
    <div class="card card-futuristic mt-4">
    <h6>Completed Requests</h6>
    <div class="table-responsive" >
        <div class="table-responsive">
            <table  class="table table-hover align-middle text-center">
                <thead class="table-success">
                    <tr>
                        <th>ID</th>
                        <th>Candidate</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th>Priority</th>
                        <th>End Date</th>
                        <th>Action</th>
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
        <div class="modal-content rounded-3"> <!-- Se agregaron bordes redondeados aquí -->
            <div class="modal-header text-white" style="background-color: #000d30;">
                <h5 class="modal-title">
                    <i class="bi bi-card-list"></i> Detalles de Solicitud #<?= $row['id'] ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light p-4">
                <ul class="list-group">
                    <li class="list-group-item">
                        <i class="bi bi-person-circle"></i> <strong>Candidate:</strong> <?= htmlspecialchars($row['candidate_name']) ?>
                    </li>
                    <li class="list-group-item">
                        <i class="bi bi-building"></i> <strong>Department:</strong> <?= htmlspecialchars($row['department']) ?>
                    </li>
                    <li class="list-group-item">
                        <i class="bi bi-briefcase"></i> <strong>Position:</strong> <?= htmlspecialchars($row['position']) ?>
                    </li>
                    <li class="list-group-item">
                        <i class="bi bi-exclamation-circle"></i> <strong>Priority:</strong> <?= htmlspecialchars($row['nivel_prioridad']) ?>
                    </li>
                    <li class="list-group-item">
                        <i class="bi bi-calendar-event"></i> <strong>Creation date:</strong> <?= date("Y-m-d H:i", strtotime($row['fecha_creacion'])) ?>
                    </li>
                    
                    <!-- Archivo Adjunto -->
                    <li class="list-group-item">
                        <i class="bi bi-paperclip"></i> <strong>Archivo Adjunto:</strong> 
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
 <!-- Tabla de todas las solicitudes -->
<div class="card shadow-lg border-0 rounded-4 mt-4">
<div class="card card-futuristic mt-4">
    <H6>All Requests</H6>
    <div class="table-responsive"  >
        <div class="table-responsive">
            <table  class="table table-hover align-middle text-center">
                <thead class="table-success">
                    <tr id="solicitud-<?= $row['id'] ?>">

                        <th>ID</th>
                        <th>Sent by</th>
                        <th>Candidate</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th>Priority</th>
                        <th>Stage</th>
                        <th>End Date</th>
                        
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
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
                        <i class="bi bi-reply"></i> Reply
                    </button>
                </td>
            </tr>

            
            <!-- MODAL PARA RESPONDER A LA SOLICITUD -->
            <div class="modal fade" id="modalResponder<?= $row['id'] ?>" tabindex="-1" aria-labelledby="modalResponder<?= $row['id'] ?>" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-3"> <!-- Bordes redondeados -->
            <div class="modal-header text-white" style="background-color: #000d30;"> <!-- Color azul de Bootstrap -->
                <h5 class="modal-title">
                    <i class="bi bi-chat-dots"></i> Responder Solicitud #<?= $row['id'] ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light p-4">
                <form action="procesar_respuesta.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">

                    <!-- Estado de la solicitud -->
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-flag"></i> <strong>Actualizar Estado</strong></label>
                        <select class="form-select" name="estado">
                            <option value="Pendiente" <?= ($row['estado'] == 'Pendiente') ? 'selected' : '' ?>>
                                ⏳ Pending
                            </option>
                            <option value="En proceso" <?= ($row['estado'] == 'En proceso') ? 'selected' : '' ?>>
                                🔄 In process
                            </option>
                            <option value="Finalizado" <?= ($row['estado'] == 'Finalizado') ? 'selected' : '' ?>>
                                ✅ Completed
                            </option>
                        </select>
                    </div>
                    
                    <!-- Comentarios de Respuesta -->
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-pencil-square"></i> <strong>Comentarios de Respuesta</strong></label>
                        <textarea class="form-control" name="response_comments" rows="4" placeholder="Escribe la respuesta..." required></textarea>
                    </div>

                    <!-- Adjuntar Archivo -->
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-paperclip"></i> <strong>Adjuntar Archivo (PDF)</strong></label>
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
<!-- BOTÓN PARA ABRIR EL MODAL -->


<!-- cierra modal de busqueda -->
<script>
    function irASolicitud(id) {
    // Cierra el modal de búsqueda
    var modal = bootstrap.Modal.getInstance(document.getElementById("modalBusqueda"));
    modal.hide();

    // Espera 300ms para cerrar el modal antes de hacer scroll
    setTimeout(() => {
        let fila = document.getElementById(`solicitud-${id}`);
        if (fila) {
            fila.scrollIntoView({ behavior: "smooth", block: "center" });
            fila.classList.add("resaltado");

            // Quita la clase de resaltado después de 2 segundos
            setTimeout(() => fila.classList.remove("resaltado"), 2000);
        }
    }, 300);
}

</script>

<!-- permite accion para cambiar foto de perfil -->
<script>
    function triggerFileInput() {
        document.getElementById('profilePictureInput').click();
    }
</script>

<!-- permite accion para buscar -->
<script>
    document.getElementById("searchQuery").addEventListener("keyup", function() {
    let searchValue = this.value.trim();

    fetch("cargaautosolicitudes.php?query=" + encodeURIComponent(searchValue))
        .then(response => response.text())
        .then(data => {
            document.getElementById("searchResults").innerHTML = data;
        })
        .catch(error => console.error("Error en la búsqueda:", error));
});

</script>

<!-- ir a tabla solicitud -->
 <script>
    function irATabla(solicitudId) {
    // Cierra el modal
    let modal = bootstrap.Modal.getInstance(document.getElementById('modalBusqueda'));
    modal.hide();

    // Esperar que el modal se cierre completamente antes de hacer scroll
    setTimeout(() => {
        // Buscar la fila en la tabla principal
        let fila = document.getElementById(`solicitud-${solicitudId}`);
        if (fila) {
            // Hacer scroll hasta la fila encontrada
            fila.scrollIntoView({ behavior: 'smooth', block: 'center' });

            // Resaltar la fila momentáneamente
            fila.style.backgroundColor = '#ffeb3b'; // Amarillo
            setTimeout(() => {
                fila.style.transition = 'background-color 1s';
                fila.style.backgroundColor = ''; // Regresa al color original
            }, 1500);
        }
    }, 300);
}

 </script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>