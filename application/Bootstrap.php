<?php 

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

 /**
  * OntoWiki bootstrap class.
  *
  * Provides on-demand loading of application resources. 
  *
  * @category OntoWiki
  * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
  * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
  * @author Norman Heino <norman.heino@gmail.com>
  */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{    
    /**
     * Loads the application config file
     *
     * @since 0.9.5
     */
    public function _initConfig()
    {
        // load default application configuration file
        try {
            $config = new Zend_Config_Ini(_OWROOT . 'application/config/default.ini', 'default', true);
        } catch (Zend_Config_Exception $e) {
            exit($e->getMessage());
        }
        
        // load user application configuration files
        try {
            $privateConfig = new Zend_Config_Ini(_OWROOT . 'config.ini', 'private', true);
            $config->merge($privateConfig);
        } catch (Zend_Config_Exception $e) {
            exit($e->getMessage());
        }
        
        // normalize path names
        $config->themes->path     = rtrim($config->themes->path, '/\\') . '/';
        $config->themes->default  = rtrim($config->themes->default, '/\\') . '/';
        $config->extensions->base = rtrim($config->extensions->base, '/\\') . '/';
        
        define('_EXTROOT', $config->extensions->base);
        $config->extensions->components = _EXTROOT . rtrim($config->extensions->components, '/\\') . '/';
        $config->extensions->modules    = _EXTROOT . rtrim($config->extensions->modules, '/\\') . '/';
        $config->extensions->plugins    = _EXTROOT . rtrim($config->extensions->plugins, '/\\') . '/';
        $config->extensions->wrapper    = _EXTROOT . rtrim($config->extensions->wrapper, '/\\') . '/';
        $config->extensions->legacy     = _EXTROOT . rtrim($config->extensions->legacy, '/\\') . '/';
        $config->languages->path        = _EXTROOT . rtrim($config->languages->path, '/\\') . '/';
        
        $config->libraries->path = rtrim($config->libraries->path, '/\\') . '/';
        $config->cache->path     = rtrim($config->cache->path, '/\\') . '/';
        
        // support absolute path
        $matches = array();
        if (!(preg_match('/^(\w:[\/|\\\\]|\/)/', $config->cache->path, $matches) === 1)) {
            $config->cache->path = _OWROOT . $config->cache->path;
        }
        
        // construct URL variables
        $config->host          = parse_url($this->urlBase, PHP_URL_HOST);
        $config->urlBase       = rtrim($this->urlBase, '/\\') . '/';
        $config->staticUrlBase = rtrim($this->staticUrlBase, '/\\') . '/';
        $config->themeUrlBase  = $this->staticUrlBase 
                               . $config->themes->path 
                               . $config->themes->default;
        
        // define constants for development/debugging
        if (isset($config->debug) and (boolean)$config->debug) {
           // display errors
           error_reporting(E_ALL | E_STRICT);
           ini_set('display_errors', 'On');
           // enable debugging options
           define('_OWDEBUG', 1);
           // log everything
           $config->log->level = 7;
        }
        
        return $config;
    }
    
    /**
     * Initializes the modified Zend_Conroller_Dispatcher to allow for
     * pluggable controllers
     *
     * @since 0.9.5
     */
    public function _initDispatcher()
    {
        $this->bootstrap('frontController');
        $frontController = $this->getResource('frontController');
        
        $dispatcher = new OntoWiki_Dispatcher();
        $frontController->setDispatcher($dispatcher);
        
        return $dispatcher;
    }
    
    /**
     * Initializes the Erfurt framework
     *
     * @since 0.9.5
     */
    public function _initErfurt()
    {
        // require Config
        $this->bootstrap('Config');
        $config = $this->getResource('Config');
        
        try {
            $erfurt = Erfurt_App::getInstance(false)->start($config);
            return $erfurt;
        } catch (Erfurt_Exception $ee) {
            exit('Error loading Erfurt framework: ' . $ee->getMessage());
        } catch (Exception $e) {
            exit('Unexpected error: ' . $e->getMessage());
        }
    }
    
    /**
     * Initializes the event dispatcher
     *
     * @since 0.9.5
     */
    public function _initEventDispatcher()
    {
        // load event dispatcher for Erfurt and OntoWiki events
        $eventDispatcher = Erfurt_Event_Dispatcher::getInstance();
        
        return $eventDispatcher;
    }
    
    /**
     * Loads logging capability
     *
     * @since 0.9.5
     */
    public function _initLog()
    {
        // require config
        $this->bootstrap('Config');
        $config = $this->getResource('Config');
        
        // support absolute path
        if (!(preg_match('/^(\w:[\/|\\\\]|\/)/', $config->log->path) === 1)) {
            $config->cache->path = _OWROOT . $config->cache->path;
        }
        
        // initialize logger
        if (is_writable($config->log->path) && ((boolean)$config->log->level !== false)) {
            $levelFilter = new Zend_Log_Filter_Priority((int)$config->log->level, '<=');
            
            $writer = new Zend_Log_Writer_Stream($config->log->path . 'ontowiki.log');
            $log = new Zend_Log($writer);
            $log->addFilter($levelFilter);
            
            return $log;
        }
        
        // fallback to NULL logger
        $writer = new Zend_Log_Writer_Null();
        $log    = new Zend_Log($writer);
        
        return $log;
    }
    
    /**
     * Initializes the navigation
     *
     * @since 0.9.5
     */
    public function _initNavigation()
    {
    }
    
    /**
     * Initializes the plug-in manager
     *
     * @since 0.9.5
     */
    public function _initPluginManager()
    {
        // require Erfurt
        $this->bootstrap('Erfurt');
        $erfurt = $this->getResource('Erfurt');
        
        // require Config
        $this->bootstrap('Config');
        $config = $this->getResource('Config');
        
        // instantiate plug-in manager and load plug-ins
        $pluginManager = $erfurt->getPluginManager(false);
        $pluginManager->addPluginPath(_OWROOT . $config->extensions->plugins);
    }
    
    public function _initPlugins()
    {
        // require front controller
        $this->bootstrap('frontController');
        $frontController = $this->getResource('frontController');
        
        $frontController->registerPlugin(new OntoWiki_Controller_Plugin_HttpAuth(), 1); // Needs to be done first!
        $frontController->registerPlugin(new OntoWiki_Controller_Plugin_SetupHelper(), 2);
    }
    
    /**
     * Initializes the request object
     *
     * @since 0.9.5
     */
    public function _initRequest()
    {
        $this->bootstrap('frontController');
        $frontController = $this->getResource('frontController');
        
        $request = new OntoWiki_Request();
        $frontController->setRequest($request);
        
        return $request;
    }
    
    /**
     * Initializes the router
     *
     * @since 0.9.5
     */
    public function _initRouter()
    {
        // require front controller
        $this->bootstrap('frontController');
        $frontController = $this->getResource('frontController');
        
        // require Config
        $this->bootstrap('Config');
        $config = $this->getResource('Config');
        
        $router = $frontController->getRouter();
        $router->addConfig($config->routes);
        
        return $router;
    }
    
    /**
     * Initializes the session and loads session variables
     *
     * @since 0.9.5
     */
    public function _initSession()
    {
        // require Config
        $this->bootstrap('Config');
        $config = $this->getResource('Config');
        
        // init session
        $sessionKey = 'ONTOWIKI' . (isset($config->session->identifier) ? $config->session->identifier : '');        
        $session    = new Zend_Session_Namespace($sessionKey);
        
        // define the session key as a constant for global reference
        define('_OWSESSION', $sessionKey);
        
        return $session;
    }
    
    /**
     * Initializes the toolbar
     *
     * @since 0.9.5
     */
    public function _initToolbar()
    {
        // init toolbar
        $toolbar = OntoWiki_Toolbar::getInstance();
        
        return $toolbar;
    }
    
    /**
     * Loads the translation
     *
     * @since 0.9.5
     */
    public function _initTranslation()
    {
        $this->bootstrap('Config');
        $config = $this->getResource('Config');
        
        // setup translation cache
        if ((boolean)$config->cache->translation and is_writable($config->cache->path)) {            
            $translationFile = _OWROOT 
                             . $config->languages->path 
                             . $config->languages->locale 
                             . DIRECTORY_SEPARATOR 
                             . 'core.csv';
            $frontendOptions = array(
                'lifetime'                => 3600, 
                'automatic_serialization' => true, 
                'master_file'             => $translationFile
            );
            $backendOptions = array(
                'cache_dir' => $config->cache->path
            );
            $translationCache = Zend_Cache::factory('File', 'File', $frontendOptions, $backendOptions);

            // set translation cache
            Zend_Translate::setCache($translationCache);
        }
        
        // set up translations
        $options = array(
            // scan locale from directories
            'scan'           => Zend_Translate::LOCALE_DIRECTORY, 
            // don't emit notices
            'disableNotices' => true
        );
        $translation = new Zend_Translate('csv', _OWROOT . $config->languages->path, null, $options);
        try {
            $translation->setLocale($config->languages->locale);
        } catch (Zend_Translate_Exception $e) {
            $config->languages->locale = 'en';
            $translation->setLocale('en');
        }
        
        return $translation;
    }
    
    /**
     * Authenticates the current user or Anonymous with Erfurt
     *
     * @since 0.9.5
     */
    public function _initUser()
    {
        // require Erfurt
        $this->bootstrap('Erfurt');
        $erfurt = $this->getResource('Erfurt');
        
        // get logged in user
        $auth = $erfurt->getAuth();
        if ($auth->hasIdentity()) {
            $this->user = $auth->getIdentity();
        }
    
        if (null === $this->user) {
            // authenticate anonymous user
            $erfurt->authenticate('Anonymous', '');
            $this->user = $auth->getIdentity();
        }
    }
    
    /**
     * Sets up the view environment
     *
     * @since 0.9.5
     */
    public function _initView()
    {
        // require Config
        $this->bootstrap('Config');
        $config = $this->getResource('Config');
        
        // standard template path
        $defaultTemplatePath = _OWROOT 
                             . 'application/views/templates';
        
        // path for theme template
        $themeTemplatePath   = _OWROOT 
                             . $config->themes->path 
                             . $config->themes->default 
                             . 'templates';
        
        // init view
        $view = new OntoWiki_View();
        $view->addScriptPath($defaultTemplatePath)  // default templates
             ->addScriptPath($themeTemplatePath)    // theme templates override default ones
             ->setEncoding($config->encoding)
             ->setHelperPath(_OWROOT . 'application/classes/OntoWiki/View/Helper', 'OntoWiki_View_Helper');
        
        // set Zend_View to emit notices in debug mode
        $view->strictVars(defined('_OWDEBUG'));
        
        // init view renderer action helper
        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer($view);
        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
        
        // initialize layout
        Zend_Layout::startMvc(array(
            // for layouts we use the default path
            'layoutPath' => $defaultTemplatePath . DIRECTORY_SEPARATOR . 'layouts'
        ));
        
        return $view;
    }
    
    /**
     * Initializes the wrapper manager
     *
     * @since 0.9.5
     */
    public function _initWrapperManager()
    {
        // require Erfurt
        $this->bootstrap('Erfurt');
        $erfurt = $this->getResource('Erfurt');
        
        // require Config
        $this->bootstrap('Config');
        $config = $this->getResource('Config');
        
        // initialize wrapper manager and load wrapper
        $wrapperManager = $erfurt->getWrapperManager(false);
        $wrapperManager->addWrapperPath(_OWROOT . $config->extensions->wrapper);
        
        return $wrapperManager;
    }
}



