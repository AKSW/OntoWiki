<?php

define('SUBS_LAST_CATCHED', 'lastCatched');
define('SUBS_LAST_CHECKED', 'lastChecked');
define('SUBS_DOCUMENT_MODIFIED', 'lastModified');
define('SUBS_UPDATE_MODUS', 'updateModus');
define('SUBS_UPDATE_INTERVAL', 'updateInterval');
define('SUBS_LAST_STATUS', 'lastStatus');
define('SUBS_STORE_MODEL', 'storeModel');
define('SUBS_QUERY', 'query');


/**
 * Subscription class. Encapsulating the information concerning one subscription.
 * Holding method to update a subscription.
 * Part of Ontowiki CommandLineInterface. 
 *
 * @author     Raphael Doehring <raphael.doehring@googlemail.com>
 * @version    SVN: $Id: Subscription.php 2326 2008-05-26 15:37:49Z norman.heino $
 * @link       http://ontowiki.net/Projects/OntoWiki/CommandLineInterface
 */
class Subscription 
{
	// model attributes			
	private $subscriptionURI;
	private $lastCatchedDate;
	/** interval to check subscription, in minutes */ 
	private $updateInterval;
	private $lastCheckedDate;
	/** timestamp in http-header for document to check */
	private $documentModifiedDate;
	private $storeModelURI;
	private $query;
	private $updateModus;
	private $lastStatus;

	private $model;
	private $database;
	private $isObjectComplete = false;


	/**
	 * Constructing Subscription object through querying information from model.
	 */		
	public function __construct($subscriptionURI, DbModel $model, $database) 
	{
		$this->subscriptionURI = $subscriptionURI;
		$this->model = $model;
		$this->database = $database;
		
		$document_query = "SPARQL SELECT * WHERE {<".$this->subscriptionURI."> ?p ?o}";
		$document_result = simpleQuery($document_query, $this->model);

		foreach($document_result as $key => $value) {
			if($value['?p']->getURI() == SUBS_SCHEMA_NS . SUBS_LAST_CHECKED) {
				$this->lastCheckedDate = $value['?o']->getLabel();
			} else if ($value['?p']->getURI() == SUBS_SCHEMA_NS . SUBS_LAST_CATCHED) {
				$this->lastCatchedDate = $value['?o']->getLabel();
			} else if ($value['?p']->getURI() == SUBS_SCHEMA_NS . SUBS_UPDATE_INTERVAL) {
				if(intval($value['?o']->getLabel()) != 0) {
					$this->updateInterval =	intval($value['?o']->getLabel());
				} // -- if intval returns int different from 0
				else {
					$this->updateInterval = "err";
				} // -- else, intval return 0 -> error
			} else if ($value['?p']->getURI() == SUBS_SCHEMA_NS . SUBS_DOCUMENT_MODIFIED) { 
				$this->documentModifiedDate = $value['?o']->getLabel();
			} else if ($value['?p']->getURI() == SUBS_SCHEMA_NS . SUBS_STORE_MODEL) { 
				$this->storeModelURI = $value['?o']->getLabel();
			} else if ($value['?p']->getURI() == SUBS_SCHEMA_NS . SUBS_QUERY) { 
				$this->query = $value['?o']->getLabel();
			} else if ($value['?p']->getURI() == SUBS_SCHEMA_NS . SUBS_UPDATE_MODUS) {
				$this->updateModus = $value['?o']->getLabel();
			} else if ($value['?p']->getURI() == SUBS_SCHEMA_NS . SUBS_LAST_STATUS) { 
				$this->lastStatus = $value['?o']->getLabel();
			}
		} // -- foreach attribute row of subscription

		$this->isObjectComplete = $this->checkObjectCompletion();
	} // -- function __construct


	/**
	 * Nice output of one subscription. For debugging purposes only.
	 */
	public function __toString() 
	{
		$returnString = SUBS_DEBUG_PREFIX . "subscription: ". $this->subscriptionURI ."\n"; 
		$returnString .= SUBS_DEBUG_PREFIX . "   lastChecked: ". $this->lastCheckedDate ."\n";
		$returnString .= SUBS_DEBUG_PREFIX . "   lastCatched: ". $this->lastCatchedDate ."\n";
		$returnString .= SUBS_DEBUG_PREFIX . "   updateInterval: ". $this->updateInterval ."\n";
		$returnString .= SUBS_DEBUG_PREFIX . "   documentModified: ". $this->documentModifiedDate ."\n";		
		$returnString .= SUBS_DEBUG_PREFIX . "   storeModel: ". $this->storeModelURI ."\n";
		$returnString .= SUBS_DEBUG_PREFIX . "   query: ". $this->query ."\n";
		$returnString .= SUBS_DEBUG_PREFIX . "   updateModus: ". $this->updateModus ."\n";
		$returnString .= SUBS_DEBUG_PREFIX . "   lastStatus: ". $this->lastStatus ."\n";
		
		return $returnString; 
	} // -- function toString
	
	
	/**
	 * Method checks if subscription needs to be updated.
	 */
	public function check() 
	{
		echo_debug(SUBS_DEBUG_PREFIX . "----> " . $this->subscriptionURI . " <---- ");
		if(!$this->isObjectComplete) {
			echo_error(SUBS_DEBUG_PREFIX . "Subcription attributes are not complete. Skipping update.");
			return;
		} // -- if object is not complete
		
		if(time() - ($this->updateInterval * 60) > strtotime($this->lastCheckedDate)) {
			echo_debug(SUBS_DEBUG_PREFIX . 'Check required.');
			$result = $this->file_getContentsIfModified($this->subscriptionURI, 30, $this->lastCatchedDate);
			if($result != FALSE) {
				if($result != -1) {
					if($this->query != "") $result = $this->selectByQuery($result);
					if($this->saveToModel($this->storeModelURI, $result) == false) {
						echo_debug(SUBS_DEBUG_PREFIX . 'Error saving information to new model.');
						return;
					} // -- if saving method returns false
				} // -- if result is not empty (not changed)
				$this->updateTimeStamp(time(), SUBS_LAST_CHECKED);
			}  // -- if result is false
		} // -- if sub has not been checked for at least updateIntervall -> check
		else {
			echo_debug(SUBS_DEBUG_PREFIX . 'Subscription was last checked within update interval.');
		}
	} // -- function check
	
	
	/** 
	 * Opens connection to a specified host, and checks the Last-Modified information
	 * against the given timestamp. Gets the document if timestamps differ.
	 * 
	 * @param: $url - URL to check
	 * @param: $timeout - timeout for connection
	 * @param: $timeStamp - the timestamp to check against the LastModified information
	 * 		of the document.  
	 * @return: String, containing the new document content, if it was modified.  
	 */
	private function file_getContentsIfModified($url, $timeout, $timeStamp) 
	{
		$ch = curl_init();
		$options = array(
			    CURLOPT_URL			   => $url,
		        CURLOPT_RETURNTRANSFER => TRUE,     
		        CURLOPT_HEADER         => TRUE,    
		        CURLOPT_FOLLOWLOCATION => TRUE,     
		        CURLOPT_USERAGENT      => NAME." ".VERSION, 
		        CURLOPT_AUTOREFERER    => TRUE,     
		        CURLOPT_CONNECTTIMEOUT => $timeout,      
		        CURLOPT_TIMEOUT        => 30,      
		        CURLOPT_MAXREDIRS      => 20,      
		        CURLOPT_VERBOSE		   => FALSE,
		        CURLOPT_NOBODY 		   => TRUE
			    );
		curl_setopt_array( $ch, $options );
		
		$result = curl_exec($ch);
		$err = curl_errno( $ch );
		if($err != 0) {
			echo_error(SUBS_DEBUG_PREFIX . 'cURL error number: '  . $err . "\n");
			$errmsg  = curl_error( $ch );
			echo_error(SUBS_DEBUG_PREFIX . 'cURL errmsg: ' . $errmsg . "\n");
			$this->updateLastStatus($err . " " . $errmsg);
			return false;
		} 

		$header  = curl_getinfo( $ch );
		if($header['http_code'] != 200) {
			echo_error(SUBS_DEBUG_PREFIX . "Error: http status code was " . $header['http_code']);
			$this->updateLastStatus("http-status-code: " . $header['http_code']);
			return false; 
		} // -- if http code not 200
		curl_close( $ch );
		
		// get whole document if timestamps differ
		preg_match('^Last-Modified:\s(.*)^', $result, $lastChanged);
		
		if (count($lastChanged) == 0 
			|| strtotime($lastChanged['1']) != strtotime($this->documentModifiedDate)) {

			if(count($lastChanged) == 0) {
				echo_debug(SUBS_DEBUG_PREFIX . 'No timestamp found for the document. Getting Contents.');
			}
			if (strtotime($lastChanged['1']) != strtotime($this->documentModifiedDate)) {
				echo_debug(SUBS_DEBUG_PREFIX . 'Document was modified since last catch. Getting Contents.');
			}
			
			$ch = curl_init();
			$options[CURLOPT_NOBODY] = FALSE;
			$options[CURLOPT_RETURNTRANSFER] = TRUE;
			$options[CURLOPT_HEADER] = FALSE;

			curl_setopt_array($ch, $options);
			$result = curl_exec($ch);
			
			$err = curl_errno( $ch );
			if($err != 0) {
				echo_error(SUBS_DEBUG_PREFIX . 'cURL error number: '  . $err . "\n");
				$errmsg  = curl_error( $ch );
				echo_error(SUBS_DEBUG_PREFIX . 'cURL errmsg: ' . $errmsg . "\n");
				$this->updateLastStatus($err . " " . $errmsg);
				return false;
			} 
		
			$header  = curl_getinfo( $ch );
			if($header['http_code'] != 200) {
				echo_error(SUBS_DEBUG_PREFIX . 'Error fetching ' . $url . ' http_code: ' . $header['http_code']);
				$this->updateLastStatus("http-status-code: " . $header['http_code']);				
				return false; 
			} // -- if http code not 200
			curl_close( $ch );

			$this->updateTimeStamp(time(), SUBS_LAST_CATCHED);
			$this->updateTimeStamp(strtotime($lastChanged['1']), SUBS_DOCUMENT_MODIFIED);
			
			return $result;
		} // -- if saved and current LastModified information differ get the document
		else {
			echo_debug(SUBS_DEBUG_PREFIX . 'Document has not been modified since last catch.');
			return -1;
		} // -- else, document was not modified 
	} // -- function file_getContentsIfModified


	/**
	 * This method takes care of updating a timeStamp of the 
	 * current subscription. Type is either lastCatched, lastChecked or 
	 * documentModified.
	 */
	private function updateTimeStamp($newTimeStamp, $timeStampType) 
	{
		if($timeStampType != SUBS_LAST_CATCHED
			&& $timeStampType != SUBS_LAST_CHECKED
			&& $timeStampType != SUBS_DOCUMENT_MODIFIED) {
				echo_error(SUBS_DEBUG_PREFIX . 'unknown timeStamp type ' . $timeStampType);
		} // -- if timeStampType is not one of the predifiend
		
		$subject = new Resource($this->subscriptionURI);
		$predicate = new Resource(SUBS_SCHEMA_NS . $timeStampType);
		$date_query = 'SPARQL SELECT ?s ?p ?o WHERE {<'.$this->subscriptionURI.'> ' .
				'<' . SUBS_SCHEMA_NS . $timeStampType .'> ?o}';
		$date_result = simpleQuery($date_query, $this->model);
		
		foreach ($date_result as $date_statement) {
			$oldStatement = new Statement($subject, $predicate, $date_statement['?o']);
			$this->model->remove($oldStatement);			
		} // -- foreach statement

		$object = new Literal(strftime('%Y-%m-%dT%H:%M:%S.000', $newTimeStamp), 
								NULL, 'http://www.w3.org/2001/XMLSchema#dateTime');
		$newStatement = new Statement($subject, $predicate, $object); 
		$this->model->add($newStatement);
	} // -- function updateTimeStamp
	
	
	/**
	 * This method writes the last status into the model. 
	 */
	private function updateLastStatus ($statusText) 
	{
		$subject = new Resource($this->subscriptionURI);
		$predicate = new Resource(SUBS_SCHEMA_NS . SUBS_LAST_STATUS);
		$lastStatusQuery = 'SPARQL SELECT ?s ?p ?o WHERE {<'.$this->subscriptionURI.'> ' .
				'<' . SUBS_SCHEMA_NS . SUBS_LAST_STATUS .'> ?o}';
		$lastStatusResult = simpleQuery($lastStatusQuery, $this->model);
		
		foreach ($lastStatusResult as $lastStatusStatement) {
			$oldStatement = new Statement($subject, $predicate, $lastStatusStatement['?o']);
			$this->model->remove($oldStatement);			
		} // -- foreach statement

		$object = new Literal($statusText , NULL, 'http://www.w3.org/2001/XMLSchema#string');
		$newStatement = new Statement($subject, $predicate, $object); 
		$this->model->add($newStatement);
	} // -- function updateLastStatus
	

	/**
	 * This method saves the downloaded content to a certain model.
	 */	
	private function saveToModel($modelURI, $data) 
	{
		$baseURI = NULL;
		// delete old model
		$model = $this->database->getModel ($modelURI);
		if($this->updateModus == "add" && !$model) {
			echo_debug(SUBS_DEBUG_PREFIX . "Model $modelURI does't exist.");
		} // -- modus add and model doesn't exist

		if($this->updateModus == "new" && $model) {
			echo_debug(SUBS_DEBUG_PREFIX . "update Modus is \"new\". Trying to delete current model: " . $modelURI);

			empty_cache($this->database, $modelURI, FALSE) or die();
			empty_namespaces($this->database, $modelURI, FALSE) or die();
			empty_popularity($this->database, $modelURI, FALSE) or die();
			empty_ratings($this->database, $modelURI, FALSE) or die();
			$model->delete();
			$model = null;
			echo_debug(SUBS_DEBUG_PREFIX . "$modelURI: Old model deleted");
		} // -- if updateModus is new - delete old model
		
		if (!$model){
			echo_debug(SUBS_DEBUG_PREFIX . "Creating new Model $modelURI .");
			$model = $this->database->getNewModel ($modelURI, $baseURI);
			if (!$model) {
				echo_error (SUBS_DEBUG_PREFIX . "Error creating model: $modelURI");
				return false;
			} else {
				echo_debug(SUBS_DEBUG_PREFIX . "$modelURI: Model created");
			}	
		} // -- model doesn't exist

		// create tmp file for model input
		$tmpname = tempnam ("/tmp", "owcli-") . ".rdf";
		$tmphandle = fopen ($tmpname, "w") ;
		fwrite($tmphandle, $data);
		fclose($tmphandle);
		$file = $tmpname;
	
		$memModel = new MemModel();
		$memModel->load($file);
		$model->addModel($memModel);
		$memModelcount = $memModel->findCount (NULL, NULL, NULL);

		if (isset($tmpname)) {
			unlink($tmpname);
			unset($tmpname);
		}
		echo_debug(SUBS_DEBUG_PREFIX . " $modelURI: Add $memModelcount Statements.");
		return TRUE;
	} // -- function saveToModel
	
	
	/**
	 * If there is a query given in the schema, select part of the information
	 * to store according to the query.
	 */
	private function selectByQuery ($data) 
	{
		echo_debug(SUBS_DEBUG_PREFIX . "selectByQuery called.");
		// create tmp file for model input
		$tmpname = tempnam ("/tmp", "owcli-") . ".rdf";
		$tmphandle = fopen ($tmpname, "w") ;
		fwrite($tmphandle, $data);
		fclose($tmphandle);
		$file = $tmpname;

		$memModel = new MemModel();
		$memModel->load($file);
		
		if (isset($tmpname)) {
			unlink($tmpname);
			unset($tmpname);
		}
		
		$queryResult = $memModel->sparqlQuery($this->query);
//		var_dump($queryResult);
//		echo ($queryResult->writeRdftoString()); 
		echo_debug(SUBS_DEBUG_PREFIX . "selectByQuery ended.");
		return $queryResult->writeRdftoString();
	} // -- function selectByQuery
	
	
	/**
	 * This method checks, if all mandatory attributes are set. Sets local
	 * $isObjectComplete to false if not.
	 */
	private function checkObjectCompletion() 
	{
		if($this->updateModus != "new" && $this->updateModus != "add") {
			echo_debug(SUBS_DEBUG_PREFIX . " updateModus is neither add nor new.");
			return FALSE;
		}
		if(! is_int($this->updateInterval)) {
			echo_debug(SUBS_DEBUG_PREFIX . " updateInterval is not an integer.");
			return FALSE;
		}
		if(! is_string($this->storeModelURI)) {
			echo_debug(SUBS_DEBUG_PREFIX . " storeModel is not a string or missing.");
			return FALSE;
		}
		return TRUE;
	} // -- function checkObjectCompletion
} // -- class Subscription
 
?>
