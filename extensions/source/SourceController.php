<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * @category   OntoWiki
 * @package    Extensions_Source
 */
class SourceController extends OntoWiki_Controller_Component
{
    public function editAction()
    {
        $store       = $this->_owApp->erfurt->getStore();
        $resource    = $this->_owApp->selectedResource;
        $translate   = $this->_owApp->translate;
        $allowSaving = false;
        $showList = false;
        
        // window title
        if (!$resource) {
            $this->_owApp->appendMessage(
                new OntoWiki_Message("No resource selected", OntoWiki_Message::WARNING)
            );
            $title = 'RDF Source';
        } else {
            $title = $resource->getTitle() 
                   ? $resource->getTitle() 
                   : OntoWiki_Utils::contractNamespace($resource->getIri());
        }
        $windowTitle = sprintf($translate->_('Source of Statements about %1$s') 
                     . ' ('. $translate->_('without imported statements') . ')', $title);
        $this->view->placeholder('main.window.title')->set($windowTitle);
	    
        // check for N3 capability
        if (array_key_exists('ttl', $store->getSupportedImportFormats())) {
            $allowSaving = true;
        } else {
            $this->_owApp->appendMessage(
                new OntoWiki_Message("Store adapter cannot handle TTL.", OntoWiki_Message::WARNING)
            );
        }

        if (!$this->_owApp->selectedModel || !$this->_owApp->selectedModel->isEditable()) {
            $allowSaving = false;
            $this->_owApp->appendMessage(
                new OntoWiki_Message("No model selected or no permissions to edit this model.", OntoWiki_Message::WARNING)
            );
        }

        if($this->_owApp->lastRoute === 'instances'){
            $allowSaving = false;
            $this->_owApp->appendMessage(
                new OntoWiki_Message("Modifications of a list currently not supported.", OntoWiki_Message::WARNING)
            );
            $showList = true;
        }

        // do not show edit stuff if model is not writeable
        if ( $this->_owApp->erfurt->getAc()->isModelAllowed('edit', $this->_owApp->selectedModel) ) {
            $allowSaving = true;
        } else {
            $allowSaving = false;
        }

        if ($allowSaving) {
            // toolbar
            $toolbar = $this->_owApp->toolbar;
            $toolbar->appendButton(OntoWiki_Toolbar::SUBMIT, array('name' => 'Save Source', 'id' => 'savesource'));
            $this->view->placeholder('main.window.toolbar')->set($toolbar);
        } else {
            $this->_owApp->appendMessage(
                new OntoWiki_Message("Saving has been disabled.", OntoWiki_Message::WARNING)
            );
        }

        // form
        $this->view->formActionUrl = $this->_config->urlBase . 'model/update';
        $this->view->formEncoding  = 'multipart/form-data';
        $this->view->formClass     = 'simple-input input-justify-left';
        $this->view->formMethod    = 'post';
        $this->view->formName      = 'savesource';
        $this->view->readonly      = $allowSaving ? '' : 'readonly="readonly"';
        $this->view->graphUri      = (string) $this->_owApp->selectedModel;

        // construct N3
        $exporter = Erfurt_Syntax_RdfSerializer::rdfSerializerWithFormat('ttl');
        if(!$showList){
            $source = $exporter->serializeResourceToString(
                (string) $this->_owApp->selectedResource,
                (string) $this->_owApp->selectedModel
            );
        } else {
            $listHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('List');
            $listName = "instances";
            if($listHelper->listExists($listName)){
                $list = $listHelper->getList($listName);
            } else {
                 $this->_owApp->appendMessage(
                    new OntoWiki_Message('something went wrong with the list of instances you want to rdf-view', OntoWiki_Message::ERROR)
            );
            }
            $source = $exporter->serializeQueryResultToString(
                 clone $list->getResourceQuery(),
                 (string) $this->_owApp->selectedModel
             );
        }
	        
        $this->view->source = $source;
        
        $url = new OntoWiki_Url(array('route' => 'properties'), array());
        $url->setParam('r', (string) $resource, true);
        $this->view->redirectUri = urlencode((string) $url);
    }
    
    /*
    public function saveAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        
        $store       = $this->_owApp->erfurt->getStore();
        $source      = $this->getParam('source');
        $modelUri    = (string) $this->_owApp->selectedModel;
        $resourceUri = (string) $this->_owApp->selectedResource;
        
        if ($this->_owApp->selectedModel->isEditable()) {
            // delete all statements about resource
            $store->deleteMatchingStatements($modelUri, $resourceUri, null, null);
            
            // save new statements
            $store->importRdf($modelUri, $source, 'turtle', Erfurt_Syntax_RdfParser::LOCATOR_DATASTRING);
        } else {
            $this->_owApp->appendMessage(
                new OntoWiki_Message("No edit privileges on graph <${modelUri}>.", OntoWiki_Message::ERROR)
            );
        }
        
        // $url = new OntoWiki_Url(array('controller' => 'source', 'action' => 'edit'), array());
        $url = new OntoWiki_Url(array('route' => 'properties'), array());
        $url->setParam('r', $resourceUri, true);
        $this->_redirect((string) $url);
    }
    */
}
