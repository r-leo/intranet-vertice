<?php

  require_once '../config.php';

  if (!isset($_POST['expediente'])) {
    echo 'Request POST no encontrado.';
    $conexion -> close();
    exit();
  }
  else {
    $expediente = $_POST['expediente'];
    $nombre     = $_POST['nombre'];
    $apellido_p = $_POST['apellido_p'];
    $apellido_m = $_POST['apellido_m'];
    $carrera    = $_POST['carrera'];
    $correo     = $_POST['correo'];
  }

  if ($conexion -> query("INSERT INTO becarios (id, nombre, apellido_p, apellido_m, carrera, correo) VALUES ('$expediente', '$nombre', '$apellido_p', '$apellido_m', '$carrera', '$correo')")) {
    echo '1';
    $conexion -> close();
    exit();
  }
  else {
    echo $conexion -> error;
    $conexion -> close();
    exit();
  }

?>
