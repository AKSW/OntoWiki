<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki bootstrap class.
 *
 * Provides on-demand loading of application resources.
 *
 * @category OntoWiki
 * @package OntoWiki_Bootstrap
 * @author Norman Heino <norman.heino@gmail.com>
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    /**
     * Initializes the the extension manager which in turn scans
     * for components, modules, plugins and wrapper and registers them.
     *
     * @since 0.9.5
     */
    public function _initExtensionManager()
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

        Erfurt_Wrapper_Registry::reset();

        // require Erfurt
        $this->bootstrap('Erfurt');

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

        $extensionPath = ONTOWIKI_ROOT
                        . $config->extensions->base;

        $extensionPathBase = $config->staticUrlBase
                        . $config->extensions->base;

        $extensionManager = new OntoWiki_Extension_Manager($extensionPath);
        $extensionManager->setTranslate($translate)
                         ->setComponentUrlBase($extensionPathBase);

        // register component controller directories
        foreach ($extensionManager->getComponents() as $extensionName => $extensionConfig) {
            $frontController->addControllerDirectory($extensionConfig->path, '_component_' . $extensionName);
        }

        // make extension manager available to dispatcher
        $dispatcher = $frontController->getDispatcher();
        $dispatcher->setExtensionManager($extensionManager);

        // keep extension manager in OntoWiki
        $ontoWiki->extensionManager = $extensionManager;

        // actionhelper
        Zend_Controller_Action_HelperBroker::addPrefix('OntoWiki_Controller_ActionHelper_');
        Zend_Controller_Action_HelperBroker::addHelper(new OntoWiki_Controller_ActionHelper_List());

        return $extensionManager;
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
            throw $e;
        }

        // load user application configuration files
        $tryDistConfig = false;
        try {
            $privateConfig = new Zend_Config_Ini(ONTOWIKI_ROOT . 'config.ini', 'private', true);
            $config->merge($privateConfig);
        } catch (Zend_Config_Exception $e) {
            $tryDistConfig = true;
        }

        if ($tryDistConfig === true) {
            try {
                $privateConfig = new Zend_Config_Ini(ONTOWIKI_ROOT . 'config.ini.dist', 'private', true);
                $config->merge($privateConfig);
            } catch (Zend_Config_Exception $e) {
                $message = '<p>OntoWiki can not find a proper configuration.</p>' . PHP_EOL
                         . '<p>Maybe you have to copy and modify the distributed '
                         . '<code>config.ini.dist</code> file?</p>'. PHP_EOL
                         . '<details><summary>Error Details</summary>'
                         . $e->getMessage() . '</details>';

                throw new OntoWiki_Exception($message);
            }
        }

        // normalize path names
        $config->themes->path     = rtrim($config->themes->path, '/\\') . '/';
        $config->themes->default  = rtrim($config->themes->default, '/\\') . '/';
        $config->extensions->base = rtrim($config->extensions->base, '/\\') . '/';

        if ( false === defined('EXTENSION_PATH'))
            define('EXTENSION_PATH', $config->extensions->base);

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

        //force caching
        if (!is_writable($config->cache->path)) {
            throw new OntoWiki_Exception(
                '<p>OntoWiki can not write to the "cache" folder.</p>' . PHP_EOL .
                '<p>Maybe you have to create the folder or allow write access for the webserver user?</p>'
            );
        }

        // set path variables
        $rewriteBase = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], BOOTSTRAP_FILE));
        $protocol    = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') ? 'https' : 'http';

        if (isset($_SERVER['SERVER_PORT'])
            && $_SERVER['SERVER_PORT'] != '80' && $_SERVER['SERVER_PORT'] != '443') {
            $port = ':' . $_SERVER['SERVER_PORT'];
        } else {
            $port = '';
        }

        if (isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], ':') !== false) {
            // IPv6
            $serverName = '[' . $_SERVER['SERVER_NAME'] . ']';
        } else if (isset($_SERVER['SERVER_NAME'])) {
            // IPv4 or host name
            $serverName = $_SERVER['SERVER_NAME'];
        } else {
            // localhost
            $serverName = 'localhost';
        }

        $urlBase = sprintf(
            '%s://%s%s%s',
            $protocol,
            $serverName,
            $port,
            $rewriteBase
        );

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
            if ( false === defined('_OWDEBUG')) {
                define('_OWDEBUG', 1);
            }
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

        // require Logger, since Erfurt logger should write into OW logs dir
        $this->bootstrap('Logger');

        // Reset the Erfurt app for testability... needs to be refactored.
        Erfurt_App::reset();

        try {
            $erfurt = Erfurt_App::getInstance(false)->start($config);
        } catch (Erfurt_Exception $ee) {
            throw new OntoWiki_Exception('Error loading Erfurt framework: ' . $ee->getMessage());
        } catch (Exception $e) {
            throw new OntoWiki_Exception('Unexpected error: ' . $e->getMessage());
        }

        // Access the store in order to check whether connection works
        if (!$erfurt->getStore()) {
            return false;
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

            $logName = $config->log->path . 'ontowiki';

            // Check whether log can be created with $logName... otherwise append a number.
            // This needs to be done, since logs may be created by other processes (e.g. with
            // testing) and thus can't be opened anymore.
            for ($i = 0; $i<10; ++$i) {
                try {
                    $fullLogName = $logName;
                    if ($i > 0) {
                        $fullLogName .= '_' . $i;
                    }
                    $fullLogName .= '.log';

                    $writer = new Zend_Log_Writer_Stream($fullLogName);
                    if (null !== $writer) {
                        break;
                    }
                } catch (Zend_Log_Exception $e) {
                    // Nothing to do... just continue
                }
            }

            if (null !== $writer) {
                $logger = new Zend_Log($writer);
                $logger->addFilter($levelFilter);

                return $logger;
            }
        }

        // fallback to NULL logger
        $writer = new Zend_Log_Writer_Null();
        $logger = new Zend_Log($writer);

        return $logger;
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
            OntoWiki::getInstance()->getNavigation()->register(
                'index',
                array(
                    'route'      => $route,
                    'controller' => $controller,
                    'action'     => $action,
                    'name'       => ucfirst($route),
                    'priority'   => 0
                )
            );
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

        OntoWiki::reset();
        $ontoWiki = OntoWiki::getInstance();
        $ontoWiki->setBootstrap($this);
        $ontoWiki->language = isset($config->languages->locale) ? $config->languages->locale : null;
        $ontoWiki->config   = $config;

        return $ontoWiki;
    }

    public function _initPlugins()
    {
        // require front controller
        $this->bootstrap('frontController');
        $frontController = $this->getResource('frontController');

        // Needs to be done first!
        $frontController->registerPlugin(new OntoWiki_Controller_Plugin_HttpAuth(), 1);
        $frontController->registerPlugin(new OntoWiki_Controller_Plugin_SetupHelper(), 2);
        //needs to be done after SetupHelper
        $frontController->registerPlugin(new OntoWiki_Controller_Plugin_ListSetupHelper(), 3);
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

        if ( false === defined('_OWSESSION'))
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

        $viewOptions = array(
            'use_module_cache' => (bool)$config->cache->modules,
            'cache_path'        => $config->cache->path,
            'lang'              => $config->languages->locale

        );

        // init view
        $view = new OntoWiki_View($viewOptions, $translate);
        $view->addScriptPath($defaultTemplatePath)  // default templates
             ->addScriptPath($themeTemplatePath)    // theme templates override default ones
             ->addScriptPath($config->extensions->base)    // extension templates
             ->setEncoding($config->encoding)
             ->setHelperPath(ONTOWIKI_ROOT . 'application/classes/OntoWiki/View/Helper', 'OntoWiki_View_Helper');

        // set Zend_View to emit notices in debug mode
        $view->strictVars(defined('_OWDEBUG'));

        // init view renderer action helper
        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer($view);
        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);

        $themeLayoutTemplate = $themeTemplatePath
                             . DIRECTORY_SEPARATOR
                             . 'layouts'
                             . DIRECTORY_SEPARATOR
                             . 'layout.phtml';

        $layoutPath = $defaultTemplatePath . DIRECTORY_SEPARATOR . 'layouts';
        if (is_readable($themeLayoutTemplate)) {
            $layoutPath = $themeTemplatePath . DIRECTORY_SEPARATOR . 'layouts';
        }

        // initialize layout
        Zend_Layout::startMvc(
            array(
                // for layouts we use the default path
                'layoutPath' => $layoutPath
            )
        );

        return $view;
    }
}
