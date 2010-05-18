<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2009, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version $Id:$
 */

require_once 'OntoWiki/Controller/Component.php';

/**
 * Component controller for the SIOC Editor.
 *
 * @copyright Copyright (c) 2009, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @category OntoWiki
 * @package Extensions
 * @subpackage Siocedit
 * @author Christoph Rieß <c.riess.dev@googlemail.com>
 */
class SioceditController extends OntoWiki_Controller_Component
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
            $this->_titleHelper = new OntoWiki_Model_TitleHelper($this->_model);
        }
        
        if ($this->_owApp->selectedResource instanceof Erfurt_Rdf_Resource) {
            $this->_resource = $this->_owApp->selectedResource;
            $this->_resourceUri = $this->_resource->getUri();
            $this->_titleHelper->addResource($this->_resourceUri);
        }
        
        // default title for now
        $this->view->placeholder('main.window.title')->append('SIOC : ' . $this->_resource->getTitle());
        
        // prepare namespaces
        $namespaces = $this->_model->getNamespaces();
        $graphBase  = $this->_model->getBaseUri();
        if (!array_key_exists($graphBase, $namespaces)) {
            $namespaces = array_merge($namespaces, array($graphBase => OntoWiki_Utils::DEFAULT_BASE));
        }
        $this->view->namespaces = $namespaces;
        
        //set resourceUri
        $this->view->resourceUri = $this->_resourceUri;
        
    }
    
    /**
     * Displays a sioc:Post
     */
    public function postAction()
    {
        $data = array();
        
        $indirectdata = array();
        
        $property = '';
        
        foreach ($this->_sparqlData($this->_resourceUri) as $row) {
            if ($row['p']['type'] === 'uri') {
                $property = $row['p']['value'];
                if (array_key_exists($property, $data) ) {
                
                } else {
                    $data[$property] = array();
                }
            } else {
                // do nothing
            }
            
            if ($row['o']['type'] === 'literal' ) {
                $data[$property][] = $row['o']['value'];
            } elseif ($row['o']['type'] === 'uri' ) {
                if (in_array($row['o']['value'], $data[$property]) ) {
                    // no dupes
                } else {
                    $data[$property][] = $row['o']['value']; 
                }

                if ( !empty($row['x']['value']) ) {
                    $indirectdata[$row['o']['value']][$row['x']['value']][] = $row['y']['value'];
                }
            } else {
                // do nothing
            }
        }
        
        
        /*
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
        }
        
        $model = new OntoWiki_Model_Resource($this->_erfurt->getStore(), $this->_model, $this->_resourceUri);
        $values = $model->getValues();
        $predicates = $model->getPredicates();
        
        // generate uri array
        $siocNS = $this->_privateConfig->ns;
        $siocProperties = array ();
        
        foreach ($this->_privateConfig->property->post->toArray() as $key => $uri) {
            $siocProperties[$siocNS . $uri] = $key;
        }
        
        foreach ($this->_privateConfig->property->other->toArray() as $key => $uri) {
            $otherProperties[$uri] = $key;
        }
        
        $siocdata   = array ();
        $otherdata  = array ();
        $remaindata = array ();
        $propertydata = array ();
        
        foreach ( current($values) as $key => $row) {
            if ( array_key_exists($key, $siocProperties) ) {
                $siocdata[$siocProperties[$key]] = $row;
                $propertydata[$siocProperties[$key]]['curi'] = $predicates[(string) $this->_model][$key]['curi'];
            } elseif ( array_key_exists($key, $otherProperties) ) {
                $otherdata[$otherProperties[$key]] = $row;
                $propertydata[$otherProperties[$key]]['curi'] = $predicates[(string) $this->_model][$key]['curi'];
            } else {
                $remaindata[$key] = $row;
            }
        }
        
        $this->view->siocdata       = $siocdata;
        $this->view->otherdata      = $otherdata;
        $this->view->remaindata     = $remaindata;
        $this->view->propertydata   = $propertydata;*/
        
    }
    
    /**
     * Displays a sioc:Site
     */
    public function siteAction()
    {
       
    }
    
    
    /**
     * Displays a sioc:User
     */
    public function userAction()
    {

    }
    
    /**
     * Displays a sioc:Usergroup
     */
    public function usergroupAction()
    {
       
    }
    
    
    /**
     * Displays a sioc:Forum
     */
    public function forumAction()
    {
       
    }
    
    private function _sparqlData($uri, $inverse = false)
    {
            require_once 'Erfurt/Sparql/SimpleQuery.php';
            $query = new Erfurt_Sparql_SimpleQuery();
            $query->addFrom( (string) $this->_owApp->selectedModel )
                  ->setProloguePart('SELECT *')
                  ->setWherePart('WHERE {<' . $uri . '> ?p ?o . OPTIONAL {?o ?x ?y.} }');
                  
            $results = $this->_erfurt->getStore()->sparqlQuery($query, array('result_format' => 'extended') );
            
            return $results['bindings'];
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
        }
        
        // set up model
        /*
        require_once $this->_componentRoot . 'models/FoafeditModel.php';
        $model = new FoafeditModel($this->_model, $this->_resource);
        $model->addProperties($this->_privateConfig->properties->toArray());
        
        // assign view values
        $this->view->assign($model->getPropertyValues());
        
        // set up view variables
        $this->view->uris         = $this->_privateConfig->properties->toArray();
        $this->view->resourceUri  = $this->_resource->getUri();
        $this->view->resourceName = $this->_titleHelper->getTitle($this->_resourceUri);*/
    }
}
