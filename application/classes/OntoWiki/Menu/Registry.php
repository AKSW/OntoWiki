<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki menu registry class.
 *
 * Serves as a central registry for menus and provides methods for setting
 * and retrieving menu instances.
 *
 * @category OntoWiki
 * @package Menu
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_Menu_Registry
{
    /** 
     * Menu registry; an array of menu instances
     * @var array 
     */
    private $_menus = array();
    
    /** 
     * Singleton instance
     * @var OntoWiki_Menu_Registry 
     */
    private static $_instance = null;
    
    /**
     * Singleton instance
     *
     * @return OntoWiki_Menu_Registry
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    /**
     * Returns the menu denoted by $menuKey.
     *
     * @param string $menuKey
     * @return OntoWiki_Menu
     */
    public function getMenu($menuKey)
    {
        if (!is_string($menuKey)) {
            throw new OntoWiki_Exception('Menu key must be string.');
        }
        
        if (!array_key_exists($menuKey, $this->_menus)) {
            $this->setMenu($menuKey, new OntoWiki_Menu());
        }
        
        return $this->_menus[$menuKey];
    }
    
    /**
     * Stores the menu $menu with key $menuKey in the registry.
     *
     * @param string $menuKey
     * @param OntoWiki_Menu $menu
     * @param boolean $replace
     * @return OntoWiki_Menu_Registry
     */
    public function setMenu($menuKey, OntoWiki_Menu $menu, $replace = true)
    {
        if (!is_string($menuKey)) {
            throw new OntoWiki_Exception('Menu key must be string.');
        }
        
        if (!$replace and array_key_exists($menuKey, $this->menus)) {
            throw new OntoWiki_Exception("Menu with key '$menuKey' already registered.");
        }
        
        $this->_menus[$menuKey] = $menu;
        
        return $this;
    }
    
    private function __construct()
    {
        $this->setMenu('application', $this->_getApplicationMenu());
    }
    
    private function _getApplicationMenu()
    {
        $owApp = OntoWiki::getInstance();
        
        // user sub menu
        if ($owApp->erfurt->isActionAllowed('RegisterNewUser')) {
            $userMenu = new OntoWiki_Menu();
            $userMenu->setEntry('Register New User', $owApp->config->urlBase . 'application/register');
        }
        if ($owApp->user && !$owApp->user->isAnonymousUser()) {
            if (!isset($userMenu)) {
                $userMenu = new OntoWiki_Menu();
            }
            
            if (!$owApp->user->isDbUser()) {
                $userMenu->setEntry('Preferences', $owApp->config->urlBase . 'application/preferences');
            }
            
            $userMenu->setEntry('Logout', $owApp->config->urlBase . 'application/logout');
        }
        
        // view sub menu
        $viewMenu = new OntoWiki_Menu();
        
        // extras sub menu
        $extrasMenu = new OntoWiki_Menu();

        $extrasMenu->setEntry('News', $owApp->config->urlBase . 'index/news');
        
        // help sub menue
        $helpMenu = new OntoWiki_Menu();
        $helpMenu->setEntry('Documentation', 'http://ontowiki.net/Projects/OntoWiki/Help')
                 ->setEntry('Bug Report', 'http://code.google.com/p/ontowiki/issues/entry')
                 ->setEntry(
                     'Version Info', 'http://ontowiki.net/Projects/OntoWiki/ChangeLog#'.
                     $owApp->config->version->number
                 )
                 ->setEntry('About', $owApp->config->urlBase . 'application/about');
        
        // build menu out of sub menus
        $applicationMenu = new OntoWiki_Menu();
        if (isset($userMenu)) {
            $applicationMenu->setEntry('User', $userMenu);
        }
        $applicationMenu->/*setEntry('View', $viewMenu)
                        ->*/setEntry('Extras', $extrasMenu)
                        ->setEntry('Help', $helpMenu);
        
        // add cache entry only in debug mode
        if (defined('_OWDEBUG')) {            
            $debugMenu = new OntoWiki_Menu();
            $debugMenu->setEntry('Clear Module Cache', $owApp->config->urlBase . 'debug/clearmodulecache')
                      ->setEntry('Clear Translation Cache', $owApp->config->urlBase . 'debug/cleartranslationcache')
                      ->setEntry('Clear Object & Query Cache', $owApp->config->urlBase . 'debug/clearquerycache')
                      ->setEntry('Reset Session', $owApp->config->urlBase . 'debug/destroysession');
                      
            // for testing sub menus  
            // $test1 = new OntoWiki_Menu();
            // $test1->appendEntry('Test 1', '#');
            // $test2 = new OntoWiki_Menu();
            // $test2->appendEntry('Test 2', $test1);
            // $debugMenu->setEntry('Test', $test2);
            
            $applicationMenu->setEntry('Debug', $debugMenu);
        }
        
        return $applicationMenu;
    }
}


