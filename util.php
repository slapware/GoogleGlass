<?php
/*
* Copyright (C) 2013 Google Inc.
*
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
*
*      http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*/
//  Author: Jenny Murphy - http://google.com/+JennyMurphy
//  Modified SLP to make mysql instead of sqlite.

require_once 'config.php';
require_once 'mirror-client.php';
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_MirrorService.php';
require_once 'distance.php';

if (!class_exists("ProductDetail")) :
require_once('ProductDetail.php');
endif;
/**
 * Store or update user credentials.
 *
 * @param int $user_id The user in question.
 * @param string $credentials The token for the user.
 */
function store_credentials($user_id, $credentials) {
	$db = init_db();

 	$sql = $db->prepare("SELECT COUNT(*) FROM `credentials` WHERE userid = ?");
 	
	$sql->bindParam(1, $user_id);
	$result = $sql->execute();
	$number_of_rows = $sql->fetchColumn();
	if(!$result)
	{
		echo "DB query failed !";
		  $file = '/tmp/glass.log';
		  $problem = 'DB query failed ';
		  file_put_contents($file, $problem, FILE_APPEND);
	}
 	if ($number_of_rows == 0) {
		$insert = $db->prepare("insert into `credentials` values (?, ?)");
		$insert->bindParam(1, $user_id);
		$insert->bindParam(2, $credentials); 
		$insert->execute();
		if($insert->errorCode() == 0) {
		echo "inserted ID !";
		} else {
    		$errors = $insert->errorInfo();
    		echo($errors[2]);
		  $file = '/tmp/glass.log';
		  $problem = 'util Insert Exception ' . $errors[2];
		  file_put_contents($file, $problem, FILE_APPEND);
		}
		// add insert into tour daily
		$insert_t = $db->prepare("insert into `tour_schedule` values (?)");
		$insert_t->bindParam(1, $user_id);
 			$insert_t->execute();
		if($insert_t->errorCode() == 0) {
		} else {
    		$errors = $insert_t->errorInfo();
    		echo($errors[2]);
		  $file = '/tmp/glass.log';
		  $problem = 'util Insert Exception ' . $errors[2];
		  file_put_contents($file, $problem, FILE_APPEND);
		}
 	}
	else {
 		$insert = $db->prepare("UPDATE `credentials` SET userid=:uid, credentials=:cd WHERE userid=:uid");
		$insert->bindParam(':uid', $user_id);
		$insert->bindParam(':cd', $credentials);    
		$insert->execute();
		if($insert->errorCode() == 0) {
		} else {
    		$errors = $insert->errorInfo();
    		echo($errors[2]);
		  $file = '/tmp/glass.log';
		  $problem = 'util Update Exception ' . $errors;
		  file_put_contents($file, $problem, FILE_APPEND);
		}
	}
}	// store_credentials
/**
 * Get settings for user, hour days for settings display setup.
 *
 * @param int $user_id The user id to get settings for.
 */
function tour_get_delivery_hour($user_id) {
	$db = init_db();
	$query = $db->prepare("select delivery_hour,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday from tour_schedule where userid=?");
	$query->bindParam(1, $user_id);
	try
	{
		$result = $query->execute();
	}
	catch (PDOException $Exception)
	{
		$file = '/tmp/devote.log';
		$problem = 'tour_util Exception ' . $Exception->errorMessage();
		file_put_contents($file, $problem, FILE_APPEND);
		//display custom message
		echo $Exception->getMessage();
	}
	if(!$result)
	{
		echo "devote_get_delivery_hour DB query failed !\n";
		$err = $query->errorInfo();
		echo print_r($err, true);
	}
	$hour = $query->fetch(PDO::FETCH_ASSOC);
	$file = '/tmp/devote.log';
	file_put_contents($file, $hour, FILE_APPEND);

	//	return $hour['delivery_hour'];
	return $hour;
}
/**
 * Get array of userid and token from user id.
 *
 * @param int $user_id The user id in question.
 *
 * @return array $row UserID and token in array.
 */
function get_credentials($user_id) {
	$db = init_db();

	$query = $db->prepare("select * from credentials where userid=?");

	//  $row = sqlite_fetch_array($query);
    $query->bindParam(1, $user_id);
        
	try
	{
		$result = $query->execute();
	}
	catch (PDOException $Exception)
	{
	  $file = '/tmp/glass.log';
	  $problem = 'util Exception ' . $Exception->errorMessage();
	  file_put_contents($file, $problem, FILE_APPEND);
	  //display custom message
	  echo $Exception->getMessage();
	}
	if(!$result)
	{
		echo "get_credentials DB query failed !\n";
		$err = $query->errorInfo();
		echo print_r($err, true);
	}
	$row = $query->fetch(PDO::FETCH_ASSOC);
	
  return $row['credentials'];
}	// get_credentials

/**
 * Set hour of day for users text delivery.
 * @param string $user_id
 * @param int $hour
 */
function tour_update_delivery($user_id, $hour, $mon,$tue,$wed,$thu,$fri,$sat,$sun) {
	$db = init_db();
	$deliver = $db->prepare("UPDATE `tour_schedule` SET delivery_hour=:hour, Monday = :mon, Tuesday = :tue, Wednesday = :wed, Thursday = :thu, Friday = :fri, Saturday = :sat, Sunday = :sun WHERE userid=:uid");
	$deliver->bindParam(':hour', $hour);
	$deliver->bindParam(':mon', $mon);
	$deliver->bindParam(':tue', $tue);
	$deliver->bindParam(':wed', $wed);
	$deliver->bindParam(':thu', $thu);
	$deliver->bindParam(':fri', $fri);
	$deliver->bindParam(':sat', $sat);
	$deliver->bindParam(':sun', $sun);
	$deliver->bindParam(':uid', $user_id);
	try
	{
		$result = $deliver->execute();
	}
	catch (PDOException $Exception)
	{
		$file = '/tmp/devote.log';
		$problem = 'tour_util update Exception ' . $Exception->errorMessage();
		file_put_contents($file, $problem, FILE_APPEND);
		//display custom message
		echo $Exception->getMessage();
	}
	if(!$result)
	{
		$file = '/tmp/devote.log';
		echo "devote_update_delivery DB query failed !\n";
		$err = $deliver->errorInfo();
		$error = print_r($err, true);
		$problem = 'tour_update_delivery error ' .  $error;
		file_put_contents($file, $problem, FILE_APPEND);
	}
}
/**
 * Get array of all users credentials.
 *
 * @param void.
 *
 * @return array $result All users in array.
 */
function list_credentials() {
	$db = init_db();

  	$query = $db->prepare('select userid, credentials from credentials');
  	$query->execute();
  	$result = $query->fetchAll(PDO::FETCH_ASSOC);
  	return $result;

}	// list_credentials
/**
 * Get users with provided delivery hour
 * @param int $hour
 * @return multitype:
 */
function tour_list_credentials_byhour($hour, $dow) {
	$db = init_db();
	$sql = $db->prepare('select t.userid, c.credentials from tour_schedule t, credentials c where t.delivery_hour=:hour AND ' . $dow . '= "Y" AND t.userid = c.userid');
	$sql->bindParam(':hour', $hour);
	try
	{
		$sql->execute();
	}
	catch (PDOException $Exception)
	{
		$file = '/tmp/event.log';
		$problem = 'tour_list_credentials_byhour Exception ' . $Exception->errorMessage();
		file_put_contents($file, $problem, FILE_APPEND);
		//display custom message
		echo $Exception->getMessage();
	}
	$result = $sql->fetchAll(PDO::FETCH_ASSOC);
	return $result;
}	// list_credentials

/**
 * Init the MySql database for query or update.
 *
 * @param void.
 *
 * @return array $db object.
 */
function init_db() {
	global $sqlite_database;

	$dsn = "mysql:host=diner.harpercollins.com;dbname=glass";
//	$dsn = "mysql:host=localhost;dbname=glass";
	$opt = array(
		// any occurring errors wil be thrown as PDOException
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		// an SQL command to execute when connecting
		PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"
		);
	$db = new PDO($dsn, "glassman", "hcglasshole");

	return $db;
}	// init_db
/**
 * bootstrap_new_user.
 * present tour cards to this user.
 */
function bootstrap_new_user() {
  global $base_url;

  $client = get_google_api_client();
  $client->setAccessToken(get_credentials($_SESSION['userid']));

  //////////////////////////////////////////////////////////////
  for ($x = 4; $x >= 0; $x--)
  {
  	// A glass service for interacting with the Mirror API
  	$mirror_service = new Google_MirrorService($client);
	// top per user loop
	$new_timeline_item = new Google_TimelineItem();
	$location = $mirror_service->locations->get('latest');

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
	$new_timeline_item2 = new Google_TimelineItem();
	$notification = new Google_NotificationConfig();
	$notification->setLevel("DEFAULT");
	$new_timeline_item->setNotification($notification);
	// $mirror_service to $user_specific_mirror_service
	insert_timeline_item($mirror_service, $new_timeline_item, null, null);
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
	$new_timeline_item2 = new Google_TimelineItem();

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
	insert_timeline_item($mirror_service, $new_timeline_item2, null, null);
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
		insert_timeline_item($mirror_service, $new_timeline_item3, null, null);
  	  } // if($objDistance->getIsMap())
  }  
  //////////////////////////////////////////////////////////////
  
//   $timeline_item = new Google_TimelineItem();
//   $timeline_item->setText("Welcome to the Mirror API Harper Events");
// 
//   insert_timeline_item($mirror_service, $timeline_item, null, null);

//   insert_contact($mirror_service, "harper-events", "Harper Events",
//       $base_url . "/static/images/chipotle-tube-640x360.jpg");

  subscribe_to_notifications($mirror_service, "timeline",
    $_SESSION['userid'], $base_url . "/notify.php");
}	// bootstrap_new_user