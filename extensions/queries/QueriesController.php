<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * Controller for OntoWiki Filter Module
 *
 * @category   OntoWiki
 * @package    Extensions_Queries
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class QueriesController extends OntoWiki_Controller_Component
{
    protected $_userUri;
    protected $_userName;
    protected $_userDbUri;

    public $prefixHandler;

    /**
     * init() Method to init() normal and add tabbed Navigation
     */
    public function init()
    {
        parent :: init();

        // setup the navigation
        OntoWiki::getInstance()->getNavigation()->reset();
        $tabExist = false;
        $ow = OntoWiki::getInstance();

        if ($this->_privateConfig->general->enabled->saving) {
            OntoWiki::getInstance()->getNavigation()->register(
                'listquery',
                array(
                    'controller' => 'queries',
                    'action' => 'listquery',
                    'name' => 'Saved Queries',
                    'position' => 0,
                    'active' => true
                )
            );
            $this->view->headScript()->appendFile(
                $ow->extensionManager->getComponentUrl('queries').'resources/savepartial.js'
            );
            $tabExist = true;
        }
        if ($this->_privateConfig->general->enabled->editor) {
            OntoWiki::getInstance()->getNavigation()->register(
                'queryeditor',
                array(
                    'controller' => 'queries',
                    'action' => 'editor',
                    'name' => 'Query Editor',
                    'position' => 1,
                    'active' => false
                )
            );
            $tabExist = true;
        }

        if ($this->_privateConfig->general->enabled->builder) {
            OntoWiki::getInstance()->getNavigation()->register(
                'savedqueries',
                array(
                    'controller' => 'queries',
                    'action' => 'manage',
                    'name' => 'Query Builder ',
                    'position' => 2,
                    'active' => false
                )
            );
            $tabExist = true;
        }

        if ($this->_privateConfig->general->enabled->gqb) {
            OntoWiki::getInstance()->getNavigation()->register(
                'gqb',
                array(
                    'controller' => 'queries',
                    'action' => 'display',
                    'name' => 'Graphical Query Builder',
                    'position' => 3,
                    'active' => false
                )
            );
            $tabExist = true;
        }

        if (!$tabExist) {
            OntoWiki::getInstance()->getNavigation()->disableNavigation();
        }

        $user = $this->_erfurt->getAuth()->getIdentity();
        $this->_userUri = $user->getUri();
        $this->_userName = $user->getUsername();
        $this->_userDbUri = $this->_privateConfig->saving->baseQueryDbUri . 'user-' . $this->_userName . '/';
    }

    public function editorAction()
    {
        $this->view->placeholder('main.window.title')->set('SPARQL Query Editor');
        $this->view->formActionUrl = $this->_config->urlBase . 'queries/editor';
        $this->view->formMethod = 'post';
        $this->view->formName = 'sparqlquery';
        $this->view->query = $this->_request->getParam('query', '');

        $this->view->urlBase = $this->_config->urlBase;
        $this->view->writeable = $this->_owApp->selectedModel->isEditable();

        // set URIs
        if ($this->_owApp->selectedModel) {
            $this->view->modelUri = $this->_owApp->selectedModel->getModelIri();
        }
        if ($this->_owApp->selectedResource) {
            $this->view->resourceUri = $this->_owApp->selectedResource;
        }

        // build toolbar
        $toolbar = $this->_owApp->toolbar;
        $toolbar->appendButton(
            OntoWiki_Toolbar :: SUBMIT,
            array(
                'name' => 'Submit Query'
            )
        )->appendButton(
            OntoWiki_Toolbar :: RESET,
            array(
                'name' => 'Reset Form'
            )
        );
        $this->view->placeholder('main.window.toolbar')->set($toolbar);

        // build menu
        if ($this->_owApp->selectedModel) {
            $insertMenu = new OntoWiki_Menu();
            $insertMenu->setEntry('Current Model URI', 'javascript:insertModelUri()');

            if ($this->_owApp->selectedResource) {
                $insertMenu->setEntry('Current Resource URI', 'javascript:insertResourceUri()');
            }
        }

        $helpMenu = new OntoWiki_Menu();
        $helpMenu
            ->setEntry(
                'Specification',
                'http://www.w3.org/TR/rdf-sparql-query/'
            )
            ->setEntry(
                'Reference Card',
                'http://www.dajobe.org/2005/04-sparql/'
            )
            ->setEntry(
                'Tutorial',
                'http://platon.escet.urjc.es/%7Eaxel/sparqltutorial/'
            );

        $menu = new OntoWiki_Menu();
        if (isset($insertMenu)) {
            $menu->setEntry('Insert', $insertMenu);
        }
        $menu->setEntry('Help', $helpMenu);
        $this->view->placeholder('main.window.menu')->set($menu->toArray());

        $prefixes = $this->_owApp->selectedModel->getNamespacePrefixes();

        if (isset($this->_request->queryUri)) {
            $query = $this->getQuery($this->_request->queryUri);
        }
        if (empty($query)) {
            $query = $this->getParam('query');
        }

        $format = $this->_request->getParam('result_format', 'plain');

        if (!empty($query)) {
            //handle a posted query
            $store = $this->_erfurt->getStore();

            foreach ($prefixes as $prefix => $namespace) {
                $query = 'PREFIX ' . $prefix . ': <' . $namespace . '>' . PHP_EOL . $query;
            }
            if ($format == 'list') {
                $url = new OntoWiki_Url(array('controller' => 'list'), array());
                $query = str_replace("\r\n", ' ', $query);
                $url .= '?init=1&instancesconfig=' .
                    urlencode(
                        json_encode(
                            array(
                                'filter' => array(
                                    array(
                                        'mode' => 'query',
                                        'action' => 'add',
                                        'query' => $query
                                    )
                                )
                            )
                        )
                    );

                //redirect
                $this->_redirect($url);
                return;
            }

            if (stristr($query, 'select') && !stristr($query, 'limit')) {
                $query .= PHP_EOL . 'LIMIT 20';
            }

            $this->view->query = $query;

            $result = null;
            try {
                $start = microtime(true);

                //this switch is for the target selection module
                if ($this->_request->getParam('target') == 'all') {
                    //query all models
                    $result = $store->sparqlQuery(
                        $query,
                        array(
                            'result_format' => $format
                        )
                    );
                } else {
                    //query selected model
                    $result = $this->_owApp->selectedModel->sparqlQuery(
                        $query,
                        array(
                            'result_format' => $format
                        )
                    );
                }

                //this is for the "output to file option
                if (
                        ($format == 'json' || $format == 'xml') &&
                        $this->_request->getParam('result_outputfile') == 'true'
                ) {
                    $this->_helper->viewRenderer->setNoRender();
                    $this->_helper->layout()->disableLayout();
                    $response = $this->getResponse();

                    switch ($format) {
                        case 'xml':
                            $contentType = 'application/rdf+xml';
                            $filename = 'query-result.xml';
                            break;
                        case 'json':
                            $contentType = 'application/json';
                            $filename = 'query-result.json';
                            break;
                    }

                    $response->setHeader('Content-Type', $contentType, true);
                    $response->setHeader('Content-Disposition', ('filename="' . $filename . '"'));

                    $response->setBody($result);
                    return;
                }

                $this->view->time = ((microtime(true) - $start) * 1000);

                $header = array();
                if (is_array($result) && isset($result[0]) && is_array($result[0])) {
                    $header = array_keys($result[0]);
                } else if (is_bool($result)) {
                    $result = $result ? 'yes' : 'no';
                } else if (is_int($result)) {
                    $result = (string) $result;
                } else if (is_string($result)) {
                    // json
                    $result = $result;
                } else {
                    $result = 'no result';
                }
            } catch (Exception $e) {
                $this->view->error = $e->getMessage();
                $header = '';
                $result = '';
                $this->view->time = 0;
            }

            $this->view->data = $result;
            $this->view->header = $header;
        }

        //load js for sparql syntax highlighting
        $this->view->headScript()->prependFile($this->_componentUrlBase . 'resources/codemirror/js/codemirror.js');
        $this->view->headScript()->prependScript(
            'var editor; $(document).ready(
                function(){
                    editor = CodeMirror.fromTextArea(
                        "inputfield", 
                        {
                          parserfile: "parsesparql.js",
                          path: "' . $this->_componentUrlBase . 'resources/codemirror/js/",
                          stylesheet: "' . $this->_componentUrlBase . 'resources/codemirror/css/sparqlcolors.css",
                        }
                    );
                    $("a.submit").unbind("click");
                    $("a.submit").click(
                        function(){ 
                            $("#inputfield").text(editor.getCode()); 
                            $(this).parents("form:first").submit(); 
                        }
                    );
                }
            );'
        );

        //fill in some placeholders
        $this->view->prefixes = $prefixes;
        $this->view->placeholder('sparql.result.format')->set($format);
        $this->view->placeholder('sparql.query.target')->set($this->_request->getParam('target', 'this'));

        //load modules
        $this->addModuleContext('main.window.queryeditor');
        if ($this->_privateConfig->general->enabled->saving) {
            $this->addModuleContext('main.window.savequery');
        }
    }

    /**
     * Action that will show existing saved Queries
     */
    public function listqueryAction()
    {
        // set the active tab navigation
        OntoWiki::getInstance()->getNavigation()->setActive('listquery');

        $store = $this->_owApp->erfurt->getStore();
        $graph = $this->_owApp->selectedModel;

        //Loading data for list of saved queries
        $listHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('List');
        $listName = 'queries';
        if ($listHelper->listExists($listName)) {
            $list = $listHelper->getList($listName);
            $list->setStore($store);
            $listHelper->addList($listName, $list, $this->view, 'list_queries_main');
        } else {
            $list = new OntoWiki_Model_Instances($store, $graph, array());

            $list->addTypeFilter($this->_privateConfig->saving->ClassUri, 'searchqueries');

            $list->addShownProperty($this->_privateConfig->saving->ModelUri, 'modelUri', false, null, true);
            $list->addShownProperty($this->_privateConfig->saving->JsonUri, 'json', false, null, true);
            $list->addShownProperty($this->_privateConfig->saving->NameUri, 'name', false, null, false);
            $list->addShownProperty($this->_privateConfig->saving->QueryUri, 'query', false, null, true);
            $list->addShownProperty($this->_privateConfig->saving->GeneratorUri, 'generator', false, null, true);
            $list->addShownProperty($this->_privateConfig->saving->NumViewsUri, 'numViews', false, null, false);
            $list->addShownProperty($this->_privateConfig->saving->CreatorUri, 'creator', false, null, false);

            $listHelper->addListPermanently($listName, $list, $this->view, 'list_queries_main');
        }
        $this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('Saved Queries'));
    }

    /**
     * webservice to save a query
     */
    public function savequeryAction()
    {
        $response = $this->getResponse();
        $response->setHeader('Content-Type', 'text/plain');

        $store = $this->_erfurt->getStore();
        $storeGraph = $this->_owApp->selectedModel;
        $graphUri = (string) $this->_owApp->selectedModel;

        $res = "json or desc missing";
        // checking for post data to save queries
        $params = $this->_request->getParams();
        if (isset($params['json']) && isset($params['json'])) {
            if ($this->_request->getParam('share') == "true") {
                // store in the model itself - everybody can see it
                $storeGraph = $this->_owApp->selectedModel;
            } else {
                //private db - should be configured so only the user can see it
                $storeGraph = $this->getUserQueryDB();
            }

            // checking whether any queries exist yet in this store
            $existingQueriesQuery = Erfurt_Sparql_SimpleQuery :: initWithString(
                'SELECT *
                 WHERE {
                    ?query <'.EF_RDF_TYPE.'> <'.
                OntoWiki_Utils::expandNamespace(
                    $this->_privateConfig->saving->ClassUri
                ).
                '> .
                 }'
            );
            $existingQueries = $storeGraph->sparqlQuery($existingQueriesQuery);
            if (empty($existingQueries)) {
                //this is the first query
                $this->insertInitials($storeGraph);
            }
            $hash = md5($this->_request->getParam('json') . $this->_request->getParam('query'));
            $name = (string) $storeGraph . '#Query-' . $hash;

            // checking whether a query with same content (Where-Part) already exists (check by md5 sum)
            $existingDataQuery = Erfurt_Sparql_SimpleQuery :: initWithString(
                'SELECT *
                 WHERE {
                     <'.$name.'> a <'.OntoWiki_Utils::expandNamespace($this->_privateConfig->saving->ClassUri) . '>
                 }'
            );

            $existingData = $storeGraph->sparqlQuery($existingDataQuery);

            if (empty($existingData)) {
                //such a query is not saved yet - lets save it

                $storeGraph->addStatement(
                    $name,
                    EF_RDF_TYPE,
                    array(
                        'value' => $this->_privateConfig->saving->ClassUri,
                        'type' => 'uri'
                    ),
                    false
                );
                $storeGraph->addStatement(
                    $name,
                    $this->_privateConfig->saving->ModelUri,
                    array(
                        'value' => (string) $this->_owApp->selectedModel,
                        'type' => 'uri'
                    ),
                    false
                );
                $storeGraph->addStatement(
                    $name,
                    $this->_privateConfig->saving->NameUri,
                    array(
                        'value' => $this->_request->getParam('name'),
                        'type' => 'literal'
                    ),
                    false
                );
                $storeGraph->addStatement(
                    $name,
                    $this->_privateConfig->saving->DateUri,
                    array(
                        'value' => (string) date('c'),
                        'type' => 'literal',
                        'datatype' => OntoWiki_Utils :: expandNamespace('xsd:dateTime')
                    ),
                    false
                );
                $storeGraph->addStatement(
                    $name,
                    OntoWiki_Utils::expandNamespace($this->_privateConfig->saving->NumViewsUri),
                    array(
                        'value' => '1',
                        'type' => 'literal',
                        'datatype' => OntoWiki_Utils :: expandNamespace('xsd:integer')
                    ),
                    false
                );
                if ($this->_request->getParam('generator') == "gqb" || $this->_request->getParam('generator') == "qb") {
                    $storeGraph->addStatement(
                        $name,
                        $this->_privateConfig->saving->JsonUri,
                        array(
                            'value' => $this->_request->getParam('json'),
                            'type' => 'literal'
                        ),
                        false
                    );
                }
                $storeGraph->addStatement(
                    $name, $this->_privateConfig->saving->QueryUri,
                    array(
                        'value' => $this->_request->getParam('query'),
                        'type' => 'literal'
                    ),
                    false
                );
                $storeGraph->addStatement(
                    $name,
                    $this->_privateConfig->saving->GeneratorUri,
                    array(
                        'value' => $this->_request->getParam('generator'),
                        'type' => 'literal'
                    ),
                    false
                );
                if ($this->_request->getParam('generator') == "gqb") {
                    $storeGraph->addStatement(
                        $name,
                        $this->_privateConfig->saving->IdUri,
                        array(
                            'value' => $this->_request->getParam('id'),
                            'type' => 'literal'
                        ),
                        false
                    );
                    $storeGraph->addStatement(
                        $name,
                        $this->_privateConfig->saving->SelClassUri,
                        array(
                            'value' => $this->_request->getParam('type'),
                            'type' => 'uri'
                        ),
                        false
                    );
                    $storeGraph->addStatement(
                        $name,
                        $this->_privateConfig->saving->SelClassLabelUri,
                        array(
                            'value' => $this->_request->getParam('typelabel'),
                            'type' => 'literal'
                        ),
                        false
                    );
                } else {
                    //TODO gqb uses id - qb not... needed?
                    $storeGraph->addStatement(
                        $name,
                        $this->_privateConfig->saving->IdUri,
                        array(
                            'value' => $hash,
                            'type' => 'literal'
                        ),
                        false
                    );
                }
                $user = $this->_erfurt->getAuth()->getIdentity();
                $userUri = $user->getUri();

                $storeGraph->addStatement(
                    $name,
                    $this->_privateConfig->saving->CreatorUri,
                    array(
                        'value' => $userUri,
                        'type' => 'uri'
                    ),
                    false
                );

                $res = 'All OK';
            } else {
                $res = 'Save failed. (Query with same pattern exists)';
            }
        }
        $response->setBody($res);
    }

    /**
     * delete a saved query by uri
     */
    public function deleteAction()
    {
        $store = OntoWiki::getInstance()->erfurt->getStore();

        $response = $this->getResponse();
        $response->setHeader('Content-Type', 'text/plain');

        // fetch param
        $uriString = $this->_request->getParam('uri', '');

        if (get_magic_quotes_gpc()) {
            $uriString = stripslashes($uriString);
        }

        $res = 'All OK';
        if (!empty($uriString)) {
            try {
                //find the db
                $userdb = $this->getUserQueryDB(false);

                //TODO pass the "where it is" as param
                //delete from private
                if ($userdb != null)
                    $userdb->deleteMatchingStatements($uriString, null, null);
                //delete from shared
                $this->_owApp->selectedModel->deleteMatchingStatements($uriString, null, null);
            } catch (Exception $e) {
                $res = $e;
            }
        } else {
            $res = 'need to pass uri';
        }

        $response->setBody($res);
    }

    private function getUserQueryDB($create = true)
    {
        $userdb = $this->findDB($this->_userDbUri);
        if ($userdb != null || !$create) {
            return $userdb;
        } else {
            return $this->createUserQueryDB();
        }
    }

    /**
     * find db by name
     *
     * @param string name of searched db
     * @return Model-Object
     */
    private function findDB($name)
    {
        $_store = $this->_erfurt->getStore();

        //get all Models (including hidden Models)
        $allModels = $_store->getAvailableModels(true);

        foreach ($allModels as $graphUri => $true) {
            if ($graphUri === $name) {
                //get the model (without authentification)
                return $_store->getModel($graphUri, false);
            }
        }

        return null;
    }

    /**
     * set up db for query saving
     * @param <type> $db
     */
    private function insertInitials($db)
    {
        //add the "Pattern" Class
        $object['value'] = EF_RDFS_CLASS;
        $object['type'] = 'uri';
        $db->addStatement($this->_privateConfig->saving->ClassUri, EF_RDF_TYPE, $object);

        //domain for the class
        $object['value'] = $db->getModelIri();
        $object['type'] = 'uri';
        $db->addStatement(
            $this->_privateConfig->saving->ClassUri,
            'http://www.w3.org/2000/01/rdf-schema#domain',
            $object
        );

        //label for the class
        $object['value'] = 'Query';
        $object['type'] = 'literal';
        $db->addStatement(
            $this->_privateConfig->saving->ClassUri,
            'http://www.w3.org/2000/01/rdf-schema#label',
            $object
        );
    }

    private function createUserQueryDB()
    {
        $proposedDBname = $this->_userDbUri;

        $store = $this->_erfurt->getStore();
        $newModel = $store->getNewModel($proposedDBname);

        $object = array();

        // add english label for this db
        $object['object_type'] = Erfurt_Store :: TYPE_LITERAL;
        $object['value'] = 'GQB Query DB of ' . $this->_userName;
        $newModel->addStatement($proposedDBname, EF_RDFS_LABEL, $object);

        // german label
        $object['literal_language'] = 'de';
        $object['value'] = 'GQB Anfrage-DB von ' . $this->_userName;
        $newModel->addStatement($proposedDBname, EF_RDFS_LABEL, $object);

        // add description of this db
        $object['value'] = 'Hier werden Sparql-Queries gespeichert, die User ' .
                $this->_userName . ' erstellt und gespeichert hat.';
        $newModel->addStatement($proposedDBname, EF_RDFS_COMMENT, $object);

        //domain of this db (needed?)
        $object['value'] = $this->_privateConfig->saving->baseQueryDbUri;
        $object['object_type'] = Erfurt_Store :: TYPE_IRI;
        $newModel->addStatement($proposedDBname, EF_RDFS_DOMAIN, $object);

        //add owner/maker of this db
        $object['value'] = $this->_userUri;
        $newModel->addStatement(
            $proposedDBname,
            $this->_privateConfig->saving->CreatorUri,
            $object
        );

        $this->insertInitials($newModel);

        return $newModel;
    }

    protected function getQuery($uri)
    {
        $queryString = 'SELECT *
             WHERE {
               <'.$uri.'> <'.$this->_privateConfig->saving->QueryUri.'> ?query
             }';
        $queryData = $this->_erfurt->getStore()->sparqlQuery($queryString);
        if (isset($queryData[0])) {
            //increment view counter
            $countQuery = 'SELECT *
             WHERE {
              <'.$uri.'> <'.$this->_privateConfig->saving->NumViewsUri.'> ?count
             }';
            $countRes = $this->_erfurt->getStore()->sparqlQuery($countQuery);
            if (isset($countRes[0])) {
                $i = $countRes[0]['count'];
                $graphUri =  (string) $this->_owApp->selectedModel;
                $this->_erfurt->getStore()->deleteMatchingStatements(
                    $graphUri,
                    $uri,
                    $this->_privateConfig->saving->NumViewsUri,
                    null
                );
                $this->_erfurt->getStore()->addStatement(
                    $graphUri,
                    $uri,
                    $this->_privateConfig->saving->NumViewsUri,
                    array('value'=>$i+1, 'type'=>'literal')
                );
            }
            return $queryData[0]['query'];
        }
    }

    /**
     * Action to construct Queries, view their results, save them ...
     */
    public function manageAction()
    {
        //TODO: to enable saving add a <input id="editortype" type="hidden" value="querybuilder" /> to the template

        // set the active tab navigation
        OntoWiki::getInstance()->getNavigation()->setActive('savedqueries', true);

        // creates toolbar and adds two buttons
        $toolbar = $this->_owApp->toolbar;
        $toolbar->appendButton(
            OntoWiki_Toolbar::RESET,
            array(
                'name' => 'Reset queries',
                'id' => 'reset'
            )
        );
        $toolbar->appendButton(
            OntoWiki_Toolbar::SUBMIT,
            array(
                'name' => 'Update Results',
                'class' => '',
                'id' => 'updateresults'
            )
        );
        $this->view->placeholder('main.window.toolbar')->set($toolbar);

        // creates menu structure
        $viewMenu = new OntoWiki_Menu();
        $viewMenu->setEntry(
            'Toggle Debug Code',
            'javascript:toggleDebugCode()'
        );
        $viewMenu->setEntry(
            'Toggle SPARQL Code',
            'javascript:toggleSparqlCode()'
        );

        $menu = new OntoWiki_Menu();
        $menu->setEntry('View', $viewMenu);
        $this->view->placeholder('main.window.menu')->set($menu->toArray());

        $includeBase = $this->_componentUrlBase;
        $this->view->headScript()->appendFile(
            $includeBase . 'resources/savepartial.js'
        );
        $this->view->headScript()->appendFile(
            $includeBase . 'resources/jquery.autocomplete.min.js'
        );
        $this->view->headScript()->appendFile(
            $includeBase . 'resources/jquery.json-1.3.min.js'
        );
        $this->view->headScript()->appendFile(
            $includeBase . 'resources/json2.js'
        );
        $this->view->headScript()->appendFile(
            $includeBase . 'resources/graphicalquerybuilder.js'
        );

        // adding stylesheet for autocompletion-boxes
        $this->view->headLink()->appendStylesheet(
            $includeBase . 'css/jquery.autocomplete.css'
        );

        $this->view->placeholder('main.window.title')->set(
            $this->_owApp->translate->_('Query Builder')
        );

        $tPattern = $this->_request->getParam('patterns');

        if (empty($tPattern)) {
            $default['qb_triple_0'] = array(
                's' => '?subject',
                'p' => '?predicate',
                'o' => '?object',
                'otype' => 'uri'
            );
        } else {
            $default = json_decode($tPattern, true);
        }

        $this->view->tPattern = json_encode($default);
        $this->view->headScript()->appendScript(
            $this->_jscript(json_encode($default))
        );
    }

    public function autocompleteAction()
    {
        $debug = defined('_OWDEBUG');
        if ($debug) {
            echo 'debug mode is: ' . (($debug) ? 'on' : 'off') . "\n";
        }
        /*
          if (false && $debug) {

          echo "<xmp>";
          //$q = "";
          $q = "Je";
          $limit = 50;
          $json = "{\"qb_triple_0\": {\"s\": \"?actors\", \"p\": \"?p\", \"o\": \"Je\", \"search\": \"o\"}}";
          }else{
         */
        $params = $this->_request->getParams();
        $json = ($params['json']);
        $q = ($params['q']);
        $limit = ($params['limit']);

        $config = self::_object2array($this->_privateConfig);
        require_once('lib/AjaxAutocompletion.php');
        $u = new AjaxAutocompletion($q, $json, $limit, $config, $debug);

        //$u = new AjaxAutocompletion("",$json, $limit,$config, $debug);
        echo $u->getAutocompletionList();
        if ($debug) {
            echo "\n Debug code:\n  " . htmlentities($u->getQuery());
        }
    }

    public function updatetableAction()
    {
        // setting up some needed variables
        $config = $this->_privateConfig->toArray();

        // stripping automatic escaped chars
        $params = array();
        foreach ($this->_request->getParams() as $key => $param) {
            if (get_magic_quotes_gpc()) {
                $params[$key] = stripslashes($param);
            } else {
                $params[$key] = $param;
            }
        }

        $now = microtime(true);
        require_once('lib/AjaxUpdateTable.php');
        $ajaxUpdate = new AjaxUpdateTable($params['json'], $params['limit'], $config, true);
        $data = $ajaxUpdate->getResultAsArray();
        $queryString = (string) $ajaxUpdate->getSPARQLQuery();
        $time = round((microtime(true) - $now) * 1000) . ' msec needed';

        // disabling layout and template as we make no use of these
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();

        // now rendering in updatetable.phtml
        $this->view->prefixHandler = $ajaxUpdate->getPrefixHandler();
        $this->view->translate = $this->_owApp->translate;
        $this->view->data = $data;
        $this->view->cssid = 'qbresulttable';
        echo $this->view->render(
            'partials/resultset.phtml',
            array(
                'data' => $data,
                'caption' => 'Results',
                'cssid' => 'qbresulttable'
            )
        );
    }

    public function updatesparqlAction()
    {
        $config = self::_object2array($this->_privateConfig);

        $params = $this->_request->getParams();
        $json = ($params['json']);
        $limit = ($params['limit']);
        require_once('lib/AjaxUpdateTable.php');
        $u = new AjaxUpdateTable($json, $limit, $config, true);
        echo $u->getSPARQLQuery();
    }

    private function _jscript($patterns)
    {
        $jscript = 'qb_js_tripleinfo = ' . $patterns . ";\n";

        $patterns = json_decode($patterns, true);
        $arr = array();
        foreach ($patterns as $key => $value) {
            $arr[] = str_replace('qb_triple_', '', $key);
        }
        sort($arr, SORT_NUMERIC);
        $max = $arr[count($arr) - 1] + 1;

        //TODO maybe not needed
        $jscript .='resetLink = \'';
        $first = true;
        $vars = '';
        foreach ($this->_request->getParams() as $key => $value) {
            if ($first) {
                $first = false;
                $vars = '?' . $key . '=' . $value;
            } else {
                $vars = '&' . $key . '=' . $value;
            }
        }
        $jscript .= $vars . "';\n";

        $jscript .= 'maxID = ' . $max . ";\n" .
                "function getNextID (){
    	  			retval = maxID;
    	  			maxID++;
    	  			return retval;
    	  			};\n";

        return $jscript;
    }

    private static function _jshelp($config)
    {
        $conf = self::_object2array($config);
        $ret = '';
        foreach ($conf as $key => $value) {
            $ret .= "config['$key'] = '$value';\n";
        }
        return $ret;
    }

    private static function _object2array($object)
    {
        if (is_object($object)) {
            foreach ($object as $key => $value) {
                $array[$key] = $value;
            }
        } else {
            $array = $object;
        }
        return $array;
    }

    /**
     * display gqb
     */
    public function displayAction()
    {
        $includeBase = $this->_componentUrlBase;
        $this->view->componentUrlBase = $this->_componentUrlBase;
        if ($this->_owApp->selectedModel != null) {
            //stylesheets
            $this->view->headLink()->appendStylesheet($includeBase . 'resources/graphicalquerybuilder.css');
            $this->view->headLink()->appendStylesheet($includeBase . 'resources/jquery.treeview.css');

            // Stylesheet for printing
            $this->view->headLink()->appendStylesheet(
                $includeBase . 'resources/graphicalquerybuilder_print.css',
                'print'
            );

            //include utils/libs
            $this->view->headScript()->appendFile($includeBase . 'resources/jquery.dump.js');
            $this->view->headScript()->appendFile($includeBase . 'resources/jquery.treeview.js');
            $this->view->headScript()->appendFile($includeBase . 'resources/jquery.scrollTo-1.4.2-min.js');
            $this->view->headScript()->appendFile($includeBase . 'resources/sparql.js');
            $this->view->headScript()->appendFile($includeBase . 'resources/raphael.js');
            $this->view->headScript()->appendFile($includeBase . 'resources/raphael.gqb.js');

            //generate some js
            $lang = $this->_owApp->config->languages->locale;

            $modelUri = $this->_owApp->selectedModel->getModelIri();
            $this->view->headScript()->appendScript(
                'var GQB = {};' .
                "GQB.selectedModelUri = \"" . $modelUri . "\";\n" .
                "GQB.userDbUri = \"" . $this->_userDbUri . "\";\n" .
                "GQB.patternClassName = \"" . $this->saveQueryClassUri . "\";\n" .
                "GQB.patternJson = \"" . $this->saveQueryJsonUri . "\";\n" .
                "GQB.patternName = \"" . $this->saveQueryNameUri . "\";\n" .
                "GQB.patternDesc = \"" . $this->saveQueryDescriptionUri . "\";\n" .
                "GQB.patternType = \"" . $this->saveQuerySelClassUri . "\";\n" .
                "GQB.patternTypeLabel = \"" . $this->saveQuerySelClassLabelUri . "\";\n" .
                "GQB.patternDate = \"" . $this->saveQueryDateUri . "\";\n" .
                "GQB.patternQuery = \"" . $this->saveQueryQueryUri . "\";\n" .
                "GQB.currLang = \"" . $lang . "\"; \n" .
                "GQB.supportedLangs = [ \"en\", \"de\" ];"
            );

            $open = $this->_request->getParam('open', '');
            if ($open == 'true') {
                $this->view->headScript()->appendScript(
                    "GQB.toload = \"" .
                    $this->_request->getParam('queryuri', '') . "\";"
                );
            }
            //include the js code
            $this->view->headScript()->appendFile($includeBase . 'resources/graphicalquerybuilder.translations.js');
            $this->view->headScript()->appendFile($includeBase . 'resources/graphicalquerybuilder.controller.js');

            $this->view->headScript()->appendFile($includeBase . 'resources/graphicalquerybuilder.model.js');
            $this->view->headScript()->appendFile(
                $includeBase . 'resources/graphicalquerybuilder.model.restrictions.js'
            );
            $this->view->headScript()->appendFile(
                $includeBase . 'resources/graphicalquerybuilder.model.GQBClass.js'
            );
            $this->view->headScript()->appendFile(
                $includeBase . 'resources/graphicalquerybuilder.model.GQBQueryPattern.js'
            );
            $this->view->headScript()->appendFile(
                $includeBase . 'resources/graphicalquerybuilder.model.GQBModel.js'
            );

            $this->view->headScript()->appendFile($includeBase . 'resources/graphicalquerybuilder.view.GQBView.js');
            $this->view->headScript()->appendFile(
                $includeBase . 'resources/graphicalquerybuilder.view.GQBView.restrictions.js'
            );
            $this->view->headScript()->appendFile(
                $includeBase . 'resources/graphicalquerybuilder.view.GQBView.init.js'
            );
            $this->view->headScript()->appendFile(
                $includeBase . 'resources/graphicalquerybuilder.view.GQBViewPattern.js'
            );

            //start
            $this->view->headScript()->appendFile($includeBase . 'resources/graphicalquerybuilder.js');
        } else {
            //no model selected error
            throw new OntoWiki_Exception('no model selected - maybe your session timed out');
        }
        $this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('Graphical Query Builder'));
    }

    public function displayprototypeAction()
    {
        $includeBase = $this->_componentUrlBase;
        $this->view->headLink()->appendStylesheet(
            $includeBase . 'resources/prototype/graphicalquerybuilder.prototype.css'
        );

        $this->view->headScript()->appendFile($includeBase . 'resources/jquery.dump.js');
        $this->view->headScript()->appendFile($includeBase . 'resources/sparql.js');

        $this->view->headScript()->appendFile(
            $includeBase . 'resources/prototype/graphicalquerybuilder.classdefs.restrictions.prototype.js'
        );
        $this->view->headScript()->appendFile(
            $includeBase . 'resources/prototype/graphicalquerybuilder.classdefs.prototype.js'
        );
        $this->view->headScript()->appendFile(
            $includeBase . 'resources/prototype/graphicalquerybuilder.prototype.js'
        );

        $this->view->placeholder('main.window.title')->set(
            $this->_owApp->translate->_('Graphical Query Builder (Prototype)')
        );
    }

    // usage: "queries/getquerysize/?query=<QUERY>"
    //       for any valid SPARQL-Query <QUERY>
    // returns the number of elements returned by the query
    public function getquerysizeAction()
    {
        $store = OntoWiki::getInstance()->erfurt->getStore();
        $response = $this->getResponse();
        $response->setHeader('Content-Type', 'text/plain');
        $count = 0;

        // fetch params
        $queryString = $this->_request->getParam('query', '');
        if (get_magic_quotes_gpc()) {
            $queryString = stripslashes($queryString);
        }
        $defaultGraph = $this->_request->getParam('default-graph-uri', null);
        $namedGraph = $this->_request->getParam('named-graph-uri', null);

        if (!empty($queryString)) {
            require_once 'Erfurt/Sparql/SimpleQuery.php';
            $query = Erfurt_Sparql_SimpleQuery :: initWithString($queryString);

            // overwrite query-specidfied dataset with protocoll-specified dataset
            if (null !== $defaultGraph) {
                $query->setFrom((array) $defaultGraph);
            }
            if (null !== $namedGraph) {
                $query->setFromNamed((array) $namedGraph);
            }

            // check graph availability
            require_once 'Erfurt/App.php';
            $ac = Erfurt_App :: getInstance()->getAc();
            foreach (array_merge($query->getFrom(), $query->getFromNamed()) as $graphUri) {
                if (!$ac->isModelAllowed('view', $graphUri)) {
                    $count = -3;
                    $response->setBody($count);
                    return;
                }
            }

            try {
                $result = $store->sparqlQuery(
                    $query,
                    array(
                        'result_format' => 'json'
                    )
                );
                $resarray = json_decode($result);
                $count = count($resarray->{'bindings'});
            } catch (Exception $e) {
                $count = -2;
                $response->setBody($count);
                return;
            }
        } else {
            $count = -1;
        }

        $response->setBody($count);
    }

    public function getresulttableAction()
    {
        // stripping automatic escaped chars
        $params = array();
        foreach ($this->_request->getParams() as $key => $param) {
            if (get_magic_quotes_gpc()) {
                $params[$key] = stripslashes($param);
            } else {
                $params[$key] = $param;
            }
        }

        $now = microtime(true);

        $query = $this->_request->getParam('query', '');
        $queryObj = Erfurt_Sparql_SimpleQuery :: initWithString($query);
        $store = OntoWiki::getInstance()->erfurt->getStore();
        $data = $store->sparqlQuery($queryObj);

        $time = round((microtime(true) - $now) * 1000) . ' msec needed';

        // disabling layout and template as we make no use of these
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();

        // now rendering
        $this->view->data = $data;
        $this->view->cssid = 'gqbresulttable';
        echo $this->view->render(
            'partials/resultset.phtml',
            array(
                'data' => $data,
                'caption' => 'Results',
                'cssid' => 'gqbresulttable'
            )
        );
    }

}
