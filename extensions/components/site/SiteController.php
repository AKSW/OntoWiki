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

        // m is automatically used and selected
        if ((!isset($this->_request->m)) && (!$this->_owApp->selectedModel)) {
            throw new OntoWiki_Exception('No model pre-selected model and missing parameter m (model)!');
            exit;
        } else {
            $this->_model = $this->_owApp->selectedModel;
            $this->_modelUri = (string) $this->_owApp->selectedModel;
        }

        // r is automatically used and selected
        if ((!isset($this->_request->r)) && (!$this->_owApp->selectedResource)) {
            throw new OntoWiki_Exception('No resource pre-selected model and missing parameter r (resource)!');
            exit;
        } else {
            $this->_resource = $this->_owApp->selectedResource;
            $this->_resourceUri = (string) $this->_owApp->selectedResource;
        }

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
                'resourceUri' => $this->_resourceUri,
                'context'     => $moduleContext,
                'privateConfig' => array()
            );
            $configFileName = $this->_componentRoot.'/sites/'.$this->_site.'/config.ini';
            if(is_readable($configFileName)){
                $ini =  parse_ini_file($configFileName, true);
                if(is_array($ini)){
                    $siteConfig['privateConfig'] = $ini;
                } 
            } 

            // mit assign kann man im Template direkt zugreifen ($this->basePath).
            $this->view->assign($siteConfig);
            $this->_response->setBody($this->view->render($mainTemplate));
        } else {
            $this->_response->setRawHeader('HTTP/1.0 404 Not Found');
            $this->_response->setBody($this->view->render('404.phtml'));
        }
    }
}
