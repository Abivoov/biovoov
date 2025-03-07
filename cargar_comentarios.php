<?php
require 'auth.php';
require 'db.php';

function obtenerComentarios($parent_id = NULL, $nivel = 0) {
    global $conn;

    // Consulta preparada para obtener comentarios
    $sql = "SELECT forum_posts.*, usuarios.username, usuarios.profile_picture 
            FROM forum_posts 
            JOIN usuarios ON forum_posts.user_id = usuarios.id 
            WHERE parent_id " . ($parent_id !== NULL ? "= ?" : "IS NULL") . " 
            ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($sql);

    if ($parent_id !== NULL) {
        $stmt->bind_param("i", $parent_id);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $margen = $nivel * 30; // Indentación para respuestas
            echo "<div class='comment-box " . ($parent_id ? "comment-reply" : "") . "' style='margin-left: {$margen}px;'>";
            echo "<div class='d-flex align-items-start'>";
            echo "<img src='" . htmlspecialchars($row['profile_picture']) . "' class='rounded-circle me-2' width='40' height='40'>";
            echo "<div>";
            echo "<p><strong>{$row['username']}</strong>: " . htmlspecialchars($row['content']) . "</p>";
            echo "<small class='text-muted'>" . date("d M Y, H:i", strtotime($row['created_at'])) . "</small>";
            echo "<button class='btn btn-sm btn-outline-primary ms-2' onclick='mostrarFormularioRespuesta({$row['id']})'><i class='bi bi-reply'></i> Responder</button>";
            echo "</div>";
            echo "</div>";

            // Caja de respuesta oculta por defecto
            echo "<div id='responder-{$row['id']}' class='mt-2 reply-box d-none'>";
            echo "  <textarea id='respuesta-{$row['id']}' class='form-control' placeholder='Escribe una respuesta...'></textarea>";
            echo "  <button onclick='publicarComentario({$row['id']})' class='btn btn-sm btn-success mt-1'><i class='bi bi-send'></i> Enviar</button>";
            echo "</div>";

            // Llamado recursivo para respuestas
            obtenerComentarios($row['id'], $nivel + 1);
            echo "</div>";
        }
    }
}

obtenerComentarios();
?>
