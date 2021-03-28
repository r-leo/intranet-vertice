<?php
session_start();
?>

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

<?php
if (isset($_SESSION['logged']) && $_SESSION['logged'] == true) {

	if (isset($_GET['usuario'])) {
		require_once 'config.php';
		$id = $_GET['usuario'];
		$usuario = $conexion->query("SELECT * FROM aplica_usuarios WHERE id='$id'")->fetch_assoc();
		?>

		<div class='main'>
			<p><a href='logout.php'>Salir del sistema</a></p>
			<p><a href='admin.php'>Volver a la página principal</a></p>
			<hr>
			<p class='bold'><?php echo $usuario['nombre'] . ' ' . $usuario['apellido_p'] . ' ' . $usuario['apellido_m']; ?></p>
			<ul>
				<li>Carrera: <?php echo $usuario['carrera']; ?></li>
				<li>Fecha de nacimiento (aaaa-mm-dd): <?php echo $usuario['fecha_nac']; ?></li>
				<li>Correo electrónico: <?php echo $usuario['correo']; ?></li>
			</ul>
			<hr>
			<div class='center'>
				<div id='contenedor_imagen'>
					<img style='width:300px;' src='perfiles/<?php echo $usuario['id'] . '_' . $usuario['sufijo']; ?>.png'>
				</div>
				<div id='contenedor_uploader' class='center' style='display:none;'>
					<form enctype="multipart/form-data" method="post" class='center'>
						<div id='texto_foto' style='margin-left:auto;margin-right:auto;'>
							<div><p id='mensaje_foto'>No has seleccionado ninguna imagen.</p></div>
						</div>
						<div id='campo_foto' style='margin-left:auto;margin-right:auto;'></div>
						<br>
						<input type="file" name='archivo_imagen' id='archivo_imagen' accept='.png,.jpg,.jpeg,.bmp,.gif'>
						<br><br>
						<label class='boton_inline' for='archivo_imagen'>Elegir archivo...</label>
						<p id='boton_crop' class='boton_inline'>Recortar y guardar</p>
					</form>
				</div>
				<br>
				<p id='cambiar_imagen'class='boton_inline'>Cambiar imagen de perfil</p>
			</div>
	  </div>

		<script>
			$('#cambiar_imagen').click(function() {
				$('#contenedor_imagen').css('display', 'none');
				$('#contenedor_uploader').css('display', 'block');
				$('#cambiar_imagen').css('display', 'none');
			});

			// Script para subir la fotografía:
			$('#campo_foto').croppie({
			  viewport: {
			    height: 250,
			    width: 250
			  }
			});

			var nombre_imagen = '';

			$('[name=archivo_imagen]').on('change', function(event) {
			  $('#campo_foto').hide();
			  $('#texto_foto').show();
			  var file_data = $('[name=archivo_imagen]').prop('files')[0];
			  var form_data = new FormData();
			  form_data.append('file', file_data);
			  form_data.append('id', '<?php echo $id; ?>');
			  if (typeof file_data != 'undefined') {
			    var file_size = file_data['size']/1000000;
			    if (file_size > 5) {
			      $('#mensaje_foto').text('El tamaño del archivo supera el límite de 5 MB. Por favor elige otro.');
			    }
			    else {
			      $('#mensaje_foto').text('Subiendo imagen...');
			      $.ajax({
			        url: 'upload.php',
			        dataType: 'text',
			        cache: false,
			        contentType: false,
			        processData: false,
			        data: form_data,
			        type: 'post'
			      }).done( function(data) {
			        nombre_imagen = data;
			        $('#texto_foto').hide();
			        $('#mensaje_foto').text('No has seleccionado ninguna imagen.');
			        $('#campo_foto').show();
			        $('#campo_foto').croppie('bind', {
			          url: 'perfiles/' + data + '.png?d=' + Date.now(),
			        });
			      }).fail( function() {
			        $('#mensaje_foto').text('Hubo un error en el servidor. Trata de nuevo, y si sigues teniendo poblemas contacta a un coordinador.');
			      });
			    }
			  }
			  else {
			    $('#mensaje_foto').text('Ninguna imagen seleccionada.');
			  }
			});

			// Script para hacer el cropping:
			$('#boton_crop').click( function() {
			  // Hacer crop de la imagen:
			  $.ajax({
			    url: 'crop.php',
			    dataType: 'json',
			    type: 'post',
			    data: {
			      crop_nombre_archivo: nombre_imagen,
			      crop_punto_0: $('#campo_foto').croppie('get')['points'][0],
			      crop_punto_1: $('#campo_foto').croppie('get')['points'][1],
			      crop_punto_2: $('#campo_foto').croppie('get')['points'][2],
			      crop_punto_3: $('#campo_foto').croppie('get')['points'][3]
			    }
			  }).done( function() {
			    location.reload();
			  });
			});

		</script>

	<?php }
	else {
		echo "<p>Error en la solicitud GET.</p>";
	}
}
else { ?>
  <p>Acceso prohibido</p>
<?php } ?>

</body>
</html>
