<?php

  require_once '../config.php';
  date_default_timezone_set('America/Mexico_City');
  $date = date('Y-m-d');

  if (!isset($_POST['expediente'])) {
    echo 'Request POST no encontrado.';
    $conexion -> close();
    exit();
  }
  else {
    $expediente = $_POST['expediente'];
  }

  if ($conexion -> query("INSERT INTO becarios_asistencias (id, fecha) VALUES ($expediente, '$date')")) {
    echo '1';
    exit();
  }
  else {
    echo $conexion -> error;
    exit();
  }

?>
