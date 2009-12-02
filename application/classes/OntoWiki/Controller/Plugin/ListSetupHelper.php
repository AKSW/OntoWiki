<?php
/**
 * ListSetupHelper handles list.
 * reacts on parameters prior ComponentHelper instantiation
 *
 *
 * @author jonas
 */
class OntoWiki_Controller_Plugin_ListSetupHelper extends Zend_Controller_Plugin_Abstract {
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
        $ontoWiki        = OntoWiki::getInstance();
        
        // only once and only when possible
        if (!$this->_isSetup &&
            $ontoWiki->selectedModel instanceof Erfurt_Rdf_Model &&
            (
                /*
                 *these are paramters that change the list
                 */
                isset($request->init) ||
                isset($request->instancesconfig) ||
                isset($request->s) ||
                isset($request->class) ||
                isset($request->p) ||
                isset($request->limit)
            )
        ) {
           
            $frontController = Zend_Controller_Front::getInstance();
            $store           = $ontoWiki->erfurt->getStore();
            $resource = $ontoWiki->selectedResource;
            $session = $ontoWiki->session;

            // when switching to another class:
            // reset session vars (regarding the instancelist)
            if (isset($request->init)) {
                //echo 'kill list session';
                // reset the instances object
                unset($session->instances);

                //reset config from tag explorer
                unset($session->cloudproperties);

                //these should be inside $session->instances as well...
                //TODO: eliminate usage of them, then delete the next 5 lines
                unset($session->shownProperties);
                unset($session->shownInverseProperties);
                unset($session->filter);
                unset($session->instancelimit);
                unset($session->instanceoffset);
            }


            if (!isset($session->instances) || // nothing build yet
                isset($request->init) // force a rebuild
            ) {
                // instantiate model
                $instances   = new OntoWiki_Model_Instances($store, $ontoWiki->selectedModel, array());
            } else {
                // use the object from the session
                $instances = $session->instances;
            }

            //a shortcut for s param
            if(isset($request->s)){
                if(isset($request->instancesconfig)){
                    $config = json_decode(stripslashes($request->instancesconfig));
                    if ($config == false) {
                        throw new OntoWiki_Exception('Invalid parameter instancesconfig (json_decode failed): ' . $this->_request->setup);
                        exit;
                    }
                } else {
                    $config = new stdClass();
                }
                if(!isset($config->filter)){
                    $config->filter = array();
                }
                $config->filter[] = array(
                    'action' => 'add',
                    'mode' => 'search',
                    'searchText' => $request->s
                );
                $request->setParam('instancesconfig', json_encode($config));
            }
            //a shortcut for class param
            if(isset($request->class)){
                if(isset($request->instancesconfig)){
                    $config = json_decode(stripslashes($request->instancesconfig));
                    if ($config == false) {
                        throw new OntoWiki_Exception('Invalid parameter instancesconfig (json_decode failed): ' . $this->_request->setup);
                        exit;
                    }
                } else {
                    $config = new stdClass();
                }
                if(!isset($config->filter)){
                    $config->filter = array();
                }
                $config->filter[] = array(
                    'action' => 'add',
                    'mode' => 'rdfsclass',
                    'rdfsclass' => $request->class
                );
                $request->setParam('instancesconfig', json_encode($config));
            }

            //check for change-requests
            if (isset($request->instancesconfig)) {
                $config = json_decode(stripslashes($request->instancesconfig));
                if ($config == false) {
                    throw new OntoWiki_Exception('Invalid parameter instancesconfig (json_decode failed): ' . $this->_request->setup);
                    exit;
                }
                if (isset($config->shownProperties)) {
                    foreach ($config->shownProperties as $prop) {
                        if ($prop->action == 'add') {
                            $instances->addShownProperty($prop->uri, $prop->label, $prop->inverse);
                        } else {
                            $instances->removeShownProperty($prop->uri.'-'.$prop->inverse);
                        }
                    }
                }

                if (isset($config->filter)) {
                    foreach ($config->filter as $filter) {
                        if ($filter->action == 'add') {
                            if($filter->mode == 'box'){
                                $instances->addFilter(
                                    $filter->property,
                                    $filter->isInverse,
                                    $filter->propertyLabel,
                                    $filter->filter,
                                    $filter->value1,
                                    $filter->value2,
                                    $filter->valuetype,
                                    $filter->literaltype,
                                    $filter->hidden,
                                    isset($filter->id) ? $filter->id : null
                                );
                            } else if($filter->mode == 'search'){
                                $instances->addSearchFilter(
                                    $filter->searchText,
                                    isset($filter->id) ? $filter->id : null
                                );
                            } else if($filter->mode == 'rdfsclass') {
                                $instances->addTypeFilter(
                                    $filter->rdfsclass,
                                    isset($filter->id) ? $filter->id : null
                                );
                            } else if($filter->mode == 'cnav') {
                                $instances->addTripleFilter(
                                    NavigationHelper::getInstancesTriples($filter->uri, $filter->cnav),
                                    isset($filter->id) ? $filter->id : null
                                );
                            }
                        } else {
                            $instances->removeFilter($filter->id);
                        }
                    }
                }
            }

            if (isset($request->limit)){ // how many results per page
                $instances->setLimit($request->limit);
            } else {
                $instances->setLimit(10);
            }
            if (isset($request->p)){ // p is the page number
                $instances->setOffset(
                    ($request->p * $instances->getLimit()) - $instances->getLimit()
                );
            } else {
                $instances->setOffset(0);
            }

            // even if the was no change made to the query -> update it (especially the value-query)
            // because the dataset may have changed since the last request
            $instances->invalidate();
            $instances->updateValueQuery();

            if($request->savelist != "false"){
                //save to session
                $session->instances = $instances;
            }

            // avoid setting up twice
            $this->_isSetup = true;
            
            //redirect normal requests if config-params are given to a param-free uri (so a later browser reload does nothing)
            if(!isset($request->isAjax)){
                header('Location:' . $ontoWiki->config->urlBase . 'list');
                exit;
            }
        }
    }
}
?>
