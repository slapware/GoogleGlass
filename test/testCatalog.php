<?php
//require_once 'Catalog.php';
require_once '../AuthorBooks.php';

//$tosearch = "List american sniper";
$tosearch = "Russell Banks";

//$objcatalog = new Catalog();
 $objcatalog = new AuthorCatalog();
// $pos = stripos($tosearch, "List ");
// if ($pos !== FALSE AND $pos == 0) {
// 	$objcatalog->setIsMulti(TRUE);
// 	$forsearch = substr_replace($tosearch, '', 0, 5);
// }
// else {
// 	$objcatalog->setIsMulti(FALSE);
// 	$forsearch = $tosearch;
// }

$objcatalog->setSearchTitle($tosearch);
$objcatalog->run();
$count = $objcatalog->countEntries();
	$count = $count-1;
  /*******************************************************************************
  limit output for popular authors here SLAP
  *******************************************************************************/
  if($count > 8) {
     $count = 8;
   } 
$objcatalog->makeSeries(0);
$objcatalog->getHtmlPage();
echo $objcatalog->getHtml();

	for($x=1; $x<=$count; $x++)
	{
	$objcatalog->makeSeries($x);
	$objcatalog->getHtmlPage();
	echo $objcatalog->getIsbn() . '<BR>';
	echo $objcatalog->getTitle() . '<BR>';
	if (strlen($objcatalog->getIsbn()) > 10 AND strlen($objcatalog->getTitle()) > 3)
	{
		$tester = 0;
    	$speakable = $objcatalog->getSpeakable();
		$content = $objcatalog->utf8_urldecode($speakable);
		$content = preg_replace('/<.*?>/', '', $content);
		echo $x . '<BR>' . $content . '<BR>';
	}
	}

?>