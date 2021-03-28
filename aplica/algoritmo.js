// Variables de configuracion:
var duracion_transicion = 400;

// Función de transición entre pantallas:
function transicion(origen, destino) {
  $(origen).fadeOut(duracion_transicion, function() {
    $(destino).fadeIn(duracion_transicion);
  });
}

// Deshabilitar el envío del formulario con <ENTER>:
$('form').on('keyup keypress', function(e) {
  var keyCode = e.keyCode || e.which;
  if (keyCode === 13) {
    e.preventDefault();
    return false;
  }
});

// Acceso de coordinadores:
$('#admin').click(function() {
  $('#acceso_coordinadores').fadeOut(duracion_transicion);
  $('.mensaje').text('');
  transicion('.inner > div:visible', '#login_admin');
});

$('#login_admin button.continuar').click( function() {
  $('.mensaje').fadeOut(duracion_transicion);
  $('#clave').prop('disabled', true);
  $('#login .continuar').prop('disabled', true);
  $.ajax({
    url: 'algoritmo.php',
    method: 'POST',
    data: {
      query: 'admin',
      clave: $('[name=clave]').val()
    }
  }).done( function(data) {
    json = $.parseJSON(data);
    if (json.response == 'valido') {
      window.location.replace(json.url);
    }
    else if (json.response == 'invalido') {
      $('.mensaje').text('Clave no válida.');
      $('.mensaje').fadeIn(duracion_transicion);
      $('[name=clave]').val('');
      $('[name=clave]').prop('disabled', false);
    }
  }).fail( function() {
    $('.mensaje').text('Hay un error de conexión interno. Por favor intenta de nuevo más tarde.');
    $('.mensaje').fadeIn(duracion_transicion);
  });
});

// Mostrar la pantalla inicial:
$('#login').fadeIn(duracion_transicion);

// login -> login_pw / registro_pw
$('#login button.continuar').click( function() {
  if ($('[name=expediente]').val() == '' || $('[name=expediente]').val() == 0) {
    $('.mensaje').text('Escribe un número de expediente válido:');
    $('.mensaje').fadeIn(duracion_transicion);
    $('[name=expediente]').focus();
  }
  else {
    $('[name=expediente]').prop('disabled', true);
    $('#login .continuar').prop('disabled', true);
    $('.mensaje').text('');
    $('.mensaje').fadeOut(duracion_transicion);
    $.ajax({
      url: 'algoritmo.php',
      method: 'POST',
      data: {
        query: 'login_id',
        id: $('[name=expediente]').val()
      }
    }).done( function(data) {
      $('#acceso_coordinadores').fadeOut(duracion_transicion);
      json = $.parseJSON(data);
      if (json.response == 'registrado') {
        $('.bienvenida').empty();
        $('.bienvenida').append(json.texto);
        obtener_info_usuario($('[name=expediente]').val());
        transicion('#login', '#login_pw');
      }
      else if (json.response == 'nuevo') {
        $('.mensaje').text('');
        transicion('#login', '#registro_pw');
      }
    }).fail( function() {
      $('.mensaje').text('Hay un error de conexión interno. Por favor intenta de nuevo más tarde.');
      $('.mensaje').fadeIn(duracion_transicion);
    });
  }
});

// login_pw -> usuario
$('#login_pw button.continuar').click( function() {
  $('.mensaje').fadeOut(duracion_transicion);
  $('[name=password]').prop('disabled', true);
  $('#login_pw .continuar').prop('disabled', true);
  $.ajax({
    url: 'algoritmo.php',
    method: 'POST',
    data: {
      query: 'login_pw',
      id: $('[name=expediente]').val(),
      pw: $('[name=password]').val()
    }
  }).done( function(data) {
    json = $.parseJSON(data);
    if (json.response == 'valido') {
      if (json.destino == '1') {
        transicion('#login_pw', '#registro_1');
      }
      if (json.destino == '2') {
        transicion('#login_pw', '#registro_2');
      }
      if (json.destino == '3') {
        actualizar_horarios();
        transicion('#login_pw', '#registro_3');
      }
      if (json.destino == 'usuario') {
        transicion('#login_pw', '#usuario');
      }
    }
    else if (json.response == 'invalido') {
    $('.mensaje').text('Tu contraseña es incorrecta. Por favor vuelve a intentarlo:');
    $('.mensaje').fadeIn(duracion_transicion);
    $('[name=password]').prop('disabled', false);
    $('#login_pw .continuar').prop('disabled', false);
    $('[name=password]').val('');
    }
  }).fail( function() {
    $('.mensaje').text('Hay un error de conexión interno. Por favor intenta de nuevo más tarde.');
    $('.mensaje').fadeIn(duracion_transicion);
  });
});

// registro_pw -> registro_1
$('#registro_pw button.continuar').click( function() {
  $('.mensaje').fadeOut(duracion_transicion);
  if ($('[name=password_1]').val() == '') {
    $('.mensaje').text('No puedes dejar la contraseña en blanco. Vuelve a intentarlo:');
    $('.mensaje').fadeIn(duracion_transicion);
    $('[name=password_1]').val('');
    $('[name=password_2]').val('');
  }
  else if ($('[name=password_1]').val() !== $('[name=password_2]').val()) {
    $('.mensaje').text('Las contraseñas no coinciden. Vuelve a intentarlo:');
    $('.mensaje').fadeIn(duracion_transicion);
    $('[name=password_1]').val('');
    $('[name=password_2]').val('');
  }
  else {
    $.ajax({
      url: 'algoritmo.php',
      method: 'POST',
      data: {
        query: 'registro_pw',
        id: $('[name=expediente]').val(),
        pw: $('[name=password_1]').val()
      }
    }).done( function(data) {
      json = $.parseJSON(data);
      if (json.response == 'valido') {
        transicion('#registro_pw', '#registro_1');
      }
      else {
        $('.mensaje').text('Hay un error de conexión interno. Por favor intenta de nuevo más tarde.');
        $('.mensaje').fadeIn(duracion_transicion);
      }
    }).fail( function() {
      $('.mensaje').text('Hay un error de conexión interno. Por favor intenta de nuevo más tarde.');
      $('.mensaje').fadeIn(duracion_transicion);
    });
  }
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
  form_data.append('id', $('[name=expediente]').val());
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
        $('#mensaje_crop').fadeIn(duracion_transicion);
        $('#registro_1 .continuar').fadeIn(duracion_transicion);
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
    $('#mensaje_crop').fadeOut(duracion_transicion);
    $('#registro_1 .continuar').not('label').fadeOut(duracion_transicion);
    $('#mensaje_foto').text('Ninguna imagen seleccionada.');
  }
});

// registro_1 -> registro_2
$('#registro_1 button.continuar').click( function() {
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
    transicion('#registro_1', '#registro_2');
  });
});

// registro_2 -> registro_3
$('#registro_2 button.continuar').click( function() {
  var flag = true;
  if ($('[name=nombre]').val() == '') {
    flag = false;
  }
  if ($('[name=apellido_p]').val() == '') {
    flag = false;
  }
  if ($('[name=apellido_m]').val() == '') {
    flag = false;
  }
  if ($('[name=correo]').val() == '') {
    flag = false;
  }
  if ($('[name=sexo]').val() == null) {
    flag = false;
  }
  if ($('[name=carrera]').val() == null) {
    flag = false;
  }
  if ($('[name=fecha_nac]').val() == '') {
    flag = false;
  }
  if (flag) {
    $.ajax({
      url: 'algoritmo.php',
      method: 'POST',
      data: {
        query: 'registro_datos',
        id: $('[name=expediente]').val(),
        nombre: $('[name=nombre]').val(),
        apellido_p: $('[name=apellido_p]').val(),
        apellido_m: $('[name=apellido_m]').val(),
        correo: $('[name=correo]').val(),
        sexo: $('[name=sexo]').val(),
        carrera: $('[name=carrera]').val(),
        fecha_nac: $('[name=fecha_nac]').val(),
      }
    }).done(function(data) {
      json = $.parseJSON(data);
      if (json.response == 'valido') {
        transicion('#registro_2', '#registro_3');
        actualizar_horarios();
      }
      else {
        console.log(json.response);
      }
    }).fail(function() {
      $('.mensaje').text('Hay un error de conexión interno. Por favor intenta de nuevo más tarde.');
      $('.mensaje').fadeIn(duracion_transicion);
    });
  }
});

$('#registro_3 .continuar').hide();

var entrevista = '';

$('.horarios').on('change', function(e) {
  $('.horarios').not(e.target).val('0');
  if ($(e.target).val() == '0') {
    $('#fecha_entrevista').text('Ningún horario seleccionado');
    $('#registro_3 .continuar').fadeOut(duracion_transicion);
  }
  else {
    switch ($(e.target).attr('id')) {
      case 'horarios_26':
        $('#fecha_entrevista').text('Miércoles 26 de septiembre, ');
        break;
      case 'horarios_27':
        $('#fecha_entrevista').text('Jueves 27 de septiembre, ');
        break;
      case 'horarios_28':
        $('#fecha_entrevista').text('Viernes 28 de septiembre, ');
        break;
      case 'horarios_8':
        $('#fecha_entrevista').text('Lunes 8 de octubre, ');
        break;
      case 'horarios_9':
        $('#fecha_entrevista').text('Martes 9 de octubre, ');
        break;
      case 'horarios_10':
        $('#fecha_entrevista').text('Miércoles 10 de octubre, ');
        break;
      case 'horarios_11':
        $('#fecha_entrevista').text('Jueves 11 de octubre, ');
        break;
      case 'horarios_12':
        $('#fecha_entrevista').text('Viernes 12 de octubre, ');
        break;
      case 'horarios_15':
        $('#fecha_entrevista').text('Lunes 15 de octubre, ');
        break;
      case 'horarios_16':
        $('#fecha_entrevista').text('Martes 16 de octubre, ');
        break;
      case 'horarios_17':
        $('#fecha_entrevista').text('Miércoles 17 de octubre, ');
        break;
      case 'horarios_18':
        $('#fecha_entrevista').text('Jueves 18 de octubre, ');
        break;
      case 'horarios_19':
        $('#fecha_entrevista').text('Viernes 19 de octubre, ');
        break;
    }

    switch ($(e.target).val()) {
      case '10':
        $('#fecha_entrevista').append('10:00 hrs.');
        break;
      case '11':
        $('#fecha_entrevista').append('11:30 hrs.');
        break;
      case '13':
        $('#fecha_entrevista').append('13:00 hrs.');
        break;
      case '16':
        $('#fecha_entrevista').append('16:00 hrs.');
        break;
      case '17':
        $('#fecha_entrevista').append('17:30 hrs.');
        break;
    }
    $('#registro_3 .continuar').fadeIn(duracion_transicion);
    entrevista = $(e.target).attr('id').split('_')[1] + '_' + $(e.target).val();
  }
});

var dias = ['26', '27', '28', '8', '9', '10', '11', '12', '15', '16', '17', '18', '19'];
var horarios = ['10', '11', '13', '16', '17'];

function actualizar_horarios() {
  $.ajax({
    url: 'algoritmo.php',
    method: 'POST',
    data: {
      query: 'actualizar_horarios'
    }
  }).done(function(data) {
    json = $.parseJSON(data);
    if (json.response == 'valido') {
      console.log('ok');
      for (i = 0; i < dias.length; i++) {
        espacio = 0;
        for (j = 0; j < horarios.length; j++) {
          capacidad = parseInt(json[dias[i] + '_' + horarios[j]]);
          console.log(dias[i] + '_' + horarios[j] + ': ' + capacidad);
          if (capacidad > 0) {
            espacio = 1;
            $('#horarios_' + dias[i] + ' option[value=' + horarios[j] + ']').show();
          }
          else {
            console.log('borrar');
            $('#horarios_' + dias[i] + ' option[value=' + horarios[j] + ']').remove();
          }
        }
        if (espacio == 1) {
          $('#' + dias[i].toString()).show();
        }
        else {
          $('#' + dias[i].toString()).remove();
        }
      }
    }
    else {
      console.log(json.response);
    }
  }).fail(function() {
    $('.mensaje').text('Hay un error de conexión interno. Por favor intenta de nuevo más tarde.');
    $('.mensaje').fadeIn(duracion_transicion);
  });
}

// registro_3 -> usuario
$('#registro_3 button.continuar').click( function() {
  $.ajax({
    url: 'algoritmo.php',
    method: 'post',
    data: {
      query: 'registrar_entrevista',
      id: $('[name=expediente]').val(),
      entrevista: entrevista
    }
  }).done(function(data) {
    json = $.parseJSON(data);
    if (json.response == 'valido') {
      $('.bienvenida').empty();
      $('.bienvenida').append(json.texto);
      obtener_info_usuario($('[name=expediente]').val());
      transicion('#registro_3', '#usuario');
    }
    else {
      console.log(json.response);
    }
  }).fail(function(data) {
    $('#registro_3 div').fadeOut(duracion_transicion);
    $('.mensaje').text('Hay un error de conexión interno. Por favor intenta de nuevo más tarde.');
    $('.mensaje').fadeIn(duracion_transicion);
  });
});

function obtener_info_usuario(id) {
  $.ajax({
    url: 'algoritmo.php',
    method: 'post',
    data: {
      query: 'obtener_info_usuario',
      id: id
    }
  }).done(function(data) {
    json = $.parseJSON(data);
    $('.avatar').attr('src', 'perfiles/' + json.id + '_' + json.sufijo + '.png?d=' + $.now());
    $('#campo_nombre').empty();
    $('#campo_nombre').append(json.nombre + ' ' + json.apellido_p + ' ' + json.apellido_m);
    $('#campo_carrera').empty();
    $('#campo_carrera').append(json.carrera);
    $('#campo_mail').empty();
    $('#campo_mail').append(json.correo);
    texto_entrevista = '';

    if (json.entrevista) {
      entrevista_split = json.entrevista.split('_');
      switch (entrevista_split[0]) {
        case '26':
          texto_entrevista = 'Miércoles 26 de septiembre, ';
          break;
        case '27':
          texto_entrevista = 'Jueves 27 de septiembre, ';
          break;
        case '28':
          texto_entrevista = 'Viernes 28 de septiembre, ';
          break;
        case '8':
          texto_entrevista = 'Lunes 8 de octubre, ';
          break;
        case '9':
          texto_entrevista = 'Martes 9 de octubre, ';
          break;
        case '10':
          texto_entrevista = 'Miércoles 10 de octubre, ';
          break;
        case '11':
          texto_entrevista = 'Jueves 11 de octubre, ';
          break;
        case '12':
          texto_entrevista = 'Viernes 12 de octubre, ';
          break;
        case '15':
          texto_entrevista = 'Lunes 15 de octubre, ';
          break;
        case '16':
          texto_entrevista = 'Martes 16 de octubre, ';
          break;
        case '17':
          texto_entrevista = 'Miércoles 17 de octubre, ';
          break;
        case '18':
          texto_entrevista = 'Jueves 18 de octubre, ';
          break;
        case '19':
          texto_entrevista = 'Viernes 19 de octubre, ';
          break;
      }
      switch (entrevista_split[1]) {
        case '10':
          texto_entrevista = texto_entrevista + '10:00 hrs.';
          break;
        case '11':
          texto_entrevista = texto_entrevista + '11:30 hrs.';
          break;
        case '13':
          texto_entrevista = texto_entrevista + '13:00 hrs.';
          break;
        case '16':
          texto_entrevista = texto_entrevista + '16:00 hrs.';
          break;
        case '17':
          texto_entrevista = texto_entrevista + '17:30 hrs.';
          break;
      }
      $('#campo_entrevista').empty();
      $('#campo_entrevista').append(texto_entrevista);
    }
  }).fail(function(data) {
    // codeh
  });
}

// salir:
$('.salir').click(function() {
  window.location.replace('http://ww2.anahuac.mx/verticeintranet/aplica');
});
