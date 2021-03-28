<?php
  include 'config.php';
  $tipos = $conexion->query("SELECT * FROM requisitos WHERE plan='{$_REQUEST['plan']}' ORDER BY nombre ASC");
  $resultado = array();
  while ($tipo = $tipos->fetch_assoc()) {
    $resultado[] = $tipo;
  }
  echo json_encode($resultado);
?>
