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
 * @copyright  Copyright (c) 2010, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class LinkeddataPlugin extends OntoWiki_Plugin
{
    /**
     * Recognized mime type requests and format (f) parameter values
     * @var array
     */
    private $_typeMapping = array(
        ''                      => 'html', // default is xhtml
        'text/html'             => 'html', // we only deliver XML-compatible html
        'application/xhtml+xml' => 'html', 
        'application/rdf+xml'   => 'rdf', 
        'text/n3'               => 'n3', 
        'application/json'      => 'json', 
        'application/xml'       => 'html'  // TODO: should this be xhtml or rdf?
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
            // content negotiation
            $flag  = false;
            $type  = $this->_getTypeForRequest($request, $uri, $flag);
            $graph = $this->_getFirstReadableGraphForUri($uri);
            if (!$graph) {
                return false;
            }
         
            $format = 'rdf';
            if (isset($this->_privateConfig->format)) {
                $format = $this->_privateConfig->format;
            }
            
            $prov = (boolean)$this->_privateConfig->provenance->enabled;
         
            // redirect accordingly
            switch ($type) {
                case 'rdf':
                case 'n3':
                    $format = $type;
                    // graph URIs export the whole graph
                    if ($graph === $uri) {
                        $controllerName = 'model';
                        $actionName = 'export';
                    } else {
                        $controllerName = 'resource';
                        $actionName = 'export';
                    }
                    
                    // set export action
                    $url = new OntoWiki_Url(array('controller' => $controllerName, 'action' => $actionName), array());
                    $url->setParam('r', $uri, true)
                        ->setParam('f', $format)
                        ->setParam('m', $graph)
                        ->setParam('provenance', $prov);
                    break;
                case 'html':
                default:
                    // default property view
                    $url = new OntoWiki_Url(array('route' => 'properties'), array());
                    $url->setParam('r', $uri, true)
                        ->setParam('m', $graph);
                    break;
            }
            
            // make active graph (session required)
            $activeModel = $store->getModel($graph);
            OntoWiki::getInstance()->selectedModel    = $activeModel;
            OntoWiki::getInstance()->selectedResource = new OntoWiki_Resource($uri, $activeModel);
            
            $request->setDispatched(true);

            // give plugins a chance to do something
            $event = new Erfurt_Event('onBeforeLinkedDataRedirect');
            $event->response = $response;
            $event->trigger();
            
            // give plugins a chance to handle redirection self
            $event = new Erfurt_Event('onShouldLinkedDataRedirect');
            $event->request  = $request;
            $event->response = $response;
            $event->type     = $type;
            $event->uri      = $uri;
            $event->flag     = $flag;
            $event->setDefault(true);
            
            $shouldRedirect = $event->trigger();
            if ($shouldRedirect) {
                // set redirect and send immediately
                $response->setRedirect((string)$url, 303)
                         ->sendResponse();
                exit;
            }
            
            return !$shouldRedirect; // will default to false
        } catch (Erfurt_Exception $e) {
            // don't handle errors since other plug-ins 
            // could chain this event
            return false;
        }
    }
    
    public function onRouteShutdown($event)
    {
        $owApp = OntoWiki::getInstance();
        $requestUri = $owApp->config->urlBase . ltrim($event->request->getPathInfo(), '/');
            
        $viewPos = strrpos($requestUri, '/view/');
        if ($viewPos !== false) {
            $uri = substr($requestUri, 0, $viewPos) . '/id/' . substr($requestUri, $viewPos+6);
                
            $store = Erfurt_App::getInstance()->getStore();
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
                
                $graph = null;
                if ($allowedGraph !== null) {
                    $graph = $store->getModel($allowedGraph);
                    $owApp->selectedModel = $graph;
                }
                    
                $resource = new OntoWiki_Resource($uri, $graph);
                $owApp->selectedResource = $resource;
            }
        }
        $dataPos = strrpos($requestUri, '/data/');
        if ($dataPos !== false) {
            $uri = substr($requestUri, 0, $dataPos) . '/id/' . substr($requestUri, $dataPos+6);
                
            $store = Erfurt_App::getInstance()->getStore();
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
                    
                $graph = null;
                if ($allowedGraph !== null) {
                    $graph = $store->getModel($allowedGraph);
                    $owApp->selectedModel = $graph;
                }
                    
                $resource = new OntoWiki_Resource($uri, $graph);
                $owApp->selectedResource = $resource;
            }
        }
    }
    
    public function onNeedsGraphForLinkedDataUri($event)
    {
        return $this->_getFirstReadableGraphForUri($event->uri);
    }
    
    public function onNeedsLinkedDataUri($event)
    {
        if ($this->_isLinkedDataUri($event->uri)) {
            $g = $this->_getFirstReadableGraphForUri($event->uri);
            if ($g !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    protected function _getTypeForRequest($request, &$uri, &$flag)
    {
        // check for valid type suffix
        $parts  = explode('.', $uri);
        $suffix = $parts[count($parts)-1];
        if (in_array($suffix, array_values($this->_typeMapping))) {
            $uri = substr($uri, 0, strlen($uri) - strlen($suffix) - 1);
            $flag = true; // rewritten flag
            return $suffix;
        }
        
        // content negotiation
        $possibleTypes = array_filter(array_keys($this->_typeMapping));
        if ($type = $this->_matchDocumentTypeRequest($request, $possibleTypes)) {
            return $this->_typeMapping[$type];
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
    private function _matchDocumentTypeRequest($request, array $supportedTypes = array())
    {
        return OntoWiki_Utils::matchMimetypeFromRequest($request, $supportedTypes);
    }
    
    private function _getFirstReadableGraphForUri($uri)
    {
        $store = OntoWiki::getInstance()->erfurt->getStore();
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
                    // We use the first matching graph. The user is redirected and the next request
                    // has to decide, whether user is allowed to view or not. (Workaround since there are problems
                    // with linkeddata and https).
                    return $result[0];
                }  else {
                    return $allowedGraph;
                }
            } else {
                return null;
            }
        } catch (Excpetion $e) {
            return null;
        }
    }
    
    private function _isLinkedDataUri($uri)
    {
        $owApp = OntoWiki::getInstance(); 
        $owBase = $owApp->config->urlBase;
        
        if (substr($uri, 0, strlen($owBase)) === $owBase) {
            return true;
        }
        
        return false;
    }
}
