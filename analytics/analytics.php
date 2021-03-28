<?php

  /* Documentación de queries aceptadas por este script:

  userIn
    Argumentos:
      pendiente
    Respuesta:
      pendiente
  */

  // Credenciales de conexión a la base de datos:
  $bd_host     = '192.168.21.205';
  $bd_usuario  = 'vertice';
  $bd_password = 'Vertice2017[+-].';
  $bd_nombre   = 'vertice01';
  $bd_puerto   = 3306;

  // Objeto de conexión a la base de datos:
  $conexion = new mysqli($bd_host, $bd_usuario, $bd_password, $bd_nombre, $bd_puerto);
  $conexion->query("SET NAMES 'utf8'");

  if ($_POST['query'] == 'userIn') {
    $conexion->query("UPDATE analytics_globals SET online_users = online_users + 1 WHERE 1");
  }

  elseif ($_POST['query'] == 'userOut') {
    $online_users = $conexion->query("SELECT online_users FROM analytics_globals WHERE 1")->fetch_assoc()['online_users'];
    if (intval($online_users) > 0) {
      $conexion->query("UPDATE analytics_globals SET online_users = online_users - 1 WHERE 1");
    }
  }

?>
