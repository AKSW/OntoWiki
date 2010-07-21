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
    const MAIN_TEMPLATE_NAME = 'main.phtml';

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
     *      *
     * @var string|null
     */
    private $_site = null;


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

            $siteConfig = array(
                'id'          => $this->_site,
                'basePath'    => sprintf('%s/sites/%s', $this->_componentRoot, $this->_site),
                'baseUri'     => sprintf('%s/sites/%s/', $this->_componentUrlBase, $this->_site),
                'context'     => $moduleContext,
                'privateConfig' => array()
            );

            // load the site config
            $configFileName = $this->_componentRoot.'/sites/'.$this->_site.'/config.ini';
            if(is_readable($configFileName)){
                $ini =  parse_ini_file($configFileName, true);
                if(is_array($ini)){
                    $siteConfig['privateConfig'] = $ini;
                }
            }

            // m is automatically used and selected
            if ((!isset($this->_request->m)) && (!$this->_owApp->selectedModel)) {
                if (!Zend_Uri::check($siteConfig['privateConfig']['model'])) {
                    throw new OntoWiki_Exception('No model pre-selected model, no parameter m (model) and no configured site model!');
                } else {
                    // setup the model
                    $this->_modelUri = $siteConfig['privateConfig']['model'];
                    $store = OntoWiki::getInstance()->erfurt->getStore();
                    $this->_model = $store->getModel($this->_modelUri);
                    OntoWiki::getInstance()->selectedModel = $this->_model;
                }
            } else {
                $this->_model = $this->_owApp->selectedModel;
                $this->_modelUri = (string) $this->_owApp->selectedModel;
            }

            // r is automatically used and selected, if not then we use the model uri as starting point
            if ((!isset($this->_request->r)) && (!$this->_owApp->selectedResource)) {
                OntoWiki::getInstance()->selectedResource = $this->_model->getResource($this->_modelUri);
            }
            $this->_resource = $this->_owApp->selectedResource;
            $this->_resourceUri = (string) $this->_owApp->selectedResource;
            $siteConfig['resourceUri']  = $this->_resourceUri;
            
            $navigation = $this->getSiteNavigationAsArray();
            $siteConfig['navi'] = $navigation;

            // mit assign kann man im Template direkt zugreifen ($this->basePath).
            $this->view->assign($siteConfig);
            $this->_response->setBody($this->view->render($mainTemplate));
        } else {
            $this->_response->setRawHeader('HTTP/1.0 404 Not Found');
            $this->_response->setBody($this->view->render('404.phtml'));
        }
    }

    
    protected function getSiteNavigationAsArray(){
        $store = OntoWiki::getInstance()->erfurt->getStore();
        $model = $this->_owApp->selectedModel;
        $query = 'PREFIX skos: <http://www.w3.org/2004/02/skos/core#>
            SELECT ?topconcept FROM <'.(string)$model.'> WHERE {
            ?cs a skos:ConceptScheme .
            ?topconcept skos:topConceptOf ?cs}';
        $res = $store->sparqlQuery($query);
        if(isset($res[0])){
            $topconcept = $res[0]['topconcept'];
        }
        $closure = $store->getTransitiveClosure((string)$model, 'http://www.w3.org/2004/02/skos/core#broader', $topconcept, true);
        $tree = array($topconcept=>array());

        function buildTree(&$tree, $closure){
            foreach($tree as $treeElement => &$childrenArr){
                foreach($closure as $closureElement){
                    if($closureElement['parent'] == $treeElement){
                        $childrenArr[$closureElement['node']] = array();
                    }
                }
                buildTree($childrenArr,$closure);
            }
        }
        buildTree($tree,$closure);
        $tree['root']=$topconcept;
        return $tree;
    }
}
