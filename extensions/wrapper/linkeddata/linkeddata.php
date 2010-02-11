<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_wrapper
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version $Id: linkeddata.php 4094 2009-08-19 22:36:13Z christian.wuerker $
 */

require_once 'Erfurt/Wrapper.php';

/**
 * This wrapper extension provides functionality for gathering linked data.
 * 
 * @category   OntoWiki
 * @package    OntoWiki_extensions_wrapper
 * @copyright  Copyright (c) 2009 {@link http://aksw.org aksw}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author     Philipp Frischmuth <pfrischmuth@googlemail.com>
 */
class LinkeddataWrapper extends Erfurt_Wrapper
{
    // ------------------------------------------------------------------------
    // --- Private properties -------------------------------------------------
    // ------------------------------------------------------------------------
    
    /**
     * Contains cached data if the wrapper is used more than once in one 
     * request.
     * 
     * @var array|null
     */
    private $_cachedData = null;
    
    /**
     * Contains cached namespaces if the wrapper is used more than once in one 
     * request.
     * 
     * @var array|null
     */
    private $_cachedNs = null;
    
    /**
     * If the location of the data differs from the tested URI, this property
     * contains the current URL.
     * 
     * @var string|null
     */
    private $_url = null;
    
    // ------------------------------------------------------------------------
    // --- Public methods -----------------------------------------------------
    // ------------------------------------------------------------------------
    
    public function getDescription()
    {
        return 'This wrapper checks for Linked Data that is accessible through an URI.';
    }
    
    public function getName()
    {
        return 'Linked Data Wrapper';
    }
    
    public function isAvailable($uri, $graphUri)
    { 
        $uri = urldecode($uri);
        
        // Check whether there is a cache hit...
        $id = $this->_cache->makeId($this, 'isAvailable', array($uri, $graphUri));
        $result = $this->_cache->load($id);
        if ($result !== false) {
            if (isset($result['data'])) {
                $this->_cachedData = $result['data'];
                $this->_cachedNs   = $result['ns'];
            }
            
            return $result['value'];
        }
        
        $retVal = false;
        $ns = array();
        $data = array();
        
        // Test the URI.
        $this->_url = $uri;
        require_once 'Zend/Http/Client.php';
        $client = new Zend_Http_Client($uri, array(
            'maxredirects'  => 0,
            'timeout'       => 30
        ));
    
        $client->setHeaders('Accept', 'application/rdf+xml');
        $response = $client->request();
        $success = $this->_handleResponse($client, $response, 'application/rdf+xml');
        
        if ($success === true) {
            $response = $client->getLastResponse();
            
            if (null !== $this->_url) {
                $temp = $this->_url;
            } else {
                $temp = $uri;
            }
            
            if (strrpos($uri, '#') !== false) {
                $baseUri = substr($temp, 0, strrpos($temp, '#'));
            } else {
                $baseUri = $temp;
            }
            
            $tempArray = $this->_handleResponseBody($response, $baseUri);
            $ns = $tempArray['ns'];
            $tempArray = $tempArray['data'];
            
            /*
            $data = $tempArray;
            if (count($data) > 0) {
                $retVal = true;
            } else {
                $retVal = false;
            }
            */

            if (isset($tempArray[$uri])) {
                $data = array($uri => $tempArray[$uri]);
                $retVal = true;
            } else {
                $data = array();
                $ns = array();
                $retVal = false;
            }
        } else {
            // try n3
            $client->setHeaders('Accept', 'text/n3');
            $response = $client->request();
            
            $success = $this->_handleResponse($client, $response);
            if ($success === true) {
                $tempArray = $this->_handleResponseBody($client->getLastResponse());
                $ns = $tempArray['ns'];
                $tempArray = $tempArray['data'];

                if (isset($tempArray[$uri])) {
                    $data = array($uri => $tempArray[$uri]);
                    $retVal = true;
                } else {
                    $data = array();
                    $ns = array();
                    $retVal = false;
                }
            } else {
                // try text/html...
                $client->setHeaders('Accept', 'text/html');
                $response = $client->request();
                
                $success = $this->_handleResponse($client, $response);
                if ($success === true) {
                    $tempArray = $this->_handleResponseBody($client->getLastResponse());
                    $ns = $tempArray['ns'];
                    $tempArray = $tempArray['data'];

                    if (isset($tempArray[$uri])) {
                        $data = array($uri => $tempArray[$uri]);
                        $retVal = true;
                    } else {
                        $data = array();
                        $ns = array();
                        $retVal = false;
                    }
                }    
            }
        } 
        
        $this->_cachedData = $data;  
        $this->_cachedNs   = $ns;
        $cacheVal = array('value' => $retVal, 'data' => $data, 'ns' => $ns);
        $this->_cache->save($cacheVal, $id);
        
        return $retVal;
    }
    
    public function isHandled($uri, $graphUri)
    {
        $uri = urldecode($uri);
        
        if ((substr($uri, 0, 7) !== 'http://')) {
            return false;
        } else {
            if (isset($this->_config->handle->mode) && $this->_config->handle->mode === 'none') {
                if (isset($this->_config->handle->exception)) {
                    // handle only explicit mentioned uris
                    $isHandled = false;
                    foreach ($this->_config->handle->exception->toArray() as $exception) {
                        if ($this->_matchUri($exception, $uri)) {
                            $isHandled = true;
                            break;
                        }
                    }

                    return $isHandled;
                } else {
                    return false;
                }
            } else {
                // handle all uris by default
                if (isset($this->_config->handle->exception)) {
                    foreach ($this->_config->handle->exception->toArray() as $ignored) {
                        if ($this->_matchUri($ignored, $uri)) {
                            return false;
                        }
                    }
                } else {
                    return true;
                }
            }
        }

        return true;
    }
    
    public function run($uri, $graphUri)
    {
        $uri = urldecode($uri);
        
        if (null === $this->_cachedData) {
            $isAvailable = $this->isAvailable($uri, $graphUri);
        
            if ($isAvailable === false) {
                return false;
            }
        }
        
        $data = $this->_cachedData;
        $ns   = $this->_cachedNs;
        
        $presetMatch = false;
        if (isset($this->_config->fetch->preset)) {
            foreach ($this->_config->fetch->preset->toArray() as $i=>$preset) {
                if ($this->_matchUri($preset['match'], $uri)) {
                    $presetMatch = $i;
                    break;
                }
            }
        }

        if ($presetMatch !== false) {
            // Use the preset.
            $presets = $this->_config->fetch->preset->toArray();

            if (isset($presets[$presetMatch]['mode']) && $presets[$presetMatch]['mode'] === 'none') {

                // Start with an empty result.
                $result = array();
                if (isset($presets[$presetMatch]['exception'])) {

                    foreach ($presets[$presetMatch]['exception'] as $exception) {
                        if (isset($data[$uri][$exception])) {
                            if (!isset($result[$uri])) {
                                $result[$uri] = array();
                            } 

                            $result[$uri][$exception] = $data[$uri][$exception];
                        }
                    }
                }   
            } else {
                // Use the default rule.

                // Start with all data.
                $result = $data;
                if (isset($presets[$presetMatch]['exception'])) {
                    foreach ($presets[$presetMatch]['exception'] as $exception) {
                        if (isset($data[$uri][$exception])) {
                            if (isset($result[$uri][$exception])) {
                                unset($result[$uri][$exception]);
                            }
                        }
                    }
                }
            }
        } else {
            if (isset($this->_config->fetch->default->mode) && $this->_config->fetch->default->mode === 'none') {
                // Start with an empty result.
                $result = array();
                if (isset($this->_config->fetch->default->exception)) {
                    foreach ($this->_config->fetch->default->exception->toArray() as $exception) {
                        if (isset($data[$uri][$exception])) {
                            if (!isset($result[$uri])) {
                                $result[$uri] = array();
                            } 

                            $result[$uri][$exception] = $data[$uri][$exception];
                        }
                    }
                }
            } else {
                // Start with all data.
                $result = $data;
                if (isset($this->_config->fetch->default->exception)) {
                    foreach ($this->_config->fetch->default->exception->toArray() as $exception) {
                        if (isset($data[$uri][$exception])) {
                            if (isset($result[$uri][$exception])) {
                                unset($result[$uri][$exception]);
                            }
                        }
                    }
                }
            }
        } 
        
        $fullResult = array();
        $fullResult['status_codes'] = array(
            Erfurt_Wrapper::NO_MODIFICATIONS, 
            Erfurt_Wrapper::RESULT_HAS_ADD, 
            Erfurt_Wrapper::RESULT_HAS_NS
        );
        $fullResult['status_description'] = "Linked Data found for URI $uri";
        
        $fullResult['ns']  = $ns;

        //remove blanknodes (bn as object)
        foreach($result as $rkey => $resource){
            foreach($resource as $pkey => $property){
                foreach($property as $vkey => $value){
                    if(($value['type'] == 'uri' && substr($value['value'], 0, 2) == '_:') || $value['type'] == 'bnode'){
                        unset($result[$rkey][$pkey][$vkey]);
                        if(empty($result[$rkey][$pkey])){ //if this was the last value if this property - delete the property
                            unset($result[$rkey][$pkey]);
                        }
                        /*
                        if(empty($result[$rkey][$pkey])){ // if this was the last property of this resource - delete the resource
                            unset($result[$rkey]);
                        }*/
                    }
                }
            }
        }

        $fullResult['add'] = $result;
        //print_r($result);

        return $fullResult;
    }
    
    // ------------------------------------------------------------------------
    // --- Private methods ----------------------------------------------------
    // ------------------------------------------------------------------------
    
    /**
     * Handles the different response codes for a given response.
     */
    private function _handleResponse(&$client, $response, $accept = null) 
    {
        switch ($response->getStatus()) {
            case 303:
                // 303 See also... Do a second request with the new url
                $this->_url = $response->getHeader('Location');
                $client->setUri($this->_url);
                $response = $client->request();

                return $this->_handleResponse($client, $response);
            case 200:
                // 200 OK
                return true;
            case 401:
                // In this case we try to request the service again with credentials.
                $identity = Erfurt_App::getInstance()->getAuth()->getIdentity();
                if (!$identity->isWebId()) {
                    // We only support WebIDs here.
                    return false;
                }

                $url = $this->_url;
                if (substr($url, 0, 7) === 'http://') {
                    // We need SSL here!
                    $url = 'https://' . substr($url, 7);
                    $client->setUri($url);
                }

                // We need a valid cert that cats as the client cert for the request
                $config = Erfurt_App::getInstance()->getConfig();
                if (isset($config->auth->foafssl->agentCertFilename)) {
                    $certFilename = $config->auth->foafssl->agentCertFilename;
                } else {
                    return false;
                }

                $client = new Zend_Http_Client($url, array(
                    'maxredirects'  => 10,
                    'timeout'       => 30,
                    'sslcert'       => $certFilename
                ));

                if (null !== $accept) {
                    $client->setHeaders('Accept', $accept);
                }

                $client->setHeaders(
                    'Authorization', 
                    'FOAF+SSL '.base64_encode('ow_auth_user_key="' . $identity->getUri() . '"'), 
                    true
                );

                $response = $client->request();
            
                return $this->_handleResponse($client, $response, $accept);
            default:
                return false;
        }
    }
    
    /**
     * Handles the data contained in a response.
     */
    private function _handleResponseBody($response, $baseUri = null)
    {
        $contentType = $response->getHeader('Content-type');
        if ($pos = strpos($contentType, ';')) {
            $contentType = substr($contentType, 0, $pos);
        }
        
        switch ($contentType) {
            case 'application/rdf+xml':
            case 'text/plain':
                $type = 'rdfxml';
                break;
            case 'application/json':
                $type = 'rdfjson';
                break;
            case 'text/rdf+n3':
            case 'text/n3':
                $type = 'rdfn3';
                break;
            case 'text/html':
                return $this->_handleResponseBodyHtml($response, $baseUri);
            default:
                require_once 'Erfurt/Wrapper/Exception.php';
                throw new Erfurt_Wrapper_Exception('Server returned not supported content type: ' . $contentType);
        }
        
        $data = $response->getBody();
        
        require_once 'Erfurt/Syntax/RdfParser.php';
        $parser = Erfurt_Syntax_RdfParser::rdfParserWithFormat($type);
        $result = $parser->parse($data, Erfurt_Syntax_RdfParser::LOCATOR_DATASTRING, $baseUri);
        $ns     = $parser->getNamespaces();
        
        return array(
            'data' => $result,
            'ns'   => $ns
        );
    }
    
    private function _handleResponseBodyHtml($response, $baseUri = null)
    {
        $htmlDoc = new DOMDocument();
        $result = @$htmlDoc->loadHtml($response->getBody());
        
        $relElements = $htmlDoc->getElementsByTagName('link');
        
        $documents = array();
        foreach ($relElements as $relElem) {
            $rel  = $relElem->getAttribute('rel');
            $type = $relElem->getAttribute('type');
            
            if (strtolower($rel) === 'meta' && strtolower($type) === 'application/rdf+xml') {
                $documents[] = $relElem->getAttribute('href');
            }
        }
        
        $fullNs     = array();
        $fullResult = array();
        
        require_once 'Zend/Http/Client.php';
        $client = new Zend_Http_Client(null, array(
            'maxredirects'  => 0,
            'timeout'       => 30
        ));
    
        $client->setHeaders('Accept', 'application/rdf+xml');
   
        foreach ($documents as $docUrl) {
            $client->setUri($docUrl);
            $response = $client->request();
           
            $success = $this->_handleResponse($client, $response);

            if ($success === true) {
                $response = $client->getLastResponse();

                if (null !== $this->_url) {
                    $temp = $this->_url;
                } else {
                    $temp = $uri;
                }

                if (strrpos($uri, '#') !== false) {
                    $baseUri = substr($temp, 0, strrpos($temp, '#'));
                } else {
                    $baseUri = $temp;
                }

                $tempArray = $this->_handleResponseBody($response, $baseUri);
                $fullNs = array_merge($tempArray['ns'], $fullNs);
                $tempArray = $tempArray['data'];
                
                foreach ($tempArray as $s=>$pArray) {
                    if (isset($fullResult[$s])) {
                        foreach ($pArray as $p=>$oArray) {
                            if (isset($fullResult[$s][$p])) {
                                foreach ($oArray as $o) {
                                    // TODO Make a full check in order to avoid duplicate objects!
                                    $fullResult[$s][$p][] = $o;
                                }
                            } else {
                                $fullResult[$s][$p] = $oArray;
                            }
                        }
                    } else {
                        $fullResult[$s] = $pArray;
                    }
                }

            } else {
                // Do nothing for the moment...
            }
        }
        
        return array(
            'data' => $fullResult,
            'ns'   => $fullNs
        );
    }
    
    private function _matchUri($pattern, $uri)
    {
        if ((substr($pattern, 0, 7) !== 'http://')) {
            $pattern = 'http://' . $pattern;
        }
        
        if ((substr($uri, 0, strlen($pattern)) === $pattern)) {
            return true;
        } else {
            return false;
        }
    }
}
