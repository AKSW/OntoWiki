<?php 

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki main class.
 *
 * Serves as a central registry for storing objects needed througout the application.
 *
 * @category OntoWiki
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki
{
    /**
     * The bootstrap object used during bootstrap.
     * @var Zend_Application_Bootstrap_Bootstrap
     */
    protected $_bootstrap = null;
    
    /** 
     * Array of properties
     * @var array 
     */
    protected $_properties = array();
    
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
    
    // ------------------------------------------------------------------------
    // --- Magic Methods
    // ------------------------------------------------------------------------
    
    /**
     * Constructor
     */
    private function __construct()
    {
    }
    
    /**
     * Disallow cloning
     */
    private function __clone()
    {
    }
    
    /**
     * Returns a property value
     *
     * @param string $propertyName
     * @return mixed
     * @since 0.9.5
     */
    public function __get($propertyName)
    {
        if (in_array($propertyName, $this->_sessionVars)) {
            $this->_properties[$propertyName] = $this->session->$propertyName;
        }
        
        $bootstrap = $this->getBootstrap();
        if ($bootstrap and $bootstrap->hasResource($propertyName)) {
            return $bootstrap->getResource($propertyName);
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
     * @since 0.9.5
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
     * @since 0.9.5
     */
    public function __isset($propertyName)
    {
        return array_key_exists($propertyName, $this->_properties);
    }
    
    /**
     * Unsets a property
     *
     * @param string $propertyName
     * @since 0.9.5
     */
    public function __unset($propertyName)
    {
        if (in_array($propertyName, $this->_sessionVars)) {
            unset($this->session->$propertyName);
        }
        
        unset($this->_properties[$propertyName]);
    }
    
    // ------------------------------------------------------------------------
    // --- Public Methods
    // ------------------------------------------------------------------------
    
    /**
     * Returns the current message stack and empties it.
     *
     * @since 0.9.5
     * @return array
     */
    public function drawMessages()
    {
        return $this->getMessages(true);
    }
    
    /**
     * Returns whether OntoWiki currently has messages for the user
     *
     * @return boolean
     */
    public function hasMessages()
    {
        $messages = $this->getMessages();
        
        return (!empty($messages));
    }
    
    /**
     * Returns the current message stack and empties it.
     *
     * @param boolean $clearMessages Clears the message stack after retrieval
     * @return array
     */
    public function getMessages($clearMessages = false)
    {
        $session = $this->getBootstrap()->getResource('Session');
        
        // store temporarily
        $messageStack = (array)$this->session->messageStack;
        
        if ($clearMessages) {
            // empty message stack
            unset($session->messageStack);
        }
        
        // return temp
        return $messageStack;
    }
    
    /**
     * Appends a message to the message stack
     *
     * @param OntoWiki_Message $message The message to be added.
     * @return OntoWiki_Application
     */
    public function appendMessage(OntoWiki_Message $message)
    {
        $session = $this->getBootstrap()->getResource('Session');
        
        $messageStack = (array)$session->messageStack;
        array_push($messageStack, $message);
        
        $session->messageStack = $messageStack;
        
        return $this;
    }
    
    /**
     * Prepends a message to the message stack
     *
     * @param OntoWiki_Message $message The message to be added.
     * @return OntoWiki_Application
     */
    public function prependMessage(OntoWiki_Message $message)
    {
        $session = $this->getBootstrap()->getResource('Session');
        
        $messageStack = (array)$session->messageStack;
        array_unshift($session->messageStack, $message);
        
        $session->messageStack = $messageStack;
        
        return $this;
    }
    
    /**
     * Returns the application bootstrap object
     */
    public function getBootstrap()
    {
        if (null === $this->_bootstrap) {
            $frontController  = Zend_Controller_Front::getInstance();
            $this->_bootstrap = $frontController->getParam('bootstrap');
        }
        
        return $this->_bootstrap;
    }
    
    /**
     * Singleton instance
     *
     * @return OntoWiki
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
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

