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
        <div class="row text-left">
          <div class="col">
            <h3 class='mb-5'>Registro y seguimiento a becarios</h3>
            <p class="text-warning">Hay un error en la red y el sistema no puede iniciarse.</p>
            <p>Por favor verifica que la conexión a la red sea correcta.</p>
            <a href="index.php"><button class="btn btn-primary">Reintentar</button></a>
          </div>
        </div>
      </main>

      <footer class="mastfoot mt-auto">
        <div class="inner">
          <p><a href="admin.php">Administración del sistema</a></br>
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
    </script>

  </body>
</html>
