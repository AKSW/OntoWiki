<?php

require_once 'OntoWiki/Controller/Component.php';

/**
 * Component controller for the Artist Editor.
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_foafedit
 * @author kurtjx <kurtjx@gmail.com>, Norman Heino <norman.heino@gmail.com>
 */
class ArtisteditController extends OntoWiki_Controller_Component
{
    private $model;

    public function init()
    {
        parent::init();

        // m is automatically used and selected
        if ((!isset($this->_request->m)) && (!$this->_owApp->selectedModel)) {
            require_once 'OntoWiki/Exception.php';
            throw new OntoWiki_Exception('No model pre-selected model and missing parameter m (model)!');
            exit;
        } else {
            $this->model = $this->_owApp->selectedModel;
            // perhaps setting $this->view stuff should be in artistAction but works here
            $this->view->resourceUri = (string) $this->_owApp->selectedResource;
        }
    }

    public function artistAction()
    {
        $this->view->placeholder('main.window.title')->append('Music Artist');

        $this->addModuleContext('main.window.properties');

        if (!isset($this->_request->r)) {
            require_once 'OntoWiki/Exception.php';
            throw new OntoWiki_Exception("Missing parameter 'r'.");
            exit;
        }

        require_once 'OntoWiki/Model/Resource.php';
        $resource = new OntoWiki_Model_Resource($this->model->getStore(), $this->model, $this->_request->r);
        $this->view->values = $resource->getValues();
        $predicates = $resource->getPredicates();
        $this->view->predicates = $predicates;

        require_once 'OntoWiki/Model/TitleHelper.php';
        $titleHelper = new OntoWiki_Model_TitleHelper($this->model);

		// add graphs
        $graphs = array_keys($predicates);
        $titleHelper->addResources($graphs);

        $graphInfo = array();
        foreach ($graphs as $g) {
            $graphInfo[$g] = $titleHelper->getTitle($g, $this->_config->languages->locale);
        }
        $this->view->graphs = $graphInfo;

        // prepare namespaces
        $namespaces = $this->model->getNamespaces();
        $graphBase  = $this->model->getBaseUri();
        if (!array_key_exists($graphBase, $namespaces)) {
            $namespaces = array_merge($namespaces, array($graphBase => OntoWiki_Utils::DEFAULT_BASE));
        }
        $this->view->namespaces = $namespaces;

        $this->view->graphUri      = $this->model->getModelIri();
        $this->view->graphBaseUri  = $this->model->getBaseIri();

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

        // show only if not forwarded
        if ($this->_request->getParam('action') == 'artist' && $this->model->isEditable()) {
            // TODO: check acl
            $toolbar = $this->_owApp->toolbar;
            $toolbar->appendButton(OntoWiki_Toolbar::EDIT, array('name' => 'Edit Properties'));
                    // ->appendButton(OntoWiki_Toolbar::EDITADD, array('name' => 'Add Property', 'class' => 'property-add'));
            $params = array(
                'name' => 'Delete Resource',
                'url'  => $this->_config->urlBase . 'resource/delete/?r=' . urlencode((string) $this->view->resourceUri)
            );
            $toolbar->appendButton(OntoWiki_Toolbar::SEPARATOR)
                    ->appendButton(OntoWiki_Toolbar::DELETE, $params);

            $this->view->placeholder('main.window.toolbar')->set($toolbar);
        }
    }
}
