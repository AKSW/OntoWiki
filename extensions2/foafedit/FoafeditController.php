<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once 'OntoWiki/Controller/Component.php';

/**
 * Component controller for the FOAF Editor.
 *
 * @category OntoWiki
 * @package Extensions
 * @subpackage Foafedit
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Norman Heino <norman.heino@gmail.com>
 */
class FoafeditController extends OntoWiki_Controller_Component
{
    /**
     * Subfolder for resources
     * @var string
     */
    const RESOURCES_DIR = 'resources/';
    
    /**
     * The currently selected model object
     * @var
     */
    protected $_model = null;
    
    /**
     * The currently selected resource object
     * @var
     */
    protected $_resource = null;
    
    /**
     * The currently selected resource's URI
     * @var
     */
    protected $_resourceUri = null;
    
    /**
     * TitleHelper object
     * @var
     */
    protected $_titleHelper = null;
    
    /**
     * Custom setup
     */
    public function init()
    {
        parent::init();
        
        $stylesheet = $this->_componentUrlBase 
                    . self::RESOURCES_DIR 
                    . 'styles.css';
        $this->view->headLink()->appendStylesheet($stylesheet);
        
        if ($this->_owApp->selectedModel instanceof Erfurt_Rdf_Model) {
            $this->_model = $this->_owApp->selectedModel;
            
            require_once 'OntoWiki/Model/TitleHelper.php';
            $this->_titleHelper = new OntoWiki_Model_TitleHelper($this->_model);
        }
        
        if ($this->_owApp->selectedResource instanceof Erfurt_Rdf_Resource) {
            $this->_resource = $this->_owApp->selectedResource;
            $this->_resourceUri = $this->_resource->getUri();
            $this->_titleHelper->addResource($this->_resourceUri);
        }
    }
    
    /**
     * Dislays a foaf:Person instance.
     */
    public function personAction()
    {
        $this->view->placeholder('main.window.title')->append($this->_resource->getTitle());
        // $this->addModuleContext('main.window.properties');
        
        // set up toolbar buttons
        if ($this->_model->isEditable()) {
            // TODO: check acl
            $toolbar = $this->_owApp->toolbar;
            $toolbar->appendButton(OntoWiki_Toolbar::EDIT, array('name' => 'Edit Properties'));
            $params = array(
                'name' => 'Delete Resource',
                'url'  => $this->_config->urlBase . 'resource/delete/?r=' . urlencode((string) $this->_resourceUri)
            );
            $toolbar->appendButton(OntoWiki_Toolbar::SEPARATOR)
                    ->appendButton(OntoWiki_Toolbar::DELETE, $params);

            $this->view->placeholder('main.window.toolbar')->set($toolbar);
            
            // prepare namespaces
            $namespaces = $this->_model->getNamespaces();
            $graphBase  = $this->_model->getBaseUri();
            if (!array_key_exists($graphBase, $namespaces)) {
                $namespaces = array_merge($namespaces, array($graphBase => OntoWiki_Utils::DEFAULT_BASE));
            }
            $this->view->namespaces = $namespaces;
            
            // add update vocabulary graph definitions
            $this->view->placeholder('update')->append(array(
                'sourceGraph'    => $this->_model->getModelUri(), 
                'queryEndpoint'  => $this->_config->urlBase . 'sparql/', 
                'updateEndpoint' => $this->_config->urlBase . 'update/'
            ));
        }
        
        // set up model
        require_once $this->_componentRoot . 'models/FoafeditModel.php';
        $model = new FoafeditModel($this->_model, $this->_resource);
        $model->addProperties($this->_privateConfig->properties->toArray());
        
        // assign view values
        $this->view->assign($model->getPropertyValues());
        
        // set up view variables
        $this->view->uris         = $this->_privateConfig->properties->toArray();
        $this->view->resourceUri  = $this->_resource->getUri();
        $this->view->resourceName = $this->_titleHelper->getTitle($this->_resourceUri);
    }
}
