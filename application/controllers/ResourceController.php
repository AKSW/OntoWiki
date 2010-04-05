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
            $this->view->editable = false; //$graph->isEditable();
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
        
        // show only if not forwarded and if model is writeable
        // TODO: why is isEditable not false here?
        if ($this->_request->getParam('action') == 'properties' && $graph->isEditable() &&
                $this->_owApp->erfurt->getAc()->isModelAllowed('edit', $this->_owApp->selectedModel)
                ) {
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
            
            $toolbar->prependButton(OntoWiki_Toolbar::SEPARATOR)
                    ->prependButton(OntoWiki_Toolbar::ADD, array('name' => 'Add Property', '+class' => 'hidden edit property-add'));
            
            $toolbar->prependButton(OntoWiki_Toolbar::SEPARATOR)
                    ->prependButton(OntoWiki_Toolbar::CANCEL, array('+class' => 'hidden'))
                    ->prependButton(OntoWiki_Toolbar::SAVE, array('+class' => 'hidden'));
            $this->view->placeholder('main.window.toolbar')->set($toolbar);
        }
        
        $this->addModuleContext('main.window.properties');
        
        
    }
    
    /**
     * Displays resources of a certain type and property values that have
     * been selected by the user.
     */
    public function instancesAction()
    {
        $store       = $this->_owApp->erfurt->getStore();
        $graph       = $this->_owApp->selectedModel;
        $resource    = $this->_owApp->selectedResource;
        $navigation  = $this->_owApp->navigation;
        $translate   = $this->_owApp->translate;

        //the instances object is setup in Ontowiki/Controller/Plugin/ListSetupHelper.php

        $start = microtime(true);
        $instances = $this->_session->instances;
        if(!($instances instanceof OntoWiki_Model_Instances)){
            throw new OntoWiki_Exception("something went wrong with list creation");
            exit;
        } else {
            $instances->updateValueQuery();
        }
        
        //begin view building
        $this->view->placeholder('main.window.title')->set('Resource List');
        
        $this->view->resourceQuery = (string) $instances->getResourceQuery();
        //echo htmlentities($this->view->resourceQuery);
        $this->view->valueQuery = (string) $instances->getQuery();
        //echo htmlentities($this->view->valueQuery);
        
        $this->view->permalink = $this->_config->urlBase.'list/'.$instances->getPermalink();
        $this->view->urlBase = $this->_config->urlBase;

        $this->view->filter = $instances->getFilter();
        $filter_js = json_encode(is_array($this->view->filter) ? $this->view->filter : array());
        $this->view->headScript()->appendScript(
            'function showPermaLink(){$("#permalink").slideToggle(400);}
            function showresQuery(){$("#resQuery").slideToggle(400);}
            function showvalQuery(){$("#valQuery").slideToggle(400);}
             var reloadUrl = "'.
            new OntoWiki_Url(array(), array('p', 'limit')). // url to reload -> without config params
            '";
            filtersFromSession = ' . $filter_js.';');

        $this->view->headScript()->appendFile(
            $this->_config->staticUrlBase.
            'extensions/modules/filter/resources/FilterAPI.js'
        );

        // build menu
        $actionMenu = new OntoWiki_Menu();
        $actionMenu->setEntry('Toggle show Permalink', "javascript:showPermaLink()");
        $actionMenu->setEntry('Toggle show Resource Query',"javascript:showresQuery()");
        $actionMenu->setEntry('Toggle show Value Query', "javascript:showvalQuery()");
        $actions = new OntoWiki_Menu();
        $actions->setEntry('View', $actionMenu);
        $this->view->placeholder('main.window.menu')->set($actions->toArray());

        if ($instances->hasData()) {
            $this->view->instanceInfo = $instances->getResources();
            $this->view->instanceData = $instances->getValues();
            //echo '<pre>'; print_r($this->view->instanceData); echo '</pre>';
            $itemsOnPage = count($this->view->instanceData);
            
            $this->view->propertyInfo = $instances->getShownProperties();
            
            $time = (microtime(true) - $start) * 1000;
            
            $this->view->type      = (string)$resource;
            $this->view->start     = $instances->getOffset() + 1;
            $this->view->class     = preg_replace('/^.*[#\/]/', '', (string)$resource);
            $translate = $this->_owApp->translate;
            
            $statusBar = $this->view->placeholder('main.window.statusbar');

            $limit = $instances->getLimit();
            $this->view->limit = $limit;
            $this->view->itemsOnPage = $itemsOnPage;
            $offset = $instances->getOffset();
            if($limit != 0){
                $page = ($offset / $limit) +1;
            } else {
                $page = 1;
            }

            if($graph->getOption($this->_config->sysont->properties->isLarge)){
                $statusBar->append(OntoWiki_Pager::get( Erfurt_Store::COUNT_NOT_SUPPORTED, $limit, $itemsOnPage, $page));
            } else {
                $query = clone $instances->getResourceQuery();

                $where = 'WHERE '.$query->getWhere();

                try {
                    $count = $store->countWhereMatches($graph->getModelIri(), $where, '?resourceUri', true);
                } catch (Erfurt_Store_Exception $e) {
                    $count = Erfurt_Store::COUNT_NOT_SUPPORTED;
                }
                $this->view->count = $count;
                $statusBar->append(OntoWiki_Pager::get($count, $limit, $itemsOnPage, $page));

                if ($count != Erfurt_Store::COUNT_NOT_SUPPORTED) {
                    $results = $count > 1 ? $translate->translate('results') : $translate->translate('result');
                    $this->view->numResultsMsg = sprintf($translate->translate('Search returned %1$d %2$s.'), $count, $results);
                    $statusBar->append(sprintf($translate->translate('Search returned %1$d %2$s.'), $count, $results));
                }
            }
            
            $limit = '<ul><li>Show me: <a class="minibutton" href="'. $this->_config->urlBase.'list/limit/10'.'">10</a></li>';
            $limit .= '<li><a class="minibutton" href="'. $this->_config->urlBase.'list/limit/50'.'">50</a></li>';
            $limit .= '<li><a class="minibutton" href="'. $this->_config->urlBase.'list/limit/100'.'">100</a></li>';
            $limit .= '<li> ... <a class="minibutton" href="'. $this->_config->urlBase.'list/limit/0'.'">all</a></ul></li>';
            $statusBar->append($limit);
            
            if (defined('_OWDEBUG')) {
                $this->view->timeMsg = sprintf($this->_owApp->translate->translate('Query execution took %1$d ms.'), $time);
                $statusBar->append(sprintf($this->_owApp->translate->translate('Query execution took %1$d ms.'), $time));
            }
            
            //$this->statusbarpagerAction();
            //$this->view->placeholder('main.window.statusbar')->append($this->render("statusbarpager"));
            
            // prepare namespaces
            $namespaces = $graph->getNamespaces();
            $graphBase  = $graph->getBaseUri();
            if (!array_key_exists($graphBase, $namespaces)) {
                $namespaces = array_merge($namespaces, array($graphBase => OntoWiki_Utils::DEFAULT_BASE));
            }
            $this->view->namespaces = $namespaces;
            
            // TODO: check acl
            // build toolbar
            /*
             * toolbar disabled for 0.9.5 (reactived hopefully later :) )

            if ($graph->isEditable()) {
                $toolbar = $this->_owApp->toolbar;
                $toolbar->appendButton(OntoWiki_Toolbar::EDIT, array('name' => 'Edit Instances', 'class' => 'edit-enable'))
                        ->appendButton(OntoWiki_Toolbar::EDITADD, array('name' => 'Add Instance', 'class' => 'init-resource'))
                        ->appendButton(OntoWiki_Toolbar::SEPARATOR)
                        ->appendButton(OntoWiki_Toolbar::DELETE, array('name' => 'Delete Selected', 'class' => 'submit'))
                        ->prependButton(OntoWiki_Toolbar::SEPARATOR)
                        ->prependButton(OntoWiki_Toolbar::CANCEL)
                        ->prependButton(OntoWiki_Toolbar::SAVE);
                //$this->view->placeholder('main.window.toolbar')->set($toolbar);
            }
            
            $url = new OntoWiki_Url(
                array(
                    'controller' => 'resource',
                    'action' => 'delete'
                ),
                array()
            );
            
            $this->view->formActionUrl = (string)$url;
            $this->view->formMethod    = 'post';
            $this->view->formName      = 'instancelist';
            $this->view->formEncoding  = 'multipart/form-data';
            *
            */
            
            $url = new OntoWiki_Url();
            $this->view->redirectUrl = (string)$url;

            // register & init modules
            $moduleRegistry = OntoWiki_Module_Registry::getInstance();
            $moduleRegistry->register('properties', 'main.window.innerwindows')
                           ->register('showproperties', 'main.window.innerwindows')
                           ->register('filter', 'main.window.innerwindows');
        }

        $this->addModuleContext('main.window.instances');


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
        
        $additional = array();
        if ((isset($this->_request->provenance) && (boolean)$this->_request->provenance)) {
            $bNodeCounter = 1;
            
            $model = $store->getModel($modelUri); 
            
            $fileUri = 'http://' . $_SERVER['HTTP_HOST'] . $this->_request->getRequestUri(); 
            $curBNode = '_:node' . $bNodeCounter++;
            $additional[$fileUri] = array(
                EF_RDF_TYPE => array(array(
                    'value' => 'http://purl.org/net/provenance/ns#DataItem',
                    'type' => 'uri'
                )),
                'http://purl.org/net/provenance/ns#createdBy' => array(array(
                    'value' => $curBNode,
                    'type' => 'bnode'
                ))
            );
            $additional[$curBNode] = array(
                EF_RDF_TYPE => array(array(
                    'value' => 'http://purl.org/net/provenance/ns#DataCreation',
                    'type' => 'uri'
                )),
                'http://purl.org/net/provenance/ns#performedAt' => array(array(
                    'type' => 'literal', 
                    'value' => date('c'),
                    'datatype' => EF_XSD_DATETIME
                )),
                'http://purl.org/net/provenance/ns#performedBy' => array(array(
                    'value' => '_:node'.(++$bNodeCounter),
                    'type' => 'bnode'
                ))
            );
            $curBNode = '_:node'.$bNodeCounter++;
            
            $additional[$curBNode] = array(
                EF_RDF_TYPE => array(array(
                    'value' => 'http://purl.org/net/provenance/types#DataCreatingService',
                    'type' => 'uri'
                )),
                'http://www.w3.org/2000/01/rdf-schema#comment' => array(array(
                    'type' => 'literal', 
                    'value' => 'OntoWiki v0.95 (http://ontowiki.net)'
                ))
            );
            
            $s = $resource;
            $operatorUri = $model->getOption('http://purl.org/net/provenance/ns#operatedBy');
            if ($operatorUri !== null) {
                $additional[$s] = array(
                    'http://purl.org/net/provenance/ns#operatedBy' => array(array(
                        'type' => 'uri', 
                        'value' => $operatorUri[0]['value']
                    ))
                );
            } else {
                $additional[$s] = array();
            }
            
            $versioning = Erfurt_App::getInstance()->getVersioning();
            $history = $versioning->getHistoryForResource($resource, $modelUri);
            
            foreach ($history as $i=>$hItem) {
                $curBNode = '_:node' . $bNodeCounter++;
                
                $additional[$s]['http://purl.org/net/provenance/ns#CreatedBy'] = array(array(
                        'type' => 'bnode', 
                        'value' => $curBNode
                ));
                
                $additional[$curBNode] = array(
                    EF_RDF_TYPE => array(array(
                        'type' => 'uri', 
                        'value' => 'http://purl.org/net/provenance/ns#DataCreation'
                    )),
                    'http://purl.org/net/provenance/ns#performedAt' => array(array(
                        'type' => 'literal', 
                        'value' => date('c', $hItem['tstamp']),
                        'datatype' => EF_XSD_DATETIME
                    )),
                    'http://purl.org/net/provenance/ns#performedBy' => array(array(
                        'type' => 'uri', 
                        'value' => $hItem['useruri']
                    ))
                );
                
                if ($i<(count($history)-1)) {
                    $additional[$curBNode]['http://purl.org/net/provenance/ns#precededBy'] = array(array(
                        'type' => 'bnode',
                        'value' => '_:node' . ($bNodeCounter+1) 
                    ));
                }
                
                $s = '_:node'.$bNodeCounter++;     
            }
        }
            
        $response = $this->getResponse();
        $response->setHeader('Content-Type', $contentType, true);
        $response->setHeader('Content-Disposition', ('filename="'.$filename.'"'));
    
        $serializer = Erfurt_Syntax_RdfSerializer::rdfSerializerWithFormat($format);
        echo $serializer->serializeResourceToString($resource, $modelUri, false, true, $additional);
        $response->sendResponse();
        exit;
    }
}
