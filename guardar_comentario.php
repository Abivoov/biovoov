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
        // Obtener los datos del usuario para devolverlos
        $stmt_user = $conn->prepare("SELECT username, profile_picture FROM usuarios WHERE id = ?");
        $stmt_user->bind_param("i", $user_id);
        $stmt_user->execute();
        $result_user = $stmt_user->get_result();
        $user_data = $result_user->fetch_assoc();

        echo json_encode([
            "success" => true,
            "username" => $user_data['username'] ?? "Usuario Desconocido",
            "profile_picture" => $user_data['profile_picture'] ?? "uploads/default-avatar.png"
        ]);

        $stmt_user->close();
    } else {
        echo json_encode(["success" => false, "message" => "Error al guardar el comentario."]);
    }

    $stmt->close();
}
?>





<style>
        body {
            background-color: #f8f9fa;
        }
        .header {
            background-color: #000d30;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #1ac6ff;
        }
        .header h3 {
            margin: 0;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }
        .header h3 i {
            margin-right: 10px;
        }
        .profile-container {
            display: flex;
            align-items: center;
        }
        .profile-container img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            margin-right: 10px;
        }
        .container {
            max-width: 800px;
            margin-top: 20px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0px 3px 6px rgba(0, 0, 0, 0.1);
        }
        .btn-outline-primary {
            border-radius: 20px;
        }
        .form-control {
            border-radius: 10px;
        }
    </style>