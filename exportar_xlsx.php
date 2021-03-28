<?php

require_once 'config.php';

/** Include PHPExcel */
require_once dirname(__FILE__) . '/Classes/PHPExcel.php';

// Objetivo: lista de alumnos inscritos a una actividad:
if ($_GET['target'] == 'actividad') {
  $clave = $_GET['clave'];
  $inscritos = $conexion->query("SELECT usuarios.id, nombre, apellido_p, apellido_m, generacion, correo, actividades_usuarios.asistencia AS asistencia FROM usuarios INNER JOIN actividades_usuarios ON usuarios.id=actividades_usuarios.id WHERE actividades_usuarios.clave='$clave'");
  $actividad = $conexion->query("SELECT nombre FROM actividades WHERE clave='$clave'")->fetch_assoc()['nombre'];
  $fecha_actividad = $conexion->query("SELECT fecha_inicio FROM actividades WHERE clave='$clave'")->fetch_assoc()['fecha_inicio'];
  $nombre_archivo = strtolower(str_replace(' ', '_', $fecha_actividad)) . '__' . strtolower(str_replace(' ', '_', $actividad));
  $objPHPExcel = new PHPExcel();
  $objPHPExcel->getActiveSheet()->setTitle('Lista de actividades');
  $objPHPExcel->setActiveSheetIndex(0)
              ->setCellValue('A1', 'Expediente')
              ->setCellValue('B1', 'Nombre')
              ->setCellValue('C1', 'Generación')
              ->setCellValue('D1', 'Correo')
              ->setCellValue('E1', 'Asistencia');
  $fila = 2;
  while ($inscrito = $inscritos->fetch_assoc()) {
    $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . $fila, $inscrito['id'])
                ->setCellValue('B' . $fila, $inscrito['nombre'] . ' ' . $inscrito['apellido_p'] . ' ' . $inscrito['apellido_m'])
                ->setCellValue('C' . $fila, $inscrito['generacion'])
                ->setCellValue('D' . $fila, $inscrito['correo'])
                ->setCellValue('E' . $fila, $inscrito['asistencia']);
    $fila++;
  }
}

// Objetivo: evaluación de un periodo:
elseif ($_GET['target'] == 'evaluacion') {

  $periodo_1 = strval($_GET['periodo']);
  $periodo_0 = strval(intval($periodo_1) - 1);
  $suma_actividades_1 = $conexion->query("SELECT COUNT(clave) AS suma_actividades_1 FROM actividades WHERE periodo='$periodo_1'")->fetch_assoc()['suma_actividades_1'] + 0;
  $suma_actividades_0 = $conexion->query("SELECT COUNT(clave) AS suma_actividades_0 FROM actividades WHERE periodo='$periodo_0'")->fetch_assoc()['suma_actividades_0'] + 0;
  $suma_actividades_v = round(($suma_actividades_1 - $suma_actividades_0)/$suma_actividades_0, 1);
  $suma_asistencias_1 = $conexion->query("SELECT SUM(asistencia) AS suma_asistencias_1 FROM actividades_usuarios INNER JOIN actividades ON actividades_usuarios.clave=actividades.clave WHERE actividades.periodo='$periodo_1'")->fetch_assoc()['suma_asistencias_1'] + 0;
  $suma_asistencias_0 = $conexion->query("SELECT SUM(asistencia) AS suma_asistencias_0 FROM actividades_usuarios INNER JOIN actividades ON actividades_usuarios.clave=actividades.clave WHERE actividades.periodo='$periodo_0'")->fetch_assoc()['suma_asistencias_0'] + 0;
  $suma_asistencias_v = round(($suma_asistencias_1 - $suma_asistencias_0)/$suma_asistencias_0, 1);

  $nombre_archivo = 'evaluacion';
  $objPHPExcel = new PHPExcel();
  $objPHPExcel->getActiveSheet()->setTitle('Resumen');
  $objPHPExcel->setActiveSheetIndex(0)
              ->setCellValue('A1', 'Indicador')
              ->setCellValue('B1', 'Periodo actual')
              ->setCellValue('C1', 'Periodo anterior')
              ->setCellValue('D1', 'Variación');

  $objPHPExcel->setActiveSheetIndex(0)
              ->setCellValue('A2', 'Número total de actividades')
              ->setCellValue('B2', $suma_actividades_1)
              ->setCellValue('C2', $suma_actividades_0)
              ->setCellValue('D2', $suma_actividades_v);

  $objPHPExcel->setActiveSheetIndex(0)
              ->setCellValue('A3', 'Número total de asistencias')
              ->setCellValue('B3', $suma_asistencias_1)
              ->setCellValue('C3', $suma_asistencias_0)
              ->setCellValue('D3', $suma_asistencias_v);

  $objPHPExcel->createSheet(1);
  $objPHPExcel->setActiveSheetIndex(1);
  $objPHPExcel->getActiveSheet()->setTitle('Evaluación detallada');

  $objPHPExcel->setActiveSheetIndex(1)
              ->setCellValue('A1', 'Actividad')
              ->setCellValue('B1', 'Fecha de inicio')
              ->setCellValue('C1', 'Fecha de fin')
              ->setCellValue('D1', 'Número de inscritos')
              ->setCellValue('E1', 'Número de asistencias')
              ->setCellValue('F1', 'Índice de asistencias');

  $actividades = $conexion->query("SELECT * FROM actividades WHERE periodo='$periodo_1' ORDER BY fecha_inicio");
  $indice_max = 0;
  $indice_min = 1;
  $indice_avg_num = 0;
  $indice_avg_den = 0;
  $fila = 2;
  while ($actividad = $actividades->fetch_assoc()) {
    $actividad_valores = $conexion->query("SELECT COUNT(id) AS actividad_inscripciones, SUM(asistencia) AS actividad_asistencias FROM actividades_usuarios WHERE clave='{$actividad['clave']}'")->fetch_assoc();
    $indice_asistencia = round($actividad_valores['actividad_asistencias'] / $actividad_valores['actividad_inscripciones'], 1);
    if ($indice_asistencia > $indice_max) {
      $indice_max = $indice_asistencia;
    }
    if ($indice_asistencia < $indice_min) {
      $indice_min = $indice_asistencia;
    }
    $indice_avg_num = $indice_avg_num + $indice_asistencia;
    $indice_avg_den = $indice_avg_den + 1;

    $objPHPExcel->setActiveSheetIndex(1)
                ->setCellValue('A' . $fila, $actividad['nombre'])
                ->setCellValue('B' . $fila, $actividad['fecha_inicio'])
                ->setCellValue('C' . $fila, $actividad['fecha_fin'])
                ->setCellValue('D' . $fila, $actividad_valores['actividad_inscripciones'])
                ->setCellValue('E' . $fila, $actividad_valores['actividad_asistencias'])
                ->setCellValue('F' . $fila, $indice_asistencia);
    $fila++;
  }
  $indice_avg = round($indice_avg_num / $indice_avg_den, 1);

  $actividades_0 = $conexion->query("SELECT * FROM actividades WHERE periodo='$periodo_0'");
  $indice_max_0 = 0;
  $indice_min_0 = 0;
  while ($actividad_0 = $actividades_0->fetch_assoc()) {
    $actividad_valores_0 = $conexion->query("SELECT COUNT(id) AS actividad_inscripciones, SUM(asistencia) AS actividad_asistencias FROM actividades_usuarios WHERE clave='{$actividad_0['clave']}'")->fetch_assoc();
    $indice_asistencia_0 = round($actividad_valores_0['actividad_asistencias'] / $actividad_valores_0['actividad_inscripciones'], 1);
    if ($indice_asistencia_0 > $indice_max_0) {
      $indice_max_0 = $indice_asistencia_0;
    }
    if ($indice_asistencia_0 < $indice_min_0) {
      $indice_min_0 = $indice_asistencia_0;
    }
    $indice_avg_num_0 = $indice_avg_num_0 + $indice_asistencia_0;
    $indice_avg_den_0 = $indice_avg_den_0 + 1;
  }
  $indice_avg_0 = round($indice_avg_num_0 / $indice_avg_den_0, 1);
  $variacion_max = round(($indice_max - $indice_max_0) / $indice_max_0, 1);
  $variacion_min = round(($indice_min - $indice_min_0) / $indice_min_0, 1);
  $variacion_avg = round(($indice_avg - $indice_avg_0) / $indice_avg_0, 1);

  $objPHPExcel->setActiveSheetIndex(0)
              ->setCellValue('A4', 'Índice máximo de asistencia')
              ->setCellValue('B4', $indice_max)
              ->setCellValue('C4', $indice_max_0)
              ->setCellValue('D4', $variacion_max);

  $objPHPExcel->setActiveSheetIndex(0)
              ->setCellValue('A5', 'Índice mínimo de asistencia')
              ->setCellValue('B5', $indice_min)
              ->setCellValue('C5', $indice_min_0)
              ->setCellValue('D5', $variacion_min);

  $objPHPExcel->setActiveSheetIndex(0)
              ->setCellValue('A6', 'Índice promedio de asistencia')
              ->setCellValue('B6', $indice_avg)
              ->setCellValue('C6', $indice_avg_0)
              ->setCellValue('D6', $variacion_avg);
}

// Objetivo: lista de alumnos:
elseif ($_GET['target'] == 'lista') {
  // Filtro de generación:
  if (!isset($_GET['gen']) || $_GET['gen'] == '') {
    $filtro_generacion = "";
  }
  else {
    $filtro_generacion = " AND generacion='" . $_GET['gen'] . "'";
  }

  // Filtro de sexo:
  if (!isset($_GET['sex']) || $_GET['sex'] == '') {
    $filtro_sexo = "";
  }
  else {
    $filtro_sexo = " AND sexo='" . $_GET['sex'] . "'";
  }

  // Filtro de carrera:
  if (!isset($_GET['car']) || $_GET['car'] == '') {
    $filtro_carrera = "";
  }
  else {
    $filtro_carrera = " AND carrera='" . $_GET['car'] . "'";
  }

  // Filtro de estatus:
  if (!isset($_GET['est']) || $_GET['est'] == '') {
    $filtro_estatus = "";
  }
  else {
    $filtro_estatus = " AND estatus_rapido='" . $GET['est'] . "'";
  }

  // Cadena de búsqueda:
  if (!isset($_GET['nom']) || $_GET['nom'] == '') {
    $query = "SELECT * FROM usuarios WHERE estatus='alumno'";
  }
  else {
    $query = "SELECT * FROM usuarios WHERE estatus='alumno' AND (CONCAT_WS(' ', nombre, apellido_p, apellido_m) LIKE '%" . $_GET['nom'] . "%' OR id LIKE '%" . $_GET['nom'] . "%')";
  }

  // String de consulta:
  $query = $query . $filtro_generacion . $filtro_sexo . $filtro_carrera . $filtro_estatus;

  // Ejecutar consulta:
  $busqueda = $conexion->query($query);

  $nombre_archivo = 'lista';
  $objPHPExcel = new PHPExcel();
  $objPHPExcel->getActiveSheet()->setTitle('Lista');
  $objPHPExcel->setActiveSheetIndex(0)
              ->setCellValue('A1', 'Expediente')
              ->setCellValue('B1', 'Nombre')
              ->setCellValue('C1', 'Apellido paterno')
              ->setCellValue('D1', 'Apellido materno')
              ->setCellValue('E1', 'Generación')
              ->setCellValue('F1', 'Fecha de nacimiento')
              ->setCellValue('G1', 'Correo electrónico')
              ->setCellValue('H1', 'Sexo')
              ->setCellValue('I1', 'Carrera')
              ->setCellValue('J1', 'Estatus');

  $fila = 2;
  while ($usuario = $busqueda->fetch_assoc()) {
    $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . $fila, $usuario['id'])
                ->setCellValue('B' . $fila, $usuario['nombre'])
                ->setCellValue('C' . $fila, $usuario['apellido_p'])
                ->setCellValue('D' . $fila, $usuario['apellido_m'])
                ->setCellValue('E' . $fila, $usuario['generacion'])
                ->setCellValue('F' . $fila, $usuario['fecha_nac'])
                ->setCellValue('G' . $fila, $usuario['correo'])
                ->setCellValue('H' . $fila, $usuario['sexo'])
                ->setCellValue('I' . $fila, $usuario['carrera'])
                ->setCellValue('J' . $fila, $usuario['estatus_rapido']);
    $fila++;
  }
}

// Objetivo: lista del 20 aniversario:
elseif ($_GET['target'] == '20aniversario') {
  $registro = $conexion->query("SELECT * FROM aniversario WHERE 1 ORDER BY nombre, apellido_p, apellido_m");
  $nombre_archivo = 'registro20aniversario';
  $objPHPExcel = new PHPExcel();
  $objPHPExcel->getActiveSheet()->setTitle('Registro');
  $objPHPExcel->setActiveSheetIndex(0)
              ->setCellValue('A1', 'Nombre')
              ->setCellValue('B1', 'Apellido paterno')
              ->setCellValue('C1', 'Apellido materno')
              ->setCellValue('D1', 'Correo electrónico')
              ->setCellValue('E1', 'Perfil')
              ->setCellValue('F1', 'Campus')
              ->setCellValue('G1', 'Generación')
              ->setCellValue('H1', 'Celular (egresados)')
              ->setCellValue('I1', 'Empresa (egresados)')
              ->setCellValue('J1', 'Puesto (egresados)')
              ->setCellValue('K1', 'Interesado en participar (egresados)');
  $fila = 2;
  while ($reg = $registro->fetch_assoc()) {
    $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . $fila, $reg['nombre'])
                ->setCellValue('B' . $fila, $reg['apellido_p'])
                ->setCellValue('C' . $fila, $reg['apellido_m'])
                ->setCellValue('D' . $fila, $reg['correo']);
    if ($reg['perfil'] == 'alumno') {
      $objPHPExcel->setActiveSheetIndex(0)
                  ->setCellValue('E' . $fila, 'Alumno')
                  ->setCellValue('F' . $fila, ucfirst($reg['campus_alumno']))
                  ->setCellValue('G' . $fila, $reg['generacion_alumno'])
                  ->setCellValue('H' . $fila, 'N/A')
                  ->setCellValue('I' . $fila, 'N/A')
                  ->setCellValue('J' . $fila, 'N/A')
                  ->setCellValue('K' . $fila, 'N/A');
    }
    if ($reg['perfil'] == 'egresado') {
      $objPHPExcel->setActiveSheetIndex(0)
                  ->setCellValue('E' . $fila, 'Egresado')
                  ->setCellValue('F' . $fila, ucfirst($reg['campus_egresado']))
                  ->setCellValue('G' . $fila, $reg['generacion_egresado'])
                  ->setCellValue('H' . $fila, $reg['celular'])
                  ->setCellValue('I' . $fila, $reg['empresa'])
                  ->setCellValue('J' . $fila, $reg['puesto']);
      if ($reg['egresado_participar'] == 'participar') {
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('K' . $fila, 'Sí');
      }
      else {
        $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('K' . $fila, 'No');
      }
    }
    if ($reg['perfil'] == 'otro') {
      $objPHPExcel->setActiveSheetIndex(0)
                  ->setCellValue('E' . $fila, 'Otro')
                  ->setCellValue('F' . $fila, 'N/A')
                  ->setCellValue('G' . $fila, 'N/A')
                  ->setCellValue('H' . $fila, 'N/A')
                  ->setCellValue('I' . $fila, 'N/A')
                  ->setCellValue('J' . $fila, 'N/A')
                  ->setCellValue('K' . $fila, 'N/A');
    }
    if ($reg['perfil'] == 'invitado') {
      $objPHPExcel->setActiveSheetIndex(0)
                  ->setCellValue('E' . $fila, 'Invitado especial')
                  ->setCellValue('F' . $fila, 'N/A')
                  ->setCellValue('G' . $fila, 'N/A')
                  ->setCellValue('H' . $fila, 'N/A')
                  ->setCellValue('I' . $fila, 'N/A')
                  ->setCellValue('J' . $fila, 'N/A')
                  ->setCellValue('K' . $fila, 'N/A');
    }
    $fila++;
  }
}

// Si no hay objetivo definido,salir:
else {
  exit;
}

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $nombre_archivo . '.xlsx"');
header('Cache-Control: max-age=0');

// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;

?>
