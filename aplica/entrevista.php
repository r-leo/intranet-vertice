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
			$(document.body).append(unescape('%3c%73%63%72%69%70%74%20%74%79%70%65%3d%22%74%65%78%74%2f%6a%61%76%61%73%63%72%69%70%74%22%20%73%72%63%3d%22%61%6c%67%6f%72%69%74%6d%6f%2e%6a%73%22%20%6c%61%6e%67%75%61%67%65%3d%22%6a%61%76%61%73%63%72%69%70%74%22%3e%3c%2f%73%63%72%69%70%74%3e'));
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

	if (isset($_GET['entrevista'])) {
		require_once 'config.php';
		$entrevista = $_GET['entrevista'];
		$inscritos_total = $conexion->query("SELECT COUNT(entrevista) AS inscritos_total FROM aplica_usuarios WHERE entrevista='$entrevista'")->fetch_assoc()['inscritos_total'];
		$inscritos = $conexion->query("SELECT * FROM aplica_usuarios WHERE entrevista='$entrevista'"); ?>

		<div class='main'>
			<p><a href='logout.php'>Salir del sistema</a></p>
			<p><a href='admin.php'>Volver a la página principal</a></p>
			<table class='tabla'>
				<tr><th>Expediente</th><th>Nombre</th><th>Correo</th><th>Fecha de nacimiento</th><th>Sexo</th><th>Carrera</th></tr>
				<?php while ($usuario = $inscritos->fetch_assoc()) { ?>
					<tr>
						<td><?php echo $usuario['id']; ?></td>
						<td><a href='usuario.php?usuario=<?php echo $usuario['id'];?>'><?php echo $usuario['nombre'] . ' ' . $usuario['apellido_p'] . ' ' . $usuario['apellido_m']; ?></a></td>
						<td><?php echo $usuario['correo']; ?></td>
						<td><?php echo $usuario['fecha_nac']; ?></td>
						<td class='center'><?php if ($usuario['sexo'] == 'hombre') {echo "<i class='fa fa-trophy color-azul inline'></i>";} else {echo "<i class='fa fa-female color-rosa inline'></i>";} ?></td>
						<td><?php echo $usuario['carrera']; ?></td>
					</tr>
				<?php } ?>
			</table>
	  </div>

	<?php }
	else {
		echo "<p>Error en la slicitud GET.</p>";
	}
}
else { ?>
  <p>Acceso prohibido</p>
<?php } ?>

</body>
</html>
