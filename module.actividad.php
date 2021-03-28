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

<?php if (isset($_GET['clave'])) {
  $clave = $_GET['clave'];
  $actividades = $conexion->query("SELECT * FROM actividades WHERE clave='$clave'");
  $actividad = $actividades->fetch_assoc();
  $periodo_activo = $conexion->query("SELECT * FROM periodos WHERE activo='1'")->fetch_assoc();
  if ($actividades->num_rows > 0) { ?>
    <div class='responsive_hide'>
      <div class='navegador'>Estás en</div>
      <div class='tag clickable' onclick="window.location='intranet.php';">Inicio</div>
      <div class='navegador'><i class='fas fa-chevron-right inline'></i></div>
      <div class='tag clickable' onclick="window.location='?d=actividades'">Actividades</div>
      <div class='navegador'><i class='fas fa-chevron-right inline'></i></div>
      <div class='tag'><?php echo $actividad['nombre']; ?></div>
    </div>

    <?php
    // Señal: desinscribir_alumno:
    if ($_POST['flag'] === 'desinscribir_alumno') {
        $sql = "DELETE FROM actividades_usuarios WHERE id='{$_POST['id']}' AND clave='$clave'";
        mensaje($sql, 'Alumno eliminado correctamente.');
    }
    // Señal: inscribir_alumno:
    if ($_POST['flag'] === 'inscribir_alumno') {
        if ($conexion->query("SELECT id FROM actividades_usuarios WHERE id='{$_POST['id']}' AND clave='$clave'")->num_rows == 0) {
          $sql = "INSERT INTO actividades_usuarios (id, clave, asistencia) VALUES ('{$_POST['id']}', '$clave', '0')";
          mensaje($sql, 'Alumno inscrito correctamente.');
        }
        else {
          mensaje_error('No se puede inscribir dos veces a un alumno a la misma actividad.');
        }
    }
    // Señal: lista_espera:
    if ($_POST['flag'] === 'lista_espera') {
        if ($conexion->query("SELECT id FROM actividades_usuarios WHERE id='{$_POST['id']}' AND clave='$clave'")->num_rows == 0) {
          if ($conexion->query("SELECT id FROM actividades_usuarios_espera WHERE id='{$_POST['id']}' AND clave='$clave'")->num_rows == 0) {
            $sql = "INSERT INTO actividades_usuarios_espera (id, clave) VALUES ('{$_POST['id']}', '$clave')";
            mensaje($sql, 'Alumno registrado correctamente en la lista de espera.');
          }
          else {
            mensaje_error('El alumno ya está registrado en la lista de espera de la actividad.');
          }
        }
        else {
          mensaje_error('El alumno ya está inscrito en la actividad.');
        }
    }
    // Señal: eliminar_lista:
    if ($_POST['flag'] === 'eliminar_lista') {
        $sql = "DELETE FROM actividades_usuarios_espera WHERE id='{$_POST['id']}' AND clave='$clave'";
        mensaje($sql, 'Alumno eliminado correctamente de la lista de espera.');
    }
    // Señal: transferir_lista:
    if ($_POST['flag'] === 'transferir_lista') {
        $sql = "DELETE FROM actividades_usuarios_espera WHERE id='{$_POST['id']}' AND clave='$clave'";
        mensaje($sql, 'Alumno eliminado correctamente de la lista de espera.');
        $sql = "INSERT INTO actividades_usuarios (id, clave, asistencia) VALUES ('{$_POST['id']}', '$clave', '0')";
        mensaje($sql, 'Alumno inscrito correctamente a la actividad.');
    }

    $inscritos = $conexion->query("SELECT * FROM usuarios INNER JOIN actividades_usuarios ON usuarios.id=actividades_usuarios.id WHERE actividades_usuarios.clave='{$actividad['clave']}'");

    if ($_SESSION['estatus'] === 'coordinador') {
      // Layout de coordinadores:
      $periodos = $conexion->query("SELECT * FROM periodos"); ?>
      <div class='card'>
        <h2><?php echo $actividad['nombre']; ?></h2>
        <table class='tabla ajustada'>
          <tr><tr>
          <tr><td>Alumnos inscritos</td><td><?php echo $inscritos->num_rows; ?></td></tr>
          <tr><td>Lugares disponibles</td><td><?php if($actividad['cupo'] - $inscritos->num_rows <= 0) { echo '0'; } else { echo $actividad['cupo'] - $inscritos->num_rows; } ?></td></tr>
          <tr><td>Cupo de la actividad</td><td><?php echo $actividad['cupo']; ?></td></tr>
          <tr><td>Alumnos en lista de espera</td><td><?php echo $conexion->query("SELECT COUNT(id) AS lista_espera FROM actividades_usuarios_espera WHERE clave='{$actividad['clave']}'")->fetch_assoc()['lista_espera']; ?></td></tr>
        </table>
        <p>Porcentaje de llenado de la actividad:</p>
        <?php barra_progreso(round($inscritos->num_rows / $actividad['cupo'], 2)); ?>
      </div>

      <div class='card'>
        <h2>Inscribir alumnos</h2>
        <form>
          <label for='busqueda'>Buscar por nombre o expediente</label>
          <input type='text' name='busqueda' id='busqueda'>
        </form>
        <div id='database'>
          <div id='loader'>
            <img src='img/ajax-loader.gif' class='loader'>
            <p>PROCESANDO...</p>
          </div>
          <div id='empty'>
            <i class='fas fa-meh-o inline fa-3x'></i>
            <p>No hemos encontrado nada que coincida con tu búsqueda</p>
            <p>Por favor vuelve a intentarlo</p>
          </div>
          <div id='waiting'>
            <i class='fas fa-lightbulb-o inline fa-3x'></i>
            <p>Introduce un texto en el campo de búsqueda</p>
          </div>
          <div id='ajaxerror'>
            <p><i class='fas fa-exclamation-circle color-rojo'></i> HA HABIDO UN ERROR AL CONECTAR A LA BASE DE DATOS</p>
          </div>
        </div>
      </div>

      <script>
        var timeoutID = null;

        function ejecutar_consulta() {
          // Actualizar espacio de tabla:
          $('#database table').hide();
          $('#loader').show();
          // Ejecutar consulta AJAX:
          clearTimeout(timeoutID);
          timeoutID = setTimeout(function() {
            /*if ($('#busqueda').val() == '') {
              $('#loader').hide();
              $('#waiting').show();
            }*/
            //else {
              $.ajax({
                url: 'busqueda_usuarios_2.php',
                data: {
                  cadena: $('#busqueda').val(),
                  clave: <?php echo $clave; ?>,
                }
              }).done( function(data) {
                $('#loader').hide();
                $('#database table').remove();
                $('#database').append(data);
              }).fail( function() {
                $('#loader').hide();
              });
            //}
          }, 1000);
        }

        $('#busqueda').keyup(ejecutar_consulta);
      </script>

      <?php if ($inscritos->num_rows > 0) { ?>
        <div class='card'>
            <h2>Lista de alumnos inscritos</h2>
            <form>
              <fieldset>
                <legend>Exportar lista</legend>
                <a target='_blank' href='exportar_xlsx.php?target=actividad&clave=<?php echo $clave; ?>'><p class='boton_inline' onclick='exportar_excel();'>Exportar como archivo de Excel</p></a>&nbsp;&nbsp;
                <p class='boton_inline' onclick='exportar_mails();'>Exportar direcciones de correo</p>
            </fieldset>
          </form>

          <div id='exportar_mails' title='Exportar direcciones de correo'>
            <p>Direcciones de correo separadas por punto y coma:</p>
            <?php $mails_inscritos = '';
            while ($inscrito = $inscritos->fetch_assoc()) {
              $mails_inscritos = $mails_inscritos . ';' . $inscrito['correo'];
            }
            $mails_inscritos = substr($mails_inscritos, 1); ?>
            <textarea rows='10' class='fixed' readonly='true'><?php echo $mails_inscritos; ?></textarea>
            <hr>
            <p class='boton' onclick='seleccionar_mails();'>Seleccionar todo</p>
          </div>

          <script>
            $('#exportar_mails').dialog({
              autoOpen: false,
              modal: true,
              closeText: '',
              width: 500
            });

            function seleccionar_mails() {
              $('#exportar_mails textarea').select();
            }

            function exportar_mails() {
              $('#exportar_mails').dialog('open');
              seleccionar_mails();
            }
          </script>

            <br>
            <table class='tabla'>
            <tr><th>Nombre</th><th>Generación</th><th>Asistencia</th><th>Dar de baja</th></tr>
            <?php $inscritos->data_seek(0);
              while ($inscrito = $inscritos->fetch_assoc()) { ?>
                <tr>
                  <td><span class='link' onclick="abrir_popup_alumno('<?php echo $inscrito['id']; ?>');"><?php echo $inscrito['nombre'] . ' ' . $inscrito['apellido_p'] . ' ' . $inscrito['apellido_m']; ?></span></td>
                  <td><?php echo $inscrito['generacion']; ?></td>
                  <td>
                    <label id='l<?php echo $inscrito['id']; ?>' for='a<?php echo $inscrito['id']; ?>'>
                      <?php $asistencia = $conexion->query("SELECT asistencia FROM actividades_usuarios WHERE clave='{$actividad['clave']}' AND id='{$inscrito['id']}'")->fetch_assoc();
                      if ($asistencia['asistencia'] === '1') { echo 'Asistió'; } else { echo 'No asistió'; } ?>
                    </label>
                    <input type='checkbox' id='a<?php echo $inscrito['id']; ?>' name='a<?php echo $inscrito['id']; ?>' <?php if ($asistencia['asistencia'] === '1') { echo 'checked'; } ?>>
                    <script>
                       $('#a<?php echo $inscrito['id']; ?>').change(function(){
                         var objeto = $(this);
                         objeto.checkboxradio('disable');
                         if (objeto.prop('checked') == true) {
                           $.ajax({
                             url: 'query.php',
                             data: {query: "UPDATE actividades_usuarios SET asistencia='1' WHERE clave='<?php echo $actividad['clave']; ?>' AND id='<?php echo $inscrito['id']; ?>'"}
                           }).done(function() {
                             objeto.checkboxradio('option', 'label', 'Asistió');
                             objeto.checkboxradio('enable');
                           }).fail(function() {
                             objeto.checkboxradio('enable');
                           });
                         }
                         else {
                           $.ajax({
                             url: 'query.php',
                             data: {query: "UPDATE actividades_usuarios SET asistencia='0' WHERE clave='<?php echo $actividad['clave']; ?>' AND id='<?php echo $inscrito['id']; ?>'"}
                           }).done(function() {
                             objeto.checkboxradio('option', 'label', 'No asistió');
                             objeto.checkboxradio('enable');
                           }).fail(function() {
                             objeto.checkboxradio('enable');
                           });
                         }
                       });
                    </script>
                  </td>
                  <td>
                    <form method='POST' action='?d=actividad&clave=<?php echo $actividad['clave']; ?>'><input type='hidden' name='flag' value='desinscribir_alumno'><input type='hidden' name='id' value='<?php echo $inscrito['id']; ?>'><input type='submit' value='Dar de baja' class='boton_inline'></form>
                  </td>
                </tr>
            <?php } ?>
          </table><p></p>
        </div>

        <?php if($conexion->query("SELECT COUNT(id) AS lista_espera FROM actividades_usuarios_espera WHERE clave='{$actividad['clave']}'")->fetch_assoc()['lista_espera'] > 0) { ?>
          <div class='card'>
            <h2>Lista de espera de la actividad</h2>

            <form>
              <fieldset>
                <legend>Exportar lista</legend>
                <p class='boton_inline' onclick='exportar_mails_espera();'>Exportar direcciones de correo</p>
              </fieldset>
            </form>

            <div id='exportar_mails_espera' title='Exportar direcciones de correo'>
              <p>Direcciones de correo separadas por punto y coma:</p>
              <?php $mails_inscritos_espera = '';
              $lista_espera = $conexion->query("SELECT * FROM usuarios INNER JOIN actividades_usuarios_espera ON usuarios.id=actividades_usuarios_espera.id WHERE actividades_usuarios_espera.clave='{$actividad['clave']}' ORDER BY actividades_usuarios_espera.clave_orden ASC");
              while ($espera = $lista_espera->fetch_assoc()) {
                $mails_inscritos_espera = $mails_inscritos_espera . ';' . $espera['correo'];
              }
              $mails_inscritos_espera = substr($mails_inscritos_espera, 1); ?>
              <textarea rows='10' class='fixed' readonly='true'><?php echo $mails_inscritos_espera; ?></textarea>
              <hr>
              <p class='boton' onclick='seleccionar_mails_espera();'>Seleccionar todo</p>
            </div>

            <script>
              $('#exportar_mails_espera').dialog({
                autoOpen: false,
                modal: true,
                closeText: '',
                width: 500
              });

              function seleccionar_mails_espera() {
                $('#exportar_mails_espera textarea').select();
              }

              function exportar_mails_espera() {
                $('#exportar_mails_espera').dialog('open');
                seleccionar_mails_espera();
              }
            </script>

            <p>La lista está ordenada de los registros más antiguos a los más recientes (aparecen primero los que se registraron primero).</p>
            <table class='tabla'>
              <tr><th>Nombre</th><th>Generación</th><th>Eliminar de la lista</th><th>Inscribir a la actividad</th></tr>
              <?php $lista_espera = $conexion->query("SELECT * FROM usuarios INNER JOIN actividades_usuarios_espera ON usuarios.id=actividades_usuarios_espera.id WHERE actividades_usuarios_espera.clave='{$actividad['clave']}' ORDER BY actividades_usuarios_espera.clave_orden ASC");
              while ($registro_lista_espera = $lista_espera->fetch_assoc()) { ?>
                <tr>
                  <td><?php echo $registro_lista_espera['nombre'] . ' ' . $registro_lista_espera['apellido_p'] . ' ' . $registro_lista_espera['apellido_m']; ?></td>
                  <td><?php echo $registro_lista_espera['generacion']; ?></td>
                  <td>
                    <form method='POST' action='?d=actividad&clave=<?php echo $actividad['clave']; ?>'>
                      <input type='hidden' name='flag' value='eliminar_lista'>
                      <input type='hidden' name='id' value='<?php echo $registro_lista_espera['id']; ?>'>
                      <input type='submit' value='Eliminar de la lista' class='boton_inline'>
                    </form>
                  </td>
                  <td>
                    <form method='POST' action='?d=actividad&clave=<?php echo $actividad['clave']; ?>'>
                      <input type='hidden' name='flag' value='transferir_lista'>
                      <input type='hidden' name='id' value='<?php echo $registro_lista_espera['id']; ?>'>
                      <input type='submit' value='Inscribir a la actividad' class='boton_inline'>
                    </form>
                  </td>
                </tr>
              <?php } ?>
            </table>
          </div>
        <?php }
        } ?>

      <div class='card'>
        <h2>Editar actividad</h2>
        <div class='accordion'>
          <h3>Editar actividad</h3>
          <form method='POST' action='?d=actividades'>
            <fieldset>
              <legend>Parámetros de la actividad&nbsp;&nbsp;<span class='hint' title='Esta característica se habilitará en una actualización posterior del sistema.'>Por el momento esto no se puede editar</span></legend>
              <label>Periodo</label>
              <select class='selectmenu' name='periodo' disabled>
                <?php foreach ($periodos as $periodo) { ?>
                  <option value="<?php echo $periodo['clave']; ?>" <?php if ($periodo['clave'] == $actividad['periodo']) {echo 'selected';} ?>>
                    <?php echo periodo_formateado(array($periodo['semestre'], $periodo['ano'])); if ($periodo['activo'] == 1) { echo ' (activo)'; } ?>
                  </option>
                <?php } ?>
              </select>
              <br>

              <?php if ($actividad['tipo_1'] <> '0' && $actividad['tipo_2'] <> '0') { ?>
                <p>Esta actividad tiene lista cruzada de la siguiente forma:</p>
                <table class='tabla' style='width:70%;'>
                  <tr><th>Plan curricular</th><th>Tipo de actividad</th></tr>
                  <tr>
                    <?php $plan_1_id = $conexion->query("SELECT plan FROM requisitos WHERE id='{$actividad['tipo_1']}'")->fetch_assoc()['plan']; ?>
                    <td><?php echo $conexion->query("SELECT nombre FROM planes WHERE id='$plan_1_id'")->fetch_assoc()['nombre']; ?></td>
                    <td><?php echo $conexion->query("SELECT nombre FROM requisitos WHERE id='{$actividad['tipo_1']}'")->fetch_assoc()['nombre']; ?></td>
                  </tr>
                  <tr>
                    <?php $plan_2_id = $conexion->query("SELECT plan FROM requisitos WHERE id='{$actividad['tipo_2']}'")->fetch_assoc()['plan']; ?>
                    <td><?php echo $conexion->query("SELECT nombre FROM planes WHERE id='$plan_2_id'")->fetch_assoc()['nombre']; ?></td>
                    <td><?php echo $conexion->query("SELECT nombre FROM requisitos WHERE id='{$actividad['tipo_2']}'")->fetch_assoc()['nombre']; ?></td>
                  </tr>
                  <tr>
                    <?php $plan_3_id = $conexion->query("SELECT plan FROM requisitos WHERE id='{$actividad['tipo_3']}'")->fetch_assoc()['plan']; ?>
                    <td><?php echo $conexion->query("SELECT nombre FROM planes WHERE id='$plan_3_id'")->fetch_assoc()['nombre']; ?></td>
                    <td><?php echo $conexion->query("SELECT nombre FROM requisitos WHERE id='{$actividad['tipo_3']}'")->fetch_assoc()['nombre']; ?></td>
                  </tr>
                  <tr>
                    <?php $plan_4_id = $conexion->query("SELECT plan FROM requisitos WHERE id='{$actividad['tipo_4']}'")->fetch_assoc()['plan']; ?>
                    <td><?php echo $conexion->query("SELECT nombre FROM planes WHERE id='$plan_4_id'")->fetch_assoc()['nombre']; ?></td>
                    <td><?php echo $conexion->query("SELECT nombre FROM requisitos WHERE id='{$actividad['tipo_4']}'")->fetch_assoc()['nombre']; ?></td>
                  </tr>
                </table>
              <?php }
              else {
                $tipo = $actividad['tipo_1'] + $actividad['tipo_2']; // esto se puede hacer porque necesariamente uno de los tipos es cero
                $plan_id = $conexion->query("SELECT plan FROM requisitos WHERE id='$tipo'")->fetch_assoc()['plan']; ?>
                <p>Esta actividad pertenece al plan curricular: <?php echo $conexion->query("SELECT nombre FROM planes WHERE id='$plan_id'")->fetch_assoc()['nombre']; ?></p>
                <p>Esta actividad es del siguiente tipo: <?php echo $conexion->query("SELECT nombre FROM requisitos WHERE id='$tipo'")->fetch_assoc()['nombre']; ?></p>
              <?php } ?>
            </fieldset>

            <fieldset>
              <legend>Información general de la actividad</legend>
              <label for='nombre'>Nombre de la actividad</label>
              <input type='text' name='nombre' value='<?php echo $actividad['nombre']; ?>'>

              <label for='puntos'>Puntos asignados</label>
              <input type='number' name='puntos' class='inline_input' value='<?php echo $actividad['puntos']; ?>'>
              <br>

              <label for='cupo'>Cupo</label>
              <input type='number' name='cupo' class='inline_input' value='<?php echo $actividad['cupo']; ?>'>
              <br>

              <label for='lugar'>Lugar de la actividad</label>
              <input type='text' name='lugar' value='<?php echo $actividad['lugar']; ?>'>

              <label for='fecha_inicio'>Fecha y hora de inicio</label>
              <input class='datepicker' type='text' name='fecha_inicio' value='<?php echo $actividad['fecha_inicio']; ?>'>

              <label for='fecha_fin'>Fecha y hora de fin</label>
              <input class='datepicker' type='text' name='fecha_fin' value='<?php echo $actividad['fecha_fin']; ?>'>

              <label for='costo'>Costo</label>$ <input type='number' name='costo' class='inline_input' value ='0' required>
            </fieldset>

            <fieldset>
              <legend>Fechas de inscripción</legend>
              <label for='inscripcion_general'>Fecha de inscripción (general)</label>
              <input class='datepicker' type='text' name='inscripcion_general' value='<?php echo $actividad['inscripcion_general']; ?>'>

              <label for='habilitar_comites'>Habilitar inscripción temprana para comités</label>
              <input type='checkbox' id='habilitar_comites' name='habilitar_comites' value='habilitar_comites'>

              <br><br>
              <label for='inscripcion_comites' id='etiqueta_fecha_comites'>Fecha de inscripción (comités)</label>
              <input class='datepicker' type='text' id='inscripcion_comites' name='inscripcion_comites' value='<?php echo $actividad['inscripcion_comites']; ?>'>
              <input type='hidden' name='inscripcion_previa' id='inscripcion_previa' value='0'>

              <?php if ($actividad['comites'] == '1') {
                $foo = "$('#habilitar_comites').prop('checked', true);$('#habilitar_comites').checkboxradio('refresh');$('#inscripcion_previa').val('1');";
                echo "<script>$(document).ready(function() {{$foo}});</script>";
              }
              else {
                $foo = "$('#habilitar_comites').prop('checked', false);$('#habilitar_comites').checkboxradio('refresh');$('#etiqueta_fecha_comites').hide();$('#inscripcion_comites').hide();$('#inscripcion_previa').val('0');";
                echo "<script>$(document).ready(function() {{$foo}});</script>";
              }?>

              <script>
                $('#habilitar_comites').change(function() {
                  if (this.checked) {
                    $('#etiqueta_fecha_comites').show();
                    $('#inscripcion_comites').show();
                    $('#inscripcion_previa').val('1');
                  }
                  else {
                    $('#etiqueta_fecha_comites').hide();
                    $('#inscripcion_comites').hide();
                    $('#inscripcion_previa').val('0');
                  }
                });
              </script>

            </fieldset>

            <fieldset>
              <legend>Comentarios</legend>
              <label for='comentarios'>Comentarios sobre la actividad (no requerido)</label>
              <textarea name='comentarios' rows='5'><?php echo $actividad['comentarios']; ?></textarea>
            </fieldset>
            <br>

            <input type='hidden' name='clave' class='flag' value='<?php echo $actividad["clave"]; ?>'>
            <input type='hidden' name='flag' class='flag' value='actualizar_actividad'>
            <input type='submit' value='Guardar cambios' class='boton'>
            <div class='aclarador'></div>
          </form>
        </div>
      </div>

    <?php } else {

      // Mensaje de los coordinadores:
      if ($ajustes['mensaje_activo'] == 1) { ?>
        <div class='card-important responsive_hide'>
          <h2><?php echo $ajustes['mensaje_titulo']; ?></h2>
          <p><?php echo $ajustes['mensaje']; ?></p>
        </div>
      <?php }

      // Layout de alumnos: ?>
      <div class='card'>
        <h2><?php echo $actividad['nombre']; ?></h2>
        <?php // Condiciones de inscripcion:

        if ($conexion->query("SELECT id FROM comites WHERE id='{$_SESSION['id']}'")->num_rows > 0 && $actividad['comites'] == '1') {
          $fecha_inscripcion = $actividad['inscripcion_comites'];
        }
        else {
          $fecha_inscripcion = $actividad['inscripcion_general'];
        }

        $condicion_cupo = $actividad['cupo'] - $inscritos->num_rows > 0;
        $condicion_tiempo = date('Y-m-d H:i:s') >= $fecha_inscripcion;
        $condicion_tiempo_2 = date('Y-m-d H:i:s') <= $actividad['fecha_inicio'];
        $condicion_inscrito = $conexion->query("SELECT id FROM actividades_usuarios WHERE id='{$_SESSION['id']}' AND clave='{$actividad['clave']}'")->num_rows > 0;
        $condicion_espera = $conexion->query("SELECT id FROM actividades_usuarios_espera WHERE id='{$_SESSION['id']}' AND clave='{$actividad['clave']}'")->num_rows > 0;
        if ($actividad['periodo'] == $periodo_activo['clave']) {
          if ($condicion_inscrito) {
            $asistencia = $conexion->query("SELECT asistencia FROM actividades_usuarios WHERE id='{$_SESSION['id']}' AND clave='{$actividad['clave']}'")->fetch_assoc()['asistencia'];
            if ($asistencia == '1') { ?>
              <p><i class='fas fa-check fa-fw color-verde'></i> Asististe a esta actividad.</p>
            <?php }
            else { ?>
              <p><i class='fas fa-check fa-fw color-tip'></i> Ya estás inscrito a esta actividad pero aún no tienes tu asistencia registrada. <span class='hint' title='Para darte de baja de una actividad consulta directamente con un coordinador.'>¿Necesitas darte de baja?</span></p>
            <?php }
          }
          elseif ($condicion_espera) { ?>
            <p><i class='far fa-clock fa-fw color-tip'></i> Estás en la lista de espera de esta actividad.</p>
          <?php }
          elseif ($condicion_cupo && $condicion_tiempo && $condicion_tiempo_2) { ?>
            <p><i class='fas fa-check fa-fw color-verde'></i> Puedes inscribir esta actividad.</p>
          <?php }
          elseif ($condicion_cupo && $condicion_tiempo_2) { ?>
            <p><i class='fas fa-lock fa-fw color-tip'></i> Esta actividad aún no está abierta. Podrás inscribirla a partir del <?php echo date('d', strtotime($fecha_inscripcion)); ?> de <?php echo mes(date('m', strtotime($fecha_inscripcion))); ?> a las <?php echo date('h:i', strtotime($fecha_inscripcion)); ?> hrs.</p>
          <?php }
          elseif ($condicion_tiempo && $condicion_tiempo_2) { ?>
            <p><i class='fas fa-times fa-fw color-rojo'></i> El cupo de esta actividad ya está lleno y por lo tanto no se puede inscribir.</p>
          <?php }
        } ?>
        <hr>
        <p><span class='bold'>Información general de la actividad</span></p>
        <table class='info'>
            <tr><td>Tipo</td><td><?php echo $actividad['tipo']; ?></td></tr>
            <tr><td>Puntos</td><td><?php echo $actividad['puntos']; ?></td></tr>
            <tr><td>Fecha/hora de inicio</td><td><?php echo fecha_formateada($actividad['fecha_inicio']); ?></td></tr>
            <tr><td>Fecha/hora de fin</td><td><?php echo fecha_formateada($actividad['fecha_fin']); ?></td></tr>
            <tr><td>Lugar</td><td><?php echo $actividad['lugar']; ?></td></tr>
            <tr><td>Cupo máximo</td><td><?php echo $actividad['cupo']; ?></td></tr>
            <tr><td>Costo</td><td><?php echo $actividad['costo']; ?></td></tr>
        </table>
        <p><span class='bold'>Comentarios sobre esta actividad:</span> <?php
          if ($actividad['comentarios'] === '') {echo 'No hay comentarios especiales para esta actividad.';}
          else {echo $actividad['comentarios'];} ?></p>
        <?php if (($condicion_cupo && $condicion_tiempo && $condicion_tiempo_2) && !$condicion_inscrito && $actividad['periodo'] == $periodo_activo['clave']) { ?>
          <hr>
          <form method='POST' action='?d=actividades'>
            <input type='hidden' name='flag' value='inscribir_actividad'>
            <input type='hidden' name='clave' value='<?php echo $actividad["clave"]; ?>'>
            <input type='submit' class='boton' value='Inscribir esta actividad'>
            <div class='aclarador'></div>
          </form>
        <?php } ?>
      </div>
    <?php }
  }

  else {
    redireccionar('intranet.php?d=actividades');
  }
}

else {
  redireccionar('intranet.php');
}
