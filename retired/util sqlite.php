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

require_once '../config.php';
require_once '../mirror-client.php';
require_once '../google-api-php-client/src/Google_Client.php';
require_once '../google-api-php-client/src/contrib/Google_MirrorService.php';


function store_credentials($user_id, $credentials) {
	$db = init_db();

	//  $user_id = sqlite_escape_string(strip_tags($user_id));
	//  $credentials = sqlite_escape_string(strip_tags($credentials));
 	$sql = $db->prepare("SELECT COUNT(*) FROM `credentials` WHERE userid = ? AND credentials = ?");
// 	$sql = "SELECT COUNT(*) FROM `credentials` WHERE userid = " . $user_id . " AND credentials = " . $credentials;

   $sql->bindParam(1, $user_id);
   $sql->bindParam(2, $credentials);
//	$result = $db->prepare($sql);
//	$result = exec($sql);
    $result = $sql->execute();
	echo "result = " . $result;
	if(!$result)
	{
		echo "DB query failed !";
	}
// 	if ($number_of_rows == 0) {
	if ($result == 0) {
		$insert = $db->prepare("insert into `credentials` values (?, ?)");
		$insert->bindParam(1, $user_id);
		$insert->bindParam(2, $credentials);    
		$insert->execute();
		echo "insert ID !";
	}
	else {
 		$insert = $db->prepare("UPDATE `credentials` SET userid=:uid, credentials=:cd WHERE userid=:uid");
		$insert->bindParam(':uid', $user_id);
		$insert->bindParam(':cd', $credentials);    
//		$insert->bindParam(3, $user_id);
		$insert->execute();
		echo "update ID !";
	}

	//  $insert = $db->prepare("insert or replace into credentials values ('$user_id', credentials = '$credentials')");
	//  sqlite_exec($db, $insert);
//	$insert->execute();
}

function get_credentials($user_id) {
	$db = init_db();
	//  $user_id = sqlite_escape_string(strip_tags($user_id));

	//  $query = sqlite_query($db, "select * from credentials where userid= '$user_id'");
	$query = $db->prepare("select * from credentials where userid=?");

	//  $row = sqlite_fetch_array($query);
    $query->bindParam(1, $user_id);
        
	try
	{
		$result = $query->execute();
	}
	catch (customException $e)
	{
	  $file = '/tmp/glass.log';
	  $problem = 'util Exception ' . $e->errorMessage();
	  file_put_contents($file, $problem, FILE_APPEND);
	  //display custom message
	  echo $e->errorMessage();
	}
	if(!$result)
	{
		echo "get_credentials DB query failed !\n";
		$err = $query->errorInfo();
		echo print_r($err, true);
	}
	$row = $query->fetch(PDO::FETCH_ASSOC);
  return $row['credentials'];
}

function list_credentials() {
	$db = init_db();

  	$query = $db->prepare('select userid, credentials from credentials');
  	$query->execute();
  	$result = $query->fetchAll(PDO::FETCH_ASSOC);
  	return $result;

}

// Create the credential storage if it does not exist
function init_db() {
	global $sqlite_database;

	//  $db = sqlite_open($sqlite_database);
	$db = new PDO('sqlite:' . $sqlite_database);
	$test_query = "select count(*) from sqlite_master where name = 'credentials'";
	//  if (sqlite_fetch_single(sqlite_query($db, $test_query)) == 0) {
	$result=$db->query($test_query);
	$number_of_rows = $result->fetchColumn();
	if ($number_of_rows == 0) {
		$create_table = "create table credentials (userid text not null unique, " .
				"credentials text not null);";
		//    sqlite_exec($db, $create_table);
		$db->exec($create_table);
	}
	return $db;
}

function bootstrap_new_user() {
  global $base_url;

  $client = get_google_api_client();
  $client->setAccessToken(get_credentials($_SESSION['userid']));

  // A glass service for interacting with the Mirror API
  $mirror_service = new Google_MirrorService($client);

  $timeline_item = new Google_TimelineItem();
  $timeline_item->setText("Welcome to the Mirror API Harper Events");

  insert_timeline_item($mirror_service, $timeline_item, null, null);

  insert_contact($mirror_service, "harper-events", "Harper Events",
      $base_url . "/static/images/chipotle-tube-640x360.jpg");

  subscribe_to_notifications($mirror_service, "timeline",
    $_SESSION['userid'], $base_url . "/notify.php");
}