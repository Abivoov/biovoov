<?php
require 'auth.php';
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content']);
    $parent_id = isset($_POST['parent_id']) ? (int) $_POST['parent_id'] : NULL;
    $user_id = $_SESSION['user_id'];

    if (empty($content)) {
        echo json_encode(["success" => false, "message" => "El comentario no puede estar vacío."]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO forum_posts (user_id, content, parent_id) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $user_id, $content, $parent_id);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al guardar el comentario."]);
    }

    $stmt->close();
}
?>
