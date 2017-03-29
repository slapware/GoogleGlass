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
require_once 'BlogFeed.php';

if (!class_exists("ProductDetail")) :
require_once('ProductDetail.php');
endif;

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
}

// A glass service for interacting with the Mirror API
$mirror_service = new Google_MirrorService($client);

// But first, handle POST data from the form (if there is any)
switch ($_POST['operation']) {
  case 'insertItem':	// **** insertItem ***********
    $new_timeline_item = new Google_TimelineItem();
    $new_timeline_item->setText($_POST['message']);

    $notification = new Google_NotificationConfig();
    $notification->setLevel("DEFAULT");
    $new_timeline_item->setNotification($notification);

    if (isset($_POST['imageUrl']) && isset($_POST['contentType'])) {
      insert_timeline_item($mirror_service, $new_timeline_item,
        $_POST['contentType'], file_get_contents($_POST['imageUrl']));
    } else {
      insert_timeline_item($mirror_service, $new_timeline_item, null, null);
    }

    $message = "Timeline Item inserted!";
    break;
  case 'insertItemWithAction':	// **** insertItemWithAction ***********
    $new_timeline_item = new Google_TimelineItem();
    $new_timeline_item->setText("What did you have for lunch?");

    $notification = new Google_NotificationConfig();
    $notification->setLevel("DEFAULT");
    $new_timeline_item->setNotification($notification);

    $menu_items = array();

    // A couple of built in menu items
    $menu_item = new Google_MenuItem();
    $menu_item->setAction("READ_ALOUD");
    array_push($menu_items, $menu_item);
    $new_timeline_item->setSpeakableText("What did you eat? Bacon?");

    $menu_item = new Google_MenuItem();
    $menu_item->setAction("SHARE");
    array_push($menu_items, $menu_item);

    // A custom menu item
    $custom_menu_item = new Google_MenuItem();
    $custom_menu_value = new Google_MenuValue();
    $custom_menu_value->setDisplayName("Drill Into");
    $custom_menu_value->setIconUrl("http://diner.harpercollins.com/events/static/images/drill.png");

    $custom_menu_item->setValues(array($custom_menu_value));
    $custom_menu_item->setAction("CUSTOM");
    // This is how you identify it on the notification ping
    $custom_menu_item->setId("safe-for-later");
    array_push($menu_items, $custom_menu_item);

    $new_timeline_item->setMenuItems($menu_items);

    insert_timeline_item($mirror_service, $new_timeline_item, null, null);

    $message = "Inserted a timeline item you can reply to";
    break;
  case 'insertTimelineAllUsers':		// **** insertTimelineAllUsers ***********
    $credentials = list_credentials();
    if (count($credentials) > 10) {
      $message = "Found " . count($credentials) . " users. Aborting to save your quota.";
    } else {
      foreach ($credentials as $credential) {
        $user_specific_client = get_google_api_client();
        $user_specific_client->setAccessToken($credential['credentials']);

        $new_timeline_item = new Google_TimelineItem();
        $new_timeline_item->setText("Did you know cats have 167 bones in their tails? Mee-wow!");

        $user_specific_mirror_service = new Google_MirrorService($user_specific_client);

        insert_timeline_item($user_specific_mirror_service, $new_timeline_item, null, null);
      }
      $message = "Sent a cat fact to " . count($credentials) . " users.";
    }
    break;
   /*******************************************************************************
   Start search with voice section
   *******************************************************************************/ 
  case 'insertSearchBookAllUsers':		// **** insertSearchBookAllUsers ***********
    $credentials = list_credentials();
    if (count($credentials) > 10) {
      $message = "Found " . count($credentials) . " users. Aborting to save your quota.";
    } else {
      foreach ($credentials as $credential) {
        $user_specific_client = get_google_api_client();
        $user_specific_client->setAccessToken($credential['credentials']);

     	$menu_items = array();
        $new_timeline_item = new Google_TimelineItem();
        $new_timeline_item->setText("Search for book by title.");
        $html = '<article>   <section>    <p class="text-auto-size"><strong class="blue">Harper Collins</strong></p> <hr>Search for book by book title. </section><footer>  </footer>  </article>';
        $new_timeline_item->setHtml($html);
        // A couple of built in menu items
		$menu_item = new Google_MenuItem();
		$menu_item->setAction("READ_ALOUD");
		array_push($menu_items, $menu_item);
		$pin_item = new Google_MenuItem();
		$pin_item->setAction("TOGGLE_PINNED");
		array_push($menu_items, $pin_item);

		$new_timeline_item->setSpeakableText("Search for book by name. For example, Kansas City lightning.. Start the name with. list.. to get all matches and formats for the book.");
		// A custom menu item
		$custom_menu_item = new Google_MenuItem();
		$custom_menu_value = new Google_MenuValue();
		$custom_menu_value->setDisplayName("Search");
		$custom_menu_value->setIconUrl("http://diner.harpercollins.com/events/static/images/search.png");

		$custom_menu_item->setValues(array($custom_menu_value));
		$custom_menu_item->setAction("REPLY");
		// Share menu item
    	$share_item = new Google_MenuItem();
    	$share_item->setAction("SHARE");
    	array_push($menu_items, $share_item);
		// This is how you identify it on the notification ping
		array_push($menu_items, $custom_menu_item);
		/* un-comment to add Delete menu item */
// 		$del_item = new Google_MenuItem();
// 		$del_item->setAction("DELETE");
// 		array_push($menu_items, $del_item);
		
    	$new_timeline_item->setMenuItems($menu_items);

        $user_specific_mirror_service = new Google_MirrorService($user_specific_client);
// 		$contact = $user_specific_mirror_service->contacts->get("0_110350450990179447197");
// 		$new_timeline_item->setCreator($contact);

        insert_timeline_item($user_specific_mirror_service, $new_timeline_item, null, null);
      }
      $message = "Sent a search book to " . count($credentials) . " users.";
    }
    break;
    /*******************************************************************************
    INSERT SEARCH AUTHOR BOOKS ALL USERS
    *******************************************************************************/
    case 'insertSearchAuthorBooksAllUsers':		// **** insertSearchAuthorBooksAllUsers ***********
    	$credentials = list_credentials();
    	if (count($credentials) > 10) {
    		$message = "Found " . count($credentials) . " users. Aborting to save your quota.";
    	} else {
    		foreach ($credentials as $credential) {
    			$user_specific_client = get_google_api_client();
    			$user_specific_client->setAccessToken($credential['credentials']);
    
    			$menu_items = array();
    			$new_timeline_item = new Google_TimelineItem();
    			$new_timeline_item->setText("Search for Author books by author name.");
    			$html = '<article>   <section>    <p class="text-auto-size"><strong class="blue">Harper Collins</strong></p>    <hr>  Search for books by author name  </section><footer>  </footer>  </article>';
    			$new_timeline_item->setHtml($html);
    			// A couple of built in menu items
    			$menu_item = new Google_MenuItem();
    			$menu_item->setAction("READ_ALOUD");
    			array_push($menu_items, $menu_item);
    			$pin_item = new Google_MenuItem();
    			$pin_item->setAction("TOGGLE_PINNED");
    			array_push($menu_items, $pin_item);
    
    			$new_timeline_item->setSpeakableText("Search for books by author name. For example, Wiley Cash.");
    			// A custom menu item
    			$custom_menu_item = new Google_MenuItem();
    			$custom_menu_value = new Google_MenuValue();
    			$custom_menu_value->setDisplayName("Search");
    			$custom_menu_value->setIconUrl("http://diner.harpercollins.com/events/static/images/search.png");
    
    			$custom_menu_item->setValues(array($custom_menu_value));
    			$custom_menu_item->setAction("REPLY");
    			// This is how you identify it on the notification ping
    			array_push($menu_items, $custom_menu_item);
				// Share menu item
    			$share_item = new Google_MenuItem();
    			$share_item->setAction("SHARE");
    			array_push($menu_items, $share_item);
    			/* un-comment to add Delete menu item */
    			// 		$del_item = new Google_MenuItem();
    			// 		$del_item->setAction("DELETE");
    			// 		array_push($menu_items, $del_item);
    
    			$new_timeline_item->setMenuItems($menu_items);
    
    			$user_specific_mirror_service = new Google_MirrorService($user_specific_client);
    			// 		$contact = $user_specific_mirror_service->contacts->get("0_110350450990179447197");
    			// 		$new_timeline_item->setCreator($contact);
    
    			insert_timeline_item($user_specific_mirror_service, $new_timeline_item, null, null);
    		}
    		$message = "Sent a search book to " . count($credentials) . " users.";
    	}
    	break;
    /*******************************************************************************
    SEARCH TOURS BY AUTHOR ALL USERS
    *******************************************************************************/
    case 'searchtoursbyauthorAllUsers':
    	$credentials = list_credentials();
    	if (count($credentials) > 10) {
    		$message = "Found " . count($credentials) . " users. Aborting to save your quota.";
    	} else {
    		foreach ($credentials as $credential) {
    			$user_specific_client = get_google_api_client();
    			$user_specific_client->setAccessToken($credential['credentials']);
    	
    			$menu_items = array();
    			$new_timeline_item = new Google_TimelineItem();
    			$new_timeline_item->setText("Search for tours by author.");
    			$html = '<article>   <section>    <p class="text-auto-size"><strong class="blue">Harper Collins</strong></p>  <hr> Search for tours by author name. </section><footer>  </footer>  </article>';
    			$new_timeline_item->setHtml($html);
    			// A couple of built in menu items
    			$menu_item = new Google_MenuItem();
    			$menu_item->setAction("READ_ALOUD");
    			array_push($menu_items, $menu_item);
    			$pin_item = new Google_MenuItem();
    			$pin_item->setAction("TOGGLE_PINNED");
    			array_push($menu_items, $pin_item);
    	
    			$new_timeline_item->setSpeakableText("Search for tours by Author Name. For example, Sarah Palin.");
    			// A custom menu item
    			$custom_menu_item = new Google_MenuItem();
    			$custom_menu_value = new Google_MenuValue();
    			$custom_menu_value->setDisplayName("Search");
    			$custom_menu_value->setIconUrl("http://diner.harpercollins.com/events/static/images/search.png");
    	
    			$custom_menu_item->setValues(array($custom_menu_value));
    			$custom_menu_item->setAction("REPLY");
    			// This is how you identify it on the notification ping
    			array_push($menu_items, $custom_menu_item);
				// Share menu item
    			$share_item = new Google_MenuItem();
    			$share_item->setAction("SHARE");
    			array_push($menu_items, $share_item);
    			/* un-comment to add Delete menu item */
    			// 		$del_item = new Google_MenuItem();
    			// 		$del_item->setAction("DELETE");
    			// 		array_push($menu_items, $del_item);
    	
    			$new_timeline_item->setMenuItems($menu_items);
    	
    			$user_specific_mirror_service = new Google_MirrorService($user_specific_client);
    			// 		$contact = $user_specific_mirror_service->contacts->get("0_110350450990179447197");
    			// 		$new_timeline_item->setCreator($contact);
    	
    			insert_timeline_item($user_specific_mirror_service, $new_timeline_item, null, null);
    		} // foreach
    		$message = "Sent a author tour search to " . count($credentials) . " users.";
    	} // else
    	break;
    /*******************************************************************************
    End reply with voice section
    *******************************************************************************/
  case 'insertSubscription':
    $message = subscribe_to_notifications($mirror_service, $_POST['subscriptionId'],
      $_SESSION['userid'], $base_url . "/notify.php");
    break;
  case 'deleteSubscription':
    $message = $mirror_service->subscriptions->delete($_POST['subscriptionId']);
    break;
  case 'petethecat':	// **** Pete the cat ***********
  	$credentials = list_credentials();
  	if (count($credentials) > 10) {
  		$message = "Found " . count($credentials) . " users. Aborting to save your quota.";
  	}
  	else
  	{
  		foreach ($credentials as $credential) {
  			$user_specific_client = get_google_api_client();
  			$user_specific_client->setAccessToken($credential['credentials']);
  			$user_specific_mirror_service = new Google_MirrorService($user_specific_client);
  			 
		    $new_timeline_item = new Google_TimelineItem();
		    $html = '<article>  <figure>    <img src="http://www.harpercollins.com/harperimages/isbn/medium_large/6/9780062110596.jpg" height="360" width="240">';
		    $html = $html . '</figure>  <section>    <h1 class="text-small"><em class="yellow">Eric Litwin / James Dean</em></h1>    <p class="text-x-small">';
		    $html = $html . 'Pete the Cat</p>    <hr>    <p class="text-x-small">    Coming Soon<br>     Watch Video Preview     </p>  </section></article>';
			$bunId = date("Y-m-d H:i:s");
			$new_timeline_item->setHtml($html);
			//
		    $notification = new Google_NotificationConfig();
		    $notification->setLevel("DEFAULT");
		    $new_timeline_item->setNotification($notification);
			// New video method is MUCH easier than before
			$menu_items = array();
			$menu_item = new Google_MenuItem();
			$menu_item->setAction("PLAY_VIDEO");
			$menu_item->setPayload("http://diner.harpercollins.com/images/Pete.mp4");
			array_push($menu_items, $menu_item);
			$pin_item = new Google_MenuItem();
			$pin_item->setAction("TOGGLE_PINNED");
			array_push($menu_items, $pin_item);
			$new_timeline_item->setMenuItems($menu_items);
			
		    $post_result = insert_timeline_item($user_specific_mirror_service, $new_timeline_item, null, null);
    	} // foreach
    $message = "Sent a author tour search to " . count($credentials) . " users.";
    } // else
    
    break;
        
  case 'addTourCard':	// **** addTourCard ***********
	$credentials = list_credentials();
	if (count($credentials) > 10) {
		$message = "Found " . count($credentials) . " users. Aborting to save your quota.";
	} 
	else 
	{
	foreach ($credentials as $credential) {
		$user_specific_client = get_google_api_client();
		$user_specific_client->setAccessToken($credential['credentials']);
		$user_specific_mirror_service = new Google_MirrorService($user_specific_client);
    			 
  	// top per user loop
    $new_timeline_item = new Google_TimelineItem();
//    $location = $mirror_service->locations->get('latest');
    $location = $user_specific_mirror_service->locations->get('latest');
    
	$objDistance = new distance($location->getLatitude(), $location->getLongitude());
	$objDistance->run();
	$objDistance->getHtml(intval($_POST['element']));
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
    $share_item = new Google_MenuItem();
    $share_item->setAction("SHARE");
    array_push($menu_items, $share_item);
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
	
    break;
  case 'insertContact':
    insert_contact($mirror_service, $_POST['id'], $_POST['name'],
        $base_url . "/static/images/chipotle-tube-640x360.jpg");
    $message = "Contact inserted. Enable it on MyGlass.";
    break;
  case 'deleteContact':
    delete_contact($mirror_service, $_POST['id']);
    $message = "Contact deleted.";
    break;
} // switch ($_POST['operation'])

//Load cool stuff to show them.
$timeline = $mirror_service->timeline->listTimeline(array('maxResults'=>'3'));
try {
  $contact = $mirror_service->contacts->get("Harper-Events");
} catch (Exception $e) {
//  echo "no contact found. Meh";
  $contact = null;
}
$subscriptions = $mirror_service->subscriptions->listSubscriptions();
$timeline_subscription_exists = false;
$location_subscription_exists = false;
foreach ($subscriptions->getItems() as $subscription) {
  if ($subscription->getId() == 'timeline') {
    $timeline_subscription_exists = true;
  } elseif ($subscription->getId() == 'location') {
    $location_subscription_exists = true;
  }
}

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
    <h1>Your Recent Timeline</h1>
    <?php if ($message != "") { ?>
    <span class="label label-warning">Message: <?php echo $message; ?> </span>
    <?php } ?>

    <div style="margin-top: 5px;">
      <?php foreach ($timeline->getItems() as $timeline_item) { ?>
      <ul class="span3 tile">
        <li><strong>ID: </strong> <?php echo $timeline_item->getId(); ?>
        </li>
        <li>
          <strong>Text: </strong> <?php echo $timeline_item->getText(); ?>
        </li>
        <li>
          <strong>Attachments: </strong>
          <?php
          if ($timeline_item->getAttachments() != null) {
            $attachments = $timeline_item->getAttachments();
            foreach ($attachments as $attachment) { ?>
                <img src="<?php echo $base_url .
                    '/attachment-proxy.php?timeline_item_id=' .
                    $timeline_item->getId() . '&attachment_id=' .
                    $attachment->getId() ?>" />
            <?php
            }
          }
          ?>
        </li>

      </ul>
      <?php } ?>
    </div>
    <div style="clear:both;"></div>
  </div>

  <div class="row">
    <div class="span4">
      <h2>Timeline</h2>

      <p>When you first sign in, this Glassware inserts a welcome message. Use
        these controls to insert more items into your timeline. Learn more about
        the timeline APIs
        <a href="https://developers.google.com/glass/timeline">here</a></p>


      <form method="post">
        <input type="hidden" name="operation" value="insertItem">
        <textarea name="message">Hello Authors!</textarea><br/>
        <button class="btn" type="submit">The above message</button>
      </form>

      <form method="post">
        <input type="hidden" name="operation" value="insertItem">
        <input type="hidden" name="message"
               value="Chipotle says hi!">
        <input type="hidden" name="imageUrl" value="<?php echo $base_url .
            "/static/images/chipotle-tube-640x360.jpg" ?>">
        <input type="hidden" name="contentType" value="image/jpeg">

        <button class="btn" type="submit">A picture
          <img class="button-icon" src="<?php echo $base_url .
             "/static/images/chipotle-tube-640x360.jpg" ?>">
        </button>
      </form>
      <form method="post">
        <input type="hidden" name="operation" value="insertItemWithAction">
        <button class="btn" type="submit">A card you can reply to</button>
      </form>
      <!-- 
      <hr>
      <form method="post">
        <input type="hidden" name="operation" value="insertTimelineAllUsers">
        <button class="btn" type="submit">A card to all users</button>
      </form> -->
      <form method="post">
        <input type="hidden" name="operation" value="insertSearchBookAllUsers">
        <button class="btn" type="submit">A book name-voice search to all users</button>
      </form>
       <form method="post">
        <input type="hidden" name="operation" value="insertSearchAuthorBooksAllUsers">
        <button class="btn" type="submit">An Author books-voice search to all users</button>
      </form>
    </div>
<!--   </div> -->
  <div class="span4">
    <h2>Contacts</h2>
    <p>By default, this project inserts a single contact that accepts
      all content types. Learn more about contacts
      <a href="https://developers.google.com/glass/contacts">here</a>.</p>

      <?php if ($contact == null) { ?>
      <form class="span3"method="post">
        <input type="hidden" name="operation" value="insertContact">
        <input type="hidden" name="iconUrl" value="<?php echo $base_url .
            "/static/images/chipotle-tube-640x360.jpg" ?>">
        <input type="hidden" name="name" value="Harper Events">
        <input type="hidden" name="id" value="Harper-Events">
        <button class="btn" type="submit">Insert Harper Events Contact</button>
      </form>
      <?php } else { ?>
      <form class="span3" method="post">
        <input type="hidden" name="operation" value="deleteContact">
        <input type="hidden" name="id" value="Harper-Events">
        <button class="btn" type="submit">Delete Harper Events Contact</button>
      </form>
    <?php } ?>
       <form method="post">
        <input type="hidden" name="operation" value="requestdevotionaltext">
        <button class="btn" type="submit">Request devotional text by voice</button>
      </form>
       <form method="post">
        <input type="hidden" name="operation" value="searchtoursbyauthorAllUsers">
        <button class="btn" type="submit">Search tours by voice-author all users</button>
      </form>
      </div>

    <div class="span4">
      <h2>Subscriptions</h2>

  <p>By default a subscription is inserted for changes to the
    <code>timeline</code> collection. Learn more about subscriptions
    <a href="https://developers.google.com/glass/subscriptions">here</a></p>

  <p class="label label-info">Note: Subscriptions require SSL. <br>They will
    not work on localhost.</p>

    <form method="post">
      <input type="hidden" name="subscriptionId" value="timeline">
      <input type="hidden" name="operation" value="petethecat">
      <button class="btn" type="submit">Insert Pete the Cat</button>
    </form>
    
<!--
   <form method="post">
  <select name="element">
  	 <option selected value="0">0</option>
  	 <option value="1">1</option>
  	 <option value="2">2</opton>
  	 <option value="3">3</option>
  	 <option value="4">4</opton>
  	 <option value="5">5</option>
  	 <option value="6">6</opton>
  	 <option value="7">7</opton>
  	 <option value="8">8</opton>
  	 <option value="9">9</opton>
  	 <option value="10">10</opton>
  </select>
      <input type="hidden" name="addDevotional" value="timeline">
      <input type="hidden" name="operation" value="addDevotional">
      <button class="btn" type="submit">Add Devotional</button>
    </form>
 -->
 
    <form method="post">
  <select name="element">
  	 <option selected value="0">0</option>
  	 <option value="1">1</option>
  	 <option value="2">2</opton>
  	 <option value="3">3</option>
  	 <option value="4">4</opton>
  	 <option value="5">5</option>
  	 <option value="6">6</opton>
  	 <option value="7">7</opton>
  	 <option value="8">8</opton>
  	 <option value="9">9</opton>
  	 <option value="10">10</opton>
  	 <option value="11">11</opton>
  	 <option value="12">12</opton>
  	 <option value="13">13</opton>
  	 <option value="14">14</opton>
  	 <option value="15">15</opton>
  	 <option value="16">16</opton>
  	 <option value="17">17</opton>
  	 <option value="18">18</opton>
  	 <option value="19">19</opton>
  	 <option value="20">20</opton>
  	 <option value="21">21</opton>
  	 <option value="22">22</opton>
  	 <option value="23">23</opton>
  	 <option value="24">24</opton>
  	 <option value="25">25</opton>
  	 <option value="26">26</opton>
  	 <option value="27">27</opton>
  	 <option value="28">28</opton>
  	 <option value="29">29</opton>
  	 <option value="30">30</opton>
  	 <option value="31">31</opton>
  	 <option value="32">32</opton>
  	 <option value="33">33</opton>
  	 <option value="34">34</opton>
  	 <option value="35">35</opton>
  	 <option value="36">36</opton>
  	 <option value="37">37</opton>
  	 <option value="38">38</opton>
  </select>
      <input type="hidden" name="subscriptionId" value="location">
      <input type="hidden" name="operation" value="addTourCard">
      <button class="btn" type="submit">Add Tour updates</button>
    </form>

    </div>
  </div>
</div>

<script
    src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="/static/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
