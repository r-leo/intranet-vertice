<?php
  include 'config.php';

  // Filtro de generación:
  if ($_REQUEST['filtro_generacion'] === '0') {
    $filtro_generacion = "";
  }
  else {
    $filtro_generacion = " AND generacion='{$_REQUEST['filtro_generacion']}'";
  }

  // Filtro de sexo:
  if ($_REQUEST['filtro_sexo'] === '0') {
    $filtro_sexo = "";
  }
  else {
    $filtro_sexo = " AND sexo='{$_REQUEST['filtro_sexo']}'";
  }

  // Filtro de carrera:
  if ($_REQUEST['filtro_carrera'] === '0') {
    $filtro_carrera = "";
  }
  else {
    $filtro_carrera = " AND carrera='{$_REQUEST['filtro_carrera']}'";
  }

  // Filtro de estatus:
  if ($_REQUEST['filtro_estatus'] === '0') {
    $filtro_estatus = "";
  }
  else {
    $filtro_estatus = " AND estatus_rapido='{$_REQUEST['filtro_estatus']}'";
  }

  // Cadena de búsqueda:
  if ($_REQUEST['cadena'] === '') {
    $query = "SELECT *
              FROM usuarios
              WHERE estatus='alumno'";
  }
  else {
    $query = "SELECT *
              FROM usuarios
              WHERE CONCAT_WS(' ', nombre, apellido_p, apellido_m) LIKE '%{$_REQUEST['cadena']}%'
              OR id LIKE '%{$_REQUEST['cadena']}%'";
  }

  // String de consulta:
  $query = $query . $filtro_generacion . $filtro_sexo . $filtro_carrera . $filtro_estatus;

  // Ejecutar consulta:
  $busqueda = $conexion->query($query);

  // Procesar y devolver
  if ($busqueda->num_rows > 0) {
    $salida = "<table class='tabla'><tr><th>Expediente</th><th>Nombre</th><th>Generación</th><th>Estatus</th></tr>";
    $mails = '';
    while ($resultado = $busqueda->fetch_assoc()) {
      if ($resultado['estatus_rapido'] == 'activo') {
        $estatus_rapido = "<span class='color-verde'>Activo</span>";
      }
      elseif ($resultado['estatus_rapido'] == 'condicionado') {
        $estatus_rapido = "<span class='color-naranja'>Condicionado</span>";
      }
      elseif ($resultado['estatus_rapido'] == 'baja') {
        $estatus_rapido = "<span class='color-rojo'>Baja</span>";
      }
      $salida = $salida . "<tr><td>" . $resultado['id'] . "</td><td><span class='link' onclick=\"abrir_popup_alumno('" . $resultado['id'] . "');\">" . $resultado['nombre'] . ' ' . $resultado['apellido_p'] . ' ' . $resultado['apellido_m'] . "</span></td><td>" . $resultado['generacion'] . "</td><td>" . $estatus_rapido . "</td></tr>";
      $mails = $mails . ';' . $resultado['correo'];
    }
    $salida = $salida . "</table>";
    $mails = substr($mails, 1);
  } else {
    $salida = '0';
    $mails = '';
  }

  // Salida en caso de un request SQL:
  if ($_REQUEST['consulta'] === 'sql') {
    echo json_encode(array('salida' => $salida, 'mails' => $mails));
  }

  // Salida en caso de un request de Excel:
  elseif ($_REQUEST['consulta'] === 'xlsx') {
    echo '0'; // CODEH: exportar excel
  }

  // Salida en caso de un request de mails:
  elseif ($_REQUEST['consulta'] === 'mails') {
    echo '0'; // CODEH: exportar mails
  }

?>
