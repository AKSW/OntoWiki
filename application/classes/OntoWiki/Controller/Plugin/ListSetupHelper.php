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
                $request->controller == "resource" &&
                $request->action == "instances"
                /*
                 *TODO: fix here...
                 * we need to separate two usages of r param
                 * use "class" param when the resource is a class
                 *
                 * then this condition can express "true if there is a class param or cnav or init"
                 * (now it triggers only when calling the instances action - as before this plugin)
                 */
            )
        ) {
           
            $frontController = Zend_Controller_Front::getInstance();
            $store           = $ontoWiki->erfurt->getStore();
            $resource = $ontoWiki->selectedResource;
            $session = $ontoWiki->session;

            // when switching to another class:
            // reset session vars (regarding the instancelist)
            if ((string)$resource != $ontoWiki->selectedClass) {
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

            $ontoWiki->selectedClass = (string)$resource;

            if (!isset($session->instances) || // nothing build yet
                isset($request->init) // force a rebuild
            ) {
                $options = array(
                    'mode' => 'all' //default
                );

                //shortcut for "all instances of a rdfs class"
                if(isset($request->r)){
                    if(!isset($request->cnav)){
                        //shortcut navigation - only a rdfs class given
                        $options['mode'] = 'instances';
                        $options['type'] = $ontoWiki->selectedClass; //dont use r here: is a curi
                        $options['memberPredicate'] = EF_RDF_TYPE;
                        $options['withChilds'] = true;

                        $options['hierarchyUp'] = EF_RDFS_SUBCLASSOF;
                        $options['hierarchyIsInverse'] = true;
                        //$options['hierarchyDown'] = null;
                        $options['direction'] = 1; // down the tree
                    } else {
                        // complex nav (from navigation module)
                        $options['mode'] = 'defaultQuery';
                        $conf = json_decode(stripslashes($request->cnav), false);
                        $options['defaultQuery'] = NavigationHelper::buildQuery($ontoWiki->selectedClass, $conf);
                        $options['defaultQuery']->setCountStar(false);
                    }
                }

                if(isset($request->query)){
                    //init with a serialized query2 obj in post
                    //usefull? should be mostly done like: init new list with this param, save to session, goto list page
                    $options['mode'] = 'defaultQuery';
                    $options['defaultQuery'] = unserialize(stripslashes($request->query));
                }

                // instantiate model
                $instances   = new OntoWiki_Model_Instances($store, $ontoWiki->selectedModel, $options);
            } else {
                // use the object from the session
                $instances = $session->instances;
            }

            //check for change-requests
            if (isset($request->instancesconfig)) {
                $config = json_decode(stripslashes($request->instancesconfig));
                //var_dump($config); exit;
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
                            $instances->addFilter(
                                $filter->id,
                                $filter->property,
                                $filter->isInverse,
                                $filter->propertyLabel,
                                $filter->filter,
                                $filter->value1,
                                $filter->value2,
                                $filter->valuetype,
                                $filter->literaltype,
                                $filter->hidden
                            );
                        } else {
                            $instances->removeFilter($filter->id);
                        }
                    }
                }
            }

            if (isset($request->s)) {
                $count = count($instances->getFilter());
                $name = 'search-the-list'. ( $count  > 0 ? $count + 1 : "" );
                $instances->addSearchFilter($name, $request->s);
            }

            if (isset($request->limit)){ //how many results per page
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
            //echo htmlentities($instances->getResourceQuery());
            //echo htmlentities($instances->getQuery());
            //var_dump($instances->getShownResources());
            /**
             * @trigger onRouteShutdown
             */
            $event = new Erfurt_Event('onRouteShutdown');
            $event->request = $request;
            //$event->trigger();

            // avoid setting up twice
            $this->_isSetup = true;
        }
    }
}
?>
