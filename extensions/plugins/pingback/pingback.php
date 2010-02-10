<?php
require_once 'OntoWiki/Plugin.php';

class PingbackPlugin extends OntoWiki_Plugin
{
    public function beforeLinkedDataRedirect($event)
    {
        if ($event->response === null) {
            return;
        }
        $response = $event->response;
        
        $owApp = OntoWiki::getInstance(); 
        $url = $owApp->config->urlBase . 'pingback/ping/';
        $response->setHeader('X-Pingback', $url, true);
	}
	
	public function onAddStatement()
	{
        $this->_logInfo('Graph URI - '. $event->graphUri); 
        $this->_logInfo($event->statement);
		
		if (!$this->_check()) {
		    return;
		}
		
		
		$this->_checkAndPingback($event->statement['subject'], $event->statement['predicate'], $event->statement['object']);
	}
	
	public function onAddMultipleStatements($event){
		
		$this->_logInfo('Graph URI - '. $event->graphUri); 
        $this->_logInfo($event->statements);
		
	    if (!$this->_check()) {
		    return;
		}
		
		// Parse SPO array.
		foreach ($event->statements as $subject => $predicatesArray) {
            foreach ($predicatesArray as $predicate => $objectsArray) {
                foreach ($objectsArray as $object) { 
					$this->_checkAndPingback($subject, $predicate, $object);
				}
			}
		}
	}
	
	protected function _check()
	{
	    // Check, whether linked data plugin is enabled.
    	$owApp = OntoWiki::getInstance(); 
    	$pluginManager = $owApp->erfurt->getPluginManager(false);
    	if (!$pluginManager->isPluginEnabled('linkeddata')) {
    		$this->_logInfo('Linked Data plugin disabled, Pingbacks are not allowed.');  
    		return false;
    	}
    	
    	return true;
	}
	
	protected function _checkAndPingback($subject, $predicate, $object)
	{
	    $owApp   = OntoWiki::getInstance(); 
	    $base    = $owApp->config->urlBase;
	    $baseLen = strlen($base);
	    
	    // Subject needs to be a linked data resource.
	    if (substr($subject, 0, $baseLen) !== $base) {
	        $this->_logInfo('Subject is not in OntoWiki namespace.');
	        return false;
	    }
	    
		// If predicate is not in confiugured allowed predicates, return.
		$predicatesAllowed = $this->_privateConfig->predicates->toArray();
		$this->_logInfo('Allowed predicates -' . print_r($predicatesAllowed, true));
		if (!in_array($predicate, $predicatesAllowed)) {
			$this->_logInfo('Predicate is not in allowed list.');
			return false;
		}
		
		// Object needs to be a dereferencable URI!
		if ((substr($object['value'], 0, 7) !== 'http://') && (substr($object['value'], 0, 8) !== 'https://')) {
			$this->_logInfo('Object is not a dereferencable URI.');	
			return false;
		}
		
		// Check, whether object is in OW namespace... If yes, return, since we do not pingback URIs in the environment.
		if (substr($object['value'], 0, $baseLen) === $base) {
		    	$this->_logInfo('Object is in OW namespace.');
    			return false;
		} else {
			$this->_logInfo('Ping to: ' . $object['value']);
			$this->_sendPingback($subject, $object['value'], $predicate);
		}
	}
	
	protected function _discoverPingbackServer($targetUri)
	{
	    // 1. Retrieve HTTP-Header and check for X-Pingback
	    $headers = get_headers($targetUri, 1);
	    if (is_array($headers)) {
	        if (strstr('200', $headers[0])) {
	            if (isset($headers['X-Pingback'])) {
    	            return $headers['X-Pingback'];
    	        }
	        } else if (strstr('303', $headers[0])) {
	            if (isset($headers['Location'])) {
	                return $this->_discoverPingbackServer($headers['Location']);
	            }
	        }
	    } else {
	        return null;
	    }
	    
	    // 2. Check for (X)HTML Link element, if target has content type text/html
	    if (isset($headers['Content-Type']) && ($headers['Content-Type'] === 'text/html')) {
	        // TODO Fetch only the first X bytes...???
	        require_once 'Zend/Http/Client.php';
            $client = new Zend_Http_Client($uri, array(
                'maxredirects'  => 10,
                'timeout'       => 30
            ));

            $client->setHeaders('Accept', 'text/html');
            $response = $client->request();
            if ($response->getStatus() === '200') {
                $htmlDoc = new DOMDocument();
                $result = @$htmlDoc->loadHtml($response->getBody());
                $relElements = $htmlDoc->getElementsByTagName('link');

                foreach ($relElements as $relElem) {
                    $rel  = $relElem->getAttribute('rel');
                    if (strtolower($rel) === 'pingback') {
                        return $relElem->getAttribute('href');
                    }
                }
            }
	    }
	    
	    // 3. Check RDF/XML ?!
	    // TODO
	    return null;
	    
	}
	
	protected function _logError($msg) 
	{
	    $owApp = OntoWiki::getInstance(); 
	    $logger = $owApp->logger;
	    
	    if (is_array($msg)) {
	        $logger->debug('Pingback Plugin Error: ' . print_r($msg, true));
	    } else {
	        $logger->debug('Pingback Plugin Error: ' . $msg);
	    }
	}
	
	protected function _logInfo($msg) 
	{
	    $owApp = OntoWiki::getInstance(); 
	    $logger = $owApp->logger;
	    
	    if (is_array($msg)) {
	        $logger->debug('Pingback Plugin Info: ' . print_r($msg, true));
	    } else {
	        $logger->debug('Pingback Plugin Info: ' . $msg);
	    }
	}
	
	protected function _sendPingback($sourceUri, $targetUri, $relationUri = null) 
	{
		$pingbackServiceUrl = $this->_discoverPingbackServer($targetUri);
		if ($pingbackServiceUrl === null) {
		    return false;
		}
		
        $xml = '<?xml version="1.0"?><methodCall><methodName>pingback.ping</methodName><params>'.
                '<param><value><string>' . $sourceUri . '</string></value></param>'.
                '<param><value><string>' . $targetUri . '</string></value></param>';
                
        if ($relationUri !== null) {
            $xml .= '<param><value><string>' . $relationUri . '</string></value></param>';
        }
                
        $xml .= '</params></methodCall>';

        // TODO without curl? with zend?
        $rq = curl_init();
        curl_setopt($rq, CURLOPT_URL, $pingbackServiceUrl);
        curl_setopt($rq, CURLOPT_POST, 1);
        curl_setopt($rq, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($rq, CURLOPT_FOLLOWLOCATION, false); 
        $res = curl_exec($rq);
        curl_close($rq);
		$this->_logInfo('Result - ' . $res);
		
		return true;
	}
}
