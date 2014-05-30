<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

define('DEFAULT_TYPE', '*/*');

/**
 * OntoWiki linked data plug-in
 *
 * Takes a request URL as a resource URI and forwards to a appropriate
 * action if a resource exists, thus providing dereferencable resource URIs.
 *
 * @category   OntoWiki
 * @package    Extensions_Linkeddata
 * @author     Norman Heino <norman.heino@gmail.com>
 * @author     Natanael Arndt <arndtn@gmail.com>
 * @copyright  Copyright (c) 2013, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class LinkeddataPlugin extends OntoWiki_Plugin
{
    /**
     * Recognized mime type requests and format (f) parameter values
     *
     * @var array
     */
    private $_typeMapping
        = array(
            DEFAULT_TYPE            => 'html', // default is xhtml
            'text/html'             => 'html', // we only deliver XML-compatible html
            'application/xhtml+xml' => 'html',
            'application/rdf+xml'   => 'rdf',
            'text/n3'               => 'n3',
            'text/turtle'           => 'ttl',
            'application/json'      => 'json',
            'application/xml'       => 'html' // TODO: should this be xhtml or rdf?
        );

    /**
     * This method is called, when the onIsDispatchable event was triggered.
     *
     * The onIsDispatchable event is fired in an early stage of the OntoWiki
     * request lifecycle. Hence it is not decided in that moment, which controller
     * an action will be used.
     *
     * The given Erfurt_Event object has an uri property, which contains the
     * requested URI. The method then checks if a resource identified by that
     * URI exists in the local store. Iff this is the case it sends a redirect
     * to another URL depending on the requested MIME type.
     *
     * $event->uri contains the request URI.
     *
     * @param Erfurt_Event $event The event containing the required parameters.
     *
     * @return boolean false if the request was not handled, i.e. no resource was found.
     */
    public function onIsDispatchable($event)
    {
        $store = null;
        try {
            $store = OntoWiki::getInstance()->erfurt->getStore();
        } catch (Exception $e) {
            // if we can't get the store, we do nothing
            return;
        }

        $request  = Zend_Controller_Front::getInstance()->getRequest();
        $response = Zend_Controller_Front::getInstance()->getResponse();

        $uri = $event->uri;

        try {
            // Check for a supported type by investigating the suffix of the URI or by
            // checking the Accept header (content negotiation). The $matchingSuffixFlag
            // parameter contains true if the suffix was used instead of the Accept header.
            $matchingSuffixFlag = false;
            $type               = $this->_getTypeForRequest($request, $uri, $matchingSuffixFlag);

            // We need a readable graph to query. We use the first graph that was found.
            // If no readable graph is available for the current user, we cancel here.
            list($graph, $matchedUri) = $this->_matchGraphAndUri($uri);

            if (!$graph || !$matchedUri) {
                // URI not found
                return false;
            }

            if ($uri !== $matchedUri) {
                // Re-append faux file extension
                if ($matchingSuffixFlag) {
                    $matchedUri .= '.' . $type;
                }
                // Redirect to new (correct URI)
                $response->setRedirect((string)$matchedUri, 301);

                return;
            }

            // Prepare for redirect according to the given type.
            $url = null; // This will contain the URL to redirect to.
            switch ($type) {
                case 'rdf':
                case 'n3':
                case 'ttl':
                    // Check the config, whether provenance information should be included.
                    $prov = false;
                    if (isset($this->_privateConfig->provenance)
                        && isset($this->_privateConfig->provenance->enabled)
                    ) {

                        $prov = (boolean)$this->_privateConfig->provenance->enabled;
                    }

                    // Special case: If the graph URI is identical to the requested URI, we export
                    // the whole graph instead of only data regarding the resource.
                    if ($graph === $uri) {
                        $controllerName = 'model';
                        $actionName     = 'export';
                    } else {
                        $controllerName = 'resource';
                        $actionName     = 'export';
                    }

                    // Create a URL with the export action on the resource or model controller.
                    // Set the required parameters for this action.
                    $url = new OntoWiki_Url(
                        array('controller' => $controllerName, 'action' => $actionName),
                        array()
                    );
                    $url->setParam('r', $uri, true)
                        ->setParam('f', $type)
                        ->setParam('m', $graph)
                        ->setParam('provenance', $prov);
                    break;
                case 'html':
                default:
                    // Defaults to the standard property view.
                    // Set the required parameters for this action.
                    $url = new OntoWiki_Url(
                        array('route' => 'properties'),
                        array()
                    );
                    $url->setParam('r', $uri, true)
                        ->setParam('m', $graph);
                    break;
            }

            // Make $graph the active graph (session required) and make the resource
            // in $uri the active resource.
            $activeModel                              = $store->getModel($graph);
            OntoWiki::getInstance()->selectedModel    = $activeModel;
            OntoWiki::getInstance()->selectedResource = new OntoWiki_Resource($uri, $activeModel);

            // Mark the request as dispatched, since we have all required information now.
            $request->setDispatched(true);

            // Give plugins a chance to do something before redirecting.
            $event           = new Erfurt_Event('onBeforeLinkedDataRedirect');
            $event->response = $response;
            $event->trigger();

            // Give plugins a chance to handle the redirection instead of doing it here.
            $event           = new Erfurt_Event('onShouldLinkedDataRedirect');
            $event->request  = $request;
            $event->response = $response;
            $event->type     = $type;
            $event->uri      = $uri;
            $event->flag     = $matchingSuffixFlag;
            $event->setDefault(true);

            $shouldRedirect = $event->trigger();
            if ($shouldRedirect) {
                // set redirect and send immediately
                $response->setRedirect((string)$url, 303);

                return;
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
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $owApp   = OntoWiki::getInstance();

        $store = null;
        try {
            $store = $owApp->erfurt->getStore();
        } catch (Exception $e) {
            // if we can't get the store, we do nothing
            return;
        }

        if (null == $owApp->selectedModel) {
            $uri = $request->getScheme() . '://' . $request->getHttpHost() . $request->getRequestUri();
            try {
                $activeModel          = $store->getModel($uri);
                $owApp->selectedModel = $activeModel;
            } catch (Exception $e) {
                // Nothing to do here.
            }
        }
    }

    public function onNeedsGraphForLinkedDataUri($event)
    {
        $store = null;
        try {
            $store = OntoWiki::getInstance()->erfurt->getStore();
        } catch (Exception $e) {
            // if we can't get the store, we do nothing
            return;
        }

        $graphs = $store->getReadableGraphsUsingResource($event->uri);

        return $graphs[0];
    }

    public function onNeedsLinkedDataUri($event)
    {
        if ($this->_isLinkedDataUri($event->uri)) {
            $store = null;
            try {
                $store = OntoWiki::getInstance()->erfurt->getStore();
            } catch (Exception $e) {
                // if we can't get the store, we do nothing
                return;
            }

            $graphs = $store->getReadableGraphsUsingResource($event->uri);
            if ($graphs !== null) {
                return true;
            }
        }

        return false;
    }

    protected function _getTypeForRequest($request, &$uri, &$flag)
    {
        // check for valid type suffix
        $parts  = explode('.', $uri);
        $suffix = $parts[count($parts) - 1];
        if (in_array($suffix, array_values($this->_typeMapping))) {
            $uri  = substr($uri, 0, strlen($uri) - strlen($suffix) - 1);
            $flag = true; // rewritten flag
            return $suffix;
        }

        // content negotiation
        $possibleTypes = array_keys($this->_typeMapping);
        if ($type = $this->_matchDocumentTypeRequest($request, $possibleTypes)) {
            return $this->_typeMapping[$type];
        }

        return $this->_typeMapping[DEFAULT_TYPE]; // default type
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

    private function _matchGraphAndUri($uri)
    {
        $graph     = null;
        $actualUri = null;

        $store = null;
        try {
            $store = OntoWiki::getInstance()->erfurt->getStore();
        } catch (Exception $e) {
            // if we can't get the store, we do nothing
            return;
        }

        if ((bool)$this->_privateConfig->fuzzyMatch === true) {
            // Remove trailing slashes
            $uri = rtrim($uri, '/');
            // Match case-insensitive and optionally with trailing slashes
            $query    = sprintf(
                'SELECT DISTINCT ?uri WHERE {?uri ?p ?o . FILTER (regex(str(?uri), "^%s/*$", "i"))}',
                $uri
            );
            $queryObj = Erfurt_Sparql_SimpleQuery::initWithString($query);
            $result   = $store->sparqlQuery($queryObj);
            $first    = current($result);
            if (isset($first['uri'])) {
                $actualUri = $first['uri'];
            }
        } else {
            $actualUri = $uri;
        }

        $graphs = $store->getReadableGraphsUsingResource($actualUri, true);

        return array($graphs[0], $actualUri);
    }

    private function _isLinkedDataUri($uri)
    {
        $owApp  = OntoWiki::getInstance();
        $owBase = $owApp->config->urlBase;

        if (substr($uri, 0, strlen($owBase)) === $owBase) {
            return true;
        }

        return false;
    }
}
