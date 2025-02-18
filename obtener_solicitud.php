<?php
require 'db.php'; // Ensure DB connection is correct

header('Content-Type: application/json'); // Ensure response is JSON

if (!isset($_GET['id'])) {
    echo json_encode(["error" => "Missing request ID"]);
    exit;
}

$id = intval($_GET['id']); // Sanitize input
$sql = "SELECT * FROM solicitudes WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["error" => "Database error: " . $conn->error]);
    exit;
}

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc()); // Return the request data
} else {
    echo json_encode(["error" => "Request not found"]);
}

$stmt->close();
$conn->close();
?>
