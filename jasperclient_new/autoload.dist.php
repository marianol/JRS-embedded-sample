<?php
define("JASPERCLIENT_ROOT", "jasperclient_new/src/");

spl_autoload_register(function($class) {
$location = JASPERCLIENT_ROOT . $class . '.php';

if(!is_readable($location)) echo $location; return;

require_once $location;
});

?>
