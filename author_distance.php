<?php
/**
 * Calls the Amazon API to create maps and directions points.
 * For individual author events within two weeks from now
 *
 * LICENSE: This source file is for Harpercollins use only:
 *
 * @author Stephen La Pierre
 * @package    Author Tours Glass Application
 */

include ("mysql.class.php");
require_once 'distance_base.php';

/**
 * The map and content for tour information.
 *
 */
class author_distance extends distance_base {
	/**
	 * @string the speakable text.
	 */
	protected $speakable;
	/**
	 * @string the Author to search for tours.
	 */
	protected $searchAuthor;
	/**
	 * @return the $searchAuthor
	 */
	public function getSearchAuthor() {
		return $this->searchAuthor;
	}

	/**
	 * @param field_type $searchAuthor
	 */
	public function setSearchAuthor($searchAuthor) {
		$this->searchAuthor = $searchAuthor;
	}

	/**
	 * @return the $speakable
	 */
	public function getSpeakable() {
		return $this->speakable;
	}

	/**
	 * @param field_type $speakable
	 */
	public function setSpeakable($speakable) {
		$this->speakable = $speakable;
	}

	/**
	 * __construct
	 * @param double $lat
	 * @param double $longt
	 */
	function __construct($lat, $longt) {
		parent::__construct($lat, $longt);
	}
		/**
		 * Check enough events present or add non-events if required. 
		 */
		public function getEvents() {
		$db = new MySQL(true, "ereader", "184.72.94.122", "ereader", "testmybooks");
//		$db = new MySQL(true, "ereader", "localhost", "ereader", "testmybooks");
//		$db = new MySql();
		$sql = "SELECT DISTINCT * from event_location WHERE name = '" . $this->getSearchAuthor() . "' ORDER BY days_until ASC LIMIT 12";
		$this->earray = $db->QueryArray($sql, MYSQL_ASSOC);
		if ($db->Error()) {
			error_log("We have an DB error: " . $db->Error());
		}
		if (sizeof($this->earray) == 0 || $this->earray == FALSE) {
				$this->setError("No Locations found");
				$this->setInError(TRUE);
				$this->speakable = 'I am very sorry, but I did not find any tours for ' . $this->getSearchAuthor() . ' in the near future';
				return;
		}
	}

}
?>