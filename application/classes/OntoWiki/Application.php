<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @category   OntoWiki
 * @package    OntoWiki
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version   $Id: Application.php 4211 2009-10-01 15:39:39Z norman.heino $
 */

/** 
 * Required Zend classes
 */
require_once 'Zend/Auth.php';
require_once 'Zend/Cache.php';
require_once 'Zend/Config/Ini.php';
require_once 'Zend/Controller/Action/HelperBroker.php';
require_once 'Zend/Controller/Action/Helper/ViewRenderer.php';
require_once 'Zend/Layout.php';
require_once 'Zend/Log.php';
require_once 'Zend/Log/Filter/Priority.php';
require_once 'Zend/Log/Writer/Stream.php';
require_once 'Zend/Log/Writer/Null.php';
require_once 'Zend/Session/Namespace.php';
require_once 'Zend/Translate.php';
require_once 'Zend/Translate/Adapter/Csv.php';
require_once 'Zend/View.php';

/** 
 * Required Erfurt classes
 */
require_once 'Erfurt/App.php';
require_once 'Erfurt/Auth/Identity.php';
require_once 'Erfurt/Event/Dispatcher.php';
require_once 'Erfurt/Owl/Model.php';
require_once 'Erfurt/Plugin/Manager.php';
require_once 'Erfurt/Rdfs/Resource.php';
require_once 'Erfurt/Wrapper/Manager.php';

/** 
 * Required OntoWiki API classes
 */
require_once 'OntoWiki/Component/Manager.php';
require_once 'OntoWiki/Message.php';
require_once 'OntoWiki/Navigation.php';
require_once 'OntoWiki/Toolbar.php';
require_once 'OntoWiki/View.php';
require_once 'OntoWiki/Resource.php';


/**
 * OntoWiki main application class.
 *
 * Initializes external libraries and components. 
 * Serves as a central registry for storing Data needed througout the application.
 *
 * @category   OntoWiki
 * @package    OntoWiki
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_Application 
{    
    /** 
     * Array of properties
     * @var array 
     */
    protected $_properties = array();
    
    /**
     * The event dispatcher
     * @var Erfurt_Event_Dispatcher 
     */
    protected $_eventDispatcher = null;
    
    /** 
     * The plug-in manager
     * @var Erfurt_Plugin_Manager 
     */
    protected $_pluginManager = null;
    
    /** 
     * The wrapper manager
     * @var OntoWiki_Wrapper_Manager 
     */
    protected $_wrapperManager = null;
    
    /** 
     * Singleton instance
     * @var OntoWiki_Application 
     */
    private static $_instance = null;
    
    /**
     * State variables that are automatically saved in the session 
     * (will survive requests).
     *
     * @var array 
     */
    private $_sessionVars = array(
        'selectedModel', 
        'selectedResource', 
        'selectedClass', 
        'authResult', 
        'lastRoute', 
        'errorState'
    );
    
    /**
     * Constructor
     */
    private function __construct() {}
    
    /**
     * Disallow cloning
     */
    private function __clone() {}
    
    /**
     * Returns a property value
     *
     * @param string $propertyName
     * @return mixed
     */
    public function __get($propertyName)
    {
        if (in_array($propertyName, $this->_sessionVars)) {
            $this->_properties[$propertyName] = $this->session->$propertyName;
        }
        
        if (isset($this->$propertyName)) {
            return $this->_properties[$propertyName];
        }
    }
    
    /**
     * Sets a property
     *
     * @param string $propertyName
     * @param mixed $propertyValue
     */
    public function __set($propertyName, $propertyValue)
    {        
        if (in_array($propertyName, $this->_sessionVars)) {
            $this->session->$propertyName = $propertyValue;
        }
        
        $this->_properties[$propertyName] = $propertyValue;
    }
    
    /**
     * Returns whether a property is set
     *
     * @param string $propertyName
     * @return boolean
     */
    public function __isset($propertyName)
    {
        return array_key_exists($propertyName, $this->_properties);
    }
    
    /**
     * Unsets a property
     *
     * @param string $propertyName
     */
    public function __unset($propertyName)
    {
        if (in_array($propertyName, $this->_sessionVars)) {
            unset($this->session->$propertyName);
        }
        
        unset($this->_properties[$propertyName]);
    }
    
    /**
     * Appends a message to the message stack
     *
     * @param OntoWiki_Message $message The message to be added.
     * @return OntoWiki_Application
     */
    public function appendMessage(OntoWiki_Message $message)
    {
        $messageStack = $this->session->messageStack;
        array_push($messageStack, $message);
        
        $this->session->messageStack = $messageStack;
        
        return $this;
    }
    
    /**
     * Singleton instance
     *
     * @return OntoWiki_Application
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    /**
     * Returns the current message stack and empties it.
     *
     * @return array
     */
    public function getMessages()
    {
        // store temporarily
        $messages = (array) $this->session->messageStack;
        // empty message stack
        unset($this->session->messageStack);
        
        // return temp
        return $messages;
    }
    
    /**
     * Returns the current agent
     *
     * @return Erfurt_Auth_Identity
     */
    public function getUser()
    {
        require_once 'Erfurt/Auth.php';
        return Erfurt_Auth::getInstance()->getIdentity();
    }
    
    /**
     * Returns whether OntoWiki currently has messages for the user
     *
     * @return boolean
     */
    public function hasMessages()
    {
        return (!empty($this->session->messageStack));
    }
    
    /**
     * Prepends a message to the message stack
     *
     * @param OntoWiki_Message $message The message to be added.
     * @return OntoWiki_Application
     */
    public function prependMessage(OntoWiki_Message $message)
    {
        array_unshift($this->session->messageStack, $message);
        
        return $this;
    }
    
    /**
     * Sets the base URL under which this OntoWiki application operates
     *
     * @param string $urlBase
     * @return OntoWiki_Application
     */
    public function setUrlBase($urlBase, $rewriteEngineOn)
    {
        // dynamic URL base for controller actions
        $this->urlBase = $urlBase . ($rewriteEngineOn ? '' : _OWBOOT);
        
        // static URL base for file access (css, js, png etc.)
        $this->staticUrlBase = $urlBase;
        
        // set rewriting state
        $this->rewriting = $rewriteEngineOn;
        
        return $this;
    }
    
    /**
     * Starts the OntoWiki application and returnes a reference to it.
     *
     * @return OntoWiki_Application
     */
    public static function start($urlBase, $rewriteEngineOn = false)
    {
        return self::getInstance()
                   ->setUrlBase($urlBase, $rewriteEngineOn)
                   ->init();
    }
    
    /**
     * Initializes this OntoWiki application
     *
     * Loads required libraries and initializes Zend Framework
     */
    public function init()
    {
        // load configuration files
        if (is_readable(_OWROOT . 'application/config/default.ini')) {
            try {
                $this->config = new Zend_Config_Ini(_OWROOT . 'application/config/default.ini', 'default', true);
            } catch (Zend_Config_Exception $e) {
                echo 'Error while parsing default.ini configuration file';
                exit;
            }
        } else {
            echo 'default.ini file was not found.';
            exit;
        }
        
        
        if (is_readable(_OWROOT . 'config.ini')) {
            try {
                $privateConfig = new Zend_Config_Ini(_OWROOT . 'config.ini', 'private', true);
                $this->config->merge($privateConfig);
            } catch (Zend_Config_Exception $e) {
                echo 'Error while parsing config.ini configuration file';
                exit;
            }
        } else {
            echo 'config.ini file was not found.';
            exit;
        }
        
        // init session
        $sessionKey = 'ONTOWIKI' . (isset($this->config->session->identifier) ? $this->config->session->identifier : '');
        define('_OWSESSION', $sessionKey);
        $this->session = new Zend_Session_Namespace(_OWSESSION);
        
        define('OW_SHOW_MAX', 5);
        
        // initialize variables from session
        $this->_loadSessionVars();
        
        // define constants for development/debugging
        if (isset($this->config->debug) and (boolean) $this->config->debug) {
            // display errors
            error_reporting(E_ALL | E_STRICT);
            ini_set('display_errors', 'On');
            // enable debugging options
            define('_OWDEBUG', 1);
            // log everything
            $this->config->log->level = 7;
        }
        
        // initialize logger
        if (substr($this->config->log->path, 0, 1) !== '/') {
            $this->config->log->path = _OWROOT . rtrim($this->config->log->path, '/\\') . '/';
        }
        
        $logPath = $this->config->log->path;
        if (is_writable($logPath) && ((boolean)$this->config->log->level !== false)) {
            $levelFilter = new Zend_Log_Filter_Priority((int) $this->config->log->level, '<=');
            
            require_once 'Zend/Log/Writer/Stream.php';
            $writer = new Zend_Log_Writer_Stream($logPath . 'ontowiki.log');
            $this->logger = new Zend_Log($writer);
            $this->logger->addFilter($levelFilter);
        } else {
            require_once 'Zend/Log/Writer/Null.php';
            $writer = new Zend_Log_Writer_Null();
            $this->logger = new Zend_Log($writer);
        }
        $this->logger->info('Request start: ' . microtime(true));
        $this->logger->info('Request with REQUEST_URI: "' . $_SERVER['REQUEST_URI'] . '"');
        
        
        // normalize path names
        $this->config->themes->path           = rtrim($this->config->themes->path,           '/\\') . '/';
        $this->config->themes->default        = rtrim($this->config->themes->default,        '/\\') . '/';
        $this->config->extensions->base       = rtrim($this->config->extensions->base,       '/\\') . '/';
        
        $this->config->extensions->components = $this->config->extensions->base
                                              . rtrim($this->config->extensions->components, '/\\') . '/';
        $this->config->extensions->modules    = $this->config->extensions->base
                                              . rtrim($this->config->extensions->modules,    '/\\') . '/';
        $this->config->extensions->plugins    = $this->config->extensions->base
                                              . rtrim($this->config->extensions->plugins,    '/\\') . '/';
        $this->config->extensions->wrapper    = $this->config->extensions->base
                                              . rtrim($this->config->extensions->wrapper,    '/\\') . '/';
        $this->config->extensions->legacy     = $this->config->extensions->base
                                              . rtrim($this->config->extensions->legacy,     '/\\') . '/';
        $this->config->languages->path        = $this->config->extensions->base
                                              . rtrim($this->config->languages->path,        '/\\') . '/';
        
        $this->config->libraries->path        = rtrim($this->config->libraries->path,        '/\\') . '/';
        $this->config->cache->path            = rtrim($this->config->cache->path,            '/\\') . '/';
        
        // support absolute path
        $matches = array();
        if (!(preg_match('/^(\w:[\/|\\\\]|\/)/', $this->config->cache->path, $matches) === 1)) {
            $this->config->cache->path = _OWROOT . $this->config->cache->path;
        }
        
        // construct URL variables
        $this->config->host          = parse_url($this->urlBase, PHP_URL_HOST);
        $this->config->urlBase       = rtrim($this->urlBase, '/\\') . '/';
        $this->config->staticUrlBase = rtrim($this->staticUrlBase, '/\\') . '/';
        $this->config->themeUrlBase  = $this->staticUrlBase 
                                     . $this->config->themes->path 
                                     . $this->config->themes->default;
        
        // initialize Erfurt
        $pre = microtime(true);
        
        try {
            $this->erfurt = Erfurt_App::getInstance(false)->start($this->config);
        } catch (Exception $e) {
            // TODO: Use an ow error page
            echo $e->getMessage();
            exit;
        }

        // get logged in user
        require_once 'Erfurt/Auth.php';
        $auth = $this->erfurt->getAuth();
        if ($auth->hasIdentity()) {
            $this->user = $auth->getIdentity();
        }
    
        if (null === $this->user) {
            // authenticate anonymous user
            $this->erfurt->authenticate('Anonymous', '');
            $this->user = $auth->getIdentity();
        }

        $this->logger->info('Loading Erfurt: ' . ((microtime(true) - $pre) * 1000) . ' ms');
        
        // init system config
        try {
            $this->configModel = $this->erfurt->getStore()->getModel($this->config->sysont->model, false);
        } catch (Exception $e) {
            // Catch exceptions here... If config model is not available, something went wrong while checking setup...
            echo $e->getMessage();
            exit;
        }
        
                
        // inititalize navigation and add default component
        if (!isset($this->lastRoute)) {
            $this->lastRoute = 'properties';
        }
        OntoWiki_Navigation::register('index', array(
            'route'    => $this->lastRoute, 
            'name'     => ucfirst($this->lastRoute), 
            'priority' => 0, 
            'active'   => false
        ));
        
        
        // load translation
        $pre = microtime(true);
        $this->translate = $this->_initTranslation();
        $this->logger->info('Loading translation: ' . ((microtime(true) - $pre) * 1000) . ' ms');
        
        // load event dispatcher for Erfurt and OntoWiki events
        $this->_eventDispatcher = Erfurt_Event_Dispatcher::getInstance();
        
        // instantiate plug-in manager and load plug-ins
        $this->_pluginManager = $this->erfurt->getPluginManager(false);
        $this->_pluginManager->addPluginPath(_OWROOT . $this->config->extensions->plugins);
        
        // initialize wrapper manager and load wrapper
        $this->_wrapperManager = $this->erfurt->getWrapperManager(false);
        $this->_wrapperManager->addWrapperPath(_OWROOT . $this->config->extensions->wrapper);
        // init toolbar
        $this->toolbar = OntoWiki_Toolbar::getInstance();
        
        
        $this->view = $this->_initViews();
        
        
        return $this;
    }
    
    /**
     * Initializes the translation object.
     *
     * @return Zend_Translate
     */
    private function _initTranslation()
    {
        $cacheDir = $this->config->cache->path;
        
        // setup translation cache
        if ((boolean)$this->config->cache->translation and is_writable($cacheDir)) {
            $this->logger->info('Initializing translation cache.');
            
            $translationFile = _OWROOT 
                             . $this->config->languages->path 
                             . $this->config->languages->locale . DIRECTORY_SEPARATOR 
                             . "core.csv";
            $frontendOptions = array(
                'lifetime'                => 3600, 
                'automatic_serialization' => true, 
                'master_file'             => $translationFile
            );
            $backendOptions = array(
                'cache_dir' => $cacheDir
            );
            $translationCache = Zend_Cache::factory('File', 'File', $frontendOptions, $backendOptions);

            // set translation cache
            Zend_Translate::setCache($translationCache);
        }
        
        // set up translations
        $options = array('scan' => Zend_Translate::LOCALE_DIRECTORY, 'disableNotices' => true);
        $translation = new Zend_Translate('csv', _OWROOT . $this->config->languages->path, null, $options);
        try {
            $translation->setLocale($this->config->languages->locale);
        } catch (Zend_Translate_Exception $e) {
            $this->config->languages->locale = 'en';
            $translation->setLocale('en');
        }
        
        return $translation;
    }
    
    /**
     * Initializes view and layout objects.
     *
     * @return Zend_View
     */
    private function _initViews()
    {
        // configure view
        $defaultTemplatePath = _OWROOT . 'application/views/templates';
        $themeTemplatePath   = _OWROOT . $this->config->themes->path . $this->config->themes->default . 'templates';
        
        
        $view = new OntoWiki_View();
        $view->addScriptPath($defaultTemplatePath)  // default templates
             ->addScriptPath($themeTemplatePath)    // theme templates override default ones
             ->setEncoding($this->config->encoding)
             ->setHelperPath(_OWROOT . 'application/classes/OntoWiki/View/Helper', 'OntoWiki_View_Helper');
        
        // set Zend_View to emit notices in debug mode
        $view->strictVars(defined('_OWDEBUG'));
        
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
     * Loads variables stored in the session.
     */ 
    private function _loadSessionVars()
    {
        foreach ($this->_sessionVars as $varName) {
            if (isset($this->session->$varName)) {
                $this->$varName = $this->session->$varName;
            }
        }
        
        // init empty message stack
        if (!isset($this->session->messageStack)) {
            $this->session->messageStack = array();
        }
    }
}

