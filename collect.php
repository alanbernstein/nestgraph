<?php

require 'inc/config.php';
require 'nest-api-master/nest.class.php';

define('USERNAME', $config['nest_user']);
define('PASSWORD', $config['nest_pass']);

define('ZIP_CODE', $config['zip_code']);
define('COUNTRY_CODE', $config['country_code']);

date_default_timezone_set($config['local_tz']);

function get_nest_data() {
  $nest = new Nest();
  $info = $nest->getDeviceInfo();
  $weather = $nest->getWeather(ZIP_CODE, COUNTRY_CODE);

  $data = array('heating'      => ($info->current_state->heat == 1 ? 1 : 0),
                'cooling'      => ($info->current_state->ac == 1 ? 1 : 0),
		'timestamp'    => $info->network->last_connection,
		'target_temp'  => sprintf("%.02f", (preg_match("/away/", $info->current_state->mode) ? 
						    $info->target->temperature[0] : $info->target->temperature)),
		'current_temp' => sprintf("%.02f", $info->current_state->temperature),
                'outside_temp' => $weather->outside_temperature,
		'humidity'     => $info->current_state->humidity
		);
  return $data;
}

function c_to_f($c) {
  return ($c * 1.8) + 32;
}

?>