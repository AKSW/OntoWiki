<?php

require_once 'OntoWiki/Module.php';
require_once 'Erfurt/Sparql/SimpleQuery.php';
require_once 'OntoWiki/Model/TitleHelper.php';

/**
 * OntoWiki module – skosrelations
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_skosrelations
 * @package    ontowiki
 * @author     Daniel Gerber, Marvin Frommhold
 */
class SkosRelationsModule extends OntoWiki_Module {
	
	private $resource;
	
	private $skosBroader = 'http://www.w3.org/2004/02/skos/core#broader';
	private $skosNarrower = 'http://www.w3.org/2004/02/skos/core#narrower';
	private $skosRelated = 'http://www.w3.org/2004/02/skos/core#related';
	private $skosTopConceptOf = 'http://www.w3.org/2004/02/skos/core#topConceptOf';
	private $skosHasTopConcept = 'http://www.w3.org/2004/02/skos/core#hasTopConcept';
	private $skosInScheme = 'http://www.w3.org/2004/02/skos/core#inScheme';
	private $skosCloseMatch = 'http://www.w3.org/2004/02/skos/core#closeMatch';
	private $skosExactMatch = 'http://www.w3.org/2004/02/skos/core#exactMatch';
	private $skosBroadMatch = 'http://www.w3.org/2004/02/skos/core#broadMatch';
	private $skosNarrowMatch = 'http://www.w3.org/2004/02/skos/core#narrowMatch';
	private $skosRelatedMatch = 'http://www.w3.org/2004/02/skos/core#relatedMatch';
	
    public function getContents() {
    	
    	$owApp = OntoWiki::getInstance();
    	
    	if (null === $owApp->selectedModel) {
            return;
        }
    	
    	$relations = array();
        
        $this->resource = (string) $owApp->selectedResource;
		
        $query = new Erfurt_Sparql_SimpleQuery();

        // build SPARQL query for getting class (rdf:type) of current resource
        $query->setProloguePart('SELECT DISTINCT ?t')
              ->setWherePart('WHERE {<' . $this->resource . '> a ?t.}');

		$availableRelations = array();

        // query the store
        if ($result = $owApp->selectedModel->sparqlQuery($query)) {
            
            $row = current($result);
            $class = $row['t'];

			// concept?
            if ( !strcmp($this->_privateConfig->concept, $class) ) {
                
                $availableRelations = array($this->skosBroader, $this->skosNarrower, $this->skosRelated, $this->skosTopConceptOf, $this->skosInScheme, $this->skosCloseMatch, $this->skosExactMatch, $this->skosBroadMatch, $this->skosNarrowMatch, $this->skosRelatedMatch);
            }
            // concept scheme?
            if ( !strcmp($this->_privateConfig->conceptscheme, $class) ) {
            	
            	$availableRelations = array($this->skosHasTopConcept, $this->skosInScheme);
            }
        }
    	
    	$relations = $this->initRelations($availableRelations);
    	
		if ( empty($relations) ) {

			$this->view->message = "No matches.";
		}

		$this->view->relations = $relations;
		$this->view->resourceUri = $this->resource; 

        return $this->render('skosrelations');
    }

    public function getStateId()
    {
        $id = $this->_owApp->selectedModel
            . $this->_owApp->selectedResource;

        return $id;
    }
    
    /**
     * initializes the relations of the selected resource
     */
    private function initRelations($availableRelations) {
    	
    	$url = new OntoWiki_Url(array('controller' => 'skos', 'action' => 'skosobject'), array('r'));
    	
    	$relations = array();
    	
        $titleHelper = new OntoWiki_Model_TitleHelper($this->_owApp->selectedModel);
        $titleHelper->addResources($availableRelations);
    	
    	foreach ( $availableRelations as $relation) {
    		
    		$foundObjects = $this->findObjects($relation);
    		
    		// is not empty add to relations
    		if ( !empty($foundObjects) ) {
    			
    			$objects = array();
    			foreach ( $foundObjects as $objectUri ) {
    				
    				if ( !array_key_Exists($objectUri, $objects) ) {
                        
						$objects[$objectUri] = array();
					}
					
					$url->setParam('r', $objectUri, true);
					
					$objects[$objectUri] = array(
					
						'title' => $titleHelper->getTitle($objectUri, $this->_lang),
						'url' => (string) $url
					);
    			}
    			
    			if ( !array_key_Exists($relation, $relations) ) {
                        
					$relations[$relation] = array();
				}
				$relations[$relation] = array(
            	
					'title' => $titleHelper->getTitle($relation, $this->_lang),
					'instances' => $objects
				);
				
    		}
    	}
    	
    	return $relations;
    }
    
    /**
     * finds objects based on the triple pattern: <selectedResource> <propertyName> ?uri .
     * TODO: fire only query for all attributes
     * TODO: query also for inverse properties ...
     */
    private function findObjects($propertyName) {
    	
    	$foundObjects = array();
    	
    	$query = new Erfurt_Sparql_SimpleQuery();
		$query->setProloguePart('SELECT DISTINCT ?uri')
			->setWherePart('
				WHERE {
					<' . (string) $this->_owApp->selectedResource . '> <' . $propertyName . '> ?uri .
				}');
				
		if ( $result = $this->_owApp->selectedModel->sparqlQuery($query) ) {
    	
    		foreach ( $result as $row ) {
    			
    			array_push($foundObjects, $row['uri']);
    		}
   		}
    
    	return $foundObjects;
    }
}
