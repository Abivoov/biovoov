<?php
require 'auth.php';
require 'db.php';
require 'vendor/autoload.php'; // Cargar PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

checkRole(['Admin']); // Solo el Admin puede acceder

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $response_comments = $_POST['response_comments'];

    // Manejo de archivo adjunto
    $response_attachment = NULL;
    if (isset($_FILES['response_attachment']) && $_FILES['response_attachment']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["response_attachment"]["name"]);
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Verificar que sea un archivo PDF
        if ($file_type != "pdf") {
            die("Solo se permiten archivos PDF.");
        }

        if (move_uploaded_file($_FILES["response_attachment"]["tmp_name"], $target_file)) {
            $response_attachment = $target_file;
        } else {
            die("Error al subir el archivo.");
        }
    }

    // Actualizar la base de datos con la respuesta y el archivo adjunto
    $sql = "UPDATE solicitudes SET response_comments = ?, response_attachment = ?, fecha_actualizacion = NOW(), estado = 'Finalizado' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $response_comments, $response_attachment, $id);
    
    if ($stmt->execute()) {
        // Obtener el correo del solicitante
        $sql_email = "SELECT usuarios.email FROM usuarios INNER JOIN solicitudes ON usuarios.id = solicitudes.user_id WHERE solicitudes.id = ?";
        $stmt_email = $conn->prepare($sql_email);
        $stmt_email->bind_param("i", $id);
        $stmt_email->execute();
        $result_email = $stmt_email->get_result();
        $user = $result_email->fetch_assoc();
        $email = $user['email'];

        // Configurar PHPMailer
        $mail = new PHPMailer(true);

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'abisag.cruz@voov.io'; // Usa tu correo
            $mail->Password = 'lofm bukv ranu chpe'; // Asegúrate de usar una App Password válida
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Usa STARTTLS
            $mail->Port = 587; // Si no funciona, usa 465 con SSL
        
            // Configurar remitente y destinatario
            $mail->setFrom('abisag.cruz@voov.io', 'Notificaciones'); // Usa el mismo correo que en Username
            $mail->addAddress($email); // Enviar al destinatario
        
            // Asunto y cuerpo del correo
            $mail->Subject = "Tu solicitud ha sido finalizada";
            $mail->Body = "Hola,\n\nTu solicitud con ID $id ha sido marcada como finalizada.";
        
            // Si hay un archivo adjunto, agregarlo
            if (!empty($response_attachment)) {
                $mail->addAttachment($response_attachment);
            }
        
            // Enviar correo
            $mail->send();
            echo '✅ ¡Correo enviado con éxito!';
        } catch (Exception $e) {
            echo "❌ Error al enviar el correo: {$mail->ErrorInfo}";
        }
        
    
    $stmt->close();
    $conn->close();
}
}
?>
