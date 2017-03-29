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

require_once 'config.php';
require_once 'mirror-client.php';
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_MirrorService.php';
require_once 'util.php';
require_once 'distance.php';

$file = "/tmp/glass.log";
$message = "";
$client = get_google_api_client();
// Authenticate if we're not already
if (!isset($_SESSION['userid']) || get_credentials($_SESSION['userid']) == null) {
  header('Location: ' . $base_url . '/oauth2callback.php');
  exit;
} else {
  verify_credentials(get_credentials($_SESSION['userid']));
  $client->setAccessToken(get_credentials($_SESSION['userid']));
  store_credentials($_SESSION['userid'], get_credentials($_SESSION['userid']));
}

header('Location: https://diner.harpercollins.com/events/settings.php');

?>
<!doctype html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Harper Events</title>
  <link href="./static/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
  <style>
    .button-icon { max-width: 75px; }
    .tile {
      border-left: 1px solid #444;
      padding: 5px;
      list-style: none;
    }
    .btn { width: 100%; }
  </style>
</head>
<body>
<div class="navbar navbar-inverse navbar-fixed-top">
  <div class="navbar-inner">
    <div class="container">
      <a class="brand" href="#">Harper Glassware Project</a>
    </div>
  </div>
</div>

<div class="container">

  <div class="hero-unit">
    <h1>Registered</h1>


  <div >
    <h2>For Harper Collins timeline</h2>
<!--    <p>To un-subscribes just click 
      <a href="https://developers.google.com/glass/contacts">here</a>.</p> --> 

</div>

<script
    src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="/static/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
