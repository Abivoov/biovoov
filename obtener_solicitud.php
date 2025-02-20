<?php
require 'db.php'; // Conexión a la base de datos

if (!isset($_GET['id'])) {
    echo json_encode(["error" => "Missing request ID"]);
    exit;
}

$id = $_GET['id'];

$sql = "SELECT id, candidate_name, department, position, nivel_prioridad, delivery_time, comments, response_comments, attachments 
        FROM solicitudes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["error" => "Request not found"]);
    exit;
}

$data = $result->fetch_assoc();
echo json_encode($data);

$stmt->close();
$conn->close();
?>
