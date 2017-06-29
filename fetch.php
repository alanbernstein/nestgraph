<?php

// SELECT from database for graph

require 'inc/config.php';
require 'inc/class.db.php';

define('DEFAULT_HRS', 72);

$hrs = DEFAULT_HRS; 
if ($_GET["hrs"]) {
  $hrs = $_GET["hrs"];
}

// NOTE: in future installations, will need to change column order to match dbsetup (cooling after heating). i couldn't figure out how to change column order in this, and don't care enough to do it by creating a new table.

try {
  $db = new DB($config);
  if ($stmt = $db->res->prepare("SELECT * from data where timestamp>=DATE_SUB(NOW(), INTERVAL ? HOUR) order by timestamp")) {
    $stmt->bind_param("i", $hrs);
    $stmt->execute();
    $stmt->bind_result($timestamp, $heating, $target, $current, $humidity, $updated, $cooling, $outside);
    header("Content-type: text/tab-separated-values");
    //header("Content-type: text");
    print "timestamp\theating\ttarget\tcurrent\thumidity\tupdated\tcooling\toutside\n";
    while ($stmt->fetch()) {
      print implode("\t", array($timestamp, $heating, $target, $current, $humidity, $updated, $cooling, $outside)) . "\n";
    }
    $stmt->close();
  }
  $db->close();
} catch (Exception $e) {
  $errors[] = ("DB connection error! <code>" . $e->getMessage() . "</code>.");
}

?>