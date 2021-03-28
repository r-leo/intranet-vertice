/*
analytics.js

Módulo de analíticas web
*/

// Variables de configuración:
var phpScriptPath = 'analytics/analytics.php';

// Función para escribir cookies:
function setCookie(cname, cvalue, exdays) {
  var d = new Date();
  d.setTime(d.getTime() + (exdays*24*60*60*1000));
  var expires = "expires="+ d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

// Función para leer cookies:
function getCookie(cname) {
  var name = cname + "=";
  var decodedCookie = decodeURIComponent(document.cookie);
  var ca = decodedCookie.split(';');
  for(var i = 0; i <ca.length; i++) {
      var c = ca[i];
      while (c.charAt(0) == ' ') {
        c = c.substring(1);
      }
      if (c.indexOf(name) == 0) {
        return c.substring(name.length, c.length);
      }
  }
  return "";
}

// Función para verificar cookies:
function checkCookie(cname) {
  var cvalue = getCookie("username");
  if (cvalue != "") {
    return true;
  }
  else {
    return false;
  }
}

// Función para escribir en el registro remoto:
function writeReg(data) {
  $.ajax({
    url: phpScriptPath,
    method: 'POST',
    data: data
  });
}

// Script al cargar la página:
$(document).ready(function() {
  writeReg({query: 'userIn'});
});

// Script al salir de la página:
window.onbeforeunload = function() {
  writeReg({query: 'userOut'});
}
