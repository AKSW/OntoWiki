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

        // overwrite the limit with a given one
        if (isset($this->setup->state->limit)) {
            $this->limit = $this->setup->state->limit;
        }
        
        $this->view->entries = $this->queryNavigationEntries($this->setup);

        // set view variable for the show more button
        if (count($this->view->entries) > $this->limit) {
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
            $this->view->rootEntry['title'] = $this->getTitle(
                $this->setup->state->parent,
                $this->setup->config->titleMode);
        }


        $this->view->messages = $this->messages;
        $this->view->setup = $this->setup;
        return;
    }

    /*
     * Queries all navigation entries according to a given setup
     */
    protected function queryNavigationEntries($setup) {
        $this->_owApp->logger->info(print_r($setup,true));
        
        // used for generating internal OntoWiki Links
        $linkurl = new OntoWiki_Url(array('route' => 'properties'), array('r'));

        if( $setup->state->lastEvent == "search" ){
            // search request
            // @todo: also search request should not show ignored entities
            $query = $this->store->findResourcesWithPropertyValue($setup->state->searchString, (string) $this->model);
            // Init query
            $union = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
            foreach ($setup->config->hierarchyTypes as $type) {
                $u1 = new Erfurt_Sparql_Query2_GroupGraphPattern();
                $u1->addTriple( new Erfurt_Sparql_Query2_Var('resourceUri'), 
                        new Erfurt_Sparql_Query2_IriRef(EF_RDF_TYPE), 
                        new Erfurt_Sparql_Query2_IriRef($type) );
                $union->addElement($u1);
            }
            $query->addElement($union);
            
        }else{
            $query = $this->buildQuery($setup);
        }
        
        // error logging
        $this->_owApp->logger->info("query: ".$query->__toString());
        
        $results = $this->model->sparqlQuery($query);
    
        // log results
        //$this->_owApp->logger->info("\n\n\n".print_r($results,true));     
    
        if( isset($setup->config->titleMode) ){ 
            $mode = $setup->config->titleMode;
        }else{ 
            $mode = null;
        }
        
        if($mode == "titleHelper"){
            $this->titleHelper = new OntoWiki_Model_TitleHelper($this->model);
            foreach ($results as $result) {
                $this->titleHelper->addResource($result['resourceUri']);
            }
        }
        
        $entries = array();
        
        if ($results == null) return;

        foreach ($results as $result) {
            $uri = $result['resourceUri'];
            $entry = array();
            $entry['title'] = $this->getTitle($uri, $mode);
            $entry['link'] = (string) $linkurl->setParam('r', $uri, true);
            $entries[$uri] = $entry;
        }

        return $entries;
    }
    
    protected function getTitle($uri, $mode){
        if (!isset($mode) || $mode == null) $mode = "baseName";

        if ($mode == "titleHelper") {
            return $this->titleHelper->getTitle($uri);
        } elseif($mode == "baseName"){
            if (strrpos($uri, '#') > 0) {
                return substr($uri, strrpos($uri, '#')+1);
            } elseif (strrpos($uri, '/') > 0) {
                return substr($uri, strrpos($uri, '/')+1);
            } else {
                return $uri;
            }
        } else {
            return "error";   
        }
    }
    
    protected function buildQuery($setup){
        require_once 'Erfurt/Sparql/Query2.php';
        
        $searchVar = new Erfurt_Sparql_Query2_Var('resourceUri'); 
        $query = new Erfurt_Sparql_Query2();
        $query->setDistinct();
        $query->addProjectionVar($searchVar);
        
        // if deeper query
        if ( isset($setup->state->parent) ) {
            // init union var
            $union = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
            // parse config gile
            foreach($setup->config->hierarchyRelations->in as $rel){
                // set type
                if ($rel == "http://www.w3.org/2000/01/rdf-schema#subClassOf") {
                    $u2 = new Erfurt_Sparql_Query2_GroupGraphPattern();
                    $u2->addTriple( new Erfurt_Sparql_Query2_Var('resourceUri'),
                        new Erfurt_Sparql_Query2_IriRef(EF_RDF_TYPE), 
                        new Erfurt_Sparql_Query2_IriRef($setup->state->parent) );
                    /*$u2->addTriple( new Erfurt_Sparql_Query2_Var('instance'), 
                        new Erfurt_Sparql_Query2_IriRef(EF_RDF_TYPE), 
                        new Erfurt_Sparql_Query2_Var('resourceUri') );*/
                    $union->addElement($u2);
                }
                // create new graph pattern
                $u1 = new Erfurt_Sparql_Query2_GroupGraphPattern();
                // add triplen
                $u1->addTriple( new Erfurt_Sparql_Query2_Var('resourceUri'), 
                    new Erfurt_Sparql_Query2_IriRef($rel), 
                    new Erfurt_Sparql_Query2_IriRef($setup->state->parent) );
                $u1->addTriple( new Erfurt_Sparql_Query2_Var('instance'), 
                    new Erfurt_Sparql_Query2_IriRef($rel), 
                    new Erfurt_Sparql_Query2_Var('resourceUri') );
                // add triplet to union var
                $union->addElement($u1);
            }
            $query->addElement($union);
            $query->addFilter( new Erfurt_Sparql_Query2_bound( new Erfurt_Sparql_Query2_Var('instance') ) );
            
        }else{ // if default request
            
            // init union var
            $union = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
            // set hierarchy types
            foreach ($setup->config->hierarchyTypes as $type) {
                // create new graph pattern
                $u1 = new Erfurt_Sparql_Query2_GroupGraphPattern();
                $u1->addTriple( new Erfurt_Sparql_Query2_Var('resourceUri'), 
                    new Erfurt_Sparql_Query2_IriRef(EF_RDF_TYPE), 
                    new Erfurt_Sparql_Query2_IriRef($type) );
                // add triplet to union var
                $union->addElement($u1);    
            }
            $query->addElement($union);
        
            // setup hierarchy relations
            if ( !isset($setup->state->parent) ) {
                // init union var
                $union = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
                // parse config
                foreach($setup->config->hierarchyRelations->in as $rel){
                    // set type
                    if ($rel == "http://www.w3.org/2000/01/rdf-schema#subClassOf") {
                        // create new graph pattern
                        $u1 = new Erfurt_Sparql_Query2_GroupGraphPattern();
                        // add triplen
                        $u1->addTriple( new Erfurt_Sparql_Query2_Var('instance'), 
                            new Erfurt_Sparql_Query2_IriRef(EF_RDF_TYPE), 
                            new Erfurt_Sparql_Query2_Var('resourceUri') );
                        // add triplet to union var
                        $union->addElement($u1);
                    }
                }
                $query->addElement($union);
            }
            
            // setup relations
            if ( isset($setup->config->instanceRelation) ){
                // init union var
                $union = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
                // parse config
                foreach($setup->config->instanceRelation->out as $rel){
                    // create new graph pattern
                    $u1 = new Erfurt_Sparql_Query2_GroupGraphPattern();
                    $u1->addTriple( new Erfurt_Sparql_Query2_Var('resourceUri'), 
                        new Erfurt_Sparql_Query2_IriRef($rel), 
                        new Erfurt_Sparql_Query2_Var('subtype') );
                    // add triplet to union var
                    $union->addElement($u1);
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
            foreach($setup->config->hierarchyRelations->in as $rel){
                $queryOptional->addTriple( new Erfurt_Sparql_Query2_Var('resourceUri'),
                    new Erfurt_Sparql_Query2_IriRef($rel),
                    new Erfurt_Sparql_Query2_Var('super') );
            }
            $queryOptional->addFilter(
                new Erfurt_Sparql_Query2_isUri( 
                    new Erfurt_Sparql_Query2_Var('super') 
                )
            );
            
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

        // set to limit+1, so we can see if there are more than $limit entries
        $query->setLimit($this->limit + 1);
        
        
        return $query;
    } 
}
