<?php
require_once('../Catalog.php');

		$objcatalog = new Catalog();
		$objcatalog->searchField("title");
		$tosearch = "Kansas City lightning";
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
?>