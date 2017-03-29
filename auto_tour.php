<?php
/*  Generate 5 tours a day for all users
 *  Called from cron
 *  Stephen La Pierre
 */
require_once 'config.php';
require_once 'mirror-client.php';
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_MirrorService.php';
require_once 'util.php';
require_once 'distance.php';

if (!class_exists("ProductDetail")) :
require_once('ProductDetail.php');
endif;


if ($_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
	// restrict
	die("I don't talk to strangers");
}

$client = get_google_api_client();
  verify_credentials(get_credentials('110350450990179447197'));
  $client->setAccessToken(get_credentials('110350450990179447197'));
  
  // A glass service for interacting with the Mirror API
for ($x = 4; $x >= 0; $x--)
{
$mirror_service = new Google_MirrorService($client);
	$hod = date('G');
	$jd=cal_to_jd(CAL_GREGORIAN,date("m"),date("d"),date("Y"));
	$dow = (jddayofweek($jd,1));
	
	$credentials = tour_list_credentials_byhour($hod, $dow);
	if (count($credentials) > 10) {
		$message = "Found " . count($credentials) . " users. Aborting to save your quota.";
	} 
	else 
	{
// 		$message = "Found " . count($credentials) . " users for " . $hod . "hour, Sending to all now..";
// 		echo $message;
		foreach ($credentials as $credential) {
		$user_specific_client = get_google_api_client();
		$user_specific_client->setAccessToken($credential['credentials']);
		$user_specific_mirror_service = new Google_MirrorService($user_specific_client);
    			 
  	// top per user loop
    $new_timeline_item = new Google_TimelineItem();
    $location = $user_specific_mirror_service->locations->get('latest');
    
	$objDistance = new distance($location->getLatitude(), $location->getLongitude());
	$objDistance->run();
	$objDistance->getHtml(intval($x));
	/*******************************************************************************
	Create tha main bundle card for element in DB
	*******************************************************************************/
	$new_timeline_item->setHtml($objDistance->getHtmlMain());
	$bunId = date("Y-m-d H:i:s");
	$new_timeline_item->setBundleId($bunId);
	$new_timeline_item->setIsBundleCover(TRUE);
	/*******************************************************************************
	Add another card for description and type of event.
	*******************************************************************************/
    $notification = new Google_NotificationConfig();
    $notification->setLevel("DEFAULT");
    $new_timeline_item->setNotification($notification);
    // $mirror_service to $user_specific_mirror_service
    insert_timeline_item($user_specific_mirror_service, $new_timeline_item, null, null);
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
    $new_timeline_item2 = new Google_TimelineItem();
	if((strlen($postdata) > 128) && ($good2go == TRUE)) {
		$postdata = $objDetail->utf8_urldecode($postdata);
		$content = preg_replace('/<.*?>/', '', $postdata);
    	$new_timeline_item2->setSpeakableText($content);
	}
	else
	{
		$rtext = $objDistance->getAuthor() . ". " . $objDistance->getTitle() . ". " . $objDistance->getVenue() . ". " . $objDistance->getLocation() . ". ";
		$new_timeline_item2->setSpeakableText($rtext);
	}
    /*******************************************************************************
    Test new audio here // NOTE: Modified Stephen La Pierre 10/3/13. At 3:52 PM
    *******************************************************************************/
	$new_timeline_item2->setBundleId($bunId);
	$new_timeline_item2->setId($new_timeline_item->getId());
	$new_timeline_item2->setHtml($objDistance->getHtmlPage());
	$new_timeline_item2->setIsBundleCover(FALSE);
	/*******************************************************************************
	Only set location if it's an event.
	*******************************************************************************/
	if($objDistance->getIsMap()) {
		$eventLoc = new Google_Location();
		$eventLoc->setLatitude($objDistance->getDest_latitude());
		$eventLoc->setLongitude($objDistance->getDest_longitude());
		$new_timeline_item2->setLocation($eventLoc);
	}

    $notification2 = new Google_NotificationConfig();
    $notification2->setLevel("DEFAULT");
    $new_timeline_item2->setNotification($notification2);
    $new_timeline_item2->setMenuItems($menu_items);
    // $mirror_service to $user_specific_mirror_service
    insert_timeline_item($user_specific_mirror_service, $new_timeline_item2, null, null);
	/*******************************************************************************
	Add map to bundle if it's an event only
	*******************************************************************************/
	if($objDistance->getIsMap()) {
		$new_timeline_item3 = new Google_TimelineItem();
		$rtext = $objDistance->getDir_voice();
		$new_timeline_item3->setSpeakableText($rtext);
		$eventLoc = new Google_Location();
		$eventLoc->setLatitude($objDistance->getDest_latitude());
		$eventLoc->setLongitude($objDistance->getDest_longitude());
		$new_timeline_item3->setLocation($eventLoc);
		$new_timeline_item3->setBundleId($bunId);
		$new_timeline_item3->setId($new_timeline_item->getId());
		$new_timeline_item3->setHtml($objDistance->getMapHtml());
		$new_timeline_item3->setIsBundleCover(FALSE);
		$notification3 = new Google_NotificationConfig();
		$notification3->setLevel("DEFAULT");
		$new_timeline_item3->setNotification($notification3);
		$new_timeline_item3->setMenuItems($menu_items);
		// $mirror_service to $user_specific_mirror_service
		insert_timeline_item($user_specific_mirror_service, $new_timeline_item3, null, null);
	  } // if($objDistance->getIsMap())
     } // foreach ($credentials as $credential)
	} // else if (count($credentials) > 10)
//set POST variables
}

?>