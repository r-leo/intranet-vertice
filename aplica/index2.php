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
		<link href="estilo.css?version=2_0" rel="stylesheet">
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
			<li id='acceso_coordinadores'><a href="#" id='admin'><i class='fa fa-cogs inline'></i></a></li>
		</ul>
		<div class='outer'>
      <div class='middle'>
        <div class='inner center'>

					<!-- Ventana de procesamiento -->
					<div id='loading'>
						<p>Cargando...</p>
					</div>

					<!-- Mensaje de sistema cerrado -->
					<!--<div id='login'>
						<h1>Gracias por tu interés en fomar parte de Vértice.</h1>
						<p>El periodo de entrevistas para el programa ha concluido, y el sistema de apliación en línea ya se encuentra cerrado.</p>
						<p>Espera la publicación de los resultados del proceso de selección a finales del mes de octubre.</p>
					</div>-->

					<!-- Ventana de login -->
					<div id='login'>
						<h1>Comienza o continúa tu proceso de aplicación aquí.</h1>
						<p class='mensaje'></p>
            <form method='post'>
              <input class='principal' type='number' name='expediente' required placeholder='Expediente...'>
              <button class='continuar' type='button'><i class='fa fa-arrow-right inline'></i></button>
            </form>
					</div>

					<!-- Ventana de login (admin) -->
					<div id='login_admin'>
						<h1>Acceso a coordinadores</h1>
						<p class='mensaje'></p>
            <form method='post'>
              <input class='principal' type='password' name='clave' required placeholder='Clave de acceso...'>
              <button class='continuar' type='button'><i class='fa fa-arrow-right inline'></i></button>
            </form>
					</div>

					<!-- Ventana de registro (contraseña) -->
					<div id='registro_pw'>
						<h1>Parece que eres un usuario nuevo.</h1><br>
						<p>Elige una contraseña:</p>
						<p class='mensaje'></p>
            <form method='post'>
              <input class='registro' type='password' name='password_1' required placeholder='Contraseña...'>
							<input class='registro' type='password' name='password_2' required placeholder='Repetir contraseña...'>
            </form>
						<hr>
						<button class='continuar' type='button'>Continuar&nbsp;&nbsp;<i class='fa fa-arrow-right inline'></i></button>
					</div>

					<!-- Ventana de login (contraseña) -->
					<div id='login_pw'>
						<h1 class='bienvenida'></h1><br>
						<p>Escribe tu contraseña para entrar:</p>
						<p class='mensaje'></p>
            <form method='post'>
              <input class='principal' type='password' name='password' required placeholder='Contraseña...'>
            </form>
						<hr>
						<button class='continuar' type='button'>Continuar&nbsp;&nbsp;<i class='fa fa-arrow-right inline'></i></button>
					</div>

					<!-- Ventana de registro (fotografía) -->
					<div id='registro_1'>
						<h1>Paso 1/3: sube una fotografía</h1>
						<hr>
						<form enctype="multipart/form-data" method="post">
							<p id='mensaje_crop'>Puedes modificar el zoom y el área de recorte de la imagen:</p>
							<div id='texto_foto'>
								<div><p id='mensaje_foto'>No has seleccionado ninguna imagen.</p></div>
							</div>
							<div id='campo_foto'></div>
							<br><br><br>
						  <input type="file" name='archivo_imagen' id='archivo_imagen' accept='.png,.jpg,.jpeg,.bmp,.gif'>
							<label class='continuar' for='archivo_imagen'><i class='fa fa-cloud-upload'></i>Elegir archivo</label>
						</form>
						<hr>
						<button class='continuar' type='button' style='display:none;'>Continuar&nbsp;&nbsp;<i class='fa fa-arrow-right inline'></i></button>
					</div>

					<!-- Ventana de registro (datos) -->
					<div id='registro_2'>
						<h1>Paso 2/3: registra tus datos</h1>
						<hr>
						<p class='mensaje'></p>
						<form method='post'>
							<input class='registro' type='text' name='nombre' required placeholder='Nombre(s)'>
							<input class='registro' type='text' name='apellido_p' required placeholder='Apellido paterno'>
							<input class='registro' type='text' name='apellido_m' required placeholder='Apellido materno'>
							<input class='registro' type='text' name='correo' required placeholder='Correo electrónico'>
							<select name='sexo'>
								<option value='0' selected disabled>Sexo...</option>
								<option value='mujer'>Mujer</option>
								<option value='hombre'>Hombre</option>
							</select>
							<select name='carrera'>
								<option value='0' selected disabled>Carrera...</option>
								<?php
									$carreras = $conexion->query("SELECT * FROM carreras ORDER BY carrera");
									while ($carrera = $carreras->fetch_assoc()) { ?>
										<option value='<?php echo $carrera['carrera']; ?>'><?php echo $carrera['carrera']; ?></option>
									<?php } ?>
							</select>
							<p>Fecha de nacimiento:</p>
							<input class='registro' type='date' name='fecha_nac' required>
						</form>
						<hr>
						<p><i class='fa fa-exclamation-triangle'></i>Es importante que verifiques que tu información sea correcta.</p>
						<button class='continuar' type='button'>Continuar&nbsp;&nbsp;<i class='fa fa-arrow-right inline'></i></button>
					</div>

					<!-- Ventana de registro (entrevista) -->
					<div id='registro_3'>
						<h1>Paso 3/3: agenda tu entrevista</h1>
						<hr>
						<p class='mensaje'></p>

						<div class='entrevista' id='26'>
							<p class='bold'><i class='fa fa-calendar'></i>Miércoles 26 de septiembre</p>
							<p>Horarios disponibles:</p>
							<select class='horarios' id='horarios_26'>
								<option value='0'selected>Selecciona...</option>
								<option value='10'>10:00 hrs</option>
								<option value='11'>11:30 hrs</option>
								<option value='13'>13:00 hrs</option>
								<option value='16'>16:00 hrs</option>
								<option value='17'>17:30 hrs</option>
							</select>
							<hr>
						</div>


						<div class='entrevista' id='27'>
							<p class='bold'><i class='fa fa-calendar'></i>Jueves 27 de septiembre</p>
							<p>Horarios disponibles:</p>
							<select class='horarios' id='horarios_27'>
								<option value='0' selected>Selecciona...</option>
								<option value='10'>10:00 hrs</option>
								<option value='11'>11:30 hrs</option>
								<option value='13'>13:00 hrs</option>
								<option value='16'>16:00 hrs</option>
								<option value='17'>17:30 hrs</option>
							</select>
							<hr>
						</div>

						<div class='entrevista' id='28'>
							<p class='bold'><i class='fa fa-calendar'></i>Viernes 28 de septiembre</p>
							<p>Horarios disponibles:</p>
							<select class='horarios' id='horarios_28'>
								<option value='0' selected>Selecciona...</option>
								<option value='10'>10:00 hrs</option>
								<option value='11'>11:30 hrs</option>
								<option value='13'>13:00 hrs</option>
								<option value='16'>16:00 hrs</option>
								<option value='17'>17:30 hrs</option>
							</select>
							<hr>
						</div>

						<div class='entrevista' id='8'>
							<p class='bold'><i class='fa fa-calendar'></i>Lunes 8 de octubre</p>
							<p>Horarios disponibles:</p>
							<select class='horarios' id='horarios_8'>
								<option value='0' selected>Selecciona...</option>
								<option value='10'>10:00 hrs</option>
								<option value='11'>11:30 hrs</option>
								<option value='13'>13:00 hrs</option>
								<option value='16'>16:00 hrs</option>
								<option value='17'>17:30 hrs</option>
							</select>
							<hr>
						</div>

						<div class='entrevista' id='9'>
							<p class='bold'><i class='fa fa-calendar'></i>Martes 9 de octubre</p>
							<p>Horarios disponibles:</p>
							<select class='horarios' id='horarios_9'>
								<option value='0' selected>Selecciona...</option>
								<option value='10'>10:00 hrs</option>
								<option value='11'>11:30 hrs</option>
								<option value='13'>13:00 hrs</option>
								<option value='16'>16:00 hrs</option>
								<option value='17'>17:30 hrs</option>
							</select>
							<hr>
						</div>

						<div class='entrevista' id='10'>
							<p class='bold'><i class='fa fa-calendar'></i>Miércoles 10 de octubre</p>
							<p>Horarios disponibles:</p>
							<select class='horarios' id='horarios_10'>
								<option value='0' selected>Selecciona...</option>
								<option value='10'>10:00 hrs</option>
								<option value='11'>11:30 hrs</option>
								<option value='13'>13:00 hrs</option>
								<option value='16'>16:00 hrs</option>
								<option value='17'>17:30 hrs</option>
							</select>
							<hr>
						</div>

						<div class='entrevista' id='11'>
							<p class='bold'><i class='fa fa-calendar'></i>Jueves 11 de octubre</p>
							<p>Horarios disponibles:</p>
							<select class='horarios' id='horarios_11'>
								<option value='0' selected>Selecciona...</option>
								<option value='10'>10:00 hrs</option>
								<option value='11'>11:30 hrs</option>
								<option value='13'>13:00 hrs</option>
								<option value='16'>16:00 hrs</option>
								<option value='17'>17:30 hrs</option>
							</select>
							<hr>
						</div>

						<div class='entrevista' id='12'>
							<p class='bold'><i class='fa fa-calendar'></i>Viernes 12 de octubre</p>
							<p>Horarios disponibles:</p>
							<select class='horarios' id='horarios_12'>
								<option value='0' selected>Selecciona...</option>
								<option value='10'>10:00 hrs</option>
								<option value='11'>11:30 hrs</option>
								<option value='13'>13:00 hrs</option>
								<option value='16'>16:00 hrs</option>
								<option value='17'>17:30 hrs</option>
							</select>
							<hr>
						</div>

						<div class='entrevista' id='15'>
							<p class='bold'><i class='fa fa-calendar'></i>Lunes 15 de octubre</p>
							<p>Horarios disponibles:</p>
							<select class='horarios' id='horarios_15'>
								<option value='0' selected>Selecciona...</option>
								<option value='10'>10:00 hrs</option>
								<option value='11'>11:30 hrs</option>
								<option value='13'>13:00 hrs</option>
								<option value='16'>16:00 hrs</option>
								<option value='17'>17:30 hrs</option>
							</select>
							<hr>
						</div>

						<div class='entrevista' id='16'>
							<p class='bold'><i class='fa fa-calendar'></i>Martes 16 de octubre</p>
							<p>Horarios disponibles:</p>
							<select class='horarios' id='horarios_16'>
								<option value='0' selected>Selecciona...</option>
								<option value='10'>10:00 hrs</option>
								<option value='11'>11:30 hrs</option>
								<option value='13'>13:00 hrs</option>
								<option value='16'>16:00 hrs</option>
								<option value='17'>17:30 hrs</option>
							</select>
							<hr>
						</div>

						<div class='entrevista' id='17'>
							<p class='bold'><i class='fa fa-calendar'></i>Martes 16 de octubre</p>
							<p>Horarios disponibles:</p>
							<select class='horarios' id='horarios_17'>
								<option value='0' selected>Selecciona...</option>
								<option value='10'>10:00 hrs</option>
								<option value='11'>11:30 hrs</option>
								<option value='13'>13:00 hrs</option>
								<option value='16'>16:00 hrs</option>
								<option value='17'>17:30 hrs</option>
							</select>
							<hr>
						</div>

						<div class='entrevista' id='18'>
							<p class='bold'><i class='fa fa-calendar'></i>Martes 16 de octubre</p>
							<p>Horarios disponibles:</p>
							<select class='horarios' id='horarios_18'>
								<option value='0' selected>Selecciona...</option>
								<option value='10'>10:00 hrs</option>
								<option value='11'>11:30 hrs</option>
								<option value='13'>13:00 hrs</option>
								<option value='16'>16:00 hrs</option>
								<option value='17'>17:30 hrs</option>
							</select>
							<hr>
						</div>

						<div class='entrevista' id='19'>
							<p class='bold'><i class='fa fa-calendar'></i>Martes 16 de octubre</p>
							<p>Horarios disponibles:</p>
							<select class='horarios' id='horarios_19'>
								<option value='0' selected>Selecciona...</option>
								<option value='10'>10:00 hrs</option>
								<option value='11'>11:30 hrs</option>
								<option value='13'>13:00 hrs</option>
								<option value='16'>16:00 hrs</option>
								<option value='17'>17:30 hrs</option>
							</select>
							<hr>
						</div>

						<p class='bold'>Verifica tu día y hora de entrevista antes de continuar:</p>
						<p id='fecha_entrevista'>Ningún horario seleccionado</p>
						<button class='continuar' type='button'>Continuar&nbsp;&nbsp;<i class='fa fa-arrow-right inline'></i></button>
					</div>

					<!-- Ventana de usuario -->
					<div id='usuario'>
						<h1 class='bienvenida'></h1>
						<hr>
						<table>
							<tr><td><i class='fa fa-user'></i></td><td><p id='campo_nombre'></p></td></tr>
							<tr><td><i class='fa fa-graduation-cap'></i></td><td><p id='campo_carrera'></p></td></tr>
							<tr><td><i class='fa fa-envelope'></i></td><td><p id='campo_mail'></p></td></tr>
						</table>
						<p class='bold'>Recuerda acudir a tu entrevista:</p>
						<p id='campo_entrevista'><i class='fa fa-calendar'></i></p>
						<hr>
						<button class='salir' type='button'>Cerrar&nbsp;&nbsp;<i class='fa fa-times inline'></i></button>
					</div>

        </div>
      </div>
    </div>

	</body>
</html>
