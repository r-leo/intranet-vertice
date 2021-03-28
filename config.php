<?php

  /************************************************************
  A partir de esta línea pueden hacerse cambios al archivo */

  // La variable $modo_local habilita las credenciales MySQL locales:
  $modo_local = true;

  // La variable $modo_debug bloquea el acceso al sitio para tareas de mantenimiento:
  $modo_debug = false;

  // Directiva de reporte de errores:
  // Consultar http://php.net/manual/en/function.error-reporting.php
  error_reporting(0);

  // Credenciales MySQL de conexión en MODO LOCAL:
  if ($modo_local) {
    $bd_host     = '127.0.0.1';
    $bd_usuario  = 'root';
    $bd_password = '';
    $bd_nombre   = 'vertice';
    $bd_puerto   = 3306;

    // URL de redirección de salida en modo local:
    $url_salida = 'http://localhost:81?code=3';
  }

  // Credenciales MySQL de conexión en MODO SERVIDOR:
  else {
    $bd_host     = '192.168.21.205';
    $bd_usuario  = 'vertice';
    $bd_password = 'Vertice2017[+-].';
    $bd_nombre   = 'vertice01';
    $bd_puerto   = 3306;

    // URL de redirección de salida en modo servidor:
    $url_salida = 'http://ww2.anahuac.mx/verticeintranet?code=3';
  }

  // Número de versión para los archivos de estilo:
  $sufijo_version = '2_18';


  /************************************************************
  A partir de esta línea no deben hacerse cambios al archivo */

  $conexion = new mysqli($bd_host, $bd_usuario, $bd_password, $bd_nombre, $bd_puerto);
  $conexion->query("SET NAMES 'utf8'");

?>
