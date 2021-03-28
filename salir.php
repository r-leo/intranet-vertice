<?php
	session_start();
	date_default_timezone_set('America/Mexico_City');

  include('config.php');
	include('snippets.php');
	session_unset();
	session_destroy();
	redireccionar($url_salida);
	exit();

?>
