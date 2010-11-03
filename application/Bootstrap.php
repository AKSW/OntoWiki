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
     * Initializes the the component manager which in turn scans
     * for components and registers them.
     *
     * @since 0.9.5
     */
    public function _initComponentManager()
    {
        // require Front controller
        $this->bootstrap('frontController');
        $frontController = $this->getResource('frontController');
        
        // require Config
        $this->bootstrap('Config');
        $config = $this->getResource('Config');

        //NOTICE: i swtiched loading of erfurt and session
        //because serialized erfurt objects in the session need constants defined in erfurt
        //is this ok?

        // require Erfurt
        $this->bootstrap('Erfurt');
        // $erfurt = $this->getResource('Erfurt');
        
        // require Session
        $this->bootstrap('Session');
        
        // require Dispatcher
        $this->bootstrap('Dispatcher');
        $dispatcher = $this->getResource('Dispatcher');
        
        // require OntoWiki
        $this->bootstrap('OntoWiki');
        $ontoWiki = $this->getResource('OntoWiki');
        
        // require Translate
        $this->bootstrap('Translate');
        $translate = $this->getResource('Translate');
        
        // require View
        $this->bootstrap('View');
        $view = $this->getResource('View');
        
        // make sure router is bootstrapped
        $this->bootstrap('Router');
        
        // set view
        $ontoWiki->view = $view;
        
        // initialize components
        $componentsPath = ONTOWIKI_ROOT
                        . $config->extensions->components;
                        
        $componentsBase = $config->staticUrlBase
                        . $config->extensions->components;
        
        $componentManager = new OntoWiki_Component_Manager($componentsPath);
        $componentManager->setTranslate($translate)
                         ->setComponentUrlBase($componentsBase);
        
        // register component controller directories
        foreach ($componentManager->getComponents() as $componentName => $componentConfig) {
            $frontController->addControllerDirectory($componentConfig['path'], '_component_' . $componentName);
        }
        
        // make component manager available to dispatcher
        $dispatcher = $frontController->getDispatcher();
        $dispatcher->setComponentManager($componentManager);
        
        // keep component manager in OntoWiki
        $ontoWiki->componentManager = $componentManager;

        // actionhelper
        Zend_Controller_Action_HelperBroker::addPrefix('OntoWiki_Controller_ActionHelper_');
        Zend_Controller_Action_HelperBroker::addHelper( new OntoWiki_Controller_ActionHelper_List());
    }
    
    /**
     * Loads the application config file
     *
     * @since 0.9.5
     */
    public function _initConfig()
    {
        // load default application configuration file
        try {
            $config = new Zend_Config_Ini(APPLICATION_PATH . 'config/default.ini', 'default', true);
        } catch (Zend_Config_Exception $e) {
            exit($e->getMessage());
        }
        
        // load user application configuration files
        try {
            $privateConfig = new Zend_Config_Ini(ONTOWIKI_ROOT . 'config.ini', 'private', true);
            $config->merge($privateConfig);
        } catch (Zend_Config_Exception $e) {
            exit($e->getMessage());
        }
        
        // normalize path names
        $config->themes->path     = rtrim($config->themes->path, '/\\') . '/';
        $config->themes->default  = rtrim($config->themes->default, '/\\') . '/';
        $config->extensions->base = rtrim($config->extensions->base, '/\\') . '/';
        
        define('EXTENSION_PATH', $config->extensions->base);
        $config->extensions->components = EXTENSION_PATH . rtrim($config->extensions->components, '/\\') . '/';
        $config->extensions->modules    = EXTENSION_PATH . rtrim($config->extensions->modules, '/\\') . '/';
        $config->extensions->plugins    = EXTENSION_PATH . rtrim($config->extensions->plugins, '/\\') . '/';
        $config->extensions->wrapper    = EXTENSION_PATH . rtrim($config->extensions->wrapper, '/\\') . '/';
        $config->extensions->legacy     = EXTENSION_PATH . rtrim($config->extensions->legacy, '/\\') . '/';
        $config->languages->path        = EXTENSION_PATH . rtrim($config->languages->path, '/\\') . '/';
        
        $config->libraries->path = rtrim($config->libraries->path, '/\\') . '/';
        $config->cache->path     = rtrim($config->cache->path, '/\\') . '/';
        $config->log->path       = rtrim($config->log->path, '/\\') . '/';
        
        // support absolute path
        $matches = array();
        if (!(preg_match('/^(\w:[\/|\\\\]|\/)/', $config->cache->path, $matches) === 1)) {
            $config->cache->path = ONTOWIKI_ROOT . $config->cache->path;
        }
        
        // set path variables
        $rewriteBase = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], BOOTSTRAP_FILE));
        $protocol    = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') ? 'https' : 'http';
        $port        = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != '80') 
                     ? (':' . $_SERVER['SERVER_PORT']) 
                     : '';
        $serverName  = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';
        $urlBase     = sprintf('%s://%s%s%s', $protocol, $serverName, $port, $rewriteBase);
        $urlBase     = rtrim($urlBase, '/') . '/';
        
        // construct URL variables
        $config->host           = parse_url($urlBase, PHP_URL_HOST);
        $config->urlBase        = rtrim($urlBase . (ONTOWIKI_REWRITE ? '' : BOOTSTRAP_FILE), '/\\') . '/';
        $config->staticUrlBase  = rtrim($urlBase, '/\\') . '/';
        $config->themeUrlBase   = $config->staticUrlBase
                                . $config->themes->path 
                                . $config->themes->default;
        $config->libraryUrlBase = $config->staticUrlBase
                                . $config->libraries->path;
        
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
        // require Front controller
        $this->bootstrap('frontController');
        $frontController = $this->getResource('frontController');
        
        // require Config
        $this->bootstrap('Config');
        $config = $this->getResource('Config');
        
        $dispatcher = new OntoWiki_Dispatcher(array('url_base' => $config->urlBase));
        $dispatcher->setControllerDirectory(APPLICATION_PATH . 'controllers');
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
        $erfurt = null;
        
        // require Config
        $this->bootstrap('Config');
        $config = $this->getResource('Config');
        
        // require OntoWiki
        $this->bootstrap('OntoWiki');
        $ontoWiki = $this->getResource('OntoWiki');
        
        try {
            $erfurt = Erfurt_App::getInstance(false)->start($config);
        } catch (Erfurt_Exception $ee) {
            exit('Error loading Erfurt framework: ' . $ee->getMessage());
        } catch (Exception $e) {
            exit('Unexpected error: ' . $e->getMessage());
        }
        
        // make available
        $ontoWiki->erfurt = $erfurt;
        
        return $erfurt;
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
    public function _initLogger()
    {
        // require config
        $this->bootstrap('Config');
        $config = $this->getResource('Config');
        
        // support absolute path
        if (!(preg_match('/^(\w:[\/|\\\\]|\/)/', $config->log->path) === 1)) {
            // prepend OntoWiki root for relative paths
            $config->log->path = ONTOWIKI_ROOT . $config->log->path;
        }
        
        // initialize logger
        if (is_writable($config->log->path) && ((boolean)$config->log->level !== false)) {
            $levelFilter = new Zend_Log_Filter_Priority((int)$config->log->level, '<=');
            
            $writer = new Zend_Log_Writer_Stream($config->log->path . 'ontowiki.log');
            $logger = new Zend_Log($writer);
            $logger->addFilter($levelFilter);
            
            return $logger;
        }
        
        // fallback to NULL logger
        $writer = new Zend_Log_Writer_Null();
        $logger = new Zend_Log($writer);
        
        return $logger;
    }
    
    public function _initModuleManager()
    {
        // require config
        $this->bootstrap('Config');
        $config = $this->getResource('Config');
        
        // TODO: make session requirement explicit
        $this->bootstrap('Session');
           
        $moduleManager = new OntoWiki_Module_Manager(ONTOWIKI_ROOT . $config->extensions->modules);
        
        return $moduleManager;
    }
    
    /**
     * Initializes the navigation
     *
     * @since 0.9.5
     */
    public function _initNavigation()
    {
        // require Session
        $this->bootstrap('Session');
        $session = $this->getResource('Session');
        
        $this->bootstrap('Request');
        $request = $this->getResource('Request');
        
        $this->bootstrap('Config');
        $config = $this->getResource('Config');
        
        // get current action name
        $currentAction = $request->getActionName();
        
        // is current action a default action?
        if ($currentAction == 'properties' or $currentAction == 'instances') {
            // save it to session
            $session->lastRoute = $currentAction;
        }
        
        // get last route or default
        $route = isset($session->lastRoute) 
               ? $session->lastRoute 
               : $config->route->default->name;
        
        // register with navigation
        if (isset($config->routes->{$route})) {
            extract($config->routes->{$route}->defaults->toArray());
            
            // and add last routed component
            OntoWiki_Navigation::register('index', array(
                'route'      => $route, 
                'controller' => $controller, 
                'action'     => $action, 
                'name'       => ucfirst($route), 
                'priority'   => 0
            ));
        }
    }
    
    /**
     * Initializes the OntoWiki main class
     *
     * @since 0.9.5
     */
    public function _initOntoWiki()
    {
        // require Config
        $this->bootstrap('Config');
        $config = $this->getResource('Config');
        
        $ontoWiki = OntoWiki::getInstance();
        $ontoWiki->language = isset($config->languages->locale) ? $config->languages->locale : null;
        $ontoWiki->config   = $config;
        
        return $ontoWiki;
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
        $pluginManager->addPluginPath(ONTOWIKI_ROOT . $config->extensions->plugins);
        
        return $pluginManager;
    }
    
    public function _initPlugins()
    {
        // require front controller
        $this->bootstrap('frontController');
        $frontController = $this->getResource('frontController');
        
        $frontController->registerPlugin(new OntoWiki_Controller_Plugin_HttpAuth(), 1); // Needs to be done first!
        $frontController->registerPlugin(new OntoWiki_Controller_Plugin_SetupHelper(), 2);
        $frontController->registerPlugin(new OntoWiki_Controller_Plugin_ListSetupHelper(), 3); //needs to be done after SetupHelper
    }
    
    /**
     * Initializes the request object
     *
     * @since 0.9.5
     */
    public function _initRequest()
    {
        $this->bootstrap('FrontController');
        $frontController = $this->getResource('FrontController');
        
        $this->bootstrap('Router');
        $router = $this->getResource('Router');
        
        $request = new OntoWiki_Request();
        $frontController->setRequest($request);
        
        $router->route($request);
        
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
        
        // require Config
        $this->bootstrap('OntoWiki');
        $ontoWiki = $this->getResource('OntoWiki');
        
        // init session
        $sessionKey = 'ONTOWIKI' . (isset($config->session->identifier) ? $config->session->identifier : '');        
        $session    = new Zend_Session_Namespace($sessionKey);
        
        // define the session key as a constant for global reference
        define('_OWSESSION', $sessionKey);
        
        // inject session vars into OntoWiki
        if (array_key_exists('sessionVars', $this->_options['bootstrap'])) {
            $ontoWiki->setSessionVars((array)$this->_options['bootstrap']['sessionVars']);
        }
        
        // make available
        $ontoWiki->session = $session;
        
        return $session;
    }
    
    /**
     * Initializes the toolbar
     *
     * @since 0.9.5
     */
    public function _initToolbar()
    {
        $this->bootstrap('Config');
        $config = $this->getResource('Config');
        
        $this->bootstrap('Translate');
        $translate = $this->getResource('Translate');
        
        // configure toolbar
        $toolbar = OntoWiki_Toolbar::getInstance();
        $toolbar->setThemeUrlBase($config->themeUrlBase)
                ->setTranslate($translate);
        
        return $toolbar;
    }
    
    /**
     * Loads the translation
     *
     * @since 0.9.5
     */
    public function _initTranslate()
    {
        $this->bootstrap('Config');
        $config = $this->getResource('Config');
        
        // setup translation cache
        if ((boolean)$config->cache->translation and is_writable($config->cache->path)) {            
            $translationFile = ONTOWIKI_ROOT 
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
        $translate = new Zend_Translate('csv', ONTOWIKI_ROOT . $config->languages->path, null, $options);
        try {
            $translate->setLocale($config->languages->locale);
        } catch (Zend_Translate_Exception $e) {
            $config->languages->locale = 'en';
            $translate->setLocale('en');
        }
        
        return $translate;
    }
    
    /**
     * Authenticates the current user or Anonymous with Erfurt
     *
     * @since 0.9.5
     */
    public function _initUser()
    {
        $user = null;
        
        // require Erfurt
        $this->bootstrap('Erfurt');
        $erfurt = $this->getResource('Erfurt');
        
        // get logged in user
        $auth = $erfurt->getAuth();
        if ($auth->hasIdentity()) {
            $user = $auth->getIdentity();
        }
    
        if (null === $user) {
            // authenticate anonymous user
            $erfurt->authenticate('Anonymous', '');
            $user = $auth->getIdentity();
        }
        
        return $user;
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
        
        // require Config
        $this->bootstrap('Translate');
        $translate = $this->getResource('Translate');
        
        // standard template path
        $defaultTemplatePath = ONTOWIKI_ROOT 
                             . 'application/views/templates';
        
        // path for theme template
        $themeTemplatePath   = ONTOWIKI_ROOT 
                             . $config->themes->path 
                             . $config->themes->default 
                             . 'templates';
        
        $moduleTemplatePath = ONTOWIKI_ROOT
                            . $config->extensions->modules;
        
        $viewOptions = array(
            'use_module_cachce' => (bool)$config->cache->modules, 
            'cache_path'        => $config->cache->path, 
            'lang'              => $config->languages->locale
            
        );
        
        // init view
        $view = new OntoWiki_View($viewOptions, $translate);
        $view->addScriptPath($defaultTemplatePath)  // default templates
             ->addScriptPath($themeTemplatePath)    // theme templates override default ones
             ->addScriptPath($moduleTemplatePath)
             ->setEncoding($config->encoding)
             ->setHelperPath(ONTOWIKI_ROOT . 'application/classes/OntoWiki/View/Helper', 'OntoWiki_View_Helper');
        
        
        
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
        $wrapperManager->addWrapperPath(ONTOWIKI_ROOT . $config->extensions->wrapper);
        
        return $wrapperManager;
    }
}



