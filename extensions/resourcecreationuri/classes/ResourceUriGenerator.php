<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 *
 * Enter description here ...
 * @package    Extensions_Resourcecreationuri
 * @author criess
 *
 */
class ResourceUriGenerator
{
    /**
     * @var Array that holds multibyte and special chars in UTF-8 to uri compatible chars.
     *      All other non-alphanumeric will be deleted
     */
    private $_charTable = array (
        'Ä'     => 'Ae' ,
        'ä'     => 'ae' ,
        'Ü'     => 'Ue' ,
        'ü'     => 'ue' ,
        'Ö'     => 'Oe' ,
        'ö'     => 'oe' ,
        'ß'     => 'ss' ,
        'ẞ'     => 'Ss' ,
    );

    private $_whiteSpaceTable = array (
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
    public function __construct($defaultModel = null, $configPath = null, $ow = null)
    {
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
    public function setDefaultModel($defaultModel)
    {

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
    public function generateUri($resourceUri, $format = self::FORMAT_SPARQL, $data = array())
    {
        $titleHelper = new OntoWiki_Model_TitleHelper($this->_model);

        if (isset($this->_config->property->title)) {
            $titleHelper->prependTitleProperty($this->_config->property->title);
        }

        // call format specific generation function
        switch ($format) {
            case self::FORMAT_SPARQL :
                $return = $this->generateUriFromSparql($resourceUri, $titleHelper);
                break;
            case self::FORMAT_RDFPHP :
                if (is_array($data) && sizeof($data) > 0) {
                    $newInstance = $data[$resourceUri];
                    $return = $this->generateUriFromRdfphp($resourceUri, $newInstance, $titleHelper);
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
     * @param   $uri string to convert to nice uri
     * @param   $titleHelper TitleHelper instance to use to get titles for URIs
     */
    private function generateUriFromSparql($uri, $titleHelper)
    {
        $schema = $this->loadNamingSchema($uri);
        $properties = array();

        foreach ($schema as $element) {
            if (is_string($this->_config->property->$element)) {
                $properties[$this->_config->property->$element] = array('element' => $element,'rank' => '1');
            } elseif (is_array($this->_config->property->$element->toArray())) {
                $countDeep = 0;
                foreach ($this->_config->property->$element->toArray() as $elementDeep) {
                    $properties[(string) $elementDeep] = array(
                        'element' => $element, 'rank' => $countDeep++
                    );
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
        $query->addTriple($sRef, $pVar, $oVar);
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

        $result = $this->_owApp->erfurt->getStore()->sparqlQuery(
            $query, array('withImports' => true)
        );

        $replacements = array();

        foreach ($result as $row) {
            if (array_key_exists($row['p'], $properties)) {
                $titleHelper->addResource($row['p']);
                if (Erfurt_Uri::check($row['o'])) {
                    $titleHelper->addResource($row['o']);
                }
                if (array_key_exists($properties[$row['p']]['element'], $replacements)) {
                    $newRank = (int) $properties[$row['p']]['rank'];
                    $minRank = $replacements[$properties[$row['p']]['element']]['rank'];
                    if ($newRank < $minRank) {
                        $replacements[$properties[$row['p']]['element']] = array(
                            'rank'  => $newRank,
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
            if (array_key_exists($element, $replacements)) {
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
            foreach (explode('/', $uri) as $element) {
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
     * @param   $newInstance array with properties of the new Resource
     * @param   $titleHelper TitleHelper instance to use to get titles for URIs
     * @return  string nice uri
     */
    private function generateUriFromRdfphp($uri, $newInstance, $titleHelper)
    {
        // prepare TitleHelper by adding all possible resources
        foreach ($newInstance as $prop => $object) {
            $titleHelper->addResource($prop);
            foreach ($object as $value) {
                if ($value['type'] === 'uri') {
                    $titleHelper->addResource($value['value']);
                }
            }
        }

        $nameParts = $this->loadNamingSchema($newInstance);
        $uriParts = array();

        foreach ($nameParts as $part) {
            if (is_string($this->_config->property->$part)) {
                $partProperties = array($this->_config->property->$part);
            } else if (is_array($this->_config->property->$part->toArray())) {
                $partProperties = $this->_config->property->$part->toArray();
            } else {
                // No propeties for the given part. Create empty array for the loop
                $partProperties = array();
            }

            foreach ($partProperties as $property) {
                if (array_key_exists($property, $newInstance) && $value = current($newInstance[$property])) {
                    $uriParts[$part] = $this->_getTitle($value, $titleHelper);
                    // on first value exit foreach
                    break;
                } else {
                    // do nothing (no data to generate title from found)
                }
            }
        }

        $baseUri = $this->_model->getBaseUri();
        $baseUriLastCharacter = $baseUri[ strlen($baseUri) - 1];
        if (($baseUriLastCharacter == '/') || ($baseUriLastCharacter == '#')) {
            $createdUri = $baseUri . implode('/', $uriParts);
        } else {
            // avoid ugly glued uris without separator
            $createdUri = $baseUri . '/' . implode('/', $uriParts);
        }

        return $createdUri;
    }

    /**
     * Returns a human readable Title for a resource
     * @param $resource Array in the object style ('value' and 'type')
     * @param $titleHelper TitleHelper instance to get a title
     */
    private function _getTitle($resource, $titleHelper = null)
    {
        if ($resource['type'] === 'uri') {
            // check if a resourcecreation specific title property is set
            if (isset($this->_config->property->title)) {
                $property = $this->_config->property->title;

                $query = 'SELECT ?title' . PHP_EOL;
                $query.= 'WHERE {' . PHP_EOL;
                $query.= '  <' . $resource['value'] . '> <' . $property . '> ?title .' . PHP_EOL;
                $query.= '}' . PHP_EOL;

                $result = $this->_model->sparqlQuery($query);

                if (count($result) > 0) {
                    return $result[0]['title'];
                }
            }
            // return TitleHelper value
            return $this->convertChars($titleHelper->getTitle($resource['value']));
        } else {
            // return literal value
            return $this->convertChars($resource['value']);
        }
    }

    /**
     * Load Naming Scheme from Model or Ini
     * @param $resource String|Array eigther the resource URI or an array with its properties
     * @return Array
     */
    private function loadNamingSchema($resource)
    {
        if (isset($this->_config->namingSchemeProperty)) {
            $schemeProperty = $this->_config->namingSchemeProperty;

            // set the query result as empty array so we can be sure its an array
            $result = array();

            if (is_string($resource)) {
                $resourceUri = $resource;
                $query = 'SELECT ?scheme' . PHP_EOL;
                $query.= 'WHERE {' . PHP_EOL;
                $query.= '  <' . $resourceUri . '> a ?type .' . PHP_EOL;
                $query.= '  ?type <' . $schemeProperty . '> ?scheme .' . PHP_EOL;
                $query.= '}' . PHP_EOL;

                $result = $this->_model->sparqlQuery($query);

            } else if (is_array($resource)) {
                $resourceProps = $resource;

                $types = $resourceProps[$this->_config->property->type];

                foreach ($types as $type) {
                    if ($type['type'] == 'uri') {
                        $query = 'SELECT ?scheme' . PHP_EOL;
                        $query.= 'WHERE {' . PHP_EOL;
                        $query.= '  <' . $type['value'] . '> <' . $schemeProperty . '> ?scheme .' . PHP_EOL;
                        $query.= '}' . PHP_EOL;

                        $result = $this->_model->sparqlQuery($query);
                        if (count($result) > 0) {
                            // break with the first type which has a sheme defined
                            break;
                        }
                    }
                }
            }

            if (count($result) > 0) {
                $scheme = $result[0]['scheme'];
                return explode('/', $scheme);
            }
        }

        return explode('/', $this->_config->defaultNamingScheme);
    }

    /**
     * Method to convert chars in a string to uri compatible
     * @param $str any string
     * @return string with some characters replaced or deleted
     */
    private function convertChars($str)
    {
        // replace defined special chars
        foreach ($this->_charTable as $key => $value) {
            $str = str_replace($key, $value, $str);
        }

        // replace defined whitespaces
        if (isset($this->_config->whiteSpaceMode)) {
            $mode = $this->_config->whiteSpaceMode;
        } else {
            $mode = false;
        }

        if ($mode == 'underscore') {
            foreach ($this->_whiteSpaceTable as $key => $value) {
                $str = str_replace($key, '_', $str);
            }
        } else if ($mode == 'CamelCaps' || $mode == 'CamelCase') {
            foreach ($this->_whiteSpaceTable as $key => $value) {
                // replace all whitespace with a simple space
                $str = str_replace($key, ' ', $str);
                // make all word uppercase
                $str = ucwords($str);
                // remove all spaces
                $str = str_replace(' ', '', $str);
            }
        } else {
            foreach ($this->_whiteSpaceTable as $key => $value) {
                $str = str_replace($key, $value, $str);
            }
        }

        // replace other special chars
        $str = preg_replace('/[^a-z0-9_]+/i', '', $str);
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
        $singlePattern->addTriple($subjectVar, $tempVars[0], $tempVars[1]);
        $unions->addElement($singlePattern);

        $singlePattern = new Erfurt_Sparql_Query2_GroupGraphPattern();
        $singlePattern->addTriple($tempVars[2], $subjectVar, $tempVars[3]);
        $unions->addElement($singlePattern);

        $singlePattern = new Erfurt_Sparql_Query2_GroupGraphPattern();
        $singlePattern->addTriple($tempVars[4], $tempVars[5], $subjectVar);
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
