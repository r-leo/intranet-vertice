<?php /*
Archivo fuente de módulo
------------------------
* En este script hay acceso a todas las variables y funciones globales disponibles en intranet.php.
*/ ?>

<div class='responsive_hide'>
  <div class='navegador'>Estás en</div>
  <div class='tag clickable' onclick="window.location='intranet.php';">Inicio</div>
  <div class='navegador'><i class='fas fa-chevron-right inline'></i></div>
  <div class='tag'>Planes curriculares</div>
</div>

<?php if ($_SESSION['estatus'] == 'coordinador') {

  // Señal: agregar_plan:
  if ($_POST['flag'] === 'agregar_plan') {
    // Insertar plan:
    $conexion->query("INSERT INTO planes (nombre, duracion, puntos_minimos) VALUES ('{$_POST['nombre']}', '{$_POST['duracion']}', '{$_POST['puntos']}')");
    // Obtener ID del plan:
    $plan_id = $conexion->query("SELECT id FROM planes WHERE nombre='{$_POST['nombre']}'")->fetch_assoc()['id'];
    // Insertar requisitos del plan:
    foreach ( array_keys($_POST) as $requisito ) {
      // Verificar que la clave sea un requisito y no sea vacía:
      if ( substr($requisito, 0, 1) == 'r' && strlen($_POST[$requisito]) > 0) {
        // Requisito no periódico:
        if ( substr($requisito, 10, 1) == 'n' ) {
          $asistencias = $_POST['asistencias_np_' . explode('_', $requisito)[2]];
          $conexion->query("INSERT INTO requisitos (tipo, plan, nombre, valor, asistencias) VALUES (1, $plan_id, '$_POST[$requisito]', '0', '$asistencias')");
        }
        // Requisito periódico:
        elseif ( substr($requisito, 10, 1) == 'p' ) {
          $valor = $_POST['valor_p_' . explode('_', $requisito)[2]];
          $asistencias = $_POST['asistencias_p_' . explode('_', $requisito)[2]];
          $conexion->query("INSERT INTO requisitos (tipo, plan, nombre, valor, asistencias) VALUES (2, $plan_id, '$_POST[$requisito]', '$valor', '$asistencias')");
        }
      }
    }
    // Fin:
    mensaje_exito('Plan curricular creado correctamente.');
  }

  // Señal: eliminar_plan:
  if ($_POST['flag'] === 'eliminar_plan') {
    $sql = "DELETE FROM planes WHERE id='{$_POST['id']}'";
    mensaje($sql, 'Plan eliminado.');
  }

  // Redefinir el objeto de ajustes (esto debe estar al final de todas las señales para evitar sobreescritura):
  $ajustes=$conexion->query("SELECT * FROM ajustes")->fetch_assoc();
  ?>

  <div class='card'>
    <h2>Crear un nuevo plan</h2>
    <div class='accordion'>
      <h3>Crear un nuevo plan curricular</h3>
      <div>
        <p><i class='fas fa-exclamation-triangle fa-fw color-rojo'></i>Crear un plan curricular es algo importante. ¡Hazlo con cuidado!</p>
        <form method='post' action='?d=planes'>
          <fieldset>
            <legend>Características generales del plan</legend>
            <label for='nombre'>Nombre del plan</label><input type='text' name='nombre' required>
            <label for='duracion'>Duración (en semestres) <i class='fas fa-question-circle inline color-tip' title='Número de semestres que dura este plan curricular'></i></label><input type='number' name='duracion' class='inline_input' required><br>
            <label for='puntos'>Suma de puntos semestral mínima requerida <i class='fas fa-question-circle inline color-tip' title='Cantidad de puntos que el aluno debe cubrir cada semestre para acreditar el plan'></i></label><input type='number' name='puntos' class='inline_input' required>
          </fieldset>
          <fieldset>
            <legend>Requisitos del plan</legend>
            <div class='tabs'>
              <ul>
                <li><a href='#requisitos_np_tab'>Requisitos no periódicos<br>(Actividades curriculares)</a></li>
                <li><a href='#requisitos_p_tab'>Requisitos periódicos<br>(Actividades extracurriculares)</a></li>
              </ul>
              <div id='requisitos_np_tab'>
                <div id='requisitos_np'>
                  <label for='requisito_np_1'><span class='bold'>Nombre del requisito 1</span></label><input type='text' name='requisito_np_1'>
                  <label for='asistencias_np_1'>Asistencias mínimas requeridas <i class='fas fa-question-circle inline color-tip' title='Número de actividades totales (NO semestrales) que el alumno debe cubrir en este requisito para acreditar el plan'></i></label><input type='number' name='asistencias_np_1' class='inline_input'><br>
                </div>
                <a href='#' onclick='agregar_np();'>Añadir un requisito más...</a>
              </div>
              <div id='requisitos_p_tab'>
                <div id='requisitos_p'>
                  <label for='requisito_p_1'><span class='bold'>Nombre del requisito 1</span></label><input type='text' name='requisito_p_1'>
                  <label for='valor_p_1'>Puntos mínimos requeridos <i class='fas fa-question-circle inline color-tip' title='Número de puntos semestrales que el alumno debe cubrir en este requisito para acreditar el plan'></i></label><input type='number' name='valor_p_1' class='inline_input'><br>
                  <label for='asistencias_p_1'>Asistencias mínimas requeridas <i class='fas fa-question-circle inline color-tip' title='Número de actividades semestrales que el alumno debe cubrir en este requisito para acreditar el plan'></i></label><input type='number' name='asistencias_p_1' class='inline_input'><br>
                </div>
                <a href='#' onclick='agregar_p();'>Añadir un requisito más...</a>
              </div>
            </div>
          </fieldset>
          <br>
          <p><i class='fas fa-exclamation-triangle fa-fw color-rojo'></i>Revisa que los requisitos sean correctos antes de dar clic en 'Guardar'. El plan no se puede editar una vez guardado. <span class='hint' title='Porque me dio flojera programarlo.'>¿Por qué no?</span></p>
          <p><i class='fas fa-lightbulb-o fa-fw color-verde'></i>Una vez creado el plan curricular, no olvides asignarlo a la(s) generación(es) que corresponda(n).</p>
          <br>
          <input type='hidden' name='flag' value='agregar_plan'>
          <input type='submit' value='Guardar' class='boton'>
        </form>
      </div>
    </div>
  </div>

  <script>
    function agregar_np() {
      var n = $('#requisitos_np input').length/2 + 1;
      $('#requisitos_np').append("<hr>");
      $('#requisitos_np').append("<label for='requisito_np_" + n + "'><span class='bold'>Nombre del requisito " + n + "</span></label><input type='text' name='requisito_np_" + n + "'>");
      $('#requisitos_np').append("<label for='asistencias_np_" + n + "'>Asistencias mínimas requeridas</label><input type='number' name='asistencias_np_" + n + "' class='inline_input'><br>");
    }
    function agregar_p() {
      var n = $('#requisitos_p input').length/3 + 1;
      $('#requisitos_p').append("<hr>");
      $('#requisitos_p').append("<label for='requisito_p_" + n + "'><span class='bold'>Nombre del requisito " + n + "</span></label><input type='text' name='requisito_p_" + n + "'>");
      $('#requisitos_p').append("<label for='valor_p_" + n + "'>Puntos mínimos requeridos para acreditar el requisito " + n + "</label><input type='number' name='valor_p_" + n + "' class='inline_input'><br>");
      $('#requisitos_p').append("<label for='asistencias_p_" + n + "'>Asistencias mínimas requeridas</label><input type='number' name='asistencias_p_" + n + "' class='inline_input'><br>");
    }
  </script>

  <div class='card'>
    <h2>Ver/eliminar planes curriculares</h2>
    <p><i class='fas fa-exclamation-triangle fa-fw color-rojo'></i>Eliminar un plan puede causar problemas. Sólo debes hacerlo en casos específicos y si conoces bien las consecuencias. <span class='hint' title='Si hay actividades dadas de alta con el plan que vas a eliminar, van a quedarse desconectadas y a lastrar la base de datos. Lo mismo pasa con generaciones que estén asignadas al plan que elimines.'>¿Cuáles son las consecuencias?</span>&nbsp;&nbsp;<span class='hint' title='En caso de que recién hayas creado un plan y (en contra de todas mis advertencias) lo hayas hecho mal, y si no se le ha asignado todavía ninguna generación ni ninguna actividad, puedes eliminarlo.'>¿En qué caso puedo eliminar un plan?</span></p>
    <p>Da clic en el nombre del plan para ver los detalles.</p>
    <table class='tabla'>
      <tr><th>Nombre del plan</th><th>Generaciones asignadas</th><th>Eliminar plan</th></tr>
      <?php $planes = $conexion->query("SELECT * FROM planes");
      while ($plan = $planes->fetch_assoc()) { ?>
        <tr>
          <td><a href="intranet.php?d=plan&id=<?php echo $plan['id']; ?>"><?php echo $plan['nombre']; ?></a></td>
          <td>
            <?php $generaciones = $conexion->query("SELECT generacion FROM generaciones WHERE plan='{$plan['id']}' ORDER BY generacion");
            if ($generaciones -> num_rows === 0) {
              $generaciones_texto = 'Ninguna';
            }
            elseif ($generaciones -> num_rows === 1) {
              $generaciones_texto = $generaciones -> fetch_assoc()['generacion'];
            }
            elseif ($generaciones -> num_rows > 1) {
              $generaciones_fetched = generar_array($generaciones);
              $generaciones_texto = $generaciones_fetched[0]['generacion'];
              for ($i = 1; $i < count($generaciones_fetched); $i++) {
                $generaciones_texto = $generaciones_texto . ', ' . $generaciones_fetched[$i]['generacion'];
              }
            }
            echo $generaciones_texto; ?>
          </td>
          <td>
            <form method='POST' action='?d=planes'><input type='hidden' name='flag' value='eliminar_plan'><input type='hidden' name='id' value='<?php echo $plan["id"]; ?>'><input type='submit' value='Eliminar' class='boton_inline'></form>
          </td>
        </tr>
      <?php } ?>
    </table>
    <p></p>
  </div>

<?php } else { ?>
  <div class='card-important'>
    <h2>Acceso prohibido</h2>
    <p>No tienes permisos para acceder a esta sección.</p>
    <p>Si crees que estás viendo este mensaje por error por favor contacta al administrador del sistema.</p>
  </div>
<?php } ?>
