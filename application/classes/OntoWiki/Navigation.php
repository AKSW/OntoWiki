<?php
/* vim: sw=4:sts=4:expandtab */
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki navigation registry.
 *
 * @category OntoWiki
 * @package Navigation
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_Navigation
{
    /** 
     * Array with navigation elements
     * @var array 
     */
    protected static $_navigation = array();
    
    /** 
     * Array for the default navigation element group
     * @var array 
     */
    protected static $_defaultGroups = array();
    
    /** 
     * Array of navigation elements with configured position
     * @var array 
     */
    protected static $_ordered = array();
    
    /** 
     * Array of navigation elements without a configured position
     * @var array 
     */
    protected static $_unordered = array();
    
    /** 
     * Key of the currently active navigation element
     * @var string 
     */
    protected static $_activeKey = null;
    
    /** 
     * Array of parameters that should be kept when switching navigation elements.
     * @var array 
     */
    protected static $_keepParams = array(
        'r'
    );
    
    /** @var boolean */
    protected static $_isDisabled = false;
    
    /**
     * Disables the navigation for the current view.
     */
    public static function disableNavigation()
    {
        self::$_isDisabled = true;
    }
    
    /**
     * Returns the currently active navigation component.
     *
     * @return array
     */
    public static function getActive()
    {
        if (!self::$_activeKey) {
            return null;
        }
        
        return self::$_navigation[self::$_activeKey];
    }
    
    /**
     * Returns whether the navigation is disabled for the current view
     *
     * @return boolean
     */
    public static function isDisabled()
    {
        return self::$_isDisabled;
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
    public static function register($key, array $options, $replace = false)
    {
        if (array_key_exists($key, self::$_navigation) && !$replace) {
            throw new OntoWiki_Exception("Navigation component with key '$key' already registered.");
        }
        
        if (!array_key_exists('name', $options)) {
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
        self::$_navigation[$key] = $options;
        
        // store order request
        if (!$replace) {
            if (array_key_exists('priority', $options) && is_numeric($options['priority'])) {
                $position = (int)$options['priority'];
                while (array_key_exists((string)$position, self::$_ordered)) {
                    $position++;
                }
                self::$_ordered[$position] = $key;
            } else {
                self::$_unordered[] = $key;
            }
        }
        
        // set activation state
        // if ((array_key_exists('active', $options) && $options['active'])) {
        //     self::setActive($key);
        // }
    }
    
    /**
     * Used by the application to register default components
     *
     * @param string $controllerName
     * @param array $actionNames
     */
    public static function registerDefaultGroup($controllerName, array $actionNames)
    {
        $groupArray = array();
        foreach ($actionNames as $action) {
            $groups[$action] = array(
                'controller' => $controllerName, 
                'action'     => $action, 
                'name'       => ucfirst($action)
            );
        }
        
        self::$_defaultGroups[$controllerName] = $groupArray;
    }
    
    /**
     * Resets the Navigation by deleting all tabs
     *
     */
    public static function reset()
    {
       self::$_navigation = array();
    }
    
    /**
     * Sets the currently active navigation component.
     * make sure there is only one active
     *
     * @param string $key the identifier for the component
     */
    public static function setActive($key)
    {
        if (!array_key_exists($key, self::$_navigation)) {
            throw new OntoWiki_Exception("Navigation component with key '$key' not registered.");
        }
        
        // set the current active to unactive
        if (self::$_activeKey != null) {
            self::$_navigation[self::$_activeKey]['active'] = 'inactive';
        }
        
        // set new active
        self::$_navigation[$key]['active'] = 'active';
        
        // remember new
        self::$_activeKey = $key;
        
        
    }

    /**
     * Checks if a navigation components with the given key has been.
     *
     * @return boolean
     */
    public static function isRegistered($key)
    {
        if (array_key_exists($key, self::$_navigation)) {
            return true;
        }
        
        return false;
    }

    /**
     * Returns an array of registered navigation components
     *
     * @return array
     */
    public static function toArray()
    {
        if (!self::$_isDisabled) {
            $return = array();
            
            $session = new Zend_Session_Namespace(_OWSESSION . 'ONTOWIKI_NAVIGATION');
            if (isset($session->tabOrder)) {
                $over = array_diff(self::$_ordered, $session->tabOrder);
                ksort($over);
                self::$_ordered = array_merge($session->tabOrder, $over);
            }

            $request = Zend_Controller_Front::getInstance()->getRequest();
            $currentController = $request->getControllerName();
            $currentAction     = $request->getActionName();
            
            ksort(self::$_ordered);
            // first the order requests
            foreach (self::$_ordered as $orderKey => $elementKey) {
                
                if (array_key_exists($elementKey, self::$_navigation)) {
                    self::$_navigation[$elementKey]['url'] = self::_getUrl($elementKey, $currentController, $currentAction);
                    
                    // set active if current
                    if ($currentController == self::$_navigation[$elementKey]['controller'] && 
                        $currentAction == self::$_navigation[$elementKey]['action']) {
                        self::setActive($elementKey);
                    }

                    $return[$elementKey] = self::$_navigation[$elementKey];
                }
            }

            // finally the unordered
            foreach (self::$_unordered as $name => $elementKey) {
                self::$_navigation[$elementKey]['url'] = self::_getUrl($elementKey, $currentController, $currentAction);
                
                // set active if current
                if ($currentController == self::$_navigation[$elementKey]['controller'] && 
                    $currentAction == self::$_navigation[$elementKey]['action']) {
                    self::setActive($elementKey);
                }
                
                $return[$elementKey] = self::$_navigation[$elementKey];
            }

            return $return;
        }
    }
    
    /**
     * Returns the URL of the given navigation element
     *
     * @return OntoWiki_Url
     */ 
    protected static function _getUrl($elementKey, $currentController, $currentAction)
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $router  = Zend_Controller_Front::getInstance()->getRouter();
        
        $current  = self::$_navigation[$elementKey];
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
            $url = new OntoWiki_Url(array('route' => $current['route']), self::$_keepParams);
        } else {
            $controller = $current['controller'];
            $action     = $current['action'] ? $current['action'] : null;
            
            $url = new OntoWiki_Url(array('controller' => $controller, 'action' => $action), self::$_keepParams);
        }
        foreach($current as $key => $value){
            if($key != 'route' && $key != 'controller' && $key != 'action' && $key != 'priority' && $key != 'name'){
                $url->setParam($key, $value);
            }
        }
        return $url;
    }
}
