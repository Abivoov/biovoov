<?php
require 'auth.php';
require 'db.php';

function obtenerComentarios($parent_id = NULL, $nivel = 0) {
    global $conn;
    $sql = "SELECT forum_posts.*, usuarios.username FROM forum_posts
            JOIN usuarios ON forum_posts.user_id = usuarios.id
            WHERE parent_id " . ($parent_id ? "= $parent_id" : "IS NULL") . " ORDER BY created_at DESC";
    
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $margen = $nivel * 30; // Indentación para las respuestas
            echo "<div class='comment-box " . ($parent_id ? "comment-reply" : "") . "'>";
            echo "<p><strong>{$row['username']}</strong>: " . htmlspecialchars($row['content']) . "</p>";
            echo "<button class='btn btn-link p-0' onclick='mostrarFormularioRespuesta({$row['id']})'><i class='bi bi-reply'></i> Responder</button>";
            echo "<div id='responder-{$row['id']}' class='mt-2 reply-box'>";
            echo "  <textarea id='respuesta-{$row['id']}' class='form-control' placeholder='Escribe una respuesta...'></textarea>";
            echo "  <button onclick='publicarComentario({$row['id']})' class='btn btn-sm btn-success mt-1'><i class='bi bi-send'></i> Enviar</button>";
            echo "</div>";

            // Llamado recursivo para cargar respuestas
            obtenerComentarios($row['id'], $nivel + 1);
            echo "</div>";
        }
    }
}

obtenerComentarios();
?>
