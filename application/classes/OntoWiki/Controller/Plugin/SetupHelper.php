<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki Setup Helper Zend plug-in.
 *
 * Sets up the component and module managers before any request is handled
 * but after the request object exists.
 *
 * @category OntoWiki
 * @package OntoWiki_Classes_Controller_Plugin
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_Controller_Plugin_SetupHelper extends Zend_Controller_Plugin_Abstract
{
    /**
     * Denotes whether the setup has been performed
     * @var boolean
     */
    protected $_isSetup = false;
    
    /**
     * RouteStartup is triggered before any routing happens.
     */
    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
        /**
         * @trigger onRouteStartup
         */
        $event = new Erfurt_Event('onRouteStartup');
        $event->trigger();
    }
    
    /**
     * RouteShutdown is the earliest event in the dispatch cycle, where a 
     * fully routed request object is available
     */
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        // only once
        if (!$this->_isSetup) {
            $frontController = Zend_Controller_Front::getInstance();
            $ontoWiki        = OntoWiki::getInstance();
            $store           = $ontoWiki->erfurt->getStore();

            // instantiate model if parameter passed
            if (isset($request->m)) {
                try {
                    $model = $store->getModel($request->getParam('m', null, false));
                    $ontoWiki->selectedModel = $model;
                } catch (Erfurt_Store_Exception $e) {
                    // When no user is given (Anoymous) give the requesting party a chance to authenticate.
                    if (Erfurt_App::getInstance()->getAuth()->getIdentity()->isAnonymousUser()) {
                        // In this case we allow the requesting party to authorize...
                        $response = $frontController->getResponse();
                        $response->setException(new OntoWiki_Http_Exception(401));
                        return;
                    }
// TODO clean up (no exit!)  
                    // post error message
                    $ontoWiki->prependMessage(new OntoWiki_Message(
                        '<p>Could not instantiate model: ' . $e->getMessage() . '</p>' . 
                        '<a href="' . $ontoWiki->config->urlBase . '">Return to index page</a>', 
                        OntoWiki_Message::ERROR, array('escape' => false)));
                    // hard redirect since finishing the dispatch cycle will lead to errors
                    header('Location:' . $ontoWiki->config->urlBase . 'error/error');
                    return;
                }
            }
            
            // instantiate resource if parameter passed
            if (isset($request->r)) {
                $rParam = $request->getParam('r', null, true);
                $graph = $ontoWiki->selectedModel;
                if (null === $graph) {
                    // try to use first readable graph
                    $possibleGraphs = $store->getGraphsUsingResource((string)$rParam, true);
                    if (count($possibleGraphs) > 0) {
                        try {
                            $graph = $store->getModel($possibleGraphs[0]);
                            $ontoWiki->selectedModel = $graph;
                        } catch (Erfurt_Store_Exception $e) {
                            $graph = null;
                            // fail as before (see below)
                        }
                    }
                }

                if ($graph instanceof Erfurt_Rdf_Model) {
                    $resource = new OntoWiki_Resource($rParam, $graph);
                    $ontoWiki->selectedResource = $resource;
                } else {
                    // post error message
                    $ontoWiki->prependMessage(new OntoWiki_Message(
                        '<p>Could not instantiate resource. No model selected.</p>' . 
                        '<a href="' . $ontoWiki->config->urlBase . '">Return to index page</a>', 
                        OntoWiki_Message::ERROR, array('escape' => false)));
                    // hard redirect since finishing the dispatch cycle will lead to errors
                    header('Location:' . $ontoWiki->config->urlBase . 'error/error');
                    return;
                }
            }
            
            /**
             * @trigger onRouteShutdown
             */
            $event = new Erfurt_Event('onRouteShutdown');
            $event->request = $request;
            $event->trigger();
            
            // avoid setting up twice
            $this->_isSetup = true;
        }
    }
}

