<?php /*
Archivo fuente de módulo
------------------------
* En este script hay acceso a todas las variables y funciones globales disponibles en intranet.php.
*/ ?>

<?php if (isset($_GET['id'])) {
  $id = $_GET['id'];
  $planes = $conexion->query("SELECT * FROM planes WHERE id='$id'");
  if ($planes->num_rows > 0) {

    // Información del plan
    $plan = $planes->fetch_assoc();
    $generaciones = $conexion->query("SELECT generacion FROM generaciones WHERE plan='$plan[id]' ORDER BY generacion");

    if ($_SESSION['estatus'] === 'coordinador') { ?>

      <div class='responsive_hide'>
        <div class='navegador'>Estás en</div>
        <div class='tag clickable' onclick="window.location='intranet.php';">Inicio</div>
        <div class='navegador'><i class='fas fa-chevron-right inline'></i></div>
        <div class='tag clickable' onclick="window.location='?d=planes';">Planes curriculares</div>
        <div class='navegador'><i class='fas fa-chevron-right inline'></i></div>
        <div class='tag'><?php echo $plan['nombre']; ?></div>
      </div>

      <div class='card'>
        <h2>Detalles del plan: <?php echo $plan['nombre']; ?></h2>
        <p>Duración del plan: <?php echo $plan['duracion']; ?> semestres.</p>
        <p>Generaciones asignadas a este plan:&nbsp;
        <?php if ($generaciones->num_rows == 0) { echo 'no hay generaciones asignadas a este plan'; }
        else {
          echo $generaciones->fetch_assoc()['generacion'];
          while ($generacion = $generaciones->fetch_assoc()) {
            echo ', ' . $generacion['generacion'];
          }
          echo '.';
        } ?></p>
        <h3>Requisitos periódicos</h3>
        <?php $requisitos_periodicos = $conexion->query("SELECT * FROM requisitos WHERE plan='{$plan['id']}' AND tipo='2'");
        if ($requisitos_periodicos -> num_rows > 0) { ?>
          <table class='tabla'>
            <tr><th>Nombre del requisito</th><th>Puntos mínimos</th><th>Asistencias mínimas</th></tr>
            <?php while ($requisito = $requisitos_periodicos->fetch_assoc()) { ?>
              <tr>
                <td><?php echo $requisito['nombre']; ?></td>
                <td><?php echo $requisito['valor']; ?></td>
                <td><?php echo $requisito['asistencias']; ?></td>
              </tr>
            <?php } ?>
          </table>
          <p></p>
        <?php }
        else {
          echo "<p>No hay requisitos de este tipo en el plan.</p>";
        } ?>
        <h3>Requisitos no periódicos</h3>
        <?php $requisitos_no_periodicos = $conexion->query("SELECT * FROM requisitos WHERE plan='{$plan['id']}' AND tipo='1'");
        if ($requisitos_no_periodicos -> num_rows > 0) { ?>
          <table class='tabla'>
            <tr><th>Nombre del requisito</th><th>Asistencias mínimas</th></tr>
            <?php while ($requisito = $requisitos_no_periodicos->fetch_assoc()) { ?>
              <tr>
                <td><?php echo $requisito['nombre']; ?></td>
                <td><?php echo $requisito['asistencias']; ?></td>
              </tr>
            <?php } ?>
          </table>
          <p></p>
        <?php }
        else {
          echo "<p>No hay requisitos de este tipo en el plan.</p>";
        } ?>
      </div>

    <?php } else {
        redireccionar('intranet.php');
    }
  }
  else {
    redireccionar('intranet.php?d=planes');
  }
}
else {
  redireccionar('intranet.php');
}
