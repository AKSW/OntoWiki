<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki module registry class.
 *
 * Serves as a central registry for modules.
 *
 * @category OntoWiki
 * @package Module
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_Module_Registry
{
    /** 
     * Default module context
     */
    const DEFAULT_CONTEXT = 'Default';
    
    /** 
     * Module is open
     */
    const MODULE_STATE_OPEN = 1;
    
    /**
     * Module is minimized
     */
    const MODULE_STATE_MINIMIZED = 2;
    
    /**
     * Module is hidden
     */
    const MODULE_STATE_HIDDEN = 3;
    
    /**
     * Array of modules
     * @var array
     */
    protected $_modules = array();
    
    /**
     * Module path
     * @var string
     */
    protected $_moduleDir = '';
    
    /** 
     * Array of module states
     * @var array 
     */
    protected $_moduleStates = array();
    
    /** 
     * Array of module contexts (keys) and modules therein
     * @var array */
    protected $_moduleOrder = array();
    
    /** 
     * Singleton instance
     * @var OntoWiki_Module_Registry 
     */
    private static $_instance = null;
    
    /**
     * Singleton instance
     *
     * @return OntoWiki_Module_Registry
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    /**
     * Resets the instance to its initial state. Mainly used for
     * testing purposes.
     */
    public function resetInstance()
    {
        $this->_modules      = array();
        $this->_moduleStates = array();
        $this->_moduleOrder  = array();
    }
    
    /**
     * Sets the path where modules are to be found
     *
     * @since 0.9.5
     * @return OntoWiki_Module_Registry
     */
    public function setModuleDir($moduleDir)
    {
        $moduleDir = (string)$moduleDir;
        
        if (is_readable($moduleDir)) {
            $this->_moduleDir = $moduleDir;
        }
        
        return $this;
    }
    
    /**
     * Registers modulewith name $moduleName in namespace $namspace.
     *
     * @param string $moduleName
     * @param string $namespace
     * @param boolean $enabled
     * @return OntoWiki_Module_Registry
     */
    public function register($moduleName, $context = self::DEFAULT_CONTEXT, array $options = array())
    {
        // create module context if necessary
        if (!array_key_exists($context, $this->_moduleOrder)) {
            $this->_moduleOrder[$context] = array();
        }
        
        if (!array_key_exists($moduleName, $this->_modules)) {
            // merge defaults
            $options = array_merge(array(
                'id'      => $moduleName,
                'classes' => '', 
                'name'    => $moduleName, 
                'enabled' => true 
            ), $options);

            // set css classes according to module state
            switch ($this->_moduleStates->$options['id']) {
                case self::MODULE_STATE_OPEN:
                    break;
                case self::MODULE_STATE_MINIMIZED:
                    $options['classes'] .= ' is-minimized';
                    break;
                case self::MODULE_STATE_HIDDEN:
                    $options['classes'] .= ' is-disabled';
                    break;
            }
            
            // register module
            $this->_modules[$moduleName] = $options;
        }
        
        // set module order and context 
        if (array_key_exists('priority', $options)) {
            $position = $this->_getModulePosition($context, $options['priority']);
            $this->_moduleOrder[$context][$position] = $moduleName;
        } else {
            $this->_moduleOrder[$context][] = $moduleName;
        }
        
        return $this;
    }
    
    /**
     * Returns whether the module with $moduleName has been registered
     * under namespace $namespace.
     *
     * @param string $moduleName
     * @param string $namespace
     * @return boolean
     */
    public function isModuleEnabled($moduleName)
    {        
        $moduleEnabled = false;
        if (array_key_exists($moduleName, $this->_modules)) {
            $moduleEnabled = $this->_modules[$moduleName]['enabled'] == true;
        }
        
        return $moduleEnabled;
    }
    
    /**
     * Sets the module's state to disabled. If the module doesn't exists, it is
     * registered as disabled.
     *
     * @param string $moduleName
     * @param string $namespace
     * @return OntoWiki_Module_Registry
     */
    public function disableModule($moduleName, $context = self::DEFAULT_CONTEXT)
    {
        if ($this->isModuleEnabled($moduleName)) {
            $this->_modules[$moduleName]['enabled'] = false;
        } else {
            $this->register($moduleName, $context, array('enabled' => false));
        }
        
        return $this;
    }
    
    /**
     * Returns an instance of the module denoted by $moduleName, if registered.
     *
     * @param string $moduleName
     * @return OntoWiki_Module
     * @throws OntoWiki_Module_Exception if a module with the has not been registered.
     */
    public function getModule($moduleName, $context = null, $options)
    {
        $moduleFile = $this->_moduleDir
                    . $moduleName 
                    . DIRECTORY_SEPARATOR 
                    . $moduleName
                    . '.php';
        
        if (!is_readable($moduleFile)) {
            throw new OntoWiki_Module_Exception("Module '$moduleName' could not be loaded from path '$moduleFile'.");
        }
        
        // instantiate module
        require_once $moduleFile;
        $moduleClass = ucfirst($moduleName) 
                     . OntoWiki_Module_Manager::MODULE_CLASS_POSTFIX;
        $module = new $moduleClass($moduleName, $context, $options);
        
        // inject module config
        foreach ((array)$this->getModuleConfig($moduleName) as $key => $value) {
            $module->$key = $value;
        }
        
        return $module;
    }
    
    /**
     * Returns the config for the module denoted by $moduleName.
     *
     * @param string $moduleName The module's name
     * @return array
     */
    public function getModuleConfig($moduleName)
    {
        $moduleName = (string)$moduleName;
        
        if (array_key_exists($moduleName, $this->_modules)) {
            return $this->_modules[$moduleName];
        }
    }
    
    /**
     * Returns all module names that are registered and enabled under 
     * namespace $namespace.
     *
     * @param string $namespace
     * @return array|null
     */
    public function getModulesForContext($context = self::DEFAULT_CONTEXT)
    {        
        $modules = array();
        if (array_key_exists($context, $this->_moduleOrder)) {
            ksort($this->_moduleOrder[$context]);
            
            foreach ($this->_moduleOrder[$context] as $moduleName) {
                if (array_key_exists($moduleName, $this->_modules)) {
                    if ((boolean)$this->_modules[$moduleName]['enabled'] === true) {
                        $modules[$moduleName] = $this->_modules[$moduleName];
                    }
                }
            }
        }
        
        return $modules;
    }
    
    /**
     * Returns the first empty position greater than $priority 
     * in the internal module array.
     *
     * @param string $context The module context
     * @param int $priority The module's priority request
     */
    protected function _getModulePosition($context, $priority)
    {
        while (array_key_exists($priority, $this->_moduleOrder[$context])) {
            $priority++;
        }
        
        return $priority;
    }
    
    /**
     * Constructor
     */
    private function __construct()
    {
        $this->_moduleStates = new Zend_Session_Namespace('Module_Registry');
        
        // TODO: module order per namespace?
        if (isset($this->_moduleStates->moduleOrder)) {
            $this->_moduleOrder = $this->_moduleStates->moduleOrder;
        }
    }
}
