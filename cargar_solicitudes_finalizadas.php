<?php
require 'db.php'; // Conexión a la base de datos

$sql = "SELECT id, candidate_name, department, position, response_comments, fecha_actualizacion, attachments
        FROM solicitudes WHERE estado = 'Finalizado' 
        ORDER BY fecha_actualizacion DESC";
$result = $conn->query($sql);

if (!$result) {
    die("<tr><td colspan='7' class='text-center'>Error en la consulta: " . $conn->error . "</td></tr>");
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['candidate_name']}</td>
                <td>{$row['department']}</td>
                <td>{$row['position']}</td>
                <td>" . (!empty($row['response_comments']) ? $row['response_comments'] : 'No response') . "</td>
                <td>{$row['fecha_actualizacion']}</td>
                <td>
                    <button class='btn btn-info btn-sm' onclick='verDetalles({$row['id']})' data-bs-toggle='modal' data-bs-target='#modalDetalles'>
                        <i class='bi bi-eye'></i> View
                    </button>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='7' class='text-center'>No completed requests found.</td></tr>";
}

$conn->close();
?>
