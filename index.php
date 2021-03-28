<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<?php include_once "config.php"; ?>

<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<link rel="icon" href="img/favicon.png">

		<!-- Hojas de estilo: -->
		<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700" rel="stylesheet">
		<link href="https://fonts.googleapis.com/css?family=Lato:400,900" rel="stylesheet">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
		<link href="css/estilo.css?version=<?php echo $sufijo_version; ?>" rel="stylesheet">

		<!-- Javascripts: -->
		<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
		<script defer src="https://use.fontawesome.com/releases/v5.0.13/js/all.js" integrity="sha384-xymdQtn1n3lH2wcu0qhcdaOpQwyoarkgLVxC/wZ5q7h9gHtxICrpcaSUfygqZGOe" crossorigin="anonymous"></script>

		<title>Intranet Vértice</title>

	</head>
	<body>

		<?php
		include 'config.php';
		?>

		<ul class="topnav">
		  <li><a href="index.php"><img src="img/logo.png"></a></li>
		</ul>

		<!-- Contenidos -->
		<div class="contenedor">
		  <div class="fila">
		    <div class="col-2">
		      <!--<div class="card"></div>-->
		    </div>

				<?php if (!$modo_debug) {
					if ($conexion->connect_error) { ?>
						<div class="col-6">
							<div class='card-important center'>
								<p class='color-rojo'><i class='fas fa-exclamation-triangle fa-fw'></i></p>
								<h2>Error del sistema</h2>
								<p>Hay un error en el sistema que impide utilizar el sitio normalmente.</p>
								<p>Por favor reporta esta situación a los coordinadores del Programa.</p>
								<hr>
								<p>Detalles del error:<br>
									<code>
										<?php echo $conexion->connect_error; ?>
									</code></p>
							</div>
				    </div>
					<?php }

					else { ?>
				    <div class="col-6">
							<?php if (isset($_GET['code'])) { ?>
								<div class='card-important center'>
									<?php if ($_GET['code'] === '1') { ?>
										<h2>Error de autenticación</h2>
										<p>Usuario y/o contraseña no válido(s).</p>
										<p>Recuerda que el nombre de usuario y la contraseña son sensibles a mayúsculas. Si no recuerdas tu usuario y/o contraseña ponte en contacto con un coordinador.</p>
									<?php }
									elseif ($_GET['code'] === '2') { ?>
										<h2>Sesión expirada</h2>
										<p>No has ingresado al sistema o bien tu sesión expiró.</p>
										<p>Por favor vuelve a escribir tus credenciales de acceso.</p>
									<?php }
									elseif ($_GET['code'] === '3') { ?>
										<h2>Sesión cerrada</h2>
										<p>Has cerrado correctamente tu sesión.</p>
									<?php } ?>
								</div>
							<?php }
							else { ?>
								<br><h2 class='center'>Bienvenido</h2><br><br>
							<?php } ?>

							<div class='card empty'>
								<div id='welcome_bar'>
									<div class='tab highlighted' id='tab_ur'>Usuarios registrados</div>
									<div class='tab' id='tab_un'>Usuarios nuevos</div>
									<div class='aclarador'></div>
								</div>

								<div class='welcome_area center' id='welcome_ur'>
									<form action="intranet.php" method="post">
										<label for='usuario'>Usuario:</label><br>
										<input type="text" name="usuario" class='center'><br>
										<label for='password'>Contraseña:</label><br>
										<input type="password" name="password" class='center' autocomplete='off'><br>
										<input type="submit" class='boton_inline' value="Entrar">
										<div class='aclarador'></div>
									</form>
									<p><span class='link' id='recuperar_pw'>Olvidé mi usuario y/o contraseña</span></p>
								</div>

								<div class='welcome_area center' id='welcome_un'>
									<form action="intranet.php" method="post">
										<label for='usuario'>Número de expediente:</label><br>
										<input type="text" name="usuario" class='center'><br>
										<input type="submit" class='boton_inline' value="Entrar">
										<div class='aclarador'></div>
									</form>
								</div>

								<div class='welcome_area center' id='welcome_pw'>
									<p>Escribe el correo electrónico que tienes registrado:</p>
									<input type="text" id='mail_pw' name="mail_pw" class='center'><br>
									<p class='boton_inline' id='btn_pw'>Recuperar</p>
									<div class='aclarador'></div>
								</div>

								<div class='welcome_area center' id='success_pw'>
									<p>Se ha enviado un correo electrónico a la dirección especificada.</p>
									<p>No olvides revisar tu bandeja de correo no deseado.</p>
									<div class='aclarador'></div>
								</div>

									<div class='welcome_area center' id='pw_error'>
										<p>Ha habido el siguiente error al procesar tu solicitud:</p>
										<code id='pw_error_msg'></code>
										<p>Pide ayuda a un coordinador para restablecer tu contraseña.</p>
										<div class='aclarador'></div>
									</div>

								</div>
							</div>

							<script>
								var tab_activa = 'ur';

								$('#tab_ur').click(function() {
									if (tab_activa != 'ur') {
										$('#welcome_un').hide();
										$('#welcome_ur').show();
										$('#tab_un').removeClass('highlighted');
										$('#tab_ur').addClass('highlighted');
										tab_activa = 'ur';
									}
								});

								$('#tab_un').click(function() {
									if (tab_activa != 'un') {
										$('#welcome_ur').hide();
										$('#welcome_un').show();
										$('#tab_ur').removeClass('highlighted');
										$('#tab_un').addClass('highlighted');
										tab_activa = 'un';
									}
								});

								$('#recuperar_pw').click(function() {
									$('#welcome_bar').hide();
									$('#welcome_ur').hide();
									$('#welcome_pw').show();
								});

								$('#btn_pw').click(function() {
									$(this).hide();
									$.ajax({
									  url: "recuperar_pw.php",
									  method: "post",
									  data: {
											mail_pw : $('#mail_pw').val()
										}
									})
									.done(function(data) {
  									if (data == '1') {
											$('#welcome_pw').hide();
											$('#success_pw').show();
										}
										else {
											console.log(data);
											$('#welcome_pw').hide();
											$('#pw_error_msg').append(data);
											$('#pw_error').show();
										}
									})
									.fail(function() {
										$('#welcome_pw').hide();
										$('#pw_error_msg').append('Error interno (AJAX).');
										$('#pw_error').show();
									});
								});

							</script>

				    </div>
					<?php }
				}

				else { ?>
					<div class="col-6">
						<div class='card-important center'>
							<p class='color-azul'><i class='fas fa-wrench fa-fw'></i></p>
							<h2>Sitio temporalmente fuera de línea</h2>
							<p>Debido a mantenimiento y/o actualización, el sistema está temporalmente cerrado.</p>
							<p>Por favor vuelve más tarde.</p>
						</div>
			    </div>
				<?php } ?>

		    <div class="col-2">
		      <!--<div class="card-dark"></div>-->
		    </div>
		  </div>
		</div>

		<div id="dialogo_contacto" title="Contacto">
  		<p><?php echo $mensaje_contacto; ?></p>
		</div>
		<script>/*
			$('#dialogo_contacto').dialog({
				resizable: false,
				modal: true,
				autoOpen: false,
				closeText: '',
				draggable: false
			});
		*/</script>

		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js" integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"></script>
	</body>
</html>
