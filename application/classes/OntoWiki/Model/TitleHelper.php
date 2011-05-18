<?php 

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * Fetches title properties for a set of resources at once.
 * The resources can be defined explicitly or via a SPARQL graph pattern.
 *
 * @category OntoWiki
 * @package Model
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_Model_TitleHelper
{
    /**
     * Maximum number of allowed resources.
     */
    const MAX_RESOURCES = 30;
    
    /**
     * Variable used for the resource URI in SPARQL queries.
     */
    const RESOURCE_VARIABLE = '__resource';
    
    /**
     * Static title cache per graph
     */
    protected static $_titleCache = array();
    
    /**
     * Whether to always search all configured title properties
     * in order to find the best language match or stop at the 
     * first matching title property.
     * @var boolean
     */
    protected $_alwaysSearchAllProperties = false;
    
    /**
     * Whether to fallback to local names instead of full
     * URIs for unknown resources
     * @var boolean
     */
    protected $_alwaysUseLocalNames = false;
    
    /**
     * The languages to consider for title properties.
     * @var array
     */
    protected $_languages = array('', 'en');
    
    /**
     * The model object to operate on
     * @var Erfurt_Rdf_Model
     */
    protected $_model = null;
    
    /**
     * Graph pattern that defines resources to operate on.
     * @var string
     */
    protected $_resourcePattern = '?p ?o . ';
    
    /**
     * The resources for whitch to fetch title properties
     * @var array
     */
    protected $_resources = null;
    
    /**
     * Flag that indicates whether resources have been added
     * @var boolean
     */
    protected $_resourcesAdded = false;
    
    /**
     * Resource query object
     * @var Erfurt_Sparql_SimpleQuery
     */
    protected $_resourceQuery = null;
    
    /**
     * Array of resource titles found
     * @var array
     */
    protected $_resourceTitles = null;
    
    /**
     * Erfurt store object
     * @var Erfurt_Store
     */
    protected $_store = null;
    
    /**
     * An array of naming properties whose values are
     * displayed instead of URIs.
     * @var array
     */
    protected $_titleProperties = null;
    
    /**
     * Title query object
     * @var Erfurt_Sparql_SimpleQuery
     */
    protected $_titleQuery = null;
    
    /**
     * Result set from the title query
     * @var array
     */
    protected $_titleQueryResults = null;

    private static $_instance = null;
    
    // ------------------------------------------------------------------------
    // --- Magic methods ------------------------------------------------------
    // ------------------------------------------------------------------------
    
    /**
     * Constructs a new title helper instance.
     *
     * @param Erfrt_Rdf_Model $model The model instance to operate on
     */
    public function __construct(Erfurt_Rdf_Model $model = null)
    {
        if (null !== $model) {
            $this->_model = $model;
        }
        
        $this->_store = Erfurt_App::getInstance()->getStore();
        $config       = OntoWiki::getInstance()->config;
        
        // naming properties for resources
        $this->_titleProperties = array_values($config->titleHelper->properties->toArray());
        
        // fetch mode
        $this->_alwaysSearchAllProperties = (strtolower($config->titleHelper->searchMode) == 'language');
        
        // always use local name for unknown resources?
        $this->_alwaysUseLocalNames = (bool)$config->titleHelper->useLocalNames;
    }
    
    // ------------------------------------------------------------------------
    // --- Public methods -----------------------------------------------------
    // ------------------------------------------------------------------------
        /**
     * Singleton instance
     *
     * @return OntoWiki_Model_Instance
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    /**
     * Adds a resource to list of resources for which to query title properties.
     *
     * @param Erfurt_Rdf_Resource|string $resource Resource instance or URI
     * @return OntoWiki_Model_TitleHelper
     */
    public function addResource($resource)
    {
        $resourceUri = (string)$resource;
        
        if (Erfurt_Uri::check($resourceUri)) {
            $this->_resources[$resourceUri] = $resourceUri;
        } else {
            // throw exeption in debug mode only
            if (defined('_OWDEBUG')) {
                require_once 'OntoWiki/Model/Exception.php';
                //throw new OntoWiki_Model_Exception('Supplied resource ' . htmlentities('<'.$resource.'>') . ' is not a valid URI.');
            }
        }
        
        $this->_resourcesAdded = true;
        
        return $this;
    }
    
    /**
     * Adds a bunch of resources for which to query title properties.
     *
     * If you pass the $variable parameter, the elements of $resourceArray are
     * interpreted as arrays where $variable holds the key which maps to the 
     * resource URI. Otherwise $resourceArray is interpreted as an array with
     * resource URIs as values.
     *
     * @param array $resourceArray
     * @param string $variable the key which maps to the resource URI
     * @return OntoWiki_Model_TitleHelper
     */
    public function addResources(array $resourceArray, $variable = null)
    {
        if (null === $variable) {
            if (!empty($resourceArray)) {
                // prepare merger array
                $merger = array_combine($resourceArray, $resourceArray);
                
                // merge in resources
                $this->_resources = array_merge((array)$this->_resources, $merger);
            }
        } else {
            foreach ($resourceArray as $row) {
                foreach ((array)$variable as $key) {
                    if (!empty($row[$key])) {
                        $object = $row[$key];
                        
                        if (is_array($object)) {
                            // probably support extended format
                            if (isset($object['type']) && ($object['type'] == 'uri')) {
                                $this->addResource($object['value']);
                            }
                        } else {
                            // plain object
                            $this->addResource($object);
                        }
                    }
                }
            }
        }
        
        $this->_resourcesAdded = true;
        
        return $this;
    }
    
    /**
     * Sets the graph pattern that identifies resources for which to query
     * title properties.
     *
     * @param string $pattern
     */
    public function setResourcePattern($pattern)
    {
        $this->_resourcePattern = $pattern;
        
        return $this;
    }
    
    /**
     * Returns the resources for which to fetch title properties
     *
     * @return array
     */
    public function getResources()
    {
        if (!$this->_resourcesAdded) {
            $this->_resources = array(); //sync
            return array();
        }

        // why not simply:
        return array_keys($this->_resources);
        // ?

        //what happens here? $wtf++ :)
        $query = $this->getResourceQuery();

        if (null !== $this->_model) {
            $result = $this->_model->sparqlQuery($query);
        } else {
            $result = $this->_store->sparqlQuery($query);
        }

        if ($result) {
            foreach ($result as $row) {
                $uri = $row[self::RESOURCE_VARIABLE];
                $this->_resources[$uri] = $uri;
            }
        }
        
        return array_keys((array)$this->_resources);
    }
    
    /**
     * Returns the SPARQL query for the resources
     *
     * @return Erfurt_Sparql_SimpleQuery
     */
    public function getResourceQuery()
    {
        if (null === $this->_resourceQuery) {
            $this->_resourceQuery = new Erfurt_Sparql_SimpleQuery();
            $this->_resourceQuery->setProloguePart('SELECT DISTINCT ?' . self::RESOURCE_VARIABLE)
                                 ->setWherePart('WHERE {?' . self::RESOURCE_VARIABLE . ' ' . $this->_resourcePattern . '}')
                                 ->setLimit(self::MAX_RESOURCES);
        }
        
        return $this->_resourceQuery;
    }
    
    /**
     * Returns the title property for the resource URI in the requested language.
     * If no title property is found for that language a list of fallback languages
     * is used. If no title property is found for any language, the local part
     * of the resource URI is returned.
     *
     * @param string $resourceUri
     * @param string $language The preferred language for the title
     * @return string
     */
    public function getTitle($resourceUri, $language = null)
    {
        if (!isset(self::$_titleCache[(string) $this->_model][$resourceUri])) {
            // * means any language
            if (trim($language) == '*') {
                $language = null;
            }

            // add if we don't have this URI (but logg)
            if (!array_key_exists($resourceUri, (array)$this->_resources)) {

                if (defined('_OWDEBUG')) {
                    $logger = OntoWiki::getInstance()->logger;
                    $logger->info('TitleHelper: getTitle called for unknown resource. Adding resource before fetch.');
                }
                $this->addResource($resourceUri);
            }

            // HACK: fix a probable Virtuoso bug with querying
            // for only one resource
            if (count((array)$this->_resources) < 2) {
                // add a dummy resource ;)
                $this->addResource('http://example.com/dummy');
            }

            // if this is the first getTitle request, fetch titles
            if (null === $this->_resourceTitles) {
                $this->_fetchResourceTitlesFromQueryResult(self::RESOURCE_VARIABLE);
            }

            // prepend the language that is asked for to the array
            // of languages we will look for
            $languages = $this->_languages;
            if (null !== $language) {
                array_unshift($languages, (string)$language);
            }

            $title = null;
            // has anything been found for the resource?
            if (array_key_exists($resourceUri, $this->_resourceTitles)) {
                $titleProperties = (array)$this->_resourceTitles[$resourceUri];

                $currentBestLanguage = PHP_INT_MAX;
                foreach ($this->_titleProperties as $currentTitleProperty) {
                    // has the property been found for the resource?
                    if (array_key_exists($currentTitleProperty, $titleProperties)) {

                        for ($i = 0, $max = count($languages); $i  < $max; $i++) {
                            $currentLanguage = $languages[$i];

                            if (($i < $currentBestLanguage) && isset($titleProperties[$currentTitleProperty][$currentLanguage])) {
                                $title = $titleProperties[$currentTitleProperty][$currentLanguage];
                                $currentBestLanguage = $i;
                                // var_dump(sprintf('%d/%d: %s', $currentBestLanguage, $i, $title));

                                if (!$this->_alwaysSearchAllProperties || ($currentBestLanguage === 0)) {
                                    // it won't get better :)
                                    break(2);
                                }
                            }
                        }
                    }
                }
            }

            // still not found?
            if (null === $title) {
                $title = OntoWiki_Utils::contractNamespace($resourceUri);

                // not even namespace found?
                if ($title == $resourceUri and $this->_alwaysUseLocalNames) {
                    $title = OntoWiki_Utils::getUriLocalPart($resourceUri);
                }           
            }
        } else {
            // cached title
            $title = self::$_titleCache[(string) $this->_model][$resourceUri];
        }
        
        return $title;
    }
    
    /**
     * Takes the current title query and fetches its result from the RDF store.
     *
     * @return array
     */
    public function getTitleQueryResult()
    {
        if (null === $this->_titleQueryResults) { 
            $numQueries = (int)ceil(max(count($this->_resources), self::MAX_RESOURCES) / self::MAX_RESOURCES);
            $execObject = $this->_model ? $this->_model : $this->_store;
            $this->_titleQueryResults = array();
            
            // get results for all queries
            foreach ($this->getTitleQueries($numQueries) as $currentQuery) {
                $queryResults = $execObject->sparqlQuery($currentQuery, array('result_format' => 'extended'));
                // var_dump((string)$currentQuery, $queryResults);
                
                if (is_array($queryResults) && isset($queryResults['head']['vars']) && !empty($queryResults['head']['vars'])) {
                    array_push($this->_titleQueryResults, $queryResults);
                }
            }
            
            if (defined('_OWDEBUG')) {
                $logger = OntoWiki::getInstance()->logger;
                $logger->info('TitleHelper: ' . $numQueries . ' queries with ' . count($this->_resources) . ' resources.');
            }
        }
        
        return $this->_titleQueryResults;
    }
    
    /**
     * Returns the queries for the title properties of all resources.
     * If the number of resources exceeds MAX_RESOURCES, the number of queries is increased
     * so that not more than MAX_RESOURCES occur per query.
     *
     * @return Erfurt_Sparql_SimpleQuery
     */
    public function getTitleQueries($numQueries)
    {        
        $currentQuery = null;
        $queries = array();
        $select = 'SELECT DISTINCT ?'
                . self::RESOURCE_VARIABLE
                . ' ?property ?value';
        
        for ($i = 0; $i < $numQueries; ++$i) {
            $start = $i * self::MAX_RESOURCES;
            $end   = $start + self::MAX_RESOURCES;
            
            $where = 'WHERE {'
                   . $this->_getTitleWhere(self::RESOURCE_VARIABLE, $start, $end)
                   . '}';
                   
            $currentQuery = new Erfurt_Sparql_SimpleQuery();
            $currentQuery->setProloguePart($select)
                         ->setWherePart($where);
            
            array_push($queries, $currentQuery);
        }
        
        return $queries;
    }
    
    /**
     * Resets the title helper, emptying all resources, results and queries stored
     */
    public function reset()
    {
        $this->_resources      = null;
        $this->_resourceQuery  = null;
        $this->_resourceTitles = null;
        
        $this->_titleQuery        = null;
        $this->_titleQueryResults = null;
    }
    
    // ------------------------------------------------------------------------
    // --- Protected methods --------------------------------------------------
    // ------------------------------------------------------------------------
    
    /**
     * Fetches information (e.g. title properties) of resources from a query result set.
     *
     * @param string $resourceVariable The query variable used for resources
     * @throws OntoWiki_Model_Exception if the query result doesn't contain the variable
     */
    protected function _fetchResourceTitlesFromQueryResult($resourceVariable)
    {
        $this->_resourceTitles = array();
        
        foreach ($this->getTitleQueryResult() as $titleQueryResult) {
            // fetch result
            $queryResult = $titleQueryResult;
            $head        = $queryResult['head'];
            $bindings    = $queryResult['bindings'];
            
            // check if variable is contained in query result
            if (!in_array($resourceVariable, $head['vars'])) {
                return;
            }
            if (defined('_OWDEBUG')) {
                $logger = OntoWiki::getInstance()->logger;
                $logger->debug('TitleHelper _fetchResourceTitlesFromQueryResult count(bindings): ' . count($bindings));
            }

            foreach ($bindings as $row) {
                // get the resource URI
                $currentResource = $row[$resourceVariable]['value'];
                $currentProperty = $row['property']['value'];
                $titleValue      = $row['value']['value'];
                
                // add the resource to the local title store
                if (!array_key_exists($currentResource, (array)$this->_resourceTitles)) {
                    $this->_resourceTitles[$currentResource] = array();
                }
                
                // add current title property to resource's title store
                if (!array_key_exists($currentProperty, $this->_resourceTitles[$currentResource])) {
                    $this->_resourceTitles[$currentResource][$currentProperty] = array();
                }
                
                // fetch the language or use default
                $titleLang = '';
                if (isset($row['value']['xml:lang'])) {
                    $titleLang = $row['value']['xml:lang'];
                }
                
                // don't overwrite previously found title
                if (!array_key_exists($titleLang, $this->_resourceTitles[$currentResource][$currentProperty])) {
                    $this->_resourceTitles[$currentResource][$currentProperty][$titleLang] = $titleValue;
                }
            }
        }
    }
    
    /**
     * Returns graph patterns for all title properties for a variable.
     *
     * @param string $variable the variable name
     * @return string
     */
    protected function _getTitleWhere($variableName, $start, $end)
    {
        $where = 'OPTIONAL {?' . $variableName . ' ?property ?value . }';
        
        // build resource sameTerm filters
        $resourceFilters = array();
        $resources       = $this->getResources();
        $end             = min(count($resources), $end);
        for ($i = $start; $i < $end; ++$i) {
            array_push($resourceFilters, 'sameTerm(?' . $variableName . ', <' . $resources[$i] . '>)');
        }
        if (!empty($resourceFilters)) {
            $where .= PHP_EOL . 'FILTER(' . implode(' || ', $resourceFilters) . ')';
        }
        
        // build title property sameTerm filters
        $propertyFilters = array();
        foreach ($this->_titleProperties as $uri) {
            array_push($propertyFilters, 'sameTerm(?property, <' . $uri . '>)');
        }
        if (!empty($propertyFilters)) {
            $where .= PHP_EOL . 'FILTER(' . implode(' || ', $propertyFilters) . ')';
        }
        
        return $where;
    }
}

