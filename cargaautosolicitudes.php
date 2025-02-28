<?php
require 'auth.php';
require 'db.php';
checkRole(['Admin']); // Solo Admin puede acceder

$searchQuery = isset($_GET['query']) ? trim($_GET['query']) : '';

// Consulta SQL con JOIN para incluir el username del usuario que creó la solicitud
$sql = "SELECT solicitudes.*, usuarios.username 
        FROM solicitudes
        JOIN usuarios ON solicitudes.user_id = usuarios.id";

// Si hay un término de búsqueda, lo agregamos a la consulta
if (!empty($searchQuery)) {
    $sql .= " WHERE solicitudes.candidate_name LIKE ? 
              OR usuarios.username LIKE ?";
}

$sql .= " ORDER BY solicitudes.fecha_creacion DESC";

$stmt = $conn->prepare($sql);

if (!empty($searchQuery)) {
    $searchParam = "%$searchQuery%";
    $stmt->bind_param("ss", $searchParam, $searchParam);
}

$stmt->execute();
$result = $stmt->get_result();

// Construcción de la tabla con los resultados filtrados
while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['candidate_name']) ?></td>
        <td><?= htmlspecialchars($row['department']) ?></td>
        <td><?= htmlspecialchars($row['position']) ?></td>
        <td><?= htmlspecialchars($row['nivel_prioridad']) ?></td>
        <td><?= htmlspecialchars($row['username']) ?></td> <!-- Nueva columna para el usuario -->
        <td>
            <form action="actualizar_estado.php" method="POST">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <select name="estado" class="form-select" onchange="this.form.submit()">
                    <option value="Pendiente" <?= ($row['estado'] == 'Pendiente') ? 'selected' : '' ?>>Pendiente</option>
                    <option value="En proceso" <?= ($row['estado'] == 'En proceso') ? 'selected' : '' ?>>En proceso</option>
                    <option value="Finalizado" <?= ($row['estado'] == 'Finalizado') ? 'selected' : '' ?>>Finalizado</option>
                </select>
            </form>
        </td>
        <td><?= date("Y-m-d H:i", strtotime($row['fecha_creacion'])) ?></td>
        <td>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalResponder<?= $row['id'] ?>">Responder</button>
        </td>
    </tr>
<?php endwhile;

$conn->close();
?>
