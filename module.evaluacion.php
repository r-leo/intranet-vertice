<?php /*
Archivo fuente de módulo
------------------------
* En este script hay acceso a todas las variables y funciones globales disponibles en intranet.php.
*/ ?>

<div class='responsive_hide'>
  <div class='navegador'>Estás en</div>
  <div class='tag clickable' onclick="window.location='intranet.php';">Inicio</div>
  <div class='navegador'><i class='fas fa-chevron-right inline'></i></div>
  <div class='tag'>Evaluación</div>
</div>

<?php if ($_SESSION['estatus'] == 'coordinador') {

  // Señal: X:
  if ($_POST['flag'] === 'X') {
    $sql = "QUERY";
    mensaje($sql, 'Mensaje de éxito');
  }

  // Redefinir el objeto de ajustes (esto debe estar al final de todas las señales para evitar sobreescritura):
  $ajustes=$conexion->query("SELECT * FROM ajustes")->fetch_assoc();

  $periodo_actual = $conexion->query("SELECT * FROM periodos WHERE activo='1'")->fetch_assoc();
  $periodo_siguiente_num = $periodo_actual['clave'] + 0;
  $periodo_siguiente = $conexion->query("SELECT * FROM periodos WHERE clave='$periodo_siguiente_num'")->fetch_assoc();
  ?>

  <div class='card'>
    <h2>Evaluación semestral</h2>
    <p>Seleccionar periodo a evaluar:</p>
    <?php $periodos = $conexion->query("SELECT * FROM periodos"); ?>
    <select class='selectmenu' id='selector_periodo'>
      <?php while ($periodo = $periodos->fetch_assoc()) { ?>
        <option value='<?php echo $periodo['clave']; ?>'><?php echo periodo_formateado(array($periodo['semestre'], $periodo['ano'])); ?> <?php if ($periodo['activo'] == '1') {echo '(activo)';} ?></option>
      <?php } ?>
    </select>
    &nbsp;&nbsp;<p class='boton_inline' onclick='generar_evaluacion();'>Generar evaluación</p>
  </div>

  <script>
    function generar_evaluacion() {
      periodo_destino = $('#selector_periodo').val()
      destino = '?d=evaluacion&periodo=' + periodo_destino;
      window.location.href = destino;
    }
  </script>

  <?php if (isset($_GET['periodo'])) { ?>

    <script>
      $('#selector_periodo').val(<?php echo $_GET['periodo']; ?>);
    </script>

    <div class='card'>
      <h2>Exportar evaluación</h2>
      <p>Esta evaluación puede exportarse en un archivo de Excel que no incluye los gráficos ni el resumen segmentado:</p>
      <a target='_blank' href='exportar_xlsx.php?target=evaluacion&periodo=<?php echo $_GET['periodo']; ?>'><p class='boton'>Exportar archivo de Excel</p></a>
      <div class='aclarador'></div>
    </div>

    <div class='card'>
      <h2>Resumen del periodo seleccionado vs. anterior</h2>

      <?php // Indicadores:
        $periodo_1 = strval($_GET['periodo']);
        $periodo_0 = strval(intval($periodo_1) - 1);
        $suma_actividades_1 = $conexion->query("SELECT COUNT(clave) AS suma_actividades_1 FROM actividades WHERE periodo='$periodo_1'")->fetch_assoc()['suma_actividades_1'] + 0;
        $suma_actividades_0 = $conexion->query("SELECT COUNT(clave) AS suma_actividades_0 FROM actividades WHERE periodo='$periodo_0'")->fetch_assoc()['suma_actividades_0'] + 0;
        $suma_actividades_v = round(100*($suma_actividades_1 - $suma_actividades_0)/$suma_actividades_0, 1);
        $suma_asistencias_1 = $conexion->query("SELECT SUM(asistencia) AS suma_asistencias_1 FROM actividades_usuarios INNER JOIN actividades ON actividades_usuarios.clave=actividades.clave WHERE actividades.periodo='$periodo_1'")->fetch_assoc()['suma_asistencias_1'] + 0;
        $suma_asistencias_0 = $conexion->query("SELECT SUM(asistencia) AS suma_asistencias_0 FROM actividades_usuarios INNER JOIN actividades ON actividades_usuarios.clave=actividades.clave WHERE actividades.periodo='$periodo_0'")->fetch_assoc()['suma_asistencias_0'] + 0;
        $suma_asistencias_v = round(100*($suma_asistencias_1 - $suma_asistencias_0)/$suma_asistencias_0, 1);
      ?>

      <table class='tabla'>
        <tr><th>Indicador</th><th>Periodo actual<sup>1</sup></th><th>Periodo anterior</th><th>Variación</th></tr>
        <tr>
          <td>Número total de actividades</td>
          <td class='center bold'><?php echo $suma_actividades_1; ?></td>
          <td class='center'><?php echo $suma_actividades_0; ?></td>
          <td class='center'><?php if ($suma_actividades_v >= 0) {echo "<span class='color-verde'><i class='fas fa-angle-up'></i>" . $suma_actividades_v . " %</span>";} else {echo "<span class='color-rojo'><i class='fas fa-angle-down'></i>" . $suma_actividades_v . " % </span>";} ?></td>
        </tr>
        <tr>
          <td>Número total de asistencias</td>
          <td class='center bold'><?php echo $suma_asistencias_1; ?></td>
          <td class='center'><?php echo $suma_asistencias_0; ?></td>
          <td class='center'><?php if ($suma_asistencias_v >= 0) {echo "<span class='color-verde'><i class='fas fa-angle-up'></i>" . $suma_asistencias_v . " %</span>";} else {echo "<span class='color-rojo'><i class='fas fa-angle-down'></i>" . $suma_asistencias_v . " % </span>";} ?></td>
        </tr>
        <tr>
          <td>Índice máximo de asistencia<sup>2</sup></td>
          <td class='center bold'><span id='indice_max_1'>Calculando...</span></td>
          <td class='center'><span id='indice_max_0'>Calculando...</span></td>
          <td class='center'><span id='indice_max_v'>Calculando...</span></td>
        </tr>
        <tr>
          <td>Índice mínimo de asistencia<sup>3</sup></td>
          <td class='center bold'><span id='indice_min_1'>Calculando...</span></td>
          <td class='center'><span id='indice_min_0'>Calculando...</span></td>
          <td class='center'><span id='indice_min_v'>Calculando...</span></td>
        </tr>
        <tr>
          <td>Índice promedio de asistencia<sup>4</sup></td>
          <td class='center bold'><span id='indice_avg_1'>Calculando...</span></td>
          <td class='center'><span id='indice_avg_0'>Calculando...</span></td>
          <td class='center'><span id='indice_avg_v'>Calculando...</span></td>
        </tr>
      </table>
      <div class='navegador'>
        <p><sup>1</sup>&nbsp;"Periodo actual" no se refiere al periodo activo en el sistema, sino al periodo seleccionado para la evaluación.</p>
        <p><sup>2, 3, 4</sup>&nbsp;El índice de asistencia se calcula por actividad e indica el porcentaje de alumnos inscritos que efectivamente aistieron. Un índice de 100 indica que todos los alumnos inscritos a la actividad asistieron, mientras que un índice de 50 indica que sólo la mitad de los alumnos inscritos a la actividad asistieron. El índice máximo (2) corresponde a la actividad con el mayor índice de asistencia, mientras que el índice mínimo (3) corresponde a la actividad con el menor índice de asistencia. El índice promedio (4) es el promedio de todos los índices y es un indicador conveniente para evaluar la asistencia de los alumnos a las actividades. Los índices mayores a 100 se truncan a 100 para evitar el sesgo introducido por las actividades con sobrecupo.</p>
      </div>
    </div>

    <div class='card'>
      <h2>Resumen segmentado</h2>

      <h3>Actividades totales por mes</h3>

      <?php
        $mes_min = $conexion->query("SELECT MIN(MONTH(fecha_inicio)) AS mes_min FROM actividades WHERE periodo='$periodo_1'")->fetch_assoc()['mes_min'];
        $mes_max = $conexion->query("SELECT MAX(MONTH(fecha_inicio)) AS mes_max FROM actividades WHERE periodo='$periodo_1'")->fetch_assoc()['mes_max'];
        $i = $mes_min;
        $actividades_mensuales = array();
        $meses = array();
        while ($i <= $mes_max) {
          $actividades_i = $conexion->query("SELECT COUNT(clave) AS actividades_i FROM actividades WHERE periodo='$periodo_1' AND MONTH(fecha_inicio)='$i'")->fetch_assoc()['actividades_i'];
          array_push($actividades_mensuales, $actividades_i);
          array_push($meses, ucwords(mes($i)));
          $i++;
        }
      ?>

      <div class='grafico'>
        <canvas id="grafico_actividades_mensuales"></canvas>
      </div>

      <script>
        var graficoActividadesMensuales = new Chart($('#grafico_actividades_mensuales'), {
          type: 'bar',
          data: {
            labels: <?php echo json_encode($meses); ?>,
            datasets: [{
              label: 'Actividades por mes',
              data: <?php echo json_encode($actividades_mensuales); ?>,
              backgroundColor: <?php echo json_encode(array_colores(count($actividades_mensuales))); ?>,
              borderColor: '#ffffff',
              borderWidth: 1
            }]
          },
          options: {
            scales: {
              yAxes: [{
                ticks: {
                  beginAtZero: true
                }
              }]
            },
            legend: {
              display: false
            }
          }
        });
      </script>

      <h3>Actividades extracurriculares por tipo</h3>

      <?php
        $rubros = array();
        $numero_actividades = array();
        $tipos = $conexion->query("SELECT id, nombre FROM requisitos WHERE tipo='2'");
        while ($tipo = $tipos->fetch_assoc()) {
          $actividades = $conexion->query("SELECT COUNT(clave) AS numero_actividades FROM actividades WHERE tipo_1='{$tipo['id']}' AND periodo='$periodo_1'")->fetch_assoc();
          if ($actividades['numero_actividades'] > 0) {
            array_push($rubros, $tipo['nombre']);
            array_push($numero_actividades, $actividades['numero_actividades']); ?>
          <?php }
        }
      ?>

      <table class='tabla'>
        <tr><th>Tipo de actividad</th><th>Número de actividades</th><th>Porcentaje</th></tr>
        <?php for ($i = 0; $i < count($numero_actividades); $i++) { ?>
          <tr>
            <td><?php echo $rubros[$i]; ?></td>
            <td><?php echo $numero_actividades[$i]; ?></td>
            <td><?php echo round(100 * $numero_actividades[$i] / array_sum($numero_actividades), 1); ?> %</td>
          </tr>
        <?php } ?>
        <tr class='bold'><td>Total</td><td><?php echo array_sum($numero_actividades); ?></td><td>100.0 %</td></tr>
      </table>

      <div class='grafico' style='width:80%;'>
        <canvas id="grafico_actividades_por_tipo"></canvas>
      </div>

      <script>
        var graficoActividadesPorTipo = new Chart($('#grafico_actividades_por_tipo'), {
          type: 'pie',
          data: {
            labels: <?php echo json_encode($rubros); ?>,
            datasets: [{
              label: 'Actividades por tipo',
              data: <?php echo json_encode($numero_actividades); ?>,
              backgroundColor: <?php echo json_encode(array_colores(count($numero_actividades))); ?>,
              borderColor: '#ffffff',
              borderWidth: 1
            }]
          },
          options: {
            legend: {
              display: true,
              position: 'left'
            }
          }
        });
      </script>

    </div>

    <div class='card'>
      <h2>Evaluación detallada</h2>
      <p>Las actividades se encuentran ordenadas por fecha, de más antigua a más reciente.</p>
      <table class='tabla'>
        <tr><th>Actividad / fecha</th><th>Inscripciones</th><th>Asistencias</th><th>Índice de asistencia</th></tr>
        <?php
          // Índices del periodo actual:
          $actividades = $conexion->query("SELECT * FROM actividades WHERE periodo='$periodo_1' ORDER BY fecha_inicio");
          $indice_max = 0;
          $indice_min = 100;
          $indice_avg_num = 0;
          $indice_avg_den = 0;
          while ($actividad = $actividades->fetch_assoc()) {
            $actividad_valores = $conexion->query("SELECT COUNT(id) AS actividad_inscripciones, SUM(asistencia) AS actividad_asistencias FROM actividades_usuarios WHERE clave='{$actividad['clave']}'")->fetch_assoc();
            $indice_asistencia = round(100 * $actividad_valores['actividad_asistencias'] / $actividad_valores['actividad_inscripciones'], 1);
            if ($indice_asistencia > $indice_max) {
              $indice_max = $indice_asistencia;
            }
            if ($indice_asistencia < $indice_min) {
              $indice_min = $indice_asistencia;
            }
            $indice_avg_num = $indice_avg_num + $indice_asistencia;
            $indice_avg_den = $indice_avg_den + 1;
          ?>
            <tr>
              <td><?php echo $actividad['nombre']; ?><br><span class='navegador'><?php echo fecha_formateada($actividad['fecha_inicio']); ?></span></td>
              <td class='center'><?php echo $actividad_valores['actividad_inscripciones']; ?></td>
              <td class='center'><?php echo $actividad_valores['actividad_asistencias']; ?></td>
              <td>
                <i class='<?php if ($indice_asistencia <= 50) {echo 'fas fa-times-circle fa-fw color-rojo';} else {echo 'fas fa-check-circle fa-fw color-verde';} ?>'></i><?php echo $indice_asistencia; ?> %</td>
            </tr>
          <?php }
          $indice_avg = round($indice_avg_num / $indice_avg_den, 1);
          // Índices del periodo anterior:
          $actividades_0 = $conexion->query("SELECT * FROM actividades WHERE periodo='$periodo_0'");
          $indice_max_0 = 0;
          $indice_min_0 = 0;
          while ($actividad_0 = $actividades_0->fetch_assoc()) {
            $actividad_valores_0 = $conexion->query("SELECT COUNT(id) AS actividad_inscripciones, SUM(asistencia) AS actividad_asistencias FROM actividades_usuarios WHERE clave='{$actividad_0['clave']}'")->fetch_assoc();
            $indice_asistencia_0 = round(100 * $actividad_valores_0['actividad_asistencias'] / $actividad_valores_0['actividad_inscripciones'], 1);
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
          // Variaciones:
          $variacion_max = round(100 * ($indice_max - $indice_max_0) / $indice_max_0, 1);
          $variacion_min = round(100 * ($indice_min - $indice_min_0) / $indice_min_0, 1);
          $variacion_avg = round(100 * ($indice_avg - $indice_avg_0) / $indice_avg_0, 1);
          if ($variacion_max >= 0) {
            $variacion_max_html = "<span class='color-verde'><i class='fas fa-angle-up fa-fw'></i>" . $variacion_max . " %</span>";
          }
          else {
            $variacion_max_html = "<span class='color-rojo'><i class='fas fa-angle-down fa-fw'></i>" . $variacion_max . " %</span>";
          }
          if ($variacion_min >= 0) {
            $variacion_min_html = "<span class='color-verde'><i class='fas fa-angle-up fa-fw'></i>" . $variacion_min . " %</span>";
          }
          else {
            $variacion_min_html = "<span class='color-rojo'><i class='fas fa-angle-down fa-fw'></i>" . $variacion_min . " %</span>";
          }
          if ($variacion_avg >= 0) {
            $variacion_avg_html = "<span class='color-verde'><i class='fas fa-angle-up fa-fw'></i>" . $variacion_avg . " %</span>";
          }
          else {
            $variacion_avg_html = "<span class='color-rojo'><i class='fas fa-angle-down fa-fw'></i>" . $variacion_avg . " %</span>";
          }
          ?>
      </table>
      <p></p>

      <script>
        // Actualizar los índices máximos:
        $('#indice_max_1').html("<?php echo $indice_max; ?> %");
        $('#indice_max_0').html("<?php echo $indice_max_0; ?> %");
        $('#indice_max_v').html("<?php echo $variacion_max_html; ?>");
        // Actualizar los índices mínimos:
        $('#indice_min_1').html("<?php echo $indice_min; ?> %");
        $('#indice_min_0').html("<?php echo $indice_min_0; ?> %");
        $('#indice_min_v').html("<?php echo $variacion_min_html; ?>");
        // Actualizar los índices promedio:
        $('#indice_avg_1').html("<?php echo $indice_avg; ?> %");
        $('#indice_avg_0').html("<?php echo $indice_avg_0; ?> %");
        $('#indice_avg_v').html("<?php echo $variacion_avg_html; ?>");
      </script>

    </div>

  <?php }
  else { ?>
    <script>
      $('#selector_periodo').val(<?php echo $periodo_actual['clave']; ?>);
    </script>
  <?php } ?>

<?php } else { ?>
  <div class='card-important'>
    <h2>Acceso prohibido</h2>
    <p>No tienes permisos para acceder a esta sección.</p>
    <p>Si crees que estás viendo este mensaje por error por favor contacta al administrador del sistema.</p>
  </div>
<?php } ?>
