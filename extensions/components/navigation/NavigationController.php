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
    /* the setup consists of state and config */
    private $setup = null;

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
        //$this->view->setup = $this->setup;
        return;
    }

    /*
     * Queries all navigation entries according to a given setup
     */
    protected function queryNavigationEntries($setup) {
        $handle = fopen('navi.log', "w");
		    fwrite($handle, print_r($setup,true));     
		    fclose($handle);
        
        // used for generating internal OntoWiki Links
        $linkurl = new OntoWiki_Url(array('route' => 'properties'), array('r'));

        /*$query = 'SELECT ?navEntry WHERE {
            ?navEntry <'.EF_RDF_TYPE.'> <'.EF_OWL_CLASS.'>.
            FILTER (isURI(?navEntry))
            } LIMIT 10
            ';*/
        $query = $this->buildQuery($setup);
        
        // error logging
    		$handle = fopen('navi.log', "a+");
		    fwrite($handle, $query->__toString());     
		    fclose($handle);
        
        $results = $this->model->sparqlQuery($query);
    
        $handle = fopen('navi.log', "a+");
        fwrite($handle, "\n\n\n".print_r($results,true));     
        fclose($handle);
    
        if( isset($setup->config->titleMode) ){ 
            $mode = $setup->config->titleMode;
        }else{ 
            $mode = null;
        }
        
        $handle = fopen('navi.log', "a+");
        fwrite($handle, "mode = ".$mode);     
        fclose($handle);

        if($mode !== null){
            $titleHelper = new OntoWiki_Model_TitleHelper($this->model);
            foreach ($results as $result) {
                $titleHelper->addResource($result['navEntry']);
            }
        }
        
        $handle = fopen('navi.log', "a+");
        fwrite($handle, "start res");     
        fclose($handle);
        
        $entries = array();
        foreach ($results as $result) {
            $uri = $result['navEntry'];
            $entry = array();
            $entry['title'] = $this->getTitle($uri, $mode);
            $entry['link'] = (string) $linkurl->setParam('r', $uri, true);
            $entries[$uri] = $entry;
        }

        return $entries;
    }
    
    protected function getTitle($uri, $mode){
        //if(!isset($mode) || $mode == null) 
        $mode = "baseName";
        if($mode == "titleHelper"){
            return $titleHelper->getTitle($uri);
        }else if($mode == "baseName"){
            if(strrpos($uri, '/') > 0){
                return substr($uri, strrpos($uri, '/')+1);
            }else{
                return $uri;
            }
        }
    }
    
    protected function buildQuery($setup){
        $query = new Erfurt_Sparql_SimpleQuery();
        $prologue = 'SELECT DISTINCT ?navEntry ';// ?sub ?subsub ?subsubsub ';
        $query->setProloguePart($prologue);
        
        $whereSpecs = array();
        
        // deeper qeury
        if ( isset($setup->state->parent) && ($setup->state->lastEvent == "navigateDeeper") ) {
            $whereSpec = '?navEntry a <'.$setup->state->parent.'>. ';
            $whereSpec .= ' FILTER (isURI(?navEntry))';
            $query->setWherePart('WHERE {' . $whereSpec . '}');
            $query->setLimit(10);;        
            return $query;
        }
        
        // Init query
        foreach ($setup->config->hierarchyTypes as $type) {
            $whereSpecs[] = '{?navEntry a <' . $type . '>}';
        }
        
        if ( !isset($setup->state->parent) ) {
            foreach($setup->config->hierarchyRelations->in as $rel){
                if ($rel == "http://www.w3.org/2000/01/rdf-schema#subClassOf") {
                    $whereSpecs[] = '{?instance a ?navEntry.}';
                }
                // entities with a subtype must be a type
                $whereSpecs[] = '{?subtype <' . $rel . '> ?navEntry.}';
            }
        }
        
        $whereSpec = implode(' UNION ', $whereSpecs);
        $whereSpec .= ' FILTER (isURI(?navEntry))';
        
        // dont't show rdfs/owl entities and subtypes in the first level
        if ( !isset($setup->state->parent) ) {
            $whereSpec .= ' FILTER (regex(str(?super), "^' . EF_OWL_NS . '") || !bound(?super))';
        }
        foreach($setup->config->hierarchyRelations->in as $rel){
            /*$whereSpec .= ' OPTIONAL {?sub <' . $rel . '> ?navEntry.
                                OPTIONAL {?subsub <' . $rel . '> ?sub.
                                      OPTIONAL {?subsubsub <' . $rel . '> ?subsub}
                                }
                            }';*/
            $whereSpec .= ' OPTIONAL {?navEntry <' . $rel . '> ?super. FILTER(isUri(?super))}';
        }
        
        // namespaces to be ignored, rdfs/owl-defined objects
        if( isset($setup->config->hiddenNS) ){
            foreach ($setup->config->hiddenNS as $ignore) {
                $whereSpec .= ' FILTER (!regex(str(?navEntry), "^' . $ignore . '"))';
            }
        }
        
        // entry point into the class tree
        if (isset($setup->state->parent)) {
            $whereSpec .= ' FILTER (str(?super) = str(<' . $setup->state->parent . '>))';
        }
        
        $query->setWherePart('WHERE {' . $whereSpec . '}');
        
        $query->setLimit(10);;        
        return $query;
    } 
}
