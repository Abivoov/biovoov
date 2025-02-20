<?php
include 'db.php';

if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);

    $sql = "SELECT username FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    echo $row ? $row['username'] : "Unknown";

    $stmt->close();
    $conn->close();
}
?>
