<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_site
 * @copyright Copyright (c) 2009, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * The main controller class for the site component. This class
 * provides an action to render a given resource
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_site
 * @copyright  Copyright (c) 2009 {@link http://aksw.org aksw}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @subpackage component
 */
class SiteController extends OntoWiki_Controller_Component
{   
    /**
     * The main template filename
     */
    const MAIN_TEMPLATE_NAME = 'layout.phtml';
    
    /**
     * Name of per-site config file
     */
    const SITE_CONFIG_FILENAME = 'config.ini';

    /**
     * The model URI of the selected model or the uri which is given
     * by the m parameter
     *
     * @var string|null
     */
    private $_modelUri = null;

    /**
     * The selected model or the model which is given
     * by the m parameter
     */
    private $_model = null;

    /**
     * The resource URI of the requested resource or the uri which is given
     * by the r parameter
     *
     * @var string|null
     */
    private $_resourceUri = null;

    /**
     * The site id which is part of the request URI as well as the template structure
     *
     * @var string|null
     */
    private $_site = null;
    
    /**
     * Site config for the current site.
     * @var array
     */
    protected $_siteConfig = null;


	public function init()
    {
        parent::init();
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
    }

    /*
     * to allow multiple template sets, every action is mapped to a template directory
     */
    public function __call($method, $args)
    {
        $this->_site  = $this->_request->getActionName();
        $templatePath = $this->_owApp->componentManager->getComponentTemplatePath('site');
        $mainTemplate = sprintf('%s/%s', $this->_site, self::MAIN_TEMPLATE_NAME);
        
        if (is_readable($templatePath . $mainTemplate)) {
            $moduleContext = 'site.' . $this->_site;
            // $this->addModuleContext($moduleContext);
            
            $this->_loadModel();
            $this->_loadResource();
            
            $siteConfig = array(
                'id'          => $this->_site,
                'generator'   => 'OntoWiki ' . $this->_config->version->number,
                'pingbackUri' => $this->_owApp->getUrlBase() . '/pingback/ping',
                'wikiBaseUri' => $this->_owApp->getUrlBase(),
                'basePath'    => sprintf('%s/sites/%s', $this->_componentRoot, $this->_site),
                'baseUri'     => sprintf('%s/sites/%s/', $this->_componentUrlBase, $this->_site),
                'resourceUri' => $this->_resourceUri,
                'context'     => $moduleContext,
                'site'        => $this->_getSiteConfig(), 
                'navigation'  => $this->_getSiteNavigationAsArray(), 
                'description' => $this->_resource->getDescription(), 
                'descriptionHelper' => $this->_resource->getDescriptionHelper(),
                'store'       => OntoWiki::getInstance()->erfurt->getStore()
            );

            // mit assign kann man im Template direkt zugreifen ($this->basePath).
            $this->view->assign($siteConfig);
            $this->_response->setBody($this->view->render($mainTemplate));
        } else {
            $this->_response->setRawHeader('HTTP/1.0 404 Not Found');
            $this->_response->setBody($this->view->render('404.phtml'));
        }
    }
    
    protected function _loadModel()
    {
        $siteConfig = $this->_getSiteConfig();
        
        // m is automatically used and selected
        if ((!isset($this->_request->m)) && (!$this->_owApp->selectedModel)) {
            // TODO: what if no site model configured?
            if (!Erfurt_Uri::check($siteConfig['model'])) {
                throw new OntoWiki_Exception('No model pre-selected model, no parameter m (model) and no configured site model!');
            } else {
                // setup the model
                $this->_modelUri = $siteConfig['model'];
                $store = OntoWiki::getInstance()->erfurt->getStore();
                $this->_model = $store->getModel($this->_modelUri);
                OntoWiki::getInstance()->selectedModel = $this->_model;
            }
        } else {
            $this->_model = $this->_owApp->selectedModel;
            $this->_modelUri = (string) $this->_owApp->selectedModel;
        }
    }
    
    protected function _loadResource()
    {
        // r is automatically used and selected, if not then we use the model uri as starting point
        if ((!isset($this->_request->r)) && (!$this->_owApp->selectedResource)) {
            OntoWiki::getInstance()->selectedResource = new OntoWiki_Resource($this->_modelUri, $this->_model);
        }
        $this->_resource = $this->_owApp->selectedResource;
        $this->_resourceUri = (string) $this->_owApp->selectedResource;
    }
    
    protected function _getSiteConfig()
    {
        if (null === $this->_siteConfig) {
            $this->_siteConfig = array();
            
            // load the site config
            $configFilePath = sprintf('%s/sites/%s/%s', $this->_componentRoot, $this->_site, self::SITE_CONFIG_FILENAME);
            if (is_readable($configFilePath)) {
                if ($config = parse_ini_file($configFilePath, true)) {
                    $this->_siteConfig = $config;
                }
            }
        }
        
        return $this->_siteConfig;
    }
    
    protected function _getSiteNavigationAsArray()
    {
        $store = OntoWiki::getInstance()->erfurt->getStore();
        $model = $this->_owApp->selectedModel;
        
        $query = 'PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
            SELECT ?topConcept 
            FROM <' . (string)$model . '> 
            WHERE {
                ?cs a skos:ConceptScheme .
                ?topConcept skos:topConceptOf ?cs
            }';
        
        if ($result = $store->sparqlQuery($query)) {
            $first = current($result);
            $topConcept = $first['topConcept'];
            $closure = $store->getTransitiveClosure(
                (string)$model, 
                'http://www.w3.org/2004/02/skos/core#broader', 
                $topConcept, 
                true);
            
            $tree = array($topConcept => array());
            $this->_buildTree($tree, $closure);                
            
            return array_merge(array('root' => $topConcept), $tree);
        }
        
        return array();
    }
    
    protected function _buildTree(&$tree, $closure)
    {
        foreach ($tree as $treeElement => &$childrenArr) {
            foreach ($closure as $closureElement) {
                if (isset($closureElement['parent']) && $closureElement['parent'] == $treeElement) {
                    $childrenArr[$closureElement['node']] = array();
                }
            }

            $this->_buildTree($childrenArr, $closure);
        }
    }
}
