<?php
require_once '../BlogFeed.php';

$objDevote = new BlogFeed('http://www.faithgateway.com/topics/devotionals/feed/', 4);
$objDevote->run();
$objDevote->getPages();
echo $objDevote->getCoverhtml();
echo $objDevote->getPage1html();
echo $objDevote->getPage2html();
?>