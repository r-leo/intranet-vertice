<?php

if (chmod('perfiles/' . '55' . '.png', 0666)) {
  echo 'OK';
}
else {
  echo 'ERROR';
}
phpinfo();

?>
