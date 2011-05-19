<?php

/**
 * OntoWiki model controller.
 * 
 * @package    application
 * @subpackage mvc
 * @author     Norman Heino <norman.heino@gmail.com>
 * @author     Philipp Frischmuth <pfrischmuth@googlemail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: ModelController.php 4162 2009-09-13 13:27:48Z jonas.brekle@gmail.com $
 */
class ModelController extends OntoWiki_Controller_Base
{
    /**
     * Adds statement to a named graph
     */
    public function addAction()
    {
        $this->view->placeholder('main.window.title')->set('Add Statements to Model');
        $this->_helper->viewRenderer->setScriptAction('create');
        OntoWiki_Navigation::disableNavigation();
        
        $this->view->formActionUrl    = $this->_config->urlBase . 'model/add';
        $this->view->formEncoding     = 'multipart/form-data';
        $this->view->formClass        = 'simple-input input-justify-left';
        $this->view->formMethod       = 'post';
        $this->view->formName         = 'addmodel';
        $this->view->activeForm       = 'upload';
        $this->view->referer          = isset($_SERVER['HTTP_REFERER']) ? urlencode($_SERVER['HTTP_REFERER']) : '';
        $this->view->supportedFormats = $this->_erfurt->getStore()->getSupportedImportFormats();
        
        $this->view->modelUri   = (string)$this->_owApp->selectedModel;
        $this->view->baseUri    = '';
        $this->view->title      = $this->view->_('Add Statements to Knowledge Base');
        
        $model = $this->_owApp->selectedModel;
        $this->view->modelTitle = $model->getTitle();
        
        if ($model->isEditable()) {
            $toolbar = $this->_owApp->toolbar;
            $toolbar->appendButton(OntoWiki_Toolbar::SUBMIT, array('name' => 'Add Data', 'id' => 'addmodel'))
                    ->appendButton(OntoWiki_Toolbar::RESET, array('name' => 'Cancel'));
            $this->view->placeholder('main.window.toolbar')->set($toolbar);
        } else {
            $this->_owApp->appendMessage(
                new OntoWiki_Message("No write permissions on model '{$this->view->modelTitle}'", OntoWiki_Message::WARNING)
            );
        }
        
        if (!$this->_request->isPost()) {
            // FIX: http://www.webmasterworld.com/macintosh_webmaster/3300569.htm
            // disable connection keep-alive
            $response = $this->getResponse();
            $response->setHeader('Connection', 'close', true);
            $response->sendHeaders();
            return;
        }
        
        // evaluate post data
        $messages = array();
        $post = $this->_request->getPost();
        $errorFlag = false;
        switch (true) {
            case ($post['activeForm'] == 'upload' and $_FILES['source']['error'] == UPLOAD_ERR_INI_SIZE):
            $message = 'The uploaded files\'s size exceeds the upload_max_filesize directive in php.ini.';
                $this->_owApp->appendMessage(
                    new OntoWiki_Message($message, OntoWiki_Message::ERROR)
                );
                $errorFlag = true;
                break;
            case ($post['activeForm'] == 'upload' and $_FILES['source']['error'] == UPLOAD_ERR_PARTIAL):
                $this->_owApp->appendMessage(
                    new OntoWiki_Message('The uploaded file was only partially uploaded.', OntoWiki_Message::ERROR)
                );
                $errorFlag = true;
                break;
            case ($post['activeForm'] == 'upload' and $_FILES['source']['error'] >= UPLOAD_ERR_NO_FILE):
                $message = 'There was an unknown error during file upload. Please check your PHP configuration.';
                $this->_owApp->appendMessage(
                    new OntoWiki_Message($message, OntoWiki_Message::ERROR)
                );
                $errorFlag = true;
                break;
        }
        
        // set submitted vars
        foreach ($post as $name => $value) {
            $this->view->$name = $value;
        }
        
        if (!$errorFlag) {

            // preparing versioning
            $versioning                 = $this->_erfurt->getVersioning();
            $actionSpec                 = array();
            $actionSpec['type']         = 120;
            $actionSpec['modeluri']     = $post['modelUri'];
            $actionSpec['resourceuri']  = $post['modelUri'];

            $versioning->startAction($actionSpec);

            // trying to import given data
            try {
                $this->_handleImport($post, false);
            } catch (Exception $e) {
                $this->_owApp->appendMessage(
                    new OntoWiki_Message('Error importing statements: ' . $e->getMessage(), OntoWiki_Message::ERROR)
                );
                return;
            }

            // stop Action
            $versioning->endAction();

            if (!empty($post['referer'])) {
                $redirect = urldecode($post['referer']);
            } else {
                $redirect = $this->_config->urlBase . 'model/add/?m=' . urlencode($this->view->modelUri);
            }
            
            $this->view->clearModuleCache('hierarchy');
            $this->view->clearModuleCache('modellist');
            
            $this->_redirect($redirect, array('code' => 302));
        }
    }
    
    /**
     * Configures options for a specified graph.
     */
    public function configAction()
    {
        OntoWiki_Navigation::disableNavigation();
        
        if (!$this->_request->getParam('m')) {
            throw new OntoWiki_Controller_Exception("Missing parameter 'm'.");
            exit;
        }
        
        $store      = $this->_owApp->erfurt->getStore();
        $graphUri   = $this->_request->getParam('m');
        $model      = $store->getModel($graphUri);
        
        // Make sure the current user is allowed to edit the model.
        if (!$model || !$model->isEditable()) {
            $this->_owApp->appendMessage(
                new OntoWiki_Message("No write permissions on model '{$graphUri}'", OntoWiki_Message::WARNING)
            );
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
                $model->setOption($this->_config->sysont->properties->hidden, array(array(
                    'value'    => 'true',
                    'type'     => 'literal',
                    'datatype' => EF_XSD_BOOLEAN
                )));
            } else {
                // We unset the value here (means not hidden).
                $model->setOption($this->_config->sysont->properties->hidden);
            }
            
            // Check the is isLarge option.
            if (isset($post['isLarge']) && $post['isLarge'] === 'isLarge') {
                // In this case we need to set the value to true in the sys ont.
                $model->setOption($this->_config->sysont->properties->isLarge, array(array(
                    'value'    => 'true',
                    'type'     => 'literal',
                    'datatype' => EF_XSD_BOOLEAN
                )));
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
                            $useSysBaseNew = $useSysBaseOld;
                            $useSysBaseNew[] = array(
                                'type'  => 'uri',
                                'value' => $this->_config->sysbase->model
                            );

                            $model->setOption($this->_config->sysont->properties->hiddenImports, $useSysBaseNew);
                        }
                    } else {
                        $useSysBaseNew = array();
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
                            $store->importRdf($this->_config->sysbase->model, _OWROOT . $this->_config->sysbase->path, 'rdfxml',
                                              Erfurt_Syntax_RdfParser::LOCATOR_FILE, false);
                        } else {
                            throw new Erfurt_Exception();
                        } 
                    } catch (Erfurt_Exception $e) {
                        // Delete the model, for the import failed.
                        $store->deleteModel($this->_config->sysbase->model, false);
                        
                        throw new Erfurt_Store_Exception("Import of '{$this->_config->sysbase->model}' failed: {$e->getMessage()}");
                    }
                    
                    // Set SysBase hidden!
                    $m->setOption($this->_config->sysont->properties->hidden, array(array(
                        'value'    => 'true',
                        'type'     => 'literal',
                        'datatype' => EF_XSD_BOOLEAN
                    )));
                }
            } else {
                // Not checked... Remove if currently set.
                if ($graphUri !== $this->_config->sysbase->model) {
                    $useSysBaseOld = $model->getOption($this->_config->sysont->properties->hiddenImports);

                    if (null !== $useSysBaseOld) {
                        $currentlySet = false;
                        foreach ($useSysBaseOld as $i=>$row) {
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
						new OntoWiki_Message("Incomplete input, namespace or prefix is missing.", OntoWiki_Message::ERROR)
					);
				} else {
					try {
					    if ($post['new_prefix_prefix'] != '' && $post['new_prefix_namespace'] != '') {
					        $model->addNamespacePrefix($post['new_prefix_prefix'], $post['new_prefix_namespace']);
					    }
					} catch (Erfurt_Ac_Exception $e) {
						$this->_owApp->appendMessage(
							new OntoWiki_Message("No write permissions on model '{$this->view->modelTitle}'", OntoWiki_Message::WARNING)
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
            } else {
                // do nothing
            }
            $queryCache->invalidateWithModelIri($this->_config->sysont->model);
            $queryCache->invalidateWithModelIri($graphUri);

            // Forward to info action
            $this->_redirect($this->_config->urlBase . 'model/config/?m=' . urlencode($this->_request->m), 
                             array('code' => 302));
            exit;
		} else if (isset($this->_request->delete_prefix)) {
			try {
				$model->deleteNamespacePrefix($this->_request->delete_prefix);
			} catch (Erfurt_Ac_Exception $e) {
				$this->_owApp->appendMessage(
					new OntoWiki_Message("No write permissions on model '{$this->view->modelTitle}'", OntoWiki_Message::WARNING)
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
            $this->_redirect($this->_config->urlBase . 'model/config/?m=' . urlencode($this->_request->m), 
                             array('code' => 302));
            exit;
		} else {
            // Set the window title in the appropriate language.
            $translate  = $this->_owApp->translate;
            $windowTitle = $translate->_('Model Configuration');
            $this->view->placeholder('main.window.title')->set($windowTitle);

            $this->view->formActionUrl = $this->_config->urlBase . 'model/config';
    		$this->view->formMethod    = 'post';
    		$this->view->formClass     = 'simple-input input-justify-left';
    		$this->view->formName      = 'modelconfig';

    		$isLarge = $model->getOption($this->_config->sysont->properties->isLarge);
    		if (null !== $isLarge && ( $isLarge[0]['value'] === 'true' ) || ($isLarge[0]['value'] == 1) ) {
    		    // Model does not count currently
    		    $this->view->isLarge = 'checked="checked"';
    		} else {
    		    $this->view->isLarge = '';
    		}

    		$isHidden = $model->getOption($this->_config->sysont->properties->hidden);
    		if (null !== $isHidden && ( $isHidden[0]['value'] === 'true' ) || ($isHidden[0]['value'] == 1) ) {
    		    // Model is currently hidden
    		    $this->view->isHidden = 'checked="checked"';
    		} else {
    		    $this->view->isHidden = '';
    		}


    		$useSysBase = $model->getOption($this->_config->sysont->properties->hiddenImports);
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
		        $translate = $this->_owApp->translate;
		        $messageText = 'This knowledge base does not import the OntoWiki System Base model. This means you probably don\'t '
		                     . 'have human-readable representations for the most commonly used vocabularies. If you want to use the '
		                     . 'OntoWiki System Base just check the according box and click \'Save Model Configuration\'.';
		        $this->_owApp->appendMessage(new OntoWiki_Message($translate->_($messageText), OntoWiki_Message::WARNING));
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
			$prefixes = $model->getNamespacePrefixes();
			$this->view->prefixes = array();
			ksort($prefixes);
			foreach ($prefixes as $prefix => $namespace) {
				$this->view->prefixes[] = array($prefix, $namespace);
			}

		/*	$toolbar = $this->_owApp->toolbar;
            $toolbar->appendButton(
                OntoWiki_Toolbar::DELETE, 
                array('name' => 'Delete namespaces', 'class' => 'submit actionid', 'id' => 'delete')
			);*/

        }

        // re-enable versioning again
        $versioning->enableVersioning(true);
    }
    
    /**
     * Creates a new named graph
     */
    public function createAction()
    {
        $this->view->clearModuleCache('modellist');
        
        OntoWiki_Navigation::disableNavigation();
        $this->view->placeholder('main.window.title')->set('Create New Knowledge Base');
        $this->view->formActionUrl = $this->_config->urlBase . 'model/create';
        $this->view->formEncoding  = 'multipart/form-data';
        $this->view->formClass     = 'simple-input input-justify-left';
        $this->view->formMethod    = 'post';
        $this->view->formName      = 'createmodel';
        $this->view->activeForm    = ini_get('allow_url_fopen') ? 'import' : 'empty';
        $this->view->referer       = '';
        
        $this->view->modelUri         = '';
        $this->view->baseUri          = '';
        $this->view->title            = $this->view->_('Create Knowledge Base');
        $this->view->supportedFormats = $this->_erfurt->getStore()->getSupportedImportFormats();
        
        if (!$this->_erfurt->isActionAllowed('ModelManagement')) {
            $this->_owApp->appendMessage(
                new OntoWiki_Message('Model management is not allowed.', OntoWiki_Message::ERROR)
            );
            $this->view->errorFlag = true;
            return;
        }
        
        // $this->view->messages = array(new OntoWiki_Message('Model already exists.', OntoWiki_Message::ERROR));
        
        $toolbar = $this->_owApp->toolbar;
        $toolbar->appendButton(OntoWiki_Toolbar::SUBMIT, array('name' => 'Create Knowledge Base', 'id' => 'createmodel'))
                ->appendButton(OntoWiki_Toolbar::RESET, array('name' => 'Cancel', 'id' => 'createmodel'));
        $this->view->placeholder('main.window.toolbar')->set($toolbar);
        
        
        
        if (!$this->_request->isPost()) {
            // FIX: http://www.webmasterworld.com/macintosh_webmaster/3300569.htm
            // disable connection keep-alive
            $response = $this->getResponse();
            $response->setHeader('Connection', 'close', true);
            $response->sendHeaders();
            return;
        }
        
        $post = $this->_request->getPost();
        $errorFlag = false;
        $newModelUri = isset($post['modelUri']) ? trim($post['modelUri']) : "";
        switch (true) {
            case $newModelUri == '':
                $this->_owApp->appendMessage(
                    new OntoWiki_Message('Model URI must not be empty.', OntoWiki_Message::ERROR)
                );
                $errorFlag = true;
                break;
            case $this->_erfurt->getStore()->isModelAvailable($newModelUri):
				$virtuosomessage = "";
				if('virtuoso'==strtolower($this->_erfurt->getStore()->getBackendName())){
					$virtuosomessage = '
					Add the following statement to let the graph show up in OntoWiki.
					SPARQL INSERT into <'.$post['modelUri'].'> {<'.$post['modelUri'].'> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://www.w3.org/2002/07/owl#Ontology>};
					 include debug=on in config.ini and clear query cache';
					}
                $this->_owApp->appendMessage(
                    new OntoWiki_Message('A knowledge base with the same URI already exists.'.$virtuosomessage, OntoWiki_Message::ERROR)
                );
                $errorFlag = true;
                break;
            case ($post['activeForm'] == 'upload' and $_FILES['source']['error'] == UPLOAD_ERR_INI_SIZE):
                $message = 'The uploaded files\'s size exceeds the upload_max_filesize directive in php.ini.';
                $this->_owApp->appendMessage(
                    new OntoWiki_Message($message, OntoWiki_Message::ERROR)
                );
                $errorFlag = true;
                break;
            case ($post['activeForm'] == 'upload' and $_FILES['source']['error'] == UPLOAD_ERR_PARTIAL):
                $this->_owApp->appendMessage(
                    new OntoWiki_Message('The file was only partially uploaded.', OntoWiki_Message::ERROR)
                );
                $errorFlag = true;
                break;
            case ($post['activeForm'] == 'upload' and $_FILES['source']['error'] >= UPLOAD_ERR_NO_FILE):
                $message = 'There was an unknown error during file upload. Please check your PHP configuration.';
                $this->_owApp->appendMessage(
                    new OntoWiki_Message($message, OntoWiki_Message::ERROR)
                );
                $errorFlag = true;
                break;
        }
        
        // set submitted vars
        foreach ($post as $name => $value) {
            $this->view->$name = $value;
        }
        
        if (!$errorFlag) {
            try {

                // disable versioning
                $versioning = $this->_erfurt->getVersioning();
                $oldValue = $versioning->isVersioningEnabled();
                $versioning->enableVersioning(false);

                $this->_handleImport($post, true);

            } catch (Exception $e) {
                $this->_owApp->appendMessage(
                    new OntoWiki_Message('Error importing knowledge base: ' . $e->getMessage(), OntoWiki_Message::ERROR)
                );
                return;
            }

            $model = $this->_erfurt->getStore()->getModel($newModelUri);
            
            if (null === $model) {
                $this->_owApp->appendMessage(
                    new OntoWiki_Message('Failed to get the model from store.', OntoWiki_Message::ERROR)
                );
                return;
            }
            
            // set userModelsEdit 
            try {
                // TODO: do not interface with ac directly
                // give creator write access
                $this->_erfurt->getAc()->setUserModelRight($model->getModelIri(), 'edit', 'grant');

                // create model resource with type SysOnt:Model
                $store = $this->_erfurt->getStore();
                $acModel = $this->_erfurt->getAcModel();
                
                $store->addStatement(
                    $acModel->getModelUri(),
                    $model->getModelUri(), 
                    EF_RDF_TYPE, 
                    array(
                        'value' => $this->_erfurt->getConfig()->ac->models->class, 
                        'type'  => 'uri'
                    ),
                    false
                );
            } catch (Erfurt_Exception $e) {
                $this->_owApp->appendMessage(
                    new OntoWiki_Message('Error setting model permissions: '. $e->getMessage(), OntoWiki_Message::ERROR)
                );
                return;
            }
            
            // re-enable versioning again
            $versioning->enableVersioning($oldValue);

            // redirect to model select
            // $this->_redirect($this->_config->urlBase . 'model/select/?m=' . urlencode($model->getModelIri()), array('code' => 302));
            
            // redirect to model config
            $this->_redirect($this->_config->urlBase . 'model/config/?m=' . urlencode($model->getModelIri()), array('code' => 302));
        }
    }
    
    private function _handleImport($postData, $createGraph = false)
    {        
        $newModelUri = trim($postData['modelUri']);
        $newBaseUri  = isset($postData['baseUri']) ? trim($postData['baseUri']) : null;
        
        switch ($postData['activeForm']) {
            case 'empty':
                $model = $this->_erfurt->getStore()->getNewModel($newModelUri, $newBaseUri, $postData['type']);
                $this->_erfurt->getAc()->setUserModelRight($model->getModelIri(), 'edit', 'grant');
                $this->_redirect($this->_config->urlBase . 'model/select/?m=' . urlencode($model->getModelIri()), array('code' => 302));
                return;
                break;
            case 'paste':
                $file = tempnam(sys_get_temp_dir(), 'ow');
                $temp = fopen($file, 'wb');
                fwrite($temp, $this->getParam('paste'));
                fclose($temp);
                $filetype = $postData['filetype-paste'];
                $locator = Erfurt_Syntax_RdfParser::LOCATOR_FILE;               
                break;
            case 'upload':
                $file = $_FILES['source']['tmp_name'];
                // setting permissions to read the tempfile for everybody 
                // (e.g. if db and webserver owned by different users)
                chmod($file,0644);
                $locator = Erfurt_Syntax_RdfParser::LOCATOR_FILE;
                $filetype = 'auto';
                // guess file mime type
                if ($postData['filetype-upload'] != 'auto') {
                    $filetype = $postData['filetype-upload'];
                } else {
                    // guess file type extension
                    $extension = strtolower(strrchr($_FILES['source']['name'], '.'));
                    if ($extension == '.rdf' or $extension == '.owl') {
                        $filetype = 'rdfxml';
                    } else if ($extension == '.n3') {
                        $filetype = 'ttl';
                    } else if ($extension == '.json') {
                        $filetype = 'rdfjson';
                    } else if ($extension == '.ttl') {
                        $filetype = 'ttl';
                    } else if ($extension == '.nt') {
                        $filetype = 'ttl';
                    }
                }
                break;
            case 'import':
                $file = $postData['location'] != '' ? $postData['location'] : $newModelUri;
                $filetype = 'auto';
                $locator = Erfurt_Syntax_RdfParser::LOCATOR_URL;
                break;
        }
        
        // create graph
        if ($createGraph) {
            $model = $this->_erfurt->getStore()->getNewModel($newModelUri, $newBaseUri, (isset($postData['type']) ? $postData['type'] : Erfurt_Store::MODEL_TYPE_OWL));
        }
        
        // import statements
        try {
            $this->_erfurt->getStore()->importRdf($newModelUri, $file, $filetype, $locator);
        } catch (Erfurt_Exception $e) {
            if ($createGraph) {
                // graph had been created: delete it
                $this->_erfurt->getStore()->deleteModel($newModelUri);
            }
            echo "exception".$e;
            // re-throw
            throw new OntoWiki_Controller_Exception("Graph '<$postData[modelUri]>' could not be imported: " . $e->getMessage());
        }
    }
    
    public function deleteAction()
    {
        $model = $this->_request->m;
        if ($this->_erfurt->isActionAllowed('ModelManagement')) {
            $event = new Erfurt_Event('onPreDeleteModel');
            $event->modelUri = $model;
            $event->trigger();
            
            try {
                $this->_erfurt->getStore()->deleteModel($model);
                
                if ((null !== $this->_owApp->selectedModel) && 
                        ($this->_owApp->selectedModel->getModelIri() === $model)) {
                    
                    unset($this->_owApp->selectedModel);
                }
            } catch (Exception $e) {
                $this->_owApp->appendMessage(
                    new OntoWiki_Message('Error deleting model: ' . $e->getMessage(), OntoWiki_Message::ERROR)
                );
            }
        } else {
            $this->_owApp->appendMessage(
                new OntoWiki_Message('Error deleting model: Not allowed.', OntoWiki_Message::ERROR)
            );
            
        }
        
        $this->view->clearModuleCache();
        $this->_redirect($this->config->urlBase, array('code' => 302));
    }
    
    /** 
     * Serializes a given model or (if supported) all models into a given format.
     */
    public function exportAction()
    {
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
        
        // Check whether a model uri is given
        if (isset($this->_request->m)) {
            $modelUri = $this->_request->m;
            
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
            echo $serializer->serializeGraphToString($modelUri);
            $response->sendResponse();
            exit;
        }
        // Else use all available models.
        else {
            // TODO Exporters need to support this feature...
            $response = $this->getResponse();
            $response->setRawHeader('HTTP/1.0 400 Bad Request');
            $response->sendResponse();
            throw new OntoWiki_Controller_Exception("No Graph URI given.");
            exit;
        }
    }
    
    public function infoAction()
    {
        OntoWiki_Navigation::disableNavigation();
        $this->_owApp->selectedResource = new OntoWiki_Resource($this->_request->getParam('m'), $this->_owApp->selectedModel);
        $store      = $this->_owApp->erfurt->getStore();
        $graph      = $this->_owApp->selectedModel;        
        $resource   = $this->_owApp->selectedResource;
        $navigation = $this->_owApp->navigation;
        $translate  = $this->_owApp->translate;
        
        $event = new Erfurt_Event('onPropertiesAction');
        $event->uri = (string)$resource;
        $event->graph = (string)$resource;
        $event->trigger();

        $windowTitle = $translate->_('Model info');
        $this->view->placeholder('main.window.title')->set($windowTitle);
        
        $title = $resource->getTitle($this->_owApp->getConfig()->languages->locale);
        $this->view->modelTitle = $title ? $title : OntoWiki_Utils::contractNamespace((string)$resource);
        $this->view->resourcesUrl = $this->_config->staticUrlBase . 'index.php/list/init/1';

        if (!empty($resource)) {
            $model = new OntoWiki_Model_Resource($store, $graph, (string)$resource);
            
            $values = $model->getValues();
            $predicates = $model->getPredicates();
            if (count($values) > 0) {
                // TODO: show imported infos as well?
                $this->view->values             = $values[(string)$graph];
                $this->view->predicates         = $predicates[(string)$graph];
                $this->view->resourceIri        = (string)$resource;
                $this->view->graphIri           = $graph->getModelIri();
                $this->view->graphBaseIri       = $graph->getBaseIri();
                $this->view->namespacePrefixes  = $graph->getNamespacePrefixes();

                if (!is_array($this->view->namespacePrefixes)) {
                        $this->view->namespacePrefixes  = array();
                }
                $this->view->namespacePrefixes['__default'] = $graph->getModelIri();

                $infoUris = $this->_config->descriptionHelper->properties;
                //echo (string)$resource;
                $query = 'ASK FROM <'.(string)$resource.'> WHERE {<'.(string)$resource.'> a <http://xmlns.com/foaf/0.1/PersonalProfileDocument>}';
                $q = Erfurt_Sparql_SimpleQuery::initWithString($query);
                if($this->_owApp->extensionManager->isExtensionActive('foafprofileviewer') && $store->sparqlAsk($q) === true){
                    $this->view->showFoafLink = true;
                    $this->view->foafLink = $this->_config->urlBase.'foafprofileviewer/display';
                } 

                $this->view->infoPredicates = array();
                foreach ($infoUris as $infoUri) {
                    if (array_key_exists($infoUri, $this->view->predicates)) {
                        $this->view->infoPredicates[$infoUri] = $this->view->predicates[$infoUri];
                        unset($this->view->predicates[$infoUri]);
                    }
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
            
            $this->_redirect($this->_config->urlBase . 'model/info/?m=' . urlencode($this->_request->m), array('code' => 302));
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
                        Erfurt_Syntax_RdfParser::LOCATOR_DATASTRING);
                }
                // check changed graph
                if ($this->_request->has('modified-graph')) {
                    $flag               = true;
                    $modifiedFormat     = $this->_request->getParam('modified-format', 'rdfjson');
                    $parser             = Erfurt_Syntax_RdfParser::rdfParserWithFormat($modifiedFormat);
                    $modifiedStatements = $parser->parse(
                        $this->getParam('modified-graph', false), 
                        Erfurt_Syntax_RdfParser::LOCATOR_DATASTRING);
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
                exit;
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
}


