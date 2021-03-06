<?php

//And one more thing to try: forking the heavy lifting into a new process. Yeah, crazy eh?
if (function_exists('pcntl_fork')) {
	$pid = pcntl_fork();
	if ($pid == -1) {
		error_log("could not fork!");
		exit();
	} else if ($pid) {
		// fork worked! but I'm the parent. time to exit.
		exit();
	}
}

require_once '../config.php';
require_once '../mirror-client.php';
require_once '../google-api-php-client/src/Google_Client.php';
require_once '../google-api-php-client/src/contrib/Google_MirrorService.php';
require_once '../util.php';
require_once '../author_distance.php';
if (!class_exists("ProductDetail")) :
require_once('../ProductDetail.php');
endif;

//$request_bytes = file_get_contents('/usr/local/zend/apache2/htdocs/events/test.log');
$request_bytes = file_get_contents('/var/www/html/events/test.log');
if ($request_bytes == FALSE) {
	echo "No Data\n";
}
$request = json_decode($request_bytes, true);

$client = get_google_api_client();
verify_credentials(get_credentials('109964476504154598150'));
$client->setAccessToken(get_credentials('109964476504154598150'));
$mirror_service = new Google_MirrorService($client);

// $location = $mirror_service->locations->get('latest');
// $objDistance = new author_distance($location->getLatitude(), $location->getLongitude());
// $tosearch = 'James Rollins';
// $objDistance->setSearchAuthor($tosearch);
// $objDistance->run();
// $tourcount = $objDistance->countEntries();
// $tlimit = $tourcount - 1;

switch ($request['collection']) {
  case 'timeline':
    // Verify that it's a share
    foreach ($request['userActions'] as $i => $user_action) {
      if ($user_action['type'] == 'SHARE') {

        $timeline_item_id = $request['itemId'];

        $timeline_item = $mirror_service->timeline->get($timeline_item_id);

        // Patch the item. Notice that since we retrieved the entire item above
        // in order to access the caption, we could have just changed the text
        // in place and used the update method, but we wanted to illustrate the
        // patch method here.
        $patch = new Google_TimelineItem();
        /*******************************************************************************
        Set Correct text here // NOTE: Modified Stephen La Pierre 9/16/13. At 3:53 PM
        *******************************************************************************/
        $patch->setText("Harper Collins got your photo! " .
            $timeline_item->getText());
        $mirror_service->timeline->patch($timeline_item_id, $patch);
        break;
      } // if ($user_action['type'] == 'SHARE')
      /*******************************************************************************
      Get reply test is here // NOTE: Modified Stephen La Pierre 9/17/13. At 10:03 AM
      *******************************************************************************/
      if ($user_action['type'] == 'REPLY') {
      	$timeline_item_id = "ae72fb26-e955-4ff0-8611-ae9e718b6dcc";
//        $timeline_item = "ae72fb26-e955-4ff0-8611-ae9e718b6dcc";
//        $inReplyTo = $timeline_item->getInReplyTo();
//        $reply_timeline_item = $mirror_service->timeline->get($inReplyTo);
        /*******************************************************************************
        Figure out what kind of voice search to perform here 10/8/13. It is now 4:38 PM
        *******************************************************************************/
        $fromCard = "tours by author";
        if(strpos($fromCard, "book by name") !== FALSE)
        {
        /*******************************************************************************
        Get search text here from user.
        *******************************************************************************/
		$objcatalog = new Catalog();
		$objcatalog->searchField("title");
		$tosearch = $timeline_item->getText();
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
		
    $new_timeline_item = new Google_TimelineItem();
	$new_timeline_item->setHtml($objcatalog->getHtml());
    $speakable = $objcatalog->getSpeakable();
	if ($isMulti)
	{
		$bunId = date("Y-m-d H:i:s");
		$new_timeline_item->setBundleId($bunId);
		$new_timeline_item->setIsBundleCover(TRUE);
	}
	else 
	{
		$content = $objcatalog->utf8_urldecode($speakable);
		$content = preg_replace('/<.*?>/', '', $content);
		$new_timeline_item->setSpeakableText($content);
	}
    $menu_items = array();
    // A couple of built in menu items
    $menu_item = new Google_MenuItem();
    $menu_item->setAction("READ_ALOUD");
    array_push($menu_items, $menu_item);
    $pin_item = new Google_MenuItem();
    $pin_item->setAction("TOGGLE_PINNED");
    array_push($menu_items, $pin_item);
    $del_item = new Google_MenuItem();
    $del_item->setAction("DELETE");
    array_push($menu_items, $del_item);
    /*  Cover does not use speakable text. */
    $new_timeline_item->setMenuItems($menu_items);
    $notification = new Google_NotificationConfig();
    $notification->setLevel("DEFAULT");
    $new_timeline_item->setNotification($notification);
    $mirror_service->timeline->patch($timeline_item_id, $new_timeline_item);
			if ($isMulti)
			{
				$count = $objcatalog->countEntries();
				$count = $count-1;
				for($x=1; $x<=$count; $x++)
				{
					$objcatalog->makeSeries($x);
					$objcatalog->getHtmlPage();
					if (strlen($objcatalog->getIsbn()) > 10 AND strlen($objcatalog->getTitle()) > 3)
					{
						$new_timeline_item = new Google_TimelineItem();
						if ($x == $count) {
							/*  This is the first visable card so full text available. */
    						$speakable = $objcatalog->getSpeakable();
							$content = $objcatalog->utf8_urldecode($speakable);
							$content = preg_replace('/<.*?>/', '', $content);
							$new_timeline_item->setSpeakableText($content);
						}
						else {
							/* All other cards have title and format not long text. */
							$new_timeline_item->setSpeakableText($objcatalog->getTitle() . ". " . $objcatalog->getFormat());
						}
						$new_timeline_item->setHtml($objcatalog->getHtml());
						$new_timeline_item->setBundleId($bunId);
						$new_timeline_item->setIsBundleCover(FALSE);
						$menu_items = array();
						// A couple of built in menu items
						$menu_item = new Google_MenuItem();
						$menu_item->setAction("READ_ALOUD");
						array_push($menu_items, $menu_item);
						$pin_item = new Google_MenuItem();
						$pin_item->setAction("TOGGLE_PINNED");
						array_push($menu_items, $pin_item);
						$del_item = new Google_MenuItem();
						$del_item->setAction("DELETE");
						array_push($menu_items, $del_item);
						$new_timeline_item->setMenuItems($menu_items);
						$new_timeline_item->setNotification($notification);
						insert_timeline_item($mirror_service, $new_timeline_item, null, null);
					}
				}
			} // if ($isMulti)
        } // if(strpos("book by name") !== FALSE)
        /*******************************************************************************
        The next voice search starts here Modified Stephen La Pierre 10/8/13. At 4:39 PM
        *******************************************************************************/
        if(strpos($fromCard, "books by author") !== FALSE)
        {
        	/*******************************************************************************
        	 Get search text here from user.
        	*******************************************************************************/
        	$objcatalog = new AuthorCatalog();
        	$tosearch = $timeline_item->getText();
        	$mcounter = 0;
			$objcatalog->searchField("authorname");
        	$objcatalog->setSearchTitle($tosearch);
        	$objcatalog->run();
        	$count = $objcatalog->countEntries();
        	echo "Found " . $count . " Entries.";
        	$count = $count-1;
            /*******************************************************************************
        	limit output for popular authors here SLAP
        	*******************************************************************************/
        	if($count > 8) {
        	    $count = 8;
        	} 
        	$new_timeline_item = new Google_TimelineItem();
        	$menu_items = array();
        	// A couple of built in menu items
        	$menu_item = new Google_MenuItem();
        	$menu_item->setAction("READ_ALOUD");
        	array_push($menu_items, $menu_item);
        	$pin_item = new Google_MenuItem();
        	$pin_item->setAction("TOGGLE_PINNED");
        	array_push($menu_items, $pin_item);
        	$del_item = new Google_MenuItem();
        	$del_item->setAction("DELETE");
        	array_push($menu_items, $del_item);
        	/*  Cover does not use speakable text. */
        	$new_timeline_item->setMenuItems($menu_items);
        	$notification = new Google_NotificationConfig();
        	$notification->setLevel("DEFAULT");
        	$new_timeline_item->setNotification($notification);
			$objcatalog->getHtmlPage();
			$new_timeline_item->setHtml($objcatalog->getHtml());
			$bunId = date("Y-m-d H:i:s");
			$new_timeline_item->setBundleId($bunId);
			$new_timeline_item->setIsBundleCover(TRUE);
			$mirror_service->timeline->patch($timeline_item_id, $new_timeline_item);
        	if ($count > 1)
        	{
        		for($x=1; $x<=$count; $x++)
				{
					if($x >= 16)
						break;
					$objcatalog->makeSeries($x);
					$objcatalog->getHtmlPage();
					if (strlen($objcatalog->getIsbn()) > 10 AND strlen($objcatalog->getTitle()) > 3)
					{
						$new_timeline_item = new Google_TimelineItem();
    					$speakable = $objcatalog->getSpeakable();
						$content = $objcatalog->utf8_urldecode($speakable);
						$content = preg_replace('/<.*?>/', '', $content);
						$new_timeline_item->setSpeakableText($content);
						$new_timeline_item->setHtml($objcatalog->getHtml());
						$new_timeline_item->setBundleId($bunId);
						$new_timeline_item->setIsBundleCover(FALSE);
						$menu_items = array();
						// A couple of built in menu items
						$menu_item = new Google_MenuItem();
						$menu_item->setAction("READ_ALOUD");
						array_push($menu_items, $menu_item);
						$pin_item = new Google_MenuItem();
						$pin_item->setAction("TOGGLE_PINNED");
						array_push($menu_items, $pin_item);
						$del_item = new Google_MenuItem();
						$del_item->setAction("DELETE");
						array_push($menu_items, $del_item);
						$new_timeline_item->setMenuItems($menu_items);
						$new_timeline_item->setNotification($notification);
						insert_timeline_item($mirror_service, $new_timeline_item, null, null);
					}
        	     } // for($x=1; $x<=$count; $x++)
      		 } // if ($count > 1)
     	 } // if(strpos($fromCard, "books by author") !== FALSE)
        /*******************************************************************************
         SEARCH TOURS BY AUTHOR ALL USERS
        *******************************************************************************/
        if(strpos("tours by author") !== FALSE)
        {
        	$location = $mirror_service->locations->get('latest');
        	$objDistance = new author_distance($location->getLatitude(), $location->getLongitude());
        	
        	$tosearch = 'James Rollins';
        	 
//			$tosearch = $timeline_item->getText();
			$objDistance->setSearchAuthor($tosearch);
        	$objDistance->run();
        	$tourcount = $objDistance->countEntries();
        	echo "Found " . $tourcount . " Entries.\n";
        	if($tourcount > 12) {
        	    $tourcount = 12;
        	}
        	$tlimit = $tourcount - 1;
        	/*******************************************************************************
        	Reverse fill so soonest events is first in timeline
        	*******************************************************************************/
        	$looper = 0;
//        	for ($looper=$tlimit;$looper>=0;$looper--) {
    		while ((int)$looper <= (int)$tlimit) {
        	     if((int)$looper >= (int)$tourcount)
        	     {
					break;
        	     }
				/*******************************************************************************
				 Check we will have fresh timeline objects
				*******************************************************************************/
				if (isset($new_timeline_item)) {
					unset($new_timeline_item);
				}
        		if (isset($new_timeline_item2)) {
					unset($new_timeline_item2);
				}
        	    if (isset($new_timeline_item3)) {
					unset($new_timeline_item3);
				}
				echo "$new_timeline_item = new Google_TimelineItem()  count= " . $looper . "\n";
        		$objDistance->getHtml((int)$looper);
// 	        	echo "$new_timeline_item->setHtml($objDistance->getHtmlMain());\n";
       			/*******************************************************************************
        		 Check if not found page only. CHANGE OUTPUT DISPLAY
        		*******************************************************************************/
        		if ($objDistance->getInError() === FALSE) {
	         		/*******************************************************************************
	        		 Create tha main bundle card for element in DB
	        		*******************************************************************************/
	        		echo '$bunId = date("Y-m-d H:i:s");';
	        		echo "\n";
// 	        		$new_timeline_item->setBundleId($bunId);
// 	       			$new_timeline_item->setIsBundleCover(TRUE);
	       			/*******************************************************************************
	       			 Add another card for description and type of event.
	       			*******************************************************************************/
// 	       			$new_timeline_item2 = new Google_TimelineItem();
// 	       			$notification = new Google_NotificationConfig();
// 	       			$notification->setLevel("DEFAULT");
//	       			$new_timeline_item->setNotification($notification);
	       			// $mirror_service to $user_specific_mirror_service
	       			if((int)$looper == 0)
	       			{
	       				echo "Patch\n";
	       			}
	       			else {
	       				echo "Insert\n";
	       			}
	       			/*******************************************************************************
	       			 Add menu options here for desired actions, read, navigate and pin / un-pin card.
	       			*************** from event object***********************************************/
	       			$menu_items = array();
	       			// A couple of built in menu items
	       			$menu_item = new Google_MenuItem();
	       			$menu_item->setAction("READ_ALOUD");
	       			array_push($menu_items, $menu_item);
	       			
	       			$menu_item = new Google_MenuItem();
	       			$menu_item->setAction("TOGGLE_PINNED");
	       			array_push($menu_items, $menu_item);
	       			
	       			$menu_item = new Google_MenuItem();
	       			$menu_item->setAction("NAVIGATE");
	       			array_push($menu_items, $menu_item);
	       			$del_item = new Google_MenuItem();
	       			$del_item->setAction("DELETE");
	       			array_push($menu_items, $del_item);
	       			/*******************************************************************************
	       			 Set the READ ALOUD text here
	       			*******************************************************************************/
	       			echo "$new_timeline_item2 = new Google_TimelineItem();\n";
	       			
					if (isset($objDetail)) {
						unset($objDetail);
					}
	       			$objDetail = new ProductDetail($objDistance->getIsbn(), "");
	       			$postdata = "";
	       			if (strlen($objDetail->seo) > 0)  {
	       				$postdata = $postdata . $objDetail->seo . ". ";
	       				$good2go = TRUE;
	       			}
	       			/*******************************************************************************
	       			 The description and quote content information.
	       			*******************************************************************************/
	       			foreach ($objDetail->description as $d) {
	       				$postdata = $postdata . $d . ". ";
	       				$good2go = TRUE;
	       			}
	       			foreach ($objDetail->quote as $q) {
	       				$postdata = $postdata . $q . ". ";
	       				$good2go = TRUE;
	       			}
	       			/*******************************************************************************
	       			 To clean the text to use as glass readable
	       			*******************************************************************************/
	       			if((strlen($postdata) > 128) && ($good2go == TRUE)) {
	       				$postdata = $objDetail->utf8_urldecode($postdata);
	       				$content = preg_replace('/<.*?>/', '', $postdata);
	       				echo "setSpeakableText\n";
	       			}
	       			else
	       			{
	       				$rtext = $objDistance->getAuthor() . ". " . $objDistance->getTitle() . ". " . $objDistance->getVenue() . ". " . $objDistance->getLocation() . ". ";
//	       				$new_timeline_item2->setSpeakableText($rtext);
	       			}
	       			/*******************************************************************************
	       			 Test new audio here // NOTE: Modified Stephen La Pierre 10/3/13. At 3:52 PM
	       			*******************************************************************************/
// 	       			$new_timeline_item2->setBundleId($bunId);
// 	       			$new_timeline_item2->setId($new_timeline_item->getId());
// 	       			$new_timeline_item2->setHtml($objDistance->getHtmlPage());
// 	       			$new_timeline_item2->setIsBundleCover(FALSE);
	       			/*******************************************************************************
	       			 Only set location if it's an event.
	       			*******************************************************************************/
	       			if($objDistance->getIsMap()) {
	       				echo "eventLoc\n";
// 	       				$eventLoc->setLatitude($objDistance->getDest_latitude());
// 	       				$eventLoc->setLongitude($objDistance->getDest_longitude());
// 	       				$new_timeline_item2->setLocation($eventLoc);
	       			}
	       			
// 	       			$notification2 = new Google_NotificationConfig();
// 	       			$notification2->setLevel("DEFAULT");
// 	       			$new_timeline_item2->setNotification($notification2);
// 	       			$new_timeline_item2->setMenuItems($menu_items);
	       			// $mirror_service to $user_specific_mirror_service
	       			echo "insert_timeline_item\n";
	       			/*******************************************************************************
	       			 Add map to bundle if it's an event only
	       			*******************************************************************************/
	       			if($objDistance->getIsMap()) {
	       				echo "isMap\n";
	       				$new_timeline_item3 = new Google_TimelineItem();
	       				$rtext = $objDistance->getDir_voice();
	       				$new_timeline_item3->setSpeakableText($rtext);
	       				$eventLoc = new Google_Location();
	       				$eventLoc->setLatitude($objDistance->getDest_latitude());
	       				$eventLoc->setLongitude($objDistance->getDest_longitude());
// 	       				$new_timeline_item3->setLocation($eventLoc);
// 	       				$new_timeline_item3->setBundleId($bunId);
// //	       				$new_timeline_item3->setId($new_timeline_item->getId());
// 	       				$new_timeline_item3->setHtml($objDistance->getMapHtml());
// 	       				$new_timeline_item3->setIsBundleCover(FALSE);
// 	       				$notification3 = new Google_NotificationConfig();
// 	       				$notification3->setLevel("DEFAULT");
// 	       				$new_timeline_item3->setNotification($notification3);
// 	       				$new_timeline_item3->setMenuItems($menu_items);
	       				// $mirror_service to $user_specific_mirror_service
	       				echo "insert_timeline_item line 419;\n";
	       			} // if($objDistance->getIsMap())	       				     
         	} // if ($objDistance->getInError() == FALSE)
         		else {
         			$new_timeline_item->setIsBundleCover(FALSE);
         			$menu_items = array();
         			// A couple of built in menu items
         			$menu_item = new Google_MenuItem();
         			$menu_item->setAction("READ_ALOUD");
         			array_push($menu_items, $menu_item);
         			$del_item = new Google_MenuItem();
         			$del_item->setAction("DELETE");
         			array_push($menu_items, $del_item);
         			$new_timeline_item->setMenuItems($menu_items);
         			$new_timeline_item->setSpeakableText($objDistance->getSpeakable());
         			$notification = new Google_NotificationConfig();
         			$notification->setLevel("DEFAULT");
         			$new_timeline_item->setNotification($notification);
         			echo "$mirror_service->timeline->patch($timeline_item_id, $new_timeline_item);\n";
         		}
         		echo 'End loop ' . $looper . ' ***************';
         		echo "\n";
         		(int)$looper = (int)$looper + 1;
        	} // for ($looper = 0; $looper < $count; $looper++) ** WHILE CHANGE $looper
//       		break;
        } // if(strpos("tours by author") !== FALSE)
        	     
       	break;
    	} // if ($user_action['type'] == 'REPLY')
    } // foreach ($request['userActions'] as $i => $user_action)

    break;
  case 'locations':
    $location_id = $request['itemId'];
    $location = $mirror_service->locations->get($location_id);
    // Insert a new timeline card, with a copy of that photo attached
    $loc_timeline_item = new Google_TimelineItem();
	/*******************************************************************************
	Set Correct text here // NOTE: Modified Stephen La Pierre 9/16/13. At 3:53 PM
	*******************************************************************************/
    $loc_timeline_item->setText("Harper Collins says you are now at " .
    $location->getLatitude() . " by " . $location->getLongitude());

    insert_timeline_item($mirror_service, $loc_timeline_item, null, null);
    break;
  default:
    error_log("I don't know how to process this notification: $request");
   } // switch ($request['collection'])
    

?>