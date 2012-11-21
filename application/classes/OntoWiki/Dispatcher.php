<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki dispatcher
 *
 * Overwrites Zend_Controller_Dispatcher_Standard in order to allow for
 * multiple (component) controller directories.
 *
 * @category OntoWiki
 * @package OntoWiki_Classes
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_Dispatcher extends Zend_Controller_Dispatcher_Standard
{
    /**
     * The extension manager
     * @var OntoWiki_Extension_Manager
     */
    protected $_extensionManager = null;

    /**
     * Base for building URLs
     * @var string
     */
    protected $_urlBase = '';

    public function __construct($params = array())
    {
        if (array_key_exists('url_base', $params)) {
            $urlBase = (string)$params['url_base'];
            unset($params['url_base']);
        }

        parent::__construct($params);

        $this->urlBase = $urlBase;
    }

    /**
     * Sets the component manager
     */
    public function setExtensionManager(OntoWiki_Extension_Manager $extensionManager)
    {
        $this->_extensionManager = $extensionManager;
    }

    /**
     * Gets the component manager
     */
    public function getExtensionManager()
    {
        return $this->_extensionManager;
    }

    /**
     * Get controller class name
     *
     * Try request first; if not found, try pulling from request parameter;
     * if still not found, fallback to default
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return string|false Returns class name on success
     */
    public function getControllerClass(Zend_Controller_Request_Abstract $request)
    {
        $controllerName = $request->getControllerName();

        if (empty($controllerName)) {
            if (!$this->getParam('useDefaultControllerAlways')) {
                return false;
            }
            $controllerName = $this->getDefaultControllerName();
            $request->setControllerName($controllerName);
        }

        // Zend 1.10+ changes
        $className = $this->formatControllerName($controllerName);

        $controllerDirs      = $this->getControllerDirectory();
        $module = $request->getModuleName();
        if ($this->isValidModule($module)) {
            $this->_curModule    = $module;
            $this->_curDirectory = $controllerDirs[$module];
        } elseif ($this->isValidModule($this->_defaultModule)) {
            $request->setModuleName($this->_defaultModule);
            $this->_curModule    = $this->_defaultModule;
            $this->_curDirectory = $controllerDirs[$this->_defaultModule];
        } else {
            require_once 'Zend/Controller/Exception.php';
            throw new Zend_Controller_Exception('No default module defined for this application');
        }

        // PATCH
        // if component manager has controller registered
        // redirect to specific controller dir index
        if (null !== $this->_extensionManager) {
            if ($this->_extensionManager->isComponentRegistered($controllerName)) {
                $this->_curDirectory = $controllerDirs[$this->_extensionManager->getComponentPrefix() . $controllerName];
            }
        }
        
        return $className;
    }

    /**
     * Returns TRUE if the Zend_Controller_Request_Abstract object can be
     * dispatched to a controller.
     *
     * Use this method wisely. By default, the dispatcher will fall back to the
     * default controller (either in the module specified or the global default)
     * if a given controller does not exist. This method returning false does
     * not necessarily indicate the dispatcher will not still dispatch the call.
     *
     * @param Zend_Controller_Request_Abstract $action
     * @return boolean
     */
    public function isDispatchable(Zend_Controller_Request_Abstract $request)
    {
        // Zend 1.10+ changes
        $className = $this->getControllerClass($request);
        $actionMethod = strtolower($request->getActionName()) . 'Action';

        if (class_exists($className, false)) {
            if (method_exists($className, $actionMethod)) {
                return true;
            }
        }

        $fileSpec    = $this->classToFilename($className);
        $dispatchDir = $this->getDispatchDirectory();
        $test        = $dispatchDir . DIRECTORY_SEPARATOR . $fileSpec;

        if (Zend_Loader::isReadable($test)) {
            require_once $test;

            if (method_exists($className, $actionMethod)) {
                return true;
            }
        }

        /**
         * @trigger onIsDispatchable
         * Triggered if no suitable controller has been found. Plug-ins can
         * attach to this event in order to modify request URLs or provide
         * mechanisms that do not allow a controller/action mapping from URL
         * parts.
         */

        $pathInfo = ltrim($request->getPathInfo(), '/');

        // URI may not contain a whitespace character!
        $pathInfo = str_replace(' ', '+', $pathInfo);

        if (class_exists($className, false)) {
            // give a chance to let class handle (e.g. index controller news action default)
            return true;
        }

        $event = new Erfurt_Event('onIsDispatchable');
        $event->uri     = $this->urlBase . $pathInfo;
        $event->request = $request;

        $eventResult = (bool)$event->trigger();
        return $eventResult;
    }
}
