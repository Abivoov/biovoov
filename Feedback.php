<?php 
session_start();
include 'db.php';

// Obtener mensajes del foro con la imagen de perfil del usuario
$sql = "SELECT forum_posts.*, usuarios.username, usuarios.profile_picture 
        FROM forum_posts 
        JOIN usuarios ON forum_posts.user_id = usuarios.id 
        ORDER BY forum_posts.created_at DESC";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Feedback</title>
    <link 
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" 
        rel="stylesheet"
    />
    <style>
        .feedback-card {
            background-color: #1e1e1e;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            color: #fff;
            box-shadow: 0 2px 5px rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .feedback-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }

        .feedback-content {
            flex: 1;
        }

        .feedback-card h5 {
            color: #1ac6ff;
            margin-bottom: 5px;
            font-size: 1.1rem;
        }

        .feedback-card small {
            color: #bbb;
            font-size: 0.85rem;
        }
    </style>
</head>
<body class="bg-dark text-white">
    <div class="container mt-4">
        <h2 class="mb-4">Community Feedback</h2>

        <!-- Formulario para publicar -->
        <?php if (isset($_SESSION['user'])): ?>
        <form action="post_forum.php" method="POST">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Message</label>
                <textarea name="content" class="form-control" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Post</button>
        </form>
        <hr>
        <?php endif; ?>

        <!-- Listado de mensajes del foro -->
        <?php while ($row = $result->fetch_assoc()): ?>
        <div class="feedback-card">
            <img src="<?= !empty($row['profile_picture']) ? htmlspecialchars($row['profile_picture']) : 'uploads/default-avatar.png'; ?>" 
                 alt="User Avatar" class="feedback-avatar">
            <div class="feedback-content">
                <h5><?= htmlspecialchars($row['title']) ?></h5>
                <p class="card-text"><?= nl2br(htmlspecialchars($row['content'])) ?></p>
                <small>Posted by <?= htmlspecialchars($row['username']) ?> on <?= $row['created_at'] ?></small>
            </div>
        </div>
        <?php endwhile; ?>

    </div>
</body>
</html>
