<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<link rel="icon" href="img/favicon.png">

		<!-- Hojas de estilo: -->
		<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700" rel="stylesheet">
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
		<link href="jquery-ui.min.css" rel="stylesheet">
		<link href="estilo.css" rel="stylesheet">
		<link  href="croppie.css" rel="stylesheet">


		<!-- Javascripts: -->
		<script src="dc3d686551.js"></script>
		<script src="jquery-1.12.4.min.js"></script>
		<script src="jquery-ui.min.js"></script>
		<script src="croppie.js"></script>

		<title>VÉRTICE | Sistema de aplicación en línea</title>

		<script>
		$(document).ready( function() {
			$(document).tooltip();
			$.getScript('algoritmo.js?version=2_0');
		});
		</script>

		<style>
		.ui-tooltip {
			padding: 10px;
			background-color: #ffffff;
			box-shadow: none;
			color: #000;
			font-family: "Open Sans", sans-serif;
			font-size: 12px;
		}
		</style>

	</head>
	<body>

		<?php require_once 'config.php'; ?>

		<!-- Contenidos -->
		<ul class="topnav">
			<li><a href="index.php"><img src="img/logo.png"></a></li>
		</ul>
		<div class='outer'>
      <div class='middle'>
        <div class='inner center'>

					<div id='login'>
						<h1>Gracias por tu interés en formar parte de Vértice</h1>
            <p><i class='fa fa-clock-o' id='reloj'></i> El sistema de aplicación en línea estará disponible próximamente.</p>
					</div>

					<script>

						function redireccionar() {
							$('#login p').text('Redireccionando...');
							setTimeout(function() {
								window.location.replace("http://ww2.anahuac.mx/verticeintranet/aplica/index2.php");
							}, 1000);
						}

						var actual_date = new Date();
						var open_date = new Date('September 24, 2018 12:00:00');

						if (actual_date.getTime() > open_date.getTime()) {
							window.location.replace("http://ww2.anahuac.mx/verticeintranet/aplica/index2.php");
						}

						var clicks = 0;
						var timer;
						$('#reloj').click(function() {
							clearTimeout(timer);
							if (clicks >= 4) {
								redireccionar();
							}
							clicks = clicks + 1;
							timer = setTimeout(function() {
								clicks = 0;
								clearTimeout(timer);
							}, 1000);
						});
					</script>

        </div>
      </div>
    </div>

	</body>
</html>
