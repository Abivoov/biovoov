<?php
require 'auth.php';
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content']);
    $parent_id = isset($_POST['parent_id']) && $_POST['parent_id'] !== "NULL" ? (int) $_POST['parent_id'] : NULL;
    $user_id = $_SESSION['user_id'];

    if (empty($content)) {
        echo json_encode(["success" => false, "message" => "El comentario no puede estar vacío."]);
        exit;
    }

    // Insertar comentario en la base de datos
    $stmt = $conn->prepare("INSERT INTO forum_posts (user_id, content, parent_id) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $user_id, $content, $parent_id);

    if ($stmt->execute()) {
        $last_id = $stmt->insert_id; // Obtener el ID del comentario insertado

        // Obtener los datos del usuario
        $stmt_user = $conn->prepare("SELECT username, profile_picture FROM usuarios WHERE id = ?");
        $stmt_user->bind_param("i", $user_id);
        $stmt_user->execute();
        $result_user = $stmt_user->get_result();
        $user_data = $result_user->fetch_assoc();

        echo json_encode([
            "success" => true,
            "id" => $last_id, // Enviar ID del comentario
            "username" => $user_data['username'] ?? "Usuario Desconocido",
            "profile_picture" => $user_data['profile_picture'] ?? "uploads/default-avatar.png",
            "content" => htmlspecialchars($content)
        ]);

        $stmt_user->close();
    } else {
        echo json_encode(["success" => false, "message" => "Error al guardar el comentario."]);
    }

    $stmt->close();
}
?>
