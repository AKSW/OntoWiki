<?php

require_once 'OntoWiki/Controller/Component.php';
require_once 'OntoWiki/Model/TitleHelper.php';

/**
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_skos
 */
class SkosController extends OntoWiki_Controller_Component {
	
	private $resource;
	private $skosType;
	
	public function skosobjectAction() {
    
    	$owApp = OntoWiki_Application::getInstance();
    	$this->resource = (string) $owApp->selectedResource;

        require_once 'Erfurt/Sparql/SimpleQuery.php';
        $query = new Erfurt_Sparql_SimpleQuery();

        // build SPARQL query for getting class (rdf:type) of current resource
        $query->setProloguePart('SELECT DISTINCT ?t')
              ->setWherePart('WHERE {<' . $this->resource . '> a ?t.}');

        // query the store
        if ($result = $owApp->selectedModel->sparqlQuery($query)) {
            
            $row = current($result);
            $this->skosType = $row['t'];
        }
    
        $this->renderSkosObject();
    }
    
    public function renderSkosObject() {
    	
		$owApp = OntoWiki_Application::getInstance();
    	
        $this->view->placeholder('main.window.title')->append("SKOS View of ".$this->resource);
        
        $this->addModuleContext('skos.relations');
        
		$this->view->headLink()->appendStylesheet($this->_componentUrlBase.'css/skos.css');
    	
    	$locale = $owApp->config->languages->locale;
    	$this->view->locale = $locale;
    	
    	require_once $this->_componentRoot . 'SkosObject.php';
    	$skosObject = new SkosObject($this->resource, $this->skosType, $locale);
    	$this->view->skosObject = $skosObject;
    	
    	$titleHelper = new OntoWiki_Model_TitleHelper($this->_owApp->selectedModel);
        $titleHelper->addResources($skosObject->getPropertyList());
        $this->view->titleHelper = $titleHelper;
    }
}
?>
