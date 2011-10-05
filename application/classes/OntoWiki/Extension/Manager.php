<?php

/**
 * will be used by OntoWiki to scan the extension folder and load the needed extension
 *
 * @author Jonas Brekle <jonas.brekle@gmail.com>
 */
class Ontowiki_Extension_Manager {
    const DEFAULT_CONFIG_FILE = 'default.ini';

    const COMPONENT_HELPER_SUFFIX = 'Helper';
    const COMPONENT_HELPER_FILE_SUFFIX = 'Helper.php';
    
    const COMPONENT_CLASS_POSTFIX = 'Controller';
    const COMPONENT_FILE_POSTFIX = 'Controller.php';

    const PLUGIN_CLASS_POSTFIX = 'Plugin';
    const PLUGIN_FILE_POSTFIX = 'Plugin.php';

    const WRAPPER_CLASS_POSTFIX = 'Wrapper';
    const WRAPPER_FILE_POSTFIX = 'Wrapper.php';


    /**
     * Array where component information is kept
     * @var array
     */
    protected $_extensionRegistry = array();

    /**
     * The path scanned for components
     * @var string
     */
    protected $_extensionPath = null;

    /**
     * The translation object.
     * @var Zend_Translate
     */
    protected $_translate = null;

    /**
     * Base URL for hyperlinks.
     * @var string
     */
    protected $_componentUrlBase = '';

    /**
     * Component helpers to be initialized
     * @var array
     */
    protected $_helpers = array();

    protected $_componentRegistry = array();

    protected $_moduleRegistry = null;


    protected $_wrapperRegistry = null;


    /**
     * Denotes whether component helpers have been called.
     * @var boolean
     */
    protected $_helpersCalled = false;

    /**
     * Prefix to distinguish component controller directories
     * from other controller directories.
     * @var string
     */
    private $_componentPrefix = '_component_';

    /**
     * Keys in the component configuration file storing path names that
     * should be normalized.
     * @var array
     */
    private $_pathKeys = array(
        'templates',
        'languages',
        'helpers'
    );

    /**
     * Name of the private section in the component config file
     * @var string
     */
    private $_privateSection = 'private';

    protected $_eventDispatcher = null;


    /**
     * Constructor
     */
    public function __construct($extensionPath)
    {
        if(!(substr($extensionPath, -1) == DIRECTORY_SEPARATOR)){
            $extensionPath .= DIRECTORY_SEPARATOR;
        }
        $this->_extensionPath = $extensionPath;
        
        OntoWiki_Module_Registry::reset();
        //OntoWiki_Module_Registry::getInstance()->resetInstance();
        $this->_moduleRegistry = OntoWiki_Module_Registry::getInstance();
        $this->_moduleRegistry->setExtensionPath($extensionPath);

        //TODO plugin & wrapper registry? needed anymore? why split it?
        Erfurt_Wrapper_Registry::reset();
        $this->_wrapperRegistry = Erfurt_Wrapper_Registry::getInstance();
        

        $this->_eventDispatcher = Erfurt_Event_Dispatcher::getInstance();

        // scan for extensions
        $this->_scanExtensionPath();

        // scan for translations
        $this->_scanTranslations();

        // register for event
        $dispatcher = Erfurt_Event_Dispatcher::getInstance();
        $dispatcher->register('onRouteShutdown', $this);
    }

    // ------------------------------------------------------------------------
    // --- Public Methods -----------------------------------------------------
    // ------------------------------------------------------------------------

    /**
     * Returns component.
     *
     * @return array
     */
    public function getExtensionConfig($name)
    {
        if(isset($this->_extensionRegistry[$name])){
            return $this->_extensionRegistry[$name];
        }
    }
    /**
     * Returns registered extensions.
     *
     * @return array
     */
    public function getExtensions()
    {
        return $this->_extensionRegistry;
    }

     /**
     * Returns registered components.
     *
     * @return array
     */
    public function getComponents()
    {
        return $this->_componentRegistry;
    }


    /**
     * Returns the helper associated with the component specified.
     *
     * @throws OntoWiki_Component_Exception if no component with the specified name has been registered or
     *         OntoWiki_Component_Exception if the specified component has no helper defined.
     * @return OntoWiki_Component_Helper
     */
    public function getComponentHelper($componentName)
    {
        if (!$this->isExtensionRegistered($componentName)) {
            throw new OntoWiki_Component_Exception('Component with key "'.$componentName.'" not registered');
        }

        if (!isset($this->_helpers[$componentName]['instance'])) {
            throw new OntoWiki_Component_Exception('no helper loaded for component "'.$componentName.'"');
        }

        return $this->_helpers[$componentName]['instance'];
    }

    /**
     * Returns the path the component manager used to search for components
     * because there is one component per extension, this path is equal to the extension path.
     *
     * @return string
     */
    public function getComponentPath()
    {
        return $this->getExtensionPath();
    }


    /**
     * Returns the path the extension manager used to search for extensions.
     *
     * @return string
     */
    public function getExtensionPath($name = null)
    {   
        if($name == null){
            return $this->_extensionPath;
        } else {
            return $this->_extensionPath . $name;
        }
    }

    /**
     * Returns the specified component's URL.
     *
     * @throws OntoWiki_Component_Exception if no component with the specified name has been registered
     * @return string
     */
    public function getComponentUrl($componentName)
    {
        if (!$this->isExtensionRegistered($componentName)) {
            throw new OntoWiki_Component_Exception("Component with key '$componentName' not registered");
        }

        return $this->_componentUrlBase . $componentName . '/';
    }

    /**
     * Checks whether a specific extension is registered.
     *
     *
     * @param  string $componentName
     * @return boolean
     */
    public function isExtensionRegistered($componentName)
    {
        return array_key_exists($componentName, $this->_extensionRegistry);
    }


    /**
     * Checks whether a specific component is registered.
     * 
     * @deprecated
     *
     * @param  string $componentName
     * @return boolean
     */
    public function isComponentRegistered($componentName)
    {
        return array_key_exists($componentName, $this->_componentRegistry);
    }

    /**
     * Checks whether a specific component is activated
     * in its configuration file.
     *
     * @param  string $componentName
     * @return boolean
     */
    public function isExtensionActive($componentName)
    {
        return array_key_exists($componentName, $this->_extensionRegistry) && $this->_extensionRegistry[$componentName]->enabled;
    }

    /**
     * Returns a prefix that can be used to distinguish components from
     * other extensions, i.e. modules or plugins.
     * 
     * @deprecated
     *
     * @return string
     */
    public function getComponentPrefix()
    {
        return $this->_componentPrefix;
    }

    /**
     * Returns the helper path for a given component.
     *
     * @param  string $componentName
     * @return string
     */
    public function getComponentHelperPath($componentName)
    {
        if (!$this->isExtensionRegistered($componentName)) {
            throw new OntoWiki_Component_Exception("Component with key '$componentName' not registered");
        }

        if (isset($this->_extensionRegistry[$componentName]->helpers)) {
            $path = $this->_extensionPath
                  . $componentName
                  . DIRECTORY_SEPARATOR
                  . $this->_extensionRegistry[$componentName]->helpers;

            return $path;
        }
    }

    /**
     * Returns the template path for a given component.
     *
     * @param  string $componentName
     * @return string
     */
    public function getComponentTemplatePath($componentName)
    {
        if (!$this->isExtensionRegistered($componentName)) {
            throw new OntoWiki_Component_Exception("Component with key '$componentName' not registered");
        }

        if (isset($this->_extensionRegistry[$componentName]->templates)) {
            $path = $this->_extensionPath
                  . $componentName
                  . DIRECTORY_SEPARATOR
                  . $this->_extensionRegistry[$componentName]->templates;

            return $path;
        }

        return $this->_extensionPath
                  . $componentName
                  . DIRECTORY_SEPARATOR;
    }

    /**
     * Returns the component's private configuration section
     *
     * @param  string $extensionName
     * @return array|null
     */
    public function getPrivateConfig($extensionName)
    {
        if (!$this->isExtensionRegistered($extensionName)) {
            throw new OntoWiki_Component_Exception("Component with key '$extensionName' not registered");
        }
        
        return $this->_extensionRegistry[$extensionName]->{$this->_privateSection};
    }

    /**
     * Sets the base URL for hyperlinks.
     *
     * @param string $urlBase
     */
    public function setComponentUrlBase($componentUrlBase)
    {
        $componentUrlBase = (string)$componentUrlBase;

        $this->_componentUrlBase = trim($componentUrlBase, '/\\') . '/';

        return $this;
    }

    /**
     * Sets the translation object to be used for string translation.
     *
     * @param Zend_Translate $translate
     */
    public function setTranslate(Zend_Translate $translate)
    {
        $this->_translate = $translate;

        // (re)scan for translations
        $this->_scanTranslations();

        return $this;
    }

    // ------------------------------------------------------------------------
    // --- Event Handlers -----------------------------------------------------
    // ------------------------------------------------------------------------

    public function onRouteShutdown(Erfurt_Event $event)
    {
        // init component helpers
        if (!$this->_helpersCalled) {
            foreach ($this->_helpers as $componentName => &$helper) {
                // only if helper has not been previously loaded
                if (!isset($helper['instance'])) {
                    $helperInstance = $this->_loadHelper($componentName);
                } else {
                    $helperInstance = $this->_helpers[$componentName]['instance'];
                }

                $helperInstance->init();
            }

            $this->_helpersCalled = true;
        }
    }

    // ------------------------------------------------------------------------
    // --- Private Methods ----------------------------------------------------
    // ------------------------------------------------------------------------

    protected function _loadHelper($componentName)
    {
        if (!isset($this->_helpers[$componentName])) {
            throw new OntoWiki_Component_Exception("No helper defined for component '$componentName'.");
        }

        $helperSpec = $this->_helpers[$componentName];

        // load helper class
        require_once $helperSpec['path'];
        // instantiate helper object
        $helperInstance = new $helperSpec['class']($this);

        // register helper events
        if (isset($helperSpec['events'])) {
            $dispatcher = Erfurt_Event_Dispatcher::getInstance();
            foreach ((array) $helperSpec['events'] as $currentEvent) {
                $dispatcher->register($currentEvent, $helperInstance);
            }
        }

        $this->_helpers[$componentName]['instance'] = $helperInstance;

        return $helperInstance;
    }

    /**
     * Scans the component path for conforming components and
     * announces their paths to appropriate components.
     */
    private function _scanExtensionPath()
    {
        $dir = new DirectoryIterator($this->_extensionPath);
        $view = OntoWiki::getInstance()->view;
        $reservedNames = array('themes','translations');
        foreach ($dir as $file) {
            if (!$file->isDot() && $file->isDir()) {
                //for all folders in <ow>/extensions/
                $extensionName = $file->getFileName();

                //we ignore these
                if(in_array($extensionName, $reservedNames)){
                    continue;
                }

                $currentExtensionPath = $file->getPathname() . DIRECTORY_SEPARATOR;

                // scan for component default ini
                if (is_readable($currentExtensionPath . self::DEFAULT_CONFIG_FILE)) {
                    $config = $this->_loadConfigs($extensionName);

                    //keep record
                    $this->_extensionRegistry[$extensionName] = $config;

                    //we have kept record but take no action on disabled extensions
                    if(!$config->enabled){
                        continue;
                    } 

                    //templates can be in the main extension folder
                    $view->addScriptPath($currentExtensionPath);
                    if(isset($config->templates)){
                        //or in a folder specified in the config
                        $view->addScriptPath($currentExtensionPath.$config->templates);
                    }

                    //check for component class (only one per extension for now)
                    if(file_exists($currentExtensionPath.ucfirst($extensionName).self::COMPONENT_FILE_POSTFIX)){
                        $this->_addComponent($extensionName, $currentExtensionPath, $config);
                    }

                    //check for modules and plugins (multiple possible)
                    $extensionDir = new DirectoryIterator($currentExtensionPath);
                    foreach($extensionDir as $extensionDirFile) {
                        $filename = $extensionDirFile->getFilename();
                        
                        if(substr($filename, -strlen(OntoWiki_Module_Registry::MODULE_FILE_POSTFIX)) === OntoWiki_Module_Registry::MODULE_FILE_POSTFIX) {
                            $this->_addModule($extensionName, $filename, $currentExtensionPath, $config);
                        } elseif(substr($filename, -strlen(self::PLUGIN_FILE_POSTFIX)) === self::PLUGIN_FILE_POSTFIX) {
                            $this->_addPlugin($filename, $currentExtensionPath, $config);
                        } elseif(substr($filename, -strlen(self::WRAPPER_FILE_POSTFIX)) === self::WRAPPER_FILE_POSTFIX) {
                            $this->_addWrapper($filename, $currentExtensionPath, $config);
                        }
                    }
                }
            }
        }
    }

    /**
     * adds a component to the internal registry.
     *
     * @param string $componentName the component's (folder) name
     * @param string $componentPath the path to the component folder
     * @param array $config the config of the components extension
     */
    private function _addComponent($componentName, $componentPath, $config)
    {
        // load helper
        $helperClassName = ucfirst($componentName) . self::COMPONENT_HELPER_SUFFIX;
        $helperPathName  = $componentPath . ucfirst($componentName) . self::COMPONENT_HELPER_FILE_SUFFIX;
        if (is_readable($helperPathName)) {
            $helperSpec = array(
                'path'   => $helperPathName,
                'class'  => $helperClassName
            );
            
            // store events
            if (isset($config->helperEvents)) {
                $helperSpec['events'] = $config->helperEvents->toArray();
            } else {
                $helperSpec['events'] = array();
            }
           
            if($config->enabled){
                $this->_helpers[$componentName] = $helperSpec;
                
                // event helpers need to be called early
                if (!empty($helperSpec['events'])) {
                    $this->_loadHelper($componentName);
                }

                //helpers without events will be instantiated onRouteShutdown
            } 
        } 

        $action = isset($config->action) ? $config->action : null;

        $position = isset($config->position) ? $config->position : null;

        if (isset($config->navigation) && (boolean)$config->navigation && $config->enabled) {
            // register with navigation
            OntoWiki_Navigation::register($componentName, array(
                'controller' => $componentName,
                'action'     => $action,
                'name'       => $config->name,
                'priority'   => $position,
                'active'     => false
            ));
        }

        $this->_componentRegistry[$componentName] = $config;
    }

    protected function _addModule($extensionName, $moduleFilename, $modulePath, $config = null)
    {
        //one extension can contain many modules - so they share a config file
        //but each module needs different settings
        //so we got this hack to change the config like it was when every module had its own config
        if(isset($config->modules)){
            $moduleName = strtolower(substr($moduleFilename, 0, strlen($moduleFilename)-strlen(OntoWiki_Module_Registry::MODULE_FILE_POSTFIX)));
            if(isset ($config->modules->{$moduleName})){
                $config = unserialize(serialize($config)) ; //dont touch the original config
                $config->merge($config->modules->{$moduleName});
            }
        } 

        if (isset($config->context) && is_string($config->context)) {
            $contexts = array($config->context);
        } else if (isset($config->contexts) && $config->contexts instanceof Zend_Config) {
            $contexts = $config->contexts->toArray();
        } else {
            $contexts = array(OntoWiki_Module_Registry::DEFAULT_CONTEXT);
        }

        // register for context(s)
        foreach ($contexts as $context) {
            $this->_moduleRegistry->register($extensionName, $moduleFilename, $context, $config);
        }
    }


    /**
     * adds a wrapper
     *
     * @param string $filename
     * @param string $wrapperPath
     */
    protected function _addWrapper($filename, $wrapperPath, $config)
    {
        $owApp = OntoWiki::getInstance();
    	$wrapperManager = $owApp->erfurt->getWrapperManager(false);
        $wrapperManager->addWrapperExternally(
                strtolower(substr($filename,0,strlen($filename) - strlen(self::WRAPPER_FILE_POSTFIX))), 
                $wrapperPath,
                isset($config->private) ? $config->private : new Zend_Config(array(), true)
        );
    }


    /**
     * Adds a plugin and registers it with the dispatcher.
     *
     * @param string $filename
     * @param string $pluginPath
     */
    private function _addPlugin($filename, $pluginPath, $config)
    {
        $owApp = OntoWiki::getInstance();
    	$pluginManager = $owApp->erfurt->getPluginManager(false);
        $pluginManager->addPluginExternally(strtolower(substr($filename,0,strlen($filename) - strlen(self::PLUGIN_FILE_POSTFIX))), $filename, $pluginPath, $config);
    }

    private function _loadConfigs($name){
        $path = $this->_extensionPath . $name . DIRECTORY_SEPARATOR;
        $config = new Zend_Config_Ini($path . self::DEFAULT_CONFIG_FILE, null, true);
        
        // overwrites default config with local config
        if (is_readable($this->_extensionPath . $name . '.ini')) {
            $local_ini = new Zend_Config_Ini($this->_extensionPath . $name . '.ini', null, true);
            $config->merge($local_ini);
        }

        //fix unset names
        if(!isset ($config->name)){
           $config->name = $name;
        }

        //this might be nessary
        if(is_string($config->enabled)){
            switch($config->enabled){
                case '1':
                case 'enabled':
                case 'true':
                case 'on':
                    $config->enabled = true;
                    break;
                default:
                    $config->enabled = false;
            }
        }

        // normalize paths
        foreach ($this->_pathKeys as $pathKey) {
            if (isset($config->{$pathKey})) {
                $config->{$pathKey} = rtrim($config->{$pathKey}, '/\\') . '/';
            }
        }

        //$config = $config->toArray();

        // save component's path
        $config->path = $path;

        return $config;
    }

    /**
      * Reads all available component translations and adds them to the translation object
      */
    private function _scanTranslations()
    {

        // check for valid translation object
        if (is_object($this->_translate) ) {
            foreach ($this->_extensionRegistry as $component => $settings) {
                // check if component owns translation
                if (
                    isset($settings->languages) &&
                    is_readable($settings->path . $settings->languages)
                ) {
                    // keep current locale
                    $locale = $this->_translate->getAdapter()->getLocale();

                    $this->_translate->addTranslation(
                        $settings->path . $settings->languages,
                        null,
                        array('scan' => Zend_Translate::LOCALE_FILENAME));

                    // reset current locale
                    $this->_translate->setLocale($locale);
                }
            }
        }
    }
}
?>
