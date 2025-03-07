<?php
require 'db.php';
session_start(); // Asegurar que la sesión está activa

// Obtener el user_id del usuario en sesión
if (!isset($_SESSION['user_id'])) {
    die("<tr><td colspan='7' class='text-center'>Error: Usuario no autenticado.</td></tr>");
}
$user_id = $_SESSION['user_id'];

$sql = "SELECT id, candidate_name, department, position, response_comments, fecha_actualizacion 
        FROM solicitudes 
        WHERE estado = 'Finalizado' AND user_id = ? 
        ORDER BY fecha_actualizacion DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

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

$stmt->close();
$conn->close();
?>
