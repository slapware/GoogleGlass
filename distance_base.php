<?php
/**
 * Calls the Amazon API to create maps and directions points.
 *
 *
 * LICENSE: This source file is for Harpercollins use only:
 *
 * @author Stephen La Pierre
 * @package    Author Tours Glass Application
 */


/**
 * The map and content for tour information.
 *
 */
abstract class distance_base {
	/**
	 * @var double origin latitude.
	 */
	protected $origin_latitude;
	/**
	 * @var double origin longitude.
	 */
	protected $origin_longitude;
	/**
	 * @var double destination latitude.
	 */
	protected $dest_latitude;
	/**
	 * @var double destination longitude.
	 */
	protected $dest_longitude;
	/**
	 * @var string base Amazon URI.
	 */
	protected $base_distancematrix;
	/**
	 * @var string completed URI.
	 */
	protected $callurl;
	/**
	 * @var string Amazon json data.
	 */
	protected $json_data;
	/**
	 * @var array for latitude and longitude.
	 */
	protected $distarray = array();
	/**
	 * @var event array.
	 */
	protected $earray = array();
	/**
	 * @var string error message.
	 */
	protected $error;
	/**
	 * @var string glass user State location.
	 */
	protected $queryState;
	/**
	 * @var bool error flag.
	 */
	protected $inError;
	/**
	 * @var string bundle cover html.
	 */
	protected $html;
	/**
	 * @var string info page html.
	 */
	protected $htmlPage;
	//
	protected $author;
	protected $title;
	protected $venue;
	protected $location;
	protected $isbn;
	/**
	 * @var bool has map flag.
	 */
	protected $isMap;
	protected $mapHtml;
	/**
	 * @var string voice directions text.
	 */
	protected $dir_voice;

	/**
	 * @return the $dir_voice
	 */
	public function getDir_voice() {
		return $this->dir_voice;
	}

	/**
	 * The voice directions
	 * @param string 
	 */
	public function setDir_voice($dir_voice) {
		$this->dir_voice = $dir_voice;
	}

	/**
	 * the isMap flag
	 * @return bool
	 */
	public function getIsMap() {
		return $this->isMap;
	}

	/**
	 * the map Html
	 * @return string
	 */
	public function getMapHtml() {
		return $this->mapHtml;
	}

	/**
	 * @param field_type $isMap
	 */
	public function setIsMap($isMap) {
		$this->isMap = $isMap;
	}

	/**
	 * @param field_type $mapHtml
	 */
	public function setMapHtml($mapHtml) {
		$this->mapHtml = $mapHtml;
	}

	/**
	 * @return the $origin_latitude
	 */
	public function getOrigin_latitude() {
		return $this->origin_latitude;
	}

	/**
	 * @return the $origin_longitude
	 */
	public function getOrigin_longitude() {
		return $this->origin_longitude;
	}

	/**
	 * @param field_type $origin_latitude
	 */
	public function setOrigin_latitude($origin_latitude) {
		$this->origin_latitude = $origin_latitude;
	}

	/**
	 * @param field_type $origin_longitude
	 */
	public function setOrigin_longitude($origin_longitude) {
		$this->origin_longitude = $origin_longitude;
	}

	/**
	 * @return the $dest_latitude
	 */
	public function getDest_latitude() {
		return $this->dest_latitude;
	}

	/**
	 * @return the $dest_longitude
	 */
	public function getDest_longitude() {
		return $this->dest_longitude;
	}

	/**
	 * @param field_type $origin_latitude
	 */
	public function setDest_latitude($origin_latitude) {
		$this->dest_latitude = $origin_latitude;
	}

	/**
	 * @param field_type $origin_longitude
	 */
	public function setDest_longitude($origin_longitude) {
		$this->dest_longitude = $origin_longitude;
	}
	
	/**
	 * @return the $author
	 */
	public function getAuthor() {
		return $this->author;
	}

	/**
	 * @return the $title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @return the $venue
	 */
	public function getVenue() {
		return $this->venue;
	}

	/**
	 * the address
	 * @return string
	 */
	public function getLocation() {
		return $this->location;
	}

	/**
	 * @param string $author
	 */
	public function setAuthor($author) {
		$this->author = $author;
	}

	/**
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * @param string $venue
	 */
	public function setVenue($venue) {
		$this->venue = $venue;
	}

	/**
	 * The address
	 * @param string $location
	 */
	public function setLocation($location) {
		$this->location = $location;
	}

	/**
	 * @return the $htmlPage
	 */
	public function getHtmlPage() {
		return $this->htmlPage;
	}

	/**
	 * @param string $html
	 */
	public function setHtmlMain($html) {
		$this->html = $html;
	}

	/**
	 * @return the $htmlMain
	 */
	public function getHtmlMain() {
		return $this->html;
	}

	/**
	 * @param field_type $htmlPage
	 */
	public function setHtmlPage($htmlPage) {
		$this->htmlPage = $htmlPage;
	}

	/**
	 * @return the $inError
	 */
	public function getInError() {
		return $this->inError;
	}

	/**
	 * @param bool error flag
	 */
	public function setInError($inError) {
		$this->inError = $inError;
	}

	/**
	 * @return the $queryState
	 */
	public function getQueryState() {
		return $this->queryState;
	}

	/** 
	 * Set the state to query in results.
	 * @param string $queryState
	 */
	public function setQueryState($queryState) {
		$this->queryState = $queryState;
	}

	/**
	 * @return the $error
	 */
	public function getError() {
		return $this->error;
	}

	/**
	 * @param field_type $error
	 */
	public function setError($error) {
		$this->error = $error;
	}

	public function getIsbn() {
		return $this->isbn;
	}
	
	public function setIsbn($isbn) {
		$this->isbn = $isbn;
		return $this;
	}
	/**
	 * __construct
	 * @param double $lat
	 * @param double $longt
	 */
	function __construct($lat, $longt) {
		$this->setOrigin_latitude($lat);
		$this->setOrigin_longitude($longt);
		$this->base_distancematrix = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=" . $lat ."," . $longt . "&destinations=";
		$this->setInError(FALSE);
		$this->setIsMap(FALSE);
		$this->getCurrent();
	}
	/**
	 * __destruct
	 */
	function __destruct() {
	}
	/**
	 * Main run event.
	 * Handles main events.
	 */
	public function run() {
		$this->getEvents();
		if ($this->getInError() == FALSE)
		{
			$this->buildURL();
			$this->decodeData();
			$this->addDistance();			
		}
	}
	/**
	 * Call Amazon API to get data.
	 */
	public function buildURL() {
		foreach($this->earray as $value)
		{
			$this->callurl = $this->callurl . '"' . urlencode($value["street"]) . '"|';
		}
		$this->callurl = substr($this->callurl, 0, -1);
		$this->callurl = $this->callurl . "&units=imperial&sensor=false";
		$url = $this->base_distancematrix . $this->callurl;
		$this->json_data = file_get_contents($url);
	}
	/**
	 * Decode the data from Amazon.
	 */
	public function decodeData() {
		$data = json_decode($this->json_data, true);
		foreach($data['rows'][0]['elements'] as $d)
		{
			$entry = $d['distance']['text'];
			array_push($this->distarray, $entry);
		}
		if (sizeof($this->distarray) == 0) {
			$this->setError("No Locations found");
			$this->setInError(TRUE);
		}
	}
	/**
	 * Add distance to array for this glass user.
	 */
	public function addDistance() {
		$counter = 0;
		foreach($this->earray as &$index)
		{
			$keys = array_keys($this->distarray);
			$dist = $this->distarray[$keys[$counter]];
    		$index['distance'] = $dist;
    		$counter++;
		}
	}
		/**
		 * Get the current state glass reported to be located in.  
		 */
		public function getCurrent() {
		$endpoint="http://maps.googleapis.com/maps/api/geocode/json?latlng=".trim($this->origin_latitude).",".trim($this->origin_longitude)."&sensor=false";
		$raw=@file_get_contents($endpoint);
		$json_data=json_decode($raw, true);
		foreach($json_data['results'][0]['address_components'] as $d)
		{
			$entry = $d['types'][0];
			if (strpos($entry, 'administrative_area_level_1') !== FALSE) {
				$state = $d['short_name'];
				if (strlen($state)>4) {
					$state = $this->convert_state_to_abbreviation($state);
				}
				$this->setQueryState($state);
				break;
			}
		}
	}
	/**
	 * Check enough events present or add non-events if required. 
	 */
	public abstract function getEvents();
	/**
	 * add Map if it's an event type.
	 * 
	 * @param double $dlat
	 * @param double $dlong
	 * @param double $dist
	 * @param int $counter 
	 */
	public function addMap($dlat, $dlong, $dist, $counter) {
		$geobase = 'http://maps.googleapis.com/maps/api/directions/json?origin=';
		$geocall = $geobase . $this->getOrigin_latitude() . ',' . $this->getOrigin_longitude() . '&destination=' . $dlat . ',' . $dlong . '&sensor=false';
		$json_route = file_get_contents($geocall);
		$decoded_route = json_decode($json_route, true);
		$route_string = "";
		foreach($decoded_route['routes'][0]['legs'][0]['steps'] as $d)
		{
			$slat = $d['start_location']['lat'];
			$slong = $d['start_location']['lng'];
			$elat = $d['end_location']['lat'];
			$elong = $d['end_location']['lng'];
			$route_string = $route_string . $slat . "," . $slong;
			if (strlen($elat)>7) {
				$route_string = $route_string . "," . $elat . "," . $elong . ",";
			}
			// generate voice direction text
			$travel = " in " . $d['distance']['text'];
			$travel = str_replace('mi', "miles. ", $travel);
			$travel = str_replace('ft', "feet. ", $travel);
			$dir = $d['html_instructions'];
			$dir = str_replace('<b>', " ", $dir);
			$dir = str_replace('</b>', " ", $dir);
			$dir = str_replace('<div style="font-size:0.9em">', " ", $dir);
			$dir = str_replace("</div>", "  ", $dir);
			$dir = str_replace("</div><br>", " ", $dir);
			$dir = rtrim($dir, '.') . '. ';
			$dir = $dir . "  .";
			
			$tmp_voice = $this->getDir_voice();
			$this->setDir_voice($tmp_voice . $travel . $dir);
		}
//		$route = substr($route_string, 0, -1);
		$route = rtrim($route_string, ",");;
//		error_log ( $route . "\n", 3, "/tmp/direction.log" );
		
		$map = '<article>  <figure>    <img src="glass://map?w=240&h=360&marker=1;' . $this->getOrigin_latitude() . ',' . $this->getOrigin_longitude() . '&marker=0;' . $dlat . ',' . $dlong . '&polyline=;';
		$map = $map . $route . '" height="360" width="240">  </figure>  <section>    <div class="text-auto-size">      <p class="yellow">' . $dist . 'les</p><p>' . $this->earray[$counter]["street"] . '</p>';
		$map = $map . '</div>  </section><footer>  <img src="http://diner.harpercollins.com/images/ETG_sm1.png" height="46" width="100"> </footer></article>';
		$this->setMapHtml($map);
	}
	/**
	 * return the count of cards available.
	 */
		public function countEntries() {
		return sizeof($this->earray);
	}
	/**
	 * Create card for display and set if map page is created for $counter entry in array
	 * @param int $counter  
	 */
	public function getHtml($counter) {
		// start with clean slate
		$this->setHtmlMain("");
		$this->setHtmlPage("");
		$this->setMapHtml("");
		$this->setIsMap(FALSE);
		$this->setInError(FALSE);
		// Fill with new values
		if (sizeof($this->earray) == 0 || $this->earray == FALSE) {
			$this->setHtmlMain('<article>  <figure>    <img src="http://diner.harpercollins.com/images/missing1.jpg" height="360" width="240"></figure>  <section>    <h1 class="text-small"><em class="yellow">Sorry</em></h1>    <p class="text-x-small">I did not find any results</p>    <hr>    <p class="text-x-small">    Please try again<br>     Its me not you     </p>  </section></article>');
			$this->setInError(TRUE);
			return;
		}
		$base1 = '<article>  <figure>    <img src="http://www.harpercollins.com/harperimages/isbn/medium_large/IMGFLD/ISBN.jpg" height="360" width="240">  </figure>  <section>    <h1 class="text-small"><em class="yellow">AUTHOR</em></h1>    <p class="text-x-small">    TITLE</p>    <hr>    <p class="text-x-small">    DAYS days until<br>      TIME    </p>  </section><footer>  <img src="http://diner.harpercollins.com/images/ETG_sm1.png" height="46" width="100"></footer>  </article>';
		$base2 = '<article>  <figure>    <img src="http://www.harpercollins.com/harperimages/isbn/medium_large/IMGFLD/ISBN.jpg" height="360" width="240">  </figure>  <section>    <h1 class="text-small"><em class="blue">VENUE</em></h1>    <p class="text-x-small">ADDRS</p>DIST    <hr>    <p class="text-x-small">      </p>  </section><footer>    <p class="red">TYEVENT</p>  </footer></article>';
		
		$folder = $this->earray[intval($counter)]["isbn"];
		$isbn = $folder;
		$this->setIsbn($isbn);
		$last = $folder[strlen($folder)-1];
		$otype = $this->earray[$counter]["description"];
		$arr = explode(' - ', $otype);
		$type = $arr[1];
		$html1 = str_replace ( 'IMGFLD', $last, $base1 );
		$html2 = str_replace ( 'IMGFLD', $last, $base2 );
		
		$html1 = str_replace ( 'ISBN', $isbn, $html1 );
		$html2 = str_replace ( 'ISBN', $isbn, $html2 );
		
		$html1 = str_replace ( 'AUTHOR', $this->earray[$counter]["name"], $html1 );
		$this->setAuthor($this->earray[$counter]["name"]);
		$html1 = str_replace ( 'TITLE', $this->earray[$counter]["title"], $html1 );
		$this->setTitle($this->earray[$counter]["title"]);
		$html1 = str_replace ( 'DAYS', $this->earray[$counter]["days_until"], $html1 );
		$html1 = str_replace ( 'TIME', $this->earray[$counter]["start_time"], $html1 );
		
		$html2 = str_replace ( 'VENUE', $this->earray[$counter]["location"], $html2 );
		$this->setVenue($this->earray[$counter]["location"]);
		$html2 = str_replace ( 'ADDRS', $this->earray[$counter]["street"], $html2 );
		$this->setLocation($this->earray[$counter]["street"]);
		//
		if($type == "EVENT" OR $type == "BOOKSTORE") {
	    	$html2 = str_replace ( 'DIST', $this->earray[$counter]["distance"], $html2 );
	    	$this->addMap($this->earray[$counter]["latitude"], $this->earray[$counter]["longitude"], $this->earray[$counter]["distance"], $counter);
			$this->setIsMap(TRUE);
			// test
			$this->setDest_latitude($this->earray[$counter]["latitude"]);
			$this->setDest_longitude($this->earray[$counter]["longitude"]);
		}
		if($type == "Publication") {
	    	$html2 = str_replace ( 'DIST', '<p class="text-x-small">Magazine / paper<p class="text-x-small">', $html2 );
		}
		if($type == "TV") {
	    	$html2 = str_replace ( 'DIST', '<p class="text-x-small">Check local channel<p class="text-x-small">', $html2 );
		}
		if($type == "RADIO") {
	    	$html2 = str_replace ( 'DIST', '<p class="text-x-small">Check local channel</p>', $html2 );
		}
		if($type == "ONLINE") {
	    	$html2 = str_replace ( 'DIST', '<p class="text-x-small">On your browser</p>', $html2 );
		}
		//
		$html2 = str_replace ( 'TYEVENT', $type, $html2 );
//		$html2 = str_replace ( 'CORD', "(" . $this->earray[$counter]["latitude"] . "," . $this->earray[$counter]["longitude"] . ")", $html2 );
		/*******************************************************************************
		$html1 is the frst card, $html2 is second card. If map mapHtml fetched.
		*******************************************************************************/
		$this->setHtmlMain($html1);
		$this->setHtmlPage($html2);
	}
	/**
	 * Convert long state name to abbreviation
	 * 
	 * @param string $state_name
	 * @return string
	 */
	public function convert_state_to_abbreviation($state_name) {
		switch ($state_name) {
			case "Alabama":
				return "AL";
				break;
			case "Alaska":
				return "AK";
				break;
			case "Arizona":
				return "AZ";
				break;
			case "Arkansas":
				return "AR";
				break;
			case "California":
				return "CA";
				break;
			case "Colorado":
				return "CO";
				break;
			case "Connecticut":
				return "CT";
				break;
			case "Delaware":
				return "DE";
				break;
			case "Florida":
				return "FL";
				break;
			case "Georgia":
				return "GA";
				break;
			case "Hawaii":
				return "HI";
				break;
			case "Idaho":
				return "ID";
				break;
			case "Illinois":
				return "IL";
				break;
			case "Indiana":
				return "IN";
				break;
			case "Iowa":
				return "IA";
				break;
			case "Kansas":
				return "KS";
				break;
			case "Kentucky":
				return "KY";
				break;
			case "Louisana":
				return "LA";
				break;
			case "Maine":
				return "ME";
				break;
			case "Maryland":
				return "MD";
				break;
			case "Massachusetts":
				return "MA";
				break;
			case "Michigan":
				return "MI";
				break;
			case "Minnesota":
				return "MN";
				break;
			case "Mississippi":
				return "MS";
				break;
			case "Missouri":
				return "MO";
				break;
			case "Montana":
				return "MT";
				break;
			case "Nebraska":
				return "NE";
				break;
			case "Nevada":
				return "NV";
				break;
			case "New Hampshire":
				return "NH";
				break;
			case "New Jersey":
				return "NJ";
				break;
			case "New Mexico":
				return "NM";
				break;
			case "New York":
				return "NY";
				break;
			case "North Carolina":
				return "NC";
				break;
			case "North Dakota":
				return "ND";
				break;
			case "Ohio":
				return "OH";
				break;
			case "Oklahoma":
				return "OK";
				break;
			case "Oregon":
				return "OR";
				break;
			case "Pennsylvania":
				return "PA";
				break;
			case "Rhode Island":
				return "RI";
				break;
			case "South Carolina":
				return "SC";
				break;
			case "South Dakota":
				return "SD";
				break;
			case "Tennessee":
				return "TN";
				break;
			case "Texas":
				return "TX";
				break;
			case "Utah":
				return "UT";
				break;
			case "Vermont":
				return "VT";
				break;
			case "Virginia":
				return "VA";
				break;
			case "Washington":
				return "WA";
				break;
			case "Washington D.C.":
				return "DC";
				break;
			case "West Virginia":
				return "WV";
				break;
			case "Wisconsin":
				return "WI";
				break;
			case "Wyoming":
				return "WY";
				break;
			case "Alberta":
				return "AB";
				break;
			case "British Columbia":
				return "BC";
				break;
			case "Manitoba":
				return "MB";
				break;
			case "New Brunswick":
				return "NB";
				break;
			case "Newfoundland & Labrador":
				return "NL";
				break;
			case "Northwest Territories":
				return "NT";
				break;
			case "Nova Scotia":
				return "NS";
				break;
			case "Nunavut":
				return "NU";
				break;
			case "Ontario":
				return "ON";
				break;
			case "Prince Edward Island":
				return "PE";
				break;
			case "Quebec":
				return "QC";
				break;
			case "Saskatchewan":
				return "SK";
				break;
			case "Yukon Territory":
				return "YT";
				break;
			default:
				return $state_name;
		}
	}

	}

?>