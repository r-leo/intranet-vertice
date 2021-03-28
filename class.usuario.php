<?php

include_once 'snippets.php';

// Motor de información de usuario:
class Usuario {

  // Declaración de propiedades de clase:
  private $database;
  public $informacion = array();
  public $periodos = array();
  public $semestres = array();
  public $requisitos_no_periodicos = array();
  //public $rubros = array();

  // Constructor de la clase:
  function __construct($id, $database) {
    $this->database = $database;

    // Cargar componentes de propiedad: informacion
    $sql = $this->database->query("SELECT * FROM usuarios WHERE id='$id'")->fetch_assoc();
    $this->informacion['id'] = $sql['id'];
    $this->informacion['nombre'] = $sql['nombre'];
    $this->informacion['generacion'] = $sql['generacion'];
    $this->informacion['plan'] = $this->database->query("SELECT plan FROM generaciones WHERE generacion='{$this->informacion['generacion']}'")->fetch_assoc()['plan'];
    $this->informacion['periodo_activo'] = intval($this->database->query("SELECT clave FROM periodos WHERE activo='1'")->fetch_assoc()['clave']);
    $this->informacion['condicionamientos'] = 0;
    $this->informacion['bajas'] = 0;
    $this->informacion['estatus'] = 'indefinido';

    // Cargar componentes de propiedad: periodos
    $periodos_usuario = array();
    $periodo_inicial_id = $this->database->query("SELECT periodo_inicial FROM generaciones WHERE generacion='{$this->informacion['generacion']}'")->fetch_assoc()['periodo_inicial'];
    $duracion = $this->database->query("SELECT duracion FROM planes WHERE id='{$this->informacion['plan']}'")->fetch_assoc()['duracion'];
    for ($i = 0; $i < $duracion; $i++) {
      $periodos_usuario[] = $periodo_inicial_id + $i;
    }
    $this->periodos = $periodos_usuario;

    // Cargar componentes de propiedad: semestres
    $resultado = array();
    $requisitos_periodicos = generar_array($this->database->query("SELECT * FROM requisitos WHERE plan='{$this->informacion['plan']}' AND tipo='2'"));
    for ($i = 0; $i < count($this->periodos); $i++) {
      $resultado[$i] = array();

      // periodo_id y periodo_nombre:
      $resultado[$i]['periodo_id'] = $this->periodos[$i];
      $periodo_sql = $this->database->query("SELECT semestre, ano FROM periodos WHERE clave='{$this->periodos[$i]}'")->fetch_assoc();
      $resultado[$i]['periodo_nombre'] = periodo_formateado(array($periodo_sql['semestre'], $periodo_sql['ano']));
      $resultado[$i]['puntos_requeridos'] = $this->database->query("SELECT puntos_minimos FROM planes WHERE id='{$this->informacion['plan']}'")->fetch_assoc()['puntos_minimos'];
      $resultado[$i]['puntos_completados'] = 0;
      $resultado[$i]['estatus'] = 'acreditado';

      $resultado[$i]['requisitos_periodicos'] = array();

      $resultado[$i]['asistencias_totales'] = 0;
      $resultado[$i]['puntos_totales'] = 0;

      for ($j = 0; $j < count($requisitos_periodicos); $j++) {
        $resultado[$i]['requisitos_periodicos'][$j]['nombre'] = $requisitos_periodicos[$j]['nombre'];
        $resultado[$i]['requisitos_periodicos'][$j]['id'] = $requisitos_periodicos[$j]['id'];
        $resultado[$i]['requisitos_periodicos'][$j]['puntos_requeridos'] = $requisitos_periodicos[$j]['valor'];
        $resultado[$i]['requisitos_periodicos'][$j]['asistencias_requeridas'] = $requisitos_periodicos[$j]['asistencias'];
        $resultado[$i]['requisitos_periodicos'][$j]['puntos_completados'] = $this->database->query("SELECT SUM(actividades_usuarios.asistencia*actividades.puntos) AS puntos_completados
                                                                                                    FROM actividades_usuarios
                                                                                                    INNER JOIN actividades ON actividades.clave=actividades_usuarios.clave
                                                                                                    WHERE actividades_usuarios.id='{$this->informacion['id']}'
                                                                                                    AND actividades.periodo='{$this->periodos[$i]}'
                                                                                                    AND (actividades.tipo_1='{$requisitos_periodicos[$j]['id']}'
                                                                                                      OR actividades.tipo_2='{$requisitos_periodicos[$j]['id']}'
                                                                                                      OR actividades.tipo_3='{$requisitos_periodicos[$j]['id']}'
                                                                                                      OR actividades.tipo_4='{$requisitos_periodicos[$j]['id']}')")->fetch_assoc()['puntos_completados'] + 0;
        $resultado[$i]['requisitos_periodicos'][$j]['asistencias_registradas'] = $this->database->query("SELECT SUM(actividades_usuarios.asistencia) AS asistencias_registradas
                                                                                                         FROM actividades_usuarios
                                                                                                         INNER JOIN actividades ON actividades.clave=actividades_usuarios.clave
                                                                                                         WHERE actividades_usuarios.id='{$this->informacion['id']}'
                                                                                                         AND actividades.periodo='{$this->periodos[$i]}'
                                                                                                         AND (actividades.tipo_1='{$requisitos_periodicos[$j]['id']}'
                                                                                                           OR actividades.tipo_2='{$requisitos_periodicos[$j]['id']}'
                                                                                                           OR actividades.tipo_3='{$requisitos_periodicos[$j]['id']}'
                                                                                                           OR actividades.tipo_4='{$requisitos_periodicos[$j]['id']}')")->fetch_assoc()['asistencias_registradas'] + 0;
        // Determinar los puntos y asistencias totales:
        $resultado[$i]['asistencias_totales'] = $resultado[$i]['asistencias_totales'] + $resultado[$i]['requisitos_periodicos'][$j]['asistencias_registradas'];
        $resultado[$i]['puntos_totales'] = $resultado[$i]['puntos_totales'] + $resultado[$i]['requisitos_periodicos'][$j]['puntos_completados'];
        // Determinar estatus:
        $resultado[$i]['requisitos_periodicos'][$j]['estatus'] = 'desconocido';
        $resultado[$i]['puntos_completados'] = $resultado[$i]['puntos_completados'] + $resultado[$i]['requisitos_periodicos'][$j]['puntos_completados'];
        if ($resultado[$i]['requisitos_periodicos'][$j]['puntos_completados'] < $resultado[$i]['requisitos_periodicos'][$j]['puntos_requeridos'] || $resultado[$i]['requisitos_periodicos'][$j]['asistencias_registradas'] < $resultado[$i]['requisitos_periodicos'][$j]['asistencias_requeridas']) {
          $resultado[$i]['requisitos_periodicos'][$j]['estatus'] = 'no_acreditado';
          $resultado[$i]['estatus'] = 'no_acreditado';
        }
        else {
          $resultado[$i]['requisitos_periodicos'][$j]['estatus'] = 'acreditado';
        }
      }
      // Determinar estatus del alumno:
      if ($resultado[$i]['puntos_totales'] <= 5) {
        $resultado[$i]['estatus_alumno'] = 'baja';
      }
      elseif ($resultado[$i]['puntos_totales'] <= 7) {
        $resultado[$i]['estatus_alumno'] = 'condicionado';
      }
      else {
        $resultado[$i]['estatus_alumno'] = 'activo';
      }
      // Recuperar estatus definidos del alumno:
      $estatus_definido = $this->database->query("SELECT estatus FROM estatus WHERE periodo='{$this->periodos[$i]}' AND id='{$this->informacion['id']}'");
      if ($estatus_definido->num_rows > 0) {
        $resultado[$i]['estatus_definido'] = $estatus_definido->fetch_assoc()['estatus'];
        $resultado[$i]['estatus_final'] = $resultado[$i]['estatus_definido'];
      }
      else {
        $resultado[$i]['estatus_definido'] = 'nulo';
        $resultado[$i]['estatus_final'] = $resultado[$i]['estatus_alumno'];
      }
      // Sumar números de condicionamientos y bajas:
      if ($resultado[$i]['periodo_id'] < $this->informacion['periodo_activo']) {
        if ($resultado[$i]['estatus_final'] == 'condicionado') {
          $this->informacion['condicionamientos'] = $this->informacion['condicionamientos'] + 1;
        }
        if ($resultado[$i]['estatus_final'] == 'baja') {
          $this->informacion['bajas'] = $this->informacion['bajas'] + 1;
        }
      }
    }
    $this->semestres = $resultado;

    if ($this->informacion['bajas'] > 0) {
      $this->informacion['estatus'] = 'baja';
    }
    elseif ($this->informacion['condicionamientos'] >= 2) {
      $this->informacion['estatus'] = 'baja';
    }
    elseif ($this->informacion['condicionamientos'] > 0) {
      $this->informacion['estatus'] = 'condicionado';
    }
    else {
      $this->informacion['estatus'] = 'activo';
    }
    $this->database->query("UPDATE usuarios SET estatus_rapido='{$this->informacion['estatus']}' WHERE id='{$this->informacion['id']}'");

    // Cargar componentes de propiedad: requisitos_no_periodicos
    $resultado = array();
    $requisitos_no_periodicos = generar_array($this->database->query("SELECT * FROM requisitos WHERE plan='{$this->informacion['plan']}' AND tipo='1'"));
    for ($i = 0; $i < count($requisitos_no_periodicos); $i++) {
      $resultado[$i] = array();
      $resultado[$i]['nombre'] = $requisitos_no_periodicos[$i]['nombre'];
      $resultado[$i]['asistencias_requeridas'] = $requisitos_no_periodicos[$i]['asistencias'];
      $resultado[$i]['asistencias_registradas'] = $this->database->query("SELECT SUM(actividades_usuarios.asistencia) AS asistencias_registradas
                                                                          FROM actividades_usuarios
                                                                          INNER JOIN actividades ON actividades.clave=actividades_usuarios.clave
                                                                          WHERE actividades_usuarios.id='{$this->informacion['id']}'
                                                                          AND (actividades.tipo_1='{$requisitos_no_periodicos[$i]['id']}'
                                                                            OR actividades.tipo_2='{$requisitos_no_periodicos[$i]['id']}'
                                                                            OR actividades.tipo_3='{$requisitos_no_periodicos[$i]['id']}'
                                                                            OR actividades.tipo_4='{$requisitos_no_periodicos[$i]['id']}')")->fetch_assoc()['asistencias_registradas'] + 0;
      $resultado[$i]['fechas_completadas'] = generar_array($this->database->query("SELECT actividades.fecha_inicio AS fecha_inicio, actividades.nombre AS nombre_actividad
                                                                                   FROM actividades
                                                                                   INNER JOIN actividades_usuarios ON actividades_usuarios.clave=actividades.clave
                                                                                   WHERE actividades_usuarios.id='{$this->informacion['id']}'
                                                                                   AND actividades_usuarios.asistencia='1'
                                                                                   AND (actividades.tipo_1='{$requisitos_no_periodicos[$i]['id']}'
                                                                                     OR actividades.tipo_2='{$requisitos_no_periodicos[$i]['id']}'
                                                                                     OR actividades.tipo_3='{$requisitos_no_periodicos[$i]['id']}'
                                                                                     OR actividades.tipo_4='{$requisitos_no_periodicos[$i]['id']}')"));
      if ($resultado[$i]['asistencias_registradas'] >= $resultado[$i]['asistencias_requeridas']) {
        $resultado[$i]['estatus'] = 'acreditado';
      }
      else {
        $resultado[$i]['estatus'] = 'no_acreditado';
      }
    }

    $this->requisitos_no_periodicos = $resultado;
    // ^ Fin del constructor de la clase
  }
}

?>
