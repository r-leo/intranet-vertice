<?php /*
Archivo fuente de módulo
------------------------
* En este script hay acceso a todas las variables y funciones globales disponibles en intranet.php.
*/ ?>

<?php
  $registro_valido = true;
  $actualizacion_imagen = '0';

  if ($_POST['flag'] === 'registrar') {

  // Verificar que el registro sea correcto:
  $errores = array();
  if ($_POST['actualizacion_imagen'] === '0') {
    $registro_valido = false;
    array_push($errores, "Olvidaste subir una imagen de perfil.");
  }
  else {
    $actualizacion_imagen = '1';
    $sufijo = $conexion->query("SELECT sufijo FROM usuarios WHERE id='{$_SESSION['id']}'")->fetch_assoc()['sufijo'];
  }
  if ($_POST['nombre'] == '') {
    $registro_valido = false;
    array_push($errores, "Dejaste tu nombre en blanco.");
  }
  if ($_POST['apellido_p'] == '') {
    $registro_valido = false;
    array_push($errores, "Dejaste tu apellido paterno en blanco.");
  }
  if ($_POST['apellido_m'] == '') {
    $registro_valido = false;
    array_push($errores, "Dejaste tu apellido materno en blanco.");
  }
  if ($_POST['correo'] == '') {
    $registro_valido = false;
    array_push($errores, "Dejaste tu dirección de correo electrónico en blanco.");
  }
  elseif (substr_count($_POST['correo'], '@') <> 1 || substr_count($_POST['correo'], '.') === 0) {
    $registro_valido = false;
    array_push($errores, "La dirección de correo electrónico que proporcionaste no es válida.");
  }
  if ($_POST['fecha_nac'] == '') {
    $registro_valido = false;
    array_push($errores, "Dejaste tu fecha de nacimiento en blanco.");
  }
  elseif (date('Y-m-d') <= $_POST['fecha_nac']) {
    $registro_valido = false;
    array_push($errores, "¡No puedes estar en el Programa si todavía no has nacido! Revisa tu fecha de nacimiento.");
  }
  elseif (date("Y-m-d", strtotime("-15 years", time())) <= $_POST['fecha_nac']) {
    $registro_valido = false;
    array_push($errores, "¡No te ves tan joven! Revisa tu fecha de nacimiento.");
  }
  if ($_POST['nombre_usuario'] === '') {
    $registro_valido = false;
    array_push($errores, "Dejaste el nombre de usuario en blanco.");
  }
  elseif ($conexion->query("SELECT * from usuarios WHERE usuario='{$_POST['nombre_usuario']}'")->num_rows > 0) {
    $registro_valido = false;
    array_push($errores, "Tu nombre de usuario ya existe en la base de datos. Por favor elige otro.");
  }
  if ($_POST['password'] == '') {
    $registro_valido = false;
    array_push($errores, "Dejaste la contraseña en blanco.");
  }

  // Registrar al usuario:
  if ($registro_valido) {
    // Registrar en la base de datos:
    $conexion->query("UPDATE usuarios SET usuario='{$_POST['nombre_usuario']}', password='{$_POST['password']}', nombre='{$_POST['nombre']}', apellido_p='{$_POST['apellido_p']}', apellido_m='{$_POST['apellido_m']}', fecha_nac='{$_POST['fecha_nac']}', estatus='alumno', correo='{$_POST['correo']}', carrera='{$_POST['carrera']}', generacion='{$_POST['generacion']}', sexo='{$_POST['sexo']}' WHERE id='{$_SESSION['id']}'");
    $crop_nombre_archivo = $_SESSION['id'] . '_' . $sufijo;
    $crop_punto_0 = $_POST['imagen_crop0'];
    $crop_punto_1 = $_POST['imagen_crop1'];
    $crop_punto_2 = $_POST['imagen_crop2'];
    $crop_punto_3 = $_POST['imagen_crop3'];
    require 'crop.php';
    ?>
    <div class='card'>
      <h2>¡Bienvenido al Intranet Vértice</h2>
      <p>Tu información ha sido guardada correctamente.</p>
      <p>Por favor <a href='salir.php'>vuelve a la página de inicio de sesión</a> para entrar al sistema con el nombre de usuario y la contraseña que elegiste.</p>
      <p>Si tienes problemas para entrar ponte en contacto con los coordinadores del Programa.</p>
    </div>
  <?php }
  else { ?>
    <div class='card-important dialog'>
      <h2>Ha habido un problema con tu registro</h2>
      <?php if (count($errores) > 1) { ?>
        <p>El sistema ha detectado <?php echo count($errores); ?> errores en tu registro:</p>
        <ol>
          <?php for ($x = 0; $x < count($errores); $x++) { ?>
            <li><?php echo $errores[$x]; ?></li>
          <?php } ?>
        </ol>
      <?php }
      else { ?>
        <p>El sistema ha detectado el siguiente error en tu registro:</p>
        <p><?php echo $errores[0]; ?></p>
      <?php } ?>
    </div>
  <?php }
}

if ($_POST['flag'] <> 'registrar' || ($_POST['flag'] === 'registrar' && !$registro_valido)) { ?>

  <?php if ($registro_valido === true) { ?>
    <div class='card-important dialog'>
      <h2>Antes de empezar...</h2>
      <p>¿Tu número de expediente es <?php echo $_SESSION['id']; ?>?</p>
      <p>Si no lo es, por favor <a href='salir.php'>sal del sistema</a> y vuelve a ingresar con tu expediente correcto.</p>
    </div>
  <?php } ?>

  <div class='card'>
    <h2>¡Bienvenido al Intranet Vértice!</h2>
    <p>Para tener acceso completo al sistema es necesario que te registres llenando la información siguiente.</p>
    <p class='color-rojo'>Por favor llena todos los campos y asegúrate que la información proporcionada sea correcta. Estos datos conformarán tu perfil oficial como alumno del Programa.</p>
    <form method='POST' action='intranet.php' enctype='multipart/form-data'>

      <fieldset>
        <legend>Imagen de perfil</legend>
        <p>Sube una imagen donde aparezcas claramente. Esta imagen será parte de tu perfil (visible sólo para los coordinadores) y podrás cambiarla en cualquier momento. <span class='hint' title='Puedes subir imágenes con las extensiones: .jpg, .jpeg, .bmp, .png y .gif.'>Consulta los formatos de archivo admitidos</span></p>
        <p>Una vez subida, puedes modificar el zoom y el área de recorte de tu imagen.</p>
        <input type='file' name='archivo' id='archivo' accept='.png,.jpg,.jpeg,.bmp,.gif'>
        <br>
        <div id='imagen_perfil_texto'>
          <div>
            <p id='ajax_imagen'>Ninguna imagen seleccionada</p>
          </div>
        </div>
        <div id='imagen_perfil_container'></div>
        <br><br><br>
        <div class='center'>
          <label class='boton_inline' for='archivo'>Elegir archivo...</label>
        </div>
      </fieldset>

      <fieldset>
        <legend>Información personal</legend>
        <label for='nombre'>Nombre(s)</label>
        <input type='text' name='nombre' <?php if (!$registro_valido) { echo "value='{$_POST['nombre']}'"; }?>>
        <label for='apellido_p'>Apellido paterno</label>
        <input type='text' name='apellido_p' <?php if (!$registro_valido) { echo "value='{$_POST['apellido_p']}'"; }?>>
        <label for='apellido_m'>Apellido materno</label>
        <input type='text' name='apellido_m' <?php if (!$registro_valido) { echo "value='{$_POST['apellido_m']}'"; }?>>
        <label for='correo'>Correo electrónico</label>
        <input type='text' name='correo' <?php if (!$registro_valido) { echo "value='{$_POST['correo']}'"; }?>>
        <label for='fecha_nac'>Fecha de nacimiento</label>
        <input type='text' name='fecha_nac' class='onlydatepicker' <?php if (!$registro_valido) { echo "value='{$_POST['fecha_nac']}'"; }?>>
        <label for='sexo'>Sexo</label>
        <select class='selectmenu' name='sexo'>
          <option value='mujer' <?php if (!$registro_valido) { if ($_POST['sexo'] === 'mujer') { echo 'selected';} }?>>Mujer</option>
          <option value='hombre' <?php if (!$registro_valido) { if ($_POST['sexo'] === 'hombre') { echo 'selected';} }?>>Hombre</option>
        </select>
      </fieldset>

      <fieldset>
        <legend>Información académica</legend>
        <label for='carrera'>¿En qué carrera estás?</label>
        <select class='selectmenu' name='carrera'>
          <?php $carreras = $conexion->query("SELECT * FROM carreras ORDER BY carrera");
          while ($carrera = $carreras->fetch_assoc()) { ?>
            <option value='<?php echo $carrera["carrera"]; ?>' <?php if (!$registro_valido) { if ($carrera['carrera'] === $_POST['carrera']) { echo 'selected';}}?>><?php echo $carrera['carrera']; ?></option>
          <?php } ?>
        </select>
      </fieldset>

      <fieldset>
        <legend>Perfil como alumno Vértice</legend>
        <label for='generacion'>¿A qué generación de Vértice perteneces?</label>
        <?php $generacion = $conexion->query("SELECT generacion FROM usuarios WHERE id='{$_SESSION['id']}'")->fetch_assoc()['generacion']; ?>
        <select class='selectmenu' name='generacion' <?php if ($generacion !== '0') { echo 'disabled'; } ?>>
          <option value='0' <?php if ($generacion == '0') { echo 'selected'; }?>>Sin generación</option>
          <?php
          $generaciones = $conexion->query("SELECT generacion FROM generaciones");
          while ($gen = $generaciones->fetch_assoc()) { ?>
            <option value='<?php echo $gen['generacion']; ?>' <?php if ($gen['generacion'] == $generacion) { echo 'selected'; } ?>>Generación <?php echo $gen['generacion']; ?></option>
          <?php } ?>
        </select>
        <?php if ($generacion !== '0') { ?>
            <input type='hidden' name='generacion' value='<?php echo $generacion; ?>'>
        <?php } ?>
        <br><br>
        <p>Elige un nombre de usuario y una contraseña para entrar al sistema:</p>
        <label for='nombre_usuario'>Nombre de usuario</label>
        <input type='text' name='nombre_usuario' id='nombre_usuario' autocomplete='off' <?php if (!$registro_valido) { echo "value='{$_POST['nombre_usuario']}'"; }?>>
        <p id='disponibilidad'></p>
        <label for='password'>Contraseña</label>
        <input type='password' name='password'>

        <script>
          var timeoutID = null;
          var flag_usuario = '';

          function ejecutar_consulta() {
            $('#disponibilidad').removeClass('color-rojo');
            $('#disponibilidad').removeClass('color-verde');
            $('#disponibilidad').text('Verificando disponibilidad...');
            clearTimeout(timeoutID);
            timeoutID = setTimeout(function() {
              $.ajax({
                url: 'verificar_usuario.php',
                data: {usuario: $('#nombre_usuario').val()},
              }).done( function(data) {
                if (data == 0) {
                  $('#disponibilidad').removeClass('color-rojo');
                  $('#disponibilidad').addClass('color-verde');
                  $('#disponibilidad').text('Este nombre de usuario está disponible.');
                }
                else {
                  $('#disponibilidad').removeClass('color-verde');
                  $('#disponibilidad').addClass('color-rojo');
                  $('#disponibilidad').text('Este nombre de usuario no está disponible. Por favor elige otro.');
                }
              });
            }, 1000);
          }

          $('#nombre_usuario').keydown(function() {
            flag_usuario = $('#nombre_usuario').val();
          });

          $('#nombre_usuario').keyup(function() {
            if ($('#nombre_usuario').val() == '') {
              $('#disponibilidad').text('');
            }
            else if ($('#nombre_usuario').val() !== flag_usuario) {
              ejecutar_consulta();
            }
          });
        </script>

      </fieldset>

      <hr>
      <p class='color-rojo'>Por favor llena todos los campos y asegúrate que la información proporcionada sea correcta. Estos datos conformarán tu perfil oficial como alumno del Programa.</p>
      <input type='hidden' name='flag' value='registrar'>
      <input type='hidden' name='actualizacion_imagen' id='actualizacion_imagen' value='0'>
      <input type='hidden' name='imagen_crop0'>
      <input type='hidden' name='imagen_crop1'>
      <input type='hidden' name='imagen_crop2'>
      <input type='hidden' name='imagen_crop3'>
      <input type='submit' value='Guardar' class='boton'>
      <div class='aclarador'></div>
    </form>
  </div>

  <script>
    $('#imagen_perfil_container').croppie({
      viewport: {
        height: 250,
        width: 250
      }
    });

    if (<?php echo $actualizacion_imagen; ?> == '1') {
      $('#imagen_perfil_texto').hide();
      $('#imagen_perfil_container').show();
      $('#imagen_perfil_container').croppie('bind', {
        url: 'perfiles/<?php echo $_SESSION['id']; ?>_<?php echo $sufijo; ?>.png?d=' + Date.now(),
        points: [<?php echo $_POST['imagen_crop0']; ?>, <?php echo $_POST['imagen_crop1']; ?>, <?php echo $_POST['imagen_crop2']; ?>, <?php echo $_POST['imagen_crop3']; ?>]
      });
      $('#actualizacion_imagen').val('1');
      link_cropping_points();
    }

    function link_cropping_points() {
      $('#imagen_perfil_container').on('update', function(){
        $('[name=imagen_crop0]').val($('#imagen_perfil_container').croppie('get')['points'][0]);
        $('[name=imagen_crop1]').val($('#imagen_perfil_container').croppie('get')['points'][1]);
        $('[name=imagen_crop2]').val($('#imagen_perfil_container').croppie('get')['points'][2]);
        $('[name=imagen_crop3]').val($('#imagen_perfil_container').croppie('get')['points'][3]);
      });
    }

    $('[name=archivo]').on('change', function(event) {
      $('#actualizacion_imagen').val('0');
      $('#imagen_perfil_container').hide();
      $('#imagen_perfil_texto').show();
      var file_data = $('[name=archivo]').prop('files')[0];
      var form_data = new FormData();
      form_data.append('file', file_data);
      form_data.append('id', '<?php echo $_SESSION['id']; ?>');
      if (typeof file_data != 'undefined') {
        var file_size = file_data['size']/1000000;
        if (file_size > 5) {
          $('#ajax_imagen').text('El tamaño del archivo supera el límite de 5 MB. Por favor elige otro.');
        }
        else {
          $('#ajax_imagen').text('Subiendo imagen...');
          $.ajax({
            url: 'upload.php',
            dataType: 'text',
            cache: false,
            contentType: false,
            processData: false,
            data: form_data,
            type: 'post'
          }).done( function(data) {
            $('#actualizacion_imagen').val('1');
            $('#imagen_perfil_texto').hide();
            $('#ajax_imagen').text('Ninguna imagen seleccionada.');
            $('#imagen_perfil_container').show();
            $('#imagen_perfil_container').croppie('bind', {
              url: 'perfiles/' + data + '.png?d=' + Date.now(),
            });
            link_cropping_points();
          }).fail( function() {
            $('#ajax_imagen').text('Hubo un error en el servidor. Trata de nuevo, y si sigues teniendo poblemas contacta a un coordinador.');
          });
        }
      }
      else {
        $('#ajax_imagen').text('Ninguna imagen seleccionada.');
      }
    });
  </script>

<?php } ?>
