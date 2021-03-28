<?php
  include 'config.php';

  // Cadena de búsqueda:
  if ($_REQUEST['cadena'] !== '') {
    $query = "SELECT *
              FROM usuarios
              WHERE CONCAT_WS(' ', nombre, apellido_p, apellido_m) LIKE '%{$_REQUEST['cadena']}%'
              OR id LIKE '%{$_REQUEST['cadena']}%'";
    $busqueda = $conexion->query($query);
    if ($busqueda->num_rows > 0) {
      $salida = "<table class='tabla'><tr><th>Expediente</th><th>Nombre</th><th>Inscribir</th><th>Registrar en lista de espera</th></tr>";
      while ($resultado = $busqueda->fetch_assoc()) {
        $salida = $salida . "<tr><td>" . $resultado['id'] . "</td><td>" . $resultado['nombre'] . ' ' . $resultado['apellido_p'] . ' ' . $resultado['apellido_m'] . "</td><td>" . "<form method='post' action='?d=actividad&clave=" . $_REQUEST['clave'] . "'><input type='hidden' name='flag' value='inscribir_alumno'><input type='hidden' name='id' value='" . $resultado['id'] . "'><input type='submit' class='boton_inline' value='Inscribir'></form></td><td><form method='post' action='?d=actividad&clave=" . $_REQUEST['clave'] . "'><input type='hidden' name='flag' value='lista_espera'><input type='hidden' name='id' value='" . $resultado['id'] . "'><input type='submit' class='boton_inline' value='Registrar en la lista de espera'></form></td></tr>";
      }
      $salida = $salida . "</table>";
    }
    else {
      $salida = '';
    }
  }
  else {
    $salida = '';
  }

  echo $salida;

?>
