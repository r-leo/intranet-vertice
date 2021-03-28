<?php
$output_dir = "perfiles/";
require_once 'class.upload.php';

// Definir nombre del arcivo a editar:
$nombre_archivo = $_POST['crop_nombre_archivo'];

// Instanciar manejador:
$handle = new upload($output_dir . $nombre_archivo . '.png');

// Activar sobreescritura:
$handle->file_overwrite = true;

// Obtener dimensiones de la imagen:
$width = $handle->image_src_x;
$height = $handle->image_src_y;

// Recortar la imagen:
$handle->image_crop = array($_POST['crop_punto_1'], $width-$_POST['crop_punto_2'], $height-$_POST['crop_punto_3'], $_POST['crop_punto_0']);

// Finalizar:
$handle->process($output_dir);

// Cambiar los permisos de la nueva imagen::
chmod($output_dir . $nombre_archivo . '.png', 0666);

echo true;

?>
