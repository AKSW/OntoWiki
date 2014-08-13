<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2006-2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki model controller.
 *
 * @category   OntoWiki
 * @package    OntoWiki_Controller
 * @author     Norman Heino <norman.heino@gmail.com>
 * @author     Philipp Frischmuth <pfrischmuth@googlemail.com>
 */
class ModelController extends OntoWiki_Controller_Base
{
    /**
     * Adds statement to a named graph
     */
    public function addAction()
    {
        $this->view->placeholder('main.window.title')->set('Import Statements to the Knowledge Base');
        $this->_helper->viewRenderer->setScriptAction('create');
        OntoWiki::getInstance()->getNavigation()->disableNavigation();

        $this->view->formActionUrl    = $this->_config->urlBase . 'model/add';
        $this->view->formEncoding     = 'multipart/form-data';
        $this->view->formClass        = 'simple-input input-justify-left';
        $this->view->formMethod       = 'post';
        $this->view->formName         = 'addmodel';

        $this->view->title    = $this->view->_('Add Statements to Knowledge Base');

        $model                     = $this->_owApp->selectedModel;
        $this->view->modelTitle    = $model->getTitle();
        $this->view->importActions = $this->_getImportActions();

        if ($model->isEditable()) {
            $toolbar = $this->_owApp->toolbar;
            $toolbar->appendButton(OntoWiki_Toolbar::SUBMIT, array('name' => 'Add Data', 'id' => 'addmodel'))
                ->appendButton(OntoWiki_Toolbar::RESET, array('name' => 'Cancel'));
            $this->view->placeholder('main.window.toolbar')->set($toolbar);
        } else {
            $this->_owApp->appendMessage(
                new OntoWiki_Message(
                    'No write permissions on model "' . $this->view->modelTitle . '"',
                    OntoWiki_Message::WARNING
                )
            );
            return;
        }

        if (!$this->_request->isPost()) {
            // FIX: http://www.webmasterworld.com/macintosh_webmaster/3300569.htm
            // disable connection keep-alive
            $response = $this->getResponse();
            $response->setHeader('Connection', 'close', true);
            $response->sendHeaders();

            return;
        } else {
            $this->_doImportActionRedirect((string)$model);
        }
    }

    /**
     * Configures options for a specified graph.
     */
    public function configAction()
    {
        $this->addModuleContext('main.window.modelconfig');
        OntoWiki::getInstance()->getNavigation()->disableNavigation();

        if (!$this->_request->getParam('m')) {
            throw new OntoWiki_Controller_Exception("Missing parameter 'm'.");
        }

        $store    = $this->_owApp->erfurt->getStore();
        $graphUri = $this->_request->getParam('m');
        $model    = $store->getModel($graphUri);

        // Make sure the current user is allowed to edit the model.
        if (!$model || !$model->isEditable()) {
            $this->_owApp->appendMessage(
                new OntoWiki_Message("No write permissions on model '{$graphUri}'", OntoWiki_Message::WARNING)
            );

            return;
        } else {
            $toolbar = $this->_owApp->toolbar;
            $toolbar->appendButton(OntoWiki_Toolbar::SUBMIT, array('name' => 'Save Model Configuration'))
                ->appendButton(OntoWiki_Toolbar::RESET, array('name' => 'Cancel'));
            $this->view->placeholder('main.window.toolbar')->set($toolbar);
        }

        // get cache for invalidation later
        $queryCache = $this->_erfurt->getQueryCache();

        // disable versioning
        $versioning = $this->_erfurt->getVersioning();
        $versioning->enableVersioning(false);

        // If there is POST data, this is the set request
        if ($this->_request->isPost()) {
            $this->view->clearModuleCache('modellist');

            $post = $this->_request->getPost();

            // Check the is hidden option.
            if (isset($post['ishidden']) && $post['ishidden'] === 'ishidden') {
                // In this case we need to set the value to true in the sys ont.
                $model->setOption(
                    $this->_config->sysont->properties->hidden,
                    array(
                         array(
                             'value'    => 'true',
                             'type'     => 'literal',
                             'datatype' => EF_XSD_BOOLEAN
                         )
                    )
                );
            } else {
                // We unset the value here (means not hidden).
                $model->setOption($this->_config->sysont->properties->hidden);
            }

            // Check the is isLarge option.
            if (isset($post['isLarge']) && $post['isLarge'] === 'isLarge') {
                // In this case we need to set the value to true in the sys ont.
                $model->setOption(
                    $this->_config->sysont->properties->isLarge,
                    array(
                         array(
                             'value'    => 'true',
                             'type'     => 'literal',
                             'datatype' => EF_XSD_BOOLEAN
                         )
                    )
                );
            } else {
                // We unset the value here (means not hidden).
                $model->setOption($this->_config->sysont->properties->isLarge);
            }

            // Check the use sysbase option.
            if (isset($post['usesysbase']) && $post['usesysbase'] === 'usesysbase') {
                // Checked, so check, whether the value is already set.
                if ($graphUri !== $this->_config->sysbase->model) {
                    $useSysBaseOld = $model->getOption($this->_config->sysont->properties->hiddenImports);

                    if (null !== $useSysBaseOld) {
                        $alreadySet = false;
                        foreach ($useSysBaseOld as $row) {
                            if ($row['value'] === $this->_config->sysbase->model && $row['type'] === 'uri') {
                                $alreadySet = true;
                                break;
                            }
                        }

                        if (!$alreadySet) {
                            $useSysBaseNew   = $useSysBaseOld;
                            $useSysBaseNew[] = array(
                                'type'  => 'uri',
                                'value' => $this->_config->sysbase->model
                            );

                            $model->setOption($this->_config->sysont->properties->hiddenImports, $useSysBaseNew);
                        }
                    } else {
                        $useSysBaseNew   = array();
                        $useSysBaseNew[] = array(
                            'type'  => 'uri',
                            'value' => $this->_config->sysbase->model
                        );

                        $model->setOption($this->_config->sysont->properties->hiddenImports, $useSysBaseNew);
                    }
                }

                // Check whether SysBase is already available... If not import it.
                if (!$store->isModelAvailable($this->_config->sysbase->model, false)) {
                    $m = $store->getNewModel($this->_config->sysbase->model, '', 'owl', false);
                    try {
                        if (is_readable($this->_config->sysbase->path)) {
                            // load SysOnt from file
                            $store->importRdf(
                                $this->_config->sysbase->model,
                                _OWROOT . $this->_config->sysbase->path,
                                'rdfxml',
                                Erfurt_Syntax_RdfParser::LOCATOR_FILE,
                                false
                            );
                        } else {
                            throw new Erfurt_Exception();
                        }
                    } catch (Erfurt_Exception $e) {
                        // Delete the model, for the import failed.
                        $store->deleteModel($this->_config->sysbase->model, false);

                        throw new Erfurt_Store_Exception(
                            'Import of "' . $this->_config->sysbase->model . '" failed: "' . $e->getMessage() . '"'
                        );
                    }

                    // Set SysBase hidden!
                    $m->setOption(
                        $this->_config->sysont->properties->hidden,
                        array(
                             array(
                                 'value'    => 'true',
                                 'type'     => 'literal',
                                 'datatype' => EF_XSD_BOOLEAN
                             )
                        )
                    );
                }
            } else {
                // Not checked... Remove if currently set.
                if ($graphUri !== $this->_config->sysbase->model) {
                    $useSysBaseOld = $model->getOption($this->_config->sysont->properties->hiddenImports);

                    if (null !== $useSysBaseOld) {
                        $currentlySet = false;
                        foreach ($useSysBaseOld as $i => $row) {
                            if (($row['value'] === $this->_config->sysbase->model) && ($row['type'] === 'uri')) {
                                $currentlySet = true;
                                unset($useSysBaseOld[$i]);
                                break;
                            }
                        }

                        if ($currentlySet) {
                            $useSysBaseNew = $useSysBaseOld;

                            $model->setOption($this->_config->sysont->properties->hiddenImports, $useSysBaseNew);
                        }
                    }
                }
            }

            /**
             * insert a new prefix and namespace to the model
             */
            if (isset($post['new_prefix_prefix']) || isset($post['new_prefix_namespace'])) {
                if (!isset($post['new_prefix_prefix']) || !isset($post['new_prefix_namespace'])) {
                    // Incomplete input
                    $this->_owApp->appendMessage(
                        new OntoWiki_Message(
                            'Incomplete input, namespace or prefix is missing.',
                            OntoWiki_Message::ERROR
                        )
                    );
                } else {
                    try {
                        if ($post['new_prefix_prefix'] != '' && $post['new_prefix_namespace'] != '') {
                            $model->addNamespacePrefix($post['new_prefix_prefix'], $post['new_prefix_namespace']);
                        }
                    } catch (Erfurt_Ac_Exception $e) {
                        $this->_owApp->appendMessage(
                            new OntoWiki_Message(
                                'No write permissions on model "' . $this->view->modelTitle . '"',
                                OntoWiki_Message::WARNING
                            )
                        );
                    } catch (Erfurt_Exception $e) {
                        $this->_owApp->appendMessage(
                            new OntoWiki_Message($e->getMessage(), OntoWiki_Message::ERROR)
                        );
                    }
                }
            }

            // invalidate model and config model and sysbase if available
            if ($store->isModelAvailable($this->_config->sysbase->model, false)) {
                $queryCache->invalidateWithModelIri($this->_config->sysbase->model);
            }
            $queryCache->invalidateWithModelIri($this->_config->sysont->model);
            $queryCache->invalidateWithModelIri($graphUri);

            // Forward to info action
            $this->_redirect(
                $this->_config->urlBase . 'model/config/?m=' . urlencode($this->_request->m),
                array('code' => 302)
            );

            return;
        } else {
            if (isset($this->_request->delete_prefix)) {
                try {
                    $model->deleteNamespacePrefix($this->_request->delete_prefix);
                } catch (Erfurt_Ac_Exception $e) {
                    $this->_owApp->appendMessage(
                        new OntoWiki_Message(
                            'No write permissions on model "' . $this->view->modelTitle . '"',
                            OntoWiki_Message::WARNING
                        )
                    );
                } catch (Erfurt_Exception $e) {
                    $this->_owApp->appendMessage(
                        new OntoWiki_Message($e->getMessage(), OntoWiki_Message::ERROR)
                    );
                }

                // invalidate model and config model
                $queryCache->invalidateWithModelIri($this->_config->sysont->model);
                $queryCache->invalidateWithModelIri($graphUri);

                // Forward to info action
                $this->_redirect(
                    $this->_config->urlBase . 'model/config/?m=' . urlencode($this->_request->m),
                    array('code' => 302)
                );

                return;
            } else {
                // Set the window title in the appropriate language.
                $translate   = $this->_owApp->translate;
                $windowTitle = $translate->_('Model Configuration');
                $this->view->placeholder('main.window.title')->set($windowTitle);

                $this->view->formActionUrl = $this->_config->urlBase . 'model/config';
                $this->view->formMethod    = 'post';
                $this->view->formClass     = 'simple-input input-justify-left';
                $this->view->formName      = 'modelconfig';

                $isLarge = $model->getOption($this->_config->sysont->properties->isLarge);
                if (null !== $isLarge && ($isLarge[0]['value'] === 'true') || ($isLarge[0]['value'] == 1)) {
                    // Model does not count currently
                    $this->view->isLarge = 'checked="checked"';
                } else {
                    $this->view->isLarge = '';
                }

                $isHidden = $model->getOption($this->_config->sysont->properties->hidden);
                if (null !== $isHidden && ($isHidden[0]['value'] === 'true') || ($isHidden[0]['value'] == 1)) {
                    // Model is currently hidden
                    $this->view->isHidden = 'checked="checked"';
                } else {
                    $this->view->isHidden = '';
                }

                $useSysBase      = $model->getOption($this->_config->sysont->properties->hiddenImports);
                $sysBaseImported = false;

                if (is_array($useSysBase)) {
                    // Option is set... now check whether one of the values is the SysBase uri.
                    foreach ($useSysBase as $row) {
                        if ($row['value'] == $this->_config->sysbase->model) {
                            $sysBaseImported = true;
                            break;
                        }
                    }
                }

                if ($sysBaseImported) {
                    $this->view->useSysBase = 'checked="checked"';
                } else {
                    $this->view->useSysBase = '';

                    // show a warning message
                    $translate   = $this->_owApp->translate;
                    $messageText = 'This knowledge base does not import the OntoWiki System Base model.'
                        . ' This means you probably don\'t have human-readable representations for the'
                        . ' most commonly used vocabularies. If you want to use the OntoWiki System'
                        . ' Base just check the according box and click \'Save Model Configuration\'.';
                    $this->_owApp->appendMessage(
                        new OntoWiki_Message(
                            $translate->_($messageText),
                            OntoWiki_Message::WARNING
                        )
                    );
                }

                if ($graphUri === $this->_config->sysbase->model) {
                    $this->view->useSysBaseDisabled = 'disabled="disabled"';
                } else {
                    $this->view->useSysBaseDisabled = '';
                }

                $this->view->readonly = 'readonly="readonly"';
                $this->view->modeluri = $graphUri;
                $this->view->baseuri  = $model->getBaseUri();

                /**
                 * Sending prefixes to the config view
                 */
                $prefixes             = $model->getNamespacePrefixes();
                $this->view->prefixes = array();
                ksort($prefixes);
                foreach ($prefixes as $prefix => $namespace) {
                    $this->view->prefixes[] = array($prefix, $namespace);
                }

                /*
                 * $toolbar = $this->_owApp->toolbar;
                 * $toolbar->appendButton(
                 * OntoWiki_Toolbar::DELETE,
                 * array('name' => 'Delete namespaces', 'class' => 'submit actionid', 'id' => 'delete')
                 * );
                 */
            }
        }

        // re-enable versioning again
        $versioning->enableVersioning(true);
    }

    /**
     * Creates a new named graph
     */
    public function createAction()
    {
        $this->addModuleContext('main.window.modelcreate');
        $store = $this->_erfurt->getStore();
        $this->view->clearModuleCache('modellist');

        OntoWiki::getInstance()->getNavigation()->disableNavigation();
        $this->view->placeholder('main.window.title')->set('Create New Knowledge Base');
        $this->view->formActionUrl = $this->_config->urlBase . 'model/create';
        $this->view->formEncoding  = 'multipart/form-data';
        $this->view->formClass     = 'simple-input input-justify-left';
        $this->view->formMethod    = 'post';
        $this->view->formName      = 'createmodel';

        // this is the default action
        $defaultActions = array(
            'empty' => array(
                'parameter' => 'checked',
                'controller' => 'model',
                'action' => 'info',
                'label' => 'Create a (nearly) empty knowledge base',
                'description' => 'Just add the label and type to the new model.'
            )
        );
        $importActions = array_merge($defaultActions, $this->_getImportActions());
        $this->view->importActions = $importActions;

        if (!$this->_erfurt->isActionAllowed('ModelManagement')) {
            $this->_owApp->appendMessage(
                new OntoWiki_Message('Model management is not allowed.', OntoWiki_Message::ERROR)
            );
            $this->view->errorFlag = true;

            return;
        }

        // TODO: add this to the template in order to allow users to tune it
        $this->view->modelUri = $this->_config->urlBase .'NEWMODEL/';

        $toolbar = $this->_owApp->toolbar;
        $toolbar->appendButton(
            OntoWiki_Toolbar::SUBMIT,
            array('name' => 'Create Knowledge Base', 'id' => 'createmodel')
        )->appendButton(
            OntoWiki_Toolbar::RESET,
            array('name' => 'Cancel', 'id' => 'createmodel')
        );
        $this->view->placeholder('main.window.toolbar')->set($toolbar);
        $this->view->title = $this->view->_('Create Knowledge Base');

        if (!$this->_request->isPost()) {
            // FIX: http://www.webmasterworld.com/macintosh_webmaster/3300569.htm
            // disable connection keep-alive
            $response = $this->getResponse();
            $response->setHeader('Connection', 'close', true);
            $response->sendHeaders();
            return;
        } else {
            // process the user input
            $post = $this->_request->getPost();

            // determine or create the model URI
            $newModelUri = '';
            if (trim($post['modeluri']) != '') {
                // URI given via form input
                $newModelUri = trim($post['modeluri']);
            } else if (trim($post['title']) != '') {
                // create a nice URI from the title (poor mans way)
                $urlBase        = $this->_config->urlBase;
                $title          = trim($post['title']);
                $title          = str_replace(' ', '', $title);
                $title          = urlencode($title);
                $newModelUri    = $urlBase . $title . '/';
            } else {
                // create a default model with counter
                $urlBase = $this->_config->urlBase . 'kb';
                $counter = 0;
                do {
                    $newModelUri = $urlBase . ($counter++) . '/';
                } while ($store->isModelAvailable($newModelUri, false));
            }

            if ($newModelUri == '') {
                $this->_owApp->appendMessage(
                    new OntoWiki_Message(
                        'Please provide at least a valid URI for the new Knowledge Base.',
                        OntoWiki_Message::ERROR
                    )
                );
                $this->view->errorFlag = true;
                return;
            }

            // create model
            if ($store->isModelAvailable($newModelUri, false)) {
                // model exists
                $this->_owApp->appendMessage(
                    new OntoWiki_Message('Given Knowledge Base already exists.', OntoWiki_Message::ERROR)
                );
                $this->view->errorFlag = true;
                return;
            } else {
                // model does not exist, will be created
                $model = $store->getNewModel(
                    $newModelUri, $newModelUri, Erfurt_Store::MODEL_TYPE_OWL
                );
                $this->_owApp->appendMessage(
                    new OntoWiki_Message('Knowledge Base successfully created.', OntoWiki_Message::SUCCESS)
                );

                // add label
                $additions = new Erfurt_Rdf_MemoryModel();
                if (isset($post['title']) && trim($post['title']) != '') {
                    $additions->addAttribute($newModelUri, EF_RDFS_LABEL, $post['title']);
                }
                $model->addMultipleStatements($additions->getStatements());

                // TODO: add ACL infos based on the post data

                $this->_doImportActionRedirect($newModelUri);
            }
        }
    }

    public function deleteAction()
    {
        $model = $this->_request->model;
        if ($this->_erfurt->isActionAllowed('ModelManagement')) {
            $event           = new Erfurt_Event('onPreDeleteModel');
            $event->modelUri = $model;
            $event->trigger();

            try {
                $this->_erfurt->getStore()->deleteModel($model);

                if (
                    (null !== $this->_owApp->selectedModel)
                    && ($this->_owApp->selectedModel->getModelIri() === $model)
                ) {
                    $this->_owApp->selectedModel = null;
                    //deletes selected model - always needed?
                    $this->view->clearModuleCache();

                    $url = new OntoWiki_Url(
                        array(
                            'controller' => $this->_config->index->default->controller,
                            'action' => $this->_config->index->default->action,
                        ),
                        array()
                    );
                    $this->_redirect($url, array('code' => 302));
                }
            } catch (Exception $e) {
                $this->_owApp->appendMessage(
                    new OntoWiki_Message(
                        'Error deleting model: ' . $e->getMessage() . '<br/>' . $e->getTraceAsString(),
                        OntoWiki_Message::ERROR
                    )
                );
            }
        } else {
            $this->_owApp->appendMessage(
                new OntoWiki_Message('Error deleting model: Not allowed.', OntoWiki_Message::ERROR)
            );
            $this->_redirect($_SERVER['HTTP_REFERER'], array('code' => 302));

            return;
        }
        $this->view->clearModuleCache(); //deletes selected model - always needed?
        $this->_redirect($_SERVER['HTTP_REFERER'], array('code' => 302));
    }

    /**
     * Serializes a given model or (if supported) all models into a given format.
     */
    public function exportAction()
    {
        if (!$this->_owApp->erfurt->getAc()->isActionAllowed(Erfurt_Ac_Default::ACTION_MODEL_EXPORT)) {
            $this->_owApp->appendMessage(
                new OntoWiki_Message('Model export not allowed.', OntoWiki_Message::ERROR)
            );
            $this->_redirect($_SERVER['HTTP_REFERER'], array('code' => 302));

            return;
        }

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

        // Check whether a model uri is given
        if (isset($this->_request->m)) {
            $modelUri = $this->_request->m;

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

            $description = Erfurt_Syntax_RdfSerializer::getFormatDescription($format);
            $contentType = $description['contentType'];
            $filename .= $description['fileExtension'];

            $this->_helper->viewRenderer->setNoRender();
            $this->_helper->layout->disableLayout();

            $response = $this->getResponse();
            $response->setHeader('Content-Type', $contentType, true);
            $response->setHeader('Content-Disposition', ('filename="' . $filename . '"'));

            $serializer = Erfurt_Syntax_RdfSerializer::rdfSerializerWithFormat($format);
            echo $serializer->serializeGraphToString($modelUri);

            return;
        } else {
            // Else use all available models.
            // TODO Exporters need to support this feature...
            $response = $this->getResponse();
            $response->setRawHeader('HTTP/1.0 400 Bad Request');
            throw new OntoWiki_Controller_Exception("No Graph URI given.");
        }
    }

    public function infoAction()
    {
        OntoWiki::getInstance()->getNavigation()->disableNavigation();
        $this->_owApp->selectedResource = new OntoWiki_Resource(
            $this->_request->getParam('m'), $this->_owApp->selectedModel
        );
        $store                          = $this->_owApp->erfurt->getStore();
        $graph                          = $this->_owApp->selectedModel;
        $resource                       = $this->_owApp->selectedResource;
        //$navigation = $this->_owApp->navigation;
        $translate = $this->_owApp->translate;

        $event        = new Erfurt_Event('onPropertiesAction');
        $event->uri   = (string)$resource;
        $event->graph = (string)$resource;
        $event->trigger();

        $windowTitle = $translate->_('Model info');
        $this->view->placeholder('main.window.title')->set($windowTitle);

        $title                    = $resource->getTitle($this->_owApp->getConfig()->languages->locale);
        $this->view->modelTitle   = $title ? $title : OntoWiki_Utils::contractNamespace((string)$resource);
        $resourcesUrl             = new OntoWiki_Url(array('route' => 'instances'), array());
        $resourcesUrl->init       = true;
        $this->view->resourcesUrl = (string)$resourcesUrl;

        if (!empty($resource)) {
            $model = new OntoWiki_Model_Resource($store, $graph, (string)$resource);

            $values     = $model->getValues();
            $predicates = $model->getPredicates();

            $titleHelper = new OntoWiki_Model_TitleHelper($graph);
            $graphs      = array_keys($predicates);
            $titleHelper->addResources($graphs);

            $graphInfo     = array();
            $editableFlags = array();
            foreach ($graphs as $g) {
                $graphInfo[$g]     = $titleHelper->getTitle($g, $this->_config->languages->locale);
                $editableFlags[$g] = false;
            }

            $this->view->graphs            = $graphInfo;
            $this->view->resourceIri       = (string)$resource;
            $this->view->graphIri          = $graph->getModelIri();
            $this->view->values            = $values;
            $this->view->predicates        = $predicates;
            $this->view->graphBaseIri      = $graph->getBaseIri();
            $this->view->namespacePrefixes = $graph->getNamespacePrefixes();
            $this->view->editableFlags     = $editableFlags;

            if (!is_array($this->view->namespacePrefixes)) {
                $this->view->namespacePrefixes = array();
            }
            if (!array_key_exists(OntoWiki_Utils::DEFAULT_BASE, $this->view->namespacePrefixes)) {
                $this->view->namespacePrefixes[OntoWiki_Utils::DEFAULT_BASE] = $graph->getBaseIri();
            }

            $infoUris = $this->_config->descriptionHelper->properties;
            //echo (string)$resource;

            if (count($values) > 0) {
                $query = 'ASK FROM <' . (string)$resource . '>'
                    . ' WHERE {'
                    . '     <' . (string)$resource . '> a <http://xmlns.com/foaf/0.1/PersonalProfileDocument>'
                    . ' }';
                $q     = Erfurt_Sparql_SimpleQuery::initWithString($query);
                if (
                    $this->_owApp->extensionManager->isExtensionActive('foafprofileviewer')
                    && $store->sparqlAsk($q) === true
                ) {
                    $this->view->showFoafLink = true;
                    $this->view->foafLink     = $this->_config->urlBase . 'foafprofileviewer/display';
                }
            }

            $this->view->infoPredicates = array();
            foreach ($infoUris as $infoUri) {
                if (isset($predicates[(string)$graph]) && array_key_exists($infoUri, $predicates[(string)$graph])) {
                    $this->view->infoPredicates[$infoUri] = $predicates[(string)$graph][$infoUri];
                }
            }
        }

        $this->addModuleContext('main.window.modelinfo');
    }

    public function selectAction()
    {
        if (isset($this->_request->m)) {
            // reset resource/class
            unset($this->_owApp->selectedResource);
            unset($this->_owApp->selectedClass);
            unset($this->_session->hierarchyOpen);

            $this->_redirect(
                $this->_config->urlBase . 'model/info/?m=' . urlencode($this->_request->m),
                array('code' => 302)
            );
        }
        $this->_redirect($this->_config->urlBase, array('code' => 302));
    }

    /**
     * Updates the current model with statements sent as JSON
     */
    public function updateAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();

        $errors = array();

        // check graph parameter
        if (!$this->_request->has('named-graph-uri')) {
            $errors[] = "Missing parameter 'named-graph-uri'.";
        }

        // Parsing may go wrong, when user types in corrupt data... So we catch all exceptions here...
        try {
            $flag = false;
            // check original graph
            if ($this->_request->has('original-graph')) {
                $flag               = true;
                $originalFormat     = $this->_request->getParam('original-format', 'rdfjson');
                $parser             = Erfurt_Syntax_RdfParser::rdfParserWithFormat($originalFormat);
                $originalStatements = $parser->parse(
                    $this->getParam('original-graph', false),
                    Erfurt_Syntax_RdfParser::LOCATOR_DATASTRING
                );
            }
            // check changed graph
            if ($this->_request->has('modified-graph')) {
                $flag               = true;
                $modifiedFormat     = $this->_request->getParam('modified-format', 'rdfjson');
                $parser             = Erfurt_Syntax_RdfParser::rdfParserWithFormat($modifiedFormat);
                $modifiedStatements = $parser->parse(
                    $this->getParam('modified-graph', false),
                    Erfurt_Syntax_RdfParser::LOCATOR_DATASTRING
                );
            }
        } catch (Exception $e) {
            $errors[] = 'Something went wrong: ' . $e->getMessage();
        }

        if (!$flag) {
            $errors[] = "At least one of the parameters 'original-graph' or 'modified-graph' must be supplied.";
        }

        // errors occured... so do not update... instead show error message or mark as bad request
        if (!empty($errors)) {
            if (null === $this->_request->getParam('redirect-uri')) {
                // This means, we do not redirect, so we can mark this request as bad request.
                $response = $this->getResponse();
                $response->setRawHeader('HTTP/1.0 400 Bad Request');
                throw new OntoWiki_Controller_Exception(implode(PHP_EOL, $errors));
            } else {
                // We have a redirect uri given, so we do not redirect, but show the error messages
                foreach ($errors as $e) {
                    $this->_owApp->appendMessage(new OntoWiki_Message($e, OntoWiki_Message::ERROR));
                }
                $server = $this->_request->getServer();
                if (isset($server['HTTP_REFERER'])) {
                    $this->_request->setParam('redirect-uri', $server['HTTP_REFERER']);
                }

                return;
            }
        }

        // instantiate model
        $graph = Erfurt_App::getInstance()->getStore()->getModel($this->getParam('named-graph-uri'));

        // update model
        $graph->updateWithMutualDifference($originalStatements, $modifiedStatements);
    }

    /**
     * prepare and do the redirect
     */
    private function _doImportActionRedirect($modelUri)
    {
        $post          = $this->_request->getPost();
        $id            = $post['importAction'];
        $importOptions = $post['importOptions'];
        $actions       = $this->_getImportActions();

        if (isset($actions[$id])) {
            $controller = $actions[$id]['controller'];
            $action     = $actions[$id]['action'];
        } else {
            $controller = 'model';
            $action     = 'info';
        }

        $url = new OntoWiki_Url(
            array(
                'controller' => $controller,
                'action' => $action
            ),
            array('m', 'importOptions')
        );
        $url->setParam('m', $modelUri);
        $url->setParam('importOptions', $importOptions);

        $this->_redirect($url, array('code' => 302));
    }

    private function _getImportActions()
    {
        /**
         * @trigger onProvideImportActions event to provide additional import actions
         *
         * Parameter: importActions
         *
         * Example:
         * <code>
         * <?php
         * $importActions = array('empty' => array(
         *   'controller' => 'model',
         *   'action' => 'info',
         *   'label' => 'Create an (nearly) empty knowledge base',
         *   'description' => 'Just add the label to the new model.'
         *   ));
         * ?>
         * </code>
         */
        $event                = new Erfurt_Event('onProvideImportActions');
        $event->importActions = array();
        $result               = $event->trigger();
        return $event->importActions;
    }
}
