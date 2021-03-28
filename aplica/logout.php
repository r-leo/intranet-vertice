<?php
session_start();
$_SESSION['logged'] = false;
header("Location: http://ww2.anahuac.mx/verticeintranet/aplica");
die();
?>
