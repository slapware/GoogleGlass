<?php
require_once('../AuthorBooks.php');

$objcatalog = new AuthorCatalog();
$objcatalog->searchField("authorname");
$tosearch = "Patti Smith";
$isMulti = FALSE;
$mcounter = 0;
$pos = stripos($tosearch, "List ");
if ($pos !== FALSE AND $pos == 0) {
	$objcatalog->setIsMulti(TRUE);
	$forsearch = substr_replace($tosearch, '', 0, 5);
	$isMulti = TRUE;
}
else {
	$objcatalog->setIsMulti(FALSE);
	$forsearch = $tosearch;
	$isMulti = FALSE;
}
$objcatalog->setSearchTitle($forsearch);
$objcatalog->run();
$objcatalog->getHtmlPage();
echo $objcatalog->getHtml() . "<br>";
echo $objcatalog->getSpeakable();
$count = $objcatalog->countEntries();
$count = $count-1;
if ($count > 1)
{
	for($x=1; $x<=$count; $x++)
	{
	if($x >= 16)
		break;
		$objcatalog->makeSeries($x);
		$objcatalog->getHtmlPage();
		echo $objcatalog->getHtml() . "<br>";
		$speakable = $objcatalog->getSpeakable();
		$content = $objcatalog->utf8_urldecode($speakable);
		$content = preg_replace('/<.*?>/', '', $speakable);
		
		echo $content . "<br>";
	}
}
		
?>