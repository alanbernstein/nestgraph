<?php

require 'inc/config.php';
require 'inc/class.db.php';
require 'collect.php';

$key = $config['web_key'];
$domain = $config['domain'];
$statefile = "state.json";
$notification_email = $config['email'];

// configure notifications
$notification_heat_subject = "nest heating on during the day!";
$notification_cool_subject = "nest AC on during the day!";
$notification_headers = "From: nest-cron-notifier";
$notification_message = "nest-cron-notifier
<a href='https://home.nest.com/'>nest dashboard</a>
<a href='$domain/nestgraph'>nestgraph</a>
<a href='$domain/nestgraph/control.php?a=off&key=$key'>turn off thermostat</a>
<a href='$domain/nestgraph/control.php?a=pause&key=$key'>pause notifications for 6 hours</a>";
$notification_start_hour = 9;
$notification_end_hour = 18;
$notification_frequency_seconds = 6*60*60; // no more than one every 6 hours

// check notification conditions
$current_hour = date("H");
$current_weekday = date("w");  // doesnt work
$worktime_flag = $notification_start_hour < $current_hour && $current_hour < $notification_end_hour;
$workday_flag = 0 < $current_weekday && $current_weekday < 6; // [0, 6] -> [saturday, sunday]

$state_data = file_get_contents($statefile);
$state = json_decode($state_data, true);
$frequency_flag = (time() - $state["last_notification_timestamp"]) > $notification_frequency_seconds;
$notify_flag = ($worktime_flag && $workday_flag && $frequency_flag);

$DEBUG = True;
if($DEBUG) {
  $notification_debug = "notify_flag = " . (int)$notify_flag . " = " . (int)$worktime_flag . " && " . (int)$workday_flag . " && " . (int)$frequency_flag;
  echo $notification_debug . "<br>";
  $notification_message .= "<br>$notification_debug (sent because debug mode)";
  $notify_flag = 1;
}



try {
  $db = new DB($config);
  $data = get_nest_data();

  // handle insert
  if (!empty($data['timestamp'])) {
    if ($stmt = $db->res->prepare("REPLACE INTO data (timestamp, heating, cooling, target, current, outside, humidity, updated) VALUES (?,?,?,?,?,?,?,NOW())")) {
      $stmt->bind_param("siidddi", $data['timestamp'], $data['heating'], $data['cooling'], $data['target_temp'], $data['current_temp'], $data['outside_temp'], $data['humidity']);
      $stmt->execute();
      $stmt->close();
    }
  }
  $db->close();

  // handle notifications
  $heat_on = $data['heating'];
  $cool_on = $data['cooling'];

  if($notify_flag) {
    if($heat_on) {
      mail($notification_email, $notification_heat_subject, $notification_message, $notification_headers);
    }
    if($cool_on) {
      mail($notification_email, $notification_cool_subject, $notification_message, $notification_headers);
    }

    // write current time to state.json
    // http://blog-en.openalfa.com/how-to-read-and-write-json-files-in-php
    $state = array('last_notification_timestamp' => time());
    $fh = fopen($statefile, 'w');
    fwrite($fh, json_encode($state));
    fclose($fh);
  }

} catch (Exception $e) {
  $errors[] = ("DB connection error! <code>" . $e->getMessage() . "</code>.");
}

?>