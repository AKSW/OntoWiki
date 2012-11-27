<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki controller base class for components.
 *
 * Provide component-specific path variables and Zend settings.
 *
 * @category OntoWiki
 * @package OntoWiki_Classes_Controller
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_Controller_Component extends OntoWiki_Controller_Base
{    
    /** 
     * The component's file system root directory
     * @var string 
     */
    protected $_componentRoot = null;
    
    /** 
     * The components URL base
     * @var string 
     */
    protected $_componentUrlBase = null;
    
    /**
     * The component helper object
     * @var OntoWiki_Component_Helper
     */
    protected $_componentHelper = null;
    
    /** 
     * The component private config
     * @var array 
     */
    protected $_privateConfig = null;
    
    /**
     * Constructor
     */
    public function init()
    {
        parent::init();
        
        $cm   = $this->_owApp->extensionManager;
        $name = $this->_request->getControllerName();
        
        // set component specific template path
        if ($tp = $cm->getComponentTemplatePath($name)) {
            $this->view->addScriptPath($tp); 
        } 
        
        // set component specific helper path
        if ($hp = $cm->getComponentHelperPath($name)) {
            $this->view->addHelperPath($hp, ucfirst($name) . '_View_Helper_');
        }
        
        // set private config
        if ($pc = $cm->getPrivateConfig($name)) {
            $this->_privateConfig = $pc;
        }
        
        // set component root dir
        $this->_componentRoot = $cm->getExtensionPath()
                              . $name 
                              . '/';
        
        // set component root url
        $this->_componentUrlBase = $this->_config->staticUrlBase 
                                 . $this->_config->extensions->base
                                 . $name 
                                 . '/';
    }
    
    /**
     * Returns the helper object associated with the component.
     *
     * @throws OntoWiki_Component_Exception if the component has no helper defined.
     * @return OntoWiki_Component_Helper
     */
    public function getComponentHelper()
    {
        if (null === $this->_componentHelper) {
            $name = $this->_request->getControllerName();
            $extensionManager = $this->_owApp->extensionManager;
            $this->_componentHelper = $extensionManager->getComponentHelper($name);
        }
        
        return $this->_componentHelper;
    }
}


