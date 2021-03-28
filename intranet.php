<?php
	session_start();
	date_default_timezone_set('America/Mexico_City');
	include_once "config.php";
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
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

		<script>
			// Colores para Chart.js:
			colores_db = ['#00A0B0', '#6A4A3C', '#CC333F', '#EB6841', '#EDC951', '#00A0B0', '#6A4A3C', '#CC333F', '#EB6841', '#EDC951'];
			function colores(n) {
				return colores_db.slice(0, n+1);
			}
		</script>

		<title>Intranet Vértice</title>
	</head>

	<body class="position-relative" data-spy="scroll" data-target="#navegacion_rapida" data-offset="100">

		<?php

			// Cargar módulos:
			include 'snippets.php';
			include 'class.plantilla.php';

			// Array de parámetros globales
			$globales = array();
			$globales['periodo']        = $conexion->query("SELECT * FROM periodos WHERE activo=1")->fetch_assoc();
			$globales['periodo_activo'] = $periodo_activo = periodo_formateado(array($periodo['semestre'], $periodo['ano']));

			// Agregar al script la clase usuario:
			include_once 'class.usuario.php';

			// Proteger el estatus de $_SESSION['logged']:
			if (!isset($_SESSION['logged'])) {
				$_SESSION['logged'] = false;
			}

			// Checkpoint 0: el usuario ya está autenticado vía cookie:
			// codeh

			// CHECKPOINT 1: el usuario está haciendo login:
			if (isset($_POST['usuario'])) {
				$usuario = $_POST['usuario'];
				$password = $_POST['password'];
				$result = $conexion->query("SELECT * FROM usuarios WHERE usuario = BINARY '$usuario' AND password = BINARY '$password' LIMIT 1");

				// Login válido:
				if ($result->num_rows > 0) {
					$fila = $result->fetch_assoc();
					$_SESSION['logged'] = true; // Esto autentifica al usuario, no es necesario hacer render aquí.
					$_SESSION['id'] = $fila['id']; // Esta variable de sesión se establece aquí.
					actualizar_sesion($fila);
					// Registrar acceso en la base de datos:
					$tamano_registro = $conexion->query("SELECT COUNT(t_s) AS tamano_registro FROM registro_login")->fetch_assoc()['tamano_registro'];
					$t_s = date('Y-m-d H:i:s');
					if ($tamano_registro >= 50) {
						$min_t_s = $conexion->query("SELECT MIN(t_s) AS min_t_s FROM registro_login")->fetch_assoc()['min_t_s'];
						$conexion->query("DELETE FROM registro_login WHERE t_s='$min_t_s'");
					}
					$conexion->query("INSERT INTO registro_login (id, t_s, estatus) VALUES ('{$_SESSION['id']}', '$t_s', '1')");
				}

				// Login no válido:
				else {
					// Usuario nuevo:
					$result = $conexion->query("SELECT * FROM usuarios WHERE id='{$_POST['usuario']}' AND estatus='nuevo'");
					if ($result->num_rows > 0) {
						$_SESSION['estatus'] = 'nuevo';
						$_SESSION['id'] = ltrim($_POST['usuario'], '0');
					}
					// Login definitivamente no válido:
					else {
						$_SESSION['logged'] = false;
						// Registrar acceso en la base de datos:
						$tamano_registro = $conexion->query("SELECT COUNT(t_s) AS tamano_registro FROM registro_login")->fetch_assoc()['tamano_registro'];
						$t_s = date('Y-m-d H:i:s');
						if ($tamano_registro >= 50) {
							$min_t_s = $conexion->query("SELECT MIN(t_s) AS min_t_s FROM registro_login")->fetch_assoc()['min_t_s'];
							$conexion->query("DELETE FROM registro_login WHERE t_s='$min_t_s'");
						}
						$conexion->query("INSERT INTO registro_login (usuario, t_s, estatus) VALUES ('{$_POST['usuario']}', '$t_s', '0')");
						redireccionar('index.php?code=1');
						exit();
					}
				}
			}

			// CHECKPOINT 2: el usuario no está haciendo login y no está autenticado:
			else {
				if ($_SESSION['logged'] == false && $_SESSION['estatus'] <> 'nuevo') {
					redireccionar('index.php?code=2');
					exit();
				}
			}

			// CHECKPOINT 3: el usuario no está haciendo login y está autenticado:
			if ($_SESSION['logged'] == true) {
				// Actualizar variables de sesión:
				$id = $_SESSION['id'];
				$sql = "SELECT * FROM usuarios WHERE id = '$id' LIMIT 1";
				$result = $conexion->query($sql);
				$fila = $result->fetch_assoc();
				actualizar_sesion($fila);

				// Instanciar el objeto usuario:
				if ($_SESSION['estatus'] <> 'coordinador') {
					$_usuario = new Usuario($id, $conexion);
					$_SESSION['estatus'] = $_usuario->informacion['estatus'];
				}

				// Leer los contenidos de la tabla de ajustes:
				$result_3 = $conexion->query("SELECT * FROM ajustes");
				$ajustes = $result_3->fetch_assoc();

				// Instanciar la clase Plantilla:
				$layout = new Plantilla("layout.principal.html");

				// Establecer los valores de los tags del layout principal:
				$layout->set("nombre", $_SESSION['nombre'] . " " . $_SESSION['apellido_p']);
				$layout->set("generacion", $_SESSION['generacion']);
				$layout->set("fecha_nac", intval(date('d', $_SESSION['fecha_nac'])) . " de " . mes(date('m', $_SESSION['fecha_nac'])) . ", " . date('Y', $_SESSION['fecha_nac']));
				$layout->set("fecha_hoy", diasem(date('w')) . " " . intval(date('d')) . " de " . mes(date('m')));
				$layout->set("ano_actual", date('Y'));

				// Vínculos de coordinadores:
				if ($_SESSION['estatus'] == 'coordinador') {
					$estatus = "Coordinador";
					$layout->set("links_alumnos", "");
					$layout->set("links_coordinadores", "
					<li class='nav-item'><a class='nav-link' href='?d=actividades'>Actividades</a></li>
					<li class='nav-item'><a class='nav-link' href='?d=alumnos'>Alumnos</a></li>
					<li class='nav-item'><a class='nav-link' href='?d=configuracion'>Configuración</a></li>
					<li class='nav-item'><a class='nav-link' href='?d=planes'>Planes curriculares</a></li>
					<li class='nav-item'><a class='nav-link' href='?d=comites'>Comités</a></li>
					<li class='nav-item'><a class='nav-link' href='?d=becarios'>Becarios</a></li>
					<li class='nav-item'><a class='nav-link' href='?d=evaluacion'>Evaluación</a></li>
					");
				}

				// Vínculos de condicionados:
				elseif ($_SESSION['estatus'] == 'activo' || $_SESSION['estatus'] == 'condicionado') {
					$layout->set("links_alumnos", "<li class='nav-item'><a class='nav-link' href='?d=actividades'>Actividades</a></li>");
					$layout->set("links_coordinadores", "<li class='nav-item'><a class='nav-link' href='?d=progreso'>Progreso</a></li>");
				}

				// Vínculos de otros:
				else {
					$layout->set("links_alumnos", "");
					$layout->set("links_coordinadores", "<li class='nav-item'><a class='nav-link' href='?d=progreso'>Progreso</a></li>");
				}

				// Tag de estatus:
				if ($_SESSION['estatus'] == 'activo') {
					$estatus = "Activo";
				}
				elseif ($_SESSION['estatus'] == 'condicionado') {
					$estatus = "Condicionado";
				}
				elseif ($_SESSION['estatus'] == 'baja') {
					$estatus = "Baja";
				}
				elseif ($_SESSION['estatus'] == 'egresado') {
					$estatus = "Egresado";
				}

				// Escribir valores de los tags del layout:
				$layout->set("estatus", $estatus);
				$layout->set("periodo", $periodo_activo);
				$layout->set("avatar", "perfiles/" . $_SESSION['id'] . '_' . $fila['sufijo'] . ".png");

				// Matriz de destinos:
				$destinos = array(
					'actividades'   => 'mod_actividades.php',
					'progreso'      => 'mod_progreso.php',
					'actividad'     => 'mod_actividad.php',
					'configuracion' => 'mod_configuracion.php',
					'alumnos'       => 'mod_alumnos.php',
					'perfil'        => 'mod_perfil.php',
					'planes'        => 'mod_planes.php',
					'plan'          => 'mod_plan.php',
					'ayuda'         => 'mod_ayuda.php',
					'comites'       => 'mod_comites.php',
					'evaluacion'    => 'mod_evaluacion.php',
					'becarios'			=> 'mod_becarios.php'
				);

				// Detectar destino mediante GET:
				if (isset($_GET['d']) && array_key_exists($_GET['d'], $destinos)) {
					$modulo_a_incluir = $destinos[$_GET['d']];
				}
				else {
					$modulo_a_incluir = "module.principal.php";
				}

				// Procesar el módulo elegido y asignar la salida a $contenido:
				ob_start('restablecer_directorio');
				include $modulo_a_incluir;
				$contenido = ob_get_contents();
				ob_end_clean();

				// Establecer el valor del tag de contenido:
				$layout->set("contenido", $contenido);
			}

			// Establecer como layout layout.registro.html si el usuario es nuevo:
			elseif ($_SESSION['estatus'] === 'nuevo') {
				$layout = new Plantilla("layout.registro.html");
				$layout->set("nombre", "Bienvenido");
				$layout->set("fecha_hoy", diasem(date('w')) . " " . intval(date('d')) . " de " . mes(date('m')));
				$layout->set("avatar", "img/avatar.png");
				ob_start('restablecer_directorio');
				include 'mod_registro.php';
				$contenido = ob_get_contents();
				ob_end_clean();
				$layout->set("contenido", $contenido);
			}

			// Finalmente hacer el render de la página:
			$layout->set("mes_actual", mes(date('m')));
			$cumpleanos = $conexion->query("SELECT * FROM usuarios WHERE estatus IN ('alumno', 'cordinador') ORDER BY DAY(fecha_nac)");
			$cumpleanos2 =  array();
			$index = 0;
			while ($cumple = $cumpleanos->fetch_assoc()) {
				if (explode('-', $cumple['fecha_nac'])[1] === date('m')) {
					$cumpleanos2[$index] = $cumple;
					$index++;
				}
			}
			if (count($cumpleanos2) > 0) {
				$tabla_cumpleanos = "<table class='table'><tbody>";
				foreach ($cumpleanos2 as $cumple) {
					$tabla_cumpleanos = $tabla_cumpleanos . "<tr><td>" . explode('-', $cumple['fecha_nac'])[2] . "</td><td>" . $cumple['nombre'] . " " . $cumple['apellido_p'] . "</td></tr>";
				}
				$tabla_cumpleanos = $tabla_cumpleanos . "</tbody></table>";
			}
			else {
				$tabla_cumpleanos = '<p class="center">Parece que nadie cumple años este mes.</p>';
			}
			$layout->set("tabla_cumpleanos", $tabla_cumpleanos);
			echo $layout->render();
		?>

		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js" integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"></script>
	</body>
</html>
