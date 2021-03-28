<?php /*
Archivo fuente de módulo
------------------------
* En este script hay acceso a todas las variables y funciones globales disponibles en intranet.php.
*/ ?>

<div class='responsive_hide'>
  <div class='navegador'>Estás en</div>
  <div class='tag clickable' onclick="window.location='intranet.php';">Inicio</div>
  <div class='navegador'><i class='fas fa-chevron-right inline'></i></div>
  <div class='tag'>Progreso</div>
</div>

<?php $periodo_activo = $conexion->query("SELECT * FROM periodos WHERE activo='1'")->fetch_assoc(); ?>

<?php if ($_SESSION['estatus'] <> 'coordinador') {

  // Señal: X:
  if ($_POST['flag'] === 'X') {
    $sql = "QUERY";
    mensaje($sql, 'Mensaje de éxito');
  }

  // Redefinir el objeto de ajustes (esto debe estar al final de todas las señales para evitar sobreescritura):
  $ajustes=$conexion->query("SELECT * FROM ajustes")->fetch_assoc();

  if ($ajustes['mensaje_activo'] == 1) { ?>
  <div class='card-important responsive_hide'>
    <h2><?php echo $ajustes['mensaje_titulo'] ?></h2>
    <p><?php echo $ajustes['mensaje'] ?></p>
  </div>
  <?php } ?>

  <div class='card'>
    <h2>Tu estatus</h2>
    <table class='tabla'>
      <tr><th>Periodo</th><th class='center'>Estatus calculado</th><th>Estatus definido</th><th class='center'>Estatus final</th></tr>
      <?php foreach ($_usuario->semestres as $semestre) {
        if ($semestre['periodo_id'] >= $periodo_activo['clave']) { ?>
          <tr class='fila_desactivada'>
            <td><?php echo $semestre['periodo_nombre']; ?></td>
            <td class='center'>--</td>
            <td class='center'>--</td>
            <td class='center'>--</td>
          </tr>
        <?php }
        else { ?>
          <tr>
            <td><?php echo $semestre['periodo_nombre']; ?></td>
            <td class='center'>
              <?php if ($semestre['estatus_alumno'] == 'activo') { ?>
                <span class='color-verde'>Activo</span>
              <?php }
              elseif ($semestre['estatus_alumno'] == 'condicionado') { ?>
                <span class='color-naranja'>Condicionado</span>
              <?php }
              elseif ($semestre['estatus_alumno'] == 'baja') { ?>
                <span class='color-rojo'>Baja</span>
              <?php } ?>
            </td>
            <td class='center'>
              <?php if ($semestre['estatus_definido'] == 'activo') { ?>
                <span class='color-verde'>Activo</span>
              <?php }
              elseif ($semestre['estatus_definido'] == 'condicionado') { ?>
                <span class='color-naranja'>Condicionado</span>
              <?php }
              elseif ($semestre['estatus_definido'] == 'baja') { ?>
                <span class='color-rojo'>Baja</span>
              <?php }
              else { ?>
                Sin modificación
              <?php } ?>
            </td>
            <td class='center'>
              <?php if ($semestre['estatus_final'] == 'activo') { ?>
                <span class='color-verde'>Activo</span>
              <?php }
              elseif ($semestre['estatus_final'] == 'condicionado') { ?>
                <span class='color-naranja'>Condicionado</span>
              <?php }
              elseif ($semestre['estatus_final'] == 'baja') { ?>
                <span class='color-rojo'>Baja</span>
              <?php } ?>
            </td>
          </tr>
        <?php }
      } ?>
    </table>
    <p>Tu estatus final actual en el Programa es:</p>
    <?php if ($_usuario->informacion['estatus'] == 'activo') { ?>
      <p class='center color-verde'><i class='fas fa-star inline'></i><br>ACTIVO</p>
    <?php }
    elseif ($_usuario->informacion['estatus'] == 'condicionado') { ?>
      <p class='center color-naranja'><i class='fas fa-exclamation-triangle inline'></i><br>CONDICIONADO</p>
    <?php }
    elseif ($_usuario->informacion['estatus'] == 'baja') { ?>
      <p class='center color-rojo'><i class='fas fa-ban inline'></i><br>BAJA</p>
    <?php } ?>
  </div>

  <div class='card'>
    <h2>Actividades extracurriculares del semestre</h2>
    <p>Esta sección muestra tu progreso en las actividades extracurriculares de este semestre.</p>
    <div class='tabs'>
      <ul>
        <li><a href='#requisitos_periodicos_tab_grafico'>Vista general</a></li>
        <li><a href='#requisitos_periodicos_tab_tabla'>Vista detallada</a></li>
        <li><a href='#requisitos_periodicos_tab_full'>Vista completa</a></li>
      </ul>
      <div id='requisitos_periodicos_tab_grafico'>
        <?php foreach ($_usuario->semestres[array_search($periodo_activo['clave'], $_usuario->periodos)]['requisitos_periodicos'] as $requisito_periodico) {
          if ($requisito_periodico['estatus'] == 'acreditado') { ?>
            <div class='requisito cumplido' title='Acreditado'>
              <p><i class='fas fa-check fa-fw color-verde'></i><?php echo $requisito_periodico['nombre']; ?></p>
            </div>
          <?php }
          else { ?>
            <div class='requisito pendiente' title='No acreditado'>
              <p><i class='fas fa-times fa-fw color-tip'></i><?php echo $requisito_periodico['nombre']; ?></p>
            </div>
          <?php }
        } ?>
        <div class='aclarador'></div>
      </div>
      <div id='requisitos_periodicos_tab_tabla'>
        <div class='scroll'>
          <table class='tabla'>
            <tr><th rowspan='2'>Rubro</th><th colspan='2' class='center'>Puntos</th><th colspan='2' class='center'>Asistencias</th><th rowspan='2' class='center'>Estatus</th></tr>
            <tr><th class='center'>Requeridos</th><th class='center'>Completados</th><th class='center'>Requeridas</th><th class='center'>Registradas</th></tr>
          <?php
            foreach ($_usuario->semestres[array_search($periodo_activo['clave'], $_usuario->periodos)]['requisitos_periodicos'] as $requisito_periodico) {
              // Símbolo de estatus del requisito:
              if ($requisito_periodico['estatus'] == 'acreditado') {
                $requisitos_periodicos_estatus = "<i class='fas fa-check color-verde inline' title='Acreditado'></i>";
              }
              else {
                $requisitos_periodicos_estatus = "<i class='fas fa-times color-tip inline' title='No acreditado'></i>";
              } ?>
              <tr>
                <td><?php echo $requisito_periodico['nombre']; ?></td>
                <td class='center'><?php echo $requisito_periodico['puntos_requeridos']; ?></td>
                <td class='center'><?php echo $requisito_periodico['puntos_completados']; ?></td>
                <td class='center'><?php echo $requisito_periodico['asistencias_requeridas']; ?></td>
                <td class='center'><?php echo $requisito_periodico['asistencias_registradas']; ?></td>
                <td class='center'><?php echo $requisitos_periodicos_estatus; ?></td>
              </tr>
            <?php } ?>
          </table>
        </div>
      </div>
      <div id='requisitos_periodicos_tab_full'>
        <p>(Pendiente)</p>
      </div>
    </div>
  </div>

  <div class='card'>
    <h2>Actividades curriculares</h2>
    <p>Esta sección muestra tu progreso en las actividades curriculares del programa. Recuerda que estas actividades no son semestrales, sino que abarcan toda tu estancia en el programa.</p>
    <div class='tabs'>
      <ul>
        <li><a href='#requisitos_no_periodicos_tab_grafico'>Vista general</a></li>
        <li><a href='#requisitos_no_periodicos_tab_tabla'>Vista detallada</a></li>
      </ul>
      <div id='requisitos_no_periodicos_tab_grafico'>
        <?php foreach ($_usuario->requisitos_no_periodicos as $requisito_no_periodico) {
          if ($requisito_no_periodico['estatus'] == 'acreditado') { ?>
            <div class='requisito cumplido' title='Acreditado'>
              <p><i class='fas fa-check fa-fw color-verde'></i><?php echo $requisito_no_periodico['nombre']; ?></p>
            </div>
          <?php }
          else { ?>
            <div class='requisito pendiente' title='No acreditado'>
              <p><i class='fas fa-times fa-fw color-tip'></i><?php echo $requisito_no_periodico['nombre']; ?></p>
            </div>
          <?php } ?>
        <?php } ?>
        <div class='aclarador'></div>
      </div>
      <div id='requisitos_no_periodicos_tab_tabla'>
        <table class='tabla'>
          <tr><th rowspan='2'>Requisito</th><th colspan='2' class='center'>Asistencias</th><th rowspan='2' class='center'>Estatus</th><th rowspan='2'>Actividades</th></tr>
          <tr><th class='center'>Requeridas</th><th class='center'>Registradas</th></tr>
          <?php foreach ($_usuario->requisitos_no_periodicos as $requisito_no_periodico) { ?>
            <tr>
              <td><?php echo $requisito_no_periodico['nombre']; ?></td>
              <td class='center'><?php echo $requisito_no_periodico['asistencias_requeridas']; ?></td>
              <td class='center'><?php echo $requisito_no_periodico['asistencias_registradas']; ?></td>
              <td class='center'>
                <?php if ($requisito_no_periodico['estatus'] == 'acreditado') { ?>
                  <i class='fas fa-check color-verde inline' title='Acreditado'></i>
                <?php }
                else { ?>
                  <i class='fas fa-times color-tip inline' title='No acreditado'></i>
                <?php } ?>
              </td>
              <td>
                <?php if (count($requisito_no_periodico['fechas_completadas']) > 0) {
                  foreach ($requisito_no_periodico['fechas_completadas'] as $fecha_completada) { ?>
                    <p>
                      <?php echo $fecha_completada['nombre_actividad']; ?><br>
                      <span class='navegador'><?php echo fecha_formateada_general($fecha_completada['fecha_inicio']); ?></span>
                    </p>
                  <?php }
                }
                else { ?>
                  --
                <?php } ?>
              </td>
            </tr>
          <?php } ?>
        </table>
        <div class='aclarador'></div>
      </div>
    </div>
  </div>

  <div class='card'>
    <h2>Actividades extracurriculares semestre a semestre</h2>
    <p>Esta sección muestra tu progreso semestre a semestre.</p>
    <div class='tabs'>
      <ul>
        <li><a href='#semestres_tab_grafico'>Vista general</a></li>
        <li><a href='#semestres_tab_tabla'>Vista detallada</a></li>
        <li><a href='#semestres_tab_full'>Vista completa</a></li>
      </ul>
      <div id='semestres_tab_grafico'>
        <div class='scroll'>
          <table class='tabla'>
            <tr><th>Semestre</th><th class='center'>Estatus</th></tr>
            <?php foreach ($_usuario->semestres as $semestre) {
              if ($semestre['periodo_id'] > $periodo_activo['clave']) { ?>
                <tr class='fila_desactivada'>
                  <td><?php echo $semestre['periodo_nombre']; ?></td>
                  <td class='center'>--</td>
                </tr>
              <?php }
              else { ?>
                <tr>
                  <td><?php echo $semestre['periodo_nombre']; ?></td>
                  <td class='center'><?php if ($semestre['estatus'] == 'acreditado') { ?>
                    <i class='fas fa-check inline color-verde' title='Acreditado'></i>
                  <?php }
                  else { ?>
                    <i class='fas fa-times inline color-tip' title='No acreditado'></i>
                  <?php } ?></td>
                </tr>
              <?php }
            } ?>
          </table>
        </div>
      </div>
      <div id='semestres_tab_tabla'>
        <?php foreach ($_usuario->semestres as $semestre) { ?>
          <h3><?php echo $semestre['periodo_nombre']; ?></h3>
          <?php if ($semestre['periodo_id'] > $periodo_activo['clave']) { ?>
            <p class='color-tip'>Sin información para mostrar todavía.</p>
          <?php }
          else { ?>
            <div class='scroll'>
              <table class='tabla'>
                <tr><th rowspan='2'>Rubro</th><th colspan='2' class='center'>Puntos</th><th colspan='2' class='center'>Asistencias</th><th rowspan='2' class='center'>Estatus</th></tr>
                <tr><th class='center'>Requeridos</th><th class='center'>Completados</th><th class='center'>Requeridas</th><th class='center'>Registradas</th></tr>
                <?php foreach ($semestre['requisitos_periodicos'] as $requisito_periodico) { ?>
                  <tr>
                    <td><?php echo $requisito_periodico['nombre']; ?></td>
                    <td class='center'><?php echo $requisito_periodico['puntos_requeridos']; ?></td>
                    <td class='center'><?php echo $requisito_periodico['puntos_completados']; ?></td>
                    <td class='center'><?php echo $requisito_periodico['asistencias_requeridas']; ?></td>
                    <td class='center'><?php echo $requisito_periodico['asistencias_registradas']; ?></td>
                    <td class='center'><?php if ($requisito_periodico['estatus'] == 'acreditado') { ?>
                      <i class='fas fa-check inline color-verde' title='Acreditado'></i>
                    <?php }
                    else { ?>
                      <i class='fas fa-times inline color-tip' title='No acreditado'></i>
                    <?php } ?></td>
                  </tr>
                <?php } ?>
              </table>
            </div>
            <p>Puntos mínimos para acreditar el semestre: <?php echo $semestre['puntos_requeridos']; ?></p>
            <p>Puntos completados en el semestre: <?php echo $semestre['puntos_completados']; ?></p>
            <p><span class='bold'>Estatus general del semestre</span>: <?php if ($semestre['estatus'] == 'acreditado') { ?>
              <i class='fas fa-check fa-fw color-verde'></i>Acreditado
            <?php }
            else { ?>
              <i class='fas fa-times fa-fw color-rojo'></i>No acreditado
            <?php } ?></p>
          <?php } ?>
          <hr>
        <?php } ?>
      </div>
      <div id='semestres_tab_full'>
        <?php foreach ($_usuario->semestres as $semestre) { ?>
          <h3><?php echo $semestre['periodo_nombre']; ?></h3>
          <?php if ($semestre['periodo_id'] > $periodo_activo['clave']) { ?>
            <p class='color-tip'>Sin información para mostrar todavía.</p>
          <?php }
          else {
            $suma_puntos = 0; ?>
            <div class='scroll'>
              <table class='tabla'>
                <tr><th>Rubro</th><th>Actividad</th><th class='center'>Puntos</th></tr>
                <?php foreach ($semestre['requisitos_periodicos'] as $requisito_periodico) {
                  if ($requisito_periodico['asistencias_registradas'] > 0) {
                    $actividades = $conexion->query("SELECT actividades.nombre AS nombre, actividades.puntos AS puntos
                                                     FROM actividades
                                                     INNER JOIN actividades_usuarios ON actividades.clave = actividades_usuarios.clave
                                                     WHERE (actividades.tipo_1 = '{$requisito_periodico['id']}'
                                                       OR actividades.tipo_2 = '{$requisito_periodico['id']}'
                                                       OR actividades.tipo_3 = '{$requisito_periodico['id']}'
                                                       OR actividades.tipo_4 = '{$requisito_periodico['id']}')
                                                     AND actividades_usuarios.id = '{$_usuario->informacion['id']}'
                                                     AND actividades.periodo = '{$semestre['periodo_id']}'
                                                     AND actividades_usuarios.asistencia > 0");
                    while ($actividad = $actividades->fetch_assoc()) {
                      $suma_puntos = $suma_puntos + $actividad['puntos']; ?>
                      <tr>
                        <td><?php echo $requisito_periodico['nombre']; ?></td>
                        <td><?php echo $actividad['nombre']; ?></td>
                        <td class='center'><?php echo $actividad['puntos']; ?></td>
                      </tr>
                    <?php }
                  }
                } ?>
                <tr class='bold'><td colspan='2'>Total</td><td class='center'><?php echo $suma_puntos; ?></td></tr>
              </table>
            </div>
            <p>Puntos mínimos para acreditar el semestre: <?php echo $semestre['puntos_requeridos']; ?></p>
            <p>Puntos completados en el semestre: <?php echo $semestre['puntos_completados']; ?></p>
            <p><span class='bold'>Estatus general del semestre</span>: <?php if ($semestre['estatus'] == 'acreditado') { ?>
              <i class='fas fa-check fa-fw color-verde'></i>Acreditado
            <?php }
            else { ?>
              <i class='fas fa-times fa-fw color-rojo'></i>No acreditado
            <?php } ?></p>
          <?php } ?>
          <hr>
        <?php } ?>
      </div>
    </div>
  </div>

<?php } else {
  redireccionar('intranet.php');
} ?>
