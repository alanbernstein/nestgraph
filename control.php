<?php


require 'inc/config.php';
require 'nest-api-master/nest.class.php';

define('USERNAME', $config['nest_user']);
define('PASSWORD', $config['nest_pass']);

$action = $_GET["a"];
$request_key = $_GET["key"];
echo "$action, $request_key<br>\n";

if($request_key != $config['web_key']) {
    echo "auth";
    die();
}

if($action == "off") {
    // <a href='http://alanbernstein.net/nestgraph/control.php?a=off&key=asdf'>turn off thermostat</a>
    $nest = new Nest();
    $res = $nest->setTargetTemperatureMode(TARGET_TEMP_MODE_OFF);
    echo "disabled nest HVAC: $res<br>\n";
}

if($action == "pause") {
    // <a href='http://alanbernstein.net/nestgraph/control.php?a=pause&key=asdf'>pause notifications for 6 hours</a>";
    echo "paused nestgraph notifications (not yet implemented)<br>\n";
}


?>