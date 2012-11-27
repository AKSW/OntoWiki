<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki URL class.
 *
 * Represents an internal OntoWiki URL and provides methods for
 * adding, removing and replacing parameters.
 *
 * @category OntoWiki
 * @package OntoWiki_Classes
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_Url
{
    /** 
     * The current request object
     * @var Zend_Controller_Request_Abstract 
     */
    protected $_request = null;
    
    /**
     * Array of URL parameters
     * @var array
     */
    protected $_params = null;
    
    /**
     * Controller name for the URL
     * @var string 
     */
    protected $_controller = null;
    
    /** 
     * Action name for the URL
     * @var string 
     */
    protected $_action = null;
    
    /** 
     * Router used for the current request
     * @var Zend_Controller_Router_Route 
     */
    protected $_route = null;
    
    /**
     * Whether to use nice search-engine friendly URLs
     * @var boolean 
     */
    protected $_useSefUrls = true;
    
    /**
     * 
     */
    protected $_base = null;

    /**
     * Constructor
     */
    public function __construct(array $options = array(), $paramsToKeep = null, $paramsToExclude = null, $base = null)
    {
        $this->_request    = Zend_Controller_Front::getInstance()->getRequest();
        $defaultAction     = Zend_Controller_Front::getInstance()->getDefaultAction();
        $defaultController = Zend_Controller_Front::getInstance()->getDefaultControllerName();
        $router            = Zend_Controller_Front::getInstance()->getRouter();
        
        // use defaults - current page
        if (!isset($options['route']) && !isset($options['controller']) && !isset($options['action'])){
            $options['controller'] = $this->_request->getControllerName();
            $options['action'] = $this->_request->getActionName();
        }
        
        if($base != null && is_string($base)){
            $this->_base = $base;
        } else if(isset(OntoWiki::getInstance()->config->urlBase)){
            $this->_base = OntoWiki::getInstance()->config->urlBase;
        } else {
            $this->_base = "http://ns.aksw.org/undefined/";
        }
        
        // keep parameters
        if (!$this->_request) {
            $this->_params = array();
        } else if (is_array($paramsToKeep)) {
            $this->_params = array_intersect_key($this->_request->getParams(), array_flip($paramsToKeep));
        } else {
            $this->_params  = $this->_request->getParams();
        }

        if (is_array($paramsToExclude)) {
            foreach ($paramsToExclude as $param) {
                if (isset($this->_params[$param])) {
                    unset($this->_params[$param]);
                }
            }
        }
        
        // set route
        $flag = false;
        if (array_key_exists('route', $options) && $router->hasRoute($options['route'])) {
            $flag = true;
            $this->_route = $router->getRoute($options['route']);
        } else {
            // set controller
            if (array_key_exists('controller', $options) && $options['controller']) {
                $flag = true;
                $this->_controller = rtrim($options['controller'], '/');
            } else if (array_key_exists('controller', $this->_params) && $this->_params['controller']) {
                $this->_controller = $this->_params['controller'];
            }

            // set action
            if (array_key_exists('action', $options) && $options['action']) {
                $flag = true;
                $this->_action = rtrim($options['action'], '/');
            } else if (array_key_exists('action', $this->_params) && $this->_params['action']) {
                $this->_action = $this->_params['action'];
            }
        }
        
        if (!$flag) {
            try {
                $routeName = $router->getCurrentRouteName();
            } catch (Exception $e) {
                $routeName = 'default';
            }
            
            if ($router->hasRoute($routeName)) {
                $this->_route = $router->getRoute($routeName);
            }
        }
        
        // check default controller/action and leave those empty
        if (rtrim($this->_action, '/') == $defaultAction) {
            $this->_action = '';
            
            if (rtrim($this->_controller, '/') == $defaultController) {
                $this->_controller = '';
            }
        }        
        
        // don't need these anymore
        unset($this->_params['module']);
        unset($this->_params['controller']);
        unset($this->_params['action']);
    }
    
    /**
     * set base url (for unittests)
     * @param string $base
     */
    public function setBase($base) 
    { 
        $this->_base = $base;
    }
    
    /**
     * Returns a URL string representing the object
     *
     * @return string
     */
    public function __toString()
    {        
        try {
            return $this->_buildQuery();
        } catch (Exception $e) {
            echo $e;
            return '';
        }
    }
    
    /**
     * Returns the URL parameters
     *
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }
    
    /**
     * Sets a URL parameter. Paramters with the same name already
     * set will be overwritten.
     *
     * @param string $name parameter name
     * @param string $value parameter value 
     * @param boolean $contractNamespace denotes whether to contract namespaces in URIs
     *
     * @return OntoWiki_Url
     */
    public function setParam($name, $value, $contractNamespace = false)
    {
        switch ($name) {
            case 'controller':
            case 'action':
                $this->{'_' . $name} = $value;
                break;
            default:
                if (null !== $value) {
                    if ($contractNamespace) {
                        $value = OntoWiki_Utils::contractNamespace($value);
                    }
                    // if (preg_match('/\//', $value)) {
                    //     $this->_useSefUrls = false;
                    // }
                    $this->_params[$name] = $value;
                } else {
                    unset($this->_params[$name]);
                }
        }
        
        // allow chaining
        return $this;
    }
    
    /**
     * Sets a URL parameter. Paramters with the same name already
     * set will be overwritten.
     *
     * @param string $name parameter name
     * @param string $value parameter value 
     *
     * @return OntoWiki_Url
     */
    public function __set($name, $value)
    {
        return $this->setParam($name, $value);
    }
    
    /**
     * Returns the value of parameter $name
     *
     * @param string $name parameter name
     *
     * @return string the value of parameter $name
     */
    public function __get($name)
    {
        if (isset($this->_params[$name])) {
            return $this->_params[$name];
        }
    }
    
    /**
     * Returns whether parameter $name is set
     *
     * @param string $name parameter name
     *
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->_params[$name]);
    }
    
    /**
     * Unsets the parameter $name
     *
     * @param string $name parameter name
     *
     * @return OntoWiki_Url
     */
    public function __unset($name)
    {
        if (isset($this->$this->_params[$name])) {
            unset($this->$this->_params[$name]);
        }
        
        // allow chaining
        return $this;
    }
    
    /**
     * Builds the query part of the URL
     */
    protected function _buildQuery()
    {
        
        $event = new Erfurt_Event('onBuildUrl');
        $event->base = $this->_base;
        $event->route = $this->_route;
        $event->controller = $this->_controller;
        $event->action     = $this->_action;
        $event->params     = $this->_params;
        $urlCreated        = $event->trigger();
        
        if ($event->handled()) {
            if ($urlCreated && isset($event->url)) {
                return $event->url;
            } else {
                $this->_params = $event->params;
                $this->_controller = $event->controller;
                $this->_action     = $event->action;
                $this->_route      = $event->route;
            }
        }
        
        // check params
        foreach ($this->_params as $name => $value) {
            if (is_string($value) && preg_match('/\//', $value)) {
                $this->_useSefUrls = false;
            }
        }
        
        $url = '';
        if ($this->_route) {
            // checking if reset of route-defaults necessary
            // fixes pager usage fails on versioning pages
            if (count($this->_route->getDefaults()) == 0) {
                $resetRoute = false;
            } else {
                $resetRoute = true;
            }

            if ($this->_useSefUrls) {
                // let the route assemble the whole URL
                $url = $this->_route->assemble($this->_params, $resetRoute, true);
            } else {
                // we will assign parameters ourselves
                $url = $this->_route->assemble(array(), $resetRoute);
                $url = sprintf('%s/%s', $url, '?' . http_build_query($this->_params, '&amp;'));
            }
        } else {
            if ($this->_useSefUrls) {
                $query   = '';
                $lastKey = '';
                foreach ($this->_params as $key => $value) {
                    if (is_scalar($value)) {
                        $value   = urlencode($value);
                        $query  .= "$key/$value/";
                        $lastKey = $key;
                    } else {
                        // drop these parameters (arrays etc...)
                    }
                }
                // remove trailing slash
                $query = rtrim($query, '/');
            } else {
                $query = '?' . http_build_query($this->_params, '&amp;');
            }
            $parts = array_filter(array($this->_controller, $this->_action, $query));
            $url = implode('/', $parts);
        }
        // HACK:
        $this->_useSefUrls = true;
        
        return $this->_base . ltrim($url, '/');
    }
}


