<?php
require_once 'OntoWiki/Controller/Component.php';
require_once 'OntoWiki/Toolbar.php';
require_once 'OntoWiki/Navigation.php';

/**
 * Controller for OntoWiki Filter Module
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_querybuilding
 * @author     Sebastian Hellmann <hellmann@informatik.uni-leipzig.de>
 * @author     Sebastian Dietzold <dietzold@informatik.uni-leipzig.de>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $$
 */
class QuerybuildingController extends OntoWiki_Controller_Component {
    protected $userUri;
    protected $userName;
    protected $userDbUri;


    /**
     * init() Method to init() normal and add tabbed Navigation
     */
    public function init() {
        parent :: init();

        // setup the navigation
        OntoWiki_Navigation :: reset();
        $tab_exist = false;
        if($this->_privateConfig->general->enabled->saving) {
            OntoWiki_Navigation :: register('listquery', array (
                    'controller' => "querybuilding",
                    'action' => "listquery",
                    'name' => "Saved Queries",
                    'position' => 0,
                    'active' => true
            ));
            $this->view->headScript()->appendFile($this->_config->staticUrlBase .
                    'extensions/components/querybuilding/resources/savepartial.js');
            $tab_exist = true;
        }
        if($this->_privateConfig->general->enabled->editor) {
            OntoWiki_Navigation :: register('queryeditor', array (
                    'controller' => "querybuilding",
                    'action' => "editor",
                    'name' => "Query Editor",
                    'position' => 1,
                    'active' => false
            ));
            $tab_exist = true;
        }
        if(class_exists("QuerybuilderHelper")) {
            OntoWiki_Navigation :: register('querybuilder', array (
                    'controller' => "querybuilder",
                    'action' => "manage",
                    'name' => "Query Builder ",
                    'position' => 2,
                    'active' => false
            ));
            $tab_exist = true;
        }
        if(class_exists("GraphicalquerybuilderHelper")) {
            OntoWiki_Navigation :: register('graphicalquerybuilder', array (
                    'controller' => "graphicalquerybuilder",
                    'action' => "display",
                    'name' => "Graphical Query Builder",
                    'position' => 3,
                    'active' => false
            ));
            $tab_exist = true;
        }
        if(!$tab_exist) {
            OntoWiki_Navigation :: disableNavigation();
        }

        $user = $this->_erfurt->getAuth()->getIdentity();
        $this->userUri = $user->getUri();
        $this->userName = $user->getUsername();
        $this->userDbUri = $this->_privateConfig->saving->baseQueryDbUri . 'user-' . $this->userName . '/';


    }

    public function editorAction() {
        $this->view->placeholder('main.window.title')->set('SPARQL Query Editor');
        $this->view->formActionUrl = $this->_config->urlBase . 'querybuilding/editor';
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
        $toolbar->appendButton(OntoWiki_Toolbar :: SUBMIT, array (
                'name' => 'Submit Query'
                ))->appendButton(OntoWiki_Toolbar :: RESET, array (
                'name' => 'Reset Form'
        ));
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
        $helpMenu->setEntry('Specification', 'http://www.w3.org/TR/rdf-sparql-query/')->setEntry('Reference Card', 'http://www.dajobe.org/2005/04-sparql/')->setEntry('Tutorial', 'http://platon.escet.urjc.es/%7Eaxel/sparqltutorial/');

        $menu = new OntoWiki_Menu();
        if (isset ($insertMenu)) {
            $menu->setEntry('Insert', $insertMenu);
        }
        $menu->setEntry('Help', $helpMenu);
        $this->view->placeholder('main.window.menu')->set($menu->toArray());

        $prefixes = $this->_owApp->selectedModel->getNamespacePrefixes();

        if( isset ($this->_request->queryUri)){
            $query = $this->getQuery($this->_request->queryUri);
        }
        if( empty ($query)){
            $query = $this->getParam('query');
        }

        $format = $this->_request->getParam('result_format', 'plain');

        if ($this->_request->isPost() || isset ($this->_request->immediate)) {
            $post = $this->_request->getPost();
            $store = $this->_erfurt->getStore();

            if (trim($query) != '') {
                if($format == 'list'){
                    $url = new OntoWiki_Url(array('controller'=>'list'),array());
                    $query = str_replace("\r\n", " ", $query);
                    $url .= '?init=1&instancesconfig=' . urlencode(json_encode(array('filter'=>array( array ('mode' => 'query', 'action' => 'add' , 'query' => $query)))));

                    //redirect
                    header('Location: ' . $url);
                    exit;
                }

                if (stristr($query, 'select') && !stristr($query, 'limit')) {
                    $query .= PHP_EOL . 'LIMIT 20';
                }

                $this->view->query = $query;

                foreach ($prefixes as $prefix => $namespace) {
                    $query = 'PREFIX ' . $prefix . ': <' . $namespace . '>' . PHP_EOL . $query;
                }

                $result = null;
                try {
                    $start = microtime(true);

                    //this switch is for the target selection module
                    if($this->_request->getParam('target') == 'all') {
                        //query all models
                        $result = $store->sparqlQuery($query, array (
                                'result_format' => $format
                        ));
                    } else {
                        //query selected model
                        $result = $this->_owApp->selectedModel->sparqlQuery($query, array (
                                'result_format' => $format
                        ));
                    }

                    //this is for the "output to file option
                    if(($format == 'json' || $format == 'xml') && $this->_request->getParam('result_outputfile') == 'true') {
                        $this->_helper->viewRenderer->setNoRender();
                        $this->_helper->layout()->disableLayout();
                        $response = $this->getResponse();

                        switch($format) {
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
                        $response->setHeader('Content-Disposition', ('filename="'.$filename.'"'));

                        $response->setBody($result)
                                ->sendResponse();
                        exit;
                    }

                    $this->view->time = ((microtime(true) - $start) * 1000);

                    $header = array ();
                    if (is_array($result) && isset ($result[0]) && is_array($result[0])) {
                        $header = array_keys($result[0]);
                    } else
                    if (is_bool($result)) {
                        $result = $result ? 'yes' : 'no';
                    } else
                    if (is_int($result)) {
                        $result = (string)$result;
                    } else
                    if (is_string($result)) {
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
        }
        $this->view->headScript()->prependFile($this->_componentUrlBase . 'resources/codemirror/js/codemirror.js');
        $this->view->headScript()->prependScript('var editor; $(document).ready(function(){
            editor = CodeMirror.fromTextArea("inputfield", {
              parserfile: "parsesparql.js",
              path: "'.$this->_componentUrlBase . 'resources/codemirror/js/",
              stylesheet: "'.$this->_componentUrlBase . 'resources/codemirror/css/sparqlcolors.css",
            });
            $("a.submit").unbind("click");
            $("a.submit").click(function(){ $("#inputfield").text(editor.getCode()); $(this).parents("form:first").submit(); });
            });');
        $this->view->prefixes = $prefixes;
        $this->view->placeholder('sparql.result.format')->set($format);
        $this->view->placeholder('sparql.query.target')->set($this->_request->getParam('target', 'this'));
    }


    /**
	 * Action that will load existing Queries for listing
     */
    public function listqueryAction() {
        // set the active tab navigation
        OntoWiki_Navigation :: setActive('listquery', true);

        $store = $this->_owApp->erfurt->getStore();
        $graph = $this->_owApp->selectedModel;

        //Loading data for list of saved queries
        $listHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('List');
        $listName = "queries";
        if($listHelper->listExists($listName)){
            $list = $listHelper->getList($listName);
            $listHelper->addList($listName, $list, $this->view, "listqueryaction.phtml");
        } else {
            $list = new OntoWiki_Model_Instances($store, $graph, array());

            $list->addTypeFilter($this->_privateConfig->saving->ClassUri, 'searchqueries');

            $list->addShownProperty($this->_privateConfig->saving->ModelUri, "modelUri", false, null, true);
            $list->addShownProperty($this->_privateConfig->saving->JsonUri, "json", false, null, true);
            $list->addShownProperty($this->_privateConfig->saving->DescriptionUri, "description", false, null, false);
            $list->addShownProperty($this->_privateConfig->saving->QueryUri, "query", false, null, true);
            $list->addShownProperty($this->_privateConfig->saving->GeneratorUri, "generator", false, null, true);
            $list->addShownProperty($this->_privateConfig->saving->NumViewsUri, "numViews", false, null, false);
            $list->addShownProperty($this->_privateConfig->saving->CreatorUri, "creator", false, null, false);

            $listHelper->addListPermanently($listName, $list, $this->view, "listqueryaction.phtml");
        }
        $this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('Saved Queries'));
        //print_r(OntoWiki::getInstance()->_properties);
    }

    public function savequeryAction() {
        $response = $this->getResponse();
        $response->setHeader('Content-Type', 'text/plain');

        $store = $this->_erfurt->getStore();
        $storeGraph = $this->_owApp->selectedModel;
        $graphUri = (string) $this->_owApp->selectedModel;

        $res = "json or desc missing";
        // checking for post data to save queries
        $params = $this->_request->getParams();
        if (isset($params["json"]) && isset($params["json"])) {
            if ($this->_request->getParam('share') == "true") {
                // store in the model itself - everybody can see it
                $storeGraph = $this->_owApp->selectedModel;
            } else {
                //private db - should be configured so only the user can see it
                $storeGraph = $this->getUserQueryDB();
            }

            // checking whether any queries exist yet in this store
            $existingQueriesQuery = Erfurt_Sparql_SimpleQuery :: initWithString('SELECT *
                                 WHERE {
                                 ?query <' . EF_RDF_TYPE . '> <' . OntoWiki_Utils :: expandNamespace($this->_privateConfig->saving->$this->_privateConfig->saving->ClassUri) . '> .
                                 }');
            $existingQueries = $storeGraph->sparqlQuery($existingQueriesQuery);
            if (empty ($existingQueries)) {
                //this is the first query
                $this->insertInitials($storeGraph);
            }
            $md5 = md5($this->_request->getParam('json') . $this->_request->getParam('query'));
            $name = (string) $storeGraph . '#Query-' . $md5;

            // checking whether a query with same content (Where-Part) already exists (check by md5 sum)
            $existingDataQuery = Erfurt_Sparql_SimpleQuery :: initWithString('SELECT *
                     WHERE {
                     <'.$name.'> a <' . OntoWiki_Utils :: expandNamespace($this->_privateConfig->saving->ClassUri) . '>
                     }');

            $existingData = $storeGraph->sparqlQuery($existingDataQuery);

            if (empty ($existingData)) {
                //such a query is not saved yet - lets save it

                $storeGraph->addStatement($name, EF_RDF_TYPE, array (
                        'value' => $this->_privateConfig->saving->ClassUri,
                        'type' => 'uri'
                        ), false);
                $storeGraph->addStatement($name,
                        $this->_privateConfig->saving->ModelUri, array (
                        'value' => (string) $this->_owApp->selectedModel, 'type' => 'uri'
                        ), false);
                $storeGraph->addStatement($name, $this->_privateConfig->saving->NameUri, array (
                        'value' => $this->_request->getParam('name'),
                        'type' => 'literal'
                        ), false);
                $storeGraph->addStatement($name, $this->_privateConfig->saving->DateUri, array (
                        'value' => (string) date('c'),
                        'type' => 'literal',
                        'datatype' => OntoWiki_Utils :: expandNamespace('xsd:dateTime')
                        ), false);
                $storeGraph->addStatement($name, OntoWiki_Utils :: expandNamespace($this->_privateConfig->saving->NumViewsUri), array (
                        'value' => '1',
                        'type' => 'literal',
                        'datatype' => OntoWiki_Utils :: expandNamespace('xsd:integer')
                        ), false);
                if($this->_request->getParam('generator') == "gqb" || $this->_request->getParam('generator') == "qb") {
                    $storeGraph->addStatement($name, $this->_privateConfig->saving->JsonUri, array (
                            'value' => $this->_request->getParam('json'),
                            'type' => 'literal'
                            ), false);
                }
                $storeGraph->addStatement($name, $this->_privateConfig->saving->QueryUri, array (
                        'value' => $this->_request->getParam('query'),
                        'type' => 'literal'
                        ), false);
                $storeGraph->addStatement($name, $this->_privateConfig->saving->GeneratorUri, array (
                        'value' => $this->_request->getParam('generator'),
                        'type' => 'literal'
                        ), false);
                if($this->_request->getParam('generator') == "gqb") {
                    $storeGraph->addStatement($name, $this->_privateConfig->saving->IdUri, array (
                            'value' => $this->_request->getParam('id'),
                            'type' => 'literal'
                            ), false);
                    $storeGraph->addStatement($name, $this->_privateConfig->saving->SelClassUri, array (
                            'value' => $this->_request->getParam('type'),
                            'type' => 'uri'
                            ), false);
                    $storeGraph->addStatement($name, $this->_privateConfig->saving->SelClassLabelUri, array (
                            'value' => $this->_request->getParam('typelabel'),
                            'type' => 'literal'
                            ), false);
                } else {
                    //TODO gqb uses id - qb not... needed?
                    $storeGraph->addStatement($name, $this->_privateConfig->saving->IdUri, array (
                            'value' => $md5,
                            'type' => 'literal'
                            ), false);
                }
                $user = $this->_erfurt->getAuth()->getIdentity();
                $userUri = $user->getUri();

                $storeGraph->addStatement($name, $this->_privateConfig->saving->CreatorUri, array (
                        'value' => $userUri,
                        'type' => 'uri'
                        ), false);

                $res = 'All OK';
            } else {
                $res = 'Save failed. (Query with same pattern exists)';
            }
        }
        $response->setBody($res);
        $response->sendResponse();
        exit;
    }

    public function deleteAction() {
        $store = OntoWiki::getInstance()->erfurt->getStore();

        $response = $this->getResponse();
        $response->setHeader('Content-Type', 'text/plain');

        // fetch param
        $uriString = $this->_request->getParam('uri', '');

        if (get_magic_quotes_gpc()) {
            $uriString = stripslashes($uriString);
        }

        $res = 'All OK';
        if (!empty ($uriString)) {
            try {
                //find the db
                $userdb = $this->getUserQueryDB(false);

                //TODO pass the "where it is" as param
                //delete from private
                if($userdb != null)
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
        $response->sendResponse();
        exit;
    }

    private function getUserQueryDB($create = true) {
        $userdb = $this->findDB($this->userDbUri);
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
    private function findDB($name) {
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

    private function insertInitials($db) {
        //add the "Pattern" Class
        $object['value'] = EF_RDFS_CLASS;
        $object['type'] = 'uri';
        $db->addStatement($this->_privateConfig->saving->ClassUri, EF_RDF_TYPE, $object);

        //domain for the class
        $object['value'] = $db->getModelIri();
        $object['type'] = 'uri';
        $db->addStatement($this->_privateConfig->saving->ClassUri, 'http://www.w3.org/2000/01/rdf-schema#domain', $object);

        //label for the class
        $object['value'] = "Query";
        $object['type'] = 'literal';
        $db->addStatement($this->_privateConfig->saving->ClassUri, 'http://www.w3.org/2000/01/rdf-schema#label', $object);
    }

    private function createUserQueryDB() {
        $proposedDBname = $this->userDbUri;

        $store = $this->_erfurt->getStore();
        $newModel = $store->getNewModel($proposedDBname);

        $options = array ();
        $object = array ();

        // add english label for this db
        $options['object_type'] = Erfurt_Store :: TYPE_LITERAL;
        $object['value'] = 'GQB Query DB of ' . $this->userName;
        $newModel->addStatement($proposedDBname, EF_RDFS_LABEL, $object);

        // german label
        $options['literal_language'] = 'de';
        $object['value'] = 'GQB Anfrage-DB von ' . $this->userName;
        $newModel->addStatement($proposedDBname, EF_RDFS_LABEL, $object);

        // add description of this db
        $object['value'] = 'Hier werden Sparql-Queries gespeichert, die User ' . $this->userName . ' erstellt und gespeichert hat.';
        $newModel->addStatement($proposedDBname, EF_RDFS_COMMENT, $object);

        //domain of this db (needed?)
        $object['value'] = $this->_privateConfig->saving->baseQueryDbUri;
        $options['object_type'] = Erfurt_Store :: TYPE_IRI;
        $newModel->addStatement($proposedDBname, EF_RDFS_DOMAIN, $object);

        //add owner/maker of this db
        $object['value'] = $this->userUri;
        $newModel->addStatement($proposedDBname, $this->_privateConfig->saving->CreatorUri, $object);

        $this->insertInitials($newModel);

        return $newModel;
    }

    protected function getQuery($uri){
        $queryString = Erfurt_Sparql_SimpleQuery :: initWithString('SELECT *
             WHERE {
             <'.$uri.'> <' . $this->_privateConfig->saving->QueryUri . '> ?query
             }');
            $queryData =$this->_erfurt->getStore()->sparqlQuery($queryString);
         if(isset($queryData[0])){
             return $queryData[0]["query"];
         }
    }
}