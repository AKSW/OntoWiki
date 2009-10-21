<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki controller base class for components.
 *
 * Provide component-specific path variables and Zend settings.
 *
 * @category OntoWiki
 * @package Controller
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
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
        
        $cm   = $this->_owApp->componentManager;
        $name = $this->_request->getControllerName();
        
        // set component specific template path
        if ($tp = $cm->getComponentTemplatePath($name)) {
            $this->view->addScriptPath($tp);
        }
        
        // set private config
        if ($pc = $cm->getComponentPrivateConfig($name)) {
            $this->_privateConfig = $pc;
        }
        
        // set component root dir
        $this->_componentRoot = $cm->getComponentPath() 
                              . $name 
                              . '/';
        
        // set component root url
        $this->_componentUrlBase = $this->_config->staticUrlBase 
                                 . $this->_config->extensions->components 
                                 . $name 
                                 . '/';
    }
}


