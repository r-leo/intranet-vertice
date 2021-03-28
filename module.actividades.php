<?php /*
Archivo fuente de módulo
------------------------
* En este script hay acceso a todas las variables y funciones globales disponibles en intranet.php.
*/ ?>

<?php
  // Bloquear acceso a egresados y bajas:
  if ($_SESSION['estatus'] == 'egresado' || $_SESSION['estatus'] == 'baja') {
    redireccionar('intranet.php');
    exit;
  }
?>

<div class='responsive_hide'>
  <div class='navegador'>Estás en</div>
  <div class='tag clickable' onclick="window.location='intranet.php';">Inicio</div>
  <div class='navegador'><i class='fas fa-chevron-right inline'></i></div>
  <div class='tag'>Actividades</div>
</div>

<?php
$planes = $conexion->query("SELECT * FROM planes");
$periodos = $conexion->query("SELECT * FROM periodos");
$periodo_activo = $conexion->query("SELECT * FROM periodos WHERE activo='1'")->fetch_assoc();

// Mostrar mensaje de los coordinadores:
if (($_SESSION['estatus'] <> 'coordinador') && $ajustes['mensaje_activo'] === '1') { ?>
  <div class='card-important responsive_hide'>
    <h2><?php echo $ajustes['mensaje_titulo']; ?></h2>
    <p><?php echo $ajustes['mensaje']; ?></p>
  </div>
<?php }

// Señal: actualizar_periodo:
if ($_POST['flag'] === 'actualizar_periodo') {
  $periodo_actividades = $_POST['periodo'];
}
else {
  $periodo_actividades = $conexion->query("SELECT * FROM periodos WHERE activo=1")->fetch_assoc()['clave'];
}

// Señal: inscribir_actividad:
if ($_POST['flag'] === 'inscribir_actividad') {
  // Verificar que haya cupo:
  $cupo = $conexion->query("SELECT cupo FROM actividades WHERE clave='{$_POST['clave']}'")->fetch_assoc()['cupo'];
  $conexion->query("LOCK TABLES actividades_usuarios WRITE");
  $inscritos = $conexion->query("SELECT * FROM actividades_usuarios WHERE clave='{$_POST['clave']}'");
  $inscrito = $conexion->query("SELECT * FROM actividades_usuarios WHERE clave='{$_POST['clave']}' AND id='{$_SESSION['id']}'")->num_rows;
  if ($cupo - $inscritos > 0) {
    // Verificar que no esté inscrito ya:
    if ($inscrito > 0) { ?>
      <div class='card dialog'>
        <h2>La actividad no se pudo inscribir</h2>
        <p>No puedes inscribirte dos veces a la misma actividad.</p>
      </div>
    <?php }
    else {
      // Inscribir la actividad:
      $sql = "INSERT INTO actividades_usuarios (id, clave, asistencia) VALUES ('{$_SESSION['id']}', '{$_POST['clave']}', '0')";
      mensaje($sql, 'Actividad inscrita correctamente');
    }
  }
  else {
    // Informar que no hay cupo: ?>
    <div class='card dialog'>
      <h2>La actividad no se pudo inscribir</h2>
      <p>Parace que el cupo está lleno.</p>
    </div>
  <?php }
  $conexion->query("UNLOCK TABLES");
}

// Verificar si el usuario es coordinador:
if ($_SESSION['estatus'] === 'coordinador') {

  // Señal: agregar_actividad:
  if ($_POST['flag'] === 'agregar_actividad') {
    if ($_POST['lista_cruzada'] === 'lista_cruzada') {
      $sql = "INSERT INTO actividades (periodo, puntos, cupo, nombre, fecha_inicio, fecha_fin, inscripcion_general, comites, inscripcion_comites, costo, lugar, comentarios, tipo_1, tipo_2, tipo_3, tipo_4) VALUES ('{$_POST['periodo']}', '{$_POST['puntos']}', '{$_POST['cupo']}', '{$_POST['nombre']}', '{$_POST['fecha_inicio']}', '{$_POST['fecha_fin']}', '{$_POST['inscripcion_general']}', '{$_POST['inscripcion_previa']}', '{$_POST['inscripcion_comites']}', '{$_POST['costo']}', '{$_POST['lugar']}', '{$_POST['comentarios']}', '{$_POST['tipo1']}', '{$_POST['tipo2']}', '{$_POST['tipo3']}', '{$_POST['tipo4']}')";
    }
    else {
      $sql = "INSERT INTO actividades (periodo, puntos, cupo, nombre, fecha_inicio, fecha_fin, inscripcion_general, comites, inscripcion_comites, costo, lugar, comentarios, tipo_1, tipo_2, tipo_3, tipo_4) VALUES ('{$_POST['periodo']}', '{$_POST['puntos']}', '{$_POST['cupo']}', '{$_POST['nombre']}', '{$_POST['fecha_inicio']}', '{$_POST['fecha_fin']}', '{$_POST['inscripcion_general']}', '{$_POST['inscripcion_previa']}', '{$_POST['inscripcion_comites']}', '{$_POST['costo']}', '{$_POST['lugar']}', '{$_POST['comentarios']}', '{$_POST['tipo']}', '0', '0', '0')";
    }
    mensaje($sql, 'Actividad creada correctamente.');
  }

  // Señal: actualizar_actividad:
  if ($_POST['flag'] === 'actualizar_actividad') {
    $sql = "UPDATE actividades SET puntos='{$_POST['puntos']}', cupo='{$_POST['cupo']}', nombre='{$_POST['nombre']}', fecha_inicio='{$_POST['fecha_inicio']}', fecha_fin='{$_POST['fecha_fin']}', inscripcion_general='{$_POST['inscripcion_general']}', comites='{$_POST['inscripcion_previa']}', inscripcion_comites='{$_POST['inscripcion_comites']}', costo='{$_POST['costo']}', lugar='{$_POST['lugar']}', comentarios='{$_POST['comentarios']}' WHERE clave='{$_POST['clave']}'";
    mensaje($sql, 'Actividad guardada correctamente.');
  }

  // Señal: eliminar_actividad:
  if ($_POST['flag'] === 'eliminar_actividad') {
    $sql = "DELETE FROM actividades WHERE clave={$_POST['clave']}; DELETE FROM actividades_usuarios WHERE clave={$_POST['clave']}";
    mensaje($sql, 'Actividad eliminada.');
  }

  // Formulario para agregar actividades: ?>
  <div class='card'>
    <h2>Área de coordinadores</h2>
    <div class='accordion'>
      <h3>Agregar nueva actividad</h3>
      <div>
        <form method='POST' action='?d=actividades'>
          <label for='periodo'>Periodo</label>
          <select class='selectmenu' name='periodo'>
            <?php while ($periodo=$periodos->fetch_assoc()) { ?>
              <option value="<?php echo $periodo['clave']; ?>" <?php if ($periodo['activo'] == 1) {echo 'selected';} ?>>
                <?php if ($periodo['semestre'] === '1') {echo 'Ene-jun ';}
                else {echo 'Ago-dic ';}
                echo $periodo['ano'];
                if ($periodo['activo'] == 1) {echo ' (activo)';} ?>
              </option>
            <?php } ?>
          </select>
          <br>

          <fieldset>
            <legend>Asignación de plan curricular</legend>
            <label for='lista_cruzada'>Esta actividad tiene lista cruzada</label>
            <input type='checkbox' name='lista_cruzada' value='lista_cruzada' id='lista_cruzada'>&nbsp;
            <span class='hint' title='Una actividad tiene lista cruzada cuando está disponible para miembros que pertenecen a distintos programas curriculares.'>¿Qué es esto?</span>
            <br><br>

            <script>
              $('#lista_cruzada').change(function() {
                if (this.checked) {
                  $('#lista_cruzada_false').hide();
                  $('#lista_cruzada_true').show();
                }
                else {
                  $('#lista_cruzada_true').hide();
                  $('#lista_cruzada_false').show();
                }
              });
            </script>

            <div id='lista_cruzada_false'>
              <label for='plan'>Plan</label>
              <select class='selectmenu' name='plan' id='plan'>
                <option disabled selected value='0'>Seleccionar...</value>
                <?php foreach ($planes as $plan) { ?>
                  <option value="<?php echo $plan['id']; ?>">
                    <?php echo $plan['nombre']; ?>
                  </option>
                <?php } ?>
              </select>
              <br><br>
              <label for='tipo'>Tipo</label>
              <select class='selectmenu' name='tipo' disabled='true' id='tipo'>
                <option disabled selected value='0'>Seleccionar...</value>
                <optgroup label='Requisitos periódicos'>
                </optgroup>
                <optgroup label='Requisitos no periódicos'>
                </optgroup>
              </select>

              <script>
                $('#plan').on('selectmenuchange', function(event, ui){
                  if ( $('#plan').val() == 0 ) {
                    $('#tipo option:eq(0)').attr('selected', true);
                    $('#tipo option:gt(0)').remove();
                    $('#tipo').selectmenu('refresh');
                    $('#tipo').selectmenu('disable');
                  }
                  else {
                    $('#tipo').selectmenu('enable');
                    $('#tipo option:gt(0)').remove();
                    $('#tipo option:eq(0)').attr('selected', true);
                    $.ajax({
                      url: 'consultar_tipos.php',
                      data: {plan: $('#plan').val()}
                    }).done( function(data) {
                      var resultado = JSON.parse(data)
                      for ( var i = 0; i < resultado.length; i++ ) {
                        if ( resultado[i]['tipo'] == 1 ) {
                          $('#tipo optgroup:last').append("<option value='" + resultado[i]['id'] + "'>" + resultado[i]['nombre'] + "</option>");
                        }
                        else {
                          $('#tipo optgroup:first').append("<option value='" + resultado[i]['id'] + "'>" + resultado[i]['nombre'] + "</option>");
                        }
                      }
                      $('#tipo').selectmenu('refresh');
                    });
                  }
                });
              </script>
            </div>

            <div id='lista_cruzada_true' style='display:none;'>
              <!-- Plan 1 -->
              <label for='plan1'>Para el plan</label>
              <select class='selectmenu' name='plan1' id='plan1'>
                <option disabled selected value='0'>Seleccionar...</option>
                <?php foreach ($planes as $plan) { ?>
                  <option value="<?php echo $plan['id']; ?>">
                    <?php echo $plan['nombre']; ?>
                  </option>
                <?php } ?>
              </select>
              <label for='tipo1'>el tipo es</label>
              <select class='selectmenu' name='tipo1' disabled='true' id='tipo1'>
                <option disabled selected value='0'>Seleccionar...</option>
                <optgroup label='Requisitos periódicos'>
                </optgroup>
                <optgroup label='Requisitos no periódicos'>
                </optgroup>
              </select>
              <br><br>

              <!-- Plan 2 -->
              <label for='plan2'>Para el plan</label>
              <select class='selectmenu' name='plan2' id='plan2'>
                <option disabled selected value='0'>Seleccionar...</option>
                <?php foreach ($planes as $plan) { ?>
                  <option value="<?php echo $plan['id']; ?>">
                    <?php echo $plan['nombre']; ?>
                  </option>
                <?php } ?>
              </select>
              <label for='tipo2'>el tipo es</label>
              <select class='selectmenu' name='tipo2' disabled='true' id='tipo2'>
                <option disabled selected value='0'>Seleccionar...</option>
                <optgroup label='Requisitos periódicos'>
                </optgroup>
                <optgroup label='Requisitos no periódicos'>
                </optgroup>
              </select>
              <br><br>

              <!-- Plan 3 -->
              <label for='plan3'>Para el plan</label>
              <select class='selectmenu' name='plan3' id='plan3'>
                <option selected value='0'>(Ninguno)</option>
                <?php foreach ($planes as $plan) { ?>
                  <option value="<?php echo $plan['id']; ?>">
                    <?php echo $plan['nombre']; ?>
                  </option>
                <?php } ?>
              </select>
              <label for='tipo3'>el tipo es</label>
              <select class='selectmenu' name='tipo3' id='tipo3'>
                <option selected value='0'>(Ninguno)</option>
                <optgroup label='Requisitos periódicos'>
                </optgroup>
                <optgroup label='Requisitos no periódicos'>
                </optgroup>
              </select>
              <br><br>

              <!-- Plan 4 -->
              <label for='plan4'>Para el plan</label>
              <select class='selectmenu' name='plan4' id='plan4'>
                <option selected value='0'>(Ninguno)</option>
                <?php foreach ($planes as $plan) { ?>
                  <option value="<?php echo $plan['id']; ?>">
                    <?php echo $plan['nombre']; ?>
                  </option>
                <?php } ?>
              </select>
              <label for='tipo4'>el tipo es</label>
              <select class='selectmenu' name='tipo4' id='tipo4'>
                <option selected value='0'>(Ninguno)</option>
                <optgroup label='Requisitos periódicos'>
                </optgroup>
                <optgroup label='Requisitos no periódicos'>
                </optgroup>
              </select>
              <br><br>

              <!-- Script para el plan 1 -->
              <script>
                $('#plan1').on('selectmenuchange', function(event, ui){
                  if ( $('#plan1').val() == 0 ) {
                    $('#tipo1 option:eq(0)').attr('selected', true);
                    $('#tipo1 option:gt(0)').remove();
                    $('#tipo1').selectmenu('refresh');
                    $('#tipo1').selectmenu('disable');
                  }
                  else {
                    $('#tipo1').selectmenu('enable');
                    $('#tipo1 option:gt(0)').remove();
                    $('#tipo1 option:eq(0)').attr('selected', true);
                    $.ajax({
                      url: 'consultar_tipos.php',
                      data: {plan: $('#plan1').val()}
                    }).done( function(data) {
                      var resultado = JSON.parse(data)
                      for ( var i = 0; i < resultado.length; i++ ) {
                        if ( resultado[i]['tipo'] == 1 ) {
                          $('#tipo1 optgroup:last').append("<option value='" + resultado[i]['id'] + "'>" + resultado[i]['nombre'] + "</option>");
                        }
                        else {
                          $('#tipo1 optgroup:first').append("<option value='" + resultado[i]['id'] + "'>" + resultado[i]['nombre'] + "</option>");
                        }
                      }
                      $('#tipo1').selectmenu('refresh');
                    });
                  }
                });
              </script>

              <!-- Script para el plan 2 -->
              <script>
                $('#plan2').on('selectmenuchange', function(event, ui){
                  if ( $('#plan2').val() == 0 ) {
                    $('#tipo2 option:eq(0)').attr('selected', true);
                    $('#tipo2 option:gt(0)').remove();
                    $('#tipo2').selectmenu('refresh');
                    $('#tipo2').selectmenu('disable');
                  }
                  else {
                    $('#tipo2').selectmenu('enable');
                    $('#tipo2 option:gt(0)').remove();
                    $('#tipo2 option:eq(0)').attr('selected', true);
                    $.ajax({
                      url: 'consultar_tipos.php',
                      data: {plan: $('#plan2').val()}
                    }).done( function(data) {
                      var resultado = JSON.parse(data)
                      for ( var i = 0; i < resultado.length; i++ ) {
                        if ( resultado[i]['tipo'] == 1 ) {
                          $('#tipo2 optgroup:last').append("<option value='" + resultado[i]['id'] + "'>" + resultado[i]['nombre'] + "</option>");
                        }
                        else {
                          $('#tipo2 optgroup:first').append("<option value='" + resultado[i]['id'] + "'>" + resultado[i]['nombre'] + "</option>");
                        }
                      }
                      $('#tipo2').selectmenu('refresh');
                    });
                  }
                });
              </script>

              <!-- Script para el plan 3 -->
              <script>
                $('#plan3').on('selectmenuchange', function(event, ui){
                  if ( $('#plan3').val() == 0 ) {
                    $('#tipo3 option:eq(0)').attr('selected', true);
                    $('#tipo3 option:gt(0)').remove();
                    $('#tipo3').selectmenu('refresh');
                    $('#tipo3').selectmenu('disable');
                  }
                  else {
                    $('#tipo3').selectmenu('enable');
                    $('#tipo3 option:gt(0)').remove();
                    $('#tipo3 option:eq(0)').attr('selected', true);
                    $.ajax({
                      url: 'consultar_tipos.php',
                      data: {plan: $('#plan3').val()}
                    }).done( function(data) {
                      var resultado = JSON.parse(data)
                      for ( var i = 0; i < resultado.length; i++ ) {
                        if ( resultado[i]['tipo'] == 1 ) {
                          $('#tipo3 optgroup:last').append("<option value='" + resultado[i]['id'] + "'>" + resultado[i]['nombre'] + "</option>");
                        }
                        else {
                          $('#tipo3 optgroup:first').append("<option value='" + resultado[i]['id'] + "'>" + resultado[i]['nombre'] + "</option>");
                        }
                      }
                      $('#tipo3').selectmenu('refresh');
                    });
                  }
                });
              </script>

              <!-- Script para el plan 4 -->
              <script>
                $('#plan4').on('selectmenuchange', function(event, ui){
                  if ( $('#plan4').val() == 0 ) {
                    $('#tipo4 option:eq(0)').attr('selected', true);
                    $('#tipo4 option:gt(0)').remove();
                    $('#tipo4').selectmenu('refresh');
                    $('#tipo4').selectmenu('disable');
                  }
                  else {
                    $('#tipo4').selectmenu('enable');
                    $('#tipo4 option:gt(0)').remove();
                    $('#tipo4 option:eq(0)').attr('selected', true);
                    $.ajax({
                      url: 'consultar_tipos.php',
                      data: {plan: $('#plan4').val()}
                    }).done( function(data) {
                      var resultado = JSON.parse(data)
                      for ( var i = 0; i < resultado.length; i++ ) {
                        if ( resultado[i]['tipo'] == 1 ) {
                          $('#tipo4 optgroup:last').append("<option value='" + resultado[i]['id'] + "'>" + resultado[i]['nombre'] + "</option>");
                        }
                        else {
                          $('#tipo4 optgroup:first').append("<option value='" + resultado[i]['id'] + "'>" + resultado[i]['nombre'] + "</option>");
                        }
                      }
                      $('#tipo4').selectmenu('refresh');
                    });
                  }
                });
              </script>

            </div>
          </fieldset>

          <fieldset>
            <legend>Información general de la actividad</legend>
            <label for='nombre'>Nombre de la actividad</label><input type='text' name='nombre' value='<?php echo ifsetor($nombre); ?>' required>
            <label for='puntos'>Puntos asignados</label><input type='number' name='puntos' class='inline_input' required><br>
            <label for='cupo'>Cupo</label><input type='number' name='cupo' class='inline_input' required><br>
            <label for='lugar'>Lugar de la actividad</label><input type='text' name='lugar' required>
            <label for='fecha_inicio'>Fecha y hora de inicio</label><input class='datepicker' type='text' name='fecha_inicio' required>
            <label for='fecha_fin'>Fecha y hora de fin</label><input class='datepicker' type='text' name='fecha_fin' required>
            <label for='costo'>Costo</label>$ <input type='number' name='costo' class='inline_input' value ='0' required>
          </fieldset>

          <fieldset>
            <legend>Fechas de inscripión</legend>
            <label for='inscripcion_general'>Fecha de inscripción (general)</label><input class='datepicker' type='text' id='inscripcion_general' name='inscripcion_general' required>

            <label for='habilitar_comites'>Habilitar inscripción temprana para comités</label>
            <input type='checkbox' id='habilitar_comites' name='habilitar_comites' value='habilitar_comites'>

            <br><br>
            <label for='inscripcion_comites' id='etiqueta_fecha_comites'>Fecha de inscripción (comités)</label>
            <input class='datepicker' type='text' id='inscripcion_comites' name='inscripcion_comites' value='<?php echo $actividad['inscripcion_comites']; ?>'>
            <input type='hidden' name='inscripcion_previa' id='inscripcion_previa' value='0'>

            <script>
              $('#etiqueta_fecha_comites').hide();
              $('#inscripcion_comites').hide();
              $('#inscripcion_comites').val('2017-01-01 12:00');
              $('#habilitar_comites').change(function() {
                if (this.checked) {
                  $('#etiqueta_fecha_comites').show();
                  $('#inscripcion_comites').show();
                  $('#inscripcion_comites').val('');
                  $('#inscripcion_previa').val('1');
                }
                else {
                  $('#etiqueta_fecha_comites').hide();
                  $('#inscripcion_comites').hide();
                  $('#inscripcion_comites').val('2017-01-01 12:00');
                  $('#inscripcion_previa').val('0');
                }
              });
            </script>
          </fieldset>

          <fieldset>
            <legend>Comentarios</legend>
            <label for='comentarios'>Comentarios sobre la actividad (no requerido)</label><textarea name='comentarios' rows='5'><?php echo ifsetor($comentarios); ?></textarea>
          </fieldset>
          <br><br>

          <input type='hidden' name='flag' value='agregar_actividad'>
          <input type='submit' value='Agregar actividad' class='boton'>
          <div class='aclarador'></div>
        </form>
      </div>
    </div>
  </div>

<?php } ?>

<div class='card'>
  <h2>Actividades</h2>

  <form method='POST' action='?d=actividades'>
    <label>Periodo: </label>
    <select class='selectmenu' name='periodo'>
      <?php $periodos->data_seek(0);
      while ($periodo=$periodos->fetch_assoc()) { ?>
        <option value="<?php echo $periodo['clave']; ?>" <?php if ($periodo['clave'] === $periodo_actividades) {echo 'selected';} ?>>
          <?php if ($periodo['semestre'] === '1') {echo 'Ene-jun ';}
          else {echo 'Ago-dic ';}
          echo $periodo['ano'];
          if ($periodo['activo'] == 1) {echo ' (activo)';} ?>
        </option>
      <?php } ?>
    </select>
    <input type='hidden' name='flag' value='actualizar_periodo'>
    <input type='submit' value='Actualizar' class='boton_inline'>
  </form>
  <hr>

  <?php // Lista de actividades (coordinadores):
  if ($_SESSION['estatus'] === 'coordinador') {
    $actividades = $conexion->query("SELECT * FROM actividades WHERE actividades.periodo='$periodo_actividades' ORDER BY fecha_inicio DESC");
    if ($actividades->num_rows > 0) {
      if ($periodo_actividades == $periodo_activo['clave']) { ?>
        <table>
          <tr><td><i class='fas fa-check fa-fw color-verde'></i></td><td>actividad con lugares disponibles.</td></tr>
          <tr><td><i class='fas fa-times fa-fw color-rojo'></i></td><td>actividad sin lugares disponibles.</td></tr>
          <tr><td><i class='fas fa-lock fa-fw color-tip'></i></td><td>actividad sin abrir.</td></tr>
        </table>
        <hr>
      <?php } ?>
      <table class='tabla'>
        <tr><th>Actividad</th><th>Puntos</th><th>Fecha</th><th>Costo</th><th>Eliminar</th></tr>
        <?php foreach ($actividades as $actividad) {
          $condicion_cupo = $actividad['cupo'] - $conexion->query("SELECT id FROM actividades_usuarios WHERE clave='{$actividad['clave']}'")->num_rows > 0;
          $condicion_tiempo = date('Y-m-d H:i:s') >= $actividad['inscripcion_general'] && date('Y-m-d H:i:s') <= $actividad['fecha_inicio'];
          if ($actividad['periodo'] !== $periodo_activo['clave']) {
            $simbolo = "";
          }
          elseif ($condicion_cupo && $condicion_tiempo) {
            $simbolo = "<i class='fas fa-check fa-fw color-verde'></i>";
          }
          elseif ($condicion_tiempo) {
            $simbolo = "<i class='fas fa-times fa-fw color-rojo'></i>";
          }
          elseif ($condicion_cupo) {
            $simbolo = "<i class='fas fa-lock fa-fw color-tip'></i>";
          }
          else {
            $simbolo = "<i class='fas fa-lock fa-fw color-tip'></i>";
          } ?>
          <tr>
            <td><?php echo $simbolo; ?><a href="intranet.php?d=actividad&clave=<?php echo $actividad['clave']; ?>"><?php echo $actividad['nombre']; ?></a></td>
            <td><?php echo $actividad['puntos']; ?></td>
            <td><?php echo fecha_formateada($actividad['fecha_inicio']); ?></td>
            <td>$ <?php echo $actividad['costo']; ?></td>
            <td><form method='POST' action='?d=actividades'><input type='hidden' name='flag' value='eliminar_actividad'><input type='hidden' name='clave' value='<?php echo $actividad["clave"]; ?>'><input type='submit' value='Eliminar' class='boton_inline'></form></td>
          </tr>
        <?php } ?>
      </table><p></p>
    <?php }
    else { ?>
      <p>No hay actividades para el periodo seleccionado.</p>
    <?php }
  }

  // Lista de actividades (alumnos):
  else {
    $requisitos = $conexion->query("SELECT * FROM requisitos WHERE plan='{$_usuario->informacion['plan']}'");

    if ($conexion->query("SELECT clave FROM actividades WHERE periodo='$periodo_actividades'")->num_rows > 0) {
      if ($periodo_actividades == $periodo_activo['clave']) { ?>
        <table>
          <tr><td><i class='fas fa-lock fa-fw color-tip'></i></td><td>la actividad todavía no está abierta.</td></tr>
          <tr><td><i class='fas fa-unlock fa-fw color-verde'></i></td><td>la actividad tiene lugares disponibles y se puede inscribir.</td></tr>
          <tr><td><i class='fas fa-check fa-fw color-tip'></i></td><td>ya estás inscrito a la actividad, pero no tienes asistencia registrada.</td></tr>
          <tr><td><i class='fas fa-check fa-fw color-verde'></i></td><td>tienes asistencia registrada a la actividad .</td></tr>
          <tr><td><i class='far fa-clock fa-fw color-tip'></i></td><td>estás en la lista de espera de la actividad.</td></tr>
          <tr><td><i class='fas fa-times fa-fw color-rojo'></i></td><td>el cupo de la actividad ya está lleno.</td></tr>
        </table>
      <?php }
      else { ?>
        <table>
          <tr><td><i class='fas fa-check fa-fw color-tip'></i></td><td>estás inscrito a la actividad, pero no tienes asistencia registrada.</td></tr>
          <tr><td><i class='fas fa-check fa-fw color-verde'></i></td><td>tienes asistencia registrada a la actividad.</td></tr>
        </table>
      <?php } ?>
      <hr>
      <?php // Se utiliza el objeto $iterador para simplificar el código a la mitad:
      $iterador = array(array(), array());
      $iterador[0]['tipo']  = 'curriculares';
      $iterador[0]['query'] = '1';
      $iterador[1]['tipo']  = 'extracurriculares';
      $iterador[1]['query'] = '2';
      for ($i = 0; $i <= 1; $i++) {
        $actividades = $conexion->query("SELECT actividades.*
          FROM actividades
          INNER JOIN requisitos ON requisitos.id IN (actividades.tipo_1, actividades.tipo_2, actividades.tipo_3, actividades.tipo_4)
          AND requisitos.plan='{$_usuario->informacion['plan']}'
          AND requisitos.tipo='{$iterador[$i]['query']}'
          WHERE actividades.periodo='$periodo_actividades'
          ORDER BY actividades.fecha_inicio DESC");?>
        <h3>Actividades <?php echo $iterador[$i]['tipo']; ?></h3>
        <?php if ($actividades->num_rows > 0) {
          $requisitos->data_seek(0);
          $flag = 0;
          while ($requisito = $requisitos->fetch_assoc()) {
            if ($requisito['tipo'] == $iterador[$i]['query']) {
              $resultados = array();
              $actividades->data_seek(0);
              while ($actividad=$actividades->fetch_assoc()) {
                if ($actividad['tipo_1'] == $requisito['id'] OR $actividad['tipo_2'] == $requisito['id'] OR $actividad['tipo_3'] == $requisito['id'] OR $actividad['tipo_4'] == $requisito['id']) {
                  $resultados[] = $actividad;
                }
              }
              if (count($resultados) > 0) {
                $flag++; ?>
                <h4><?php echo $requisito['nombre']; ?></h4>
                <div class='scroll'>
                  <table class='tabla'>
                    <tr><th>Actividad</th><th>Puntos</th><th>Fecha</th><th>Costo</th></tr>
                    <?php foreach ($resultados as $resultado) {
                      // Condiciones de inscripcion:

                      if ($conexion->query("SELECT id FROM comites WHERE id='{$_SESSION['id']}'")->num_rows > 0) {
                        $fecha_inscripcion = $resultado['inscripcion_comites'];
                      }
                      else {
                        $fecha_inscripcion = $resultado['inscripcion_general'];
                      }

                      $condicion_cupo = $resultado['cupo'] - $conexion->query("SELECT id FROM actividades_usuarios WHERE clave='{$resultado['clave']}'")->num_rows > 0;
                      $condicion_tiempo = date('Y-m-d H:i:s') >= $fecha_inscripcion && date('Y-m-d H:i:s') <= $resultado['fecha_inicio'];
                      $condicion_inscrito = $conexion->query("SELECT id FROM actividades_usuarios WHERE id='{$_SESSION['id']}' AND clave='{$resultado['clave']}'")->num_rows > 0;
                      $condicion_espera = $conexion->query("SELECT id FROM actividades_usuarios_espera WHERE id='{$_SESSION['id']}' AND clave='{$resultado['clave']}'")->num_rows > 0;
                      if ($resultado['periodo'] == $periodo_activo['clave']) {
                        if ($condicion_inscrito) {
                          $asistencia = $conexion->query("SELECT asistencia FROM actividades_usuarios WHERE id='{$_SESSION['id']}' AND clave='{$resultado['clave']}'")->fetch_assoc()['asistencia'];
                          if ($asistencia == '1') {
                            $simbolo = "<i class='fas fa-check fa-fw color-verde'></i>";
                          }
                          else {
                            $simbolo = "<i class='fas fa-check fa-fw color-tip'></i>";
                          }
                        }
                        elseif ($condicion_espera) {
                          $simbolo = "<i class='far fa-clock fa-fw color-tip'></i>";
                        }
                        elseif ($condicion_cupo && $condicion_tiempo) {
                          $simbolo = "<i class='fas fa-unlock fa-fw color-verde'></i>";
                        }
                        elseif ($condicion_tiempo) {
                          $simbolo = "<i class='fas fa-times fa-fw color-rojo'></i>";
                        }
                        elseif ($condicion_cupo) {
                          $simbolo = "<i class='fas fa-lock fa-fw color-tip'></i>";
                        }
                        else {
                          $simbolo = "<i class='fas fa-lock fa-fw color-tip'></i>";
                        }
                      }
                      else {
                        if ($condicion_inscrito) {
                          $simbolo = "<i class='fas fa-check fa-fw color-tip'></i>";
                        }
                        else {
                          $simbolo = "";
                        }
                      }?>
                      <tr>
                        <td><?php echo $simbolo; ?><a href="intranet.php?d=actividad&clave=<?php echo $resultado['clave']; ?>"><?php echo $resultado['nombre']; ?></a></td>
                        <td><?php echo $resultado['puntos']; ?></td>
                        <td><?php echo fecha_formateada($resultado['fecha_inicio']); ?></td>
                        <td>$ <?php echo $resultado['costo']; ?></td>
                      </tr>
                      <?php } ?>
                  </table>
                </div>
              <?php }
            }
          }
          if ($flag === 0) { ?>
            <p>No hay actividades <?php echo $iterador[$i]['tipo']; ?> disponibles en este periodo.</p>
          <?php }
        }
        else { ?>
          <p>No hay actividades <?php echo $iterador[$i]['tipo']; ?> disponibles en este periodo.</p>
        <?php }
      }
    }
    else { ?>
      <p>No hay actividades registradas en el periodo seleccionado.</p>
    <?php }
  } ?>
</div>
