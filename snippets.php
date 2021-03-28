<?php

function barra_progreso($porcentaje) {
  // $porcentaje debe ser un decimal entre 0 y 1
  if ($porcentaje == 0) {
    echo "<div class='barra_progreso'>0 %</div>";
  }
  elseif ($porcentaje < 1) {
    echo "<div class='barra_progreso'><div class='progreso' style='width:" . $porcentaje*100 . "%;'></div></div>";
    echo "<p class='porcentaje'>" . $porcentaje*100 . " %</p>";
  }
  else {
    echo "<div class='barra_progreso'><div class='progreso'>100 %</div></div>";
  }
}

function mensaje($instruccion, $mensaje) {
  global $conexion;
  if (substr_count($instruccion, ';') > 0) {
    if($conexion->multi_query($instruccion)) {
      echo "<div class='card dialog card-green'><p><i class='fa fa-check color-verde'></i>&nbsp;&nbsp;$mensaje</p></div>";
      do {;}
      while ($conexion->next_result());
    }
    else {
      echo "<div class='card dialog card-red'><p><i class='fa fa-times color-rojo'></i>&nbsp;&nbsp;Ha habido un problema al procesar tu solicitud. Detalles del error:</p>
      <p>Solicitud SQL:</p><code>$instruccion</code><p>Respuesta del servidor:</p><code>$conexion->error</code></div>";
    }
    while ($conexion->next_result()) {;}
  }
  else {
    if ($conexion->query($instruccion)) {
      echo "<div class='card dialog card-green'><p><i class='fa fa-check color-verde'></i>&nbsp;&nbsp;$mensaje</p></div>";
    }
    else {
      echo "<div class='card dialog card-red'><p><i class='fa fa-times color-rojo'></i>&nbsp;&nbsp;Ha habido un problema al procesar tu solicitud. Detalles del error:</p>
      <p>Solicitud SQL:</p><code>$instruccion</code><p>Respuesta del servidor:</p><code>$conexion->error</code></div>";
    }
  }
}

function mensaje_error($mensaje) {
  echo "<div class='card dialog card-red'><p><i class='fa fa-times color-rojo'></i>" . $mensaje . "</p></div>";
}

function mensaje_exito($mensaje) {
  echo "<div class='card dialog card-green'><p><i class='fa fa-check color-verde'></i>" . $mensaje . "</p></div>";
}

function redireccionar($url) {
  echo '<script>window.location.href = "' . $url . '";</script>';
}

function periodo_siguiente($periodo) {
  if (intval($periodo[0]) === 1) {
    return array('2', (string)$periodo[1]);
  }
  else {
    return array('1', (string)($periodo[1] + 1));
  }
}

function periodo_mayor($periodo1, $periodo2) {
  if (intval($periodo1[1]) > intval($periodo2[1])) {
    return 1;
  }
  elseif (intval($periodo1[1]) < intval($periodo2[1])) {
    return 2;
  }
  elseif (intval($periodo1[0]) > intval($periodo2[0])) {
    return 1;
  }
  elseif (intval($periodo1[0]) < intval($periodo2[0])) {
    return 2;
  }
  else {
    return 0;
  }
}

function periodo_suma($periodo, $duracion) {
  if (intval($duracion) % 2 == 0) {
    return array((string)$periodo[0], (string)($periodo[1] + intval($duracion)/2));
  }
  elseif (intval($periodo[0]) === 1) {
    return array('2', (string)($periodo[1] + (intval($duracion) - 1)/2));
  }
  else {
    return array('1', (string)($periodo[1] + (intval($duracion) + 1)/2));
  }
}

// Función de transformación de meses:
function mes($n) {
  $array = array(
    1 => 'enero',
    2 => 'febrero',
    3 => 'marzo',
    4 => 'abril',
    5 => 'mayo',
    6 => 'junio',
    7 => 'julio',
    8 => 'agosto',
    9 => 'septiembre',
    10 => 'octubre',
    11 => 'noviembre',
    12 => 'diciembre'
  );
  return $array[intval($n)];
}

// Función de transformación de días de la semana:
function diasem($n) {
  $array = array(
    0 => 'domingo',
    1 => 'lunes',
    2 => 'martes',
    3 => 'miércoles',
    4 => 'jueves',
    5 => 'viernes',
    6 => 'sábado'
  );
  return $array[intval($n)];
}

// Funciones de formateo de fechas:
function fecha_formateada($fecha) {
  $fecha_raw = strtotime($fecha);
  return date('d', $fecha_raw) . '/' . substr(mes(date('m', $fecha_raw)), 0, 3) . ' ' . date('h:i a', $fecha_raw);
}

function fecha_formateada_general($fecha) {
  $fecha_raw = strtotime($fecha);
  return date('d', $fecha_raw) . '/' . substr(mes(date('m', $fecha_raw)), 0, 3) . '/' .  date('Y', $fecha_raw);
}

// Función para restablecer el directorio de trabajo del servidor:
// Esta función es requerida para el control de búfer de salida.
function restablecer_directorio($output_buffer) {
  chdir(dirname($_SERVER['SCRIPT_FILENAME']));
  return "";
}

// Función de registro de variables de sesión:
function actualizar_sesion($fila) {
  $_SESSION['nombre'] = $fila['nombre'];
  $_SESSION['apellido_p'] = $fila['apellido_p'];
  $_SESSION['apellido_m'] = $fila['apellido_p'];
  $_SESSION['generacion'] = $fila['generacion'];
  $_SESSION['fecha_nac'] = strtotime($fila['fecha_nac']);
  $_SESSION['estatus'] = $fila['estatus'];
}

// Función de limpieza de caracteres:
function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

// Función de protección de variables no definidas:
function ifsetor(&$variable, $default='') {
  if (isset($variable)) {
    $tmp = $variable;
  }
  else {
    $tmp = $default;
  }
  return $tmp;
}

// Función de formateo de periodo:
function periodo_formateado($periodo) {
  if (intval($periodo[0]) === 1) {
    $resultado = 'Ene-jun ' . $periodo[1];
  }
  elseif (intval($periodo[0]) === 2) {
    $resultado = 'Ago-dic ' . $periodo[1];
  }
  else {
    $resultado = 'Hubo un error al formatear el periodo';
  }
  return $resultado;
}

// Función de Dirac:
function dirac($input) {
  if (intval($input) === 0) {
    $resultado = "<i class='fa fa-times inline color-rojo'></i>";
  }
  else {
    $resultado = "<i class='fa fa-check inline color-verde'></i>";
  }
  return $resultado;
}

// Función para generar un array numérico a partir de una consulta MySQL:
function generar_array($consulta) {
  $resultado = array();
  while ($fila = $consulta->fetch_assoc()) {
    $resultado[] = $fila;
  }
  return $resultado;
}

// Función para generar strings aleatorios:
function string_aleatorio($longitud) {
  $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $resultado = '';
  for ($i = 0; $i < $longitud; $i++) {
    $resultado .= $caracteres[rand(0, strlen($caracteres) - 1)];
  }
  return $resultado;
}

// Funció para devolver un array de colores:
function array_colores($n) {
  $colores_base_1 = ['#0B486B', '#3B8686', '#79BD9A', '#A8DBA8', '#CFF09E'];
  $colores_base_2 = ['#C5E0DC', '#ECE5CE', '#F1D4AF', '#E08E79', '#774F38'];
  $colores_extendido = array_merge($colores_base_1, $colores_base_2);
  return array_slice($colores_extendido, 0, $n);
}

?>
