<?php

session_start();
$_SESSION['logged'] = false;

require_once 'config.php';

// Query: login_id
if ($_POST['query'] == 'login_id') {
  $usuario = $conexion->query("SELECT * FROM aplica_usuarios WHERE id='{$_POST['id']}'");
  if ($usuario->num_rows == 1) {
    $usuario_fetched = $usuario->fetch_assoc();
    if ($usuario_fetched['sexo'] == 'hombre') {
      $texto = 'Bienvenido, ' . $usuario_fetched['nombre'];
    }
    else {
      $texto = 'Bienvenida, ' . $usuario_fetched['nombre'];
    }
    $resultado = array('response'=> 'registrado',
                       'id' => $usuario_fetched['id'],
                       'texto' => $texto);
  }
  else {
    $resultado = array('response'=> 'nuevo');
  }
}

// Query: login_pw
if ($_POST['query'] == 'login_pw') {
  $usuario = $conexion->query("SELECT * FROM aplica_usuarios WHERE id='{$_POST['id']}'")->fetch_assoc();
  if ($_POST['pw'] == $usuario['pw']) {
    $resultado = array('response' => 'valido');
    if (is_null($usuario['sufijo'])) {
      $resultado['destino'] = '1';
    }
    else if (is_null($usuario['nombre'])) {
      $resultado['destino'] = '2';
    }
    else if (is_null($usuario['entrevista'])) {
      $resultado['destino'] = '3';
    }
    else {
      $resultado['destino'] = 'usuario';
    }
  }
  else {
    $resultado = array('response' => 'invalido');
  }
}

// Query: admin
if ($_POST['query'] == 'admin') {
  if ($_POST['clave'] == $conexion->query("SELECT * FROM aplica_ajustes")->fetch_assoc()['clave_coordinadores']) {
    $resultado = array('response' => 'valido',
                       'url' => 'admin.php');
    $_SESSION['logged'] = true;
  }
  else {
    $resultado = array('response' => 'invalido');
  }
}

// Query: registro_pw
if ($_POST['query'] == 'registro_pw') {
  if (isset($_POST['id'])) {
    if ($conexion->query("INSERT INTO aplica_usuarios (id, pw) VALUES ('{$_POST['id']}', '{$_POST['pw']}')")) {
      $resultado = array('response' => 'valido');
    }
    else {
      $resultado = array('response' => 'invalido');
    }
  }
  else {
    $resultado = array('response' => 'invalido');
  }
}

// Query: registro_datos
if ($_POST['query'] == 'registro_datos') {
  if ($conexion->query("UPDATE aplica_usuarios SET nombre='{$_POST['nombre']}', apellido_p='{$_POST['apellido_p']}', apellido_m='{$_POST['apellido_m']}', correo='{$_POST['correo']}', sexo='{$_POST['sexo']}', carrera='{$_POST['carrera']}', fecha_nac='{$_POST['fecha_nac']}' WHERE id='{$_POST['id']}'")) {
    $resultado = array('response' => 'valido');
  }
  else {
    $resultado = array('response' => $conexion->error);
  }
}

$entrevistas = array(
  '26_10', '26_11', '26_13', '26_16', '26_17',
  '27_10', '27_11', '27_13', '27_16', '27_17',
  '28_10', '28_11', '28_13', '28_16', '28_17',
  '8_10', '8_11', '8_13', '8_16', '8_17',
  '9_10', '9_11', '9_13', '9_16', '9_17',
  '10_10', '10_11', '10_13', '10_16', '10_17',
  '11_10', '11_11', '11_13', '11_16', '11_17',
  '12_10', '12_11', '12_13', '12_16', '12_17',
  '15_10', '15_11', '15_13', '15_16', '15_17',
  '16_10', '16_11', '16_13', '16_16', '16_17',
  '17_10', '17_11', '17_13', '17_16', '17_17',
  '18_10', '18_11', '18_13', '18_16', '18_17',
  '19_10', '19_11', '19_13', '19_16', '19_17'
);

// Query: actualizar_horarios
if ($_POST['query'] == 'actualizar_horarios') {
  $resultado = array();
  $success = true;
  $capacidades_unfetched = $conexion->query("SELECT * FROM aplica_entrevistas");
  $capacidades = array();
  while ($capacidad = $capacidades_unfetched->fetch_assoc()) {
    $capacidades[$capacidad['clave']] = intval($capacidad['capacidad']);
  }
  foreach($entrevistas as $entrevista) {
    $inscritos = $conexion->query("SELECT id FROM aplica_usuarios WHERE entrevista='$entrevista'")->num_rows;
    $resultado[$entrevista] = $capacidades[$entrevista] - $inscritos;
  }
  if ($success) {
    $resultado['response'] = 'valido';
  }
}

// Query: registrar_entrevista
if ($_POST['query'] == 'registrar_entrevista') {
  if ($conexion->query("UPDATE aplica_usuarios SET entrevista='{$_POST['entrevista']}' WHERE id='{$_POST['id']}'")) {
    $resultado = array('response' => 'valido');
    $usuario = $conexion->query("SELECT nombre, sexo FROM aplica_usuarios WHERE id='{$_POST['id']}'")->fetch_assoc();
    if ($usuario['sexo'] == 'hombre') {
      $texto2 = 'Bienvenido, ' . $usuario['nombre'];
    }
    else {
      $texto2 = 'Bienvenida, ' . $usuario['nombre'];
    }
    $resultado['texto'] = $texto2;
  }
  else {
    $resultado = array('response' => $conexion->error);
  }
}

// Query: obtener_info_usuario
if ($_POST['query'] == 'obtener_info_usuario') {
  if ($usuario = $conexion->query("SELECT * FROM aplica_usuarios WHERE id='{$_POST['id']}'")) {
    $usuario = $usuario->fetch_assoc();
    $resultado = array('response' => 'valido');
    $resultado['id'] = $usuario['id'];
    $resultado['nombre'] = $usuario['nombre'];
    $resultado['apellido_p'] = $usuario['apellido_p'];
    $resultado['apellido_m'] = $usuario['apellido_m'];
    $resultado['correo'] = $usuario['correo'];
    $resultado['fecha_nac'] = $usuario['fecha_nac'];
    $resultado['carrera'] = $usuario['carrera'];
    $resultado['sexo'] = $usuario['sexo'];
    $resultado['sufijo'] = $usuario['sufijo'];
    $resultado['entrevista'] = $usuario['entrevista'];
  }
  else {
    $resultado = array('response' => $conexion->error);
  }
}

echo json_encode($resultado);

?>
