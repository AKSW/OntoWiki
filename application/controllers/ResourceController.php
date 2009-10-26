<?php

/**
 * OntoWiki resource controller.
 * 
 * @package    application
 * @subpackage mvc
 * @author     Norman Heino <norman.heino@gmail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: ResourceController.php 4308 2009-10-14 15:13:51Z jonas.brekle@gmail.com $
 */
class ResourceController extends OntoWiki_Controller_Base
{
    private function _addLastModifiedHeader()
    {        
        $r = $this->_owApp->selectedResource;
        $m = $this->_owApp->selectedModel;
        
        if (!$m || !$r) {
            return;
        }
        
        $versioning   = Erfurt_App::getInstance()->getVersioning();
        $lastModArray = $versioning->getLastModifiedForResource($r, $m->getModelUri());
        
        if (null === $lastModArray || !is_numeric($lastModArray['tstamp'])) {
            return;
        }
        
        $response = $this->getResponse();
        $response->setHeader('Last-Modified', date('r', $lastModArray['tstamp']), true);
    }
    
    /**
     * Displays all preoperties and values for a resource, denoted by parameter 
     */
    public function propertiesAction()
    {   
        $this->_addLastModifiedHeader();
        
        $store      = $this->_owApp->erfurt->getStore();
        $graph      = $this->_owApp->selectedModel;
        $resource   = $this->_owApp->selectedResource;
        $navigation = $this->_owApp->navigation;
        $translate  = $this->_owApp->translate;
        
        // add export formats to resource menu
        $resourceMenu = OntoWiki_Menu_Registry::getInstance()->getMenu('resource');
        foreach (array_reverse(Erfurt_Syntax_RdfSerializer::getSupportedFormats()) as $key => $format) {
            $resourceMenu->prependEntry(
                'Export Resource as ' . $format,
                $this->_config->urlBase . 'resource/export/f/' . $key . '?r=' . urlencode($resource)
            );
        }
        
        $menu = new OntoWiki_Menu();
        $menu->setEntry('Resource', $resourceMenu);
        
        $event = new Erfurt_Event('onCreateMenu');
        $event->menu = $resourceMenu;
        $event->resource = $this->_owApp->selectedResource;
        $event->model = $this->_owApp->selectedModel;
        $event->trigger();
         
        $event = new Erfurt_Event('onPropertiesAction');
        $event->uri = (string)$resource;
        $event->graph = $this->_owApp->selectedModel->getModelUri();
        $event->trigger();
        
        // Give plugins a chance to add entries to the menu
        $this->view->placeholder('main.window.menu')->set($menu->toArray(false, true));
        
        $title = $resource->getTitle() ? $resource->getTitle() : OntoWiki_Utils::contractNamespace((string)$resource);
        $windowTitle = sprintf($translate->_('Properties of %1$s'), $title);
        $this->view->placeholder('main.window.title')->set($windowTitle);
        
        $this->_owApp->setDefaultRoute('properties');
        // $this->_owApp->lastRoute = 'properties';
        // OntoWiki_Navigation::register('index', array(
        //     'route'      => 'properties', 
        //     'controller' => 'resource', 
        //     'action'     => 'properties',
        //     'name'       => 'Properties', 
        //     'position'   => 0, 
        //     'active'     => false
        // ), true);
        
        if (!empty($resource)) {
            $event = new Erfurt_Event('onPreTabsContentAction');
            $event->uri = (string)$resource;
            $result = $event->trigger();
            
            if ($result) {
                $this->view->preTabsContent = $result;
            }
            
            $event = new Erfurt_Event('onPrePropertiesContentAction');
            $event->uri = (string)$resource;
            $result = $event->trigger();
            
            if ($result) {
                $this->view->prePropertiesContent = $result;
            }
            
            $model = new OntoWiki_Model_Resource($store, $graph, (string)$resource);
            $values = $model->getValues();
            $predicates = $model->getPredicates();

            // new trigger onPropertiesActionData to work with data (reorder with plugin)
            $event = new Erfurt_Event('onPropertiesActionData');
            $event->uri         = (string)$resource;
            $event->predicates  = $predicates;
            $event->values      = $values;
            $result = $event->trigger();
            
            if ( $result ) {
                $predicates = $event->predicates;
                $values     = $event->values;
            }
            
            $titleHelper = new OntoWiki_Model_TitleHelper($graph);
            // add graphs
            $graphs = array_keys($predicates);
            $titleHelper->addResources($graphs);
            
            $graphInfo = array();
            foreach ($graphs as $g) {
                $graphInfo[$g] = $titleHelper->getTitle($g, $this->_config->languages->locale);
            }
            
            $this->view->graphs        = $graphInfo;
            $this->view->values        = $values;
            $this->view->predicates    = $predicates;
            $this->view->resourceUri   = (string)$resource;
            $this->view->graphUri      = $graph->getModelIri();
            $this->view->graphBaseUri  = $graph->getBaseIri();
            
            // prepare namespaces
            $namespaces = $graph->getNamespaces();
            $graphBase  = $graph->getBaseUri();
            if (!array_key_exists($graphBase, $namespaces)) {
                $namespaces = array_merge($namespaces, array($graphBase => OntoWiki_Utils::DEFAULT_BASE));
            }
            $this->view->namespaces = $namespaces;
            
            // set RDFa widgets update info for editable graphs
            foreach ($graphs as $g) {
                if ($this->_erfurt->getAc()->isModelAllowed('edit', $g)) {
                    $this->view->placeholder('update')->append(array(
                        'sourceGraph'    => $g, 
                        'queryEndpoint'  => $this->_config->urlBase . 'sparql/', 
                        'updateEndpoint' => $this->_config->urlBase . 'update/'
                    ));
                }
            }
        }
        
        // show only if not forwarded
        if ($this->_request->getParam('action') == 'properties' && $graph->isEditable()) {
            // TODO: check acl
            $toolbar = $this->_owApp->toolbar;
            $toolbar->appendButton(OntoWiki_Toolbar::EDIT, array('name' => 'Edit Properties'));
            $toolbar->appendButton(OntoWiki_Toolbar::EDITADD, array(
                'name' => 'Clone Resource',
                'url' => 'javascript:createInstanceFromURI(\''.$this->view->resourceUri.'\');'
            ));
                    // ->appendButton(OntoWiki_Toolbar::EDITADD, array('name' => 'Add Property', 'class' => 'property-add'));
            $params = array(
                'name' => 'Delete Resource', 
                'url'  => $this->_config->urlBase . 'resource/delete/?r=' . urlencode((string)$resource)
            );
            $toolbar->appendButton(OntoWiki_Toolbar::SEPARATOR)
                    ->appendButton(OntoWiki_Toolbar::DELETE, $params);
            
            // $toolbar->prependButton(OntoWiki_Toolbar::SEPARATOR)
            //         ->prependButton(OntoWiki_Toolbar::CANCEL)
            //         ->prependButton(OntoWiki_Toolbar::SAVE);
            $this->view->placeholder('main.window.toolbar')->set($toolbar);
        }
        
        $this->addModuleContext('main.window.properties');
        
        
    }
    
    /**
     * Displays resources of a certain type and property values that have
     * bee selected by the user.
     */
    public function instancesAction()
    {
        $store       = $this->_owApp->erfurt->getStore();
        $graph       = $this->_owApp->selectedModel;
        $resource    = $this->_owApp->selectedResource;
        $navigation  = $this->_owApp->navigation;
        $translate   = $this->_owApp->translate;
        
        $title = $resource->getTitle() ? $resource->getTitle() : OntoWiki_Utils::contractNamespace((string)$resource);
        $windowTitle = sprintf($translate->_('Instances of %1$s'), $title);
        $this->view->placeholder('main.window.title')->set($windowTitle);
        
        // Hack: reset shown properties
        if ((string)$resource != $this->_owApp->selectedClass) {
            unset($this->_session->shownProperties);
            unset($this->_session->shownInverseProperties);
            unset($this->_session->filter);
            unset($this->_session->cloudproperties);
            unset($this->_session->instancelimit);
            unset($this->_session->instanceoffset);
            // if (is_array($this->_session->hierarchyOpen)) {
            //     if (!in_array($resource, $this->_session->hierarchyOpen)) {
            //         array_push($this->_session->hierarchyOpen, $resource);
            //     }
            // } else {
            //     $this->_session->hierarchyOpen = array($resource);
            // }
        }
        
        $this->_owApp->selectedClass = (string)$resource;
        $this->_owApp->setDefaultRoute('instances');
        
        // $this->_owApp->lastRoute = 'instances';
        // OntoWiki_Navigation::register('index', array(
        //     'route'      => 'instances', 
        //     'controller' => 'resource', 
        //     'action'     => 'instances',
        //     'name'       => 'Instances', 
        //     'position'   => 0, 
        //     'active'     => false
        // ), true);

        //determine limit & offset
        //request params go first
        //if they are not set : look into session
        //if no session (first load) : take default values
        $limit  = isset($this->_request->limit) ?
                    $this->_request->limit :
                    (isset($this->_session->instancelimit) ?
                        $this->_session->instancelimit :
                        10
                    );
        $offset = isset($this->_request->p) ? 
                    $this->_request->p * $limit - $limit :
                    (isset($this->_session->instancelimit) ?
                        $this->_session->instanceoffset : 
                        0
                    );

        //save to session
        $this->_session->instancelimit = $limit;
        $this->_session->instanceoffset = $offset;

        $options = array(
            'rdf_type' => $this->_owApp->selectedClass,
            'memberPredicate' => EF_RDF_TYPE, // TODO make this variable for handling collections...
            'withChilds' => true,
            'limit' => $limit,
            'offset' => $offset,
            'shownProperties' => is_array($this->_session->shownProperties) ? $this->_session->shownProperties : array(),
            'shownInverseProperties' => is_array($this->_session->shownInverseProperties) ? $this->_session->shownInverseProperties : array(),
            'filter' => is_array($this->_session->filter) ? $this->_session->filter : array(),
        );
        
        $start = microtime(true);
            
        // instantiate model
        $instances   = new OntoWiki_Model_Instances($store, $graph, $options);
        $this->_owApp->instances = $instances;
        $this->view->headScript()->appendScript('var classUri = "'.OntoWiki_Utils::contractNamespace($this->_owApp->selectedClass).'";');
            
        if ($instances->hasData()) {
            $this->view->instanceInfo = $instances->getResources();
            $this->view->instanceData = $instances->getValues();
            $itemsOnPage = count($this->view->instanceData);
            
            $this->view->propertyInfo = $instances->getShownProperties();
            
            $time = (microtime(true) - $start) * 1000;
            
            $this->view->type      = (string)$resource;
            $this->view->start     = $offset ? $offset + 1 : 1;
            $this->view->class     = preg_replace('/^.*[#\/]/', '', (string )$resource);
            $translate = $this->_owApp->translate;
			
            $query = clone $instances->getResourceQuery();
            
            $query->setLimit(0)->setOffset(0);
			
            $where = 'WHERE '.$query->getWhere();
            
            $count = $store->countWhereMatches($graph->getModelIri(), $where, '?resourceUri');
            
            $statusBar = $this->view->placeholder('main.window.statusbar');
            $this->view->count = $count;
            $this->view->limit = $limit;
            $this->view->itemsOnPage = $itemsOnPage;
            
            $statusBar->append(OntoWiki_Pager::get($count, $limit, $itemsOnPage));
            
            if ($count != Erfurt_Store::COUNT_NOT_SUPPORTED) {
                $results = $count > 1 ? $translate->translate('results') : $translate->translate('result');
                $this->view->numResultsMsg = sprintf($translate->translate('Search returned %1$d %2$s.'), $count, $results);
                $statusBar->append(sprintf($translate->translate('Search returned %1$d %2$s.'), $count, $results));
            }
            
            if (defined('_OWDEBUG')) {
                $this->view->timeMsg = sprintf($this->_owApp->translate->translate('Query execution took %1$d ms.'), $time);
                $statusBar->append(sprintf($this->_owApp->translate->translate('Query execution took %1$d ms.'), $time));
            }
            
            //$this->statusbarpagerAction();
            //$this->view->placeholder('main.window.statusbar')->append($this->render("statusbarpager"));
            
            // TODO: check acl
            // build toolbar
            if ($graph->isEditable()) {
                $toolbar = $this->_owApp->toolbar;
                $toolbar/*->appendButton(OntoWiki_Toolbar::EDIT, array('name' => 'Edit Instances'))
                      */->appendButton(OntoWiki_Toolbar::EDITADD, array('name' => 'Add Instance', 'class' => 'init-resource'))
                        ->appendButton(OntoWiki_Toolbar::SEPARATOR)
                        ->appendButton(OntoWiki_Toolbar::DELETE, array('name' => 'Delete Selected', 'class' => 'submit'))
                        ->prependButton(OntoWiki_Toolbar::SEPARATOR)
                        ->prependButton(OntoWiki_Toolbar::CANCEL)
                        ->prependButton(OntoWiki_Toolbar::SAVE);
                $this->view->placeholder('main.window.toolbar')->set($toolbar);
            }
            
            $url = new OntoWiki_Url(array('controller' => 'resource', 'action' => 'delete'), array());
            
            $this->view->formActionUrl = (string)$url;
            $this->view->formMethod    = 'post';
            $this->view->formName      = 'instancelist';
            $this->view->formEncoding  = 'multipart/form-data';
            
            $url = new OntoWiki_Url();
            $this->view->redirectUrl = (string)$url;
            
            $this->view->headScript()->appendFile(
                $this->_owApp->getStaticUrlBase() . 'extensions/themes/silverblue/scripts/serialize-php.js');
            // register modules
            $moduleRegistry = OntoWiki_Module_Registry::getInstance();
            $moduleRegistry->register('properties', 'main.window.innerwindows')
                           ->register('showproperties', 'main.window.innerwindows')
                           ->register('filter', 'main.window.innerwindows');
        }
        
        $this->addModuleContext('main.window.instances');
    }
    
    public function statusbarpagerAction(){
    	$store       = $this->_owApp->erfurt->getStore();
        $graph       = $this->_owApp->selectedModel;
    	if(!isset($this->_owApp->instances))
    		$this->instancesAction();
    	
    	$translate = $this->_owApp->translate;
    	$query = clone $this->_owApp->instances->getQuery();
        $query->getStartNode()->clearShownProperties();
        $newquery = $query->getRealQuery()->setDistinct(true);

        $where = 'WHERE '.$newquery->getWhere()->getSparql();
        $count = $store->countWhereMatches($graph->getModelIri(), $where, '?resourceUri');
        $itemsOnPage = count($this->_owApp->instances->getValues());
        $limit =  $this->_owApp->instances->getLimit();
            
            $statusBar = OntoWiki_Pager::get($count, $limit, $itemsOnPage);
            
            if ($count != Erfurt_Store::COUNT_NOT_SUPPORTED) {
                $results = $count > 1 ? $translate->translate('results') : $translate->translate('result');
                $statusBar += sprintf($translate->translate('Search returned %1$d %2$s.'), $count, $results);
            }
            
            if (defined('_OWDEBUG')) {
                $statusBar += sprintf($this->_owApp->translate->translate('Query execution took %1$d ms.'), -1);
            }
        echo "vor set";
        $this->view->statusBar = $statusBar;
        echo "nach set";
    }
    /**
     * Deletes one or more resources denoted by param 'r'
     */
    public function deleteAction()
    {
        $this->view->clearModuleCache();

        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();

        $store     = $this->_erfurt->getStore();
        $modelIri  = (string)$this->_owApp->selectedModel;
        $redirect  = $this->_request->getParam('redirect', $this->_config->urlBase);
        
        $resources = $this->_request->getParam('r', array());
        if (!is_array($resources)) {
            $resources = array($resources);
        }

        // get versioning
        $versioning = $this->_erfurt->getVersioning();    

        $count = 0;
        if ($this->_erfurt->getAc()->isModelAllowed('edit', $modelIri)) {
            foreach ($resources as $resource) {
                // action spec for versioning
                $actionSpec                 = array();
                $actionSpec['type']         = 130;
                $actionSpec['modeluri']     = $modelIri;
                $actionSpec['resourceuri']  = $resource;

                // starting action
                $versioning->startAction($actionSpec);

                $stmtArray = array();

                // query for all triples to delete them
                $sparqlQuery = new Erfurt_Sparql_SimpleQuery();
                $sparqlQuery->setProloguePart('SELECT ?p, ?o');
                $sparqlQuery->addFrom($modelIri);
                $sparqlQuery->setWherePart('{ <' . $resource . '> ?p ?o . }');

                $result = $store->sparqlQuery($sparqlQuery,array('result_format'=>'extended'));
                // transform them to statement array to be compatible with store methods
                foreach ($result['bindings'] as $stmt) {
                    $stmtArray[$resource][$stmt['p']['value']][] = $stmt['o'];
                }

                $store->deleteMultipleStatements($modelIri, $stmtArray);

                // stopping action
                $versioning->endAction();

                $count++;
            }

            $message = $count 
                     . ' resource'. ($count != 1 ? 's': '') 
                     . ($count ? ' successfully' : '') 
                     . ' deleted.';

            $this->_owApp->appendMessage(
                new OntoWiki_Message($message, OntoWiki_Message::SUCCESS)
            );

        } else {

            $message = 'not allowed.';

            $this->_owApp->appendMessage(
                new OntoWiki_Message($message, OntoWiki_Message::WARNING)
            );
        }
        
        $event = new Erfurt_Event('onDeleteResources');
        $event->resourceArray = $resources;
        $event->modelUri = $modelIri;
        $event->trigger();


        $this->_redirect($redirect, array('code' => 302));
    }
    
    public function exportAction()
    {
        $this->_addLastModifiedHeader();
        
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        
        if (isset($this->_request->m)) {
            $modelUri = $this->_request->m;
        } else if (isset($this->_owApp->selectedModel)) {
            $modelUri = $this->_owApp->selectedModel->getModelUri();
        } else {
            $response = $this->getResponse();
            $response->setRawHeader('HTTP/1.0 400 Bad Request');
            $response->sendResponse();
            throw new OntoWiki_Controller_Exception("No nodel given.");
            exit;
        }
        
        $resource = $this->getParam('r', true);
        
        // Check whether the f parameter is given. If not: default to rdf/xml
        if (!isset($this->_request->f)) {
            $format = 'rdfxml';
        } else {
            $format = $this->_request->f;
        }
        
        $format = Erfurt_Syntax_RdfSerializer::normalizeFormat($format);
        
        $store = $this->_erfurt->getStore();  
        
        // Check whether given format is supported. If not: 400 Bad Request.
        if (!in_array($format, array_keys(Erfurt_Syntax_RdfSerializer::getSupportedFormats()))) {
            $response = $this->getResponse();
            $response->setRawHeader('HTTP/1.0 400 Bad Request');
            $response->sendResponse();
            throw new OntoWiki_Controller_Exception("Format '$format' not supported.");
            exit;
        }
            
        // Check whether model exists. If not: 404 Not Found.
        if (!$store->isModelAvailable($modelUri, false)) {
            $response = $this->getResponse();
            $response->setRawHeader('HTTP/1.0 404 Not Found');
            $response->sendResponse();
            throw new OntoWiki_Controller_Exception("Model '$modelUri' not found.");
            exit;
        }
            
        // Check whether model is available (with acl). If not: 403 Forbidden.
        if (!$store->isModelAvailable($modelUri)) {
            $response = $this->getResponse();
            $response->setRawHeader('HTTP/1.0 403 Forbidden');
            $response->sendResponse();
            throw new OntoWiki_Controller_Exception("Model '$modelUri' not available.");
            exit;
        }
            
        $filename = 'export' . date('Y-m-d_Hi');
        
        switch ($format) {
            case 'rdfxml':
                $contentType = 'application/rdf+xml'; 
                $filename .= '.rdf';
                break;
            case 'rdfn3':
                $contentType = 'text/rdf+n3';
                $filename .= '.n3';
                break;
            case 'rdfjson':
                $contentType = 'application/json';
                $filename .= '.json';
                break;
            case 'turtle':
                $contentType = 'application/x-turtle';
                $filename .= '.ttl';
                break;
        }
            
        $response = $this->getResponse();
        $response->setHeader('Content-Type', $contentType, true);
        $response->setHeader('Content-Disposition', ('filename="'.$filename.'"'));
                
        $serializer = Erfurt_Syntax_RdfSerializer::rdfSerializerWithFormat($format);
        echo $serializer->serializeResourceToString($resource, $modelUri);
        $response->sendResponse();
        exit;
    }
}
