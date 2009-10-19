<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @category   OntoWiki
 * @package    OntoWiki
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version   $Id: Plugin.php 4095 2009-08-19 23:00:19Z christian.wuerker $
 */

require_once 'Erfurt/Plugin.php';

/**
 * OntoWiki plugin base class.
 *
 * Serves as a base class for all OntoWiki plug-ins. An OntoWiki plug-in is a 
 * class or object that meets the following requirements:
 * - it consists of a folder residing under OntoWiki's plug-in dir
 * - that folder contains a .php file with the same name
 * - that php file defines a class that is named like the file with the 
 *   first letter in upper case and the suffix 'Plugin'
 * - the folder as well contains a 'plugin.ini' config file
 *
 * @category   OntoWiki
 * @package    OntoWiki
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author    Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_Plugin extends Erfurt_Plugin
{
    /**
     * The plug-in's view for rendering templates
     * @var OntoWiki_View
     */
    public $view = null;
    
    /**
     * The plug-in URL base
     * @var string
     */
    protected $_pluginUrlBase = null;
    
    /**
     * Constructor
     */
    public function __construct($root, $config = null)
    {
        // init view
        if (null === $this->view) {
            require_once 'Zend/Controller/Action/HelperBroker.php';
            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
            if (null === $viewRenderer->view) {
                $viewRenderer->initView();
            }
            $this->view = clone $viewRenderer->view;
            $this->view->clearVars();
        }
        
        $systemConfig = OntoWiki_Application::getInstance()->config;
        $this->_pluginUrlBase = $systemConfig->staticUrlBase
                              . str_replace(_OWROOT, '', $root);
        
        parent::__construct($root, $config);
    }
}


