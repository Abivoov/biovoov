<?php
require 'auth.php';
require 'db.php';
checkRole(['Admin']); // Solo Admin puede acceder

$sql = "SELECT * FROM solicitudes ORDER BY fecha_creacion DESC";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['candidate_name']) ?></td>
        <td><?= htmlspecialchars($row['department']) ?></td>
        <td><?= htmlspecialchars($row['position']) ?></td>
        <td><?= htmlspecialchars($row['nivel_prioridad']) ?></td>
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
<?php endwhile; ?>
