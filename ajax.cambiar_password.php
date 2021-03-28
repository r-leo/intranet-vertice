<?php
  include 'config.php';
  $conexion->query("UPDATE usuarios SET password='{$_REQUEST['password']}' WHERE id='{$_REQUEST['id']}'");
?>
