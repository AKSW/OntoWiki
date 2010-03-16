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
            $graph = $this->_getFirstReadableGraphForUri($uri);
            if (!$graph) {
                return false;
            }
            
            // content negotiation
            $type = (string)$this->_matchDocumentTypeRequest($event->request, array(
                'text/html',
                'application/xhtml+xml',
                'application/rdf+xml',
                'text/n3',
                'application/json',
                'application/xml'
            ));
         
            $format = 'rdf';
            if (isset($this->_privateConfig->format)) {
                $format = $this->_privateConfig->format;
            }
            
            if (true === (boolean)$this->_privateConfig->provenance->enabled) {
                $prov = 1;
            } else {
                $prov = 0;
            }
            
            // graph URIs export the whole graph
            if ($graph === $uri) {
                $controllerName = 'model';
                $actionName = 'export';
            } else {
                $controllerName = 'resource';
                $actionName = 'export';
            }
         
            // redirect accordingly
            switch ($this->_typeMapping[$type]) {
                case 'rdf':
                    // set export action
                    $url = new OntoWiki_Url(array('controller' => $controllerName, 'action' => $actionName), array());
                    $url->setParam('r', $uri, true)
                        ->setParam('f', $format)
                        ->setParam('m', $graph)
                        ->setParam('provenance', $prov);
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
            
            // give plugins a chance to do something
            $event = new Erfurt_Event('beforeLinkedDataRedirect');
            $event->response = $response;
            $event->trigger();
            
            // set redirect and send immediately
            $response->setRedirect((string)$url, 303)
                     ->sendResponse();
            
            // TODO: do it the official Zend way
            exit;
            
            return false;
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
