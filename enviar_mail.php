<?php

  // Objeto de envío de correo electrónico:
  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;
  use PHPMailer\PHPMailer\SMTP;
  require 'PHPMailer/src/PHPMailer.php';
  require 'PHPMailer/src/Exception.php';
  require 'PHPMailer/src/SMTP.php';
  $mail = new PHPMailer;
  $mail->setLanguage('fr', '/PHPMailer/language');
  $mail->CharSet = "UTF-8";
  $mail->isSMTP();
  $mail->Host = 'smtp.gmail.com';
  $mail->Port = 587;
  $mail->SMTPSecure = 'tls';
  $mail->SMTPAuth = true;
  $mail->Username = 'verticeintranet@gmail.com';
  $mail->Password = 'AGhj89PTyu';
  $mail->setFrom('verticeintranet@gmail.com', 'Intranet Vértice');

  // Snippet de envío de correo electrónico:

    /*
    $contenido = "
    <!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">
    <html>
      <head>
        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">
        <title>PHPMailer Test</title>
      </head>
      <body>
        <h1>Prueba del módulo PHPMailer</h1>
        <p>Mail de prueba desde el Intranet de Vértice.</p>
      </body>
    </html>
    ";
    $mail->addAddress('rodrigo.leop@anahuac.mx');
    $mail->Subject = 'Correo electrónico de prueba';
    $mail->msgHTML($contenido);
    if (!$mail->send()) {
      echo $mail->ErrorInfo;
    }
    else {
      echo 'success';
    }
    */


?>
