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
        $listHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('List');
        // only once and only when possible
        if (!$this->_isSetup &&
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
            // reset session vars (regarding the list)
            if (isset($request->init)) {
                //echo 'kill list session';
                // reset the instances object
                unset($session->instances);

                //reset config from tag explorer
                unset($session->cloudproperties);
            }

            //react on m parameter to set the selected model
            if (isset($request->m)) {
                
                try {
                    $model = $store->getModel($request->getParam('m', null, false));
                    $ontoWiki->selectedModel = $model;
                } catch (Erfurt_Store_Exception $e) {

                }
            }

            $list = $listHelper->getLastList();

            if ((!isset($request->list) && $list == null) || // nothing build yet
                isset($request->init) // force a rebuild
            ) {
                // instantiate model, that selects all resources
                $list = new OntoWiki_Model_Instances($store, $ontoWiki->selectedModel, array());
            } else {
                // use the object from the session
                if(isset($request->list) && $request->list != $listHelper->getLastListName()) {
                    if($listHelper->listExists($request->list)){
                        $list = $listHelper->getList($request->list);
                        $ontoWiki->appendMessage(new OntoWiki_Message("reuse list"));
                    } else {
                        throw new OntoWiki_Exception('your trying to configure a list, but there is no list name specified');
                    }
                }
            }

            //local function :)
            function _json_decode($string) {
            
                /* PHP 5.3 DEPRECATED ; REMOVE IN PHP 6.0 */
                if (get_magic_quotes_gpc()) {
                    // add slashes for unicode chars in json
                    $string = str_replace('\\u','\\\\u',$string);
                    //$string = str_replace('\\u000a','', $string);
                    $string = stripslashes($string);
                }
                /* ---- */
                
                return json_decode($string);
            }
            
            //a shortcut for search param
            if(isset($request->s)){
                if(isset($request->instancesconfig)){
                    $config = _json_decode($request->instancesconfig);
                    if ($config == false) {
                        throw new OntoWiki_Exception('Invalid parameter instancesconfig (json_decode failed): ' . $this->_request->setup);
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
                    $config = _json_decode($request->instancesconfig);
                    if ($config == false) {
                        throw new OntoWiki_Exception('Invalid parameter instancesconfig (json_decode failed): ' . $this->_request->setup);
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
                $config = _json_decode($request->instancesconfig);
                if ($config == false) {
                    throw new OntoWiki_Exception('Invalid parameter instancesconfig (json_decode failed)');
                }

                if (isset($config->shownProperties)) {
                    foreach ($config->shownProperties as $prop) {
                        if ($prop->action == 'add') {
                            $list->addShownProperty($prop->uri, $prop->label, $prop->inverse);
                        } else {
                            $list->removeShownProperty($prop->uri.'-'.$prop->inverse);
                        }
                    }
                }

                if (isset($config->filter)) {
                    foreach ($config->filter as $filter) {
                        // set default value for action and mode if they're not assigned
                        if(!isset($filter->action)) $filter->action = 'add';
                        if(!isset($filter->mode)) $filter->mode = 'box';

                        if ($filter->action == 'add') {
                            if($filter->mode == 'box'){
                                $list->addFilter(
                                    $filter->property,
                                    isset($filter->isInverse) ? $filter->isInverse : false,
                                    isset($filter->propertyLabel) ? $filter->propertyLabel : 'defaultLabel',
                                    $filter->filter,
                                    isset($filter->value1) ? $filter->value1 : null,
                                    isset($filter->value2) ? $filter->value2 : null,
                                    isset($filter->valuetype) ? $filter->valuetype : 'literal',
                                    isset($filter->literaltype) ? $filter->literaltype : null,
                                    isset($filter->hidden) ? $filter->hidden : false,
                                    isset($filter->id) ? $filter->id : null,
                                    isset($filter->negate) ? $filter->negate : false
                                );
                            } else if($filter->mode == 'search'){
                                $list->addSearchFilter(
                                    $filter->searchText,
                                    isset($filter->id) ? $filter->id : null
                                );
                            } else if($filter->mode == 'rdfsclass') {
                                $list->addTypeFilter(
                                    $filter->rdfsclass,
                                    isset($filter->id) ? $filter->id : null
                                );
                            } else if($filter->mode == 'cnav') {
                                $list->addTripleFilter(
                                    NavigationHelper::getInstancesTriples($filter->uri, $filter->cnav),
                                    isset($filter->id) ? $filter->id : null
                                );
                            } else if($filter->mode == 'query') {
                                try{
                                    //echo $filter->query."   ";
                                    $query = Erfurt_Sparql_Query2::initFromString($filter->query);
                                    if(!($query instanceof  Exception)){
                                        $list->addTripleFilter(
                                            $query->getWhere()->getElements(),
                                            isset($filter->id) ? $filter->id : null
                                        );
                                    }
                                    //echo $query->getSparql();
                                } catch (Erfurt_Sparql_ParserException $e){
                                    $ontoWiki->appendMessage("the query could not be parsed");
                                }
                            }
                        } else {
                            $list->removeFilter($filter->id);
                        }
                    }
                }

                if (isset($config->order)) {
                    foreach ($config->order as $prop) {
                        if ($prop->action == 'set') {
                            if ($prop->mode == 'var') {
                                $list->setOrderVar($prop->var);
                            } else {
                                $list->setOrderUri($prop->uri);
                            }
                        }
                    }
                }
            }

            if (isset($request->limit)){ // how many results per page
                $list->setLimit($request->limit);
            } else {
                $list->setLimit(10);
            }
            if (isset($request->p)){ // p is the page number
                $list->setOffset(
                    ($request->p * $list->getLimit()) - $list->getLimit()
                );
            } else {
                $list->setOffset(0);
            }
            
            //save to session
            $name = (isset($request->list) ? $request->list : "instances");
            $listHelper->updateList($name, $list, true);
            
            // avoid setting up twice
            $this->_isSetup = true;
            //redirect normal requests if config-params are given to a param-free uri (so a browser reload by user does nothing unwanted)
            if(!$request->isXmlHttpRequest()){
                //strip of url parameters that modify the list
                $url = new OntoWiki_Url(array(), null, array('init', 'instancesconfig', 's', 'p', 'limit', 'class', 'list'));
                //redirect
                $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
                $redirector->gotoUrl($url);
            }
        }
        
        // even if the was no change made to the resource query -> update the value-query
        // because the dataset may have changed since the last request
        // and controllers using this list then get the newest data
        foreach($listHelper->getAllLists() as $aList){
            $aList->invalidate();
        }
    }
}
?>
