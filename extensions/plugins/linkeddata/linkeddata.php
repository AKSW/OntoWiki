<?php

/**
 * OntoWiki linked data plug-in
 *
 * Takes a request URL as a resource URI and forwards to a appropriate
 * action if a resource exists, thus providing dereferencable resource URIs.
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_plugins
 * @author     Norman Heino <norman.heino@gmail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: linkeddata.php 4093 2009-08-19 22:29:29Z christian.wuerker $
 */
class LinkeddataPlugin extends OntoWiki_Plugin
{
    /**
     * Recognized mime type requests and format (f) parameter values
     * @var array
     */
    private $_typeMapping = array(
        ''                      => 'xhtml', // default is xhtml
        'text/html'             => 'xhtml', // we only deliver XML-compatible html
        'application/xhtml+xml' => 'xhtml', 
        'application/rdf+xml'   => 'rdf', 
        'text/n3'               => 'n3', 
        'application/json'      => 'json', 
        'application/xml'       => 'xhtml'  // TODO: should this be xhtml or rdf?
    );
    
    /**
     * Handles an arbitrary URI by checking if a resource exists in the store
     * and forwarding to a redirecting to a URL that provides information
     * about that resource in the requested mime type.
     *
     * @param string $uri the requested URI
     * @param Zend_Controller_Request_Http
     *
     * @return boolean False if the request was not handled, i.e. no resource was found.
     */
    public function onIsDispatchable($event)
    {
        $store    = OntoWiki::getInstance()->erfurt->getStore();
        $request  = Zend_Controller_Front::getInstance()->getRequest();
        $response = Zend_Controller_Front::getInstance()->getResponse();
      
        $uri = $event->uri;
      
        try {
            $result = $store->getGraphsUsingResource($uri, false);
            
            if ($result) {                
                // get source graph
                $allowedGraph = null;
                $ac = Erfurt_App::getInstance()->getAc();
                foreach ($result as $g) {
                    if ($ac->isModelAllowed('view', $g)) {
                        $allowedGraph = $g;
                        break;
                    }
                }
                
                if (null === $allowedGraph) {
                    // We use the first matching graph... The user is redirected and the next request
                    // has to decide, whether user is allowed to view or not. (Workaround, for there are problems
                    // with linkeddata and https).
                    $graph = $result[0];
                }  else {
                    $graph = $allowedGraph;
                }
                
                // content negotiation
                $type = (string)$this->_matchDocumentTypeRequest($event->request);
             
                // redirect accordingly
                switch ($this->_typeMapping[$type]) {
                    case 'rdf':
                        // set export action
                        $url = new OntoWiki_Url(array('controller' => 'resource', 'action' => 'export'), array());
                        $url->setParam('r', $uri, true)
                            ->setParam('f', 'rdf')
                            ->setParam('m', $graph);
                        break;
                    case 'xhtml':
                    default:
                        // make active graph (session required)
                        OntoWiki::getInstance()->selectedModel = $store->getModel($graph);
                        // default property view
                        $url = new OntoWiki_Url(array('route' => 'properties'), array());
                        $url->setParam('r', $uri, true)
                            ->setParam('m', $graph);
                        break;
                }
                
                $request->setDispatched(true);
                
                // set redirect and send immediately
                $response->setRedirect((string)$url, 303)
                         ->sendResponse();
                
                // TODO: do it the official Zend way
                exit;
            } 
            
            return false;
        } catch (Erfurt_Exception $e) {
            // don't handle errors since other plug-ins 
            // could chain this event
            return false;
        }
    }
    
    /**
     * Matches the request's accept header againest supported mime types
     * and returns the supported type with highest priority found.
     *
     * @param Zend_Request_Abstract the request object
     *
     * @return string
     */
    private function _matchDocumentTypeRequest($request)
    {
        $matches = array();
        $maxQ    = 0;
        $serve   = null;
        
        // get accept header
        $acceptHeader = strtolower($request->getHeader('Accept'));
        
        // patterns
        $type    = '[\w]+\/[\w+]+|[\w]+\/\*|\*\/\*';
        $quality = ';q=0.[0-9]+|1';
        
        // match
        preg_match_all("/$type|$quality/", $acceptHeader, $matches);
        // we only need full pattern matches
        if (isset($matches[0])) {
            for ($i = 0, $max = count($matches[0]); $i < $max; ++$i) {
                // handle quality
                if (is_numeric($quality = str_replace(';q=', '', $matches[0][$i]))) {
                    $maxQ = $quality;
                    continue;
                }
                // handle type
                if ((!$serve || $quality > $maxQ) && array_key_exists($matches[0][$i], $this->_typeMapping)) {
                    $serve = $matches[0][$i];
                }
            }
            
            return $serve;
        }
    }
}

