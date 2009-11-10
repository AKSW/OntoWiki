<?php
require_once 'OntoWiki/Plugin.php';

class PingbackPlugin extends OntoWiki_Plugin
{
	protected $debug = true;

    public function onAfterInitController(){
		$url = preg_replace('/extensions.plugins.pingback.*/', '', $this->_pluginUrlBase);
		header("X-Pingback: ".$url."index.php/pingback/ping");
	}
	
	public function onAddStatement(){
		$this->errorlog();
        $this->errorlog("graphUri: ".$event->graphUri); 
        $this->errorlog($event->statement, true);
		
		$this->errorlog("onAddStatement called");
		
		// check if linked data is enabled
		$owApp = OntoWiki::getInstance(); 
		$pluginManager = $owApp->erfurt->getPluginManager(false);
		if(!$pluginManager->isPluginEnabled('linkeddata')){
			$this->errorlog("plugin linkeddata disabled, pingbacks are not allowed");  
			return;
		}
		
		$this->checkAndPingback($event->statement['subject'], $event->statement['predicate'], $event->statement['object']);
	}
	
	public function onAddMultipleStatements($event){
		$this->errorlog();
		$this->errorlog("graphUri: ".$event->graphUri); 
        $this->errorlog($event->statements, true);
		
		$this->errorlog("onAddMultipleStatements called");
		
		// check if linked data is enabled
		$owApp = OntoWiki::getInstance(); 
		$pluginManager = $owApp->erfurt->getPluginManager(false);
		if(!$pluginManager->isPluginEnabled('linkeddata')){
			$this->errorlog("plugin linkeddata disabled, pingbacks are not allowed");  
			return;
		}
		
		// parse SOP array
		foreach ($event->statements as $subject => $predicatesArray) {
            foreach ($predicatesArray as $predicate => $objectsArray) {
                foreach ($objectsArray as $object) { 
					$this->checkAndPingback($subject, $predicate, $object);
				}
			}
		}
	}
	
	protected function errorlog($msg, $array = false) {
		if(!$this->debug) return;
	
		$filename = 'ping_plug.log';
		if($msg == null && !$array){
			$handle = fopen($filename, "w+");
			fwrite($handle, "");
			fclose($handle);
			return;
		}
		$handle = fopen($filename, "a+");
		if(!$array){
			$writestring = "Pingback_plugin: ".$msg."\n";
		}else{
			$writestring = "Pingback_plugin: ".print_r($msg,true)."\n";
		}
		fwrite($handle, $writestring);
		fclose($handle);
		//$query = "INSERT INTO bloglogs (date, message) VALUES (NOW(), '".addslashes($msg)."')";
		//@mysql_query($query);
	}
	
	protected function checkAndPingback($subject, $predicate, $object){
		$predicatesAllowed = $this->_privateConfig->predicates->toArray();
		$this->errorlog("predicates: ".print_r($predicatesAllowed,true));
		
		$url = preg_replace('/extensions.plugins.pingback.*/', '', $this->_pluginUrlBase);
		//$this->errorlog($url);
		//$this->errorlog($subject." - ".$predicate." - ".$object);
		
		// check if subject is in OW namespace
		if(strstr($subject, $url) === null){
			$this->errorlog("subject is not in OW namespace");
			return;
		}
		
		// if predicate isn't allowed, end
		if(!in_array($predicate, $predicatesAllowed)){
			$this->errorlog("predicates is not in allowed list");
			return;
		}
		
		// object check
		//if ($object['type'] !== 'uri') { 
		if(strstr($object['value'], "http://") === null){
			$this->errorlog("object is not link");	
			return;
		}
		
		// check if object is in OW namespace
		if(strstr($object['value'], $url) === null){
			$this->errorlog("object is in OW namespace");
			return;
		}else{
			$this->errorlog("Ping to: ".$object['value']);
			$this->send_pingback($subject, $object['value']);
		}
	}
	
	protected function send_pingback($sourceURI, $targetURI){
		$this->errorlog($sourceURI." = ".$targetURI);
		
		$sourceURI = $this->get_final_url($sourceURI);
		$this->errorlog("redir to: ".$sourceURI);
	
        $target = file_get_contents($targetURI);
        // HTTP headers are now in $http_response_header, see docs of HTTP wrapper

        foreach ($http_response_header as $header)
                if (preg_match("/^X-Pingback:\s*([^\s]+)\s*$/", $header, $matches))
                        $serverURI = $matches[1];

        if (!$serverURI) {
                $target = html_entity_decode($target);
                if (preg_match("/<link rel=\"pingback\" href=\"([^\"]+)\" ?\/?>/", $target, $matches))
                        $serverURI = $matches[1];
        }
		
		$this->errorlog($serverURI);
		
        if (!$serverURI)
                return;

        $xml = '<?xml version="1.0"?><methodCall><methodName>pingback.ping</methodName><params>'.
                '<param><value><string>'.$sourceURI.'</string></value></param>'.
                '<param><value><string>'.$targetURI.'</string></value></param>'.
                '</params></methodCall>';

        $rq = curl_init();
        curl_setopt($rq, CURLOPT_URL, $serverURI);
        curl_setopt($rq, CURLOPT_POST, 1);
        curl_setopt($rq, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($rq, CURLOPT_FOLLOWLOCATION,false); 
        $res = curl_exec($rq);
        curl_close($rq);
		
		//$this->errorlog("result: ".$res);
		echo $res;
	}
	
	
	protected function get_final_url( $url, $timeout = 5 ){
		$url = str_replace( "&amp;", "&amp;", urldecode(trim($url)) );
 
		$cookie = tempnam ("/tmp", "CURLCOOKIE");
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1" );
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
		curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		$content = curl_exec( $ch );
		$response = curl_getinfo( $ch );
		curl_close ( $ch );
 
		if ($response['http_code'] == 301 || $response['http_code'] == 302){
			ini_set("user_agent", "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1");
			$headers = get_headers($response['url']);
 
			$location = "";
			foreach( $headers as $value ){
				if ( substr( strtolower($value), 0, 9 ) == "location:" )
					return get_final_url( trim( substr( $value, 9, strlen($value) ) ) );
			}
		}
 
		if (preg_match("/window\.location\.replace\('(.*)'\)/i", $content, $value) ||
		preg_match("/window\.location\=\"(.*)\"/i", $content, $value)){
			return get_final_url ( $value[1] );
		}else{
			return $response['url'];
		}
	}
}

