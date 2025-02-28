<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer();
if ($mail) {
    echo "PHPMailer está funcionando correctamente.";
} else {
    echo "Error en PHPMailer.";
}
?>
