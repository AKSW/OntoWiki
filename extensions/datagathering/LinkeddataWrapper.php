<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @category  OntoWiki
 * @package   OntoWiki_extensions_wrapper
 * @copyright Copyright (c) 2010, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once 'Erfurt/Wrapper.php';

/**
 * This wrapper extension provides functionality for gathering linked data.
 *
 * @category  OntoWiki
 * @package   OntoWiki_extensions_wrapper
 * @copyright Copyright (c) 2009 {@link http://aksw.org aksw}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author    Philipp Frischmuth <pfrischmuth@googlemail.com>
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
    
    private $_httpAdapter = null;
    
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
    
    public function isAvailable($r, $graphUri, $all = false)
    { 
        $uri = $r->getUri();
        $url = $r->getLocator();
        
        // Check whether there is a cache hit...
        if (null !== $this->_cache) {
            $id = $this->_cache->makeId($this, 'isAvailable', array($uri, $graphUri));
            $result = $this->_cache->load($id);
            if ($result !== false) {
                if (isset($result['data'])) {
                    $this->_cachedData = $result['data'];
                    $this->_cachedNs   = $result['ns'];
                }

                return $result['value'];
            }
        }
        
        $retVal = false;
        $ns = array();
        $data = array();

        // Test the URI.
        $this->_url = $url;
        try{ 
            $client = $this->_getHttpClient($url, array(
                'maxredirects'  => 0,
                'timeout'       => 30
            ));
        } catch (Zend_Uri_Exception $e){
            return false;
        }
        
        $client->setHeaders('Accept', 'application/rdf+xml');
        $response = $client->request();
        $success = $this->_handleResponse($client, $response, 'application/rdf+xml');

        if ($success === true) {
            $response = $client->getLastResponse();
            
            if (null !== $this->_url) {
                $temp = $this->_url;
            } else {
                $temp = $url;
            }
            
            if (strrpos($url, '#') !== false) {
                $baseUri = substr($temp, 0, strrpos($temp, '#'));
            } else {
                $baseUri = $temp;
            }
            
            $tempArray = $this->_handleResponseBody($response, $baseUri);
            $ns = $tempArray['ns'];
            $tempArray = $tempArray['data'];
            
            if (!$all) {
                //only load statements, that have the $uri as subject
                if (isset($tempArray[$uri])) {
                    $data = array($uri => $tempArray[$uri]);
                    $retVal = true;
                } else {
                    $data = array();
                    $ns = array();
                    $retVal = false;
                }
            } else {
                //all statements that were found
                $data = $tempArray;
                $retVal = true;
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
        
        if (null !== $this->_cache) {
            $cacheVal = array('value' => $retVal, 'data' => $data, 'ns' => $ns);
            $this->_cache->save($cacheVal, $id);
        }
        
        return $retVal;
    }
    
    public function isHandled($r, $graphUri)
    {
        $url = $r->getLocator();
        
        // We only support HTTP URLs.
        if ((substr($url, 0, 7) !== 'http://') && (substr($url, 0, 8) !== 'https://')) {
            return false;
        } else {
            if (isset($this->_config->handle->mode) && $this->_config->handle->mode === 'none') {
                if (isset($this->_config->handle->exception)) {
                    // handle only explicit mentioned uris
                    $isHandled = false;
                    foreach ($this->_config->handle->exception->toArray() as $exception) {
                        if ($this->_matchUri($exception, $url)) {
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
                        if ($this->_matchUri($ignored, $url)) {
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
    
    public function run($r, $graphUri, $all = false)
    { 
        if (null === $this->_cachedData) {
            $isAvailable = $this->isAvailable($r, $graphUri, $all);
        
            if ($isAvailable === false) {
                return false;
            }
        }
        
        $data = $this->_cachedData;
        $ns   = $this->_cachedNs;
        
        $fullResult = array();
        $fullResult['status_codes'] = array(
            Erfurt_Wrapper::NO_MODIFICATIONS, 
            Erfurt_Wrapper::RESULT_HAS_ADD, 
            Erfurt_Wrapper::RESULT_HAS_NS
        );
        
        $uri = $r->getUri();
 
        $fullResult['status_description'] = "Linked Data found for URI $uri";
        $fullResult['ns'] = $ns;
        $fullResult['add'] = $data;
         
        return $fullResult;
    }
    
    public function setHttpAdapter($adapter)
    {
        $this->_httpAdapter = $adapter;
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

                $client = $this->_getHttpClient($url, array(
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
        
        $found = false;
        if($contentType == 'text/plain' && !empty($baseUri)){
            //if the mime type does not reveal anything, try file endings. duh
            $parts = explode('.', $baseUri);
            $ending = end($parts);
            $found = true;
            switch ($ending) {
                case 'n3':
                case 'ttl':
                    $type = 'rdfn3';
                    break;
                case 'xml':
                    $type = 'rdfxml';
                    break;
                case 'json':
                    $type = 'rdfjson';
                    break;
                default:
                    $found = false;
                    break;
            }
        }
        if(!$found){
            //use the defined mime type
            switch ($contentType) {
                case 'application/rdf+xml':
                case 'application/xml': // Hack for lsi urns
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
        
        $client = $this->_getHttpClient(null, array(
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
    
    private function _getHttpClient($uri, $options = array())
    {
        if (null !== $this->_httpAdapter) {
            $options['adapter'] = $this->_httpAdapter;
        }
// TODO Create HTTP client here and remove method from Erfurt_App.
        return Erfurt_App::getInstance()->getHttpClient($uri, $options);
    }
}
