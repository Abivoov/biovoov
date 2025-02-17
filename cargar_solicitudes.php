<?php
include 'db.php';

$sql = "SELECT * FROM solicitudes ORDER BY fecha_creacion DESC";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>{$row['id']}</td>
        <td>{$row['solicitante']}</td>
        <td>{$row['candidate_name']}</td>
        <td>{$row['nivel_prioridad']}</td>
        <td><span class='badge bg-warning text-dark'>{$row['estado']}</span></td>
        <td>{$row['fecha_creacion']}</td>
    </tr>";
}
?>
