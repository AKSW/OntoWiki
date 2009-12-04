<?php
/**
 * Controller for OntoWiki Navigation Module
 *
 * @category   OntoWiki
 * @package    extensions_components_navigation
 * @author     Sebastian Dietzold <dietzold@informatik.uni-leipzig.de>
 * @copyright  Copyright (c) 2009, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
 
require_once 'Erfurt/Sparql/Query2.php';

class NavigationController extends OntoWiki_Controller_Component
{
    private $store;
    private $translate;
    private $session;
    private $ac;
    private $model;
    /* an array of arrays, each has type and text */
    private $messages = array();
    /* the setup consists of state and config */
    private $setup = null;
    private $limit = 50;

    public function init()
    {
        parent::init();
        $this->store = $this->_owApp->erfurt->getStore();
        $this->translate = $this->_owApp->translate;
        $this->session = $this->_owApp->session->navigation;
        $this->ac = $this->_erfurt->getAc();
        $this->stateSession = new Zend_Session_Namespace("NavigationState");

        $this->model = $this->_owApp->selectedModel;
        if (isset($this->_request->m)) {
            $this->model = $store->getModel($this->_request->m);
        }
        if (empty($this->model)) {
            throw new OntoWiki_Exception('Missing parameter m (model) and no selected model in session!');
            exit;
        }
        // Model Based Access Control
        if (!$this->ac->isModelAllowed('view', $this->model->getModelIri()) ) {
            throw new Erfurt_Ac_Exception('You are not allowed to read this model.');
        }
    }

    /*
     * The main action which is retrieved via json
     */
    public function exploreAction() {
        OntoWiki_Navigation::disableNavigation();
        $this->_owApp->logger->info('stage 1');
        $this->view->placeholder('main.window.title')
            ->set($this->translate->_('Navigation'));

        if (empty($this->_request->setup)) {
            throw new OntoWiki_Exception('Missing parameter setup !');
            exit;
        }
        $this->setup = json_decode($this->_request->getParam('setup'));
        
        if ($this->setup == false) {
            throw new OntoWiki_Exception('Invalid parameter setup (json_decode failed): ' . $this->_request->setup);
            exit;
        }

        // overwrite the hard limit with the given one
        if (isset($this->setup->state->limit)) {
            $this->limit = $this->setup->state->limit;
        }
        
        $this->view->entries = $this->_queryNavigationEntries($this->setup);
        
        if( $this->setup->state->lastEvent == 'more' ){
            $this->view->showRoot = false;
        }
        
        // set view variable for the show more button
        if (count($this->view->entries) > $this->limit) {
            // return only $limit entries
            $this->view->entries = array_slice($this->view->entries, 0, $this->limit);
            $this->view->showMeMore = true;
        } else {
            $this->view->showMeMore = false;
        }

        if (empty($this->view->entries)) {
            if (isset($this->setup->state->searchString)) {
                $this->messages[] = array( 'type' => 'info', 'text' => 'No result for this search.');
            } else {
                $this->messages[] = array( 'type' => 'info', 'text' => 'Nothing to navigate here.');
            }
        }

        // the root entry (parent of the shown elements)
        if ( isset($this->setup->state->parent) ) {
            $this->view->rootEntry = array();
            $this->view->rootEntry['uri'] = $this->setup->state->parent;
            $this->view->rootEntry['title'] = $this->_getTitle(
                $this->setup->state->parent,
                isset($this->setup->config->titleMode) ? $this->setup->config->titleMode : null,
                null
            );
        }

        if (isset($this->setup->state->searchString)) {
            $this->view->searchString = $this->setup->state->searchString;
        }

        $this->view->messages = $this->messages;
        $this->view->setup = $this->setup;
        
        return;
    }
    
    public function savestateAction(){
        OntoWiki_Navigation::disableNavigation();
        $view = $this->_request->view;
        $setup = $this->_request->setup;
        
        $replaceFrom = array("\\'", '\\"');
        $replaceTo = array("'", '"');
        $view = str_replace($replaceFrom, $replaceTo, $view);
        $setup = str_replace($replaceFrom, $replaceTo, $setup);
        
        $this->stateSession->view = $view;
        $this->stateSession->setup = $setup;
        $this->stateSession->model = (string)$this->model;
    }

    /*
     * Queries all navigation entries according to a given setup
     */
    protected function _queryNavigationEntries($setup) {
        $this->_owApp->logger->info(print_r($setup,true));
        
        if( $setup->state->lastEvent == "search" ){
            // search request
            // @todo: also search request should not show ignored entities
            $resVar = new Erfurt_Sparql_Query2_Var('resourceUri');
            $typeVar = new Erfurt_Sparql_Query2_IriRef(EF_RDF_TYPE);
            
            $query = new Erfurt_Sparql_Query2();
            $query->addProjectionVar($resVar)->setDistinct(true);

            $pattern = $this->store->getSearchPattern($setup->state->searchString, (string) $this->model);
            $query->addElements($pattern);
            
            $union = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
            
            foreach ($setup->config->hierarchyTypes as $type) {
                $u1 = new Erfurt_Sparql_Query2_GroupGraphPattern();
                $u1->addTriple( $resVar,
                    $typeVar,
                    new Erfurt_Sparql_Query2_IriRef($type)
                );
                $union->addElement($u1);
            }
            $query->addElement($union);
            
            // set to limit+1, so we can see if there are more than $limit entries
            $query->setLimit($this->limit + 1);
            
        }else{
            if ( !isset($setup->config->hideDefaultHierarchy) || $setup->config->hideDefaultHierarchy == false ){
                $query = $this->_buildQuery($setup, false);
            }else{
                $query = null;
            }
        }
        
        if($query == null) return;
        
        // error logging
        $this->_owApp->logger->info("query: ".$query->__toString());
        
        $results = $this->model->sparqlQuery($query);
               
        // if we need to show implicit elements
        $showImplicit = false;
        if(!isset($setup->state->showImplicit)){
            if(isset($setup->config->showImplicitElements) && $setup->config->showImplicitElements == true ){
                $showImplicit = true;
            }
        }else{ 
            if($setup->state->showImplicit == true){
                $showImplicit = true;
            }
        }
        
        if($showImplicit &&  $setup->state->lastEvent != "search"){
            $query = $this->_buildQuery($setup, true);
            $results_implicit = $this->model->sparqlQuery($query);
            
            // append implicit classes to results
            foreach($results_implicit as $res){
                if( !in_array($res, $results) ){
                    $results[] = $res;
                }
            }
        }
            
        // log results
        $this->_owApp->logger->info("\n\n\n".print_r($results,true));
    
        if ( isset($setup->config->titleMode) ){
            $mode = $setup->config->titleMode;
        } else {
            $mode = null;
        }
        
        if ($mode == "titleHelper"){
            //$this->_owApp->logger->info('TITLE HELPER.');
            //$this->_owApp->logger->info('TITLE HELPER REs: '.print_r($results,true));
            $this->titleHelper = new OntoWiki_Model_TitleHelper($this->model);
            if (isset($results)){
                foreach ($results as $result) {
                    //$this->_owApp->logger->info('TITLE HELPER: '.$result['resourceUri']);
                    $this->titleHelper->addResource($result['resourceUri']);
                }
            }
            // add parent to titleHelper to get title
            if( isset($setup->state->parent) ){
                $this->titleHelper->addResource($setup->state->parent);
            }
        }

        $entries = array();
        
        if ($results == null) return;

        foreach ($results as $result) {
            $uri = $result['resourceUri'];
            $entry = array();
            $entry['title'] = $this->_getTitle($uri, $mode, $setup);
            $entry['link'] = $this->_getListLink($uri, $setup);
            
            // if filtering empty is needed
            $filterEmpty = false;
            if(!isset($setup->state->showEmpty)){
                if(isset($setup->config->showEmptyElements) && $setup->config->showEmptyElements == false ){
                    $filterEmpty = true;
                }
            }else{ 
                if($setup->state->showEmpty == false){
                    $filterEmpty = true;
                }
            }
            // do filter
            $show = true;
            if( $filterEmpty ){
                $modelIRI = $this->model->getModelIri();
        
                // get all subclass of the super class
                $classes = array();
                if( isset($setup->config->hierarchyRelations->out) ){
                    foreach($setup->config->hierarchyRelations->out as $rel){
                        $classes += $this->store->getTransitiveClosure($modelIRI, $rel, $uri, false);
                    }
                }
                if( isset($setup->config->hierarchyRelations->in) ){
                    foreach($setup->config->hierarchyRelations->in as $rel){
                        $classes += $this->store->getTransitiveClosure($modelIRI, $rel, $uri, true);
                    }
                }
                
                //$this->_owApp->logger->info("array: ".print_r($classes,true));
            
                $count = 0;
                $counted = array();
                foreach($classes as $class){
                    // get uri
                    $uri = ($class['parent'] != '')?$class['parent']:$class['node'];
                
                    // if this class is already counted - continue
                    if( in_array($uri, $counted) ) {
                        if( $class['node'] != '' ){
                            $uri = $class['node'];
                            if( in_array($uri, $counted) )
                                continue;
                        }else{
                            continue;
                        }
                    }
                
                    $query = $this->_buildCountQuery($uri, $setup);
                
                    //$this->_owApp->logger->info('EMPTY QUERY: '.$query);
                
                    $results = $this->model->sparqlQuery($query);
                    
                    //$this->_owApp->logger->info('EMPTY RES: '.print_r($results,true));
                    
                    if( isset($results[0]['callret-0']) ){
                        $count += $results[0]['callret-0'];
                    }else{
                        $count += count($results);
                    }
                    
                    // add uri to counted
                    $counted[] = $uri;
                }
                if($count == 0) $show = false;
            }
            
            if($show) $entries[$uri] = $entry;
        }
        
        return $entries;
    }
    
    protected function _getTitle($uri, $mode, $setup){
        $name = '';
        // set default mode if none is set
        if (!isset($mode) || $mode == null) $mode = "baseName";

        // get title
        if ($mode == "titleHelper") {
            $name = $this->titleHelper->getTitle($uri);
        } elseif($mode == "baseName"){
            if (strrpos($uri, '#') > 0) {
                $name = substr($uri, strrpos($uri, '#')+1);
            } elseif (strrpos($uri, '/') > 0) {
                $name = substr($uri, strrpos($uri, '/')+1);
            } else {
                $name = $uri;
            }
        } else {
            $name = 'error';
        }
        
        // count entries
        if( isset($setup->config->showCounts) && $setup->config->showCounts == true ){            
            $modelIRI = $this->model->getModelIri();
        
            // get all subclass of the super class
            $classes = array();
            if( isset($setup->config->hierarchyRelations->out) ){
                foreach($setup->config->hierarchyRelations->out as $rel){
                    $classes += $this->store->getTransitiveClosure($modelIRI, $rel, $uri, false);
                }
            }
            if( isset($setup->config->hierarchyRelations->in) ){
                foreach($setup->config->hierarchyRelations->in as $rel){
                    $classes += $this->store->getTransitiveClosure($modelIRI, $rel, $uri, true);
                }
            }
            
            //$this->_owApp->logger->info("array: ".print_r($classes,true));
            
            $count = 0;
            $counted = array();
            foreach($classes as $class){
                // get uri
                $uri = ($class['parent'] != '')?$class['parent']:$class['node'];
                
                // if this class is already counted - continue
                if( in_array($uri, $counted) ) {
                    if( $class['node'] != '' ){
                        $uri = $class['node'];
                        if( in_array($uri, $counted) )
                            continue;
                    }else{
                        continue;
                    }
                }
                
                $query = $this->_buildCountQuery($uri, $setup);
                //$query->setCountStar(true);
            
                //$this->_owApp->logger->info("count query: ".$query->__toString());
                
                $results = $this->model->sparqlQuery($query);
            
                //$this->_owApp->logger->info("count query results: ".print_r($results,true));
            
                if( isset($results[0]['callret-0']) ){
                    $count += $results[0]['callret-0'];
                }else{
                    $count += count($results);
                }
                
                // add uri to counted
                $counted[] = $uri;
            }
            
            if( $count > 0 ) $name .= ' ('.$count.')';
            //}
        }
        
        return $name;
    }
   
    protected function _buildQuery($setup, $forImplicit = false){
        $query = new Erfurt_Sparql_Query2();
        $query->addElements(NavigationHelper::getSearchTriples($setup, $forImplicit));
        //$query->setCountStar(true);
        $query->setDistinct(true);
        $query->addProjectionVar(new Erfurt_Sparql_Query2_Var('resourceUri'));
        // set to limit+1, so we can see if there are more than $limit entries
        $query->setLimit($this->limit + 1);
        // set ordering
        if( isset($setup->config->ordering->relation) ){
            $query->getOrder()->add( 
                new Erfurt_Sparql_Query2_IriRef($setup->config->ordering->relation),
                $setup->config->ordering->modifier
            );
        }
        
        if( isset($setup->state->offset) && $setup->state->lastEvent == 'more' ){
            $query->setOffset($setup->state->offset);
        }
        
        return $query;
    }
    
    protected function _buildCountQuery($uri, $setup){
        
        //$classVar = new Erfurt_Sparql_Query2_Var('classUri'); // new Erfurt_Sparql_Query2_IriRef($uri)
        $query = new Erfurt_Sparql_Query2();
        $query->setCountStar(true);
        //$query->setDistinct();
        
        $query->addElements(NavigationHelper::getInstancesTriples($uri, $setup));
        //$query->addFilter( new Erfurt_Sparql_Query2_sameTerm($classVar, new Erfurt_Sparql_Query2_IriRef($uri)) );
        
        return $query;
    }

    /*
     * This method returns the link to the resource list action
     * according to a given URI in the navigation module and a
     * given navigation setup
     */
    protected function _getListLink ($uri, $setup) {
        $owUrl = new OntoWiki_Url(array('route' => 'instances'), array());
        $return = (string) $owUrl;

        // at the moment, we use r= here, not class=
        $return .= "?init";
        $conf = array();
        // there is a shortcut for rdfs classes
        if ( !empty($setup->config->instanceRelation->out) && 
            !empty($setup->config->instanceRelation->in) && ( $setup->config->instanceRelation->out[0] == EF_RDF_TYPE) &&
            ($setup->config->hierarchyRelations->in[0] == EF_RDFS_SUBCLASSOF ) ) {
            $conf['filter'][] = array(
                'mode' => 'rdfsclass',
                'rdfsclass' => $uri,
                'action' => 'add'
            );
            return $return . "&instancesconfig=".urlencode(json_encode($conf));
        } else {
            $conf['filter'][] = array(
                'mode' => 'cnav',
                'cnav' => $setup,
                'uri'  => $uri,
                'action' => 'add'
            );
            return $return . "&instancesconfig=" . urlencode(json_encode($conf));
        }
    }

}