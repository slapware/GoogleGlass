<?php
/*  Add the voice search commands, set to renew every 7 days
 *  So they do not fall off the timeline.
 */
require_once 'config.php';
require_once 'mirror-client.php';
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_MirrorService.php';
require_once 'util.php';

/* Lets keep it local */
if ($_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
	// restrict
	die("I don't talk to strangers");
}

$client = get_google_api_client();
verify_credentials(get_credentials('110350450990179447197'));
$client->setAccessToken(get_credentials('110350450990179447197'));

$mirror_service = new Google_MirrorService($client);
	$hod = date('G');
//	$credentials = devote_list_credentials();
	$jd=cal_to_jd(CAL_GREGORIAN,date("m"),date("d"),date("Y"));
	$dow = (jddayofweek($jd,1));
	
	$credentials = tour_list_credentials_byhour($hod, $dow);
if (count($credentials) > 10) {
	$message = "Found " . count($credentials) . " users. Aborting to save your quota.";
}
else
{
	foreach ($credentials as $credential) {
		$user_specific_client = get_google_api_client();
		$user_specific_client->setAccessToken($credential['credentials']);
		$user_specific_mirror_service = new Google_MirrorService($user_specific_client);
		/*******************************************************************************
		 RENEW INSERT SEARCH BOOK ALL USERS CARD
		*******************************************************************************/
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
		// This is how you identify it on the notification ping
		array_push($menu_items, $custom_menu_item);
		/* un-comment to add Delete menu item */
		// 		$del_item = new Google_MenuItem();
		// 		$del_item->setAction("DELETE");
		// 		array_push($menu_items, $del_item);
		
		$new_timeline_item->setMenuItems($menu_items);
		$user_specific_mirror_service = new Google_MirrorService($user_specific_client);
		insert_timeline_item($user_specific_mirror_service, $new_timeline_item, null, null);
		/*******************************************************************************
		 RENEW INSERT SEARCH AUTHOR BOOKS BY TITLE ALL USERS
		*******************************************************************************/
		$new_timeline_item2 = new Google_TimelineItem();
		$new_timeline_item2->setText("Search for Author books by author name.");
    	$html = '<article>   <section>    <p class="text-auto-size"><strong class="blue">Harper Collins</strong></p>    <hr>  Search for books by author name  </section><footer>  </footer>  </article>';
    	$new_timeline_item2->setHtml($html);
		$new_timeline_item2->setSpeakableText("Search for books by author name. For example, Wiley Cash.");
		$new_timeline_item2->setMenuItems($menu_items);
		$user_specific_mirror_service = new Google_MirrorService($user_specific_client);
		insert_timeline_item($user_specific_mirror_service, $new_timeline_item2, null, null);
		/*******************************************************************************
		 RENEW SEARCH TOURS BY AUTHOR ALL USERS
		*******************************************************************************/
		$new_timeline_item3 = new Google_TimelineItem();
		$new_timeline_item3->setText("Search for tours by author.");
    	$html = '<article>   <section>    <p class="text-auto-size"><strong class="blue">Harper Collins</strong></p>  <hr> Search for tours by author name. </section><footer>  </footer>  </article>';
    	$new_timeline_item3->setHtml($html);
		$new_timeline_item3->setSpeakableText("Search for tours by Author Name. For example, Sarah Palin.");
		$new_timeline_item3->setMenuItems($menu_items);
		$user_specific_mirror_service = new Google_MirrorService($user_specific_client);
		insert_timeline_item($user_specific_mirror_service, $new_timeline_item3, null, null);
	} // foreach ($credentials as $credential)
  } // else if (count($credentials) > 10)
		
?>