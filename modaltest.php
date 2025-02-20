<?php 
require 'auth.php';
require 'db.php';

checkRole(['Admin']); 

if (!isset($_SESSION['user_id'])) {
    die("Error: No hay una sesión activa. Inicia sesión nuevamente.");
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT username, email, profile_picture FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email, $profile_picture);
$stmt->fetch();
$stmt->close();

$sql = "SELECT * FROM solicitudes WHERE estado IN ('Pendiente', 'En proceso') ORDER BY fecha_creacion DESC";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Admin Panel - Dashboard Futurista</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
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
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>

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
</body>
</html>
