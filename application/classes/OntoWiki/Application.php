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
}

