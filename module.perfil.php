<?php /*
Archivo fuente de módulo
------------------------
* En este script hay acceso a todas las variables y funciones globales disponibles en intranet.php.
*/ ?>

<?php if (isset($_GET['id'])) {
  $usuario = $conexion->query("SELECT * FROM usuarios WHERE id='{$_GET['id']}'")->fetch_assoc();
}
else {
  $usuario = $conexion->query("SELECT * FROM usuarios WHERE id='{$_SESSION['id']}'")->fetch_assoc();
} ?>

<div class='responsive_hide'>
  <div class='navegador'>Estás en</div>
  <div class='tag clickable' onclick="window.location='intranet.php';">Inicio</div>
  <div class='navegador'><i class='fas fa-chevron-right inline'></i></div>
  <?php if ($usuario['id'] <> $_SESSION['id']) { ?>
    <div class='tag clickable' onclick="window.location='?d=alumnos';">Alumnos</div>
    <div class='navegador'><i class='fas fa-chevron-right inline'></i></div>
  <?php } ?>
  <div class='tag'>Editar perfil de usuario</div>
</div>

<?php
// Mensaje de los coordinadores:
if (($_SESSION['estatus'] <> 'coordinador') && $ajustes['mensaje_activo'] === '1') { ?>
  <div class='card-important responsive_hide'>
    <h2><?php echo $ajustes['mensaje_titulo']; ?></h2>
    <p><?php echo $ajustes['mensaje']; ?></p>
  </div>
<?php }

// Señal: cambiar_estatus:
if ($_GET['flag'] == 'cambiar_estatus') {
  $periodo_nuevo = explode('.', $_GET['cambio'])[0];
  $estatus_nuevo = explode('.', $_GET['cambio'])[1];
  if ($conexion->query("SELECT estatus FROM estatus WHERE id='{$usuario['id']}' AND periodo='$periodo_nuevo'")->num_rows > 0) {
    if ($estatus_nuevo == '0') {
      $conexion->query("DELETE FROM estatus WHERE id='{$usuario['id']}' AND periodo='$periodo_nuevo'");
    }
    else {
      $conexion->query("UPDATE estatus SET estatus='$estatus_nuevo' WHERE id='{$usuario['id']}' AND periodo='$periodo_nuevo'");
    }
  }
  else {
    if ($estatus_nuevo == '0') {
      $conexion->query("DELETE FROM estatus WHERE id='{$usuario['id']}' AND periodo='$periodo_nuevo'");
    }
    else {
      $conexion->query("INSERT INTO estatus (id, periodo, estatus) VALUES ('{$usuario['id']}', '$periodo_nuevo', '$estatus_nuevo')");
    }
  }
}

// Señal: cambiar_comite:
if ($_GET['flag'] == 'cambiar_comite') {
  if ($_GET['cambio'] == '0') {
    $conexion->query("DELETE FROM comites WHERE id='{$_GET['id']}'");
  }
  else {
    $conexion->query("DELETE FROM comites WHERE id='{$_GET['id']}'");
    $conexion->query("INSERT INTO comites (id, comite) VALUES ('{$_GET['id']}', '{$_GET['cambio']}')");
  }
}

// Señal: actualizar_perfil:
if ($_POST['flag'] === 'actualizar_perfil') {
  // Procesar la imagen y guardarla:
  if ($_POST['actualizacion_imagen'] == '1') {
    $sufijo = $conexion->query("SELECT sufijo FROM usuarios WHERE id='{$_SESSION['id']}'")->fetch_assoc()['sufijo'];
    // Definir variables para crop.php:
    $crop_nombre_archivo = $_SESSION['id'] . '_' . $sufijo;
    $crop_punto_0 = $_POST['imagen_crop0'];
    $crop_punto_1 = $_POST['imagen_crop1'];
    $crop_punto_2 = $_POST['imagen_crop2'];
    $crop_punto_3 = $_POST['imagen_crop3'];
    require 'crop.php';
  }

  if ($_POST['password_anterior'] <> '') {
    if ($_POST['password_anterior'] <> $usuario['password']) { ?>
      <div class='card-important dialog'>
        <h2>No se puede actualizar tu perfil</h2>
        <p>La contraseña actual que introdujiste es incorrecta. Por favor trata de nuevo.</p>
      </div>
    <?php }
    else {
      if (($_POST['password_nuevo_1'] === $_POST['password_nuevo_2']) && $_POST['password_nuevo_1'] <> '') {
        $conexion->query("UPDATE usuarios SET password='{$_POST['password_nuevo_1']}' WHERE id='{$_SESSION['id']}'"); ?>
        <div class='card-important dialog'>
          <h2>Perfil actualizado</h2>
          <p>Tu perfil se ha actualizado correctamente.</p>
        </div>
      <?php }
      else { ?>
        <div class='card-important dialog'>
          <h2>No se puede actualizar tu perfil</h2>
          <p>Las nuevas contraseñas no coinciden o están en blanco. Por favor trata de nuevo.</p>
        </div>
      <?php }
    }
  }

  // Coordinadores:
  if ($_SESSION['estatus'] === 'coordinador') {
    $sql = "UPDATE usuarios SET nombre='{$_POST['nombre']}', apellido_p='{$_POST['apellido_p']}', apellido_m='{$_POST['apellido_m']}', correo='{$_POST['correo']}', fecha_nac='{$_POST['fecha_nac']}', sexo='{$_POST['sexo']}', carrera='{$_POST['carrera']}', usuario='{$_POST['nombre_usuario']}' WHERE id='{$_POST[id]}'";
    mensaje($sql, 'Perfil modificado correctamente.');
  }

  // Usuarios regulares:
  //else {
  //  // CODEH
  //}

  // Es necesario actualizar el puntero de la base de datos:
  if (isset($_GET['id'])) {
    $usuario = $conexion->query("SELECT * FROM usuarios WHERE id='{$_GET['id']}'")->fetch_assoc();
  }
  else {
    $usuario = $conexion->query("SELECT * FROM usuarios WHERE id='{$_SESSION['id']}'")->fetch_assoc();
  }

}


if ($_SESSION['estatus'] === 'coordinador' && $_SESSION['id'] <> $usuario['id']) { ?>
  <!--<div class='card'>
    <h2>Navegación rápida</h2>
    <ol>
      <li><a href=''>Información general del alumno</a></li>
      <li><a href=''>Estatus del alumno</a></li>
      <li><a href=''>Progreso del alumno</a></li>
      <li><a href=''>Editar perfil del alumno</a></li>
      <li><a href=''>Acciones especiales para el alumno</a></li>
    </ol>
  </div>-->
  <div class='card'>
    <h2><?php echo $usuario['nombre'] . ' ' . $usuario['apellido_p']; ?></h2>
    <hr>
    <img src='perfiles/<?php echo $usuario['id'] . '_' . $usuario['sufijo']; ?>.png' class='foto_usuario'>
    <p class='center'><a href='perfiles/<?php echo $usuario['id'] . '_' . $usuario['sufijo']; ?>.png' target='_blank' class='hint'>Abrir foto de perfil... <i class='fas fa-external-link inline'></i></a></p>
    <hr>
    <h3>Información general</h3>
    <table class='tabla ajustada vertical'>
      <tr></tr>
      <tr><td>Número de expediente</td><td><?php echo $usuario['id']; ?></td></tr>
      <tr><td>Nombre completo</td><td><?php echo $usuario['nombre'].' '.$usuario['apellido_p'].' '.$usuario['apellido_m']; ?></td></tr>
      <tr><td>Generación</td><td><?php echo $usuario['generacion']; ?></td></tr>
      <tr><td>Carrera</td><td><?php echo $usuario['carrera']; ?></td></tr>
      <tr><td>Edad</td><td><?php echo date_diff(date_create($usuario['fecha_nac']), date_create('now'))->y . ' años'; ?></td></tr>
      <tr><td>Correo electrónico</td><td><?php echo $usuario['correo']; ?>&nbsp;<a href='mailto:<?php echo $usuario['correo']; ?>'>(redactar)</a></td></tr>
      <tr><td>Fecha de nacimiento</td><td><?php echo $usuario['fecha_nac']; ?> <span class='hint' title='El formato es año-mes-día'>(Formato)</span></td></tr>
    </table>
    <p>La información del alumno se puede modificar <a href='#editarperfil'>más abajo</a>.</p>
    <hr>
    <h3>Asignación de comité</h3>
    <label for='cambiar_comite'>Asignar el alumno al siguiente comité:</label>
    <select class='selectmenu' name='cambiar_comite' id='cambiar_comite'>
      <?php $comite_actual = $conexion->query("SELECT comite FROM comites WHERE id='{$usuario['id']}'");
      if ($comite_actual->num_rows == 0) {
        $comite_actual = 0;
      }
      else {
        $comite_actual = $comite_actual->fetch_assoc()['comite'];
      } ?>
      <option value='0' <?php if ($comite_actual == 0){ echo 'selected';} ?>>No asignar a ningún comité</option>
      <?php $comites = $conexion->query("SELECT comite, nombre FROM catalogo_comites WHERE 1 ORDER BY nombre");
      while ($comite = $comites->fetch_assoc()) { ?>
        <option value='<?php echo $comite['comite']; ?>' <?php if ($comite['comite'] == $comite_actual){ echo 'selected';} ?>><?php echo $comite['nombre']; ?></option>
      <?php } ?>
    </select><p></p>

    <script>
      $('#cambiar_comite').on('selectmenuchange', function(){
        window.location.replace('?d=perfil&id=<?php echo $usuario['id']; ?>&flag=cambiar_comite&cambio=' + $(this).val());
      });
    </script>

  </div>

  <?php
    $_usuario2 = new Usuario($usuario['id'], $conexion);
    $periodo_activo = $conexion->query("SELECT * FROM periodos WHERE activo='1'")->fetch_assoc();
  ?>

  <div class='card'>
    <h2>Estatus del alumno</h2>
    <table class='tabla'>
      <tr><th>Periodo</th><th class='center'>Estatus calculado</th><th>Estatus definido</th><th class='center'>Estatus final</th></tr>
      <?php foreach ($_usuario2->semestres as $semestre) {
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
            <td>
              <select class='selectmenu cambiar_estatus'>
                <option value='<?php echo $semestre['periodo_id']; ?>.0' <?php if ($semestre['estatus_definido'] == 'nulo'){echo 'selected';} ?>>No modificar</option>
                <option value='<?php echo $semestre['periodo_id']; ?>.activo' <?php if ($semestre['estatus_definido'] == 'activo'){echo 'selected';} ?>>Activo</option>
                <option value='<?php echo $semestre['periodo_id']; ?>.condicionado' <?php if ($semestre['estatus_definido'] == 'condicionado'){echo 'selected';} ?>>Condicionado</option>
                <option value='<?php echo $semestre['periodo_id']; ?>.baja' <?php if ($semestre['estatus_definido'] == 'baja'){echo 'selected';} ?>>Baja</option>
              </select>

              <script>
                $('.cambiar_estatus').on('selectmenuchange', function(){
                  window.location.replace('?d=perfil&id=<?php echo $usuario['id']; ?>&flag=cambiar_estatus&cambio=' + $(this).val());
                });
              </script>

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
    <p>El estatus final del alumno es:</p>
    <?php if ($_usuario2->informacion['estatus'] == 'activo') { ?>
      <p class='center color-verde'><i class='fas fa-star inline'></i><br>ACTIVO</p>
    <?php }
    elseif ($_usuario2->informacion['estatus'] == 'condicionado') { ?>
      <p class='center color-naranja'><i class='fas fa-exclamation-triangle inline'></i><br>CONDICIONADO</p>
    <?php }
    elseif ($_usuario2->informacion['estatus'] == 'baja') { ?>
      <p class='center color-rojo'><i class='fas fa-ban inline'></i><br>BAJA</p>
    <?php } ?>

  </div>

  <div class='card'>
    <h2>Progreso del alumno</h2>

    <table>
      <tr><td><i class='fas fa-check color-verde'></i></td><td>acreditado.</td></tr>
      <tr><td><i class='fas fa-times color-tip'></i></td><td>no acreditado.</td></tr>
    </table>

    <h3>Actividades extracurriculares de este semestre</h3>
    <div class='tabs'>
      <ul>
        <li><a href='#requisitos_periodicos_tab_grafico'>Vista general</a></li>
        <li><a href='#requisitos_periodicos_tab_tabla'>Vista detallada</a></li>
        <li><a href='#requisitos_periodicos_tab_full'>Vista completa</a></li>
      </ul>
      <div id='requisitos_periodicos_tab_grafico'>
        <?php foreach ($_usuario2->semestres[array_search($periodo_activo['clave'], $_usuario2->periodos)]['requisitos_periodicos'] as $requisito_periodico) {
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
            foreach ($_usuario2->semestres[array_search($periodo_activo['clave'], $_usuario2->periodos)]['requisitos_periodicos'] as $requisito_periodico) {
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

    <h3>Actividades curriculares</h3>
    <div class='tabs'>
      <ul>
        <li><a href='#requisitos_no_periodicos_tab_grafico'>Vista general</a></li>
        <li><a href='#requisitos_no_periodicos_tab_tabla'>Vista detallada</a></li>
      </ul>
      <div id='requisitos_no_periodicos_tab_grafico'>
        <?php foreach ($_usuario2->requisitos_no_periodicos as $requisito_no_periodico) {
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
          <?php foreach ($_usuario2->requisitos_no_periodicos as $requisito_no_periodico) { ?>
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

    <h3>Actividades extracurriculares semestre a semestre</h3>
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
            <?php foreach ($_usuario2->semestres as $semestre) {
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
        <?php foreach ($_usuario2->semestres as $semestre) { ?>
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
        <?php foreach ($_usuario2->semestres as $semestre) { ?>
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
                                                     AND actividades_usuarios.id = '{$_usuario2->informacion['id']}'
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

<?php } ?>

<div class='card'>
  <h2><a name='editarperfil'></a>Editar perfil de usuario</h2>
  <form method='POST' action='?d=perfil&id=<?php echo $usuario["id"];?>' enctype='multipart/form-data'>

    <fieldset>
      <legend>Imagen de perfil</legend>
      <?php if ($_SESSION['estatus'] <> 'coordinador') { ?>
        <p>Recuerda que la imagen de perfil es visible sólo para ti y para los coordinadores. Debe ser una imagen donde aparezcas claramente.</p>
      <?php } ?>

      <div class='accordion'>
        <h3>Modificar imagen de perfil</h3>
          <div>
          <p>Sube una imagen donde aparezcas claramente. Esta imagen será parte de tu perfil (visible sólo para los coordinadores) y podrás cambiarla en cualquier momento. <span class='hint' title='Puedes subir imágenes con las extensiones: .jpg, .jpeg, .bmp, .png y .gif.'>Consulta los formatos de archivo admitidos</span></p>
          <p>Una vez subida, puedes modificar el zoom y el área de recorte de tu imagen.</p>
          <input type='file' name='archivo' id='archivo' accept='.png,.jpg,.jpeg,.bmp,.gif'>
          <br>
          <div id='imagen_perfil_texto'>
            <div>
              <p id='ajax_imagen'>Ninguna imagen seleccionada</p>
            </div>
          </div>
          <div id='imagen_perfil_container'></div>
          <br><br><br>
          <div class='center'>
            <label class='boton_inline' for='archivo'>Elegir archivo...</label>
          </div>
        </div>
    </div>
    </fieldset>

    <fieldset>
      <legend>Información personal</legend>
      <?php if ($_SESSION['estatus'] === 'coordinador') { ?>
        <label for='nombre'>Nombre(s)</label>
        <input type='text' name='nombre' value='<?php echo $usuario['nombre']; ?>' required>
        <label for='apellido_p'>Apellido paterno</label>
        <input type='text' name='apellido_p' value='<?php echo $usuario['apellido_p']; ?>' required>
        <label for='apellido_m'>Apellido materno</label>
        <input type='text' name='apellido_m' value='<?php echo $usuario['apellido_m']; ?>' required>
        <label for='correo'>Correo electrónico</label>
        <input type='text' name='correo' value='<?php echo $usuario['correo']; ?>' required>
        <label for='fecha_nac'>Fecha de nacimiento</label>
        <input type='text' name='fecha_nac' class='onlydatepicker' value='<?php echo $usuario['fecha_nac']; ?>' required>
        <label for='sexo'>Sexo</label>
        <select class='selectmenu' name='sexo'>
          <option value='mujer' <?php if ($usuario['sexo'] === 'mujer'){ echo 'selected'; }?>>Mujer</option>
          <option value='hombre' <?php if ($usuario['sexo'] === 'hombre'){ echo 'selected'; }?>>Hombre</option>
        </select>
      <?php }
      else { ?>
        <p><span class='bold'>Nombre(s):</span> <?php echo $usuario['nombre']; ?></p>
        <p><span class='bold'>Apellido paterno:</span> <?php echo $usuario['apellido_p']; ?></p>
        <p><span class='bold'>Apellido materno:</span> <?php echo $usuario['apellido_m']; ?></p>
        <p><span class='bold'>Correo electrónico:</span> <?php echo $usuario['correo']; ?></p>
        <p><span class='bold'>Fecha de nacimiento:</span> <?php echo $usuario['fecha_nac']; ?></p>
        <p><span class='bold'>Sexo:</span> <?php if ($usuario['sexo'] === 'mujer'){ echo 'Mujer'; } if ($usuario['sexo'] === 'hombre'){ echo 'Hombre'; }?></p>
      <?php } ?>
    </fieldset>

    <fieldset>
      <legend>Información académica</legend>
      <?php if ($_SESSION['estatus'] === 'coordinador') { ?>
        <label for='carrera'>Carrera</label>
        <select class='selectmenu' name='carrera'>
          <?php $carreras = $conexion->query("SELECT * FROM carreras");
          while ($carrera = $carreras->fetch_assoc()) { ?>
            <option value='<?php echo $carrera["carrera"]; ?>' <?php if ($carrera['carrera'] === $usuario['carrera']){ echo 'selected'; }?>><?php echo $carrera['carrera']; ?></option>
          <?php } ?>
        </select>
      <?php }
      else { ?>
        <p><span class='bold'>Carrera:</span> <?php echo $usuario['carrera']; ?></p>
      <?php } ?>
    </fieldset>

    <fieldset>
      <legend>Perfil como alumno Vértice</legend>
      <?php if ($_SESSION['estatus'] === 'coordinador') { ?>
        <label for='generacion'>Generación de Vértice</label>
        <select class='selectmenu' name='generacion' disabled>
          <?php
          $generaciones = $conexion->query("SELECT generacion FROM generaciones ORDER BY generacion");
          $generacion = $conexion->query("SELECT generacion FROM usuarios WHERE id='{$usuario['id']}'")->fetch_assoc()['generacion']; ?>
          <option value='0' <?php if ($generacion == '0') {echo 'selected'; } ?>>Sin generación</option>
          <?php while ($gen = $generaciones->fetch_assoc()) { ?>
            <option value='<?php echo $gen['generacion']; ?>' <?php if($gen['generacion'] == $generacion){ echo 'selected';}?>>Generación <?php echo $gen['generacion']; ?></option>
          <?php } ?>
        </select>
        <br>
      <?php }
      else { ?>
        <p><span class='bold'>Generación de Vértice:</span> <?php echo $usuario['generacion']; ?></p>
        <hr>
      <?php } ?>

      <p class='bold'>Información de inicio de sesión</p>
      <?php if ($_SESSION['estatus'] === 'coordinador') { ?>
        <label for='nombre_usuario'>Nombre de usuario</label>
        <input type='text' name='nombre_usuario' id='nombre_usuario' autocomplete='off' value="<?php echo $usuario['usuario']; ?>" required>
        <p id='disponibilidad'></p>
      <?php }
      else { ?>
        <p>Nombre de usuario: <?php echo $usuario['usuario']; ?></p>
      <?php }
      if  ($_SESSION['estatus'] === 'coordinador' && $usuario['id'] <> $_SESSION['id']) { ?>
        <p>Para generar una nueva contraseña para el usuario da clic en el siguiente botón:</p>
        <p class='boton_inline' onclick='newpassword();'>Restablecer contraseña del usuario</p>
        <p id='newpassword'></p>
        <script>
        function generatePassword() {
          var length = 8,
          charset = "abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789",
          retVal = "";
          for (var i = 0, n = charset.length; i < length; ++i) {
            retVal += charset.charAt(Math.floor(Math.random() * n));
          }
          return retVal;
        }
          function newpassword() {
            var $password = generatePassword();
            $('#newpassword').text('La nueva contraseña del usuario es: ' + $password);
            $.ajax({
              url: 'cambiar_password.php',
              data: {id: <?php echo $usuario['id']; ?>, password: $password}
            });
          }
        </script>
      <?php }
      else { ?>
        <p class='bold'>¿Quieres cambiar tu contraseña?</p>
        <label for='password_anterior'>Contraseña actual:</label>
        <input type='password' name='password_anterior'>
        <label for='password_nuevo_1'>Contraseña nueva:</label>
        <input type='password' name='password_nuevo_1'>
        <label for='password_nuevo_2'>Confirma tu nueva contraseña:</label>
        <input type='password' name='password_nuevo_2'>
      <?php } ?>

      <script>
        $('#nombre_usuario').keyup(function() {
          if ($('#nombre_usuario').val() == '') {
            $('#disponibilidad').text('');
          }
          else {
            setTimeout(function() {
              $.ajax({
                url: 'verificar_usuario.php',
                data: {usuario: $('#nombre_usuario').val()},
              }).done( function(data) {
                if (data == 0) {
                  $('#disponibilidad').removeClass('color-rojo');
                  $('#disponibilidad').addClass('color-verde');
                  $('#disponibilidad').text('Este nombre de usuario está disponible.');
                }
                else {
                  $('#disponibilidad').removeClass('color-verde');
                  $('#disponibilidad').addClass('color-rojo');
                  $('#disponibilidad').text('Este nombre de usuario no está disponible. Por favor elige otro.');
                }
              });
            }, 500);
          }
        });
      </script>

    </fieldset>
    <hr>
    <input type='hidden' name='flag' value='actualizar_perfil'>
    <input type='hidden' name='actualizacion_imagen' id='actualizacion_imagen' value='0'>
    <input type='hidden' name='imagen_crop0'>
    <input type='hidden' name='imagen_crop1'>
    <input type='hidden' name='imagen_crop2'>
    <input type='hidden' name='imagen_crop3'>
    <input type='hidden' name='id' value="<?php echo $usuario['id']; ?>">
    <input type='submit' value='Guardar los cambios' class='boton'>
    <div class='aclarador'></div>
  </form>
</div>

<?php if ($_SESSION['estatus'] === 'coordinador' && $_SESSION['id'] <> $usuario['id']) { ?>
  <div class='card'>
    <h2>Acciones especiales para el alumno</h2>
    <p class='color-rojo'><i class='fas fa-exclamation-triangle fa-fw'></i>Todas estas acciones son irreversibles. El sistema no solicitará confirmación antes de proceder.</p>
    <p>Dar de alta al alumno como coordinador:</p>
    <form method='POST' action='?d=alumnos'>
      <input type='hidden' name='flag' value='alta_coordinador'>
      <input type='hidden' name='id' value='<?php echo $usuario['id']; ?>'>
      <input type='submit' value='Dar de alta a <?php echo $usuario['nombre']; ?> como coordinador' class='boton_inline'>
      <div class='aclarador'></div>
    </form>
    <p>Eliminar el perfil del alumno y toda la información asociada a él:</p>
    <p class='boton_inline' id='boton_eliminar'>Eliminar a <?php echo $usuario['nombre']; ?> del sistema</p>
    <form method='POST' action='?d=alumnos' id='formulario_eliminar' style='display:none;'>
      <p class='color-rojo'>¿Estás seguro que deseas eliminar definitivamente a este alumno del sistema?</p>
      <input type='hidden' name='flag' value='eliminar_alumno'>
      <input type='hidden' name='id' value='<?php echo $usuario['id']; ?>'>
      <input type='submit' value='Sí, eliminar a <?php echo $usuario['nombre']; ?> del sistema' class='boton_inline'>
      <div class='aclarador'></div>
    </form>
    <script>
      $('#boton_eliminar').click(function() {
        $(this).hide();
        $('#formulario_eliminar').show();
      });
    </script>
  </div>

<?php } ?>

<script>
  $('#imagen_perfil_container').croppie({
    viewport: {
      height: 250,
      width: 250
    }
  });

  function link_cropping_points() {
    $('#imagen_perfil_container').on('update', function(){
      $('[name=imagen_crop0]').val($('#imagen_perfil_container').croppie('get')['points'][0]);
      $('[name=imagen_crop1]').val($('#imagen_perfil_container').croppie('get')['points'][1]);
      $('[name=imagen_crop2]').val($('#imagen_perfil_container').croppie('get')['points'][2]);
      $('[name=imagen_crop3]').val($('#imagen_perfil_container').croppie('get')['points'][3]);
    });
  }

  $('[name=archivo]').on('change', function(event) {
    $('#actualizacion_imagen').val('0');
    $('#imagen_perfil_container').hide();
    $('#imagen_perfil_texto').show();
    var file_data = $('[name=archivo]').prop('files')[0];
    var form_data = new FormData();
    form_data.append('file', file_data);
    form_data.append('id', '<?php echo $_SESSION['id']; ?>');
    if (typeof file_data != 'undefined') {
      var file_size = file_data['size']/1000000;
      if (file_size > 5) {
        $('#ajax_imagen').text('El tamaño del archivo supera el límite de 5 MB. Por favor elige otro.');
      }
      else {
        $('#ajax_imagen').text('Subiendo imagen...');
        $.ajax({
          url: 'upload.php',
          dataType: 'text',
          cache: false,
          contentType: false,
          processData: false,
          data: form_data,
          type: 'post'
        }).done( function(data) {
          $('#actualizacion_imagen').val('1');
          $('#imagen_perfil_texto').hide();
          $('#ajax_imagen').text('Ninguna imagen seleccionada.');
          $('#imagen_perfil_container').show();
          $('#imagen_perfil_container').croppie('bind', {
            url: 'perfiles/' + data + '.png?d=' + Date.now(),
          });
          link_cropping_points();
        }).fail( function() {
          $('#ajax_imagen').text('Hubo un error en el servidor. Trata de nuevo, y si sigues teniendo poblemas contacta a un coordinador.');
        });
      }
    }
    else {
      $('#ajax_imagen').text('Ninguna imagen seleccionada.');
    }
  });
</script>
