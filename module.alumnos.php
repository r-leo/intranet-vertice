<?php /*
Archivo fuente de módulo
------------------------
* En este script hay acceso a todas las variables y funciones globales disponibles en intranet.php.
*/ ?>

<div class='responsive_hide'>
  <div class='navegador'>Estás en</div>
  <div class='tag clickable' onclick="window.location='intranet.php';">Inicio</div>
  <div class='navegador'><i class='fas fa-chevron-right inline'></i></div>
  <div class='tag'>Alumnos</div>
</div>

<?php $generaciones = $conexion->query("SELECT generacion FROM generaciones ORDER BY generacion"); ?>

<?php if ($_SESSION['estatus'] == 'coordinador') {

  // Señal: consultar:
  if ($_POST['flag'] === 'consultar') {
    $consulta = "";
  }
  else {
    $consulta = "SELECT * FROM usuarios WHERE estatus IN ('activo', 'condicionado')";
  }

  // Señal: eliminar_nuevo:
  if ($_POST['flag'] === 'eliminar_nuevo') {
    $sql = "DELETE FROM usuarios WHERE id='{$_POST['id']}'";
    mensaje($sql, 'Alumno eliminado correctamente.');
  }

  // Señal: alta_coordinador:
  if ($_POST['flag'] === 'alta_coordinador') {
    $sql = "UPDATE usuarios SET estatus='coordinador' WHERE id='{$_POST['id']}'";
    $coordinador = $conexion->query("SELECT nombre, apellido_p FROM usuarios WHERE id='{$_POST['id']}'")->fetch_assoc();
    // CODEH: eliminar todos los registros del usuario
    mensaje($sql, 'Ahora ' . $coordinador['nombre'] . ' ' . $coordinador['apellido_p'] . ' es un coordinador.');
  }

  // Señal: agregar_alumnos:
  if ($_POST['flag'] === 'agregar_alumnos') {
    $alumnos = explode(',', $_POST['alumnos']);
    $insertados = 0;
    for ($i = 0; $i < count($alumnos); $i++) {
      $alumnos[$i] = preg_replace('/\D/', '', $alumnos[$i]);
      if ($alumnos[$i] !== '') {
        if ($conexion->query("INSERT INTO usuarios (id, estatus, generacion) VALUES ('$alumnos[$i]', 'nuevo', '{$_POST['generacion']}')") === TRUE) {
          $insertados++;
        }
        else {
          echo $conexion->error;
        }
      }
    }

    // Mostrar mensaje de confirmación:
    mensaje_exito($insertados . ' alumnos añadidos.');
  } ?>

  <div class='card'>
    <h2>Adminsitración de usuarios</h2>
    <div class='accordion'>
      <h3>Dar de alta uno o varios alumnos</h3>
      <div>
        <form method='POST' action='?d=alumnos'>
          <p>Introducir los expedientes de los alumnos que se darán de alta separados por comas:</p>
          <textarea style='width:100%; height: 100px;' name='alumnos'></textarea>
          <p>Generación a la que pertenecerán los alumnos nuevos:</p>
          <select name='generacion' class='selectmenu'>
            <option value='0'>Sin generación</option>
            <?php while ($generacion = $generaciones->fetch_assoc()) { ?>
              <option value='<?php echo $generacion['generacion']; ?>'>Generación <?php echo $generacion['generacion']; ?></option>
            <?php } ?>
          </select>
          <p><i class='fas fa-lightbulb-o fa-fw color-verde'></i> Si no se define la generación ahora, los alumnos deberán elegirla al momento de registrarse.</p>
          <hr>
          <input type='hidden' name='flag' value='agregar_alumnos'>
          <input type='submit' value='Dar de alta' class='boton'>
        </form>
      </div>
      <h3>Lista de alumnos nuevos</h3>
      <div>
        <?php $nuevos = $conexion->query("SELECT id, generacion FROM usuarios WHERE estatus='nuevo' ORDER BY generacion, id");
        if ($nuevos->num_rows > 0) { ?>
          <p>La siguiente tabla muestra los alumnos dados de alta que aún no se han registrado.</p>
          <table class='tabla'>
            <tr><th>Número de expediente</th><th>Generación</th><th>Eliminar</th></tr>
            <?php while ($nuevo = $nuevos->fetch_assoc()) { ?>
              <tr>
                <td><?php echo $nuevo['id']; ?></td>
                <td><?php if ($nuevo['generacion'] == '0') { echo 'Sin generación'; } else { echo $nuevo['generacion']; } ?></td>
                <td>
                  <form method='POST' action='?d=alumnos'><input type='hidden' name='flag' value='eliminar_nuevo'><input type='hidden' name='id' value='<?php echo $nuevo['id']; ?>'><input type='submit' value='Eliminar' class='boton_inline'></form>
                </td>
              </tr>
            <?php } ?>
          </table>
        <?php }
        else { ?>
          <p>No hay alumnos pendientes de registrarse.</p>
        <?php } ?>
      </div>
      <h3>Lista de coordinadores</h3>
      <div>
        <p>Esta es la lista de coordinadores actuales del sistema:</p>
        <table class='tabla'>
          <tr><th>Nombre</th><th>Eliminar &nbsp;<i class='fas fa-exclamation-triangle inline' title='Eliminar a un coordinador es una acción irreversible'></i></th></tr>
          <?php $coordinadores = $conexion->query("SELECT * FROM usuarios WHERE estatus='coordinador' ORDER BY id");
          while ($coordinador = $coordinadores->fetch_assoc()) { ?>
            <tr><td><?php echo $coordinador['nombre'] . ' ' . $coordinador['apellido_p'] . ' ' . $coordinador['apellido_m']; ?></td><td><span class='hint color-rojo'>Característica pendiente de programar</span></td></tr>
          <?php } ?>
        </table>
      </div>
    </div>
  </div>

  <div class='card'>

    <?php // Consultas SQL de generaciones y carreras:
      $carreras = $conexion->query("SELECT DISTINCT carrera FROM usuarios WHERE carrera IS NOT NULL ORDER BY usuarios.carrera");
      $generaciones->data_seek(0);
    ?>

    <h2>Herramientas de consulta</h2>
      <fieldset>
        <legend>Buscar por nombre o expediente</legend>
        <input type='text' id='busqueda'>
      </fieldset>
      <fieldset>
        <legend>Filtrar</legend>
        <div class='tabs'>
          <ul>
            <li><a href='#tab_generacion' id='titulo_generacion'>Generación&nbsp;</a></li>
            <li><a href='#tab_sexo' id='titulo_sexo'>Sexo&nbsp;</a></li>
            <li><a href='#tab_carrera' id='titulo_carrera'>Carrera&nbsp;</a></li>
            <li><a href='#tab_estatus' id='titulo_estatus'>Estatus&nbsp;</a></li>
          </ul>
          <div id='tab_generacion'>
            <label for='filtro_generacion'>Generación</label>
            <select name='filtro_generacion' id='filtro_generacion' class='selectmenu'>
              <option value='0'>(Sin filtro)</option>
              <?php while ($generacion = $generaciones->fetch_assoc()) { ?>
                <option value='<?php echo $generacion['generacion']; ?>'><?php echo $generacion['generacion']; ?></option>
              <?php } ?>
            </select>
          </div>
          <div id='tab_sexo'>
            <label for='filtro_sexo'>Sexo</label>
            <select name='filtro_sexo' id='filtro_sexo' class='selectmenu'>
              <option value='0'>(Sin filtro)</option>
              <option value='hombre'>Hombre</option>
              <option value='mujer'>Mujer</option>
            </select>
          </div>
          <div id='tab_carrera'>
            <label for='filtro_carrera'>Carrera</label>
            <select name='filtro_carrera' id='filtro_carrera' class='selectmenu'>
              <option value='0'>(Sin filtro)</option>
              <?php while ($carrera = $carreras->fetch_assoc()) { ?>
                <option value='<?php echo $carrera['carrera']; ?>'><?php echo $carrera['carrera']; ?></option>
              <?php } ?>
            </select>
          </div>
          <div id='tab_estatus'>
            <label for='filtro_estatus'>Estatus</label>
            <select name='filtro_estatus' id='filtro_estatus' class='selectmenu'>
              <option value='0' selected>(Sin filtro)</option>
              <option value='activo'>Activo</option>
              <option value='condicionado'>Condicionado</option>
              <option value='baja'>Baja</option>
              <option value='egresado'>Egresado</option>
            </select>
          </div>
        </div>
      </fieldset>
      <fieldset>
        <legend>Exportar <i class='fas fa-question-circle inline' title='Se exportará la tabla mostrada en la parte inferior de esta página.'></i></legend>
        <a target='_blank' href='exportar_xlsx.php?target=lista&est=activo' id='boton_exportar_excel'><p class='boton_inline'>Exportar como archivo de Excel</p></a>&nbsp;&nbsp;
        <p class='boton_inline' onclick='exportar_mails();'>Exportar direcciones de correo</p>
      </fieldset>

      <div id='exportar_mails' title='Exportar direcciones de correo'>
        <p>Direcciones de correo separadas por punto y coma:</p>
        <textarea rows='10' class='fixed' readonly='true'></textarea>
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

  </div>
  <div class='card'>
    <h2>Base de datos</h2>
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
      <table class='tabla'>
        <tr>
          <th>Expediente</th><th>Nombre</th><th>Generación</th><th>Estatus</th>
        </tr>
        <?php $alumnos_bd = $conexion->query("SELECT * FROM usuarios WHERE estatus='alumno'");
        $mails = '';
        while($alumno = $alumnos_bd->fetch_assoc()) {
          $mails = $mails . ';' . $alumno['correo']; ?>
          <tr>
            <td><?php echo $alumno['id']; ?></td>
            <td><span class='link' onclick="abrir_popup_alumno('<?php echo $alumno['id']; ?>');"><?php echo $alumno['nombre'].' '.$alumno['apellido_p'].' '.$alumno['apellido_m']; ?></span></td>
            <td><?php echo $alumno['generacion']; ?></td>
            <td>
              <?php if ($alumno['estatus_rapido'] == 'activo') { ?>
                <span class='color-verde'>Activo</span>
              <?php }
              elseif ($alumno['estatus_rapido'] == 'condicionado') { ?>
                <span class='color-naranja'>Condicionado</span>
              <?php }
              elseif ($alumno['estatus_rapido'] == 'baja') { ?>
                <span class='color-rojo'>Baja</span>
              <?php } ?>
            </td>
          </tr>
        <?php }
        $mails = substr($mails, 1); ?>
        <script>
          $('#exportar_mails textarea').text("<?php echo $mails; ?>");
        </script>
      </table>
    </div>
  </div>

  <script>
    var timeoutID = null;

    function ejecutar_consulta() {
      // Modificar parámetros de exportación:
      $('#boton_exportar_excel').attr('href', 'exportar_xlsx.php?target=lista&nom=' + $('#busqueda').val() + '&gen=' + $('#filtro_generacion').val() + '&sex=' + $('#filtro_sexo').val() + '&car=' + $('#filtro_carrera').val() + '&est=' + $('#filtro_estatus').val());

      // Actualizar indicadores de filtro:
      // Filtro de generación:
      if ($('#filtro_generacion').val() == '0') {
        $('#titulo_generacion i').remove();
      }
      else if ($('#titulo_generacion i').length == 0) {
        $('#titulo_generacion').append("<i class='fas fa-filter color-verde inline' title='Este filtro está activo'></i>");
      }
      // Filtro de sexo:
      if ($('#filtro_sexo').val() == '0') {
        $('#titulo_sexo i').remove();
      }
      else if ($('#titulo_sexo i').length == 0) {
        $('#titulo_sexo').append("<i class='fas fa-filter color-verde inline' title='Este filtro está activo'></i>");
      }
      // Filtro de carrera:
      if ($('#filtro_carrera').val() == '0') {
        $('#titulo_carrera i').remove();
      }
      else if ($('#titulo_carrera i').length == 0) {
        $('#titulo_carrera').append("<i class='fas fa-filter color-verde inline' title='Este filtro está activo'></i>");
      }
      // Filtro de estatus:
      if ($('#filtro_estatus').val() == '0') {
        $('#titulo_estatus i').remove();
      }
      else if ($('#titulo_estatus i').length == 0) {
        $('#titulo_estatus').append("<i class='fas fa-filter color-verde inline' title='Este filtro está activo'></i>");
      }
      // Actualizar espacio de tabla:
      $('#database table').hide();
      $('#empty').hide();
      $('#waiting').hide();
      $('#ajaxerror').hide();
      $('#loader').show();
      // Ejecutar consulta AJAX:
      clearTimeout(timeoutID);
      timeoutID = setTimeout(function() {
          $.ajax({
            url: 'busqueda_usuarios.php',
            data: {
              cadena: $('#busqueda').val(),
              consulta: 'sql',
              filtro_generacion: $('#filtro_generacion').val(),
              filtro_sexo: $('#filtro_sexo').val(),
              filtro_carrera: $('#filtro_carrera').val(),
              filtro_estatus: $('#filtro_estatus').val()
            },
            dataType: 'json'
          }).done( function(data) {
            $('#exportar_mails textarea').text(data['mails']);
            $('#loader').hide();
            $('#ajaxerror').hide();
            if (data['salida'] == '0') {
              $('#empty').show();
            }
            else {
              // Modificar tabla:
              $('#database table').remove();
              $('#database').append(data['salida']);
            }
            }).fail( function() {
              $('#loader').hide();
              $('#ajaxerror').show();
            });
        //}
      }, 1000);
    }

    $('#busqueda').keyup(ejecutar_consulta);
    $('#filtro_generacion').on('selectmenuchange', ejecutar_consulta);
    $('#filtro_sexo').on('selectmenuchange', ejecutar_consulta);
    $('#filtro_carrera').on('selectmenuchange', ejecutar_consulta);
    $('#filtro_estatus').on('selectmenuchange', ejecutar_consulta);

  </script>

<?php } else { ?>
  <div class='card-important'>
    <h2>Acceso prohibido</h2>
    <p>No tienes permisos para acceder a esta sección.</p>
    <p>Si crees que estás viendo este mensaje por error por favor contacta al administrador del sistema.</p>
  </div>
<?php } ?>
