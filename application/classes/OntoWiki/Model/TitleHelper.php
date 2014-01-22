<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * Fetches title properties for a set of resources at once.
 * The resources can be defined explicitly or via a SPARQL graph pattern.
 *
 * @category OntoWiki
 * @package  OntoWiki_Classes_Model
 */
class OntoWiki_Model_TitleHelper
{
    /**
     * Whether to always search all configured title properties
     * in order to find the best language match or stop at the
     * first matching title property.
     *
     * @var boolean
     */
    protected $_alwaysSearchAllProperties = false;

    /**
     * Whether to fallback to local names instead of full
     * URIs for unknown resources
     *
     * @var boolean
     */
    protected $_alwaysUseLocalNames = false;

    /**
     * The singleton instance
     *
     * @var OntoWiki_Model_TitleHelper
     */
    private static $_instance = null;

    /**
     * The languages to consider for title properties.
     *
     * @var array
     */
    protected $_languages = array('en','','localname');

    /**
     * The model object to operate on
     *
     * @var Erfurt_Rdf_Model
     */
    protected $_model = null;

    /**
     * The resources for whitch to fetch title properties
     *
     * @var array
     */
    protected $_resources = array();

    /**
     * Resource query object
     *
     * @var Erfurt_Sparql_SimpleQuery
     */
    protected $_resourceQuery = null;

    /**
     * Erfurt store object
     *
     * @var Erfurt_Store
     */
    protected $_store = null;

    /**
     * Erfurt store object
     *
     * @var Erfurt_App
     */
    protected $_erfurtApp = null;

    /**
     * titleProperties from configuration
     *
     * @var array
     */
    protected $_titleProperties = null;



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

        $this->_erfurtApp = Erfurt_App::getInstance();

        if (null !== $store) {
            $this->_store = $store;
        } else {
            $this->_store = $this->_erfurtApp->getStore();
        }

        if (null == $config) {
            $config = OntoWiki::getInstance()->config;
        }
        if (is_array($config)) {
            if (isset($config['titleHelper']['properties'])) { // naming properties for resources
                $this->_titleProperties = array_values($config['titleHelper']['properties']);
            } else {
                $this->_titleProperties = array();
            }

            // fetch mode
            if (isset($config['titleHelper']['searchMode'])) {
                $this->_alwaysSearchAllProperties = (strtolower($config['titleHelper']['searchMode']) === 'language');
            }
        } else {
            if ($config instanceof Zend_Config) {
                //its possible to define myProperties in config.ini
                if (isset($config->titleHelper->myProperties)) {
                    $this->_titleProperties = array_values($config->titleHelper->myProperties->toArray());
                } else if (isset($config->titleHelper->properties)) { // naming properties for resources
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
        }
        // always use local name for unknown resources?
        if (isset($config->titleHelper->useLocalNames)) {
            $this->_alwaysUseLocalNames = (bool)$config->titleHelper->useLocalNames;
        }
        // add localname to titleproperties
        $this->_titleProperties[] = 'localname';

        if (null === $this->_languages) {
            $this->_languages = array();
        }
        if (isset($config->languages->locale)) {
            array_unshift($this->_languages, (string)$config->languages->locale);
            $this->_languages = array_unique($this->_languages);
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
     *
     * @return OntoWiki_Model_TitleHelper
     */
    public function addResource($resource)
    {
        $resourceUri = (string)$resource;
        if (Erfurt_Uri::check($resourceUri)) {
            if (empty($this->_resources[$resourceUri])) {
                $this->_resources[$resourceUri] = null;
            }
        } else {
            // throw exeption in debug mode only
            if (defined('_OWDEBUG')) {
                $logger = OntoWiki::getInstance()->logger;
                $logger->info('Supplied resource ' . htmlentities('<' . $resource . '>') . ' is not a valid URI.');
            }
        }
        return $this;
    }

    /**
     * Adds a bunch of resources for which to query title properties.
     * @param array  $resources
     * @return OntoWiki_Model_TitleHelper
     */
    public function addResources($resources = array(), $variable = null)
    {
        if (null === $variable) {
            foreach ($resources as $resourceUri) {
                $this->addResource($resourceUri);
            }
        } else {
            foreach ($resources as $row) {
                foreach ((array)$variable as $key) {
                    if (!empty($row[$key])) {
                        $object = $row[$key];
                        $toBeAdded = null;
                        if (is_array($object)) {
                            // probably support extended format
                            if (isset($object['type']) && ($object['type'] == 'uri')) {
                                $toBeAdded = $object['value'];
                            }
                        } else {
                            // plain object
                            $toBeAdded = $object;
                        }
                        if ($toBeAdded != null) {
                            $this->addResource($toBeAdded);
                        }
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Returns the title property for the resource URI in the requested language.
     * If no title property is found for that language the local part
     * of the resource URI  will be returned.
     *
     * @param string $resourceUri
     * @param string $language The preferred language for the title
     *
     * @return string
     */
    public function getTitle($resourceUri, $language = null)
    {
        if (!Erfurt_Uri::check($resourceUri)) {
            return $resourceUri;
        }
        // * means any language
        if (trim($language) == '*') {
            $language = null;
        }

        //Have a look if we have an entry for the given resourceUri
        if (!array_key_exists($resourceUri, $this->_resources) ) {

            if (defined('_OWDEBUG')) {
                $logger = OntoWiki::getInstance()->logger;
                $logger->info('TitleHelper: getTitle called for unknown resource. Adding resource before fetch.');
            }
            //If we dont have an entry create one
            $this->addResource($resourceUri);
        }

        // prepend the language that is asked for to the array
        // of languages we will look for
        $languages = $this->_languages;
        if (null !== $language) {
            array_unshift($languages, (string)$language);
        }
        $languages = array_values(array_unique($languages));

        //Have a look if we have already a title for the given resourceUri
        if ($this->_resources[$resourceUri] === null ) {
            $this->_receiveTitles();
        }
        //Select the best found title according received titles and order of configured titleproperties
        $title = $resourceUri;

        $titles = $this->_resources[$resourceUri];
        foreach ($languages as $language) {
            foreach ($this->_titleProperties as $titleProperty) {
                if (isset($titles[$titleProperty][$language]) && !empty($titles[$titleProperty][$language])) {
                    $title = $titles[$titleProperty][$language];
                    break(2);
                }
            }
        }
        return $title;
    }

    /**
     * Add a new title property on top of the list (most important) of title properties
     *
     * @param $propertyUri a string with the URI of the property to add
     */
    public function prependTitleProperty($propertyUri)
    {
        // check if we have a valid URI
        if (Erfurt_Uri::check($propertyUri)) {
            // remove the property from the list if it already exist
            foreach ($this->_titleProperties as $key => $value) {
                if ($value == $propertyUri) {
                    unset($this->_titleProperties[$key]);
                }
            }

            // rewrite the array
            $this->_titleProperties = array_values($this->_titleProperties);

            // prepend the new URI
            array_unshift($this->_titleProperties, $propertyUri);

            // reset the TitleHelper to fetch resources with new title properties
            $this->reset();
        }
    }

     /**
     * Resets the title helper, emptying all resources, results and queries stored
     */
    public function reset()
    {
        $this->_resources = array();
    }

    /**
     * operate on _resources array and call the method to fetch the titles
     * if no titles found for the respective resource the localname will be extracted
     *
     * @return void
     */
    private function _receiveTitles()
    {
        //first we check if there are resourceUris without a title representation
        $toBeReceived = array();
        foreach ($this->_resources as $resourceUri => $resource) {
            if ($resource == null) {
                $toBeReceived[] = $resourceUri;
            }
        }
        //now we try to receive the Titles from ResourcePool
        $this->_fetchTitlesFromResourcePool($toBeReceived);

        //If we dont find titles then we extract them from LocalName
        foreach ($this->_resources as $resourceUri => $resource) {
            if ($resource == null) {
                $this->_resources[$resourceUri]['localname']['localname']
                    = $this->_extractTitleFromLocalName($resourceUri);
            }
        }
    }

    /**
     * fetches all titles according the given array if Uris
     *
     * @param array resourceUris
     */
    private function _fetchTitlesFromResourcePool($resourceUris)
    {
        $resourcePool = $this->_erfurtApp->getResourcePool();
        $resources = array();
        if (!empty($this->_model)) {
            $modelUri = $this->_model->getModelIri();
            $resources = $resourcePool->getResources($resourceUris, $modelUri);
        } else {
            $resources = $resourcePool->getResources($resourceUris);
        }

        $memoryModel = new Erfurt_Rdf_MemoryModel();
        foreach ($resources as $resourceUri => $resource) {
            $resourceDescription = $resource->getDescription();
            $memoryModel->addStatements($resourceDescription);
            $found = false;
            foreach ($this->_titleProperties as $titleProperty) {
                $values = $memoryModel->getValues($resourceUri, $titleProperty);
                foreach ($values as $value) {
                    if (!empty($value['lang'])) {
                        $language = $value['lang'];
                    } else {
                        $language = '';
                    }
                    $this->_resources[$resourceUri][$titleProperty][$language] = $value['value'];
                }
            }
        }
    }

    /**
     * extract the localname from given resourceUri
     *
     * @param string resourceUri
     * @return string title
     */
    private function _extractTitleFromLocalName($resourceUri)
    {
        $title = OntoWiki_Utils::contractNamespace($resourceUri);
        // not even namespace found?
        if ($title == $resourceUri && $this->_alwaysUseLocalNames) {
            $title = OntoWiki_Utils::getUriLocalPart($resourceUri);
        }
        return $title;
    }
}
