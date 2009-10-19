<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @category   OntoWiki
 * @package    OntoWiki
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version   $Id: Toolbar.php 4095 2009-08-19 23:00:19Z christian.wuerker $
 */

require_once 'OntoWiki/Application.php';

/**
 * OntoWiki Toolbar class.
 *
 * Facilitates the programmatical construction of toolbars.
 *
 * @category   OntoWiki
 * @package    OntoWiki
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author    Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_Toolbar
{
    /**
     * Constants for default buttons 
     */
    const CANCEL    =   0;
    const SAVE      =   1;
    const EDIT      =   2;
    const ADD       =   3;
    const EDITADD   =   4;
    const DELETE    =   5;
    const SUBMIT    =  10;
    const RESET     =  11;
    const SEPARATOR = 100;
    
    /**
     * Default button configurations
     * @var array 
     */
    protected $_defaultButtons = array(
        self::CANCEL  => array('name' => 'Cancel', 'image' => 'cancel', 'class' => 'edit'), 
        self::SAVE    => array('name' => 'Save Changes', 'image' => 'save2', 'class' => 'edit'), 
        self::EDIT    => array('name' => 'Edit', 'image' => 'edit', 'class' => 'edit-enable'), 
        self::ADD     => array('name' => 'Add', 'image' => 'add'), 
        self::EDITADD => array('name' => 'Add', 'image' => 'editadd'), 
        self::DELETE  => array('name' => 'Delete', 'image' => 'delete'), 
        self::SUBMIT  => array('name' => 'Submit', 'class' => 'submit', 'image' => 'go2'), 
        self::RESET   => array('name' => 'Reset', 'class' => 'reset', 'image' => 'reset') 
    );
    
    /** 
     * Array of toolbar buttons
     * @var array 
     */
    protected $_buttons = array();
    
    /** 
     * OntoWiki Application config
     * @var Zend_Config 
     */
    protected $_config = null;
    
    /** 
     * Singleton instance
     * @var OntoWiki_Toolbar 
     */
    private static $_instance = null;
    
    /** 
     * Translation object
     * @var Zend_Translate 
     */
    protected $_translate = null;
    
    /**
     * Constructor
     */
    private function __construct()
    {
        $this->_config    = OntoWiki_Application::getInstance()->config;
        $this->_translate = OntoWiki_Application::getInstance()->translate;
    }
    
    /**
     * Disallow cloning
     */
    private function __clone() {}
    
    /**
     * Adds a button to the global toolbar.
     *
     * @param  mixed $type either a button constant defined by OntoWiki_Toolbar or
     *         a name string that identifies a custom button.
     * @param  array $options If $type is a custom type, providing $options is mandatory. 
     *         For default buttons $options is optional but you can overwrite the behaviour 
     *         of default buttons by providing $options. The following keys are regognized:
     *         - name:      the button's name
     *         - class:     the button's css class(es)
     *         - id:        the button's css id
     *         - image:     the button's theme image name (w/o icon- and .png, eg. 'edit' for 'icon-edit.png')
     *         - image_url: the complete URL of the button's image. Use this to define custom images that are
     *                      stored anywhere on the web.
     * @return OntoWiki_Toolbar
     */
    public function appendButton($type, array $options = array())
    {
        if ($button = $this->_getButton($type, $options)) {
            array_push($this->_buttons, $button);
        }
        
        return $this;
    }
    
    /**
     * Returns an instance of OntoWiki_Toolbar
     *
     * @return OntoWiki_Toolbar
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    /**
     * Adds a button to the front of the global toolbar.
     *
     * @param  mixed $type either a button constant defined by OntoWiki_Toolbar or
     *         a name string that identifies a custom button.
     * @param  array $options If $type is a custom type, providing $options is mandatory. 
     *         For default buttons $options is optional but you can overwrite the behaviour 
     *         of default buttons by providing $options. The following keys are regognized:
     *         name      – the button's name
     *         class     – the button's css class(es)
     *         id        – the button's css id
     *         image     – the button's theme image name (w/o icon- and .png, eg. 'edit' for 'icon-edit.png')
     *         image_url – the complete URL of the button's image. Use this to define custom images that are
     *                     stored anywhere on the web.
     * @return OntoWiki_Toolbar
     */
    public function prependButton($type, array $options = array())
    {
        if ($button = $this->_getButton($type, $options)) {
            array_unshift($this->_buttons, $button);
        }
        
        return $this;
    }
    
    /**
     * Renders the toolbar as an HTML string.
     *
     * @return string
     */
    public function __toString()
    {
        return '<div class="toolbar">' . implode('', $this->_buttons) . '</div>';
    }
    
    /**
     * Returns HTML for the specified button type.
     *
     * @param int $type the button type
     * @param array $options button options
     * @return string
     */
    private function _getButton($type, $options = array())
    {        
        if ($type == self::SEPARATOR) {
            return '<a class="button separator"></a>';
        } else if (array_key_exists($type, $this->_defaultButtons)) {
            $options = array_merge($this->_defaultButtons[$type], $options);
        } else {
            if (empty($options)) {
                require_once 'OntoWiki/Exception.php';
                throw new OntoWiki_Exception("Missing options for button '$type'.");
            }
            
            if (!array_key_exists('name', $options)) {
                $options['name'] = $type;
            }
        }
        
        // translate name
        if (array_key_exists('name', $options)) {
            $label = $this->_translate->translate($options['name']);
        } else {
            $label = null;
        }
        
        // set class
        if (array_key_exists('class', $options)) {
            $class = $options['class'];
        } else {
            $class = null;
        }
        
        // set id
        if (array_key_exists('id', $options)) {
            $id = 'id="' . $options['id'] . '"';
        } else {
            $id = null;
        }
        
        if (array_key_exists('url', $options)) {
            $href = 'href="' . $options['url'] . '"';
        } else {
            $href = null;
        }
        
        // set image
        if (array_key_exists('image_url', $options)) {
            $image = $options['image_url'];
        } else if (array_key_exists('image', $options)) {
            $image = $this->_config->themeUrlBase . 'images/icon-' . $options['image'] . '.png';
        } else {
            $image = null;
        }
        
        // construct button link
        $button = sprintf('<a class="button %s" %s %s><img src="%s"/><span>&nbsp;%s</span></a>', $class, $id, $href, $image, $label);
        
        return $button;
    }
}


