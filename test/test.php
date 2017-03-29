<?php
require_once '../config.php';
require_once '../mirror-client.php';
require_once '../google-api-php-client/src/Google_Client.php';
require_once '../google-api-php-client/src/contrib/Google_MirrorService.php';
require_once '../util.php';

$hod = date('G');
$jd=cal_to_jd(CAL_GREGORIAN,date("m"),date("d"),date("Y"));
$dow = (jddayofweek($jd,1));
$message = "day =  " . $dow . 'Hour = ' . $hod;
echo $message;

$credentials = tour_list_credentials_byhour($hod, $dow);
$error = print_r($credentials, true);
$message = "Found " . $error;
echo $message;

?>