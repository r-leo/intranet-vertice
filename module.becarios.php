<?php /*
Archivo fuente de módulo
------------------------
* En este script hay acceso a todas las variables y funciones globales disponibles en intranet.php.
*/ ?>

<div class='responsive_hide'>
  <div class='navegador'>Estás en</div>
  <div class='tag clickable' onclick="window.location='intranet.php';">Inicio</div>
  <div class='navegador'><i class='fas fa-chevron-right inline'></i></div>
  <div class='tag'>Becarios</div>
</div>

<?php if ($_SESSION['estatus'] == 'coordinador') {

  // Señal: activar_acceso:
  if ($_POST['flag'] === 'activar_acceso') {
    $sql = "UPDATE ajustes SET becarios_aplicacion='1' WHERE 1";
    mensaje($sql, 'El acceso a la aplicación remota ha sido habilitado.');
  }

  // Señal: desactivar_acceso:
  if ($_POST['flag'] === 'desactivar_acceso') {
    $sql = "UPDATE ajustes SET becarios_aplicacion='0' WHERE 1";
    mensaje($sql, 'El acceso a la aplicación remota ha sido deshabilitado.');
  }

  // Señal: actualizar_clave:
  if ($_POST['flag'] === 'actualizar_clave') {
    $sql = "UPDATE ajustes SET clave_becarios='{$_POST['nueva_clave']}' WHERE 1";
    mensaje($sql, 'Clave de acceso actualizada.');
  }

  // Redefinir el objeto de ajustes (esto debe estar al final de todas las señales para evitar sobreescritura):
  $ajustes=$conexion->query("SELECT * FROM ajustes")->fetch_assoc();
  ?>

  <div class='card'>
    <h2>Aplicación remota</h2>
    <div class='accordion'>

      <!-- Acceso a la aplicación remota -->
      <h3>Acceso a la aplicación remota</h3>
      <div>
        <?php
          $becarios_aplicacion = $conexion->query("SELECT becarios_aplicacion FROM ajustes WHERE 1")->fetch_assoc()['becarios_aplicacion'];
          $clave_becarios = $conexion->query("SELECT clave_becarios FROM ajustes WHERE 1")->fetch_assoc()['clave_becarios'];
          if ($becarios_aplicacion == '1') { ?>
            <p>Acceso a la aplicación: <span class='color-verde'>activado</span></p>
            <form method='post' action='?d=becarios'>
              <input type='hidden' name='flag' value='desactivar_acceso'>
              <input type='submit' value='Desactivar acceso' class='boton_inline'>
            </form>
          <?php }
          else { ?>
            <p>Acceso a la aplicación: <span class='color-rojo'>desactivado</span></p>
            <form method='post' action='?d=becarios'>
              <input type='hidden' name='flag' value='activar_acceso'>
              <input type='submit' value='Activar acceso' class='boton_inline'>
            </form>
          <?php }
        ?>
      </div>

      <!-- Clave de acceso -->
      <h3>Clave de acceso</h3>
      <div>
        <p>Clave de acceso a la aplicación remota:</p>
        <form method='post' action='?d=becarios'>
          <input type='text' name='nueva_clave' value='<?php echo $clave_becarios; ?>'>
          <input type='hidden' name='flag' value='actualizar_clave'>
          <input type='submit' value='Actualizar clave' class='boton_inline'>
        </form>
      </div>
    </div>
  </div>

  <div class='card'>
    <?php
      $registros_huerfanos_totales = $conexion->query("SELECT becarios_asistencias.id FROM becarios_asistencias WHERE NOT EXISTS(SELECT 1 FROM becarios WHERE becarios.id=becarios_asistencias.id)")->num_rows;
      $registros_huerfanos_unicos = $conexion->query("SELECT DISTINCT becarios_asistencias.id FROM becarios_asistencias WHERE NOT EXISTS(SELECT 1 FROM becarios WHERE becarios.id=becarios_asistencias.id)")->num_rows;
      $numero_becarios = $conexion->query("SELECT id FROM becarios")->num_rows - $registros_huerfanos_unicos;
      $numero_asistencias = $conexion->query("SELECT id FROM becarios_asistencias")->num_rows + $conexion->query("SELECT SUM(asistencias) AS asistencias FROM becarios WHERE 1")->fetch_assoc()['asistencias'] - $registros_huerfanos_totales;
      if ($numero_becarios == 0) {
        $promedio_asistencias = 0;
      }
      else {
        $promedio_asistencias = $numero_asistencias / $numero_becarios;
      }
    ?>
    <h2>Indicadores</h2>
    <table class='tabla ajustada'>
      <tr>
        <th>Indicador</th>
        <th>Valor</th>
      </tr>
      <tr>
        <td>Registros huérfanos</td>
        <td><?php echo $registros_huerfanos_totales; ?> totales / <?php echo $registros_huerfanos_unicos; ?> únicos</td>
      </tr>
      <tr>
        <td>Número total de asistencias registradas</td>
        <td><?php echo $numero_asistencias; ?></td>
      </tr>
      <tr>
        <td>Número de becarios con asistencias registradas</td>
        <td><?php echo $numero_becarios; ?></td>
      </tr>
      <tr>
        <td>Asistencias promedio por becario</td>
        <td><?php echo round($promedio_asistencias, 2); ?></td>
      </tr>
    </table>
  </div>

  <div class='card'>
    <h2>Lista de becarios</h2>
    <?php $becarios = $conexion->query("SELECT * FROM becarios");
    if ($becarios->num_rows > 0) { ?>
      <table class='tabla'>
        <tr>
          <th>Expediente</th>
          <th>Nombre</th>
          <th>Última asistencia</th>
          <th>Número de asistencias</th>
        </tr>
        <?php while ($becario = $becarios->fetch_assoc()) {
          $becario_fechas = $conexion->query("SELECT MAX(fecha) AS fecha_max, COUNT(id) AS asistencias FROM becarios_asistencias WHERE id='{$becario['id']}'")->fetch_assoc(); ?>
          <tr>
            <td><?php echo $becario['id']; ?></td>
            <td><?php echo $becario['nombre'] . ' ' . $becario['apellido_p'] . ' ' . $becario['apellido_m']; ?></td>
            <td><?php echo $becario_fechas['fecha_max']; ?></td>
            <td><?php echo $becario_fechas['asistencias'] + $becario['asistencias']; ?></td>
          </tr>
        <?php } ?>
        </table>
    <?php }
    else { ?>
      <p>No hay registros disponibles.</p>
    <?php } ?>
  </div>

<?php } else { ?>
  <div class='card-important'>
    <h2>Acceso prohibido</h2>
    <p>No tienes permisos para acceder a esta sección.</p>
    <p>Si crees que estás viendo este mensaje por error por favor contacta al administrador del sistema.</p>
  </div>
<?php } ?>
