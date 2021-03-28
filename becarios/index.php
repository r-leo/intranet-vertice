<?php
  session_start();
  require_once '../config.php';
  $becarios_aplicacion = $conexion->query("SELECT becarios_aplicacion FROM ajustes WHERE 1")->fetch_assoc()['becarios_aplicacion'];
  $clave_becarios = $conexion->query("SELECT clave_becarios FROM ajustes WHERE 1")->fetch_assoc()['clave_becarios'];
  if ($becarios_aplicacion == '0') {
    header("Location: shutdown.php");
    exit();
  }
  if (isset($_POST['flag'])) {
    if ($_POST['flag'] == 'authenticate' && isset($_POST['password'])) {
      $_SESSION['clave_becarios'] = $_POST['password'];
    }
  }
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

      <main role="main" class="inner cover text-left">

        <?php
        // Aplicación autenticada:
        if ($_SESSION['clave_becarios'] == $clave_becarios) { ?>
          <div class="row">
            <h3 class='mb-5'>Registro y seguimiento a becarios</h3>
          </div>
          <div class="row">
            <div class="col-8">
              <input id="expediente" type="number" class="form-control" placeholder="Número de expediente">
            </div>
            <div class="col-2">
              <button id="btnContinuar" class="btn btn-primary">Continuar</button>
            </div>
          </div>
        <?php }

        // Aplicación no autenticada:
        else { ?>
          <form method='post' action='index.php'>
            <div class='row'>
              <div class='col-10'>
                <p class='text-warning'>La aplicación no está autenticada. Introduce la clave de la aplicación:</p>
              </div>
            </div>
            <div class="form-row">
              <div class="col-8">
                <input id="password" name='password' type="password" class="form-control" placeholder="Clave de aplicación">
              </div>
              <div class="col-2">
                <button type='submit' class="btn btn-primary">Validar</button>
              </div>
            </div>
            <input type='hidden' name='flag' value='authenticate'>
          </form>
        <?php } ?>
      </main>

      <footer class="mastfoot mt-auto">
        <div class="inner">
          <p id='dauth'><a href="#">Revocar acceso para esta sesión</a></br>
          &copy; <?php echo date("Y"); ?> Rodrigo Leo</p>
        </div>
      </footer>
    </div>

    <!-- Scripts -->
    <script src="jquery-3.2.1.slim.min.js"></script>
    <script src="popper.min.js"></script>
    <script src="bootstrap.min.js"></script>

    <script>
      $("#btnContinuar").click(function(){
        location.href = "usuario.php?expediente=" + $("#expediente").val();
      });
      $('#dauth').click(function(){
        dauth_confirm = confirm('Esto cerrará la sesión actual. ¿Deseas continuar?');
        if (dauth_confirm == true) {
          location.href = "dauth.php";
        }
      });
    </script>

  </body>
</html>
