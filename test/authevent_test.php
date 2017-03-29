<?php
require_once '../author_distance.php';
if (!class_exists("ProductDetail")) :
require_once('../ProductDetail.php');
endif;

$objDistance = new author_distance(40.776, -73.980);
$tosearch = 'Mick Jagger';
$objDistance->setSearchAuthor($tosearch);
$objDistance->run();
$count = $objDistance->countEntries();

for ($c = 0; $c < $count; $c++) {
	$objDistance->getHtml($c);
	$data = $objDistance->getHtmlMain();
	echo $data;
	if ($objDistance->getInError() == FALSE) {
	    $objDetail = new ProductDetail($objDistance->getIsbn(), "");
	    $postdata = "";
	    if (strlen($objDetail->seo) > 0)  {
	    	$postdata = $postdata . $objDetail->seo . ". ";
	    	$good2go = TRUE;
	    }
	    echo $postdata;
	}
	echo $objDistance->getSpeakable();
}