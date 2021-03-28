<?php
  session_start();
  if (!isset($_GET["expediente"]) || $_GET["expediente"] == "") {
    header("Location: index.php");
    exit();
  }
  else {
    require_once "../config.php";
    $becarios_aplicacion = $conexion->query("SELECT becarios_aplicacion FROM ajustes WHERE 1")->fetch_assoc()['becarios_aplicacion'];
    $clave_becarios = $conexion->query("SELECT clave_becarios FROM ajustes WHERE 1")->fetch_assoc()['clave_becarios'];
    if ($becarios_aplicacion == '0' || $_SESSION['clave_becarios'] !== $clave_becarios) {
      header("Location: index.php");
      exit();
    }
  }

  date_default_timezone_set('America/Mexico_City');
  $date = date('Y-m-d');
?>

<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="bootstrap.min.css">
    <link href="cover.css" rel="stylesheet">
    <title>Sistema de becarios VÉRTICE</title>
  </head>
  <body class="text-center">

    <div class="cover-container d-flex h-100 p-3 mx-auto flex-column">
      <header class="masthead mb-auto">
      </header>

      <main role="main" class="inner cover">

        <?php $usuario = $conexion -> query("SELECT * FROM becarios WHERE id={$_GET['expediente']}");

        // Becario registrado:
        if ($usuario -> num_rows > 0) {
          $usuario_fetched = $usuario->fetch_assoc();
          $asistencia = $conexion -> query("SELECT fecha FROM becarios_asistencias WHERE id={$usuario_fetched['id']} AND fecha='$date'");
          $asistencias_usuario = $usuario_fetched['asistencias'];
          $numero_asistencias = $conexion -> query("SELECT COUNT(fecha) AS numero_asistencias FROM becarios_asistencias WHERE id={$usuario_fetched['id']}")->fetch_assoc()['numero_asistencias'] + $asistencias_usuario;

          // Asistencia registrada en el mismo día:
          if ($asistencia -> num_rows > 0) { ?>
            <div class="row">
              <div class="col text-left">
                <h3>Hola, <?php echo $usuario_fetched['nombre']; ?></h3>
                <p>Hoy ya tienes tu asistencia registrada.</p>
                <hr class='border-secondary'>
                <p>Número de asistencias registradas:</p>
                <h3><?php echo $numero_asistencias; ?></h3>
                <hr class='border-secondary'>
                <a href="index.php"><button class="btn btn-primary">Continuar</button></a>
              </div>
            </div>
          <?php }
          else { ?>

            <!-- Pantalla para registrar asistencia -->
            <div id="pgAsistencia" class="row">
              <div class="col text-left">
                <h3>Hola, <?php echo $usuario_fetched['nombre']; ?></h3>
                <p>Da clic en el siguiente botón para registrar tu asistencia de hoy:</p>
                <button id="btnAsistencia" class="btn btn-success">Registrar asistencia</button>
              </div>
            </div>

            <!-- Pantalla de asistencia exitosa -->
            <div id="pgExito" class="row" style="display: none;">
              <div class="col text-left">
                <h3>Tu asistencia ha sido registrada</h3>
                <hr class='border-secondary'>
                <p>Número de asistencias registradas:</p>
                <h3><?php echo $numero_asistencias + 1; ?></h3>
                <hr class='border-secondary'>
                <a href="index.php"><button id="btnSalir" class="btn btn-primary">Salir</button></a>
              </div>
            </div>

            <!-- Pantalla de asistencia fallida -->
            <div id="pgFalla" class="row" style="display: none;">
              <div class="col text-left">
                <h3>Hubo un <span class="text-warning">error</span> al registrar tu asistencia.</h3>
                <p>Por favor contacta a un coordinador.</p>
                <p>Código del error: <code id="codigoError" class='text-light'></code></p>
                <a href="usuario.php?expediente=<?php echo $_GET['expediente']; ?>"<button class="btn btn-primary">Continuar</button></a>
              </div>
            </div>

          <?php }
        }

        // Becario no registrado:
        else { ?>

          <!-- Formulario de registro -->
          <div id="pgRegistro" class="row">
            <div class="col text-left">
              <h3>Registro de becarios nuevos</h3>
              <p>Por favor llena los siguientes campos con tu información. Este paso sólo deberás hacerlo una vez.</p>
              <div class="form-row mb-1">
                <div class="col-4">
                  <label for="expediente">Número de expediente</label>
                </div>
                <div class="col-6">
                  <input type="number" id="expediente" name="expediente" class="form-control" value="<?php echo $_GET['expediente']; ?>" readonly>
                </div>
              </div>
              <div class="form-row mb-1">
                <div class="col-4">
                  <label for="nombre">Nombre(s)</label>
                </div>
                <div class="col-6">
                  <input type="text" id="nombre" name="nombre" class="form-control">
                </div>
              </div>
              <div class="form-row mb-1">
                <div class="col-4">
                  <label for="apellido_p">Apellido paterno</label>
                </div>
                <div class="col-6">
                  <input type="text" id="apellido_p" name="apellido_p" class="form-control">
                </div>
              </div>
              <div class="form-row mb-1">
                <div class="col-4">
                  <label for="apellido_m">Apellido materno</label>
                </div>
                <div class="col-6">
                  <input type="text" id="apellido_m" name="apellido_m" class="form-control">
                </div>
              </div>
              <div class="form-row mb-1">
                <div class="col-4">
                  <label for="correo">Correo electrónico</label>
                </div>
                <div class="col-6">
                  <input type="text" id="correo" name="correo" class="form-control">
                </div>
              </div>
              <div class="form-row mb-2">
                <div class="col-4">
                  <label for="carrera">Carrera</label>
                </div>
                <div class="col-6">
                  <select class="form-control" id="carrera" name="carrera">
                    <?php $carreras = $conexion -> query("SELECT carrera FROM carreras WHERE 1 ORDER BY carrera");
                    while ($carrera = $carreras -> fetch_assoc()) { ?>
                      <option><?php echo $carrera["carrera"]; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="text-center">
                <a href="index.php"><button id="btnCancelar" class="btn btn-warning mr-3">Cancelar</button></a>
                <button id="btnGuardar" class="btn btn-primary" style="display: none;">Guardar información</button>
              </div>
            </div>
          </div>

          <!-- Pantalla de confirmación -->
          <div id="pgConfirmacion" class="row" style="display: none;">
            <div class="col text-left">
              <h3>Listo.</h3>
              <p>Tu información ha sido guardada correctamente.</p>
              <a href="usuario.php?expediente=<?php echo $_GET['expediente']; ?>"><button class="btn btn-primary">Continuar</button></a>
            </div>
          </div>

          <!-- Pantalla de error -->
          <div id="pgError" class="row" style="display: none;">
            <div class="col text-left">
              <h3>Hubo un <span class="text-warning">error</span> al darte de alta.</h3>
              <p>Por favor contacta a un coordinador.</p>
              <p>Código del error: <code id="codigoError" class='text-light'></code></p>
              <a href="usuario.php?expediente=<?php echo $_GET['expediente']; ?>"<button class="btn btn-primary">Continuar</button></a>
            </div>
          </div>

        <?php } ?>

      </main>

      <footer class="mastfoot mt-auto">
        <div class="inner">
          <p>&copy; <?php echo date("Y"); ?> Rodrigo Leo</p>
        </div>
      </footer>
    </div>

    <!-- Scripts -->
    <script src="jquery.min.js"></script>
    <script src="popper.min.js"></script>
    <script src="bootstrap.min.js"></script>

    <script>
      $("#nombre, #apellido_p, #apellido_m, #correo").keyup(function(){
        $condicion = $("#nombre").val() != "" & $("#apellido_p").val() != "" & $("#apellido_m").val() != "" & $("#correo").val() != "";
        if ($condicion) {
          $("#btnGuardar").fadeIn();
        }
        else {
          $("#btnGuardar").fadeOut();
        }
      });

      $("#btnGuardar").click(function(){
        // Registrar información en la base de datos:
        $.ajax({
          method: "post",
          url: "registrar_usuario.php",
          data: {
            expediente: $("#expediente").val(),
            nombre:     $("#nombre").val(),
            apellido_p: $("#apellido_p").val(),
            apellido_m: $("#apellido_m").val(),
            correo:     $("#correo").val(),
            carrera:    $("#carrera").val()
          }
        })
        .done(function(data) {
          if (data == '1') {
            $("#pgRegistro").fadeOut(400, function(){ $("#pgConfirmacion").fadeIn(); });
          }
          else {
            $("#codigoError").append(data);
            $("#pgRegistro").fadeOut(400, function(){ $("#pgError").fadeIn(); });
            console.log(data);
          }
        });
      });

      $("#btnAsistencia").click(function(){
        $.ajax({
          method: "post",
          url: "registrar_asistencia.php",
          data: {
            expediente: "<?php echo $_GET['expediente']; ?>"
          }
        })
        .done(function(data) {
          if (data == '1') {
            $("#pgAsistencia").fadeOut(400, function(){ $("#pgExito").fadeIn(); });
          }
          else {
            $("#codigoError").append(data);
            $("#pgAsistencia").fadeOut(400, function(){ $("#pgFalla").fadeIn(); });
            console.log(data);
          }
        });
      });
    </script>

  </body>
</html>
