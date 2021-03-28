<?php /*
Archivo fuente de módulo
------------------------
* En este script hay acceso a todas las variables y funciones globales disponibles en intranet.php.
*/ ?>

<div class='responsive_hide'>
  <div class='navegador'>Estás en</div>
  <div class='tag clickable' onclick="window.location='intranet.php';">Inicio</div>
  <div class='navegador'><i class='fas fa-chevron-right inline'></i></div>
  <div class='tag'>Comités</div>
</div>

<?php if ($_SESSION['estatus'] == 'coordinador') {

  // Señal: agregar_comite:
  if ($_POST['flag'] === 'agregar_comite') {
    $sql = "INSERT INTO catalogo_comites (nombre) VALUES ('" . $_POST['comite'] . "')";
    mensaje($sql, 'Comité añadido correctamete.');
  }

  // Señal: eliminar_comite:
  if ($_POST['flag'] === 'eliminar_comite') {
    $sql = "DELETE FROM catalogo_comites WHERE comite='{$_POST['comite']}'";
    mensaje($sql, 'Comité eliminado correctamete.');
  }

  // Redefinir el objeto de ajustes (esto debe estar al final de todas las señales para evitar sobreescritura):
  $ajustes=$conexion->query("SELECT * FROM ajustes")->fetch_assoc();
  ?>

  <div class='card'>
    <h2>Catálogo de comités</h2>
    <h3>Lista de comités registrados</h3>
    <?php $catalogo_comites = $conexion->query("SELECT comite, nombre FROM catalogo_comites WHERE 1 ORDER BY nombre ASC");

    // Con comités registrados:
    if ($catalogo_comites->num_rows > 0) { ?>
      <table class='tabla'>
        <tr><th>Nombre del comité</th><th>Número de miembros</th><th>Eliminar</th></tr>
      <?php while ($comite = $catalogo_comites->fetch_assoc()) {
        $miembros = $conexion->query("SELECT COUNT(id) AS miembros FROM comites WHERE comite='{$comite['comite']}'")->fetch_assoc()['miembros']; ?>
        <tr>
          <td><?php echo $comite['nombre']; ?></td>
          <td><?php echo $miembros; ?></td>
          <td>
            <form method='post' action='?d=comites'>
              <input type='hidden' name='flag' value='eliminar_comite'>
              <input type='hidden' name='comite' value='<?php echo $comite['comite']; ?>'>
              <input type='submit' class='boton_inline' value='Eliminar'>
            </form>
          </td>
        </tr>
      <?php } ?>
      </table>
    <?php }

    // Sin comités registrados:
    else { ?>
      <p>No hay comités registrados. Utiliza el formulario de abajo para agregar el primero:</p>
    <?php } ?>

    <h3>Añadir un comité</h3>
    <form method='post' action='?d=comites'>
      <label>Nombre del comité:</label>
      <input type='text' name='comite'>
      <input type='hidden' name='flag' value='agregar_comite'>
      <input type='submit' class='boton_inline' value='Añadir'>
      <div class='aclarador'></div>
    </form>
  </div>

  <div class='card'>
    <h2>Asignación de alumnos a comités</h2>

    <?php // Con asignaciones de comités:
    if ($conexion->query("SELECT comite FROM comites WHERE 1")->num_rows > 0) { ?>
      <?php $comites = $conexion->query("SELECT comite, nombre FROM catalogo_comites WHERE 1 ORDER BY nombre");
      while ($comite = $comites->fetch_assoc()) { ?>
        <h3><?php echo $comite['nombre']; ?></h3>
        <?php $comite_sub = $conexion->query("SELECT usuarios.nombre, usuarios.apellido_p, usuarios.apellido_m FROM usuarios INNER JOIN comites ON usuarios.id=comites.id WHERE comites.comite='{$comite['comite']}'");
        if ($comite_sub->num_rows > 0) { ?>
          <table class='tabla'>
            <?php while ($alumno_comite = $comite_sub->fetch_assoc()) { ?>
              <tr><td><?php echo $alumno_comite['nombre'] . ' ' . $alumno_comite['apellido_p'] . ' ' . $alumno_comite['apellido_m']; ?></td></tr>
            <?php } ?>
          </table>
        <?php }
        else { ?>
          <p>No hay ningún alumno registrado en este comité.</p>
        <?php }
      }
    }

    // Sin asignaciones de comités:
    else { ?>
      <p>No hay ningún comité registrado.</p>
    <?php } ?>
  </div>

<?php } else { ?>
  <div class='card-important'>
    <h2>Acceso prohibido</h2>
    <p>No tienes permisos para acceder a esta sección.</p>
    <p>Si crees que estás viendo este mensaje por error por favor contacta al administrador del sistema.</p>
  </div>
<?php } ?>
