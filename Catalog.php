<?php

/**
 * Calls the catalog API and captures data to object members.
 * 
 * 
 * LICENSE: This source file is for Harpercollins use only:
 *
 * @author Stephen La Pierre
 * @package    Author Tours Glass Application
*/
require_once 'Catalog_base.php';

/**
 * The book object of the desired book title
 *
 */
class Catalog extends Catalog_base {
	/**
	 *  __construct()
	 */
	function __construct() {
		parent::__construct();
		$this->setBase_catalog('http://openbook.harpercollins.com/api/v3/hcapim?apiname=catalog&format=XML&');
	}

        /**
         * @return the number of products found
         */
        function countEntries() {
        	$doc = new DOMDocument;
        	// We don't want to bother with white spaces
        	$doc->preserveWhiteSpace = false;
        	$doc->loadXML($this->getRawxml());
        	
        	$prods = $doc->getElementsByTagName("Products");
        	$count = $prods->length;
        	return $count;
        } // countEntries
        
        
} // class Catalog

?>