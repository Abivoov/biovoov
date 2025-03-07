<?php
require 'auth.php';
require 'db.php';

checkRole(['Manager']);

if (!isset($_SESSION['user_id'])) {
    die("Error: No hay una sesión activa.");
}

$user_id = $_SESSION['user_id'];
$query = isset($_GET['query']) ? "%{$_GET['query']}%" : "%";
$fechaInicio = isset($_GET['fechaInicio']) && !empty($_GET['fechaInicio']) ? $_GET['fechaInicio'] : null;
$fechaFin = isset($_GET['fechaFin']) && !empty($_GET['fechaFin']) ? $_GET['fechaFin'] : null;

$sql = "SELECT id, candidate_name, department, position, nivel_prioridad, estado, fecha_creacion 
        FROM solicitudes 
        WHERE user_id = ? AND candidate_name LIKE ?";

$params = [$user_id, $query];
$types = "is"; 

if ($fechaInicio && $fechaFin) {
    $sql .= " AND DATE(fecha_creacion) BETWEEN ? AND ?";
    $params[] = $fechaInicio;
    $params[] = $fechaFin;
    $types .= "ss";
} elseif ($fechaInicio) {
    $sql .= " AND DATE(fecha_creacion) >= ?";
    $params[] = $fechaInicio;
    $types .= "s";
} elseif ($fechaFin) {
    $sql .= " AND DATE(fecha_creacion) <= ?";
    $params[] = $fechaFin;
    $types .= "s";
}

$sql .= " ORDER BY fecha_creacion DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['candidate_name']}</td>
                <td>{$row['department']}</td>
                <td>{$row['position']}</td>
                <td>{$row['nivel_prioridad']}</td>
                <td>{$row['estado']}</td>
                <td>
                    <button class='btn btn-sm btn-outline-primary' onclick='verDetalles({$row['id']})'>
                        <i class='bi bi-eye'></i> Ver Detalles
                    </button>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='7' class='text-center'>No se encontraron resultados</td></tr>";
}

$stmt->close();
$conn->close();
?>
