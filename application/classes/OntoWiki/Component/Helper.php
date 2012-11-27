<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * Component helper base class.
 *
 * Component helpers are small objects that are instantiated when a component 
 * is found by the component manager. Component helpers allow for certain tasks 
 * to be executed on every request (even those the component doesn't handle). 
 * Example usages are registering a menu entry or a navigation tab.
 *
 * @category OntoWiki
 * @package OntoWiki_Classes_Component
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_Component_Helper
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
     * OntoWiki Application config
     * @var Zend_Config
     */
    protected $_config;

    /**
     * The component private config
     * @var Zend_Config
     */
    protected $_privateConfig;

    /**
     * OntoWiki Application
     * @var OntoWiki
     */
    protected $_owApp;

    /**
     * Constructor
     *
     * @param OntoWiki_Component_Manager $componentManager
     */
    public function __construct($config)
    {
        $this->_owApp            = OntoWiki::getInstance();
        $this->_config           = $this->_owApp->getConfig();
        $this->_privateConfig    = isset($config->private) ? $config->private : new Zend_Config(array(), true);
    }

    /**
     * Overwritten in subclasses
     */
    public function init()
    {
    }

    public function getPrivateConfig()
    {
        return $this->_privateConfig;
    }

    public function getComponentRoot()
    {
        $componentName = strtolower(str_replace('Helper', '', get_class($this)));

        // set component root dir
        $this->_componentRoot = $this->_owApp->extensionManager->getComponentPath() . $componentName . '/';

        return $this->_componentRoot;
    }

    public function getComponentUrlBase()
    {
        $componentName = strtolower(str_replace('Helper', '', get_class($this)));

        // set component root url
        $this->_componentUrlBase = $this->_config->staticUrlBase.$this->_config->extensions->base.$componentName . '/';

        return $this->_componentUrlBase;
    }
}
