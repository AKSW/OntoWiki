<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki menu class.
 *
 * @category OntoWiki
 * @package OntoWiki_Classes
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_Menu
{
    /**
     * Menu entry separator
     */
    const SEPARATOR = '_---_';
    
    /** 
     * Array of menu entries
     * @var array 
     */
    private $_entries = array();
    
    /**
     * Constructor
     */
    public function __construct() {}
    
    public function appendEntry($entryKey, $entryContent = null)
    {
        if (!is_string($entryKey)) {
            throw new OntoWiki_Exception('Entry key must be string.');
        }
        
        if (($entryKey != self::SEPARATOR) and !is_string($entryContent) and !is_array($entryContent) and !($entryContent instanceof OntoWiki_Menu)) {
            throw new OntoWiki_Exception('Menu content must be an instance of ' . __CLASS__ . ' or string, ' . gettype($entryContent) . ' given.');
        }
        
        if (array_key_exists($entryKey, $this->_entries)) {
            throw new OntoWiki_Exception("An entry with key '$entryKey' already exists.");
        }
        
        if ($entryKey == self::SEPARATOR) {
            $key = self::SEPARATOR . uniqid();
            $this->_entries[$key] = self::SEPARATOR;
        } else {
            $this->_entries[$entryKey] = $entryContent;
        }
        
        return $this;
    }
    
    public function prependEntry($entryKey, $entryContent = null)
    {
        if (!is_string($entryKey)) {
            throw new OntoWiki_Exception('Entry key must be string.');
        }
        
        if (($entryKey != self::SEPARATOR) and !is_string($entryContent) and !is_array($entryContent) and !($entryContent instanceof OntoWiki_Menu)) {
            throw new OntoWiki_Exception('Menu content must be an instance of ' . __CLASS__ . ' or string, ' . gettype($entryContent) . ' given.');
        }
        
        if (array_key_exists($entryKey, $this->_entries)) {
            throw new OntoWiki_Exception("An entry with key '$key' already exists.");
        }
        
        if ($entryKey == self::SEPARATOR) {
            $key = self::SEPARATOR . uniqid();
            
            $this->_entries = array_merge(array($key => self::SEPARATOR), $this->_entries);
        } else {
            $this->_entries = array_merge(array($entryKey => $entryContent), $this->_entries);
        }
        
        return $this;
    }
    
    /**
     * Sets a menu entry. Throws an exception if a menu key with
     * the same name already exists and 
     *
     * @param string $entryKey
     * @param string|array|OntoWiki_Menu $entryContent
     *        If a string is provided, it is used as the target URL for the menu entry.
     *        If an array is given, it must have a key 'url', whereas 'class' and 'id'
     *        are optional and can be used for CSS classes and CSS ids respectively.
     *        An instance of OntoWiki_Menu means, this entry is a submenu.
     * @param boolean replace
     */
    public function setEntry($entryKey, $entryContent = null, $replace = true)
    {
        if (!is_string($entryKey)) {
            throw new OntoWiki_Exception('Entry key must be string.');
        }
        
        if (($entryKey != self::SEPARATOR) and !is_string($entryContent) and !is_array($entryContent) and !($entryContent instanceof OntoWiki_Menu)) {
            throw new OntoWiki_Exception('Menu content must be an instance of ' . __CLASS__ . ' or string, ' . gettype($entryContent) . ' given.');
        }
        
        if (!$replace and array_key_exists($entryKey, $this->_entries)) {
            throw new OntoWiki_Exception("An entry with key '$entryKey' already exists.");
        }
        
        if ($entryKey == self::SEPARATOR) {
            $key = self::SEPARATOR . uniqid();
            $this->_entries[$key] = self::SEPARATOR;
        } else {
            $this->_entries[$entryKey] = $entryContent;
        }
        
        return $this;
    }
    
    /**
     * Returns a sub menu entry. If the sub menu does not
     * exist an empty instance of OntoWiki_Menu is returned
     * that represents a new submenu.
     *
     * @param string $subMenuKey
     * @return OntoWiki_Menu|null
     */
    public function getSubMenu($subMenuKey)
    {
        if (!array_key_exists($subMenuKey, $this->_entries)) {
            $subMenu = new OntoWiki_Menu();
            $this->setEntry($subMenuKey, $subMenu);
        }
        
        if (!$this->_entries[$subMenuKey] instanceof OntoWiki_Menu) {
            throw new OntoWiki_Exception("Entry '$subMenuKey' is not a menu.");
        }
        
        return $this->_entries[$subMenuKey];
    }
    
    /**
     * Removes the entry with key $entryKey from the menu.
     *
     * @param string $entryKey
     */
    public function removeEntry($entryKey)
    {
        if (array_key_exists($entryKey, $this->_entries)) {
            unset($this->_entries[$entryKey]);
        }
    }
    
    /**
     * Returns the menu instance as an array
     *
     * @param boolean $translate Whether to translate keys
     * @param boolean $recursive Whether to recursively array-ize this instance
     * @return array
     */
    public function toArray($translate = false, $recursive = true)
    {
        if ($translate) {
            $translation = OntoWiki::getInstance()->translate;
        }
        
        $return = array();
        
        foreach ($this->_entries as $key => $content) {
            if ($translate) {
                $key = $translation->translate($key);
            }
            
            if ($content instanceof self) {
                if ($recursive) {
                    $return[$key] = $content->toArray($translate, $recursive);
                } else {
                    $return[$key] = $content;
                }
            } else {
                $return[$key] = $content;
            }
        }
        
        return $return;
    }
    
    /**
     * Returns the menu instance as an HTML list
     *
     * @return array
     */
    public function toHtmlList()
    {
        // TODO:
    }
    
    /**
     * Returns the menu instance as a JSON array
     *
     * @return array
     */
    public function toJson($translate = true)
    {   
        return json_encode($this->toArray($translate, true));
    }
}


