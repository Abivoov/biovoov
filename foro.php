<?php
require 'auth.php';
require 'db.php'; // Conexión a la base de datos
checkRole(['Manager', 'Admin']); // Solo usuarios autenticados pueden ver

// Obtener la foto de perfil del usuario
$user_id = $_SESSION['user_id'];
$sql_user = "SELECT username, profile_picture FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_user = $stmt->get_result();
$user_data = $result_user->fetch_assoc();

$username = $user_data['username'] ?? "Usuario";
$profile_picture = $user_data['profile_picture'] ?? "uploads/default-avatar.png";

// Obtener los comentarios principales (sin parent_id)
$sql = "SELECT * FROM forum_posts WHERE parent_id IS NULL ORDER BY created_at DESC";
$result = $conn->query($sql);

// Función para obtener respuestas anidadas
function obtenerRespuestas($parent_id, $conn, $nivel = 0) {
    $sql = "SELECT * FROM forum_posts WHERE parent_id = ? ORDER BY created_at ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $parent_id);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        echo '<div class="card mt-2 ms-' . ($nivel * 3) . '">';
        echo '<div class="card-body p-3">';
        echo '<p><strong>' . htmlspecialchars($row['user_id']) . ':</strong> ' . htmlspecialchars($row['content']) . '</p>';
        echo '<button class="btn btn-sm btn-outline-primary" onclick="responderComentario(' . $row['id'] . ')">Responder</button>';
        echo '<div id="responder-' . $row['id'] . '" class="d-none mt-2">';
        echo '<textarea id="respuesta-' . $row['id'] . '" class="form-control" rows="2" placeholder="Escribe tu respuesta..."></textarea>';
        echo '<button class="btn btn-sm btn-success mt-1" onclick="enviarRespuesta(' . $row['id'] . ')">Enviar</button>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        obtenerRespuestas($row['id'], $conn, $nivel + 1);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Foro de Comentarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
</head>
<body>

    <!-- ENCABEZADO -->
    <div class="header">
        <h3><i class="bi bi-chat-dots"></i> Feedback</h3>
        <div class="profile-container">
            <img src="<?= htmlspecialchars($profile_picture) ?>" alt="Foto de perfil">
            <span><?= htmlspecialchars($username) ?></span>
        </div>
    </div>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="container">
        <!-- Formulario para nuevo comentario -->
        <div class="card mb-4">
            <div class="card-body">
                <textarea id="nuevoComentario" class="form-control" rows="3" placeholder="Escribe tu comentario..."></textarea>
                <button class="btn btn-primary mt-2 w-100" onclick="publicarComentario()">Publicar</button>
            </div>
        </div>

        <!-- Listado de comentarios -->
        <div id="comentarios">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="card mb-2">
                    <div class="card-body">
                        <p><strong><?= htmlspecialchars($row['user_id']) ?>:</strong> <?= htmlspecialchars($row['content']) ?></p>
                        <button class="btn btn-sm btn-outline-primary" onclick="responderComentario(<?= $row['id'] ?>)">Responder</button>
                        <div id="responder-<?= $row['id'] ?>" class="d-none mt-2">
                            <textarea id="respuesta-<?= $row['id'] ?>" class="form-control" rows="2" placeholder="Escribe tu respuesta..."></textarea>
                            <button class="btn btn-sm btn-success mt-1" onclick="enviarRespuesta(<?= $row['id'] ?>)">Enviar</button>
                        </div>
                    </div>
                </div>
                <?php obtenerRespuestas($row['id'], $conn); ?>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function publicarComentario() {
            let comentario = document.getElementById("nuevoComentario").value;
            if (comentario.trim() === "") return alert("No puedes enviar un comentario vacío.");

            let formData = new FormData();
            formData.append("content", comentario);
            formData.append("parent_id", "NULL");

            fetch("procesar_comentario.php", {
                method: "POST",
                body: formData
            }).then(() => location.reload());
        }

        function responderComentario(id) {
            document.getElementById("responder-" + id).classList.toggle("d-none");
        }

        function enviarRespuesta(parent_id) {
            let respuesta = document.getElementById("respuesta-" + parent_id).value;
            if (respuesta.trim() === "") return alert("No puedes enviar una respuesta vacía.");

            let formData = new FormData();
            formData.append("content", respuesta);
            formData.append("parent_id", parent_id);

            fetch("procesar_comentario.php", {
                method: "POST",
                body: formData
            }).then(() => location.reload());
        }
    </script>
</body>
</html>
