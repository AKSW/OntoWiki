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

        $this->view->entries = $this->queryNavigationEntries($this->setup);
        if (empty($this->view->entries)) {
            if ($this->setup->searchString) {
                $this->messages[] = array( 'type' => 'info', 'text' => 'No result for this search.');
            } else {
                $this->messages[] = array( 'type' => 'info', 'text' => 'Nothing to navigate deeper here.');
            }
        }

        $this->view->messages = $this->messages;
        $this->view->setup = $this->setup;
        return;
    }

    /*
     * Queries all navigation entries according to a given setup
     */
    protected function queryNavigationEntries($setup) {
        //$this->_owApp->logger->info(print_r($setup,true));
        
        // used for generating internal OntoWiki Links
        $linkurl = new OntoWiki_Url(array('route' => 'properties'), array('r'));

        if( isset($this->setup->searchString) ){ // search request
            $query = $this->store->findResourcesWithPropertyValue($setup->state->searchString,$this->model->__toString());
        }else{
            $query = $this->buildQuery($setup);
        }
        
        // error logging
        //$this->_owApp->logger->info($query->__toString());
        
        $results = $this->model->sparqlQuery($query);
    
        // log results
        //$this->_owApp->logger->info("\n\n\n".print_r($results,true));     
    
        if( isset($setup->config->titleMode) ){ 
            $mode = $setup->config->titleMode;
        }else{ 
            $mode = null;
        }
        
        $entries = array();
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
        if(!isset($mode) || $mode == null) $mode = "baseName";
        if($mode == "titleHelper"){
            $titleHelper = new OntoWiki_Model_TitleHelper($this->model);
            $titleHelper->addResource($uri);
            return $titleHelper->getTitle($uri);
        }else if($mode == "baseName"){
            if(strrpos($uri, '/') > 0){
                return substr($uri, strrpos($uri, '/')+1);
            }else{
                return $uri;
            }
        }else{
            return "error";   
        }
    }
    
    protected function buildQuery($setup){
        $query = new Erfurt_Sparql_SimpleQuery();
        $prologue = 'SELECT DISTINCT ?resourceUri ';
        $query->setProloguePart($prologue);
        
        $whereSpecs = array();
        
        // deeper qeury
        if ( isset($setup->state->parent) ) {
            
            foreach($setup->config->hierarchyRelations->in as $rel){
                if ($rel == "http://www.w3.org/2000/01/rdf-schema#subClassOf") {
                    $whereSpecs[] = '{?resourceUri a <'.$setup->state->parent.'>.}';
                }
                // entities with a subtype must be a type
                $whereSpecs[] = '{?resourceUri <' . $rel . '> <'.$setup->state->parent.'>.}';
            }
            
        }else{ // toplevel query
                
            // Init query
            foreach ($setup->config->hierarchyTypes as $type) {
                $whereSpecs[] = '{?resourceUri a <' . $type . '>}';
            }
        
            if ( !isset($setup->state->parent) ) {
                foreach($setup->config->hierarchyRelations->in as $rel){
                    if ($rel == "http://www.w3.org/2000/01/rdf-schema#subClassOf") {
                        $whereSpecs[] = '{?instance a ?resourceUri.}';
                    }
                    // entities with a subtype must be a type
                    //$whereSpecs[] = '{?subtype <' . $rel . '> ?resourceUri.}';
                }
            }
        
            // relations
            if ( isset($setup->config->instanceRelation) ){
                foreach($setup->config->instanceRelation->out as $rel){
                    // entities must have a subtype
                    $whereSpecs[] = '{?resourceUri <' . $rel . '> ?subtype.}';
                }        
            }
        }
        
        $whereSpec = implode(' UNION ', $whereSpecs);
        
        // namespaces to be ignored, rdfs/owl-defined objects
        if( isset($setup->config->hiddenRelation) ){
            foreach ($setup->config->hiddenRelation as $ignore) {
                $whereSpec .= ' OPTIONAL { ?resourceUri <' . $ignore . '> ?reg . }';
            }
            $whereSpec .= 'FILTER (!bound(?reg))';
        }

        $whereSpec .= 'FILTER (isURI(?resourceUri))';
        
        // dont't show rdfs/owl entities and subtypes in the first level
        if ( !isset($setup->state->parent) ) {
            $whereSpec .= ' FILTER (regex(str(?super), "^' . EF_OWL_NS . '") || !bound(?super))';
        
            foreach($setup->config->hierarchyRelations->in as $rel){
                $whereSpec .= ' OPTIONAL {?resourceUri <' . $rel . '> ?super. FILTER(isUri(?super))}';
            }
        }
        
        // namespaces to be ignored, rdfs/owl-defined objects
        if ( !isset($setup->state->showHidden)) {
            if( isset($setup->config->hiddenNS) ){
                foreach ($setup->config->hiddenNS as $ignore) {
                    $whereSpec .= ' FILTER (!regex(str(?resourceUri), "^' . $ignore . '"))';
                }
            }
        }
        
        // entry point into the class tree
        if (isset($setup->state->parent)) {
            $whereSpec .= ' FILTER (str(?super) = str(<' . $setup->state->parent . '>))';
        }
        
        $query->setWherePart('WHERE {' . $whereSpec . '}');
        
        $query->setLimit($this->limit);
        return $query;
    } 
}
