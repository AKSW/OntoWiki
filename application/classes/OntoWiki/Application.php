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
 * @deprecated 0.9.5 use OntoWiki instead
 */
class OntoWiki_Application 
{    
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
        define('OW_SHOW_MAX', 5);
        

        $this->logger->info('Request start: ' . microtime(true));
        
        // initialize Erfurt
        $pre = microtime(true);

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
        

        
        
        return $this;
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

