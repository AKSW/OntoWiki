<?php

require_once 'OntoWiki/Controller/Component.php';
require_once 'Zend/XmlRpc/Server.php';
require_once 'Zend/XmlRpc/Server/Exception.php';

class PingbackController extends OntoWiki_Controller_Component
{
    protected $_targetGraph = null;
    protected $_sourceRdf = null;
    
	public function pingAction()
    {
        $this->_logInfo('Pingback Server Init.'); 
		
		// Create XML RPC Server
		$server = new Zend_XmlRpc_Server();
		$server->setClass($this, 'pingback');
		
		// Let the server handle the RPC calls.
		$response = $this->getResponse();
        $response->setBody($server->handle());
        $response->sendResponse();
		exit;
    }
    
    /**
	 * @param string $sourceUri The source URI
	 * @param string $targetUri The target URI
	 *
	 * @return integer An integer (fault) code
	 */
	public function ping($sourceUri, $targetUri) 
	{	
		$this->_logInfo('Method ping was called.');

        // Is $targetUri a valid linked data resource in this namespace?
        if (!$this->_checkTargetExists($targetUri)) {
            $this->_logError('0x0021');
            return 0x0021;
        }

        $config = $this->_privateConfig;
        
        $versioning = Erfurt_App::getInstance()->getVersioning();
        $versioning->setUserUri($sourceUri);
        $versioning->setLimit(1000000);
        
        $foundPingbackTriples = array();

		// 1. Try to dereference the source URI as RDF/XML
        $client = new Zend_Http_Client($sourceUri, array(
            'maxredirects'  => 10,
            'timeout'       => 30
        ));
        $client->setHeaders('Accept', 'application/rdf+xml');
		try {
		    $response = $client->request();
		} catch (Exception $e) {
		    $this->_logError($e->getMessage());
		    return 0x0000;
		}
		if ($response->getStatus() === 200) {
	        $data = $response->getBody();
            $result = $this->_getPingbackTriplesFromRdfXmlString($data, $sourceUri, $targetUri);
            if (is_array($result)) {
                $foundPingbackTriples = $result;
            }
	    }
	    
	    // 2. If nothing was found, try to use as RDFa service
	    if (((boolean)$config->rdfa->enabled) && (count($foundPingbackTriples) === 0)) {
	        $service = $config->rdfa->service . urlencode($sourceUri);
	        $client = new Zend_Http_Client($service, array(
                'maxredirects'  => 10,
                'timeout'       => 30
            ));
            
            try {
    		    $response = $client->request();
    		} catch (Exception $e) {
    		    $this->_logError($e->getMessage());
    		    return 0x0000;
    		}
    		if ($response->getStatus() === 200) {
		        $data = $response->getBody();
                $result = $this->_getPingbackTriplesFromRdfXmlString($data, $sourceUri, $targetUri);
                if ($result) {
                    $foundPingbackTriples = $result;
                }
		    }
	    }
	    
	    $versioning->startAction(array(
            'type' => '9000',
            'modeluri' => $this->_targetGraph,
            'resource' => $sourceUri
        ));
        
	    // 3. If still nothing was found, try to find a link in the html
		if (count($foundPingbackTriples) === 0) {
		    $client = new Zend_Http_Client($sourceUri, array(
                'maxredirects'  => 10,
                'timeout'       => 30
            ));
            
            try {
    		    $response = $client->request();
    		} catch (Exception $e) {
    		    $this->_logError($e->getMessage());
    		    $versioning->endAction();
    		    return 0x0000;
    		}
    		if ($response->getStatus() === 200) {
    		    $htmlDoc = new DOMDocument();
                $result = @$htmlDoc->loadHtml($response->getBody());
                $aElements = $htmlDoc->getElementsByTagName('a');

                foreach ($aElements as $aElem) {
                    $a  = $aElem->getAttribute('href');
                    if (strtolower($a) === $targetUri) {
                        $foundPingbackTriples[] = array(
                            's' => $sourceUri,
                            'p' => $config->generic_relation,
                            'o' => $targetUri
                        );
                        break;
                    }
                }
		    } else {
		        $this->_logError('0x0010');
		        $versioning->endAction();
                return 0x0010;
		    }
		}
		
		// 4. If still nothing was found, the sourceUri does not contain any link to targetUri
		if (count($foundPingbackTriples) === 0) {
            // Remove all existing pingback triples from that sourceUri.
            $removed = false;
            $history = $versioning->getHistoryForUser($sourceUri);
            foreach ($history as $hItem) {
                if ($hItem['resource'] === $sourceUri) {
                    $details = $versioning->getDetailsForAction($hItem['id']);
                    if (count($details) === 0) {
                        continue;
                    }
                    $payload = unserialize($payloadResult[0]['statement_hash']);
                    $contained = false;
                    if (!$contained && ($this->_targetGraph !== null)) {
                        // Remove it...
                        $store = Erfurt_App::getInstance()->getStore();
                        
                        $s = $sourceUri; 
                        $p = null;
                        $o = null;
                        foreach($payload[$sourceUri] as $pArray) {
                            foreach ($pArray as $p1 => $oArray) {
                                $p = $p1;
                                $o = $oArray[0];
                                break;
                            }
                        }
                        
                        
                		$store->deleteMatchingStatement($this->_targetGraph, $s, $p, $o, array('use_ac' => false));
                		$removed = true;
                    }
                }
            }
            
            if (!$removed) {
                $this->_logError('0x0011');
                $versioning->endAction();
                return 0x0011;
            } else {
                $this->_logInfo('All existing Pingbacks removed.');
                $versioning->endAction();
                return;
            }
            
            
        }
		
	    // 6. Iterate through pingback triples candidates and add those, wo are not already registered.
		$added = false;
		foreach ($foundPingbackTriples as $triple) {
		    if (!$this->_pingbackExists($triple['s'], $triple['p'], $triple['o'])) {
		        $this->_addPingback($triple['s'], $triple['p'], $triple['o']);
		        $added = true;
		    }
		}
        
        $removed = false;
        // Remove all existing pingbacks from that source uri, that were not found this time.
        $history = $versioning->getHistoryForUser($sourceUri);
        foreach ($history as $hItem) {
            if ($hItem['resource'] === $sourceUri) {
                $details = $versioning->getDetailsForAction($hItem['id']);
                if (count($details) === 0) {
                    continue;
                }
                $payload = unserialize($payloadResult[0]['statement_hash']);
                $contained = false;
                foreach ($foundPingbackTriples as $triple) {
                    if (isset($payload[$triple['s']])) {
                        $pArray = $payload[$triple['s']];
                        if (isset($pArray[$triple['p']])) {
                            $oArray = $pArray[$triple['p']];
                            foreach ($oArray as $oSpec) {
                                if (($oSpec['type'] === 'uri') && ($oSpec['value'] === $triple['o'])) {
                                    $contained = true;
                                    break;
                                }
                            }
                        }
                    }
                }
                if (!$contained && ($this->_targetGraph !== null)) {
                    // Remove it...
                    $store = Erfurt_App::getInstance()->getStore();
            		$s = $sourceUri; 
                    $p = null;
                    $o = null;
                    foreach($payload[$sourceUri] as $pArray) {
                        foreach ($pArray as $p1 => $oArray) {
                            $p = $p1;
                            $o = $oArray[0];
                            break;
                        }
                    }
                    
            		$store->deleteMatchingStatement($this->_targetGraph, $s, $p, $o, array('use_ac' => false));
            		$removed = true;
                }
            }
        }
        
        if (!$added && !$removed) {
            $this->_logError('0x0030');
            $versioning->endAction();
            return 0x0030;
        }
		
		$this->_logInfo('Pingback registered.');
		$versioning->endAction();
	}
	
	protected function _addPingback($s, $p, $o) 
	{
	    if ($this->_targetGraph === null) {
	        return false;
	    }
	    
		$store = Erfurt_App::getInstance()->getStore();
		
		$store->addStatement(
		    $this->_targetGraph,
			$s,
		    $p,
			array('value' => $o, 'type' => 'uri'),
			false
		);
		
		if ($this->_sourceRdf !== null) {
		    foreach ($this->_sourceRdf as $prop => $oArray) {
		        $titleProps = $this->_privateConfig->title_properties->toArray();
		        if (in_array($prop, $titleProps)) {
		            $store->addStatement(
            		    $this->_targetGraph,
            			$s,
            		    $prop,
            			$oArray[0],
            			false
            		);
            		break; // only one title
		        }
		    }
		}
		
		return true;
	}
	
	protected function _checkTargetExists($targetUri)
	{
	    if ($this->_targetGraph == null) {
	        $event = new Erfurt_Event('onNeedsGraphForLinkedDataUri');
	        $event->uri = $targetUri;
	        
	        $graph = $event->trigger();
	        if ($graph) {
	            $this->_targetGraph = $graph;
	            // If we get a target graph from linked data plugin, we no that the target uri exists, sinc
	            // getGraphsUsingResource ist used by store.
	            return true;
	        } else {
	            return false;
	        }
	    }
	}
	
	protected function _determineInverseProperty($propertyUri)
	{
	    $client = new Zend_Http_Client($propertyUri, array(
            'maxredirects'  => 10,
            'timeout'       => 30
        ));
        $client->setHeaders('Accept', 'application/rdf+xml');
		try {
		    $response = $client->request();
		} catch (Exception $e) {
		    return null;
		}
		if ($response->getStatus() === 200) {
	        $data = $response->getBody();
	        
	        $parser = Erfurt_Syntax_RdfParser::rdfParserWithFormat('rdfxml');
    	    try {
    	        $result = $parser->parse($data, Erfurt_Syntax_RdfParser::LOCATOR_DATASTRING);
    	    } catch (Exception $e) {
    	        return null;
    	    }
	        
            if (isset($result[$propertyUri])) {
                $pArray = $result[$propertyUri];
                if (isset($pArray['http://www.w3.org/2002/07/owl#inverseOf'])) {
                    $oArray = $pArray['http://www.w3.org/2002/07/owl#inverseOf'];
                    return $oArray[0]['value'];
                }
            }
            
            return null;
	    }
	}
	
	protected function _getPingbackTriplesFromRdfXmlString($rdfXml, $sourceUri, $targetUri)
	{
	    $parser = Erfurt_Syntax_RdfParser::rdfParserWithFormat('rdfxml');
	    try {
	        $result = $parser->parse($rdfxml, Erfurt_Syntax_RdfParser::LOCATOR_DATASTRING);
	    } catch (Exception $e) {
	        $this->_logError($e->getMessage());
	        return false;
	    }
        
        if (isset($result[$sourceUri])) {
            $this->_sourceRdf = $result[$sourceUri];
        }
        
        $foundTriples = array();
        foreach ($result as $s => $pArray) {
            foreach ($pArray as $p => $oArray) {
                foreach ($oArray as $oSpec) {
                    if ($s === $sourceUri) {
                        if (($oSpec['type'] === 'uri') && ($oSpec['value'] === $targetUri)) {
                            $foundTriples[] = array(
                                's' => $s,
                                'p' => $p,
                                'o' => $oSpec['value']
                            );
                        }
                    } else if (($oSpec['type'] === 'uri') && ($oSpec['value'] === $sourceUri)) {
                        // Try to find inverse property for $p
                        $inverseProp = $this->_determineInverseProperty($p);
                        if ($inverseProp !== null) {
                            $foundTriples[] = array(
                                's' => $oSpec['value'],
                                'p' => $inverseProp,
                                'o' => $s
                            );
                        }
                    }
                }
            }
        }
        
        return $foundTriples;
	}

	protected function _logError($msg) 
	{
	    $owApp = OntoWiki::getInstance(); 
	    $logger = $owApp->logger;
	    
	    if (is_array($msg)) {
	        $logger->debug('Pingback Component Error: ' . print_r($msg, true));
	    } else {
	        $logger->debug('Pingback Component Error: ' . $msg);
	    }
	}
	
	protected function _logInfo($msg) 
	{
	    $owApp = OntoWiki::getInstance(); 
	    $logger = $owApp->logger;
	    
	    if (is_array($msg)) {
	        $logger->debug('Pingback Component Info: ' . print_r($msg, true));
	    } else {
	        $logger->debug('Pingback Component Info: ' . $msg);
	    }
	}
	
	protected function _pingbackExists($s, $p, $o)
    {
        if ($this->_targetGraph === null) {
            return false;
        }
        
        $store = Erfurt_App::getInstance()->getStore();
        $sparql = 'SELECT ?s FROM <' . $this->_targetGraph . '> WHERE { ?s <' . $p . '> <' . $o . '> . FILTER(sameTerm(?s, <' . $s . '>))} LIMIT 1';
        	
		require_once 'Erfurt/Sparql/SimpleQuery.php';
		$query = Erfurt_Sparql_SimpleQuery::initWithString($sparql);
		$result = $store->sparqlQuery($query, array('use_ac' => false));
		
		return (boolean)$result;
    }
}
