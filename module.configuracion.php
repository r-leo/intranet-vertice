<?php /*
Archivo fuente de módulo
------------------------
* En este script hay acceso a todas las variables y funciones globales disponibles en intranet.php.
*/ ?>

<div class='responsive_hide'>
  <div class='navegador'>Estás en</div>
  <div class='tag clickable' onclick="window.location='intranet.php';">Inicio</div>
  <div class='navegador'><i class='fas fa-chevron-right inline'></i></div>
  <div class='tag'>Configuración</div>
</div>

<?php if ($_SESSION['estatus'] == 'coordinador') {

  // Señal: agregar_carrera:
  if ($_POST['flag'] === 'agregar_carrera') {
    $sql = "INSERT INTO carreras (carrera) VALUES ('{$_POST['carrera']}')";
    mensaje($sql, 'Carrera agregada correctamente');
  }

  // Señal: eliminar_carrera:
  if ($_POST['flag'] === 'eliminar_carrera') {
    $sql = "DELETE FROM carreras WHERE carrera='{$_POST['carrera']}'";
    mensaje($sql, 'Carrera eliminada correctamente');
  }

  // Señal: agregar_generacion:
  if ($_POST['flag'] === 'agregar_generacion') {
    $exito = FALSE;
    $clave_periodo_maximo = $conexion->query("SELECT MAX(clave) AS clave FROM periodos")->fetch_assoc()['clave'];
    $periodo_maximo = $conexion->query("SELECT semestre, ano FROM periodos WHERE clave='$clave_periodo_maximo'")->fetch_assoc();
    $clave_periodo_minimo = $conexion->query("SELECT MIN(clave) AS clave FROM periodos")->fetch_assoc()['clave'];
    $periodo_minimo = $conexion->query("SELECT semestre, ano FROM periodos WHERE clave='$clave_periodo_minimo'")->fetch_assoc();
    $duracion = $conexion->query("SELECT duracion FROM planes WHERE id='{$_POST['plan']}'")->fetch_assoc()['duracion'];
    $periodo_inicial = $conexion->query("SELECT clave FROM periodos WHERE semestre='{$_POST['semestre_inicial']}' AND ano='{$_POST['ano_inicial']}'")->fetch_assoc()['clave'];
    if (periodo_mayor(array($periodo_minimo['semestre'], $periodo_minimo['ano']), array($_POST['semestre_inicial'], $_POST['ano_inicial'])) % 2 == 0) {
      $periodo_indice = array($periodo_maximo['semestre'], $periodo_maximo['ano']);
      $periodo_final = periodo_suma(array($_POST['semestre_inicial'], $_POST['ano_inicial']), $duracion);
      while (periodo_mayor($periodo_indice, $periodo_final) === 2) {
        $clave_periodo_max = $conexion->query("SELECT MAX(clave) AS clave FROM periodos")->fetch_assoc()['clave'];
        $periodo_max = $conexion->query("SELECT semestre, ano FROM periodos WHERE clave='$clave_periodo_max'")->fetch_assoc();
        $periodo_indice = periodo_siguiente($periodo_indice);
        if (periodo_mayor($periodo_indice, array($periodo_max['semestre'], $periodo_max['ano'])) === 1) {
          $conexion->query("INSERT INTO periodos (semestre, ano, activo) VALUES ('$periodo_indice[0]', '$periodo_indice[1]', '0')");
        }
      }
      $exito = TRUE;
    }
    else {
      mensaje_error('No se puede agregar a la generación. La fecha de inicio es anterior a la fecha inicial del sistema.');
    }
    // Registrar generación:
    if ($exito) {
      $sql = "INSERT INTO generaciones (generacion, plan, periodo_inicial) VALUES ('{$_POST['generacion']}', '{$_POST['plan']}', '$periodo_inicial')";
      mensaje($sql, 'Generación agregada correctamente.');
    }
  }

  // Señal: eliminar_generacion:
  if ($_POST['flag'] === 'eliminar_generacion') {
    $sql = "DELETE FROM generaciones WHERE generacion='{$_POST['generacion']}'";
    mensaje($sql, 'Generación eliminada correctamente');
  }

  // Señal: actualizar_mensaje:
  if ($_POST['flag'] === 'actualizar_mensaje') {
    $conexion->query("UPDATE ajustes SET mensaje_titulo='{$_POST['mensaje_titulo']}', mensaje='{$_POST['mensaje']}'");
  }

  // Redefinir el objeto de ajustes (esto debe estar al final de todas las señales para evitar sobreescritura):
  $ajustes=$conexion->query("SELECT * FROM ajustes")->fetch_assoc();
  ?>

  <div class='card'>
    <h2>General</h2>
    <div class='accordion'>

      <h3>Mensaje de los coordinadores</h3>
      <div>
        <form method='POST' action='?d=configuracion'>
          <fieldset>
            <legend>Mostrar/ocultar mensaje de los coordinadores</legend>
            <label for='estatus_mensaje'>Mostrar mensaje de los coordinadores</label>
            <input type='checkbox' name='estatus_mensaje' id='estatus_mensaje'>
            <?php if ($ajustes['mensaje_activo'] === '1') {
              $foo = "$('#estatus_mensaje').prop('checked', true);$('#estatus_mensaje').checkboxradio('refresh');";
              echo "<script>$(document).ready(function() {{$foo}});</script>";
            } ?>
          </fieldset>
          <fieldset id='configuracion_mensaje'>
            <legend>Mensaje de los coordinadores</legend>
            <label for='mensaje_titulo'>Título del mensaje</label>
            <input type='text' name='mensaje_titulo' value='<?php echo $ajustes["mensaje_titulo"]; ?>'>
            <label for='mensaje'>Mensaje</label>
            <textarea name='mensaje' rows=10><?php echo $ajustes['mensaje']; ?></textarea>
            <input type='hidden' name='flag' value='actualizar_mensaje'>
            <input type='submit' value='Guardar' class='boton'>
            <div class='aclarador'></div>
          </fieldset>
        </form>
      </div>

      <h3>Registro de carreras</h3>
      <div>
        <p>En esta sección se administran las licenciaturas disponibles para que el alumno seleccione al darse de alta en el Programa. Esta lista de carreras únicamente se muestra cuando el alumno se registra por primera vez. Agregar o eliminar una carrera no modifica el perfil de ningún alumno.</p>
        <table class='tabla'>
          <tr><th>Nombre de la licenciatura</th><th>Eliminar</th></tr>
          <?php $carreras = $conexion->query("SELECT * FROM carreras ORDER BY carrera");
          while ($carrera = $carreras->fetch_assoc()) { ?>
            <tr><td><?php echo $carrera['carrera']; ?></td><td><form method='POST' action='?d=configuracion'><input type='hidden' name='flag' value='eliminar_carrera'><input type='hidden' name='carrera' value='<?php echo $carrera["carrera"]; ?>'><input type='submit' value='Eliminar' class='boton_inline'></form></td></tr>
          <?php }
          ?>
        </table>
        <hr>
        <form method='POST' action='?d=configuracion'>
          <label for='carrera'>Agregar una nueva carrera:</label><input type='text' name='carrera'>
          <input type='hidden' name='flag' value='agregar_carrera'>
          <input type='submit' value='Agregar' class='boton'><div class='aclarador'></div>
        </form>
      </div>

      <h3>Registro de generaciones y planes curriculares</h3>
      <div>
        <p>En esta sección se administran las generaciones del Programa y se le asigna a cada una el plan curricular correspondiente.</p>
        <table class='tabla'>
          <tr><th>Generación</th><th>Plan curricular</th><th>Eliminar</th></tr>
          <?php $generaciones = $conexion->query("SELECT * FROM generaciones");
          $nueva_generacion = $conexion->query("SELECT MAX(generacion) AS nueva_generacion FROM generaciones")->fetch_assoc();
          $nueva_generacion = $nueva_generacion['nueva_generacion'] + 1;
          while ($generacion = $generaciones->fetch_assoc()) {
            $plan = $conexion->query("SELECT nombre FROM planes WHERE id='{$generacion['plan']}'")->fetch_assoc(); ?>
            <tr><td><?php echo 'Generación '. $generacion['generacion']; ?></td><td><?php echo $plan['nombre']; ?></td><td><form method='POST' action='?d=configuracion'><input type='hidden' name='flag' value='eliminar_generacion'><input type='hidden' name='generacion' value='<?php echo $generacion["generacion"]; ?>'><input type='submit' value='Eliminar' class='boton_inline'></form></td></tr>
          <?php }
          ?>
        </table>

        <form method='POST' action='?d=configuracion'>
          <fieldset>
            <legend>Dar de alta a la generación <?php echo $nueva_generacion; ?></legend>
            <p>Para dar de alta a la generación <?php echo $nueva_generacion; ?>, selecciona el plan curricular y el periodo inicial que le corresponderá y da clic en "Dar de alta".</p>
            <label for='plan'>Plan curricular</label>
            <select class='selectmenu' name='plan'>
              <?php
                $planes = $conexion->query("SELECT * FROM planes");
                while ($plan = $planes->fetch_assoc()) { ?>
                  <option value="<?php echo $plan['id']; ?>"><?php echo $plan['nombre']; ?></option>
                <?php } ?>
            </select>
            <br><br>
            <?php
              $ano_actual = $conexion->query("SELECT ano FROM periodos WHERE activo='1'")->fetch_assoc()['ano'];
              $semestre_actual = $conexion->query("SELECT semestre FROM periodos WHERE activo='1'")->fetch_assoc()['semestre'];
            ?>
            <label for='semestre_inicial'>Periodo inicial</label>
            <select class='selectmenu' name='semestre_inicial'>
              <option value='1' <?php if ($semestre_actual == '1'){ echo 'selected';} ?>>Enero-junio</option>
              <option value='2' <?php if ($semestre_actual == '2'){ echo 'selected';} ?>>Agosto-diciembre</option>
            </select>
            <select class='selectmenu' name='ano_inicial'>
              <?php for ($i = -4; $i <= 4; $i++) { ?>
                <option value='<?php echo $ano_actual+$i; ?>' <?php if ($i == 0){ echo 'selected';} ?>><?php echo $ano_actual+$i; ?></option>
              <?php } ?>
            </select>
            <br><br>
            <input type='hidden' name='flag' value='agregar_generacion'>
            <input type='hidden' name='generacion' value='<?php echo $nueva_generacion; ?>'>
            <input type='submit' value='Dar de alta la generación <?php echo $nueva_generacion; ?>' class='boton'>
            <div class='aclarador'></div>
          </fieldset>
        </form>
      </div>

      <h3>Copia de seguridad de la base de datos</h3>
      <div>
        <p>En esta sección se puede descargar una copia de la base de datos del sistema. Conviene guardarla periódicamente para poder restablecerla en caso de un daño en el servidor.</p>
        <p>El nombre del archivo descargado contiene la fecha y la hora del último cambio a la base de datos.</p>
        <a target='_blank' href='exportar_bd.php'><p class='boton'>Descargar en formato SQL</p></a>
        <div class='aclarador'></div>
      </div>

    </div>
  </div>

  <div class='card'>
    <h2>Analíticas locales</h2>
    <?php $ultimo_acceso = $conexion->query("SELECT MAX(t_s) AS ultimo_acceso FROM registro_login")->fetch_assoc()['ultimo_acceso'];
    $diferencia = abs(time() - strtotime($ultimo_acceso));
    if ($diferencia < 60) {
      $diferencia_texto = 'en este momento';
    }
    elseif ($diferencia < 3600) {
      $diferencia_texto = 'hace ' . ceil($diferencia/60) . ' minutos';
    }
    elseif ($diferencia < 86400) {
      $diferencia_texto = 'hace ' . ceil($diferencia/3600) . ' horas';
    }
    else {
      $diferencia_texto = 'hace ' . ceil($diferencia/86400) . ' días';
    }
    ?>
    <p>Último acceso al sistema: <?php echo fecha_formateada($ultimo_acceso); ?> (<?php echo $diferencia_texto; ?>).</p>
    <p>Las siguientes listas corresponden a los últimos cincuenta registros de personas que han intentado acceder al sistema:</p>
    <div class='accordion'>
      <h3>Últimos usuarios en línea</h3>
      <div>
        <?php $registro_login = $conexion->query("SELECT * FROM registro_login WHERE estatus='1' ORDER BY t_s DESC");
        if ($registro_login->num_rows > 0) { ?>
          <table class='tabla'>
            <tr><th>Nombre</th><th>Fecha/hora de ingreso</th></tr>
            <?php while ($registro_login_fetched = $registro_login->fetch_assoc()) {
              $nombre_login = $conexion->query("SELECT nombre, apellido_p, apellido_m FROM usuarios WHERE id='{$registro_login_fetched['id']}'")->fetch_assoc(); ?>
              <tr><td><?php echo $nombre_login['nombre'] . ' ' . $nombre_login['apellido_p'] . ' ' . $nombre_login['apellido_m']; ?></td><td><?php echo fecha_formateada($registro_login_fetched['t_s']); ?></td></tr>
            <?php } ?>
          </table>
        <?php }
        else { ?>
          <p>No hay registros.</p>
        <?php } ?>
      </div>
      <h3>Últimos intentos de acceso fallidos</h3>
      <div>
        <?php $registro_login_fail = $conexion->query("SELECT * FROM registro_login WHERE estatus='0' ORDER BY t_s DESC");
        if ($registro_login_fail->num_rows > 0) { ?>
          <table class='tabla'>
            <tr><th>Usuario</th><th>Nombre</th><th>Fecha/hora de ingreso</th></tr>
            <?php while ($registro_login_fail_fetched = $registro_login_fail->fetch_assoc()) {
              $nombre_login_try = $conexion->query("SELECT nombre, apellido_p, apellido_m FROM usuarios WHERE usuario='{$registro_login_fail_fetched['usuario']}'");
              if ($nombre_login_try->num_rows > 0) {
                $nombre_login_try_fetched = $nombre_login_try->fetch_assoc();
                $nombre_login = $nombre_login_try_fetched['nombre'] . ' ' . $nombre_login_try_fetched['apellido_p'] . ' ' . $nombre_login_try_fetched['apellido_m'];
              }
              else {
                $nombre_login = '(Desconocido)';
              } ?>
              <tr><td><?php echo $registro_login_fail_fetched['usuario']; ?></td><td><?php echo $nombre_login; ?></td><td><?php echo fecha_formateada($registro_login_fail_fetched['t_s']); ?></td></tr>
            <?php } ?>
          </table>
        <?php }
        else { ?>
          <p>No hay registros.</p>
        <?php } ?>
      </div>
    </div>
    <?php // Logins exitosos/fallidos:
    $logins_1 = $conexion->query("SELECT COUNT(id) AS logins_1 FROM registro_login WHERE estatus='1'")->fetch_assoc()['logins_1'];
    $logins_0 = $conexion->query("SELECT COUNT(usuario) AS logins_0 FROM registro_login WHERE estatus='0'")->fetch_assoc()['logins_0'];
    $logins_exito = round(100 * $logins_1/($logins_1 + $logins_0), 1);
    ?>
    <p><?php if ($logins_exito >= 70) { ?>
      <i class='fas fa-check fa-fw color-verde'></i>
      <?php }
      else { ?>
        <i class='fas fa-times fa-fw color-rojo'></i><span class='bold'>Este indicador puede significar un problema potencial en el sistema:</span>&nbsp;
      <?php } ?>
      Actualmente, el <?php echo $logins_exito ?>% de los intentos de acceso al intranet son exitosos <span class='hint' title='Significa que el <?php echo 100 - $logins_exito; ?>% de los usuarios se equivocan en su usuario y/o contraseña al tratar de entrar al sistema.'>¿Qué significa esto?</span>.</p>
  </div>

<?php } else { ?>
  <div class='card-important'>
    <h2>Acceso prohibido</h2>
    <p>No tienes permisos para acceder a esta sección.</p>
    <p>Si crees que estás viendo este mensaje por error por favor contacta al administrador del sistema.</p>
  </div>
<?php } ?>
