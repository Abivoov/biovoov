<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);


try {
    // Configurar SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'tuemail@gmail.com'; // Cambia esto por tu correo
    $mail->Password = 'contraseña-generada'; // Usa la App Password de Google
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Usa STARTTLS
    $mail->Port = 587; // Prueba 465 si 587 no funciona

    // Configurar remitente y destinatario
    $mail->setFrom('tuemail@gmail.com', 'Tu Nombre');
    $mail->addAddress('destinatario@gmail.com', 'Destinatario'); // Cambia esto por el email de prueba

    // Contenido del correo
    $mail->Subject = 'Prueba de PHPMailer con Gmail';
    $mail->Body = 'Hola, este es un correo de prueba enviado desde PHPMailer usando Gmail SMTP.';

    // Enviar correo
    if ($mail->send()) {
        echo '✅ ¡Correo enviado con éxito!';
    } else {
        echo '❌ Error al enviar el correo.';
    }
} catch (Exception $e) {
    echo "❌ Error: {$mail->ErrorInfo}";
}
?>
