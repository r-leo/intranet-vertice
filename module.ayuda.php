<?php /*
Archivo fuente de módulo
------------------------
* En este script hay acceso a todas las variables y funciones globales disponibles en intranet.php.
*/ ?>

<div class='responsive_hide'>
  <div class='navegador'>Estás en</div>
  <div class='tag clickable' onclick="window.location='intranet.php';">Inicio</div>
  <div class='navegador'><i class='fas fa-chevron-right inline'></i></div>
  <div class='tag'>Ayuda</div>
</div>

<?php
  // Señal: X:
  if ($_POST['flag'] === 'X') {
    $sql = "QUERY";
    mensaje($sql, 'Mensaje de éxito');
  }

  // Redefinir el objeto de ajustes (esto debe estar al final de todas las señales para evitar sobreescritura):
  $ajustes=$conexion->query("SELECT * FROM ajustes")->fetch_assoc();
?>

<div class='card'>
  <h2>Ayuda y documentación</h2>
  <p>(Sección pendiente)</p>
</div>
