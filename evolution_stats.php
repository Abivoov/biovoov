<?php
require 'db.php';

// Obtener datos de los últimos 7 días
$sql_week = "SELECT DATE(fecha_creacion) as day, COUNT(*) as total 
             FROM solicitudes 
             WHERE estado = 'Finalizado' 
             AND fecha_creacion >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
             GROUP BY day ORDER BY day ASC";
$result_week = $conn->query($sql_week);
$week_data = [];

while ($row = $result_week->fetch_assoc()) {
    $week_data[$row['day']] = $row['total'];
}

// Obtener datos del último mes
$sql_month = "SELECT DATE(fecha_creacion) as day, COUNT(*) as total 
              FROM solicitudes 
              WHERE estado = 'Finalizado' 
              AND fecha_creacion >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH) 
              GROUP BY day ORDER BY day ASC";
$result_month = $conn->query($sql_month);
$month_data = [];

while ($row = $result_month->fetch_assoc()) {
    $month_data[$row['day']] = $row['total'];
}

$conn->close();

// Convertir datos en formato JSON
echo json_encode([
    'week' => $week_data,
    'month' => $month_data
]);
?>
