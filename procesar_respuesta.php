<?php
require 'auth.php';
require 'db.php';
require 'vendor/autoload.php'; // Cargar PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

checkRole(['Admin']); // Solo el Admin puede acceder

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $response_comments = trim($_POST['response_comments']); // Eliminar espacios extra
    $response_attachment = NULL;

    // Manejo seguro de archivo adjunto
    if (isset($_FILES['response_attachment']) && $_FILES['response_attachment']['error'] == 0) {
        $target_dir = "uploads/";
        $file_name = time() . "_" . basename($_FILES["response_attachment"]["name"]); // Evita sobreescritura
        $target_file = $target_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Permitir solo PDF
        if ($file_type != "pdf") {
            die("❌ Solo se permiten archivos PDF.");
        }

        if (move_uploaded_file($_FILES["response_attachment"]["tmp_name"], $target_file)) {
            $response_attachment = $target_file;
        } else {
            die("❌ Error al subir el archivo.");
        }
    }

    // Actualizar la base de datos con la respuesta
    $sql = "UPDATE solicitudes SET response_comments = ?, response_attachment = ?, fecha_actualizacion = NOW(), estado = 'Finalizado' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $response_comments, $response_attachment, $id);
    
    if ($stmt->execute()) {
        // Obtener el correo y nombre del usuario que hizo la solicitud
        $sql_email = "SELECT u.email, u.username FROM usuarios u 
                      INNER JOIN solicitudes s ON u.id = s.user_id WHERE s.id = ?";
        $stmt_email = $conn->prepare($sql_email);
        $stmt_email->bind_param("i", $id);
        $stmt_email->execute();
        $result_email = $stmt_email->get_result();
        $user = $result_email->fetch_assoc();

        if (!$user || empty($user['email'])) {
            die("❌ No se encontró el email del usuario.");
        }

        $email = $user['email'];
        $username = $user['username'];

        // Configurar PHPMailer
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'abisag.cruz@voov.io'; // Tu correo
            $mail->Password = 'lofm bukv ranu chpe'; // Usa una App Password válida
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Configurar remitente y destinatario
            $mail->setFrom('abisag.cruz@voov.io', 'Soporte de Solicitudes');
            $mail->addAddress($email, $username);

            // Asunto y cuerpo del correo con HTML
            $mail->Subject = " Your request #$id has been answered";
            $mail->isHTML(true);
            $mail->Body = '
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;">
        <h2 style="color: #000d30; text-align: center;">🔔 ¡Your Bio request is ready! 🔔</h2>
        <p>Hola <strong>' . $username . '</strong>,</p>
        <p>Your request with ID <strong>#' . $id . '</strong>has been marked as Completed</strong>.</p>
        
        <div style="background:rgb(238, 239, 240); padding: 15px; border-left: 4px solid #000d30; margin: 15px 0;">
            <p><strong>Admin Comment:</strong></p>
            <p style="font-style: italic;">' . nl2br($response_comments) . '</p>
        </div>

        <p>You can check the full details in the system by clicking the button below:</p>
        <div style="text-align: center; margin: 20px 0;">
            <a href="https://tusistema.com/ver_solicitud.php?id=' . $id . '" 
               style="background: #000d30; color: white; padding: 12px 20px; text-decoration: none; font-size: 16px; border-radius: 5px;">
               🔍 View my request
            </a>
        </div>

        <p style="text-align: center; font-size: 14px; color: #555;">
            If you have any questions, please feel free to reply to this email.
        </p>

        <p style="text-align: center; font-size: 14px; color: #777;">
            <strong>Support Team</strong><br>
            <a href="https://tusistema.com" style="color: #000d30; text-decoration: none;">Go to BIO</a>
        </p>
    </div>
';

            // Agregar archivo adjunto si existe
            if (!empty($response_attachment)) {
                $mail->addAttachment($response_attachment);
            }

            // Enviar correo
            $mail->send();

// Redirigir al listado con el mensaje de éxito para mostrar el modal
header("Location: index.php?success=1");
exit();


        } catch (Exception $e) {
            echo "❌ Error al enviar el correo: {$mail->ErrorInfo}";
        }
    } else {
        echo "❌ Error al actualizar la solicitud en la base de datos.";
    }

    $stmt->close();
    $conn->close();
}
?>
