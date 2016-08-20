<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011-2016, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * Controller for OntoWiki Filter Module
 *
 * @category   OntoWiki
 * @package    Extensions_Queries
 * @copyright  Copyright (c) 2013, {@link http://aksw.org AKSW}
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
        parent::init();

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
        if ($this->_owApp->selectedModel === null) {
            $this->_owApp->appendMessage(
                new OntoWiki_Message($this->view->_('No model selected.'), OntoWiki_Message::ERROR)
            );
            $this->view->errorFlag = true;
            return;
        }

        $this->view->headLink()->appendStylesheet(
            $this->_owApp->extensionManager->getComponentUrl('queries').'resources/querieseditor.css'
        );
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
            OntoWiki_Toolbar::SUBMIT,
            array(
                'name' => 'Submit Query'
            )
        )->appendButton(
            OntoWiki_Toolbar::RESET,
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
                $prefixString = 'PREFIX ' . $prefix . ': <' . $namespace . '>';
                // only add prefix if it's not there yet
                if (strpos($query, $prefixString) === false) {
                    $query = $prefixString . PHP_EOL . $query;
                }
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
                if (($format == 'json' || $format == 'xml' || $format == 'csv')
                    && ($this->_request->getParam('result_outputfile') == 'true')
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
                        case 'csv':
                            $contentType = 'text/csv';
                            $filename = 'query-result.csv';
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
                    $result = (string)$result;
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
        $this->view->headLink()->appendStylesheet(
            $this->_componentUrlBase . 'resources/codemirror/lib/codemirror.css'
        );
        $this->view->headScript()->appendFile(
            $this->_componentUrlBase . 'resources/codemirror/lib/codemirror.js'
        );
        $this->view->headScript()->appendFile(
            $this->_componentUrlBase . 'resources/codemirror/addon/edit/matchbrackets.js'
        );
        $this->view->headScript()->appendFile(
            $this->_componentUrlBase . 'resources/codemirror/mode/sparql/sparql.js'
        );

        $this->view->headStyle()->appendStyle(
            '.CodeMirror { border: 1px solid black; }'
        );

        $this->view->headScript()->appendScript(
            'var editor;
            $(document).ready(
                function(){
                    editor = CodeMirror.fromTextArea(
                        document.getElementById("inputfield"),
                        {
                            mode: "application/x-sparql-query",
                            tabMode: "indent",
                            matchBrackets: true,
                        }
                    );
                    $(".CodeMirror").resizable({
                        resize: function() {
                            editor.setSize($(this).width(), $(this).height());
                        }
                    });
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
        if ($this->_owApp->selectedModel === null) {
            $this->_owApp->appendMessage(
                new OntoWiki_Message($this->view->_('No model selected.'), OntoWiki_Message::ERROR)
            );
            $this->view->errorFlag = true;
            return;
        }

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
        $this->_helper->layout()->disableLayout();

        $response = $this->getResponse();
        $response->setHeader('Content-Type', 'text/plain');

        $store = $this->_erfurt->getStore();
        $storeGraph = $this->_owApp->selectedModel;
        $graphUri = (string)$this->_owApp->selectedModel;
        $res = "json or desc missing";
        // checking for post data to save queries
        $params = $this->_request->getParams();
        if (isset($params['json'])) {
            if ($this->_request->getParam('share') == 'true') {
                //The User wants to story the Query in the DB he is querying -> check if he can edit it
                if (!$this->_owApp->selectedModel->isEditable()) {
                    $res = "The Query cannot be shared, because the Model is not editable.";
                    $response->setBody($res);
                    return;
                }
                // store in the model itself - everybody can see it
                $storeGraph = $this->_owApp->selectedModel;
            } else {
                //the User wants to Store the Query in his private DB -> check rights/if it already exists
                if (!Erfurt_App::getInstance()->isActionAllowed('ModelManagement')) {
                    if ($this->findDB($this->_userDbUri) == null) {
                        $res = 'You dont have the Permission to create a DB for your Queries,'
                        . ' ask your Admin about it.';
                        $response->setBody($res);
                        return;
                    } else {
                        $storeGraph = $this->getUserQueryDB();
                    }
                } else {
                    $storeGraph = $this->getUserQueryDB();
                }
            }

            // checking whether any queries exist yet in this store
            $existingQueriesQuery = Erfurt_Sparql_SimpleQuery::initWithString(
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
            $name = (string)$storeGraph . '#Query-' . $hash;

            // checking whether a query with same content (Where-Part) already exists (check by md5 sum)
            $existingDataQuery = Erfurt_Sparql_SimpleQuery::initWithString(
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
                        'value' => (string)$this->_owApp->selectedModel,
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
                        'value' => (string)date('c'),
                        'type' => 'literal',
                        'datatype' => OntoWiki_Utils::expandNamespace('xsd:dateTime')
                    ),
                    false
                );
                $storeGraph->addStatement(
                    $name,
                    OntoWiki_Utils::expandNamespace($this->_privateConfig->saving->NumViewsUri),
                    array(
                        'value' => '1',
                        'type' => 'literal',
                        'datatype' => OntoWiki_Utils::expandNamespace('xsd:integer')
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
        } else {
            $res = 'You dont have the permissions to save your Queries non-shared.';
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
                if ($userdb != null) {
                    $userdb->deleteMatchingStatements($uriString, null, null);
                }
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
        $newModel = $store->getNewModel($proposedDBname, null, null, true);

        $object = array();

        // add english label for this db
        $object['type'] = 'literal';
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
        $object['type'] = 'uri';
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
                $graphUri = (string)$this->_owApp->selectedModel;
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
                    array(
                         'value' => $i + 1,
                         'type' => 'literal'
                    )
                );
            }
            return $queryData[0]['query'];
        }
    }
}
