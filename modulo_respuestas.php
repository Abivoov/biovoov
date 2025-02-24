<?php 
require 'auth.php';
require 'db.php';
require 'cargar_solicitudes_pendientes.php';
checkRole(['Admin']); // Solo Admins pueden acceder

// Obtener todas las solicitudes pendientes o en proceso
$sql = "SELECT * FROM solicitudes WHERE estado IN ('Pendiente', 'En proceso') ORDER BY fecha_creacion DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Modulo de Respuestas - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <h2 class="text-center mb-4">Módulo de Respuestas</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Candidate</th>
                    <th>Status</th>
                    <th>Date Created</th>
                    <th>Priority Level</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="tablaSolicitudes">
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $row['id']; ?></td>
                        <td><?= $row['candidate_name']; ?></td>
                        <td><span class="badge bg-warning"><?= $row['estado']; ?></span></td>
                        <td><?= date("Y-m-d H:i:s", strtotime($row['fecha_creacion'])); ?></td>
                        <td>
                            <span class="badge <?= $row['nivel_prioridad'] == 'Very high' ? 'bg-danger' : 'bg-success'; ?>">
                                <?= $row['nivel_prioridad']; ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-primary btn-sm btn-reply" 
                                data-bs-toggle="modal" 
                                data-bs-target="#respuestaModal" 
                                data-info='<?= json_encode($row, JSON_HEX_APOS); ?>'>
                                Responder
                            </button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- MODAL PARA RESPONDER SOLICITUD -->
    <div class="modal fade" id="respuestaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Responder Solicitud</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="updateForm">
                        <input type="hidden" name="id" id="solicitudId">
                        
                        <div class="mb-3">
                            <label class="form-label">Comentarios de Respuesta</label>
                            <textarea class="form-control" name="response_comments" id="responseComments"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" name="estado" id="estadoNuevo">
                                <option value="Pendiente">Pendiente</option>
                                <option value="En proceso">En Proceso</option>
                                <option value="Finalizado">Finalizado</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-success w-100">Guardar Respuesta</button>
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
                document.getElementById('solicitudId').value = data.id;
                document.getElementById('responseComments').value = data.response_comments || '';
                document.getElementById('estadoNuevo').value = data.estado;
            }
        });

        document.getElementById("updateForm").addEventListener("submit", function(event) {
            event.preventDefault();
            let formData = new FormData(this);

            fetch('update_solicitud.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
                let modal = bootstrap.Modal.getInstance(document.getElementById("respuestaModal"));
                modal.hide();
                cargarSolicitudes();
            })
            .catch(error => console.error("Error:", error));
        });

        function cargarSolicitudes() {
            fetch('cargar_solicitudes_pendientes.php')
            .then(response => response.text())
            .then(data => {
                document.getElementById("tablaSolicitudes").innerHTML = data;
            });
        }

        setInterval(cargarSolicitudes, 5000);
        cargarSolicitudes();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
