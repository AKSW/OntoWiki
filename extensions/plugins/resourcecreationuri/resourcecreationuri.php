<?php
require_once 'OntoWiki/Plugin.php';
/**
 * Plugin that tries to make nice uris if new resources are created.
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_plugins
 */
class ResourcecreationuriPlugin extends OntoWiki_Plugin
{
    
    /**
     * @var Statements Array for statements to delete
     */
    private $deleteData     = array();
    
    /**
     * @var Statements Array for statements to insert
     */
    private $insertData     = array();
    
    /**
     * @var Erfurt_Rdf_Model (used with title helper)
     */
    private $deleteModel    = null;
    
    /**
     * @var Erfurt_Rdf_Model (used with title helper)
     */
    private $insertModel    = null;
    
    /**
     * @var Array that holds multibyte and special chars to uri compatible chars.
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
        PHP_EOL => '_'
    );
    
    /**
     * Try to generate nice uri if new resource uri is found
     * @param   $event triggered Erfurt_Event
     * @return  null
     */
    public function onUpdateServiceAction($event)
    {
    
        // set values from event
        $this->insertModel  = $event->insertModel;
        $this->insertData   = $event->insertData;
        $this->deleteModel  = $event->deleteModel;
        $this->deleteData   = $event->deleteData;

        $subjectArray   = array_keys($this->insertData);
        $subjectUri     = current($subjectArray);
        $pattern        = '/^'
                        // URI Component
                        . addcslashes($this->insertModel->getBaseUri() . $this->_privateConfig->newResourceUri,'./')
                        // MD5 Component
                        . '\/([A-Z]|[0-9]){32,32}'
                        . '/i';
                        
        $nameParts = $this->loadNamingSchema();
        
        if ( count($event->insertData) == 1 && preg_match($pattern,$subjectUri) ) {
            $newUri = $this->buildNiceUri($subjectUri, $nameParts);
            $temp   = array();
            foreach ($this->insertData[$subjectUri] as $p => $o) {
                $temp[$newUri][$p] = $o;
            }
            $this->insertData = $temp;
        } else {
            //do nothing
        }
        
        //writeback on event
        $event->insertModel = $this->insertModel;
        $event->insertData  = $this->insertData;
        $event->deleteModel = $this->deleteModel;
        $event->deleteData  = $this->deleteData;
    
    }
    
    /**
     * Nice uri building method
     * @param   $uri string to convert to nice uri
     * @return  string nice uri
     */
    private function buildNiceUri($uri, $nameParts)
    {
        $newInstance = $this->insertData[$uri];
        
        $titleHelper = new OntoWiki_Model_TitleHelper($this->insertModel);
        
        // prepare TitleHelper by adding all possible resources
        foreach ($newInstance as $prop => $object) {
            $titleHelper->addResource($prop);
            foreach($object as $value) {
                if ($value['type'] === 'uri') {
                    $titleHelper->addResource($value['value']);
                }
            }
        } 

        $uriParts = array();

        foreach($nameParts as $part) {
            if ( is_string($this->_privateConfig->property->$part) ) {
                $property = $this->_privateConfig->property->$part;
                if ( array_key_exists($property,$newInstance) && $value = current($newInstance[$property]) ) {
                    if ($value['type'] === 'uri') {
                        $uriParts[$part] = $this->convertChars($titleHelper->getTitle($value['value']));
                    } else {
                        $uriParts[$part] = $this->convertChars($value['value']);
                    }
                } else {
                    // do nothing (no data to generate title from found)
                }
            } elseif ( is_array($this->_privateConfig->property->$part->toArray()) ) {
                foreach ( $this->_privateConfig->property->$part as $subpart) {
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
        
        $createdUri = $this->insertModel->getBaseUri() . implode('/',$uriParts);
        
        $count = $this->countUriPattern($createdUri);

        if ($count) {
            return $createdUri . '_' . $count;
        } else {
            return $createdUri;
        }
    }
    
    /**
     * Load Naming Scheme from Model or Ini
     * @return Array
     */
    private function loadNamingSchema()
    {
        if ( $this->_privateConfig->fromModel ) {
        
            $query          = new Erfurt_Sparql_Query2();
            $schemaVar      = new Erfurt_Sparql_Query2_Var('schema');
            
            $subjectArray   = array_keys($this->insertData);
            $subjectUri     = current($subjectArray);
            $typeArray      = current($this->insertData[$subjectUri][$this->_privateConfig->property->type]);
            $type           = $typeArray['value'];
            
            $query->addTriple(
                new Erfurt_Sparql_Query2_IriRef($type),
                new Erfurt_Sparql_Query2_IriRef($this->_privateConfig->namingSchemeProperty),
                $schemaVar
            );
            
            $query->setDistinct(true);
            
            $result = $this->insertModel->sparqlQuery($query);
            
            if ( !empty($result['bindings']) ) {
                $schema = current($result);
                return explode('/',$schema['schema']['value']);
            }
            
        }       
        
        return explode('/',$this->_privateConfig->defaultNamingScheme);
        
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
        $str = substr($str,0,32);
        return $str;
    }
    
    /**
     * Method that counts already existing distinct datasets for given uri
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
                    $subjectVar ,
                    new Erfurt_Sparql_Query2_RDFLiteral('^' . $uri),
                    new Erfurt_Sparql_Query2_RDFLiteral('i')
            )
        );

        $query->addFilter($filter);
        $result = $this->insertModel->sparqlQuery($query);
        return count($result);
    }

}
