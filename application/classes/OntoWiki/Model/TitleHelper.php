<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * Fetches title properties for a set of resources at once.
 * The resources can be defined explicitly or via a SPARQL graph pattern.
 *
 * @category OntoWiki
 * @package OntoWiki_Classes_Model
 */
class OntoWiki_Model_TitleHelper
{
    /**
     * Static title cache per graph
     */
    private static $_titleCache = array();

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
    protected $_resourceTitles = array();

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
    protected $_titleQueryResults = array();

    private static $_instance = null;

    // ------------------------------------------------------------------------
    // --- Magic methods ------------------------------------------------------
    // ------------------------------------------------------------------------

    /**
     * Constructs a new title helper instance.
     *
     * @param Erfurt_Rdf_Model $model The model instance to operate on
     */
    public function __construct(Erfurt_Rdf_Model $model = null, Erfurt_Store $store = null, $config = null)
    {
        if (null !== $model) {
            $this->_model = $model;
        }

        if (null !== $store) {
            $this->_store = $store;
        } else {
            $this->_store = Erfurt_App::getInstance()->getStore();
        }

        if (null == $config) {
            $config  = OntoWiki::getInstance()->config;
        }
        if (is_array($config)) {
            if (isset($config['titleHelper']['properties'])) {// naming properties for resources
                $this->_titleProperties = array_values($config['titleHelper']['properties']);
            } else {
                $this->_titleProperties = array();
            }

            // fetch mode
            if (isset($config['titleHelper']['searchMode'])) {
                $this->_alwaysSearchAllProperties = (strtolower($config['titleHelper']['searchMode']) === 'language');
            }
        } else if ($config instanceof Zend_Config) {
            if (isset($config->titleHelper->properties)) {// naming properties for resources
                $this->_titleProperties = array_values($config->titleHelper->properties->toArray());
            } else {
                $this->_titleProperties = array();
            }

            // fetch mode
            if (isset($config->titleHelper->searchMode)) {
                $this->_alwaysSearchAllProperties = (strtolower($config->titleHelper->searchMode) == 'language');
            }
        } else {
            $this->_titleProperties = array();
        }

        // always use local name for unknown resources?
        if (isset($config->titleHelper->useLocalNames)) {
            $this->_alwaysUseLocalNames = (bool)$config->titleHelper->useLocalNames;
        }

        if (null === $this->_languages) {
            $this->_languages = array();
        }
        if (isset($config->languages->locale)) {
            array_unshift($this->_languages, (string)$config->languages->locale);
        }
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
                throw new OntoWiki_Model_Exception(
                    'Supplied resource ' . htmlentities('<'.$resource.'>') . ' is not a valid URI.'
                );
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

        return array_keys($this->_resources);
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
        if (!Erfurt_Uri::check($resourceUri)) {
            return $resourceUri;
        }

        if (!$this->_cache($resourceUri, (string)$this->_model)) {
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

            // if this is the first getTitle request, fetch titles
            if (!isset($this->_resourceTitles[$resourceUri])) {
                $this->_fetchResourceTitlesFromQueryResult();
            }

            // prepend the language that is asked for to the array
            // of languages we will look for
            $languages = $this->_languages;
            if (null !== $language) {
                array_unshift($languages, (string)$language);
            }
            $languages = array_values(array_unique($languages));

            $title = null;
            // has anything been found for the resource?
            if (array_key_exists($resourceUri, $this->_resourceTitles)) {
                $titleProperties = (array)$this->_resourceTitles[$resourceUri];

                $currentBestLanguage = PHP_INT_MAX;
                foreach ($this->_titleProperties as $currentTitleProperty) {
                    // has the property been found for the resource?
                    if (array_key_exists($currentTitleProperty, $titleProperties)) {

                        for ($i = 0, $max = count($languages); $i < $max; ++$i) {
                            $currentLanguage = $languages[$i];

                            if (
                                ($i < $currentBestLanguage)
                                && isset($titleProperties[$currentTitleProperty][$currentLanguage])
                            ) {
                                $title = $titleProperties[$currentTitleProperty][$currentLanguage];
                                $currentBestLanguage = $i;
                                #var_dump(sprintf('%d/%d: %s', $currentBestLanguage, $i, $title));

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

            $this->_cache($resourceUri, (string)$this->_model, $title);
        } else {
            // cached title
            $title = $this->_cache($resourceUri, (string)$this->_model);
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
        $execObject = $this->_model;
        if (null !== $this->_store) {
            $execObject = $this->_store;
        }

        // get results for all queries
        $queries = $this->getTitleQueries();
        foreach ($queries as $resourceUri=>$currentQuery) {
            if (isset($this->_titleQueryResults[$resourceUri])) {
                continue;
            }

            $queryResults = $execObject->sparqlQuery($currentQuery, array('result_format' => 'extended'));

            if (
                is_array($queryResults)
                && isset($queryResults['head']['vars'])
                && !empty($queryResults['head']['vars'])
            ) {
                $this->_titleQueryResults[$resourceUri] = $queryResults;
            }
        }

        if (defined('_OWDEBUG')) {
            $numQueries = count($queries);

            $logger = OntoWiki::getInstance()->logger;

            $logger->info(
                'TitleHelper: ' . $numQueries . ' queries with ' . count($this->_resources) . ' resources.'
            );
        }

        return $this->_titleQueryResults;
    }

    /**
     * Returns the queries for the title properties of all resources.
     *
     * @return Erfurt_Sparql_SimpleQuery
     */
    public function getTitleQueries()
    {
        $currentQuery = null;
        $queries = array();
        $select = 'SELECT DISTINCT ?property ?value';
        if ($this->_resources === null) {
            return array();
        }
        foreach ($this->_resources as $resourceUri) {
            $where = 'WHERE {'
                   . $this->_getTitleWhere($resourceUri)
                   . '}';

            $currentQuery = new Erfurt_Sparql_SimpleQuery();
            $currentQuery->setProloguePart($select)
                         ->setWherePart($where);

            $queries[$resourceUri] = $currentQuery;
        }

        return $queries;
    }

    /**
     * Add a new title property on top of the list (most important)
     */
    public function prependTitleProperty($propertyUri)
    {
        // check if we have a valid URI
        if (Erfurt_Uri::check($propertyUri)) {
            // remove the property from the list if it already exist
            foreach ($this->_titleProperties as $key => $value) {
                if ($value == $propertyUri) unset($this->_titleProperties[$key]);
            }
            // rewrite the array
            $this->_titleProperties = array_values($this->_titleProperties);
            // prepend the new URI
            array_unshift($this->_titleProperties, $propertyUri);
        }
    }

    /**
     * Resets the title helper, emptying all resources, results and queries stored
     */
    public function reset()
    {
        $this->_resources      = null;
        $this->_resourceQuery  = null;
        $this->_resourceTitles = array();

        $this->_titleQuery        = null;
        $this->_titleQueryResults = array();
    }

    // ------------------------------------------------------------------------
    // --- Protected methods --------------------------------------------------
    // ------------------------------------------------------------------------

    /**
     * Fetches information (e.g. title properties) of resources from a query result set.
     *
     */
    protected function _fetchResourceTitlesFromQueryResult()
    {
        foreach ($this->getTitleQueryResult() as $resourceUri=>$titleQueryResult) {
            // fetch result
            $queryResult = $titleQueryResult;
            $head        = $queryResult['head'];
            $bindings    = $queryResult['results']['bindings'];

            if (defined('_OWDEBUG')) {
                $logger = OntoWiki::getInstance()->logger;

                $logger->debug('TitleHelper _fetchResourceTitlesFromQueryResult count(bindings): ' . count($bindings));
            }

            foreach ($bindings as $row) {
                // get the resource URI
                $currentResource = $resourceUri;
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
     * Returns graph patterns for all title properties for a resource URI.
     *
     * @param string $resourceUri the resource URI
     * @return string
     */
    protected function _getTitleWhere($resourceUri)
    {
        $where = 'OPTIONAL { <' . $resourceUri . '> ?property ?value . }';

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

    private function _cache($resourceUri, $graphUri, $newValue = null)
    {
        $cacheBucketId = md5(serialize($this->_titleProperties));
        if (null !== $newValue) {
            if (!isset(self::$_titleCache[$cacheBucketId])) {
                self::$_titleCache[$cacheBucketId] = array();
            }
            if (!isset(self::$_titleCache[$cacheBucketId][$graphUri])) {
                self::$_titleCache[$cacheBucketId][$graphUri] = array();
            }
            self::$_titleCache[$cacheBucketId][$graphUri][$resourceUri] = $newValue;
            return true;
        }

        if (isset(self::$_titleCache[$cacheBucketId][$graphUri][$resourceUri])) {
            return self::$_titleCache[$cacheBucketId][$graphUri][$resourceUri];
        }

        return false;
    }

    private function _resetCache()
    {
        $cacheBucketId = md5(serialize($this->_titleProperties));
        if (isset(self::$_titleCache[$cacheBucketId])) {
            self::$_titleCache[$cacheBucketId] = array();
        }
    }
}
