<?php
require_once '../distance.php';

$objDistance = new distance(40.776, -73.980);
$objDistance->run();
$objDistance->getHtml(0);
echo $objDistance->getHtmlMain();
?>