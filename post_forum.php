<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user'])) {
    $user_id = $_SESSION['user']['id'];
    $title = $_POST['title'];
    $content = $_POST['content'];

    $sql = "INSERT INTO forum_posts (user_id, title, content) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $title, $content);

    if ($stmt->execute()) {
        header("Location: feedback.php");
    } else {
        echo "Error submitting feedback: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
