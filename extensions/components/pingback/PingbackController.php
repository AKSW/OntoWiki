<?php

require_once 'OntoWiki/Controller/Component.php';
require_once 'Zend/XmlRpc/Server.php';
require_once 'Zend/XmlRpc/Server/Exception.php';

class PingbackController extends OntoWiki_Controller_Component
{
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
	 *
	 * @param string      $sourceUri
	 * @param string      $targetUri
	 * @param string/null $relationUri
	 *
	 * @return int
	 */
	public function ping($sourceUri, $targetUri, $relationUri = null) 
	{	
		$this->_logInfo('Method ping was called.');

		// 1. Try to dereference the source URI.
		require_once 'Zend/Http/Client.php';
        $client = new Zend_Http_Client($uri, array(
            'maxredirects'  => 10,
            'timeout'       => 30
        ));
		// If a relation URI is given, we try application/rdf+xml first, if not we use text/html
		if ($relationUri !== null) {
		    $client->setHeaders('Accept', 'application/rdf+xml');
		} else {
		    $client->setHeaders('Accept', 'text/html');
		}
		
        $response = $client->request();
        if ($response->getStatus() === '200') {
            if ($relationUri !== null) {
                // TODO handle rdf/xml
            } else {
                $htmlDoc = new DOMDocument();
                $result = @$htmlDoc->loadHtml($response->getBody());
                $aElements = $htmlDoc->getElementsByTagName('a');

                $success = false;
                foreach ($aElements as $aElem) {
                    $a  = $aElem->getAttribute('href');
                    if (strtolower($a) === $targetUri) {
                        $success = true;
                        break;
                    }
                }
                
                if (!$success) {
                    return 0x0011;
                }
            }
        } else {
            return 0x0010;
        }
		
        
        // 2. Now we not that sourceUri exists and that the dereferenced content contains a link to targetUri.
        // Next step is to check, whether target URI exists (at least one statement).
        $store = Erfurt_App::getInstance()->getStore();
        $sparql = 'ASK WHERE { <' . $targetUri . '> ?p ?o . }';
        require_once 'Erfurt/Sparql/SimpleQuery.php';
		$query = Erfurt_Sparql_SimpleQuery::initWithString($commentSparql);
        $result = $store->sparqlQuery($query);
	    if (!$result) {
	        return 0x0020;
	    } 
        // Next step is to check, whether the pingback statement already exists and if not to add it.
		if ($this->_pingbackExists($sourceUri, $targetUri, $relationUri)) {
		    return 0x0030;
		}
		
		$this->_addPingback($sourceUri, $targetUri, $relationUri);
   
		// pingback done
		$error = "Thanks! Pingback from ".$sourceURI." to ".$targetURI." registered";
		$this->errorlog($error);

		throw new Zend_XmlRpc_Server_Exception($error);
		return 0;
	}
	
	protected function _addPingback($sourceUri, $targetUri, $relationUri) 
	{
		$store = Erfurt_App::getInstance()->getStore();
		$model = $store->getModel($this->_privateConfig->pingback_model);
		
// TODO use configurable relation uris...
		if ($relationUri === null) {
		    // Use default...
		    $model->addStatement(
    			$sourceUri,
    			'http://rdfs.org/sioc/ns#reply_of',
    			array('value' => $targetUri, 'type' => 'uri')
    		);
		} else {
// TODO use inverse?
		    $model->addStatement(
    			$targetUri,
    		    $relationUri,
    			array('value' => $sourceUri, 'type' => 'uri')
    		);
		}
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
	
	protected function _pingbackExists($sourceUri, $targetUri, $relationUri = null)
    {
// TODO use confgurable predicate uris
// TODO search for inverse uris if relation is given
        $store = Erfurt_App::getInstance()->getStore();
		$model = $store->getModel($this->_privateConfig->pingback_model);

		// Check if it already was pinged.
		if ($relationUri === null) {
		    $relationUri = 'http://rdfs.org/sioc/ns#reply_of';
		    
		    $sparql = 'SELECT ?pingback
    			WHERE {
    				?pingback <' . $relationUri . '> <' . $targetUri . '>.
    			}
    			LIMIT 1';
		} else {
		    $sparql = 'SELECT ?pingback
    			WHERE {
    				<' . $targetUri . '> <' . $relationUri . '> ?pingback.
    			}
    			LIMIT 1';
		}
	
		require_once 'Erfurt/Sparql/SimpleQuery.php';
		$query = Erfurt_Sparql_SimpleQuery::initWithString($sparql);
		$result = $model->sparqlQuery($query);
		if ($result[0]['pingback'] === $sourceUri) {
			return true;
		}
		
        return false;
    }
}
