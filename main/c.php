<?php

$gS = new Config();
$cfg = $gS->getSettings();

// Controller script for load classes

if (file_exists('classes/'.$_GET['m'].'.class.php')) {
    include 'classes/'.$_GET['m'].'.class.php';
} else {
    print "here";
}
if (is_callable($_GET['m'].'::'.$_GET['id'])) {
    $do = new $_GET['m']($cfg);
    $data = json_encode($do->{$_GET['id']}($_REQUEST));
    header('Content-Type: application/json');
    exit ($data);
} else {
    header($_SERVER['SERVER_PROTOCOL'].' 400 Bad Request');
    exit;
}

?>
