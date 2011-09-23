<?php

/**
 * 
 * Enter description here ...
 * @author criess
 *
 */
class ResourceUriGenerator {
    
    /**
     * @var Array that holds multibyte and special chars in UTF-8 to uri compatible chars.
     *      All other non-alphanumeric will be deleted
     */
    private $charTable = array (
        'Ä'     => 'Ae' ,
        'ä'     => 'ae' ,
        'Ü'     => 'Ue' ,
        'ü'     => 'ue' ,
        'Ö'     => 'Oe' ,
        'ö'     => 'oe' ,
        ' '     => '_'  ,
        PHP_EOL => '_'  ,
        ':'     => '_'  ,
    );
    
    /**
     * 
     * Enter description here ...
     * @var array
     */
    private $_defaultConfig = array();
    
    /**
     * 
     * Enter description here ...
     * @var OntoWiki (Application)
     */
    private $_owApp = null;
    
    /**
     * 
     * Enter description here ...
     * @var Zend_Config
     */
    private $_config = null;
    
    /**
     * 
     * Enter description here ...
     * @var Erfurt_Rdf_Model
     */
    private $_model = null;
    
    /**
     * 
     * Enter description here ...
     * @var string
     */
    const FORMAT_RDFPHP = 'rdfphp';
    
    /**
     * 
     * Enter description here ...
     * @var string
     */
    const FORMAT_SPARQL = 'sparql';
    
    /**
     * constructor
     * @param $resourceUri
     * @param $defaultModel
     * @param $configPath
     */
    public function __construct($defaultModel = null, $configPath = null, $ow = null) {

        // check for ontowiki application
        if ( $ow === null || !($ow instanceof OntoWiki) ) {
            $this->_owApp = OntoWiki::getInstance();
        } else {
            $this->_owApp = $ow;
        }

        //get config
        $config = $this->_owApp->extensionManager->getExtensionConfig("resourcecreationuri");
        $this->_config = $config->private;
        
        // check for defaultModel to work on
        if ($defaultModel === null && $this->_owApp->selectedModel !== null) {
            $this->_model = $this->_owApp->selectedModel;
        } else {
            if ($defaultModel !== null) {
                $this->_model = $defaultModel;
            } else {
                $erroruri = is_null($defaultModel) ? 'null' : (string) $defaultModel;
                throw new InvalidArgumentException('ResourceUriGenerator can\'t load model with URI: ' . $erroruri);
            }
        }
        
    }
    
    /**
     * set a new defaultmodel in which the resources should be created
     * @param $defaultModel
     */
    public function setDefaultModel($defaultModel) {
        
        if ($defaultModel instanceof Erfurt_Rdf_Model) {
            $this->_model = $defaultModel;
        } else {
            $this->_model = $this->erfurt->getStore()->getModel((string) $defaultModel);
        }
    }
    
    /**
     * Function generates nice URIs by certain rules (naming scheme and available resource data)
     * Two modes are possible: live from store namingly 'sparql', or with memory model in rdfphp
     * format to use 'rdfphp'.
     * 
     * @param string $format (see class constants for possible values) 
     * @param array $data further data (in rdfphp-mode the insert statements)
     * @return string generated 'nice' URI
     */
    public function generateUri($resourceUri, $format = self::FORMAT_SPARQL, $data = array()) {

        // call format specific generation function
        switch ($format) {
            case self::FORMAT_SPARQL :
                $return = $this->generateUriFromSparql($resourceUri, $data);
                break;
            case self::FORMAT_RDFPHP :
                if (is_array($data) && sizeof($data) > 0) {
                    $return = $this->generateUriFromRdfphp($resourceUri, $data);
                } else {
                    //TODO what else
                }
                break;
        }
        
        // check if resources with same prefix exist
        if ( ($count = $this->countUriPattern($return)) > 0) {
            $return .= '/' . $count;
        } else {
            // do nothing
        }
        
        return $return;
    }
    
    /**
     * 
     * Enter description here ...
     * @param string $uri
     * @param array $data
     */
    private function generateUriFromSparql($uri,$data) {

        $properties = array();
        
        $schema = $this->loadNamingSchema($uri);
        
        $titleHelper = new OntoWiki_Model_TitleHelper($this->_model);
        
        foreach ($schema as $element) {
            
            if (is_string($this->_config->property->$element)) {
                $properties[$this->_config->property->$element] = array('element' => $element,'rank' => '1');
            } elseif (is_array($this->_config->property->$element->toArray())) {
                $countDeep = 0;
                foreach ($this->_config->property->$element->toArray() as $elementDeep) {
                    $properties[(string) $elementDeep] = array('element' => $element, 'rank' => $countDeep++); 
                }
            } else {
                // do nothing
            }
        }
        
        $query = new Erfurt_Sparql_Query2();
        $sRef  = new Erfurt_Sparql_Query2_IriRef($uri);
        $pVar  = new Erfurt_Sparql_Query2_Var('p');
        $oVar  = new Erfurt_Sparql_Query2_Var('o');
        
        $query->addProjectionVar($pVar);
        $query->addProjectionVar($oVar);
        $query->addTriple($sRef,$pVar,$oVar);
        $query->addFrom((string) $this->_model);
        $query->setLimit(100);
        $query->setDistinct(true);
        
        
        $container = new Erfurt_Sparql_Query2_ConditionalOrExpression();
        foreach ($properties as $filterProp => $element) {
            $sameTerm = new Erfurt_Sparql_Query2_sameTerm($pVar, new Erfurt_Sparql_Query2_IriRef($filterProp));
            //$filter = new Erfurt_Sparql_Query2_Filter($sameTerm);
            $container->addElement($sameTerm);
        }
        
        $query->addFilter($container);
        
        $result = $this->_owApp->erfurt->getStore()->sparqlQuery($query,array('withImports' => true));
        
        $replacements = array();
        
        foreach ($result as $row) {
            if (array_key_exists($row['p'],$properties)) {
                $titleHelper->addResource($row['p']);
                if (Erfurt_Uri::check($row['o'])) {
                    $titleHelper->addResource($row['o']);
                }
                if (array_key_exists($properties[$row['p']]['element'], $replacements)) {
                    if ((int)$properties[$row['p']]['rank'] < (int)$replacements[$properties[$row['p']]['element']]['rank']) {
                        $replacements[$properties[$row['p']]['element']] = array(
                    		'rank'  => $properties[$row['p']]['rank'],
                    		'value' => $row['o'],
                            'key'   => $row['p']
                        );
                    } else {
                        // do nothing (lower rank is already set)
                    }
                } else {
                    $replacements[$properties[$row['p']]['element']] = array(
                    	'rank'  => $properties[$row['p']]['rank'],
                    	'value' => $row['o'],
                        'key'   => $row['p']
                    );
                }
            }
        }
        
        $localName = '';

        foreach ($schema as $element) {
            if (array_key_exists($element,$replacements)) {
                if (Erfurt_Uri::check($replacements[$element]['value'])) {
                    $val = $titleHelper->getTitle($replacements[$element]['value']);
                } else {
                    $val = $replacements[$element]['value'];
                }
                $val = $this->convertChars($val);
                
                $key = $this->convertChars($titleHelper->getTitle($replacements[$element]['key']));
                
                $localName .=  $key . '/' . $val . '/';
            }
        }
        
        // no meaningful localname created falback to old uri (TODO or md5 a new one?)
        if ($localName === '') {
            return $uri;
            //$localName = 'resource/' . md5($uri . time());
        }

        $base = '';
        
        if ($this->_model !== null && $this->_model->getBaseIri() !== '') {
            $base = $this->_model->getBaseIri();
            if ($base[strlen($base)-1] !== '#' && $base[strlen($base)-1] !== '/') {
                $base .= '/';
            }
        } else {
            $count = 0;
            foreach (explode('/',$uri) as $element) {
                if ($count > 2) {
                    break;
                } else {
                    $count++;
                    $base .= $element . '/';
                }
            }
        }
        
        return $base . $localName;
        
    }
    
    /**
     * Nice uri building method
     * @param   $uri string to convert to nice uri
     * @return  string nice uri
     */
    private function generateUriFromRdfphp($uri,$data)
    {
        $newInstance = $data[$uri];
        
        $titleHelper = new OntoWiki_Model_TitleHelper($this->_model);
        
        // prepare TitleHelper by adding all possible resources
        foreach ($newInstance as $prop => $object) {
            $titleHelper->addResource($prop);
            foreach($object as $value) {
                if ($value['type'] === 'uri') {
                    $titleHelper->addResource($value['value']);
                }
            }
        } 
        
        $nameParts = $this->loadNamingSchema($uri);

        $uriParts = array();

        foreach($nameParts as $part) {
            if ( is_string($this->_config->property->$part) ) {
                $property = $this->_config->property->$part;
                if ( array_key_exists($property,$newInstance) && $value = current($newInstance[$property]) ) {
                    if ($value['type'] === 'uri') {
                        $uriParts[$part] = $this->convertChars($titleHelper->getTitle($value['value']));
                    } else {
                        $uriParts[$part] = $this->convertChars($value['value']);
                    }
                } else {
                    // do nothing (no data to generate title from found)
                }
            } elseif ( is_array($this->_config->property->$part->toArray()) ) {
                foreach ( $this->_config->property->$part as $subpart) {
                    $property = $subpart;
                    if ( array_key_exists($property,$newInstance) && $value = current($newInstance[$property]) ) {
                        if ($value['type'] === 'uri') {
                            $uriParts[$part] = $this->convertChars($titleHelper->getTitle($value['value']));
                        } else {
                            $uriParts[$part] = $this->convertChars($value['value']);
                        }
                        // on first value exit foreach
                        break;
                    } else {
                        // do nothing (no data to generate title from found)
                    }
                }
            } else {
                // do nothing
            }
        
        }

        $baseUri = $this->_model->getBaseUri();
        $baseUriLastCharacter = $baseUri[ strlen($baseUri) - 1];
        if ( ($baseUriLastCharacter == '/') || ($baseUriLastCharacter == '#') ) {
            $createdUri = $baseUri . implode('/',$uriParts);
        } else {
            // avoid ugly glued uris without separator
            $createdUri = $baseUri . '/' . implode('/',$uriParts);
        }
        
        return $createdUri;
    }
    
    /**
     * Load Naming Scheme from Model or Ini
     * @return Array
     */
    private function loadNamingSchema($resourceUri, $typeHint = null)
    {
        if ( $this->_config->fromModel ) {
        
            // TODO Needs to be completly rewritten.
            /*
            $query          = new Erfurt_Sparql_Query2();
            $schemaVar      = new Erfurt_Sparql_Query2_Var('schema');
            
            $subjectArray   = array_keys($this->insertData);
            
            // Test if exists at least one subject and if this first subject has a type statement
            if ( sizeof($subjectArray) > 0 &&
                 array_key_exists($this->_config->property->type, $subjectArray)
            ) {
                $subjectUri     = current($subjectArray);
                $typeArray      = current($this->insertData[$subjectUri][$this->_config->property->type]);
                $type           = $typeArray['value'];
            
                $query->addTriple(
                    new Erfurt_Sparql_Query2_IriRef($type),
                    new Erfurt_Sparql_Query2_IriRef($this->_config->namingSchemeProperty),
                    $schemaVar
                );
                
                $query->setDistinct(true);
                
                $result = $this->_model->sparqlQuery($query);
                
                if ( !empty($result['results']['bindings']) ) {
                    $schema = current($result);
                    return explode('/',$schema['schema']['value']);
                }
            }
            */
            
        }       
        
        return explode('/',$this->_config->defaultNamingScheme);
        
    }
    
    /**
     * Method to convert chars in a string to uri compatible
     * @param $str any string
     * @return string with some characters replaced or deleted
     */
    private function convertChars($str)
    {
        foreach ($this->charTable as $key => $value) {
            $str = str_replace($key, $value, $str);
        }
        $str= preg_replace('/[^a-z0-9_]+/i','',$str);
        //$str = substr($str,0,32);
        return $str;
    }
    
    /**
     * Method that counts already existing distinct datasets for given uri
     * 
     * @param $uri uri string
     * @return int distinct existing datasets
     */
    private function countUriPattern($uri)
    {
        $query = new Erfurt_Sparql_Query2();
        $query->setDistinct(true);
        
        $unions = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
        
        $subjectVar = new Erfurt_Sparql_Query2_Var('s');
        $query->addProjectionVar($subjectVar);
        
        // create six temporary vars (not selected in query)
        $tempVars = array();
        for ($i = 0;$i < 6; $i++) {
            $tempVars[] = new Erfurt_Sparql_Query2_Var('var' . $i);
        }
        
        $singlePattern = new Erfurt_Sparql_Query2_GroupGraphPattern();
        $singlePattern->addTriple($subjectVar,$tempVars[0],$tempVars[1]);
        $unions->addElement($singlePattern);
        
        $singlePattern = new Erfurt_Sparql_Query2_GroupGraphPattern();
        $singlePattern->addTriple($tempVars[2],$subjectVar,$tempVars[3]);
        $unions->addElement($singlePattern);
        
        $singlePattern = new Erfurt_Sparql_Query2_GroupGraphPattern();
        $singlePattern->addTriple($tempVars[4],$tempVars[5],$subjectVar);
        $unions->addElement($singlePattern);
        
        $query->getWhere()->addElement($unions);

        $filter = new Erfurt_Sparql_Query2_ConditionalOrExpression();

        $filter->addElement(
            new Erfurt_Sparql_Query2_Regex(
                    $subjectVar,
                    new Erfurt_Sparql_Query2_RDFLiteral('^' . $uri),
                    new Erfurt_Sparql_Query2_RDFLiteral('i')
            )
        );

        $query->addFilter($filter);
        
        $result = $this->_owApp->erfurt->getStore()->countWhereMatches(
            $this->_model->getModelIri(),
            $query->getWhere(),
            's',
            true
        );
        
        return $result;
    }
    
}
