<?php
require 'db.php';
session_start();

$user_id = $_SESSION['user_id']; // Asegúrate de que esta variable esté definida
$content = $_POST['content'];
$parent_id = $_POST['parent_id'] === "NULL" ? NULL : $_POST['parent_id'];

if (empty($content)) {
    die("Error: El comentario no puede estar vacío.");
}

// Insertar comentario en la base de datos
$sql = "INSERT INTO forum_posts (user_id, content, parent_id, created_at) VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isi", $user_id, $content, $parent_id);

if ($stmt->execute()) {
    echo "Comentario agregado correctamente";
} else {
    die("Error al guardar el comentario: " . $conn->error);
}
