<?php /*
Archivo fuente de módulo
------------------------
* En este script hay acceso a todas las variables y funciones globales disponibles en intranet.php.
*/ ?>

<!-- Columna central -->
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item active">Inicio</li>
  </ol>
</nav>
<h1 id="navegador_inicio" class="font-weight-light mb-4 mt-3">Hola, <?php echo $_SESSION['nombre']; ?></h1>

<?php if ($ajustes['mensaje_activo'] == 1) { ?>

  <div class="card mb-4 bg-light text-dark">
    <div class="card-body">
      <h5 class="card-title"><?php echo $ajustes['mensaje_titulo'] ?></h5>
      <p class="card-text"><?php echo $ajustes['mensaje'] ?></p>
      <?php if ($_SESSION['estatus'] === 'coordinador') { ?>
        <small>Recuerda que puedes desactivar o modificar este mensaje en <a href='?d=configuracion'>configuración</a>.</small>
      <?php } ?>
    </div>
  </div>

<?php }
$usuario = $conexion->query("SELECT * FROM usuarios WHERE id='{$_SESSION['id']}'")->fetch_assoc();
$periodo_activo = $conexion->query("SELECT * FROM periodos WHERE activo='1'")->fetch_assoc();

// Vista del coordinador:
if ($_SESSION['estatus'] === 'coordinador') {
  // Logins exitosos/fallidos:
  $logins_1 = $conexion->query("SELECT COUNT(id) AS logins_1 FROM registro_login WHERE estatus='1'")->fetch_assoc()['logins_1'];
  $logins_0 = $conexion->query("SELECT COUNT(usuario) AS logins_0 FROM registro_login WHERE estatus='0'")->fetch_assoc()['logins_0'];
  $logins_exito = round(100 * $logins_1/($logins_1 + $logins_0), 1);
  // Actividades pendientes de asistencia:
  $actividades = $conexion->query("SELECT clave FROM actividades WHERE periodo='{$periodo_activo['clave']}' AND fecha_fin <= NOW()");
  $actividades_pendientes = 0;
  while ($actividad = $actividades->fetch_assoc()) {
    $asistencias = $conexion->query("SELECT SUM(asistencia) AS asistencias FROM actividades_usuarios WHERE clave='{$actividad['clave']}'")->fetch_assoc()['asistencias'];
    if ($asistencias == 0) {
      $actividades_pendientes = $actividades_pendientes + 1;
    }
  } ?>

  <h3 class="mt-5" id="navegador_estatus">Estatus del sistema</h3>

  <table class="table">
    <tbody>
      <tr>
        <td>
          Porcentaje de accesos exitosos al sistema<br>
          <small class="d-none d-md-inline">Este porcentaje indica la proporción de intentos de accesar al sistema que son exitosos. Un porcentaje menor a 70% se coloreará de rojo para indicar que un número significativo de alumnos tiene dificultades para entrar al intranet, o bien que alguien está intentando entrar a la fuerza.</small>
        </td>
        <td>
          <?php if ($logins_exito >= 70) { ?>
            <span class="badge badge-success"><?php echo $logins_exito; ?> %</span>
          <?php }
          else { ?>
            <span class="badge badge-danger"><?php echo $logins_exito; ?> %</span>
          <?php } ?>
        </td>
      </tr>
      <tr>
        <td>
          Actividades sin asistencia registrada<br>
          <small class="d-none d-md-inline">Si hay actividades cuya fecha de fin ya pasó y no tienen ninguna asistencia registrada, entonces este indicador se coloreará de rojo. Es importante registrar las asistencias a las actividades con el menor retraso posible, ya que así aumenta la confianza de los alumnos en el sistema.</small>
        </td>
        <td>
          <?php if ($actividades_pendientes > 0) { ?>
            <span class="badge badge-danger"><?php echo $actividades_pendientes; ?></span>
          <?php }
          else { ?>
            <span class="badge badge-success"><?php echo $actividades_pendientes; ?></span>
          <?php } ?>
        </td>
      </tr>
      <tr>
        <td>
          Días desde la última copia de seguridad<br>
          <small class="d-none d-md-inline">Si han pasado más de diez días desde la última copia de seguridad de la base de datos, este indicador se pondrá color rojo. Se recomienda descargar periódicamente una copia de seguridad de la base de datos, para poder restablecerla en caso de una falla en el servidor.</small>
        </td>
        <td>
            <span class="badge badge-warning">Pendiente</span>
        </td>
      </tr>
    </tbody>
  </table>

  <h3 class="mt-5" id="navegador_proximas_actividades">Próximas actividades</h3>
  <table class="table">
    <tbody>
      <tr>
        <td>
          12 de mayo
        </td>
        <td>
          <a href="#">ASUA por los niños</a>
        </td>
        <td>
          12 inscritos
        </td>
    </tbody>
  </table>

  <h3 class="mt-5" id="navegador_cumpleanos">Cumpleaños del mes</h3>
  <p><span class="badge badge-warning">Pendiente</span> Revisar que se sólo se muestren los cumpleaños de los usuarios con correspondencia en el periodo actual.</p>
  {@tabla_cumpleanos}
<?php }

// Vista del alumno (regular o condicionado):
elseif ($_SESSION['estatus'] === 'activo' || $_SESSION['estatus'] === 'condicionado') { ?>
  <div class='card'>
    <h2>Actividades inscritas este semestre</h2>
    <hr>
    <?php $actividades = $conexion->query("SELECT actividades.*, actividades_usuarios.asistencia FROM actividades INNER JOIN actividades_usuarios ON actividades.clave=actividades_usuarios.clave WHERE actividades_usuarios.id='{$_SESSION['id']}' AND actividades.periodo='{$periodo_activo['clave']}'");
    if ($actividades->num_rows > 0) { ?>
      <table class='tabla'>
        <tr><th>Actividad</th><th>Puntos</th><th>Fecha</th><th>Asistencia</th></tr>
      <?php foreach ($actividades as $actividad) {
        if ($actividad['asistencia'] == '1') {
          $asistencia = "<i class='fas fa-check color-verde inline' title='Asististe a esta actividad'></i>";
        }
        else {
          $asistencia = "<i class='fas fa-times color-tip inline' title='No tienes asistencia registrada'></i>";
        } ?>
        <tr>
          <td><a href='?d=actividad&clave=<?php echo $actividad['clave']; ?>'><?php echo $actividad['nombre']; ?></a></td>
          <td><?php echo $actividad['puntos']; ?></td>
          <td><?php echo fecha_formateada($actividad['fecha_inicio']); ?></td>
          <td class='center'><?php echo $asistencia; ?></td>
        </tr>
      <?php } ?>
      </table>
    <?php }
    else { ?>
      <p>No tienes actividades inscritas. Puedes encontrar las actividades de este semestre en la sección de <a href='?d=actividades'>Actividades</a>.</p>
    <?php } ?>
  </div>

  <?php $actividades_espera = $conexion->query("SELECT actividades.* FROM actividades INNER JOIN actividades_usuarios_espera ON actividades.clave=actividades_usuarios_espera.clave WHERE actividades_usuarios_espera.id='{$_SESSION['id']}' AND actividades.periodo='{$periodo_activo['clave']}'");
  if ($actividades_espera->num_rows > 0) { ?>
    <div class='card'>
      <h2>Actividades en lista de espera</h2>
      <hr>
      <p>Recuerda que todavía no estás inscrito a estas actividades. En caso que se abran lugares se te dará un lugar.</p>
      <table class='tabla'>
        <tr><th>Actividad</th><th>Puntos</th><th>Fecha</th></tr>
        <?php foreach ($actividades_espera as $actividad) { ?>
          <tr>
            <td><a href='?d=actividad&clave=<?php echo $actividad['clave']; ?>'><?php echo $actividad['nombre']; ?></a></td>
            <td><?php echo $actividad['puntos']; ?></td>
            <td><?php echo fecha_formateada($actividad['fecha_inicio']); ?></td>
          </tr>
        <?php } ?>
      </table>
    </div>
  <?php } ?>

  <div class='card'>
    <h2>Tu progreso este semestre</h2>
    <hr>
    <?php $requisitos_periodicos = $conexion->query("SELECT * FROM requisitos WHERE plan='{$_usuario->informacion['plan']}' AND tipo='2'"); ?>
    <?php
      while ($requisito_periodico = $requisitos_periodicos->fetch_assoc()) {
        $puntos_completados = $conexion->query("SELECT SUM(actividades_usuarios.asistencia*actividades.puntos) AS puntos_completados
                                                FROM actividades_usuarios
                                                INNER JOIN actividades ON actividades.clave=actividades_usuarios.clave
                                                WHERE actividades_usuarios.id='{$_usuario->informacion['id']}'
                                                AND actividades.periodo='{$periodo_activo['clave']}'
                                                AND (actividades.tipo_1='{$requisito_periodico['id']}'
                                                  OR actividades.tipo_2='{$requisito_periodico['id']}'
                                                  OR actividades.tipo_3='{$requisito_periodico['id']}'
                                                  OR actividades.tipo_4='{$requisito_periodico['id']}')")->fetch_assoc()['puntos_completados'] + 0;
        $asistencias_registradas = $conexion->query("SELECT SUM(actividades_usuarios.asistencia) AS asistencias_registradas
                                                     FROM actividades_usuarios
                                                     INNER JOIN actividades ON actividades.clave=actividades_usuarios.clave
                                                     WHERE actividades_usuarios.id='{$_usuario->informacion['id']}'
                                                     AND actividades.periodo='{$periodo_activo['clave']}'
                                                     AND (actividades.tipo_1='{$requisito_periodico['id']}'
                                                       OR actividades.tipo_2='{$requisito_periodico['id']}'
                                                       OR actividades.tipo_3='{$requisito_periodico['id']}'
                                                       OR actividades.tipo_4='{$requisito_periodico['id']}')")->fetch_assoc()['asistencias_registradas'] + 0;
        if ($puntos_completados >= $requisito_periodico['valor'] && $asistencias_registradas >= $requisito_periodico['asistencias']) { ?>
          <div class='requisito cumplido' title='Ya acreditaste este rubro'>
            <p><i class='fas fa-check color-verde fa-fw'></i><?php echo $requisito_periodico['nombre']; ?></p>
          </div>
        <?php }
        else { ?>
          <div class='requisito pendiente' title='No has acreditado este rubro'>
            <p><i class='fas fa-times color-tip fa-fw'></i><?php echo $requisito_periodico['nombre']; ?></p>
          </div>
        <?php }
      } ?>

    <div class='aclarador'></div>
    <hr>
    <p>Esta sección sólo muestra si este semestre has cubierto (<i class='fas fa-check color-verde inline'></i>) o no (<i class='fas fa-times color-tip inline'></i>) cada rubro de actividades. Para revisar tu progreso detallado visita la sección de <a href='?d=progreso'>Progreso</a>.</p>
  </div>

<?php }

elseif ($_SESSION['estatus'] === 'baja') { ?>
  <div class='card'>
    <h2>Bienvenid<?php if ($usuario['sexo'] === 'mujer'){ echo 'a';} else{echo 'o';} ?>, <?php echo $usuario['nombre']; ?></h2>
    <p>¡Parece que estás dad<?php if ($usuario['sexo'] === 'mujer'){ echo 'a';} else{echo 'o';} ?> de baja del programa!</p>
    <p>En tu estatus actual no tienes permitido ver ni inscribir actividades.</p>
    <p><span class='bold'>Importante.</span> Si crees que esto es un error por favor contacta a los coordinadores del Programa para solucionar el problema.</p>
  </div>
<?php }

elseif ($_SESSION['estatus'] === 'egresado') { ?>
  <div class='card'>
    <h2>Bienvenid<?php if ($usuario['sexo'] === 'mujer'){ echo 'a';} else{echo 'o';} ?>, <?php echo $usuario['nombre']; ?></h2>
    <p>Parece que eres un egresado del programa.</p>
  </div>
<?php } ?>
