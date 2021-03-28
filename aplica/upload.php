<?php
$output_dir = "perfiles/";
require_once 'class.upload.php';
require_once 'config.php';

function string_aleatorio($longitud) {
  $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $resultado = '';
  for ($i = 0; $i < $longitud; $i++) {
    $resultado .= $caracteres[rand(0, strlen($caracteres) - 1)];
  }
  return $resultado;
}

 if ($_FILES['file']['error'] > 0) {
	 echo $_FILES['file']['error'];
 }
 else {

   // Eliminar los archivos anteriores que puedan existir:
   $archivos_residuales = glob($output_dir . $_POST['id'] . '_*.png');
   foreach ($archivos_residuales as $archivo_residual) {
     unlink($archivo_residual);
   }

   // Definir sufijo y nombre del arhivo:
   $sufijo = string_aleatorio(8);
   $nombre = strval(intval($_POST['id'])) . '_' . $sufijo;

   // Escribir sufijo en la base de datos:
   $conexion->query("UPDATE aplica_usuarios SET sufijo='$sufijo' WHERE id='{$_POST['id']}'");

	 // Subir imagen:
	 move_uploaded_file($_FILES['file']['tmp_name'], $output_dir . $nombre);

	 // Instanciar manejador:
	 $handle = new upload($output_dir . $nombre);

	 // Convertir a PNG y activar sobreescritura:
	 $handle->image_convert = 'png';
   $handle->file_new_name_ext = 'png';
   $handle->file_overwrite = true;

   // Finalizar:
	 $handle->process($output_dir);

	 // Cambiar los permisos de ambos archivos:
   chmod($output_dir . $nombre, 0666);
	 chmod($output_dir . $nombre . '.png', 0666);

   // Eliminar el archivo original:
   unlink($output_dir . $nombre);

	 echo $nombre;
 }

?>
