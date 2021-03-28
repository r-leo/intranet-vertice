<?php
include_once 'config.php';
include_once 'snippets.php';
include_once 'clase_usuario.php';

$usuarios = $conexion->query("SELECT id FROM usuarios WHERE estatus='alumno'");

while ($usuario = $usuarios->fetch_assoc()) {
  $objeto_usuario = new Usuario($usuario['id'], $conexion);
}

echo 'Listo'.

redireccionar('intranet.php');
exit;
