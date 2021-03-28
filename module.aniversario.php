<?php
header('Location: index.php');
exit();
?>

<?php /*
Archivo fuente de módulo
------------------------
* En este script hay acceso a todas las variables y funciones globales disponibles en intranet.php.
*/ ?>

<div class='responsive_hide'>
  <div class='navegador'>Estás en</div>
  <div class='tag clickable' onclick="window.location='intranet.php';">Inicio</div>
  <div class='navegador'><i class='fas fa-chevron-right inline'></i></div>
  <div class='tag'>20&deg; aniversario</div>
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

  <?php
  $numero_alumnos_norte = $conexion->query("SELECT COUNT(id) AS numero_alumnos_norte FROM aniversario WHERE perfil='alumno' AND campus_alumno='norte'")->fetch_assoc()['numero_alumnos_norte'];
  $numero_alumnos_sur = $conexion->query("SELECT COUNT(id) AS numero_alumnos_sur FROM aniversario WHERE perfil='alumno' AND campus_alumno='sur'")->fetch_assoc()['numero_alumnos_sur'];
  $numero_egresados_norte = $conexion->query("SELECT COUNT(id) AS numero_egresados_norte FROM aniversario WHERE perfil='egresado' AND campus_egresado='norte'")->fetch_assoc()['numero_egresados_norte'];
  $numero_egresados_sur = $conexion->query("SELECT COUNT(id) AS numero_egresados_sur FROM aniversario WHERE perfil='egresado' AND campus_egresado='sur'")->fetch_assoc()['numero_egresados_sur'];
  $numero_invitados = $conexion->query("SELECT COUNT(id) AS numero_invitados FROM aniversario WHERE perfil='invitado'")->fetch_assoc()['numero_invitados'];
  $numero_otros = $conexion->query("SELECT COUNT(id) AS numero_otros FROM aniversario WHERE perfil='otro'")->fetch_assoc()['numero_otros'];
  $numero_total = $numero_alumnos_norte + $numero_alumnos_sur + $numero_egresados_norte + $numero_egresados_sur + $numero_invitados + $numero_otros;
  ?>

  <div class='card'>
    <h2>Resumen del registro</h2>
    <h3>Registros por categoría</h3>
    <table class="tabla ajustada">
      <tr>
        <th>Categoría</th>
        <th>Campus norte</th>
        <th>Campus sur</th>
        <th>Total general</th>
      </tr>
      <tr>
        <td>Alumnos</td>
        <td class="center"><?php echo $numero_alumnos_norte; ?></td>
        <td class="center"><?php echo $numero_alumnos_sur; ?></td>
        <td class="center"><?php echo $numero_alumnos_norte + $numero_alumnos_sur; ?></td>
      </tr>
      <tr>
        <td>Egresados</td>
        <td class="center"><?php echo $numero_egresados_norte; ?></td>
        <td class="center"><?php echo $numero_egresados_sur; ?></td>
        <td class="center"><?php echo $numero_egresados_norte + $numero_egresados_sur; ?></td>
      </tr>
      <tr>
        <td class="bold">Subtotal por campus</td>
        <td class="bold center"><?php echo $numero_alumnos_norte + $numero_egresados_norte; ?></td>
        <td class="bold center"><?php echo $numero_alumnos_sur + $numero_egresados_sur; ?></td>
        <td class="bold center"><?php echo $numero_alumnos_norte + $numero_egresados_norte + $numero_alumnos_sur + $numero_egresados_sur; ?></td>
      </tr>
      <tr>
        <td colspan="3">Invitados especiales</td>
        <td class="center"><?php echo $numero_invitados; ?></td>
      </tr>
      <tr>
        <td colspan="3">Otros</td>
        <td class="center"><?php echo $numero_otros; ?></td>
      </tr>
      <tr>
        <td colspan=3 class="bold">Total</td>
        <td class="center"><span class="bold"><?php echo $numero_total; ?></span></td>
      </tr>
    </table>
    <p></p>

    <h3>Registros por generación del campus norte</h3>
    <div class='grafico'>
      <canvas id="registros_norte"></canvas>
    </div>
    <?php
    $registros_alumnos = array();
    $registros_egresados = array();
    for ($gen = 1; $gen <= 20; $gen++) {
      $registros_a = $conexion->query("SELECT COUNT(id) AS registros_norte FROM aniversario WHERE generacion_alumno=$gen AND perfil='alumno' AND campus_alumno='norte'")->fetch_assoc()['registros_norte'];
      array_push($registros_alumnos, $registros_a);
      $registros_e = $conexion->query("SELECT COUNT(id) AS registros_norte FROM aniversario WHERE generacion_egresado=$gen AND perfil='egresado' AND campus_egresado='norte'")->fetch_assoc()['registros_norte'];
      array_push($registros_egresados, $registros_e);
    }
    ?>
    <script>
      var graficoRegistrosNorte = new Chart($('#registros_norte'), {
        type: 'bar',
        data: {
          labels: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20],
          datasets: [{
            label: 'Alumnos',
            data: <?php echo json_encode($registros_alumnos); ?>,
            backgroundColor: '#0B486B',
            borderColor: '#ffffff',
            borderWidth: 1
          },
          {
            label: 'Egresados',
            data: <?php echo json_encode($registros_egresados); ?>,
            backgroundColor: '#79BD9A',
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
            display: true
          }
        }
      });
    </script>

    <h3>Registros por generación del campus sur</h3>
    <div class='grafico'>
      <canvas id="registros_sur"></canvas>
    </div>
    <?php
    $registros_alumnos = array();
    $registros_egresados = array();
    for ($gen = 1; $gen <= 20; $gen++) {
      $registros_a = $conexion->query("SELECT COUNT(id) AS registros_sur FROM aniversario WHERE generacion_alumno=$gen AND perfil='alumno' AND campus_alumno='sur'")->fetch_assoc()['registros_sur'];
      array_push($registros_alumnos, $registros_a);
      $registros_e = $conexion->query("SELECT COUNT(id) AS registros_sur FROM aniversario WHERE generacion_egresado=$gen AND perfil='egresado' AND campus_egresado='sur'")->fetch_assoc()['registros_sur'];
      array_push($registros_egresados, $registros_e);
    }
    ?>
    <script>
      var graficoRegistrosSur = new Chart($('#registros_sur'), {
        type: 'bar',
        data: {
          labels: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20],
          datasets: [{
            label: 'Alumnos',
            data: <?php echo json_encode($registros_alumnos); ?>,
            backgroundColor: '#0B486B',
            borderColor: '#ffffff',
            borderWidth: 1
          },
          {
            label: 'Egresados',
            data: <?php echo json_encode($registros_egresados); ?>,
            backgroundColor: '#79BD9A',
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
            display: true
          }
        }
      });
    </script>
  </div>

  <div class="card">
    <h2>Registro completo</h2>
    <p>Exportar registro como archivo de Excel:</p>
    <a target='_blank' href='exportar_xlsx.php?target=20aniversario'><p class='boton_inline'>Exportar como archivo de Excel</p></a>
    <h3>Alumnos</h3>
    <?php $registro_alumnos = $conexion->query("SELECT nombre, apellido_p, apellido_m, correo, campus_alumno, generacion_alumno FROM aniversario WHERE perfil='alumno' ORDER BY campus_alumno, generacion_alumno");
    if ($registro_alumnos->num_rows > 0) {?>
      <table class="tabla">
        <tr>
          <th>Campus</th>
          <th>Generacion</th>
          <th>Nombre</th>
          <th>Correo electrónico</th>
        </tr>
        <?php while($registro_alumno = $registro_alumnos->fetch_assoc()) { ?>
          <tr>
            <td><?php echo ucfirst($registro_alumno['campus_alumno']); ?></td>
            <td><?php echo $registro_alumno['generacion_alumno']; ?></td>
            <td><?php echo $registro_alumno['nombre'] . ' ' . $registro_alumno['apellido_p'] . ' ' . $registro_alumno['apellido_m']; ?></td>
            <td><?php echo $registro_alumno['correo']; ?></td>
          </tr>
        <?php } ?>
      </table>
    <?php }
    else { ?>
      <p>No hay ningún registro en esta categoría.</p>
    <?php } ?>
    <h3>Egresados</h3>
    <?php $registro_egresados = $conexion->query("SELECT nombre, apellido_p, apellido_m, correo, campus_egresado, generacion_egresado, celular, empresa, puesto, egresado_participar FROM aniversario WHERE perfil='egresado' ORDER BY campus_egresado ASC, generacion_egresado ASC, egresado_participar DESC, nombre ASC");
    if ($registro_egresados->num_rows > 0) { ?>
      <table class="tabla">
        <tr>
          <th>Campus</th>
          <th>Generación</th>
          <th>Nombre</th>
          <th>Correo y celular</th>
          <th>Trabajo actual</th>
          <th>Consejero</th>
        </tr>
        <?php while($registro_egresado = $registro_egresados->fetch_assoc()) { ?>
          <tr>
            <td><?php echo ucfirst($registro_egresado['campus_egresado']); ?></td>
            <td><?php echo $registro_egresado['generacion_egresado']; ?></td>
            <td><?php echo $registro_egresado['nombre'] . ' ' . $registro_egresado['apellido_p'] . ' ' . $registro_egresado['apellido_m']; ?></td>
            <td><?php echo $registro_egresado['correo']; ?><br><?php echo $registro_egresado['celular']; ?></td>
            <td><?php echo $registro_egresado['empresa']; ?><br><span class="navegador"><?php echo $registro_egresado['puesto']; ?></span></td>
            <td>
              <?php if($registro_egresado['egresado_participar'] == 'participar') { ?>
                <i class='fas fa-check inline color-verde'></i>
              <?php } else { ?>
                <i class='fas fa-times inline color-gris-1'></i>
              <?php } ?>
            </td>
        <?php } ?>
      </table>
    <?php }
    else { ?>
      <p>No hay ningún registro en esta categoría.</p>
    <?php } ?>

    <h3>Invitados especiales</h3>
    <?php $registro_invitados = $conexion->query("SELECT nombre, apellido_p, apellido_m, correo FROM aniversario WHERE perfil='invitado'");
    if ($registro_invitados->num_rows > 0) { ?>
      <table class="tabla">
        <tr>
          <th>Nombre</th>
          <th>Correo electrónico</th>
        </tr>
        <?php while($registro_invitado = $registro_invitados->fetch_assoc()) { ?>
          <tr>
            <td><?php echo $registro_invitado['nombre'] . ' ' . $registro_invitado['apellido_p'] . ' '. $registro_invitado['apellido_m']; ?></td>
            <td><?php echo $registro_invitado['correo']; ?></td>
          </tr>
        <?php } ?>
      </table>
    <?php }
    else { ?>
      <p>No hay ningún registro en esta categoría.</p>
    <?php } ?>

    <h3>Otros</h3>
    <?php $registro_otros = $conexion->query("SELECT nombre, apellido_p, apellido_m, correo FROM aniversario WHERE perfil='otro'");
    if ($registro_otros->num_rows > 0) { ?>
      <table class="tabla">
        <tr>
          <th>Nombre</th>
          <th>Correo electrónico</th>
        </tr>
        <?php while($registro_otro = $registro_otros->fetch_assoc()) { ?>
          <tr>
            <td><?php echo $registro_otro['nombre'] . ' ' . $registro_otro['apellido_p'] . ' '. $registro_otro['apellido_m']; ?></td>
            <td><?php echo $registro_otro['correo']; ?></td>
          </tr>
        <?php } ?>
      </table>
    <?php }
    else { ?>
      <p>No hay ningún registro en esta categoría.</p>
    <?php } ?>
  </div>

<?php } else { ?>
  <div class='card-important'>
    <h2>Acceso prohibido</h2>
    <p>No tienes permisos para acceder a esta sección.</p>
    <p>Si crees que estás viendo este mensaje por error por favor contacta al administrador del sistema.</p>
  </div>
<?php } ?>
