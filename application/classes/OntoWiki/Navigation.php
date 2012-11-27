<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki navigation registry.
 *
 * @category OntoWiki
 * @package OntoWiki_Classes
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_Navigation
{
    /** 
     * Array with navigation elements
     * @var array 
     */
    protected $_navigation = array();
    
    /** 
     * Array for the default navigation element group
     * @var array 
     */
    protected $_defaultGroups = array();
    
    /** 
     * Array of navigation elements with configured position
     * @var array 
     */
    protected $_ordered = array();
    
    /** 
     * Array of navigation elements without a configured position
     * @var array 
     */
    protected $_unordered = array();
    
    /** 
     * Key of the currently active navigation element
     * @var string 
     */
    protected $_activeKey = null;
    
    /** 
     * Array of parameters that should be kept when switching navigation elements.
     * @var array 
     */
    protected $_keepParams = array(
        'r'
    );
    
    /** @var boolean */
    protected $_isDisabled = false;
    
    /**
     * Constructor
     */
    public function __construct () 
    {
        
    }
    
    /**
     * Disables the navigation for the current view.
     */
    public function disableNavigation()
    {
        $this->_isDisabled = true;
    }
    
    /**
     * Returns the currently active navigation component.
     *
     * @return array
     */
    public function getActive()
    {
        if (!$this->_activeKey) {
            return null;
        }
        
        return $this->_navigation[$this->_activeKey];
    }
    
    /**
     * Returns whether the navigation is disabled for the current view
     *
     * @return boolean
     */
    public function isDisabled()
    {
        return $this->_isDisabled;
    }
    
    /**
     * Registers a component with the navigation
     *
     * @param string $key the identifier for the component
     * @param array $options An options array for the navigation entry.
     *        The following keys are recognized:
     *        name –       The name displayed on the tab
     *        route –      A Zend route name (internal OntoWiki route; 
     *                     mapped automatically to a controller and action name 
     *                     by Zend). Controller and action keys are ignored if a 
     *                     route is given.
     *        controller – Controller name for the URL
     *        action –     Action name for the URL
     *        priority –   Priority of the tab
     * @param boolean $replace Whether to replace previously registered tabs
     *        with the same name
     * @todo  Implement functionality to maintain a preferred order
     */
    public function register($key, array $options, $replace = false)
    {
        if (true == array_key_exists($key, $this->_navigation) && !$replace) {
            throw new OntoWiki_Exception("Navigation component with key '$key' already registered.");
        }
        
        if (!is_string($key)) {
            throw new OntoWiki_Exception("Key needs to be a string.");
        }
        
        if ( 0 == strlen((string)$key) ) {
            throw new OntoWiki_Exception("No key was set.");
        }
        
        if (false == array_key_exists('name', $options)) {
            $options['name'] = $key;
        }
        
        // merge defaults
        $options = array_merge(array(
            'route'      => null, 
            'controller' => null, 
            'action'     => null, 
            'name'       => null
        ), $options);
        
        // add registrant
        $this->_navigation[$key] = $options;
        
        // store order request
        if (false == $replace) {
            if (true == array_key_exists('priority', $options) && true == is_numeric($options['priority'])) {
                $position = (int)$options['priority'];
                while (array_key_exists((string)$position, $this->_ordered)) {
                    $position++;
                }
                $this->_ordered[$position] = $key;
            } else {
                $this->_unordered[] = $key;
            }
        }
        
        // if this is the first element, set it active
        if(1 == count($this->_navigation)) {
            $this->setActive ($key);
        }
    }
    
    /**
     * Not in Use!
     * Used by the application to register default components
     *
     * @param string $controllerName
     * @param array $actionNames
     *
    public function registerDefaultGroup($controllerName, array $actionNames)
    {
        $groupArray = array();
        foreach ($actionNames as $action) {
            $groups[$action] = array(
                'controller' => $controllerName, 
                'action'     => $action, 
                'name'       => ucfirst($action)
            );
        }
        
        $this->_defaultGroups[$controllerName] = $groupArray;
    }
    */
    
    /**
     * Sets the currently active navigation component. 
     *
     * @param string $key the identifier for the component
     */
    public function setActive($key)
    {
        if (false == array_key_exists($key, $this->_navigation)) {
            throw new OntoWiki_Exception('Navigation component with key \''. $key .'\' not registered.');
        }

        // set the current active to unactive
        if ($this->_activeKey != null) {
           unset($this->_navigation[$this->_activeKey]['active']);
        }
        
        // set new active
        $this->_navigation[$key]['active'] = 'active';
        
        // remember new
        $this->_activeKey = $key;
    }

    /**
     * Checks if a navigation components with the given key has been.
     *
     * @return boolean
     */
    public function isRegistered($key)
    {
        if (array_key_exists($key, $this->_navigation)) {
            return true;
        }
        
        return false;
    }

    /**
     * Returns an array of registered navigation components
     *
     * @return array
     */
    public function toArray()
    {
        if (false == $this->_isDisabled) {
            $return = array();
            
            $session = new Zend_Session_Namespace('ONTOWIKI_NAVIGATION');
            if (isset($session->tabOrder)) {
                $over = array_diff($this->_ordered, $session->tabOrder);
                ksort($over);
                $this->_ordered = array_merge($session->tabOrder, $over);
            }

            $request = Zend_Controller_Front::getInstance()->getRequest();
            $currentController = $request->getControllerName();
            $currentAction     = $request->getActionName();

            ksort($this->_ordered);
            // first the order requests
            foreach ($this->_ordered as $orderKey => $elementKey) {
                
                if (array_key_exists($elementKey, $this->_navigation)) {
                    $this->_navigation[$elementKey]['url'] = $this->_getUrl($elementKey, $currentController, $currentAction);
                    
                    // set active if current
                    if ($currentController == $this->_navigation[$elementKey]['controller'] && 
                        $currentAction == $this->_navigation[$elementKey]['action']) {
                        $this->setActive($elementKey);
                    }

                    $return[$elementKey] = true;
                }
            }

            // finally the unordered
            foreach ($this->_unordered as $name => $elementKey) {
                $this->_navigation[$elementKey]['url'] = $this->_getUrl($elementKey, $currentController, $currentAction);
                
                // set active if current
                if ($currentController == $this->_navigation[$elementKey]['controller'] && 
                    $currentAction == $this->_navigation[$elementKey]['action']) {
                    $this->setActive($elementKey);
                }
                
                $return[$elementKey] = $this->_navigation[$elementKey];
            }

            // now use the most recent version from $this->_navigation, since it contains the real active state
            $newReturn = array();
            foreach ($return as $key => $true) {
                $newReturn[$key] = $this->_navigation[$key];
            }
            $return = $newReturn;

            return $return;
        }
    }
    
    /**
     * Returns the URL of the given navigation element
     *
     * @return OntoWiki_Url
     */ 
    protected function _getUrl($elementKey, $currentController, $currentAction)
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $router  = Zend_Controller_Front::getInstance()->getRouter();
        
        $current  = $this->_navigation[$elementKey];
        $hasRoute = false;
        
        $currentController = $request->getControllerName();
        $currentAction     = $request->getActionName();
        
        if (isset($current['route'])) {
            if ($router->hasRoute($current['route'])) {
                $route    = $router->getRoute($current['route']);
                $defaults = $route->getDefaults();
                
                if ($defaults['controller'] == $current['controller'] && $defaults['action'] == $current['action']) {
                    $hasRoute = true;
                }
            }
        }
        
        if ($hasRoute) {
            $url = new OntoWiki_Url(array('route' => $current['route']), $this->_keepParams);
        } else {
            $controller = $current['controller'];
            $action     = $current['action'] ? $current['action'] : null;
            
            $url = new OntoWiki_Url(array('controller' => $controller, 'action' => $action), $this->_keepParams);
        }
        foreach($current as $key => $value){
            if($key != 'route' && $key != 'controller' && $key != 'action' && $key != 'priority' && $key != 'name'){
                $url->setParam($key, $value);
            }
        }
        return $url;
    }
    
    /**
     * 
     */
    public function getNavigation () 
    {
        return $this->_navigation;
    }
    
    /**
     * 
     */
    public function reset ()
    {
        $this->_navigation = array ();
        $this->_activeKey = null;
        $this->_isDisabled = false;
        $this->_defaultGroups = array ();
        $this->_ordered = array ();
        $this->_unordered = array ();
        $this->_keepParams = array ('r');
    }
}
