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

include ("mysql.class.php");
require_once 'distance_base.php';

/**
 * The map and content for tour information.
 *
 */
class distance extends distance_base {
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
		$sql = "SELECT * from event_location WHERE state = '" . $this->getQueryState() . "' ORDER BY days_until ASC LIMIT 20";
		$this->earray = $db->QueryArray($sql, MYSQL_ASSOC);
		if ($db->Error()) {
			error_log("We have an DB error: " . $db->Error());
		}
		if (sizeof($this->earray) == 0 || $this->earray == FALSE) {
			$sql = "SELECT * from event_location WHERE description NOT LIKE 'EVENT%' ORDER BY days_until ASC LIMIT 20";
			$this->earray = $db->QueryArray($sql, MYSQL_ASSOC);
		}
		if (sizeof($this->earray) < 6) {
			$tmp = array();
			$fetch = array();
			$tmp = $this->earray;
			$sql = "SELECT * from event_location WHERE description NOT LIKE 'EVENT%' ORDER BY days_until ASC LIMIT 10";
			$fetch = $db->QueryArray($sql, MYSQL_ASSOC);
			$this->earray = array_merge($tmp, $fetch);
			unset($fetch);
			unset($tmp);
		}
		if (sizeof($this->earray) == 0) {
		$this->setError("No Locations found");
		$this->setInError(TRUE);
		}
	}

}
?>