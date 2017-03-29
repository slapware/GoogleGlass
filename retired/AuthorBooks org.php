<?php

/**
 * Calls the catalog API and captures data to object members.
 * Searches by authorname for books by author.
 * 
 * LICENSE: This source file is for Harpercollins use only:
 *
 * @author Stephen La Pierre
 * @package    Author Tours Glass Application
*/

/**
 * The book objects of the desired author
 *
 */
class AuthorCatalog {
	/**
	 * @var string base API call
	 */
	private $base_catalog;
	/**
	 * @var string base call with ISBN added to complete
	 */
	private $callurl;
	/**
	 * @var string author name to search on
	 */
	private $searchTitle;
	/**
	 * @var string book cover image URI
	 */
	private $image;
	/**
	 * @var string book ISBN
	 */
	private $isbn;
	/**
	 * @var string the book format
	 */
	private $format;
	/**
	 * @var string the book on sale date
	 */
	private $onsaledate;
	/**
	 * @var bool muti result flag
	 */
	private $isMulti;
	/**
	 * @var string speakable text for glass
	 */
	private $speakable;
	private $error;
	private $rawxml;
	private $title;
	private $author;
	/**
	 * @var string glass card html
	 */
	private $html;
	/**
	 * @var bool data not found flag
	 */
	private $notFound;
	
	private static $instance = NULL;
	
	/**
	 * @return the $isMulti
	 */
	public function getIsMulti() {
		return $this->isMulti;
	}

	/**
	 * @param field_type $isMulti
	 */
	public function setIsMulti($isMulti) {
		$this->isMulti = $isMulti;
	}

	/**
	 * @param field_type $html
	 */
	public function setHtml($html) {
		$this->html = $html;
	}

	/**
	 * @return the $html
	 */
	public function getHtml() {
		return $this->html;
	}
	
	/**
	 * @return the $rawxml
	 */
	public function getRawxml() {
		return $this->rawxml;
	}

	/**
	 * @return the $title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @return the $author
	 */
	public function getAuthor() {
		return $this->author;
	}

	/**
	 * @param field_type $rawxml
	 */
	public function setRawxml($rawxml) {
		$this->rawxml = $rawxml;
	}

	/**
	 * @param field_type $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * @param field_type $author
	 */
	public function setAuthor($author) {
		$this->author = $author;
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

	/**
	 * @return the $base_catalog
	 */
	public function getBase_catalog() {
		return $this->base_catalog;
	}

	/**
	 * @return the $callurl
	 */
	public function getCallurl() {
		return $this->callurl;
	}

	/**
	 * @return the $searchTitle
	 */
	public function getSearchTitle() {
		return $this->searchTitle;
	}

	/**
	 * @return the $image
	 */
	public function getImage() {
		return $this->image;
	}

	/**
	 * @return the $isbn
	 */
	public function getIsbn() {
		return $this->isbn;
	}

	/**
	 * @return the book $format
	 */
	public function getFormat() {
		return $this->format;
	}

	/**
	 * @return the book $onsaledate
	 */
	public function getOnsaledate() {
		return $this->onsaledate;
	}

	/**
	 * @return the $speakable
	 */
	public function getSpeakable() {
		if (strlen($this->speakable) > 5)
		{
			return $this->speakable;
		}
		else 
		{
			return $this->title . ". by " . $this->author;
		}
	}

	/**
	 * @param field_type $base_catalog
	 */
	public function setBase_catalog($base_catalog) {
		$this->base_catalog = $base_catalog;
	}

	/**
	 * @param field_type $callurl
	 */
	public function setCallurl($callurl) {
		$this->callurl = $callurl;
	}

	/**
	 * @param field_type $searchTitle
	 */
	public function setSearchTitle($searchTitle) {
		$this->searchTitle = $searchTitle;
	}

	/**
	 * @param field_type $image
	 */
	public function setImage($image) {
		$this->image = $image;
	}

	/**
	 * @param field_type $isbn
	 */
	public function setIsbn($isbn) {
		$this->isbn = $isbn;
	}

	/**
	 * @param field_type $format
	 */
	public function setFormat($format) {
		$this->format = $format;
	}

	/**
	 * @param field_type $onsaledate
	 */
	public function setOnsaledate($onsaledate) {
		$this->onsaledate = $onsaledate;
	}

	/**
	 * @param field_type $speakable
	 */
	public function setSpeakable($speakable) {
		$this->speakable = $speakable;
	}

	public function getNotFound() {
		return $this->notFound;
	}
	
	public function setNotFound($notFound) {
		$this->notFound = $notFound;
		return $this;
	}
	
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new self();
		}
		return self::$instance;
	}
	/**
	 * __construct()
	 */
	function __construct() {
		$this->setBase_catalog('http://openbook.harpercollins.com/api/v3/hcapim?apiname=catalog&format=XML&authorname=');
		$this->speakable = "Not Found";
		$this->setNotFound(FALSE);
		$this->setIsMulti(FALSE);
	}
	/**
	 * __destruct
     */
	function __destruct() {
		/*******************************************************************************
		We have a lot of luggage, so lets give it up.
		*******************************************************************************/
		unset($data);
		unset($xml);
		unset($cleandata);
	}
	/**
	 * Call the api to perform the search and bind results to member variables.
	 * no params passed.
     */
	function run() {
		if (strlen($this->searchTitle) > 1) {
			$this->callurl = $this->getBase_catalog() . urlencode($this->searchTitle);
		}
		else
		{
			$this->error = 'no data to search';
			$current = "Error in run" . $this->getError() . "\n";
			error_log($current);
		}
		/*******************************************************************************
		Here we make the call to the API to get detail information on this book.
		*******************************************************************************/
        $ch = curl_init();
        $timeout = 40;
        curl_setopt($ch, CURLOPT_URL, $this->callurl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        if ($data == FALSE) {
        	echo "Error on GET";
			$current = "Error on GET" . "\n";
			error_log($current);
			$this->setNotFound(TRUE);
        }
        /*******************************************************************************
        clean the nasty stuff we find from some text data.
        *******************************************************************************/
        $cleandata = filter_var($data, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW);
        $this->setRawxml($cleandata);
        curl_close($ch);
        if (strlen($data) > 32) {
			$this->setNotFound(FALSE);
        } // if (strlen($data) > 64
        else {
			$this->setNotFound(TRUE);
        	$this->error = "short data returned " . strlen($data) . "<BR>\n" . $data;
        }
	} // run
        
	function makeSeries($number) {
        $xml = new DOMDocument;
        // We don't want to bother with white spaces
        $xml->preserveWhiteSpace = false;
        $xml->loadXML($this->getRawxml());
        $this->speakable = "";
        try {
        	$destinations = $xml->getElementsByTagName("Product_Group_SEO_Copy")->item($number);
        	foreach($destinations->childNodes as $child) {
        		if ($child->nodeType == XML_CDATA_SECTION_NODE) {
        			$this->speakable = filter_var($child->textContent, FILTER_SANITIZE_STRING);
        			break;
        		}
        	} // foreach
        	$this->onsaledate = $xml->getElementsByTagName("On_Sale_Date")->item($number)->nodeValue;
        	$this->onsaledate = str_replace( "00:00:00.000", "", $this->onsaledate);
        	$this->isbn = $xml->getElementsByTagName("ISBN")->item($number)->nodeValue;
        	$this->title = $xml->getElementsByTagName("Title")->item($number)->nodeValue;
        	$this->format = $xml->getElementsByTagName("Format")->item($number)->nodeValue;
        	$this->image = $xml->getElementsByTagName("CoverImageURL_MediumLarge")->item($number)->nodeValue;
        	$this->author = $xml->getElementsByTagName("Author1")->item($number)->nodeValue;
        	if (strlen($this->speakable) < 5) {
				$this->speakable = $this->author . ". " . $this->title . ". ";
        	}
        } // try
        catch (Exception $e) {
        	$this->error = $e->getMessage();
        	$current = "Exception in run" . $this->getError() . "\n";
        	error_log($current);
        }
        
	} // makeSeries
        /*******************************************************************************
         Create card for display and set if map page is created for $counter entry in DB
        *******************************************************************************/
        function getHtmlPage() {
        	if($this->getNotFound() == TRUE)
        	{
	        	$this->setHtml('<article>  <figure>    <img src="http://diner.harpercollins.com/images/missing1.jpg" height="360" width="240"></figure>  <section>    <h1 class="text-small"><em class="yellow">Sorry</em></h1>    <p class="text-x-small">I Did not understand</p>    <hr>    <p class="text-x-small">    Please try again<br>     Its me not you     </p>  </section></article>');
	        	$this->speakable = 'I am very sorry, but I did not understand the name of the author you mentioned.';
	        	return;
            }
        	$base1 = '<article>  <figure>    <img src="http://www.harpercollins.com/harperimages/isbn/medium_large/IMGFLD/ISBN.jpg" height="360" width="240">  </figure>  <section>    <h1 class="text-small"><em class="yellow">AUTHOR</em></h1>    <p class="text-x-small">    TITLE</p>    <hr>    <p class="text-x-small">    DAYS<br>      TIME    </p>  </section><footer>    <p class="red">TYEVENT</p>  </footer></article>';
        
        	$last = substr($this->isbn, -1);;
       		$html1 = str_replace ( 'IMGFLD', $last, $base1 );
        
        	$html1 = str_replace ( 'ISBN', $this->isbn, $html1 );
        
        	$html1 = str_replace ( 'AUTHOR', $this->author, $html1 );
        	$html1 = str_replace ( 'TITLE', $this->title, $html1 );
        	$html1 = str_replace ( 'DAYS', $this->isbn, $html1 );
        	$html1 = str_replace ( 'TIME', $this->onsaledate, $html1 );        
        	//
        //
        $html1 = str_replace ( 'TYEVENT', $this->format, $html1 );
        //		$html2 = str_replace ( 'CORD', "(" . $this->earray[$counter]["latitude"] . "," . $this->earray[$counter]["longitude"] . ")", $html2 );
        /*******************************************************************************
        $html1 is the frst card, $html2 is second card. If map mapHtml fetched.
        *******************************************************************************/
        $this->setHtml($html1);
        }

        /**
         * @return the number of products found
         */
        function countEntries() {
        	$doc = new DOMDocument;
        	// We don't want to bother with white spaces
        	$doc->preserveWhiteSpace = false;
        	$doc->loadXML($this->getRawxml());
        	
        	$prods = $doc->getElementsByTagName("Product_Group");
        	$count = $prods->length;
        	return $count;
        } // countEntries
        
        /**
         * @param string $str
         * @return string
         */
        function utf8_urldecode($str) {
        	$str = preg_replace("/%u([0-9a-f]{3,4})/i","&#x\\1;",urldecode($str));
        	return html_entity_decode($str,null,'UTF-8');;
        } // utf8_urldecode
        
} // class Catalog

?>