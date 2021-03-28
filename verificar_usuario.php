<?php
  include 'config.php';
  $usuarios = $conexion->query("SELECT * FROM usuarios WHERE usuario='{$_REQUEST['usuario']}'");
  echo $usuarios->num_rows;
?>
