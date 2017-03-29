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
//  Modified SLP to make devotional data source.

require_once 'config.php';
require_once 'mirror-client.php';
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_MirrorService.php';


function tour_store_credentials($user_id, $credentials) {
	$db = tour_init_db();

 	$sql = $db->prepare("SELECT COUNT(*) FROM `tour_schedule` WHERE userid = ?");
 	
	$sql->bindParam(1, $user_id);
	$result = $sql->execute();
	$number_of_rows = $sql->fetchColumn();
	if(!$result)
	{
		echo "DB query failed !";
		  $file = '/tmp/glass.log';
		  $problem = 'tour_util query failed ';
		  file_put_contents($file, $problem, FILE_APPEND);
	}
 	if ($number_of_rows == 0) {
		$insert = $db->prepare("insert into `tour_schedule` values (?, ?)");
		$insert->bindParam(1, $user_id);
		$insert->bindParam(2, $credentials); 
		$insert->execute();
		if($insert->errorCode() == 0) {
		echo "inserted ID !";
		} else {
    		$errors = $insert->errorInfo();
    		echo($errors[2]);
		  $file = '/tmp/glass.log';
		  $problem = 'tour_util Insert Exception ' . $errors[2];
		  file_put_contents($file, $problem, FILE_APPEND);
		}
	}
	else {
 		$insert = $db->prepare("UPDATE `tour_schedule` SET userid=:uid, credentials=:cd WHERE userid=:uid");
		$insert->bindParam(':uid', $user_id);
		$insert->bindParam(':cd', $credentials);    
		$insert->execute();
		if($insert->errorCode() == 0) {
		echo "updated ID !";
		} else {
    		$errors = $insert->errorInfo();
    		echo($errors[2]);
		  $file = '/tmp/glass.log';
		  $problem = 'tour_util Update Exception ' . $errors;
		  file_put_contents($file, $problem, FILE_APPEND);
		}
	}
}	// store_credentials
/**
 * Set hour of day for users text delivery.
 * @param string $user_id
 * @param int $hour
 */
function tour_update_delivery($user_id, $hour, $mon,$tue,$wed,$thu,$fri,$sat,$sun) {
	$db = tour_init_db();
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
 * Get users with provided delivery hour
 * @param int $hour
 * @return multitype:
 */
function tour_list_credentials_byhour($hour, $dow) {
	$db = tour_init_db();
	$sql = 'select userid, credentials from tour_schedule where delivery_hour=:hour AND ';
	$sql = $sql . $dow .  ' = "Y"';
	//  	$query = $db->prepare('select userid, credentials from daily_devotional where delivery_hour=:hour');
	$query = $db->prepare($sql);
	$query->bindParam(':hour', $hour);
	$query->execute();
	$result = $query->fetchAll(PDO::FETCH_ASSOC);
	return $result;
}	// list_credentials

function tour_get_delivery_hour($user_id) {
	$db = tour_init_db();
	//	$query = $db->prepare("select delivery_hour from daily_devotional where userid=?");
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

function tour_remove_user($user_id) {
	$db = tour_init_db();
	$query = $db->prepare("DELETE FROM tour_schedule where userid=?");
    $query->bindParam(1, $user_id);
	try
	{
		$result = $query->execute();
	}
	catch (PDOException $Exception)
	{
	  $file = '/tmp/glass.log';
	  $problem = 'tour_util delete Exception ' . $Exception->errorMessage();
	  file_put_contents($file, $problem, FILE_APPEND);
	  //display custom message
	  echo $Exception->getMessage();
	}
	if(!$result)
	{
		echo "devote_remove_user DB query failed !\n";
		$err = $query->errorInfo();
		echo print_r($err, true);
	}
}

function tour_get_credentials($user_id) {
	$db = tour_init_db();

	$query = $db->prepare("select * from tour_schedule where userid=?");

    $query->bindParam(1, $user_id);
        
	try
	{
		$result = $query->execute();
	}
	catch (PDOException $Exception)
	{
	  $file = '/tmp/glass.log';
	  $problem = 'tour_util Exception ' . $Exception->errorMessage();
	  file_put_contents($file, $problem, FILE_APPEND);
	  //display custom message
	  echo $Exception->getMessage();
	}
	if(!$result)
	{
		echo "devote_get_credentials DB query failed !\n";
		$err = $query->errorInfo();
		echo print_r($err, true);
	}
	$row = $query->fetch(PDO::FETCH_ASSOC);
	
  return $row['credentials'];
}	// get_credentials

function tour_list_credentials() {
	$db = tour_init_db();

  	$query = $db->prepare('select userid, credentials from tour_schedule');
//	$query->bindColumn(1, $user_id); 
  	$query->execute();
  	$result = $query->fetchAll(PDO::FETCH_ASSOC);
  	return $result;

}	// list_credentials

// Create the credential storage if it does not exist
function tour_init_db() {
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

