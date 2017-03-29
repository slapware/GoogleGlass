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

require_once '../config.php';
require_once '../mirror-client.php';
require_once '../google-api-php-client/src/Google_Client.php';
require_once '../google-api-php-client/src/contrib/Google_MirrorService.php';


function store_credentials($user_id, $credentials) {
	$db = init_db();

 	$sql = $db->prepare("SELECT COUNT(*) FROM `credentials` WHERE userid = ? AND credentials = ?");

	$sql->bindParam(1, $user_id);
	$sql->bindParam(2, $credentials);
	$result = $sql->execute();
	$number_of_rows = $sql->fetchColumn();
	echo "number_of_rows = " . $number_of_rows['COUNT(*)'] . '\n';
	echo "userid = " . $user_id . '\n';
	echo "credentials = " . $credentials . '\n';
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
		  $problem = 'util Insert Exception ' . $errors;
		  file_put_contents($file, $problem, FILE_APPEND);
		}
	}
	else {
 		$insert = $db->prepare("UPDATE `credentials` SET userid=:uid, credentials=:cd WHERE userid=:uid");
		$insert->bindParam(':uid', $user_id);
		$insert->bindParam(':cd', $credentials);    
		$insert->execute();
		if($insert->errorCode() == 0) {
		echo "updated ID !";
		} else {
    		$errors = $insert->errorInfo();
    		echo($errors[2]);
		  $file = '/tmp/glass.log';
		  $problem = 'util Update Exception ' . $errors;
		  file_put_contents($file, $problem, FILE_APPEND);
		}
	}
}	// store_credentials

function get_credentials($user_id) {
	$db = init_db();

	$query = $db->prepare("select * from credentials where userid=?");

	//  $row = sqlite_fetch_array($query);
    $query->bindParam(1, $user_id);
	$query->bindColumn(2, $creds); 
        
	try
	{
		$result = $query->execute();
	}
	catch (PDOException $Exception)
	{
	  $file = '/tmp/glass.log';
	  $problem = 'util Exception ' . $e->errorMessage();
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

function list_credentials() {
	$db = init_db();

  	$query = $db->prepare('select userid, credentials from credentials');
	$query->bindColumn(1, $user_id); 
  	$query->execute();
  	$result = $query->fetchAll(PDO::FETCH_ASSOC);
  	return $result;

}	// list_credentials

// Create the credential storage if it does not exist
function init_db() {
	global $sqlite_database;

	$dsn = "mysql:host=localhost;dbname=glass";
	$opt = array(
		// any occurring errors wil be thrown as PDOException
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		// an SQL command to execute when connecting
		PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"
		);
	$db = new PDO($dsn, "glassman", "hcglasshole");

	return $db;
}	// init_db

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
}	// bootstrap_new_user