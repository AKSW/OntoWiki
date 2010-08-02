<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki Module Manager
 *
 * Scans a module directory for suitable modules and registers them
 * for module contexts.
 *
 * A module consists of a folder with at least one php file that is named 
 * the same like the folder. That module class provided by that file
 * must be named like the file with the first letter in upper case and
 * the suffix 'Module'.
 *
 * @category OntoWiki
 * @package Module
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_Module_Manager
{
    /**
     * The name of the private section in the config file
     */
    const CONFIG_PRIVATE_SECTION = 'private';
    
    /**
     * The module config file name
     */
    const MODULE_CONFIG_FILE = 'module.ini';
    
    /**
     * The local module config file name.
     * Local config files locally override keys in module config file.
     */
    const MODULE_LOCAL_CONFIG_FILE = 'local.ini';
    
    /**
     * Postfix for module class names
     */
    const MODULE_CLASS_POSTFIX = 'Module';
    
    /**
     * Path that is scanned for modules
     * @var string
     */
    protected $_modulePath = null;
    
    /**
     * Array with registered modules
     * @var array
     */
    protected $_moduleRegistry = null;
    
    /**
     * Constructor
     *
     * @param string $modulePath The path that is scanned for modules
     */
    public function __construct($modulePath)
    {
        $this->_modulePath     = (string)$modulePath;
        $this->_moduleRegistry = OntoWiki_Module_Registry::getInstance();
        $this->_moduleRegistry->setModuleDir($modulePath);
        
        // scan for modules
        $this->_scanModulePath($this->_modulePath);
    }
    
    /**
     * Adds a valid module found to the module registry.
     *
     * @param string $moduleName
     * @param string $modulePath
     * @param Zend_Config_Ini $config
     */
    protected function _addModule($moduleName, $modulePath, $config = null)
    {
        if (array_key_exists('context', $config)) {
            $contexts = (array)$config['context'];
        } else if (array_key_exists('contexts', $config) and is_array($config['contexts'])) {
            $contexts = $config['contexts'];
        } else {
            $contexts = (array) OntoWiki_Module_Registry::DEFAULT_CONTEXT;
        }
        
        // register for context(s)
        foreach ($contexts as $context) {
            $this->_moduleRegistry->register($moduleName, $context, $config);
        }
    }
    
    /**
     * Scans a path for suitable modules
     *
     * @param string $path The path to be scanned
     */
    protected function _scanModulePath($path)
    {
        if (is_readable($path)) {
            $iterator = new DirectoryIterator($path);
            
            foreach ($iterator as $file) {
                if (!$file->isDot() && $file->isDir()) {
                    $moduleName = $file->getFileName();
                    $innerModulePath = $path
                                     . $moduleName
                                     . DIRECTORY_SEPARATOR;
                    
                    $moduleConfigFile = $innerModulePath . self::MODULE_CONFIG_FILE;
                    $moduleClassFile  = $innerModulePath . $moduleName . '.php';
                    
                    if (is_readable($moduleConfigFile)) {
                        $config = parse_ini_file($moduleConfigFile, true);

                        $moduleLocalConfigFile = $innerModulePath . self::MODULE_LOCAL_CONFIG_FILE;
                        if (is_readable($moduleLocalConfigFile)) {
                            $config = array_merge($config, parse_ini_file($moduleLocalConfigFile, true));
                        }
                        
                        if (!array_key_exists('enabled', $config) || !((boolean)$config['enabled'])) {
                            continue;
                        }
                        
                        $this->_addModule($moduleName, $innerModulePath, $config);
                    } 
                }
            }
        }
    }
}
