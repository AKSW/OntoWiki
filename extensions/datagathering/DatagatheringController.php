<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once 'Erfurt/Wrapper/Registry.php';
require_once 'OntoWiki/Controller/Component.php';

/**
 * The main controller class for the datagathering component. This class
 * provides services for URI search, statement import and statement sync.
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_datagathering
 * @author     Philipp Frischmuth <pfrischmuth@googlemail.com>
 */
class DatagatheringController extends OntoWiki_Controller_Component
{
    // ------------------------------------------------------------------------
    // --- Search related constants -------------------------------------------
    // ------------------------------------------------------------------------

    const SEARCH_MODE_ALL        = 0;
    const SEARCH_MODE_PROPERTIES = 1;

    const SEARCH_DEFAULT_LIMIT = 20;
    const SEARCH_MAX_LIMIT     = 100;


    // ------------------------------------------------------------------------
    // --- Import and Sync related constants ----------------------------------
    // ------------------------------------------------------------------------

    /**
     * New versioning type codes.
     */
    const VERSIONING_IMPORT_ACTION_TYPE = 1010;
    const VERSIONING_SYNC_ACTION_TYPE   = 1020;

    /**
     * Syncing status codes
     */
    const NO_SYNC_CONFIG_FOUND = 20;
    const NO_EDITING_RIGHTS    = 10;
    const SYNC_SUCCESS         = 30;

    /**
     * IMPORT STATUS CODES
     */
    CONST IMPORT_OK = 10;
    CONST IMPORT_NO_DATA = 20;
    CONST IMPORT_WRAPPER_ERR = 30;
    CONST IMPORT_WRAPPER_INSTANCIATION_ERR = 40;
    CONST IMPORT_NOT_EDITABLE = 50;
    CONST IMPORT_WRAPPER_EXCEPTION = 60;
    CONST IMPORT_WRAPPER_NOT_AVAILABLE = 70;

    // ------------------------------------------------------------------------
    // --- Import and Sync related private properties -------------------------
    // ------------------------------------------------------------------------

    /**
     * Contains an array with matching sync configs.
     *
     * @var array
     */
    private $_syncConfigCache = array();


    // ------------------------------------------------------------------------
    // --- General private properties for this component ----------------------
    // ------------------------------------------------------------------------

    /**
     * Contains the graph URI of the selected graph or the uri which is given
     * by the m parameter
     *
     * @var string|null
     */
    private $_graphUri = null;

    /**
     * Contains the properties as configured in the ini file.
     *
     * @var array
     */
    private $_properties = array();

    /**
     * Contains the sync model helper URI as configured in the ini file.
     *
     * @var string
     */
    private $_syncModelHelperBase = null;

    /**
     * Contains the sync model URI as configured in the ini file.
     *
     * @var string
     */
    private $_syncModelUri = null;

    /**
     * Contains a reference to the wrapper registry.
     *
     * @var Erfurt_Wrapper_Registry
     */
    private $_wrapperRegisty = null;


    // ------------------------------------------------------------------------
    // --- Component initialization -------------------------------------------
    // ------------------------------------------------------------------------

    public function init()
    {
        parent::init();

        $this->_syncModelUri = $this->_privateConfig->syncModelUri;
        $this->_syncModelHelperBase = $this->_privateConfig->syncModelHelperBase;

        //$this->_properties = $this->_privateConfig->properties->toArray();

        $this->_wrapperRegisty = Erfurt_Wrapper_Registry::getInstance();

        $owApp = OntoWiki::getInstance();
        if (null !== $owApp->selectedModel) {
            $this->_graphUri = $owApp->selectedModel->getModelIri();
        } else {
            if (isset($this->_request->m)) {
                $this->_graphUri = $this->_request->m;
            } else {
                throw new Ontowiki_Exception("model must be selected or sprecified with the m parameter");
            }
        }
    }


    // ------------------------------------------------------------------------
    // --- URI and Property Search related methods ----------------------------
    // ------------------------------------------------------------------------

    /**
     * Searches for relevant URIs for given term(s). This service has two
     * modes. One that returns all matching URIs and one that returns only
     * resource URIs that are defined as properties or at least used once as
     * property.
     *
     * q - The mandatory parameter q contains a whitespace seperated list of
     *     serach terms.
     *
     * mode - This optional parameter sets the search mode. The default value
     *        is 0. In this mode all matching URIs will be returned. If the
     *        mode is set to 1, only matching URIs are returned, that are
     *        defined as properties or that are used as propertie at least
     *        once.
     *
     * limit - Optional sets the maximum number of URIs the result may contain.
     *         The default value is SEARCH_DEFAULT_LIMIT. The maximum value
     *         is SEARCH_MAX_LIMIT.
     *
     * classes - an optional paramter which is a json_encoded array of class URIs
     *
     * This service will output a json encoded string, which represents the
     * result list. Each entry is returned with an title, the URI itself and
     * the source of that result. The entry components are sepearted by a | and
     * the entries are seperated by a newline. If nothing is found an empty
     * json encodes string will be returned.
     */
    public function searchAction()
    {
        // Use the selected graph if present. If not, search all available graphs.
        $modelUri = $this->_graphUri;

        // Check for the mandatory q parameter.
        $q = $this->_request->q;
        if (null === $q) {
            $this->_response->setRawHeader('HTTP/1.0 400 Bad Request');
            echo '400 Bad Request - The q parameter is missing.';
            exit;
        }
        $termsArray = explode(' ', $q);

        // check for the classes json array
        $classes = array();
        if (null !== $this->_request->classes) {
            $classes = json_decode($this->_request->classes);
            if (!is_array($classes)) {
                $classes = array();
            }
        }

        // Set the search mode.
        if (null !== $this->_request->mode) {
            if ((int)$this->_request->mode === self::SEARCH_MODE_PROPERTIES) {
                $mode = self::SEARCH_MODE_PROPERTIES;
            } else {
                // Default to search mode all.
                $mode = self::SEARCH_MODE_ALL;
            }
        } else {
            $mode = self::SEARCH_MODE_ALL;
        }

        // Check for optional limit parameter.
        if (null !== $this->_request->limit) {
            $limit = $this->_request->limit;
            if ((int)$limit > 0 && $limit <= self::SEARCH_MAX_LIMIT) {
                $limit = (int)$limit;
            } else {
                // Default to search mode all.
                $limit = self::SEARCH_DEFAULT_LIMIT;
            }
        } else {
            $limit = self::SEARCH_DEFAULT_LIMIT;
        }


        // Check whether given term is a URI! If a URI is given we return an empty result, for some users
        // may want to enter URIs themself.
        if (count($termsArray) === 1) {
            foreach ($termsArray as $t) {
                $regExp = '/^(' . implode(':|', $this->_listIanaSchemes()) . ':).$/';

                if (preg_match($regExp, $t)) {
                    echo json_encode('');
                    exit;
                }
                if (strlen($t) > 20) {
                    echo json_encode('');
                    exit;
                }
            }
        }

        $result = array();

        // Step 1a: Search the local database for URIs (class restricted)
        $localWithRestriction = $this->_searchLocal($termsArray, $modelUri, $mode, $limit, $classes);
        if (count($localWithRestriction) > 0) {
            $result = $localWithRestriction;
        }
        // Step 1b: Search the local database for URIs (NOT class restricted)
        $local = $this->_searchLocal($termsArray, $modelUri, $mode, $limit);
        if (count($local) > 0) {
            # put new result values at the end of the result array
            foreach ($local as $resultKey => $resultValue) {
                if (!isset($result[$resultKey])) {
                    $result[$resultKey] = $resultValue;
                }
            }
        }

        if ($mode === self::SEARCH_MODE_ALL && count($result) < $limit) {
            // Step 2: Extend the result with the results from plugins...
            $currentCount = count($result);

            require_once 'Erfurt/Event.php';
            $event = new Erfurt_Event('onDatagatheringComponentSearch');
            $event->translate  = $this->_owApp->translate;
            $event->termsArray = $termsArray;
            $event->modelUri   = $modelUri;
            $event->classes   = $classes;
            $pluginResult = $event->trigger();

            if (is_array($pluginResult)) {
                foreach ($pluginResult as $uri=>$value) {
                    if (!isset($result[$uri])) {
                        $result[$uri] = $value;
                        $currentCount++;
                    }

                    if ($currentCount === $limit) {
                        break;
                    }
                }
            }

            // 3. Step: Expand qnames
            if (count($result) < $limit) {
                $currentCount = count($result);

                $expanded = $this->_expandNamespaces($termsArray, $modelUri);
                if (count($expanded) > 0) {
                    foreach ($expanded as $uri=>$value) {
                        if (!isset($result[$uri])) {
                            $result[$uri] = $value;
                            $currentCount++;
                        }

                        if ($currentCount === $limit) {
                            break;
                        }
                    }
                }
            }

            // 4. If there is place for one more result, we add auto generated URI.
            if (count($result) < $limit) {
                $store = Erfurt_App::getInstance()->getStore();
                $model = $store->getModel($modelUri);

                if ($model) {
                    $suffix = '';
                    foreach ($termsArray as $t) {
                        $suffix .= ucfirst($t);
                    }

                    $prefix = $model->getBaseUri();
                    $lastChar = substr($prefix, -1);
                    if ($lastChar !== '/' && $lastChar !== '#') {
                        $prefix .= '/';
                    }
                    $uri = $prefix . urlencode($suffix);

                    if (!isset($result[$uri])) {
                        $translate = $this->_owApp->translate;
                        $result[$uri] = implode(' ', $termsArray) . '|' . $uri . '|' . $translate->_('Generated URI');
                    }
                }
            }
        }

        // 5. if the user input was an URI, give this URI as result back too
        if (Zend_Uri::check($termsArray[0]) == true) {
            $translate = $this->_owApp->translate;
            $result = array(
                $termsArray[0] => $termsArray[0] .'|'.$termsArray[0].'|'.$translate->_('your manual input')
            ) + $result;
        }

        $body = json_encode(implode(PHP_EOL, $result));

        if (isset($this->_request->callback)) {
            $callback = $this->_request->callback;

            // build jsonp
            $body = $callback
                  . ' ('
                  . $body
                  . ')';
        }

        // send
        $response = $this->getResponse();
        // comment from seebi: why this issnt set to application/json? with
        //$response->setHeader('Content-Type', 'application/json');
        $response->setBody($body);
        $response->sendResponse();

        exit;
    }

    /**
     * Expands qnames if possible.
     *
     * @param array $termsArray
     * @param string $modelUri
     *
     * @return array
     */
    private function _expandNamespaces(array $termsArray, $modelUri)
    {
        $result = array();

        // We need the model here, for prefix definitions are model specific.
        if (null === $modelUri) {
            return $result;
        }

        $erfurtNamespaces = Erfurt_App::getInstance()->getNamespaces();
        $namespaces = $erfurtNamespaces->getNamespacePrefixes($modelUri);

        $translate = $this->_owApp->translate;
        foreach ($termsArray as $t) {
            if ($pos = strpos($t, ':')) {
                $prefix = substr($t, 0, $pos);
                $local  = substr($t, $pos+1);

                if (isset($namespaces[$prefix])) {
                    $uri = $namespaces[$prefix] . $local;
                    $result[$uri] = implode(' ', $termsArray) . '|' . $uri . '|' . $translate->_('Expanded QNames');
                }
            }
        }

        return $result;
    }

    /**
     * Returns a number between 0 and 1, which weights a result regardings its
     * value for the given search.
     *
     * @param array $termsArray
     * @param string $uri
     * @param string|null $object
     *
     * @return int
     */
    private function _getWeight(array $termsArray, $uri, $object = null)
    {
        if (null !== $object) {
            $object = strtolower($object);
        }

        $weight = 0.0;

        if (substr($uri, -1) === '/') {
            $uri = substr($uri, 0, -1);
        }

        if ($i = strrpos($uri, '#')) {
            $uriLocalPart = substr($uri, $i+1);
        } else if ($i = strrpos($uri, '/')) {
            $uriLocalPart = substr($uri, $i+1);
        } else {
            // No local part found... ignore
        }

        if (null !== $uriLocalPart) {
            foreach ($termsArray as $t) {
                if (strpos($uriLocalPart, $t) !== false) {
                    $weight += 0.4/count($termsArray);
                }
            }
        }

        if (null !== $object) {
            foreach ($termsArray as $t) {
                if (strpos($object, $t) !== false) {
                    $weight += 0.5/count($termsArray);
                }
            }
        }

        if (strlen(implode('', $termsArray)) === strlen(str_replace(' ', '', trim($object)))) {
            $weight += 0.1;
        }

        return $weight;
    }

    /**
     * Returns a list of registered URI schemes.
     *
     * @return array
     */
    private function _listIanaSchemes()
    {
        return array('aaa', 'aaas', 'acap', 'cap', 'cid', 'crid', 'data', 'dav', 'dict', 'dns', 'fax', 'file', 'ftp',
            'go', 'gopher', 'h323', 'http', 'https', 'iax', 'icap', 'im', 'imap', 'info', 'ipp', 'iris', 'iris.beep',
            'iris.xpc', 'iris.xpcs', 'iris.lwz', 'ldap', 'mailto', 'mid', 'modem', 'msrp', 'msrps', 'mtqp', 'mupdate',
            'news', 'nfs', 'nntp', 'opaquelocktoken', 'pop', 'pres', 'rtsp', 'service', 'shttp', 'sieve', 'sip', 'sips',
            'snmp', 'soap.beep', 'soap.beeps', 'tag', 'tel', 'telnet', 'tftp', 'thismessage', 'tip', 'tv', 'urn',
            'vemmi', 'xmlrpc.beep', 'xmlrpc.beeps', 'xmpp', 'z39.50r', 'z39.50s', 'afs', 'dtn', 'mailserver', 'pack',
            'tn3270', 'prospero', 'snews', 'videotex', 'wais'
        );
    }

    /**
     * Searches the local database for URIs. If mode is set to properties only,
     * only defined properties and URIs that are used at least once as a
     * property are returned.
     *
     * the classes array is used for class restrictions
     *
     * @param array $termsArray
     * @param string $modelUri
     * @param int $mode
     * @param int $limit
     * @param array $classes
     *
     * @return array
     */
    private function _searchLocal(array $termsArray, $modelUri, $mode, $limit, $classes = array() )
    {
        if ($mode === self::SEARCH_MODE_PROPERTIES) {
            return $this->_searchLocalPropertiesOnly($termsArray, $modelUri, $limit);
        }

        $store = Erfurt_App::getInstance()->getStore();

        // get a store specific text-search query2 object
        $searchPattern = $store->getSearchPattern(implode(" ", $termsArray), $modelUri);
        $query = new Erfurt_Sparql_Query2();
        $query->addElements($searchPattern);
        $projVar = new Erfurt_Sparql_Query2_Var('resourceUri');
        $query->addProjectionVar($projVar);

        // add class restriction patterns for each class
        foreach ($classes as $class) {
            $classPattern = new Erfurt_Sparql_Query2_GroupGraphPattern();
            $classPattern->addTriple(
                $projVar,
                new Erfurt_Sparql_Query2_IriRef(EF_RDF_TYPE),
                new Erfurt_Sparql_Query2_IriRef($class)
            );
            $query->addElement($classPattern);
        }

        $query->setLimit(20);
        $query->setDistinct(true);

        $queryResult = $store->sparqlQuery($query, array('result_format' => 'extended'));

        $tempResult = array();

        foreach ($queryResult['results']['bindings'] as $row) {
            $object = isset($row['o']) ? $row['o']['value'] : null;

            $weight = $this->_getWeight($termsArray, $row['resourceUri']['value'], $object);

            if (isset($tempResult[$row['resourceUri']['value']])) {
                if ($weight > $tempResult[$row['resourceUri']['value']]) {
                    $tempResult[$row['resourceUri']['value']] = $weight;
                }
            } else {
                $tempResult[$row['resourceUri']['value']] = $weight;
            }
        }

        arsort($tempResult);

        require_once 'OntoWiki/Model/TitleHelper.php';
        require_once 'OntoWiki/Utils.php';
        if (null !== $modelUri) {
            $model = $store->getModel($modelUri, false);
            $titleHelper = new OntoWiki_Model_TitleHelper($model);
        } else {
            $titleHelper = new OntoWiki_Model_TitleHelper();
        }
        $titleHelper->addResources(array_keys($tempResult));

        $translate = $this->_owApp->translate;

        // create different source description strings
        if (count($classes) > 0) {
            $sourceString = $translate->_('Local Search') . ' ('.$translate->_('recommended').')';
        } else {
            $sourceString = $translate->_('Local Search');
        }

        $result = array();
        foreach ($tempResult as $uri => $w) {
            $title = $titleHelper->getTitle($uri);

            if (null !== $title) {
                $result[$uri] = str_replace('|', '&Iota;', $title) . '|' . $uri . '|' . $sourceString;
            } else {
                $result[$uri] = OntoWiki_Utils::compactUri($uri) . $uri . '|' . $sourceString;
            }
        }

        return $result;
    }

    /**
     * Searches for properties in the local database.
     *
     * @param array $termsArray
     * @param string $modelUri
     * @param int $limit
     *
     * @return array
     */
    private function _searchLocalPropertiesOnly(array $termsArray, $modelUri, $limit)
    {
        require_once 'Erfurt/Sparql/SimpleQuery.php';
        $query = new Erfurt_Sparql_SimpleQuery();
        $query->setProloguePart('SELECT DISTINCT ?uri ?o');

        if (null !== $modelUri) {
            $query->addFrom($modelUri);
        }

        $where = '{ { ?uri ?p ?o . ?uri <' . EF_RDF_TYPE . '> ?o2 .
            FILTER (
                sameTerm(?o2, <http://www.w3.org/1999/02/22-rdf-syntax-ns#Property>) ||
                sameTerm(?o2, <http://www.w3.org/2002/07/owl#DatatypeProperty>) ||
                sameTerm(?o2, <http://www.w3.org/2002/07/owl#ObjectProperty>)
            )
            FILTER ((';

        $uriRegexFilter = array();
        foreach ($termsArray as $t) {
            $uriRegexFilter[] = 'regex(str(?uri), "' . $t . '", "i")';
        }
        $where .= implode(' && ', $uriRegexFilter) . ') || (isLiteral(?o) && ';

        $oRegexFilter = array();
        foreach ($termsArray as $t) {
            $oRegexFilter[] = 'regex(?o, "' . $t . '", "i")';
        }
        $where .= implode(' && ', $oRegexFilter) . ')) } UNION {';

        $where .= '?s ?uri ?o .
                  FILTER (';
        $where .= implode(' && ', $uriRegexFilter) . ') } }';

        $query->setWherePart($where);
        $query->setOrderClause('?uri');
        $query->setLimit($limit);

        $store = Erfurt_App::getInstance()->getStore();
        $queryResult = $store->sparqlQuery($query, array('result_format' => 'extended'));

        $tempResult = array();
        foreach ($queryResult['results']['bindings'] as $row) {
            if ($row['o']['type'] === 'literal') {
                $weight = $this->_getWeight($termsArray, $row['uri']['value'], $row['o']['value']);
            } else {
                $weight = $this->_getWeight($termsArray, $row['uri']['value']);
            }

            if (isset($tempResult[$row['uri']['value']])) {
                if ($weight > $tempResult[$row['uri']['value']]) {
                    $tempResult[$row['uri']['value']] = $weight;
                }
            } else {
                $tempResult[$row['uri']['value']] = $weight;
            }
        }

        arsort($tempResult);

        require_once 'OntoWiki/Model/TitleHelper.php';
        require_once 'OntoWiki/Utils.php';
        if (null !== $modelUri) {
            $model = $store->getModel($modelUri, false);
            $titleHelper = new OntoWiki_Model_TitleHelper($model);
        } else {
            $titleHelper = new OntoWiki_Model_TitleHelper();
        }
        $titleHelper->addResources(array_keys($tempResult));

        $translate = $this->_owApp->translate;
        $result = array();
        foreach ($tempResult as $uri => $w) {
            $title = $titleHelper->getTitle($uri);

            if (null !== $title) {
                $result[$uri] = str_replace('|', '&Iota;', $title) . '|' . $uri . '|' . $translate->_('Local Search');
            } else {
                $result[$uri] = OntoWiki_Utils::compactUri($uri) . $uri . '|' .
                    $translate->_('Local Search');
            }
        }

        return $result;
    }


    // ------------------------------------------------------------------------
    // --- Statement import related methods -----------------------------------
    // ------------------------------------------------------------------------

    /**
     * Tests whether data is available for a given URI and wrapper name.
     *
     * uri - Mandatory parameter that contains the URI to test.
     *
     * wrapper - Optional parameter, which contains the name of the wrapper
     *           to use. If not given linkeddata is used as default.
     */
    /*public function testAction()
    {
        if (!isset($this->_request->uri)) {
            $this->_response->setRawHeader('HTTP/1.0 400 Bad Request');
            echo '400 Bad Request - The uri parameter is missing.';
            exit;
        }
        $uri = urldecode($this->_request->uri);

        if (isset($this->_request->wrapper)) {
            $wrapperName = $this->_request->wrapper;
        } else {
            $wrapperName = 'linkeddata';
        }

        $result = false;
        try {
            $result = $this->_wrapperRegisty->getWrapperInstance($wrapperName)->isAvailable($uri, $this->_graphUri);
        } catch (Exception $e) {
            $result = false;
        }

        $this->_response->setHeader('Content-Type', 'application/json', true);
        echo json_encode($result);
        exit;
    }
    */

    /**
     * Imports data available for a given URI and wrapper name.
     *
     * uri - Mandatory parameter that contains the URI to test.
     *
     * wrapper - Optional parameter, which contains the name of the wrapper
     *           to use. If not given linkeddata is used as default.
     */
    public function importAction()
    {
        // Disable rendering
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();

        // We require GET requests here.
        if (!$this->_request->isGet()) {
            $this->_response->setException(new OntoWiki_Http_Exception(400));
            return;
        }

        // uri param is required.
        if (!isset($this->_request->uri)) {
            $this->_response->setException(new OntoWiki_Http_Exception(400));
            return;
        }
        $uri = urldecode($this->_request->uri);

        $wrapperName = 'linkeddata';
        if (isset($this->_request->wrapper)) {
            $wrapperName = $this->_request->wrapper;
        }

        $res = self::import(
            $this->_graphUri,
            $uri,
            $this->_getProxyUri($uri),
            isset(
                $this->_privateConfig->fetch->allData
            ) && (
                (boolean) $this->_privateConfig->fetch->allData === true
            ),
            !isset(
                $this->_privateConfig->fetch->preset
            ) ? array() : $this->_privateConfig->fetch->preset->toArray(),
            !isset(
                $this->_privateConfig->fetch->default->exception
            ) ? array() : $this->_privateConfig->fetch->default->exception->toArray(),
            $wrapperName,
            $this->_privateConfig->fetch->default->mode
        );
        if ($res == self::IMPORT_OK) {
            return $this->_sendResponse(
                true,
                'Data was found for the given URI. Statements were added.',
                OntoWiki_Message::INFO
            );
        } else if ($res == self::IMPORT_WRAPPER_ERR) {
            return $this->_sendResponse(false, 'The wrapper had an error.', OntoWiki_Message::ERROR);
        } else if ($res == self::IMPORT_NO_DATA) {
            return $this->_sendResponse(false, 'No statements were found.', OntoWiki_Message::ERROR);
        } else if ($res == self::IMPORT_WRAPPER_INSTANCIATION_ERR) {
            return $this->_sendResponse(false, 'could not get wrapper instance.', OntoWiki_Message::ERROR);
            //$this->_response->setException(new OntoWiki_Http_Exception(400));
        } else if ($res == self::IMPORT_NOT_EDITABLE) {
            return $this->_sendResponse(false, 'you cannot write to this model.', OntoWiki_Message::ERROR);
            //$this->_response->setException(new OntoWiki_Http_Exception(403));
        } else if ($res == self::IMPORT_WRAPPER_EXCEPTION) {
            return $this->_sendResponse(false, 'the wrapper run threw an error.', OntoWiki_Message::ERROR);
        } else if ($res == DatagatheringController::IMPORT_WRAPPER_NOT_AVAILABLE) {
            return $this->_sendResponse($res, 'the data is not available.', OntoWiki_Message::ERROR);
        } else {
            return $this->_sendResponse(false, 'unexpected return value.', OntoWiki_Message::ERROR);
        }
    }

    public static function filterStatements(
        $statements, $uri, $all = true, $presets = array(),
        $exceptedProperties = array(), $fetchMode = 'none'
    )
    {
        // TODO handle configuration for import...
        if ($all) {
            // Keep all data...
            return $statements;
        } else {
            // Only use those parts of the data, that have the resource URI as subject.
            if (isset($statements[$uri])) {
                $statements = array(
                    $uri => $statements[$uri]
                );
            } else {
                $statements = array();
            }

            // We also need to remove all blank node objects
            $newResult = array();
            foreach ($statements as $s => $pArray) {
                foreach ($pArray as $p => $oArray) {
                    foreach ($oArray as $oSpec) {
                        if ($oSpec['type'] !== 'bnode') {
                            if (!isset($newResult[$s])) {
                                $newResult[$s] = array();
                            }
                            if (!isset($newResult[$s][$p])) {
                                $newResult[$s][$p] = array();
                            }
                            $newResult[$s][$p][] = $oSpec;
                        }
                    }
                }
            }
            $statements = $newResult;
        }

        $presetMatch = false;
        foreach ($presets as $i => $preset) {
            if (self::_matchUriStatic($preset['match'], $uri)) {
                $presetMatch = true;
                break;
            }
        }

        $data = $statements;
        $result = null;
        if ($presetMatch !== false) {
            // Use the preset
            if (isset($presets[$presetMatch]['mode']) && $presets[$presetMatch]['mode'] === 'none') {
                // Start with an empty result.
                $result = array();
                if (isset($presets[$presetMatch]['exception'])) {
                    foreach ($presets[$presetMatch]['exception'] as $exception) {
                        if (isset($data[$uri][$exception])) {
                            if (!isset($result[$uri])) {
                                $result[$uri] = array();
                            }
                            if (!isset($result[$uri][$exception])) {
                                $result[$uri][$exception] = array();
                            }

                            foreach ($data[$uri][$exception] as $o) {
                                if ($o['type'] === 'literal') {
                                    if (isset($presets[$presetMatch]['lang'])) {
                                        foreach ($presets[$presetMatch]['lang'] as $lang) {
                                            if (isset($o['lang']) && $o['lang'] === $lang) {
                                                $result[$uri][$exception][] = $o;
                                            }
                                        }
                                    } else {
                                        $result[$uri][$exception][] = $o;
                                    }
                                } else {
                                    $result[$uri][$exception][] = $o;
                                }
                            }

                            if (isset($presets[$presetMatch]['lang'])) {
                                if (count($result[$uri][$exception]) === 0) {
                                    foreach ($data[$uri][$exception] as $o) {
                                        if (!isset($o['lang'])) {
                                            $result[$uri][$exception][] = $o;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                // Use the default rule.
                // Start with all data.
                $result = $data;
                if (isset($presets[$presetMatch]['exception'])) {
                    foreach ($presets[$presetMatch]['exception'] as $exception) {
                        if (isset($data[$uri][$exception])) {
                            if (isset($result[$uri][$exception])) {
                                unset($result[$uri][$exception]);
                            }
                        }
                    }
                }
            }
        } else {
            if ($fetchMode === 'none') {
                // Start with an empty result.
                $result = array();
                foreach ($exceptedProperties as $exception) {
                    if (isset($data[$uri][$exception])) {
                        if (!isset($result[$uri])) {
                            $result[$uri] = array();
                        }
                        $result[$uri][$exception] = $data[$uri][$exception];
                    }
                }
            } else {
                // Start with all data.
                $result = $data;
                foreach ($exceptedProperties as $exception) {
                    if (isset($data[$uri][$exception])) {
                        if (isset($result[$uri][$exception])) {
                            unset($result[$uri][$exception]);
                        }
                    }
                }
            }
        }
        $statements = $result;
        return $statements;
    }

    /**
     *
     * @param string $graphUri
     * @param string $uri
     * @param type $locator
     * @param boolean $all
     * @param array $presets
     * @param array $exceptedProperties
     * @param string $wrapperName
     * @param string $fetchMode
     * @param string $action
     * @param boolean $versioning
     * @return int status code
     */
    //TODO refactor these 11 parameters (use a config object or break it down with adapters etc)
    public static function import (
        $graphUri, $uri, $locator, $all = true, $presets = array(),
        $exceptedProperties = array(), $wrapperName = 'linkeddata', $fetchMode = 'none',
        $action = 'add', $versioning = true, $filterCallback = null
    )
    {
        // Check whether user is allowed to write the model.
        $erfurt = Erfurt_App::getInstance();
        $store = $erfurt->getStore();
        $storeGraph = $store->getModel($graphUri);
        if (!$storeGraph || !$storeGraph->isEditable()) {
            return self::IMPORT_NOT_EDITABLE;
        }

        $r = new Erfurt_Rdf_Resource($uri);
        $r->setLocator($locator);

        // Try to instanciate the requested wrapper
        $wrapper = null;
        try {
            $wrapper = Erfurt_Wrapper_Registry::getInstance()->getWrapperInstance($wrapperName);
        } catch (Erfurt_Wrapper_Exception $e) {
            return self::IMPORT_WRAPPER_INSTANCIATION_ERR;
        }

        $wrapperResult = null;
        try {
            $wrapperResult = $wrapper->run($r, $graphUri, $all);
        } catch (Erfurt_Wrapper_Exception $e) {
            return self::IMPORT_WRAPPER_EXCEPTION;
        }

        if ($wrapperResult === false) {
            return self::IMPORT_WRAPPER_NOT_AVAILABLE;
        } else if (is_array($wrapperResult)) {
            if (isset($wrapperResult['status_codes'])) {
                if (in_array(Erfurt_Wrapper::RESULT_HAS_ADD, $wrapperResult['status_codes'])) {
                    $newStatements = $wrapperResult['add'];

                    $stmtBeforeCount = $store->countWhereMatches(
                        $graphUri,
                        '{ ?s ?p ?o }',
                        '*'
                    );

                    if ($versioning) {
                        // Prepare versioning...
                        $versioning = $erfurt->getVersioning();
                        $actionSpec = array(
                            'type'        => self::VERSIONING_IMPORT_ACTION_TYPE,
                            'modeluri'    => $graphUri,
                            'resourceuri' => $uri
                        );

                        // Start action, add statements, finish action.
                        $versioning->startAction($actionSpec);
                    }

                    $newStatements = self::filterStatements(
                        $newStatements, $uri, $all, $presets, $exceptedProperties, $fetchMode
                    );
                    if ($filterCallback != null && is_array($filterCallback)) {
                        $newStatements = call_user_func($filterCallback, $newStatements);
                    }

                    if ($action == 'add') {
                        $store->addMultipleStatements($graphUri, $newStatements);
                    } else if ($action == 'update') {
                        $queryoptions = array(
                            'use_ac'                 => false,
                            'result_format'          => STORE_RESULTFORMAT_EXTENDED,
                            'use_additional_imports' => false
                        );
                        $oldStatements = $store->sparqlQuery(
                            'SELECT * FROM <'.$graphUri.'> WHERE { ?s ?p ?o }',
                            $queryoptions
                        );
                        //transform resultset to rdf/php statements
                        $modelOld = new Erfurt_Rdf_MemoryModel();
                        $modelOld->addStatementsFromSPOQuery($oldStatements);
                        $modelNew = new Erfurt_Rdf_MemoryModel($newStatements);
                        $storeGraph->updateWithMutualDifference(
                            $modelOld->getStatements(),
                            $modelNew->getStatements()
                        );
                    }

                    if ($versioning) {
                        $versioning->endAction();
                    }

                    $stmtAfterCount = $store->countWhereMatches(
                        $graphUri,
                        '{ ?s ?p ?o }',
                        '*'
                    );

                    $stmtAddCount = $stmtAfterCount - $stmtBeforeCount;

                    if ($stmtAddCount > 0) {
                        // TODO test ns
                        // If we added some statements, we check for additional namespaces and add them.
                        if (in_array(Erfurt_Wrapper::RESULT_HAS_NS, $wrapperResult['status_codes'])) {
                            $namespaces = $wrapperResult['ns'];

                            $erfurtNamespaces = $erfurt->getNamespaces();

                            foreach ($namespaces as $ns => $prefix) {
                                try {
                                    $erfurtNamespaces->addNamespacePrefix(
                                        $graphUri,
                                        $prefix,
                                        $ns,
                                        false
                                    );
                                } catch (Exception $e) {
                                    // Ignore...
                                }
                            }
                        }
                        return self::IMPORT_OK;

                    } else {
                        return self::IMPORT_NO_DATA;
                    }
                } else {
                    return self::IMPORT_NO_DATA;
                }
            } else {
                return self::IMPORT_WRAPPER_ERR;
            }
        } else {
            return self::IMPORT_WRAPPER_ERR;
        }
    }

    private function _sendResponse($returnValue, $message = null, $messageType = OntoWiki_Message::SUCCESS)
    {
        if (null !== $message) {
            $translate = $this->_owApp->translate;

            $message = $translate->_($message);
            $this->_owApp->appendMessage(
                new OntoWiki_Message($message, $messageType)
            );
        }

        $this->_response->setHeader('Content-Type', 'application/json', true);
        $this->_response->setBody(json_encode($returnValue));
        $this->_response->sendResponse();
        exit;
    }

    // ------------------------------------------------------------------------
    // --- Statement sync related methods -------------------------------------
    // ------------------------------------------------------------------------

    /**
     * Adds the sync config model and imports the schema. This can only be done
     * by an user with editing rights on the local config model or with model
     * management rights.
     */
    /*public function initAction()
    {
        try {
            $syncModel = $this->_getSyncModel();
            if (!$syncModel) {
                echo 'Something went wrong.';
                exit;
            }

            $filename = $this->_componentRoot . $this->_privateConfig->syncModelFilename;
            require_once 'Erfurt/Syntax/RdfParser.php';
            $rdfParser = Erfurt_Syntax_RdfParser::rdfParserWithFormat('rdfxml');
            $rdfParser->parseToStore(
                $filename,
                Erfurt_Syntax_RdfParser::LOCATOR_FILE,
                $this->_syncModelUri
            );

            echo 'Init done...';
            exit;
        } catch (Exception $e) {
            echo 'Something went wrong.';
            exit;
        }
    }
    */

    /**
     * This service allows to configure a resource for sync.
     *
     * uri - Mandatory URI parameter.
     *
     * wrapper - Optional wrapper parameter. Default is linked data.
     */
    /*public function configAction()
    {
        if (!(boolean)$this->_privateConfig->sync->enabled) {
            $this->_response->setRawHeader('HTTP/1.0 400 Bad Request');
            echo '400 Bad Request';
            exit;
        }

        OntoWiki_Navigation::disableNavigation();

        // Get Request: Show the user the existing data or give him default values.
        if ($this->_request->isGet()) {
            if (!isset($this->_request->uri)) {
                $this->_owApp->appendMessage(
                    new OntoWiki_Message('Missing uri parameter.', OntoWiki_Message::ERROR)
                );
                $this->view->errorFlag = true;
                return;
            }
            $uri = urldecode($this->_request->uri);

            $modelUri = $this->_graphUri;
            if (null === $modelUri) {
                $this->_owApp->appendMessage(
                    new OntoWiki_Message('No model selected.', OntoWiki_Message::ERROR)
                );
                $this->view->errorFlag = true;
                return;
            }

            $wrapperName = 'linkeddata'; // Default
            if (isset($this->_request->wrapper)) {
                $wrapperName = $this->_request->wrapper;
            }

            $this->view->uri = $uri;
            $this->view->modelUri = $modelUri;
            $this->view->wrapperName = $wrapperName;

            $syncConfig = $this->_getSyncConfig($uri, $wrapperName, $modelUri);
            $translate = $this->_owApp->translate;
            if ($syncConfig === false) {
                $syncQuery = "SELECT ?s ?p ?o \nWHERE {\n ".
                    "?s ?p ?o \n FILTER (sameTerm(?s, <" . $uri . ">) && !isBlank(?o))\n}";
                $checkHasChanged = false;

                $msg = $translate->_('No existing sync configuration. Create one now by clicking the Save button.');
                $this->_owApp->appendMessage(new OntoWiki_Message($msg, OntoWiki_Message::INFO));
            } else {
                $syncConfig = $syncConfig[0];
                $syncQuery = $syncConfig['syncQuery'];
                if (isset($syncConfig['checkHasChanged'])) {
                    $checkHasChanged = $syncConfig['checkHasChanged'];
                } else {
                    $checkHasChanged = false;
                }
            }

            $this->view->syncQuery = $syncQuery;
            $this->view->checkHasChanged = $checkHasChanged ? 'checked="checked"' : '';

            // Make sure the current user is allowed to edit the given model. Otherwise he should not be able to sync.
            $store = $this->_erfurt->getStore();
            $model = $store->getModel($modelUri);
            if (!$model || !$model->isEditable()) {
                $this->_owApp->appendMessage(
                    new OntoWiki_Message("No write permissions on given model", OntoWiki_Message::WARNING)
                );
                $this->view->errorFlag = true;
                return;
            } else {
                $toolbar = $this->_owApp->toolbar;
        		$toolbar->appendButton(OntoWiki_Toolbar::SUBMIT, array('name' => 'Save'))
        		        ->appendButton(OntoWiki_Toolbar::RESET, array('name' => 'Cancel'));
        		$this->view->placeholder('main.window.toolbar')->set($toolbar);
            }
        } else if ($this->_request->isPost()) {
            $post = $this->_request->getPost();

            $uri = $post['resourceuri'];
            $modelUri = $post['modeluri'];
            $wrapperName = $post['wrappername'];

            // Make sure the current user is allowed to edit the given model. Otherwise he should not be able to sync.
            $store = $this->_erfurt->getStore();
            $model = $store->getModel($modelUri);
            if (!$model || !$model->isEditable()) {
                $this->_owApp->appendMessage(
                    new OntoWiki_Message("No write permissions on given model", OntoWiki_Message::WARNING)
                );
                $this->view->errorFlag = true;
                return;
            }

            $syncConfig = $this->_getSyncConfig($uri, $wrapperName, $modelUri);
            $syncConfigUri = $this->_syncModelUri . 'SyncConfig' . md5($uri.$wrapperName.$modelUri);
            if ($syncConfig === false) {
                // Add new config
                $stmtArray = array(
                    $syncConfigUri => array(
                        EF_RDF_TYPE => array(array('type' => 'uri', 'value' => $this->_properties['syncConfigClass'])),
                        $this->_properties['targetModel'] => array(array(
                            'type' => 'uri',
                            'value' => $post['modeluri']
                        )),
                        $this->_properties['wrapperName'] => array(array(
                            'type' => 'literal',
                            'value' => $post['wrappername']
                        )),
                        $this->_properties['syncResource'] => array(array(
                            'type' => 'uri',
                            'value' => $post['resourceuri']
                        )),
                        $this->_properties['syncQuery'] => array(array(
                            'type'  => 'literal',
                            'value' => $post['syncQuery']
                        )),
                        $this->_properties['checkHasChanged'] => array(array(
                            'type'  => 'literal',
                            'value' => isset($post['checkhaschanged']) ? 'true' : 'false',
                            'datatype' => EF_XSD_BOOLEAN
                        ))
                    )
                );

                $store->addMultipleStatements($this->_syncModelUri, $stmtArray, false);
            } else {
                $syncConfig = $syncConfig[0];
                // Update the config...
                $newValues = array(
                    $syncConfigUri => array(
                        $this->_properties['syncQuery'] => array(array(
                            'type'  => 'literal',
                            'value' => $post['syncQuery']
                        )),
                        $this->_properties['checkHasChanged'] => array(array(
                            'type'  => 'literal',
                            'value' => isset($post['checkhaschanged']) ? 'true' : 'false',
                            'datatype' => EF_XSD_BOOLEAN
                        ))
                    )
                );

                $oldValues = array(
                    $syncConfigUri => array(
                        $this->_properties['syncQuery'] => array(array(
                            'type'  => 'literal',
                            'value' => $syncConfig['syncQuery']
                        ))
                    )
                );

                if (isset($syncConfig['checkHasChanged'])) {
                    $oldValues[$syncConfigUri][$this->_properties['checkHasChanged']] = array(array(
                        'type'     => 'literal',
                        'value'    => ($syncConfig['checkHasChanged'] === true) ? 'true' : 'false',
                        'datatype' => EF_XSD_BOOLEAN
                    ));
                }

                $syncModelUri = $this->_syncModelUri;
                $store->deleteMultipleStatements($syncModelUri, $oldValues, false);
                $store->addMultipleStatements($syncModelUri, $newValues, false);
            }

            $url = $this->_config->urlBase . '/datagathering/config?uri=' . urlencode($uri) .
                   '&wrapper=' . $wrapperName;

            $this->_redirect($url, array('prependBase' => false));
        }

        $translate  = $this->_owApp->translate;
        $windowTitle = $translate->_('Sync Configuration');
        $this->view->placeholder('main.window.title')->set($windowTitle);

        $this->view->formActionUrl = $this->_config->urlBase . 'datagathering/config';
		$this->view->formMethod    = 'post';
		$this->view->formClass     = 'simple-input input-justify-left';
		$this->view->formName      = 'config';
    }
    */

    /**
     * Executes a sync iff a sync is configured for the given resource.
     *
     * uri - Mandatory parameter that contains the URI to sync.
     *
     * wrapper - Optional parameter containing a specific wrapper.
     *           Otherwise all matching sync actions are executed.
     */
    /*public function syncAction()
    {
        if (!(boolean)$this->_privateConfig->sync->enabled) {
            $this->_response->setRawHeader('HTTP/1.0 400 Bad Request');
            echo '400 Bad Request';
            exit;
        }

        if (!isset($this->_request->uri)) {
            $this->_response->setRawHeader('HTTP/1.0 400 Bad Request');
            echo '400 Bad Request - The uri parameter is missing.';
            exit;
        }
        $uri = urldecode($this->_request->uri);

        $wrapperName = null;
        if (isset($this->_request->wrapper)) {
            $wrapperName = $this->_request->wrapper;
        }

        $modelUri = $this->_graphUri;

        if (null == $wrapperName || null === $modelUri) {
            $syncConfigArray = $this->_getSyncConfig($uri, $wrapperName, $modelUri);

            if (!is_array($syncConfigArray) || count($syncConfigArray) === 0) {
                if ($this->_request->isXmlHttpRequest()) {
                    $this->_response->setHeader('Content-Type', 'application/json', true);
                    echo json_encode(false);
                    exit;
                } else {
                    $this->_response->setRawHeader('HTTP/1.0 400 Bad Request');
                    echo '400 Bad Request - No sync config found.';
                    exit;
                }

            }

            foreach ($syncConfigArray as $syncConfig) {
                $w = $syncConfig['wrapperName'];
                $m = $syncConfig['targetModel'];

                $this->_sync($uri, $w, $m);
            }
        } else {
            $syncResult = $this->_sync($uri, $wrapperName, $modelUri);

            // All parameters given, but no sync config found.
            if ($syncResult === self::NO_SYNC_CONFIG_FOUND) {
                $redirect = $this->_owApp->urlBase .
                            'datagathering/config?uri=' . urlencode($uri) .
                            '&wrapper=' . urlencode($wrapperName);

                if ($this->_request->isXmlHttpRequest()) {
                    $this->_response->setHeader('Content-Type', 'application/json', true);
                    echo json_encode(array('redirect' => $redirect));
                    exit;
                } else {
                    $this->_response->setRawHeader('HTTP/1.0 400 Bad Request');
                    echo '400 Bad Request - No sync config found.';
                    exit;
                }
            } else if ($syncResult === self::SYNC_SUCCESS) {
                if ($this->_request->isXmlHttpRequest()) {
                    $this->_response->setHeader('Content-Type', 'application/json', true);
                    echo json_encode(true);
                    exit;
                } else {
                    echo 'Successfully synced.';
                    exit;
                }
            } else {
                 if ($this->_request->isXmlHttpRequest()) {
                     $this->_response->setHeader('Content-Type', 'application/json', true);
                     echo json_encode(false);
                     exit;
                 } else {
                     echo '400 Bad Request - No sync config found.';
                     exit;
                 }
            }
        }
    }
    */

    /**
     * This service returns the date/time of the last modification of the given
     * resource uri, iff a sync configuration exists and the check for updates
     * option is set. The source needs to send a Last-Modified header. E.g.
     * OntoWiki sends this header.
     *
     * uri - This mandatory parameter contains the uri to check.
     *
     * wrapper - This optional parameter contains the name of the wrapper to
     *           use. If not given, this defaults to linkeddata.
     *
     * On success returns the date/time of the last sync, as well as the
     * date/time of the last modification of the resource.
     */
    /*public function modifiedAction()
    {
        if (!(boolean)$this->_privateConfig->sync->enabled) {
            $this->_response->setRawHeader('HTTP/1.0 400 Bad Request');
            echo '400 Bad Request';
            exit;
        }

        if (!isset($this->_request->uri)) {
            $this->_response->setRawHeader('HTTP/1.0 400 Bad Request');
            echo '400 Bad Request - The uri parameter is missing.';
            exit;
        }
        $uri = urldecode($this->_request->uri);
        $modelUri = $this->_graphUri;

        $wrapperName = 'linkeddata';
        if (isset($this->_request->wrapper)) {
            $wrapper = $this->_request->wrapper;
        }

        $syncConfigArray = $this->_getSyncConfig($uri, $wrapperName, $modelUri);
        if (!is_array($syncConfigArray) || count($syncConfigArray) === 0) {
            if ($this->_request->isXmlHttpRequest()) {
                $this->_response->setHeader('Content-Type', 'application/json', true);
                echo json_encode(false);
                exit;
            } else {
                echo 'No sync config found.';
                exit;
            }
        }

        $syncConfig = $syncConfigArray[0];
        if (!isset($syncConfig['checkHasChanged']) || !$syncConfig['checkHasChanged']) {
            if ($this->_request->isXmlHttpRequest()) {
                $this->_response->setHeader('Content-Type', 'application/json', true);
                echo json_encode(false);
                exit;
            } else {
                echo 'Not configured for update check.';
                exit;
            }
        }

        $headers = get_headers($uri, true);

        if (isset($headers['Last-Modified'])) {
            $ts = 0;
            if (is_array($headers['Last-Modified'])) {
                foreach ($headers['Last-Modified'] as $mod) {
                    $temp = strtotime($mod);
                    if ($temp > $ts) {
                        $ts = $temp;
                    }
                }
            } else {
                $ts = strtotime($headers['Last-Modified']);
            }

            if ($ts > 0) {
                if (isset($syncConfig['lastSyncDateTime'])) {
                    $lastSyncTs = strtotime($syncConfig['lastSyncDateTime']);
                } else {
                    $lastSyncTs = 0;
                }


                if ($ts > $lastSyncTs) {
                    $result = array(
                        'lastMod' => date('r', $ts)
                    );

                    if ($lastSyncTs > 0) {
                        $result['lastSync'] = date('r', $lastSyncTs);
                    }

                    $this->_response->setHeader('Content-Type', 'application/json', true);
                    echo json_encode($result);
                    exit;
                } else {
                    if ($this->_request->isXmlHttpRequest()) {
                        $this->_response->setHeader('Content-Type', 'application/json', true);
                        echo json_encode(false);
                        exit;
                    } else {
                        echo 'No changes since last sync.';
                        exit;
                    }
                }


            } else {
                if ($this->_request->isXmlHttpRequest()) {
                    $this->_response->setHeader('Content-Type', 'application/json', true);
                    echo json_encode(false);
                    exit;
                } else {
                    echo 'No valid date/time found.';
                    exit;
                }
            }
        } else {
            if ($this->_request->isXmlHttpRequest()) {
                $this->_response->setHeader('Content-Type', 'application/json', true);
                echo json_encode(false);
                exit;
            } else {
                echo 'No Last-Modified information found.';
                exit;
            }
        }
    }*/

    /**
     * Filters a result regarding the configured sparql query.
     *
     * @param array $result
     * @param string $uri
     * @param string $wrapperName
     * @param string $modelUri
     *
     * @return array
     */
    private function _filterResult($result, $uri, $wrapperName, $modelUri)
    {
        $store = Erfurt_App::getInstance()->getStore();
        $syncConfig = $this->_getSyncConfig($uri, $wrapperName, $modelUri);

        if (!is_array($syncConfig) || count($syncConfig) === 0) {
            return array();
        }
        $syncConfig = $syncConfig[0];

        // TODO We need support for in-memory models... this is a workaround
        $tempModelUri = $this->_syncModelHelperBase . md5($uri.$wrapperName.$modelUri.time());
        $tempModel = $store->getNewModel($tempModelUri, '', 'rdf', false);
        $store->addMultipleStatements($tempModelUri, $result);
        $simpleQuery = Erfurt_Sparql_SimpleQuery::initWithString($syncConfig['syncQuery']);
        $simpleQuery->addFrom($tempModelUri);
        $sparqlResult = $store->sparqlQuery($simpleQuery, array('result_format' => 'extended', 'use_ac' => false));
        $store->deleteModel($tempModelUri);

        $retVal = array();
        foreach ($sparqlResult['results']['bindings'] as $row) {
            if (!isset($retVal[$row['s']['value']])) {
                $retVal[$row['s']['value']] = array();
            }
            if (!isset($retVal[$row['s']['value']][$row['p']['value']])) {
                $retVal[$row['s']['value']][$row['p']['value']] = array();
            }

            $oArray = array(
                'value' => $row['o']['value']
            );

            if ($row['o']['type'] === 'typed-literal') {
                $oArray['type'] = 'literal';
                $oArray['dataytpe'] = $row['o']['datatype'];
            } else {
                $oArray['type'] = $row['o']['type'];
            }

            if (isset($row['o']['xml:lang'])) {
                $oArray['lang'] = $row['o']['xml:lang'];
            }

            $retVal[$row['s']['value']][$row['p']['value']][] = $oArray;
        }

        return $retVal;
    }

    /**
     * Fetches the data from the wrapper.
     *
     * @param string $uri
     * @param string $wrapperName
     * @param string $modelUri
     *
     * @return array
     */
    private function _getData($uri, $wrapperName, $modelUri)
    {
        try {
            $wrapper = $this->_wrapperRegisty->getWrapperInstance($wrapperName);

            $wrapperResult = $wrapper->run($uri, $modelUri);
            if (is_array($wrapperResult) && isset($wrapperResult['add'])) {
                $wrapperResult = $wrapperResult;
            } else {
                $wrapperResult = array();
            }
        } catch (Exception $e) {
            $wrapperResult = array();
        }

        return $wrapperResult;
    }





    private function _getProxyUri($uri)
    {
        // If at least one rewrite rule is defined, we iterate through them.
        if (isset($this->_privateConfig->rewrite)) {
            $rulesArray = $this->_privateConfig->rewrite->toArray();
            foreach ($rulesArray as $ruleId => $ruleSpec) {
                $proxyUri = preg_replace($ruleSpec['pattern'], $ruleSpec['replacement'], $uri);
                if ($proxyUri !== $uri) {
                    return $proxyUri;
                }
            }
        }

        return null;
    }

    /**
     * Returns all sync configs for the given parameters or false, if no such exists.
     *
     * @param string $uri The resource uri.
     * @param string $wrapperName The wrapper name.
     * @param string $modelUri The model uri.
     *
     * @return array|false
     */
// TODO Remove?
    /*private function _getSyncConfig($uri, $wrapperName = null, $modelUri = null)
    {
        $hash = md5($uri . (string)$wrapperName . (string)$modelUri);

        if (!isset($this->_syncConfig[$hash])) {
            $store = Erfurt_App::getInstance()->getStore();

            require_once 'Erfurt/Sparql/SimpleQuery.php';
            $query = new Erfurt_Sparql_SimpleQuery();
            $query->setProloguePart('SELECT ?s');
            $query->addFrom($this->_syncModelUri);
            $where = 'WHERE {
                ?s <' . EF_RDF_TYPE . '> <' . $this->_properties['syncConfigClass'] . '> .
                ?s <' . $this->_properties['syncResource'] . '> <' . $uri . '> . ';

            if (null !== $modelUri) {
                $where .= '?s <' . $this->_properties['targetModel'] .'> <' . $modelUri . '> . ';
            }
            if (null !== $wrapperName) {
                $where .= '?s <' . $this->_properties['wrapperName'] . '> "' . $wrapperName . '" .';
            }

            $where .= '}';
            $query->setWherePart($where);

            $result = $store->sparqlQuery($query, array('use_ac' => false));

            if (count($result) === 0) {
                return false;
            } else {
                $configUris = array();
                foreach ($result as $row) {
                    $configUris[] = $row['s'];
                }
            }

            $query = new Erfurt_Sparql_SimpleQuery();
            $query->setProloguePart('SELECT ?s ?p ?o');
            $query->addFrom($this->_syncModelUri);
            $query->setWherePart('WHERE {
                ?s ?p ?o . FILTER (sameTerm(?s, <' . implode('>) || sameTerm(?s, <', $configUris)  . '>))
            }');

            $result = $store->sparqlQuery($query);

            $retVal = array();
            foreach($result as $row) {
                if (!isset($retVal[$row['s']])) {
                    $retVal[$row['s']] = array(
                        'uri' => $row['s']
                    );
                }

                switch ($row['p']) {
                    case $this->_properties['targetModel']:
                        $retVal[$row['s']]['targetModel'] = $row['o'];
                        break;
                    case $this->_properties['syncResource']:
                        $retVal[$row['s']]['syncResource'] = $row['o'];
                        break;
                    case $this->_properties['wrapperName']:
                        $retVal[$row['s']]['wrapperName'] = $row['o'];
                        break;
                    case $this->_properties['lastSyncPayload']:
                        $retVal[$row['s']]['lastSyncPayload'] = unserialize($row['o']);
                        break;
                    case $this->_properties['lastSyncDateTime']:
                        $retVal[$row['s']]['lastSyncDateTime'] = $row['o'];
                        break;
                    case $this->_properties['syncQuery']:
                        $retVal[$row['s']]['syncQuery'] = $row['o'];
                        break;
                    case $this->_properties['checkHasChanged']:
                        $retVal[$row['s']]['checkHasChanged'] = ($row['o'] === 'true') ? true : false;
                        break;
                }
            }

            $this->_syncConfig[$hash] = array_values($retVal);
        }

        return $this->_syncConfig[$hash];
    }*/

    /**
     * Checks whether the sync model is available and imports it if needed.
     *
     * @return Erfurt_Rdf_Model
     */
// TODO Remove?
    /*private function _getSyncModel()
    {
        $store = Erfurt_App::getInstance()->getStore();

        if (!$store->isModelAvailable($this->_syncModelUri, false)) {
            try {
                $syncModel = $store->getNewModel($this->_syncModelUri, '', 'owl');

                if (!$syncModel) {
                    return false;
                }

                $sysOnt = Erfurt_App::getInstance()->getSysOntModel();
                $store->addStatement(
                    $sysOnt->getModelUri(),
                    'http://ns.ontowiki.net/SysOnt/Anonymous',
                    'http://ns.ontowiki.net/SysOnt/denyModelView',
                    array(
                        'type'  => 'uri',
                        'value' => $this->_syncModelUri
                    )
                );

                $store->addStatement(
                    $sysOnt->getModelUri(),
                    'http://localhost/OntoWiki/Config/DefaultUserGroup',
                    'http://ns.ontowiki.net/SysOnt/denyModelView',
                    array(
                        'type'  => 'uri',
                        'value' => $this->_syncModelUri
                    )
                );
            } catch (Exception $e) {
                return false;
            }
        } else {
            $syncModel = $store->getModel($this->_syncModelUri, false);
        }

        return $syncModel;
    }
    */

    /**
     * Executes the sync.
     */
// TODO Remove or make usable
    /*private function _sync($uri, $wrapperName, $modelUri)
    {
        $store = Erfurt_App::getInstance()->getStore();

        $importModel = $store->getModel($modelUri);
        if (!$importModel || !$importModel->isEditable()) {
            return self::NO_EDITING_RIGHTS;
        }

        $syncConfig = $this->_getSyncConfig($uri, $wrapperName, $modelUri);
        if ($syncConfig === false) {
            return self::NO_SYNC_CONFIG_FOUND;
        }
        $syncConfig = $syncConfig[0];

        $configUri = $syncConfig['uri'];
        if (isset($syncConfig['lastSyncPayload'])) {
            $lastSyncPayload = $syncConfig['lastSyncPayload'];
        } else {
            // No last sync, so last payload was empty (we need this info for update with mutual difference).
            $lastSyncPayload = array();
        }

        $newPayload = $this->_getData($uri, $wrapperName, $modelUri);
        $namespaces = $newPayload['ns'];
        $newPayload = $newPayload['add'];
        $newPayload = $this->_filterResult($newPayload, $uri, $wrapperName, $modelUri);

        $versioning = Erfurt_App::getInstance()->getVersioning();
        $actionSpec = array(
            'type'        => self::VERSIONING_SYNC_ACTION_TYPE,
            'modeluri'    => $modelUri,
            'resourceuri' => $uri
        );

        $erfurtNamespaces = Erfurt_App::getInstance()->getNamespaces();

        // Try to add new namespaces
        if (is_array($namespaces)) {
            foreach ($namespaces as $ns => $prefix) {
                try {
                    $erfurtNamespaces->addNamespacePrefix(
                        $modelUri,
                        $prefix,
                        $ns,
                        false
                    );
                } catch (Erfurt_Namespaces_Exception $e) {
                    // Ignore...
                }
            }
        }

        try {
            $versioning->startAction($actionSpec);
            // We do not use update here, for otherwise there are problems, if lastSyncPayload contains data, but
            // real data differs (e.g. when deleting a resource through rollback).
            $importModel->deleteMultipleStatements($lastSyncPayload);
            $importModel->addMultipleStatements($newPayload);
            $versioning->endAction();

            // Save payload for next sync
            $add = array(
                $configUri => array(
                    $this->_properties['lastSyncPayload'] => array(array(
                        'type'  => 'literal',
                        'value' => serialize($newPayload)
                    )),
                    $this->_properties['lastSyncDateTime'] => array(array(
                        'type'  => 'literal',
                        'value' => date('c')
                    ))
                )
            );

            $store->deleteMatchingStatements(
                $this->_syncModelUri,
                $configUri,
                $this->_properties['lastSyncPayload'],
                null
            );
            $store->deleteMatchingStatements(
                $this->_syncModelUri,
                $configUri,
                $this->_properties['lastSyncDateTime'],
                null
            );
            $store->addMultipleStatements($this->_syncModelUri, $add);
        } catch (Exception $e) {
            return false;
        }

        // If we reach this point, the sync was successful.
        return self::SYNC_SUCCESS;
    }*/

    private function _matchUri($pattern, $uri)
    {
        if ((substr($pattern, 0, 7) !== 'http://')) {
            $pattern = 'http://' . $pattern;
        }

        if ((substr($uri, 0, strlen($pattern)) === $pattern)) {
            return true;
        } else {
            return false;
        }
    }

    private static function _matchUriStatic($pattern, $uri)
    {
        if ((substr($pattern, 0, 7) !== 'http://')) {
            $pattern = 'http://' . $pattern;
        }

        if ((substr($uri, 0, strlen($pattern)) === $pattern)) {
            return true;
        } else {
            return false;
        }
    }
}
