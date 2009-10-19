<?php

require_once 'OntoWiki/Controller/Component.php';
require_once 'Zend/XmlRpc/Server.php';
require_once 'Zend/XmlRpc/Server/Exception.php';

class PingbackController extends OntoWiki_Controller_Component
{
	public function pingAction()
	 {
		// error logging
		$handle = fopen('ping.log', "w");
		fwrite($handle, "");     
		fclose($handle);
		$this->errorlog("Server init"); 
		
		// create server
		$server = new Zend_XmlRpc_Server();
		$server->setClass($this, 'pingback');
		// init server
		echo $server->handle();
	}

	public function addPingback($from, $to, $label = null, $type = null) {
		$store = Erfurt_App::getInstance()->getStore();
		$model = $store->getModel( $this->_privateConfig->pingback_model );
		//$pingbackUri = $model->createResourceUri('Pingback_');
		$model->addStatement(
			//$pingbackUri,
			$from,
			"http://rdfs.org/sioc/ns#reply_of",
			array('value' => $to, 'type' => 'uri')
		);
		if($type){
			$model->addStatement(
				$from,
				"http://rdfs.org/sioc/ns#Item",
				array('value' => $type, 'type' => 'literal')
			);
		}
		if($label){
			$model->addStatement(
				$from,
				EF_RDFS_LABEL,
				array('value' => $label, 'type' => 'literal')
			);
		}
	}

    public function checkPingback($from, $to){
        $store = Erfurt_App::getInstance()->getStore();
		$model = $store->getModel( $this->_privateConfig->pingback_model );

		// check if it already was pinged
		// get all resource comments
		$commentSparql = 'SELECT DISTINCT ?pingback
			WHERE {
				?pingback <http://rdfs.org/sioc/ns#reply_of> <' . $to . '>.
			}
			LIMIT 1';

		// var_dump($commentSparql);
		require_once 'Erfurt/Sparql/SimpleQuery.php';
		$query = Erfurt_Sparql_SimpleQuery::initWithString($commentSparql);

		$result = $model->sparqlQuery($query);
		$this->errorlog(print_r($result,true));
        if(!$result) return false;
		if($result[0]['pingback']==$from) {
			return true;
		}
        return false;
    }

	public function errorlog($msg) {
		$filename = 'ping.log';
		$handle = fopen($filename, "a");
		$writestring = "Pingback: ".$msg."\n";
		fwrite($handle, $writestring);     
		fclose($handle);
	}
	
	/**
	* This is a sample function
	*
	* @param string $source String
	* @param string $target String
	* @return integer
	*/
	public function ping($source, $target) {	
		$this->errorlog("pingback called");

		$sourceURI = $source;
		$targetURI = $target;

		// check if source URL exists
		$file = fopen($sourceURI, "r");
		if(!$file) {
			// This page they claim they're linking to me from, it doesn't exist!  That can't be right.
			$error = "Source URI (".$sourceURI.") does not exist (target URI was ".$targetURI.")";
			$this->errorlog($error);
			throw new Zend_XmlRpc_Server_Exception($error, 0x0010);
			return 0x0010;
		}
		
		// check if header is allowed (optional)
		/*$headerOk = 0;
		for($count = 0; $count < count($http_response_header); $count++) {
			if(strstr($http_response_header[$count],"Content-Type: text/")) {
				$headerOk = 1;
			}
		}
		if($headerOk == 0) {
			// Check for MIME-type disabled in case it excludes valid stuff by accident (eg. application/xhtml+xml).
			// return new xmlrpcresp(new xmlrpcval(0, 0x0011));
		}*/
		
		// check if source contains a link to target
		$fileContents = fread($file,100000);//$filestats['size']);
		if(!strstr($fileContents, preg_replace("/&(amp;)?/i","&amp;",$targetURI))) {
			// Doesn't contain a valid link
			if(!strstr($fileContents, str_replace("&amp;","&",$targetURI))) {
				// Doesn't contain an invalid link either!
				$error = "Source URI (".$sourceURI.") does not contain a link to the target URI (".$targetURI.")";
				$this->errorlog($error);
				//new xmlrpcresp(0, 0x0011, $error);
				throw new Zend_XmlRpc_Server_Exception($error, 0x0011);
				return 0x0011;
			}	
		}
		
		// get page title (optional)
		if(preg_match("/<title>([^<]*)<\/title>/i",$fileContents,$matches)) {
			$title = $matches[1];
		} else {
			$title = null;
		}
		
		// get page title (optional)
		if(preg_match("/WordPress/i",$fileContents,$matches)) {
			$type = "WordPress";
		} else {
			$type = null;
		}
		fclose($file);
		
		// check if target URL exists
		$rq = curl_init();
		curl_setopt($rq, CURLOPT_URL, $sourceURI);
		curl_setopt($rq,CURLOPT_FOLLOWLOCATION,true); 
		$res = curl_exec($rq);
		curl_close($rq);
		
		//$file = fopen($targetURI, "r");
		if(!$res) {
			// A blog entry was specified that doesn't exist!
			$error = "Specified target URI (".$targetURI.") does not exist (source URI was ".$sourceURI.")";
			$this->errorlog($error);
			//new xmlrpcresp(0, 0x0020, $error);
			//throw new Zend_XmlRpc_Server_Exception($error, 0x0020);
			throw new Zend_XmlRpc_Server_Exception($error, 0x0020);
			return 0x0020;
		}
		//fclose($file);
		
		// check if pingback already exists
        if( $this->checkPingback($sourceURI, $targetURI) ){
            // They've already pingbacked/trackbacked this post once from this source URI.  No more!!!
			$error = "The pingback from ".$sourceURI." to ".$targetURI." has already been registered";
			$this->errorlog($error);
            throw new Zend_XmlRpc_Server_Exception($error, 0x0030);
        }
		
		// add pingback to DB
		$this->addPingback($sourceURI, $targetURI, $title, $type);
   
		// pingback done
		$error = "Thanks! Pingback from ".$sourceURI." to ".$targetURI." registered";
		$this->errorlog($error);

		throw new Zend_XmlRpc_Server_Exception($error);
		return 0;
	}
}
