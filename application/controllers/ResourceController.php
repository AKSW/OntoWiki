<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki resource controller.
 *
 * @package OntoWiki_Controller
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

        $title = $resource->getTitle($this->_config->languages->locale)
               ? $resource->getTitle($this->_config->languages->locale)
               : OntoWiki_Utils::contractNamespace((string)$resource);
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

            // set RDFa widgets update info for editable graphs and other graph info
            $graphInfo = array();
            $editableFlags = array();
            foreach ($graphs as $g) {
                $graphInfo[$g] = $titleHelper->getTitle($g, $this->_config->languages->locale);

                if ($this->_erfurt->getAc()->isModelAllowed('edit', $g)) {
                    $editableFlags[$g] = true;
                    $this->view->placeholder('update')->append(
                        array(
                            'sourceGraph'    => $g,
                            'queryEndpoint'  => $this->_config->urlBase . 'sparql/',
                            'updateEndpoint' => $this->_config->urlBase . 'update/'
                        )
                    );
                } else {
                    $editableFlags[$g] = false;
                }
            }

            $this->view->graphs        = $graphInfo;
            $this->view->editableFlags = $editableFlags;
            $this->view->values        = $values;
            $this->view->predicates    = $predicates;
            $this->view->resourceUri   = (string)$resource;
            $this->view->graphUri      = $graph->getModelIri();
            $this->view->graphBaseUri  = $graph->getBaseIri();
            $this->view->editable = false; // use $this->editableFlags[$graph] now
            // prepare namespaces
            $namespaces = $graph->getNamespaces();
            $graphBase  = $graph->getBaseUri();
            if (!array_key_exists($graphBase, $namespaces)) {
                $namespaces = array_merge($namespaces, array($graphBase => OntoWiki_Utils::DEFAULT_BASE));
            }
            $this->view->namespaces = $namespaces;
        }

        $toolbar = $this->_owApp->toolbar;

        // show only if not forwarded and if model is writeable
        // TODO: why is isEditable not false here?
        if ($this->_request->getParam('action') == 'properties' && $graph->isEditable() &&
                $this->_owApp->erfurt->getAc()->isModelAllowed('edit', $this->_owApp->selectedModel)
        ) {
            // TODO: check acl
            $toolbar->appendButton(OntoWiki_Toolbar::EDIT, array('name' => 'Edit Properties', 'title' => 'SHIFT + ALT + e'));
            $toolbar->appendButton(
                OntoWiki_Toolbar::EDITADD, array(
                    'name'  => 'Clone',
                    'class' => 'clone-resource',
                    'title' => 'SHIFT + ALT + l'
                )
            );
            // ->appendButton(OntoWiki_Toolbar::EDITADD, array('name' => 'Add Property', 'class' => 'property-add'));
            $params = array(
                    'name' => 'Delete',
                    'url'  => $this->_config->urlBase . 'resource/delete/?r=' . urlencode((string)$resource)
            );
            $toolbar->appendButton(OntoWiki_Toolbar::SEPARATOR)
                    ->appendButton(OntoWiki_Toolbar::DELETE, $params);

            $toolbar->prependButton(OntoWiki_Toolbar::SEPARATOR)
                    ->prependButton(OntoWiki_Toolbar::ADD, array(
                        'name' => 'Add Property', 
                        '+class' => 'property-add', 
                        'title' => 'SHIFT + ALT + a'
                    )
            );

            $toolbar->prependButton(OntoWiki_Toolbar::SEPARATOR)
                    ->prependButton(OntoWiki_Toolbar::CANCEL, array(
                        '+class' => 'hidden', 
                        'title' => 'SHIFT + ALT + c'
                    )
            );

            $toolbar->prependButton(
                OntoWiki_Toolbar::SAVE, array(
                    '+class' => 'hidden', 
                    'title' => 'SHIFT + ALT + s'
                )
            );
        }

        // let plug-ins add buttons
        $toolbarEvent = new Erfurt_Event('onCreateToolbar');
        $toolbarEvent->resource = (string)$resource;
        $toolbarEvent->graph    = (string)$graph;
        $toolbarEvent->toolbar  = $toolbar;
        $eventResult = $toolbarEvent->trigger();

        if ($eventResult instanceof OntoWiki_Toolbar) {
            $toolbar = $eventResult;
        }

        // add toolbar
        $this->view->placeholder('main.window.toolbar')->set($toolbar);

        //show modules
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

        // the list is managed by a controller plugin that catches special http-parameters
        // @see Ontowiki/Controller/Plugin/ListSetupHelper.php

        //here this list is added to the view
        $listHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('List');
        $listName = 'instances';
        if ($listHelper->listExists($listName)) {
            $list = $listHelper->getList($listName);
            $list->setStore($store);
            $listHelper->addList($listName, $list, $this->view);
        } else {
            if ($this->_owApp->selectedModel == null) {
                $this->_owApp->appendMessage(
                    new OntoWiki_Message('your session timed out. select a model',  OntoWiki_Message::ERROR)
                );
                $this->_redirect($this->_config->baseUrl);
            }
            $list = new OntoWiki_Model_Instances($store, $this->_owApp->selectedModel, array());
            $listHelper->addListPermanently($listName, $list, $this->view);
        }

        //two usefull order
        //$list->orderByUri();
        //$list->setOrderProperty('http://ns.ontowiki.net/SysOnt/order');

        //begin view building
        $this->view->placeholder('main.window.title')->set('Resource List');

        // rdfauthor on a list is not possible yet
        // TODO: check acl
        // build toolbar
        /*
         * toolbar disabled for 0.9.5 (reactived hopefully later :) ) */

            if ($graph->isEditable()) {
                $toolbar = $this->_owApp->toolbar;
                $toolbar->appendButton(
                    OntoWiki_Toolbar::EDITADD, array('name' => 'Add Instance', 'class' => 'init-resource')
                );
                // ->appendButton(OntoWiki_Toolbar::EDIT, array('name' => 'Edit Instances', 'class' => 'edit-enable'))
                // ->appendButton(OntoWiki_Toolbar::SEPARATOR)
                // ->appendButton(OntoWiki_Toolbar::DELETE, array('name' => 'Delete Selected', 'class' => 'submit'))
                // ->prependButton(OntoWiki_Toolbar::SEPARATOR)
                // ->prependButton(OntoWiki_Toolbar::CANCEL)
                // ->prependButton(OntoWiki_Toolbar::SAVE);
                $this->view->placeholder('main.window.toolbar')->set($toolbar);
            }
        /*

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

        $this->addModuleContext('main.window.list');
        $this->addModuleContext('main.window.instances');
    }

    /**
     * Deletes one or more resources denoted by param 'r'
     * TODO: This should be done by a evolution pattern in the future
     */
    public function deleteAction()
    {
        $this->view->clearModuleCache();

        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();

        $store     = $this->_erfurt->getStore();
        $model     = $this->_owApp->selectedModel;
        $modelIri  = (string) $model;
        $redirect  = $this->_request->getParam('redirect', $this->_config->urlBase);

        if (isset($this->_request->r)) {
            $resources = $this->_request->getParam('r', array());
        } else {
            throw new OntoWiki_Exception('Missing parameter r!');
        }

        if (!is_array($resources)) {
            $resources = array($resources);
        }

        // get versioning
        $versioning = $this->_erfurt->getVersioning();

        $count = 0;
        if ($this->_erfurt->getAc()->isModelAllowed('edit', $modelIri)) {
            foreach ($resources as $resource) {

                // if we have only a nice uri, fill to full uri
                if (Zend_Uri::check($resource) == false) {
                    // check for namespace
                    if (strstr($resource, ':')) {
                        $resource = OntoWiki_Utils::expandNamespace($resource);
                    } else {
                        $resource = $model->getBaseIri() . $resource;
                    }
                }

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

                $result = $store->sparqlQuery($sparqlQuery, array('result_format'=>'extended'));
                // transform them to statement array to be compatible with store methods
                foreach ($result['results']['bindings'] as $stmt) {
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
            throw new OntoWiki_Controller_Exception("No model given.");
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
            throw new OntoWiki_Controller_Exception("Format '$format' not supported.");
        }

        // Check whether model exists. If not: 404 Not Found.
        if (!$store->isModelAvailable($modelUri, false)) {
            $response = $this->getResponse();
            $response->setRawHeader('HTTP/1.0 404 Not Found');
            throw new OntoWiki_Controller_Exception("Model '$modelUri' not found.");
        }

        // Check whether model is available (with acl). If not: 403 Forbidden.
        if (!$store->isModelAvailable($modelUri)) {
            $response = $this->getResponse();
            $response->setRawHeader('HTTP/1.0 403 Forbidden');
            throw new OntoWiki_Controller_Exception("Model '$modelUri' not available.");
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

        /*
         * Event: allow for adding / deleting statements to the export
         *   event uses a memory model and gets an empty memory model as
         *   default value, all plugins should add statements to the existing
         *   value and should not create a new model as return value
         */
        $event             = new Erfurt_Event('beforeExportResource');
        $event->resource   = $resource;
        $event->modelUri   = $modelUri;
        $event->setDefault = new Erfurt_Rdf_MemoryModel();
        $addedModel        = $event->trigger();
        if (is_object($addedModel) && get_class($addedModel) == 'Erfurt_Rdf_MemoryModel') {
            $addedStatements = $addedModel->getStatements();
        } else {
            $addedStatements = array();
        }

        $response = $this->getResponse();
        $response->setHeader('Content-Type', $contentType, true);
        $response->setHeader('Content-Disposition', ('filename="'.$filename.'"'));

        $serializer = Erfurt_Syntax_RdfSerializer::rdfSerializerWithFormat($format);
        echo $serializer->serializeResourceToString($resource, $modelUri, false, true, $addedStatements);
        return;
    }
}
