<?php
  include 'config.php';

  $alumno = $conexion->query("SELECT * FROM usuarios WHERE id='{$_POST['id']}'");

  if ($alumno->num_rows > 0) {
    $usuario_fetched = $alumno->fetch_assoc();
    $salida = [
      'estatus'    => '1',
      'nombre'     => $usuario_fetched['nombre'],
      'apellido_p' => $usuario_fetched['apellido_p'],
      'apellido_m' => $usuario_fetched['apellido_m'],
      'generacion' => $usuario_fetched['generacion'],
      'correo'     => $usuario_fetched['correo'],
      'carrera'    => $usuario_fetched['carrera'],
      'sufijo'     => $usuario_fetched['sufijo'],
    ];
  }

  else {
    $salida = [
      'estatus' => '0',
    ];
  }

  echo json_encode($salida);

?>
