<?php

  require_once 'config.php';
  require_once 'enviar_mail.php';

  $registro = $conexion->query("SELECT nombre, usuario, password FROM usuarios WHERE correo='{$_POST['mail_pw']}'");

  if ($registro->num_rows < 1) {
    echo 'Correo no encontrado en la base de datos.';
    exit();
  }
  else {
    $correo = $_POST['mail_pw'];
    $registro = $registro->fetch_assoc();
    $nombre = $registro['nombre'];
    $usuario = $registro['usuario'];
    $password = $registro['password'];

    $contenido = "
    <!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">
    <html>
      <head>
        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">
        <title>Intranet Vértice: recuperar credenciales de acceso</title>
      </head>
      <body>
        <h1>Intranet Vértice</h1>
        <h2 style=\"color: #8cb3d9;\">Recuperación de usuario y contraseña</h2>
        <hr>
        <p>Hola $nombre,</p>
        <p>Se ha solicitado la recuperación de tus credenciales de acceso al Intranet de Vértice:</p>
        <p><span style=\"font-weight: bold;\">Nombre de usuario: </span>$usuario<br>
        <span style=\"font-weight: bold;\">Contraseña: </span>$password</p>
        <p>Recuerda que el nombre de usuario y la contraseña son sensibles a mayúsculas.</p>
      </body>
    </html>
    ";

    $mail->addAddress($correo);
    $mail->Subject = 'Recuperación de credenciales de acceso';
    $mail->msgHTML($contenido);
    if (!$mail->send()) {
      echo $mail->ErrorInfo;
      exit();
    }
    else {
      echo '1';
      exit();
    }
  }


?>
