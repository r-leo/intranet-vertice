<?php

// Credenciales de conexión a la base de datos:
$bd_host     = '192.168.21.205';
$bd_usuario  = 'vertice';
$bd_password = 'Vertice2017[+-].';
$bd_nombre   = 'vertice01';
$bd_puerto   = 3306;

// Objeto de conexión a la base de datos:
$conexion = new mysqli($bd_host, $bd_usuario, $bd_password, $bd_nombre, $bd_puerto);
$conexion->query("SET NAMES 'utf8'");

?>
