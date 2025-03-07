<?php
require 'auth.php';
require 'db.php'; // Conexión a la base de datos
checkRole(['Manager', 'Admin']); // Solo usuarios autenticados pueden ver

// Obtener la foto de perfil del usuario autenticado
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
$sql = "SELECT forum_posts.*, usuarios.username, usuarios.profile_picture 
        FROM forum_posts 
        JOIN usuarios ON forum_posts.user_id = usuarios.id 
        WHERE parent_id IS NULL ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

// Función para obtener respuestas anidadas con JOIN para mostrar username y foto
function obtenerRespuestas($parent_id, $conn, $nivel = 1) {
    $sql = "SELECT forum_posts.*, usuarios.username, usuarios.profile_picture 
            FROM forum_posts 
            JOIN usuarios ON forum_posts.user_id = usuarios.id 
            WHERE parent_id = ? ORDER BY created_at ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $parent_id);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        echo '<div class="respuesta-container">';
        echo '<div class="linea-respuesta"></div>'; // Línea que conecta con el comentario original
        echo '<div class="card mb-1 respuesta-card ms-' . ($nivel * 2) . '">';
        echo '<div class="card-body">';
        echo '<div class="d-flex align-items-start">';
        echo '<img src="' . htmlspecialchars($row['profile_picture'] ?? 'uploads/default-avatar.png') . '" class="rounded-circle me-2">';
        echo '<div>';
        echo '<p><strong>' . htmlspecialchars($row['username'] ?? 'Usuario Desconocido') . ':</strong> ' . htmlspecialchars($row['content']) . '</p>';
        echo '<small class="text-muted">' . date("d M Y, H:i", strtotime($row['created_at'])) . '</small>';
        echo '</div>';
        echo '</div>';
        echo '<button class="btn btn-xs btn-outline-primary" onclick="responderComentario(' . $row['id'] . ')">Responder</button>';
        echo '<div id="responder-' . $row['id'] . '" class="d-none mt-2">';
        echo '<textarea id="respuesta-' . $row['id'] . '" class="form-control form-control-sm" rows="1" placeholder="Escribe tu respuesta..."></textarea>';
        echo '<button class="btn btn-xs btn-success mt-1" onclick="enviarRespuesta(' . $row['id'] . ')">Enviar</button>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        obtenerRespuestas($row['id'], $conn, $nivel + 1);
    }
}

    $sql = "SELECT forum_posts.*, usuarios.username, usuarios.profile_picture 
            FROM forum_posts 
            JOIN usuarios ON forum_posts.user_id = usuarios.id 
            WHERE parent_id = ? ORDER BY created_at ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $parent_id);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        echo '<div class="card mb-2 respuesta-card ms-' . ($nivel * 2) . '">';
        echo '<div class="card-body p-2">';
        echo '<div class="d-flex align-items-start">';
        echo '<img src="' . htmlspecialchars($row['profile_picture'] ?? 'uploads/default-avatar.png') . '" class="rounded-circle me-2" width="35" height="35">';
        echo '<div>';
        echo '<p><strong>' . htmlspecialchars($row['username'] ?? 'Usuario Desconocido') . ':</strong> ' . htmlspecialchars($row['content']) . '</p>';
        echo '<small class="text-muted">' . date("d M Y, H:i", strtotime($row['created_at'])) . '</small>';
        echo '</div>';
        echo '</div>';
        echo '<button class="btn btn-sm btn-outline-primary" onclick="responderComentario(' . $row['id'] . ')">Responder</button>';
        echo '<div id="responder-' . $row['id'] . '" class="d-none mt-2">';
        echo '<textarea id="respuesta-' . $row['id'] . '" class="form-control" rows="2" placeholder="Escribe tu respuesta..."></textarea>';
        echo '<button class="btn btn-sm btn-success mt-1" onclick="enviarRespuesta(' . $row['id'] . ')">Enviar</button>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        obtenerRespuestas($row['id'], $conn, $nivel + 1); // Llamada recursiva para respuestas anidadas
    }


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Foro de Comentarios</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<style>

.comment-card {
    font-size: 0.9rem; /* Reduce el tamaño del texto */
    padding: 8px; /* Reduce el espacio dentro de la tarjeta */
    border-radius: 8px; /* Bordes más redondeados */
}

.comment-card img {
    width: 35px; /* Imagen de perfil más pequeña */
    height: 35px;
}

.comment-card p {
    margin-bottom: 4px; /* Reduce espacio entre texto */
}

.comment-card small {
    font-size: 0.8rem; /* Reduce el tamaño de la fecha */
}

    .profile-img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #007bff; /* Borde azul */
}
.respuesta-container {
    position: relative;
    margin-left: 15px;
}

.linea-respuesta {
    position: absolute;
    left: -10px;
    top: 10px;
    width: 2px;
    height: 100%;
    background-color: #007bff; /* Color azul para la línea */
}

.respuesta-card {
    border-left: 3px solid #007bff; /* Borde azul para diferenciar respuestas */
    background-color: #f1f8ff; /* Fondo más claro */
    font-size: 0.75rem; /* Reducir aún más el tamaño del texto */
    padding: 3px; /* Menos espacio interno */
    margin-left: 10px; /* Más desplazado a la derecha */
    max-width: 90%; /* Que no ocupe todo el ancho */
}

.respuesta-card .card-body {
    padding: 4px; /* Reducir más el padding interno */
}

.respuesta-card img {
    width: 25px; /* Imagen más pequeña */
    height: 25px;
}

.respuesta-card p {
    margin-bottom: 2px; /* Menos espacio entre el texto */
    font-size: 0.7rem; /* Texto más pequeño */
}

.respuesta-card small {
    font-size: 0.65rem; /* Fecha aún más pequeña */
}



@media (max-width: 768px) {
    .respuesta-card {
        margin-left: 10px !important;
    }
}


@media (max-width: 768px) {
    .col-md-4, .col-md-8 {
        width: 100%;
    }
}

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
<body>

    <!-- ENCABEZADO -->
    <div class="header">
    <a href="javascript:history.back()" class="home-icon">
        <i class="bi bi-house-door-fill fs-3"></i>
    </a>
        <h3><i class="bi bi-chat-dots"></i> Feedback</h3>
        <div class="profile-container">
            <img src="<?= htmlspecialchars($profile_picture) ?>" alt="Foto de perfil">
            <span><?= htmlspecialchars($username) ?></span>
        </div>
    </div>

    <!-- CONTENIDO PRINCIPAL -->
   <!-- CONTENIDO PRINCIPAL -->
<div class="container">
    <div class="row">
        <!-- Sección para escribir un nuevo comentario -->
        <div class="col-md-4"> <!-- Ocupa 4 columnas de 12 en pantallas medianas/grandes -->
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="mb-3">New comment</h6>
                    <textarea id="nuevoComentario" class="form-control" rows="3" placeholder="Say something..."></textarea>
                    <button class="btn btn-primary mt-2 w-100" onclick="publicarComentario()">Share</button>
                </div>
            </div>
        </div>

        <!-- Sección de comentarios -->
        <div class="col-md-8"> <!-- Ocupa 8 columnas en pantallas medianas/grandes -->
            <h5 class="mb-3">Comments</h5>
            <div id="comentarios">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="card mb-2 comment-card">
    <div class="card-body">
        <div class="d-flex align-items-start">
            <img src="<?= htmlspecialchars($row['profile_picture'] ?? 'uploads/default-avatar.png') ?>" class="rounded-circle me-2">
            <div>
                <p><strong><?= htmlspecialchars($row['username'] ?? 'Usuario Desconocido') ?>:</strong> <?= htmlspecialchars($row['content']) ?></p>
                <small class="text-muted"><?= date("d M Y, H:i", strtotime($row['created_at'])) ?></small>
            </div>
        </div>
        <button class="btn btn-sm btn-outline-primary" onclick="responderComentario(<?= $row['id'] ?>)">Reply</button>
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
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById("nuevoComentario").value = ""; // Limpiar el campo de texto
            agregarComentarioAlDOM(data); // Agregar comentario sin recargar
        } else {
            alert(data.message);
        }
    });
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

    fetch("guardar_comentario.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById("respuesta-" + parent_id).value = ""; // Limpiar respuesta
            agregarComentarioAlDOM(data, parent_id); // Agregar respuesta sin recargar
        } else {
            alert(data.message);
        }
    });
}

function agregarComentarioAlDOM(data, parent_id = null) {
    let comentarioHTML = `
        <div class="card mb-2">
            <div class="card-body">
                <div class="d-flex align-items-start">
                    <img src="${data.profile_picture}" class="rounded-circle me-2" width="40" height="40">
                    <div>
                        <p><strong>${data.username}:</strong> ${data.content}</p>
                        <small class="text-muted">Justo ahora</small>
                    </div>
                </div>
                <button class="btn btn-sm btn-outline-primary" onclick="responderComentario(${data.id})">Responder</button>
                <div id="responder-${data.id}" class="d-none mt-2">
                    <textarea id="respuesta-${data.id}" class="form-control" rows="2" placeholder="Escribe tu respuesta..."></textarea>
                    <button class="btn btn-sm btn-success mt-1" onclick="enviarRespuesta(${data.id})">Enviar</button>
                </div>
            </div>
        </div>
    `;

    if (parent_id) {
        document.getElementById("responder-" + parent_id).classList.add("d-none"); // Ocultar caja de respuesta
        let parentDiv = document.getElementById("comentarios");
        if (!parentDiv) parentDiv = document.createElement("div");
        parentDiv.innerHTML += comentarioHTML;
    } else {
        document.getElementById("comentarios").insertAdjacentHTML("afterbegin", comentarioHTML);
    }
}

    </script>
</body>
</html>
