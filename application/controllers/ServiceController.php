<?php

/**
 * OntoWiki service controller.
 * 
 * @package    application
 * @subpackage mvc
 * @copyright  Copyright (c) 2010, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class ServiceController extends Zend_Controller_Action
{
    /** @var OntoWiki */
    protected $_owApp = null;
    
    /** @var Zend_Config */
    protected $_config = null;
    
    /**
     * Attempts an authentication to the underlying Erfurt framework via 
     * HTTP GET/POST parameters.
     */
    public function authAction()
    {
        if (!$this->_config->service->allowGetAuth) {
            // disallow get
            if (!$this->_request->isPost()) {
                $this->_response->setRawHeader('HTTP/1.0 405 Method Not Allowed');
                $this->_response->setRawHeader('Allow: POST');
                exit();
            }
        }
    
        // fetch params
        if (isset($this->_request->logout)) {
            $logout = $this->_request->logout == 'true' ? true : false;
        } elseif (isset($this->_request->u)) {
            $username = $this->_request->u;
            $password = $this->_request->getParam('p', '');
        } else {
            $this->_response->setRawHeader('HTTP/1.0 400 Bad Request');
            // $this->_response->setRawHeader('');
            exit();
        }
      
        if ($logout) {
            // logout
            Erfurt_Auth::getInstance()->clearIdentity();
            session_destroy();
            $this->_response->setRawHeader('HTTP/1.0 200 OK');
            exit();
        } else {
            // authenticate
            $result = $owApp->erfurt->authenticate($username, $password);
        }
      
        // return HTTP result
        if ($result->isValid()) {
            // return success (200)
            $this->_response->setRawHeader('HTTP/1.0 200 OK');
            exit();
        } else {
            // return fail (401)
            $this->_response->setRawHeader('HTTP/1.0 401 Unauthorized');
            exit();
        }
    }
    
    /**
     * Entity search
     */
    public function entitiesAction()
    {
        $type  = (string)$this->_request->getParam('type', 's');
        $match = (string)$this->_request->getParam('match');
        
        $type = $type[0]; // use only first letter
        
        if ($this->_owApp->selectedModel && strlen($match) > 2) {
            $namespaces = $this->_owApp->selectedModel->getNamespaces();
            
            $namespacesFlipped = array_flip($namespaces);
            $nsFilter = array();
            foreach ($namespacesFlipped as $prefix => $uri) {
                if (stripos($prefix, $match) === 0) {
                    $nsFilter[] = 'FILTER (regex(str(?' . $type . '), "' . $uri . '"))';
                }
            }
            
            $store = $this->_owApp->selectedModel->getStore();
            $query = Erfurt_Sparql_SimpleQuery::initWithString(
                'SELECT DISTINCT ?' . $type . '
                FROM <' . $this->_owApp->selectedModel->getModelIri() . '>
                WHERE {
                    ?s ?p ?o.
                    ' . implode(PHP_EOL, $nsFilter) . '
                }'
            );
        }
    }

    public function hierarchyAction()
    {
        $options = array();
        if (isset($this->_request->entry)) {
            $options['entry'] = $this->_request->entry;
        }
        
        $model = new OntoWiki_Model_Hierarchy(Erfurt_App::getInstance()->getStore(), 
                                              $this->_owApp->selectedModel, 
                                              $options);
        
        $this->view->open = true;
        $this->view->classes = $model->getHierarchy();
        $this->_response->setBody($this->view->render('partials/hierarchy_list.phtml'));
        // $this->_response->setBody(json_encode($model->getHierarchy()));
    }
    
    /**
     * Constructor
     */
    public function init()
    {
        // init controller variables
        $this->_owApp   = OntoWiki::getInstance();
        $this->_config  = $this->_owApp->config;
        $this->_session = $this->_owApp->session;
        
        // prepare Ajax context
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('view', 'html')
                    ->addActionContext('form', 'html')
                    ->addActionContext('process', 'json')
                    ->initContext();
    }
    
    /**
     * Menu Action to generate JSON serializations of OntoWiki_Menu for context-, module-, component-menus
     */
    public function menuAction()
    {
        $module   = $this->_request->getParam('module');
        $resource = $this->_request->getParam('resource');

        $translate = $this->_owApp->translate;

        // create empty menu first
        $menuRegistry = OntoWiki_Menu_Registry::getInstance();
        $menu = $menuRegistry->getMenu(EF_RDFS_RESOURCE);

        if (!empty($module)) {
            $moduleRegistry = OntoWiki_Module_Registry::getInstance();
            $menu = $moduleRegistry->getModule($module)->getContextMenu();
        }
        
        if (!empty($resource)) {
            $models = array_keys($this->_owApp->erfurt->getStore()->getAvailableModels(true));
            $isModel = in_array($resource, $models);

            $menu->prependEntry(
                'Go to Resource (external)',
                (string)$resource
            );

            if ($this->_owApp->erfurt->getAc()->isModelAllowed('edit', $this->_owApp->selectedModel) ) {
                // Delete resource option
                $url = new OntoWiki_Url(
                    array('controller' => 'resource', 'action' => 'delete'),
                    array()
                );
                if ($isModel) {
                    $url->setParam('m',$resource,false);
                }
                $url->setParam('r',$resource,true);                
                $menu->prependEntry( 'Delete Resource', (string) $url );

                // edit resource option
                $menu->prependEntry('Edit Resource', 'javascript:editResourceFromURI(\''.(string) $resource.'\')');
            }
            
            // add resource menu entries
            $url = new OntoWiki_Url(
                array( 'action' => 'view'),
                array()
            );
            if ($isModel) {
                $url->setParam('m',$resource,false);
            }
            $url->setParam('r',$resource,true);

            $menu->prependEntry(
                'View Resource',
                (string)$url
            );
            
            if ($isModel) {    
                // add a seperator
                $menu->prependEntry(OntoWiki_Menu::SEPARATOR);
                
                // can user delete models?
                if ( $this->_owApp->erfurt->getAc()->isModelAllowed('edit', $resource) &&
                     $this->_owApp->erfurt->getAc()->isActionAllowed('ModelManagement') 
                ) {

                    $url = new OntoWiki_Url(
                        array('controller' => 'model', 'action' => 'delete'),
                        array()
                    );
                    $url->setParam('m',$resource,false);

                    $menu->prependEntry(
                        'Delete Knowledge Base',
                        (string)$url
                    );
                }
                
                
                // add entries for supported export formats
                foreach (array_reverse(Erfurt_Syntax_RdfSerializer::getSupportedFormats()) as $key => $format) {

                    $url = new OntoWiki_Url(
                        array('controller' => 'model', 'action' => 'export'),
                        array()
                    );
                    $url->setParam('m',$resource,false);
                    $url->setParam('f',$key);

                    $menu->prependEntry(
                        'Export Knowledge Base as ' . $format,
                        (string)$url
                    );
                }
                
                
                // check if model could be edited (prefixes and data)
                if ($this->_owApp->erfurt->getAc()->isModelAllowed('edit', $resource)) {

                    $url = new OntoWiki_Url(
                        array('controller' => 'model', 'action' => 'add'),
                        array()
                    );
                    $url->setParam('m',$resource,false);
                    $menu->prependEntry(
                        'Add Data to Knowledge Base',
                        (string)$url
                    );

                    $url = new OntoWiki_Url(
                        array('controller' => 'model', 'action' => 'config'),
                        array()
                    );
                    $url->setParam('m',$resource,false);
                    $menu->prependEntry(
                        'Configure Knowledge Base',
                        (string)$url
                    );
                }
                

                // Select Knowledge Base
                $url = new OntoWiki_Url(
                    array('controller' => 'model', 'action' => 'select'),
                    array()
                );
                $url->setParam('m',$resource,false);
                $menu->prependEntry(
                    'Select Knowledge Base',
                    (string)$url
                );
            } else {
                $query = Erfurt_Sparql_SimpleQuery::initWithString(
                    'SELECT * 
                     FROM <' . (string)$this->_owApp->selectedModel . '> 
                     WHERE {
                        <' . $resource . '> a ?type  .  
                     }'
                );
                $results[] = $this->_owApp->erfurt->getStore()->sparqlQuery($query);

                $query = Erfurt_Sparql_SimpleQuery::initWithString(
                    'SELECT * 
                     FROM <' . (string)$this->_owApp->selectedModel . '>
                     WHERE {
                        ?inst a <' . $resource . '> .    
                     } LIMIT 2'
                );

                if ( sizeof($this->_owApp->erfurt->getStore()->sparqlQuery($query)) > 0 ) {
                    $hasInstances = true;
                } else {
                    $hasInstances = false;
                }

                $typeArray = array();
                foreach ($results[0] as $row) {
                    $typeArray[] = $row['type'];
                }

                if (in_array(EF_RDFS_CLASS, $typeArray) ||
                    in_array(EF_OWL_CLASS, $typeArray)  ||
                    $hasInstances
                ) {
                    
                    // add a seperator
                    $menu->prependEntry(OntoWiki_Menu::SEPARATOR);

                    $url = new OntoWiki_Url(
                        array('action' => 'list'),
                        array()
                    );
                    $url->setParam('class',$resource,false);
                    $url->setParam('init',"true",true);

                    // add class menu entries
                    if ($this->_owApp->erfurt->getAc()->isModelAllowed('edit', $this->_owApp->selectedModel) ) {
                        $menu->prependEntry(
                            'Create Instance',
                            "javascript:createInstanceFromClassURI('$resource');"
                        );
                    }
                    $menu->prependEntry(
                        'List Instances',
                        (string)$url
                    );
                     // ->prependEntry('Create Instance', $this->_config->urlBase . 'index/create/?r=')
                     // ->prependEntry('Create Subclass', $this->_config->urlBase . 'index/create/?r=');
                }
            }        
        }
        
        // Fire a event;
        $event = new Erfurt_Event('onCreateMenu');
        $event->menu = $menu;
        $event->resource = $resource;
        
        if (isset($isModel)) {
            $event->isModel = $isModel;
        }
        
        
        $event->model = $this->_owApp->selectedModel;
        $event->trigger();

        echo $menu->toJson();
    }
    
    public function preDispatch()
    {
        // disable auto-rendering
        $this->_helper->viewRenderer->setNoRender();
        
        // disable layout for Ajax requests
        $this->_helper->layout()->disableLayout();
    }
    
    public function sessionAction()
    {
        if (!isset($this->_request->name)) {
            throw new OntoWiki_Exception("Missing parameter 'name'.");
            exit;
        }
               
        if (isset($this->_request->namespace)) {
            $namespace = $this->_request->namespace;
        } else {
            $namespace = _OWSESSION;
        }
        
        $session = new Zend_Session_Namespace($namespace);
        $name    = $this->_request->name;
        $method = 'set'; // default
        if (isset($this->_request->method)) {
            $method = $this->_request->method;
        }

        if (isset($this->_request->value)) {
            $value = $this->_request->value;
        } else if($method!='unsetArray' && $method!='unsetArrayKey' && !($method=='unset' && !is_array($session->$name))) {
            throw new OntoWiki_Exception('Missing parameter "value".');
            exit;
        }

        if (isset($this->_request->value) && isset($this->_request->valueIsSerialized) && $this->_request->valueIsSerialized == "true") {
            $value = json_decode(stripslashes($value), true);
        }
        
        if (isset($this->_request->key)) {
            $key = $this->_request->key;
        } else if ($method == 'setArrayValue' || $method == 'unsetArrayKey') {
            throw new OntoWiki_Exception('Missing parameter "key".');
            exit;
        } 

        switch ($method) {
            case 'set':
                $session->$name = $value;
                break;
             case 'setArrayValue':
                if(!is_array($session->$name))$session->$name = array();
                $array = $session->$name;
                $array[$key] = $value;
                $session->$name = $array; //strange (because the __get and __set interceptors)
                break;
            case 'push':
                if (!is_array($session->$name)) {
                    $session->$name = array();
                }
                array_push($session->$name, $value);
                break;
            case 'merge':
                if (!is_array($session->$name)) {
                    $session->$name = array();
                }
                $session->$name = array_merge($session->$name, $value);
                break;
            case 'unset':
                // unset a value by inverting the array
                // and unsetting the specified key
                if (is_array($session->$name)) {
                    $valuesAsKeys = array_flip($session->$name);
                    unset($valuesAsKeys[$value]);
                    $session->$name = array_flip($valuesAsKeys);
                } else {
                    //unset a non-array
                    unset($session->$name);
                }
                break;
            case 'unsetArrayKey':
                //done this way because of interceptor-methods...
                $new = array();
                if(is_array($session->$name)){
                   foreach($session->$name as $comparekey => $comparevalue){
                        if($comparekey != $key){
                            $new[] = $comparevalue;
                        }
                    }
                }
                $session->$name = $new;
                break;
            case 'unsetArray':
                // unset the array
                // (the above unsets only values in arrays)
                unset($session->$name);
                break;
        }
        
        $msg = 'sessionStore: ' 
             . $name 
             . ' = ' 
             . print_r($session->$name, true);
        
        $this->_owApp->logger->debug($msg);
    }
    
    /**
     * OntoWiki Sparql Endpoint
     *
     * Implements the SPARQL protocol according to {@link http://www.w3.org/TR/rdf-sparql-protocol/}.
     */
    public function sparqlAction()
    {
        // service controller needs no view renderer
        $this->_helper->viewRenderer->setNoRender();
        // disable layout for Ajax requests
        $this->_helper->layout()->disableLayout();
        
        $store    = OntoWiki::getInstance()->erfurt->getStore();
        $response = $this->getResponse();

        // fetch params
        // TODO: support maxOccurs:unbound
        $queryString  = $this->_request->getParam('query', '');
        if (get_magic_quotes_gpc()) {
            $queryString = stripslashes($queryString);
        }
        $defaultGraph = $this->_request->getParam('default-graph-uri', null);
        $namedGraph   = $this->_request->getParam('named-graph-uri', null);
          
        if (!empty($queryString)) {
            $query = Erfurt_Sparql_SimpleQuery::initWithString($queryString);

            // overwrite query-specidfied dataset with protocoll-specified dataset
            if (null !== $defaultGraph) {
                $query->setFrom((array)$defaultGraph);
            }
            if (null !== $namedGraph) {
                $query->setFromNamed((array)$namedGraph);
            }
 
            // check graph availability
            $ac = Erfurt_App::getInstance()->getAc();
            foreach (array_merge($query->getFrom(), $query->getFromNamed()) as $graphUri) {
                if (!$ac->isModelAllowed('view', $graphUri)) {
                    if (Erfurt_App::getInstance()->getAuth()->getIdentity()->isAnonymousUser()) {
                        // In this case we allow the requesting party to authorize...
                        $response->setRawHeader('HTTP/1.1 401 Unauthorized');
                        $response->setHeader('WWW-Authenticate', 'Basic realm="OntoWiki"');
                        $response->sendResponse();
                        exit;
                        
                    } else {
                        $response->setRawHeader('HTTP/1.1 500 Internal Server Error')
                                 ->setBody('QueryRequestRefused')
                                 ->sendResponse();
                        exit;
                    }
                }
            }
            
            $typeMapping = array(
                'application/sparql-results+xml'  => 'xml', 
                'application/json'                => 'json', 
                'application/sparql-results+json' => 'json'
            );
            
            try {
                $type = OntoWiki_Utils::matchMimetypeFromRequest($this->_request, array_keys($typeMapping));
            } catch (Exeption $e) {
                // 
            }
            
            if (empty($type) && isset($this->_request->callback)) {
                // JSONp
                $type = 'application/sparql-results+json';
            } else if (empty($type)) {
                // dafault: XML
                $type = 'application/sparql-results+xml';
            }

            try {
                // get result for mimetype
                $result = $store->sparqlQuery($query, array('result_format' => $typeMapping[$type]));
            } catch (Exception $e) {
                $response->setRawHeader('HTTP/1.1 400 Bad Request')
                         ->setBody('MalformedQuery: ' . $e->getMessage())
                         ->sendResponse();
                exit;
            }
            
            if (/* $typeMapping[$type] == 'json' && */isset($this->_request->callback)) {
                // return jsonp
                $response->setHeader('Content-Type', 'application/javascript');
                $padding = $this->_request->getParam('callback', '');
                $response->setBody($padding . '(' . $result . ')');
            } else {
                // set header
                $response->setHeader('Content-Type', $type);
                // return normally
                $response->setBody($result);
            }
            
            $response->sendResponse();
            exit;
        }
    }
    
    /**
     * OntoWiki Update Endpoint
     *
     * Only data inserts and deletes are implemented at the moment (e.g. no graph patterns).
     * @todo LOAD <> INTO <>, CLEAR GRAPH <>, CREATE[SILENT] GRAPH <>, DROP[ SILENT] GRAPH <>
     */
    public function updateAction()
    {        
        // service controller needs no view renderer
        $this->_helper->viewRenderer->setNoRender();
        // disable layout for Ajax requests
        $this->_helper->layout()->disableLayout();
    
        $store        = OntoWiki::getInstance()->erfurt->getStore();
        $response     = $this->getResponse();
        $defaultGraph = $this->_request->getParam('default-graph-uri', null);
        $namedGraph   = $this->_request->getParam('named-graph-uri', null);
        $insertGraph  = null;
        $deleteGraph  = null;
        $insertModel  = null;
        $deleteModel  = null;
        
        if (isset($this->_request->query)) {
            // we have a query, enter SPARQL/Update mode
            $query = $this->_request->getParam('query', '');
            OntoWiki::getInstance()->logger->info('SPARQL/Update query: ' . $query);

            $matches = array();
            // insert
            preg_match('/INSERT\s+DATA(\s+INTO\s*<(.+)>)?\s*{\s*([^}]*)/i', $query, $matches);
            $insertGraph   = (isset($matches[2]) && ($matches[2] !== '')) ? $matches[2] : null;
            $insertTriples = isset($matches[3]) ? $matches[3] : '';

            if ((null === $insertGraph) && ($insertTriples !== '')) {
                if (null !== $defaultGraph) {
                    $insertGraph = $defaultGraph;
                }
                if (null !== $namedGraph) {
                    $insertGraph = $namedGraph;
                }
            }

            OntoWiki::getInstance()->logger->info('SPARQL/Update insertGraph: ' . $insertGraph);
            OntoWiki::getInstance()->logger->info('SPARQL/Update insertTriples: ' . $insertTriples);
            
            // delete
            preg_match('/DELETE\s+DATA(\s+FROM\s*<(.+)>)?\s*{\s*([^}]*)/i', $query, $matches);
            $deleteGraph   = (isset($matches[2]) && ($matches[2] !== '')) ? $matches[2] : null;
            $deleteTriples = isset($matches[3]) ? $matches[3] : '';
            
            if ((null === $deleteGraph) && ($deleteTriples !== '')) {
                if (null !== $defaultGraph) {
                    $deleteGraph = $defaultGraph;
                }
                if (null !== $namedGraph) {
                    $deleteGraph = $namedGraph;
                }
            }
            
            // TODO: normalize literals
            
            $parser = Erfurt_Syntax_RdfParser::rdfParserWithFormat('nt');
            $insert = $parser->parse($insertTriples, Erfurt_Syntax_RdfParser::LOCATOR_DATASTRING);
            $parser->reset();
            $delete = $parser->parse($deleteTriples, Erfurt_Syntax_RdfParser::LOCATOR_DATASTRING);
            
            if (null !== $insertGraph) {
                try {
                    $insertModel = $insertGraph ? $store->getModel($insertGraph) : $store->getModel($namedGraph);       
                } catch (Erfurt_Store_Exception $e) {
                    // TODO: error
                    if (defined('_OWDEBUG')) {
                        OntoWiki::getInstance()->logger->info('Could not instantiate models.');
                    }
                    exit;
                }
            }
            
            if (null !== $deleteGraph) {
                try {
                    $deleteModel = $deleteGraph ? $store->getModel($deleteGraph) : $store->getModel($namedGraph);
                } catch (Erfurt_Store_Exception $e) {
                    // TODO: error
                    if (defined('_OWDEBUG')) {
                        OntoWiki::getInstance()->logger->info('Could not instantiate models.');
                    }
                    exit;
                }
            }
        } else {
            // no query, inserts and delete triples by JSON via param
            $insert = json_decode($this->_request->getParam('insert', '{}'), true);
            $delete = json_decode($this->_request->getParam('delete', '{}'), true);
            
            if ($this->_request->has('delete_hashed')) {
                $hashedObjectStatements = $this->_findStatementsForObjectsWithHashes(
                    $namedGraph, 
                    json_decode($this->_request->getParam('delete_hashed'), true));
                $delete = array_merge_recursive($delete, $hashedObjectStatements);
            }
            
            try {
                $namedModel  = $store->getModel($namedGraph);
                $insertModel = $namedModel;
                $deleteModel = $namedModel;
            } catch (Erfurt_Store_Exception $e) {
                // TODO: error
                if (defined('_OWDEBUG')) {
                    OntoWiki::getInstance()->logger->info('Could not instantiate models.');
                }
                exit;
            }
        }
        
        if (empty($insert) or empty($delete)) {
            // TODO: error
        }
        
        $flag = false;
        
        /**
         * @trigger onUpdateServiceAction is triggered when Service-Controller Update Action is executed.
         * Event contains following attributes:
         * deleteModel  :   model to delete statments from
         * deleteData   :   statements payload being deleted
         * insertModel  :   model to add statements to
         * insertDara   :   statements payload being added
         */
        $event = new Erfurt_Event('onUpdateServiceAction');
        $event->deleteModel = $deleteModel;
        $event->insertModel = $insertModel;
        $event->deleteData  = $delete;
        $event->insertData  = $insert;
        $event->trigger();
        
        // writeback
        $delete = $event->deleteData;
        $insert = $event->insertData;
        $changes = isset($event->changes) ? $event->changes : null;

        // delete
        if ($deleteModel && $deleteModel->isEditable()) {
            try {
                $count = $deleteModel->deleteMultipleStatements((array)$delete);
            } catch (Erfurt_Store_Exception $e) {
                if (defined('_OWDEBUG')) {
                    OntoWiki::getInstance()->logger->info(
                        'Could not delete statements from graph: ' . $e->getMessage() . PHP_EOL . 
                        'Statements: ' . print_r($delete, true)
                    );
                }
            }
            
            $flag = true;
            if (defined('_OWDEBUG')) {
                OntoWiki::getInstance()->logger->info(
                    sprintf('Deleted %i statements from graph <%s>', $count, $deleteModel->getModelUri())
                );
            }
        }
        
        // insert
        if ($insertModel && $insertModel->isEditable()) {
            $count = $insertModel->addMultipleStatements((array)$insert);
            $flag = true;
            if (defined('_OWDEBUG')) {
                OntoWiki::getInstance()->logger->info(
                    sprintf('Inserted %i statements into graph <%s>', $count, $insertModel->getModelUri())
                );
            }
        }
        
        // nothing done?
        if (!$flag) {
            // When no user is given (Anoymous) give the requesting party a chance to authenticate.
            if (Erfurt_App::getInstance()->getAuth()->getIdentity()->isAnonymousUser()) {
                // In this case we allow the requesting party to authorize
                $response->setRawHeader('HTTP/1.1 401 Unauthorized');
                $response->setHeader('WWW-Authenticate', 'Basic realm="OntoWiki"');
                $response->sendResponse();
                exit;
            }
        }
        
        if ($changes) {
            /**
             * @see {http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html#sec10.2.2}
             */
            $response->setHttpResponseCode(201);
            $response->setHeader('Location', $changes['changed']);
            $response->setHeader('Content-Type', 'application/json');
            $response->setBody(json_encode($changes));
        }
    }
    
    /**
     * Renders a template and responds with the output.
     *
     * All GET and POST parameters are populated into the view object
     * and therefore available in the view script. You have to know
     * which parameters the script uses and objects obviously cannot
     * be passed via GET/POST.
     */
    public function templateAction()
    {
        // fetch folder parameter
        if (isset($this->_request->f)) {
            $folder = $this->_request->getParam('f');
        } else {
            throw new OntoWiki_Exception('Missing parameter f!');
            exit;
        }

        // fetch template parameter
        if (isset($this->_request->t)) {
            $template = $this->_request->getParam('t');
        } else {
            throw new OntoWiki_Exception('Missing parameter t!');
            exit;
        }

        if (!preg_match('/^[a-z_]+$/', $folder) || !preg_match('/^[a-z_]+$/', $template)) {
            throw new OntoWiki_Exception('Illegal characters in folder or template name!');
            exit;
        }

        $path = _OWROOT . $this->_config->themes->path . $this->_config->themes->default . 'templates/' . $folder . DIRECTORY_SEPARATOR;
        $file = $template . '.' . $this->_helper->viewRenderer->getViewSuffix();

        if (!is_readable($path . $file)) {
            // $this->log('Template file not readable: ' . $path .  $file, Zend_Log::ERR);
            throw new OntoWiki_Exception('Template file not readable. ' . $path .  $file);
            exit;
        }

        // set script path
        $this->view->setScriptPath($path);

        // assign get and post parameters to view
        $this->view->assign($this->_request->getParams());

        // set header
        $this->_response->setRawHeader('Content-type: text/html');

        // render script
        $this->_response->setBody($this->view->render($file));
    }


    /**
     * JSON outputs of the transitive closure of resources to a given start
     * resource and an transitive attribute
     */
    public function transitiveclosureAction()
    {
        // service controller needs no view renderer
        $this->_helper->viewRenderer->setNoRender();
        // disable layout for Ajax requests
        $this->_helper->layout()->disableLayout();

        $store    = OntoWiki::getInstance()->erfurt->getStore();
        $response = $this->getResponse();

        // fetch start resource parameter
        if (isset($this->_request->sr)) {
            $resource = $this->_request->getParam('sr', null, true);
        } else {
            throw new OntoWiki_Exception('Missing parameter sr (start resource)!');
            exit;
        }

        // fetch property resource parameter
        if (isset($this->_request->p)) {
            $property = $this->_request->getParam('p', null, true);
        } else {
            throw new OntoWiki_Exception('Missing parameter p (property)!');
            exit;
        }

        // m is automatically used and selected
        if ((!isset($this->_request->m)) && (!$this->_owApp->selectedModel)) {
            throw new OntoWiki_Exception('No model pre-selected model and missing parameter m (model)!');
            exit;
        } else {
            $model = $this->_owApp->selectedModel;
        }
        
        // fetch inverse parameter
        $inverse = $this->_request->getParam('inverse', 'true');
        switch ($inverse) {
            case 'false':   /* fallthrough */
            case 'no':      /* fallthrough */
            case 'off':     /* fallthrough */
            case '0':       
                $inverse = false;
                break;
            default:
                $inverse = true;
        }

        $store = $model->getStore();
        
        // get the transitive closure
        $closure = $store->getTransitiveClosure((string)$model, $property, array($resource), $inverse);

        // send the response
        $response->setHeader('Content-Type', 'application/json');
        $response->setBody(json_encode($closure));
        $response->sendResponse();
        exit;
    }
    
    /**
     * JSON output of the RDFauthor selection Cache File of the current model or
     * of the model given in parameter m
     */
    public function rdfauthorcacheAction()
    {
        // service controller needs no view renderer
        $this->_helper->viewRenderer->setNoRender();
        // disable layout for Ajax requests
        $this->_helper->layout()->disableLayout();

        $store    = OntoWiki::getInstance()->erfurt->getStore();
        $response = $this->getResponse();
        $model    = $this->_owApp->selectedModel;

        if (isset($this->_request->m)) {
            $model = $store->getModel($this->_request->m);
        }
        if (empty($model)) {
            throw new OntoWiki_Exception('Missing parameter m (model) and no selected model in session!');
            exit;
        }

        $output = array();

        $properties = $model->sparqlQuery('SELECT DISTINCT ?uri {
            ?uri a ?propertyClass.
            FILTER(
                sameTerm(?propertyClass, <'.EF_OWL_OBJECT_PROPERTY.'>) ||
                sameTerm(?propertyClass, <'.EF_OWL_DATATYPE_PROPERTY.'>) ||
                sameTerm(?propertyClass, <'.EF_OWL_ONTOLOGY_PROPERTY.'>) ||
                sameTerm(?propertyClass, <'.EF_RDF_PROPERTY.'>)
            )} LIMIT 200 ');
        if (!empty($properties)) {

            // push all URIs to titleHelper
            $titleHelper = new OntoWiki_Model_TitleHelper($model);
            foreach($properties as $property) {
                 $titleHelper->addResource($property['uri']);
            }

            $lastProperty = end($properties);
            foreach($properties as $property) {
                $newProperty = array();

                // return title from titleHelper
                $newProperty['label'] = $titleHelper->getTitle($property['uri']);

                $pdata = $model->sparqlQuery('SELECT DISTINCT ?key ?value
                    WHERE {
                        <'.$property['uri'].'> ?key ?value
                        FILTER(
                         sameTerm(?key, <'.EF_RDF_TYPE.'>) ||
                         sameTerm(?key, <'.EF_RDFS_DOMAIN.'>) ||
                         sameTerm(?key, <'.EF_RDFS_RANGE.'>)
                        )
                        FILTER(isUri(?value))
                    }
                LIMIT 20');

                if (!empty($pdata)) {
                    $types = array();
                    $ranges = array();
                    $domains = array();
                    // prepare the data in arrays
                    foreach($pdata as $data) {
                        if ( ($data['key'] == EF_RDF_TYPE) && ($data['value'] != EF_RDF_PROPERTY) ) {
                            $types[] = $data['value'];
                        } elseif ($data['key'] == EF_RDFS_RANGE) {
                            $ranges[] = $data['value'];
                        } elseif ($data['key'] == EF_RDFS_DOMAIN) {
                            $domains[] = $data['value'];
                        }
                    }

                    if (!empty($types)) {
                        $newProperty['type'] = array_unique($types);
                    }

                    if (!empty($ranges)) {
                        $newProperty['range'] = array_unique($ranges);
                    }
                    
                    if (!empty($domains)) {
                        $newProperty['domain'] = array_unique($domains);
                    }

                }
                $output[ $property['uri'] ] = $newProperty;
            }
        }

        // send the response
        $response->setHeader('Content-Type', 'application/json');
        $response->setBody(json_encode($output));
        $response->sendResponse();
        exit;
    }


    /**
     * JSON output of the RDFauthor init config, which is a RDF/JSON Model
     * without objects where the user should be able to add data
     *
     * get/post parameters:
     *   mode - class, resource or clone
     *          class: prop list based on one class' resources
     *          resource: prop list based on one resource
     *          clone: prop list and values based on one resource (with new uri)
     *          edit: prop list and values based on one resource
     *   uri  - parameter for mode (class uri, resource uri)
     */
    public function rdfauthorinitAction()
    {
        // service controller needs no view renderer
        $this->_helper->viewRenderer->setNoRender();
        // disable layout for Ajax requests
        $this->_helper->layout()->disableLayout();

        $store    = OntoWiki::getInstance()->erfurt->getStore();
        $response = $this->getResponse();
        $model    = $this->_owApp->selectedModel;

        if (isset($this->_request->m)) {
            $model = $store->getModel($this->_request->m);
        }
        if (empty($model)) {
            throw new OntoWiki_Exception('Missing parameter m (model) and no selected model in session!');
            exit;
        }

        if ( (isset($this->_request->uri)) && (Zend_Uri::check($this->_request->uri)) ) {
            $parameter = $this->_request->uri;
        } else {
            throw new OntoWiki_Exception('Missing or invalid parameter uri (clone uri) !');
            exit;
        }

        if (isset($this->_request->mode)) {
            $workingMode = $this->_request->mode;
        } else {
            $workingMode = 'resource';
        }

        if ($workingMode != 'edit') {
            $resourceUri = $model->getBaseUri(). 'newResource/' .md5(date('F j, Y, g:i:s:u a'));
        } else {
            $resourceUri = $parameter;
        }

        if ($workingMode == 'class') {
            $properties = $model->sparqlQuery('SELECT DISTINCT ?uri ?value {
                ?s ?uri ?value.
                ?s a <'.$parameter.'>.
                } LIMIT 20 ', array('result_format' => 'extended'));
        } elseif ($workingMode == 'clone') {
            # BUG: more than one values of a property are not supported right now
            # BUG: Literals are not supported right now
            $properties = $model->sparqlQuery('SELECT ?uri ?value {
                <'.$parameter.'> ?uri ?value.
                #FILTER (isUri(?value))
                } LIMIT 20 ', array('result_format' => 'extended'));
        } elseif ($workingMode == 'edit') {
            $properties = $model->sparqlQuery('SELECT ?uri ?value {
                <'.$parameter.'> ?uri ?value.
                } LIMIT 20 ', array('result_format' => 'extended'));
        } else { // resource
            $properties = $model->sparqlQuery('SELECT DISTINCT ?uri ?value {
                <'.$parameter.'> ?uri ?value.
                } LIMIT 20 ', array('result_format' => 'extended'));
        }
        
        // empty object to hold data
        $output        = new stdClass();
        $newProperties = new stdClass();
        
        $properties = $properties['results']['bindings'];
        
        // feed title helper w/ URIs
        $titleHelper = new OntoWiki_Model_TitleHelper($model);
        $titleHelper->addResources($properties, 'uri');
        
        if (!empty($properties)) {
            foreach ($properties as $property) {
                
                $currentUri   = $property['uri']['value'];
                $currentValue = $property['value']['value'];
                $currentType  = $property['value']['type'];

                $value = new stdClass();
                
                if ($currentType == 'literal' || $currentType == 'typed-literal') {                    
                    if (isset($property['value']['datatype'])) {
                        $value->datatype = $property['value']['datatype'];
                    } else if (isset($property['value']['xml:lang'])) {
                        $value->lang = $property['value']['xml:lang'];
                    }
                    /* not in RDFauthor 0.8
                    else {
                        // plain literal --> rdfQuery needs extra quotes
                        $currentValue = '"' . $currentValue . '"';
                    }
                    */
                }

                // return title from titleHelper
                $value->title = $titleHelper->getTitle($currentUri);
                
                if ($currentUri == EF_RDF_TYPE) {
                    switch ($workingMode) {
                        case 'resource':
                            /* fallthrough */
                        case 'clone':
                            $value->value  = $currentValue;
                            break;
                        case 'edit':
                            $value->value  = $currentValue;
                            break;
                        case 'class':
                            $value->value  = $parameter;
                            break;
                    }
                    
                    $value->type   = $currentType;
                    #$value->hidden = true;
                    
                } else { // $currentUri != EF_RDF_TYPE
                    if ( ($workingMode == 'clone') || ($workingMode == 'edit') ) {
                        $value->value = $currentValue;
                        $value->type  = $currentType;
                    }
                }

                // deal with multiple values of a property
                if (isset($newProperties->$currentUri)) {
                    $tempProperty = $newProperties->$currentUri;
                    $tempProperty[] = $value;
                    $newProperties->$currentUri = $tempProperty;
                } else {
                    $newProperties->$currentUri = array($value);
                }
            } // foreach
            $output->$resourceUri = $newProperties;
        } else {
            // empty sparql results -> start with a plain resource
            if ($workingMode == 'class') {
                // for classes, add the rdf:type property
                $value = new stdClass();
                $value->value = $parameter;
                $value->type = 'uri';
                $value->hidden = true;
                $uri = EF_RDF_TYPE;
                $newProperties->$uri = array($value);
            }
            
            $value = new stdClass();
            $value->type = 'literal';
            $value->title = 'label';
            $uri = EF_RDFS_LABEL;
            $newProperties->$uri = array($value);
            
            $output->$resourceUri = $newProperties;
        }

        // send the response
        $response->setHeader('Content-Type', 'application/json');
        $response->setBody(json_encode($output));
        $response->sendResponse();
        exit;
    }
    
    protected function _findStatementsForObjectsWithHashes($graphUri, $indexWithHashedObjects, $hashFunc = 'md5')
    {
        $queryOptions = array(
            'result_format' => 'extended'
        );
        $result = array();
        foreach ($indexWithHashedObjects as $subject => $predicates) {
            foreach ($predicates as $predicate => $hashedObjects) {
                $query = "SELECT ?o FROM <$graphUri> WHERE {<$subject> <$predicate> ?o .}";
                $queryObj = Erfurt_Sparql_SimpleQuery::initWithString($query);
                
                if ($queryResult = $this->_owApp->erfurt->getStore()->sparqlQuery($queryObj, $queryOptions)) {
                    $bindings = $queryResult['results']['bindings'];
                    
                    for ($i = 0, $max = count($bindings); $i < $max; $i++) {
                        $currentObject = $bindings[$i]['o'];
                        
                        $objectString = Erfurt_Utils::buildLiteralString(
                            $currentObject['value'], 
                            isset($currentObject['datatype']) ? $currentObject['datatype'] : null, 
                            isset($currentObject['xml:lang']) ? $currentObject['xml:lang'] : null);

                        $hash = $hashFunc($objectString);
                        if (in_array($hash, $hashedObjects)) {
                            // add current statement to result
                            if (!isset($result[$subject])) {
                                $result[$subject] = array();
                            }
                            if (!isset($result[$subject][$predicate])) {
                                $result[$subject][$predicate] = array();
                            }
                            
                            $objectSpec = array(
                                'value' => $currentObject['value'], 
                                'type'  => str_replace('typed-', '', $currentObject['type'])
                            );
                            if (isset($currentObject['datatype'])) {
                                $objectSpec['datatype'] = $currentObject['datatype'];
                            } else if (isset($currentObject['xml:lang'])) {
                                $objectSpec['lang'] = $currentObject['xml:lang'];
                            }
                            
                            array_push($result[$subject][$predicate], $objectSpec);
                        }
                    }
                }
            }
        }
        
        return $result;
    }
}
