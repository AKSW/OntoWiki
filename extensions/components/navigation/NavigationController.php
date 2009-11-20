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
            $query = $this->_buildQuery($setup);
        }
        
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
        
        if($showImplicit){
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
        //$this->_owApp->logger->info("\n\n\n".print_r($results,true));
    
        if ( isset($setup->config->titleMode) ){
            $mode = $setup->config->titleMode;
        } else {
            $mode = null;
        }
        
        if ($mode == "titleHelper"){
            $this->titleHelper = new OntoWiki_Model_TitleHelper($this->model);
            if (is_array($results)){
                foreach ($results as $result) {
                    $this->titleHelper->addResource($result['resourceUri']);
                }
            }
        }

        $entries = array();
        
        if ($results == null) return;

        foreach ($results as $result) {
            $uri = $result['resourceUri'];
            $entry = array();
            $entry['title'] = $this->_getTitle($uri, $mode, $setup);
            $entry['link'] = $this->_getListLink($uri, $setup);
            $entries[$uri] = $entry;
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
                if( in_array($uri, $counted) ) continue;
                
                $query = NavigationHelper::buildQuery($uri, $setup);
                $query->setCountStar(true);
            
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
        $searchVar = new Erfurt_Sparql_Query2_Var('resourceUri'); 
        $classVar = new Erfurt_Sparql_Query2_Var('classUri'); 
        $query = new Erfurt_Sparql_Query2();
        $query->setDistinct();
        $query->addProjectionVar($searchVar);
        
        // if deeper query
        if ( isset($setup->state->parent) ) {
            // in relations
            if ( isset($setup->config->hierarchyRelations->in) ){ 
                if( count($setup->config->hierarchyRelations->in) > 1 ){
                    // init union var
                    $union = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
                    // parse config gile
                    foreach($setup->config->hierarchyRelations->in as $rel){
                        // set type
                        $u1 = new Erfurt_Sparql_Query2_GroupGraphPattern();
                        // add triplen
                        $u1->addTriple( $searchVar, 
                            new Erfurt_Sparql_Query2_IriRef($rel), 
                            new Erfurt_Sparql_Query2_IriRef($setup->state->parent) );
                        // add triplet to union var
                        $union->addElement($u1);
                    }
                    $query->addElement($union);
                    //$query->addFilter( new Erfurt_Sparql_Query2_bound( new Erfurt_Sparql_Query2_Var('instance') ) );
                }else{
                    $rel = $setup->config->hierarchyRelations->in;
                    $query->addTriple( $searchVar, 
                            new Erfurt_Sparql_Query2_IriRef($rel[0]), 
                            new Erfurt_Sparql_Query2_IriRef($setup->state->parent) );    
                }
            }
            
            // out relations
            if ( isset($setup->config->hierarchyRelations->out) ){
                if ( count($setup->config->hierarchyRelations->out) > 1 ){
                    // init union var
                    $union = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
                    // parse config gile
                    foreach($setup->config->hierarchyRelations->out as $rel){
                        // set type
                        $u1 = new Erfurt_Sparql_Query2_GroupGraphPattern();
                        // add triplen
                        $u1->addTriple( new Erfurt_Sparql_Query2_IriRef($setup->state->parent), 
                            new Erfurt_Sparql_Query2_IriRef($rel), 
                            $searchVar );
                        // add triplet to union var
                        $union->addElement($u1);
                    }
                    $query->addElement($union);
                    //$query->addFilter( new Erfurt_Sparql_Query2_bound( new Erfurt_Sparql_Query2_Var('instance') ) );
                }else{
                    $rel = $setup->config->hierarchyRelations->out;
                    $query->addTriple( new Erfurt_Sparql_Query2_IriRef($setup->state->parent), 
                            new Erfurt_Sparql_Query2_IriRef($rel[0]), 
                            $searchVar );    
                }
            }
            
        }else{ // if default request
            
            if(!$forImplicit){
                // set hierarchy types
                $query->addTriple( $searchVar, new Erfurt_Sparql_Query2_IriRef(EF_RDF_TYPE), $classVar );
                // create filter for types
                $filter_type = array();
                foreach ($setup->config->hierarchyTypes as $type) {
                    $filter_type[] = new Erfurt_Sparql_Query2_sameTerm($classVar, new Erfurt_Sparql_Query2_IriRef($type));
                }
                // add filter
                $query->addFilter(
                    new Erfurt_Sparql_Query2_ConditionalOrExpression($filter_type)
                );
            }else{
                // define subvar
                $subVar = new Erfurt_Sparql_Query2_Var('sub'); 
                // init union var
                $union = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
                // parse config
                if( isset($setup->config->instanceRelation->out) ){
                    foreach($setup->config->instanceRelation->out as $rel){
                        // create new graph pattern
                        $u1 = new Erfurt_Sparql_Query2_GroupGraphPattern();
                        // add triplen
                        $u1->addTriple( $subVar,
                            new Erfurt_Sparql_Query2_IriRef($rel),//EF_RDF_TYPE),
                            $searchVar
                        );
                        // add triplet to union var
                        $union->addElement($u1);
                    }
                }
                // parse config
                if( isset($setup->config->instanceRelation->in) ){
                    foreach($setup->config->instanceRelation->in as $rel){
                        // create new graph pattern
                        $u1 = new Erfurt_Sparql_Query2_GroupGraphPattern();
                        // add triplen
                        $u1->addTriple( $searchVar,
                            new Erfurt_Sparql_Query2_IriRef($rel),//EF_RDF_TYPE),
                            $subVar
                        );
                        // add triplet to union var
                        $union->addElement($u1);
                    }
                }
                $query->addElement($union);
            }
            
        }
        
        
        $query->addFilter(
            new Erfurt_Sparql_Query2_isUri( 
                new Erfurt_Sparql_Query2_Var('resourceUri') 
            )
        );
        
        // namespaces to be ignored, rdfs/owl-defined objects
        if ( !isset($setup->state->showHidden) ) {
            
            $this->_owApp->logger->info("\nset hidden relation\n\n");
            
            if( isset($setup->config->hiddenRelation) ){
                // optional var
                $queryOptional = new Erfurt_Sparql_Query2_OptionalGraphPattern();
                // parse config
                foreach ($setup->config->hiddenRelation as $ignore) {
                    $queryOptional->addTriple( new Erfurt_Sparql_Query2_Var('resourceUri'),
                        new Erfurt_Sparql_Query2_IriRef($ignore),
                        new Erfurt_Sparql_Query2_Var('reg') );
                }
                $query->addElement($queryOptional);
                $query->addFilter(
                    new Erfurt_Sparql_Query2_UnaryExpressionNot(
                        new Erfurt_Sparql_Query2_bound( 
                            new Erfurt_Sparql_Query2_Var('reg') 
                        )
                    )
                );
            }
            
            if( isset($setup->config->hiddenNS) ){
                // parse config
                foreach ($setup->config->hiddenNS as $ignore) {
                    $query->addFilter(
                        new Erfurt_Sparql_Query2_UnaryExpressionNot(
                            new Erfurt_Sparql_Query2_Regex(
                                new Erfurt_Sparql_Query2_Str( new Erfurt_Sparql_Query2_Var('resourceUri') ), 
                                new Erfurt_Sparql_Query2_RDFLiteral('^' . $ignore)
                            )
                        )
                    );
                }
            }
        }
        
        // dont't show rdfs/owl entities and subtypes in the first level
        if ( !isset($setup->state->parent) ) {
            // optional var
            $queryOptional = new Erfurt_Sparql_Query2_OptionalGraphPattern();
            if( isset($setup->config->hierarchyRelations->in) ){
                foreach($setup->config->hierarchyRelations->in as $rel){
                    $queryOptional->addTriple( new Erfurt_Sparql_Query2_Var('resourceUri'),
                        new Erfurt_Sparql_Query2_IriRef($rel),
                        new Erfurt_Sparql_Query2_Var('super') );
                }
            }
            if( isset($setup->config->hierarchyRelations->out) ){
                foreach($setup->config->hierarchyRelations->out as $rel){
                    $queryOptional->addTriple( new Erfurt_Sparql_Query2_Var('super'),
                        new Erfurt_Sparql_Query2_IriRef($rel),
                        new Erfurt_Sparql_Query2_Var('resourceUri') );
                }
            }
            /*$queryOptional->addFilter(
                new Erfurt_Sparql_Query2_isUri( 
                    new Erfurt_Sparql_Query2_Var('super') 
                )
            );*/
            
            $query->addElement($queryOptional);
            
            $filter[] = new Erfurt_Sparql_Query2_Regex(
                            new Erfurt_Sparql_Query2_Str( new Erfurt_Sparql_Query2_Var('super') ), 
                            new Erfurt_Sparql_Query2_RDFLiteral('^'.EF_OWL_NS )
                        );
            $filter[] = new Erfurt_Sparql_Query2_UnaryExpressionNot(
                            new Erfurt_Sparql_Query2_bound( 
                                new Erfurt_Sparql_Query2_Var('super') 
                            )
                        );
            
            $query->addFilter(
                new Erfurt_Sparql_Query2_ConditionalOrExpression($filter)
            );
        }
        
        
        // set ordering
        if( isset($setup->config->ordering->relation) ){
            $query->order = new Erfurt_Sparql_Query2_OrderClause();
            $query->order->add( new Erfurt_Sparql_Query2_IriRef($setup->config->ordering->relation),
                    $setup->config->ordering->modifier);
        }

        // set to limit+1, so we can see if there are more than $limit entries
        $query->setLimit($this->limit + 1);
        
        
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
        $return .= "?init&r=" . urlencode(OntoWiki_Utils::contractNamespace($uri));

        // shortcut for classes
        if ( isset($setup->config->instanceRelation) && ( $setup->config->instanceRelation->out[0] == EF_RDF_TYPE) &&
            ($setup->config->hierarchyRelations->in[0] == EF_RDFS_SUBCLASSOF ) ) {
            //nothing to do
            return $return;
        }
        
        //pass config as get
        $return .= "&cnav=" . urlencode(json_encode($setup));
        
        return $return;
        
    }

}