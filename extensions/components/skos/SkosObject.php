<?php 
/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_skos
 */
class SkosObject {
	
	private $uri;
	private $notations;
	private $type;
	private $locale;
	
	public $skosPrefLabel = 'http://www.w3.org/2004/02/skos/core#prefLabel';
	public $skosAltLabel = 'http://www.w3.org/2004/02/skos/core#altLabel';
	public $skosNotation = 'http://www.w3.org/2004/02/skos/core#notation';
	public $skosEditorialNote = 'http://www.w3.org/2004/02/skos/core#editorialNote';
	public $skosChangeNote = 'http://www.w3.org/2004/02/skos/core#changeNote';
	public $skosDefinition = 'http://www.w3.org/2004/02/skos/core#definition';
	public $skosNote = 'http://www.w3.org/2004/02/skos/core#note';
	public $skosHistoryNote = 'http://www.w3.org/2004/02/skos/core#historyNote';
	public $skosScopeNote = 'http://www.w3.org/2004/02/skos/core#scopeNote';
	public $skosExample = 'http://www.w3.org/2004/02/skos/core#example';
	
	private $prefLabel;
	private $altLabel;
	private $notation;
	private $editorialNote;
	private $changeNote;
	private $definition;
	private $note;
	private $historyNote;
	private $scopeNote;
	private $example;
	
	public function __construct($uri, $type, $locale) {
		
		$this->uri = $uri;
		$this->type = $type;
		$this->locale = $locale;
	}
		
	/**
	 * returns the rdf:type of the skos object (concept or conceptScheme)
	 */
	public function getType() {
		
		return $this->type;
	}
	
	/**
	 * returns the uri of the object
	 */
	public function getUri() {
		
		return $this->uri;
	}
	
	public function getLocale() {
		
		return $this->locale;
	}
	
	public function findObjects($propertyName) {
       
       $foundObjects = array();
       
       $query = new Erfurt_Sparql_SimpleQuery();
       $query->setProloguePart('SELECT DISTINCT ?object')
           ->setWherePart('
               WHERE {
                    <' . $this->uri . '> <' . $propertyName. '> ?object . ' .
              //      FILTER (LANG(?object) = \'' . $this->locale . '\')
              ' }');
               
       if ( $result = OntoWiki::getInstance()->selectedModel->sparqlQuery($query) ) {
             
           foreach ( $result as $row ) {
               
               array_push($foundObjects, $row['object']);
           }
       }
   
       return $foundObjects;
	}
	
	/**
	 * returns the literal of skos:prefLabel
	 */
	public function getPrefLabel() {
	
		if ( $this->prefLabel == null ) {
			
			$this->prefLabel = $this->findObjects($this->skosPrefLabel);
		}
		if ( $this->prefLabel ) return $this->prefLabel[0];
		else return null;
	}
	
	public function getAltLabel() {
		
		if ( $this->altLabel == null ) {
			
			$this->altLabel = $this->findObjects($this->skosAltLabel);
		}
		if ( $this->altLabel ) return $this->altLabel[0];
		else return null;
	}

	public function getNotations() {
		
		if ( $this->notation == null ) {
			
			$this->notation = $this->findObjects($this->skosNotation);
		}
		return $this->notation;
	}
	
	public function getEditorialNotes() {
	
		if ( $this->editorialNote == null ) {
			
			$this->editorialNote = $this->findObjects($this->skosEditorialNote);
		}
		return $this->editorialNote;
	}
	
	public function getChangeNotes() {
	
		if ( $this->changeNote == null ) {
			
			$this->changeNote = $this->findObjects($this->skosChangeNote); 
		}
		return $this->changeNote;
	}
	
	public function getDefinitions() {

		if ( $this->definition == null ) {
			
			$this->definition = $this->findObjects($this->skosDefinition);
		}		
		return $this->definition;
	}
	
	public function getNotes() {
		
		if ( $this->note == null ) {
			
			$this->note = $this->findObjects($this->skosNote);
		}
		return $this->note;
	}
	
	public function getHistoryNotes() {
		
		if ( $this->historyNote == null ) {
			
			$this->historyNote = $this->findObjects($this->skosHistoryNote);
		}
		return $this->historyNote;
	}
	
	public function getScopeNotes() {
		
		if ( $this->scopeNote == null ) {
			
			$this->scopeNote = $this->findObjects($this->skosScopeNote);
		}
		return $this->scopeNote;
	}
	
	public function getExamples() {
		
		if ( $this->example == null ) {
			
			$this->example = $this->findObjects($this->skosExample);
		}
		return $this->example;
	}
	
	public function getPropertyList() {
		
		return array($this->skosPrefLabel, $this->skosAltLabel, $this->skosNotation, $this->skosEditorialNote, $this->skosChangeNote, $this->skosDefinition, $this->skosNote, $this->skosHistoryNote, $this->skosScopeNote, $this->skosExample);
	}
}

?>