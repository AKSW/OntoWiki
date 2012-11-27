<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki Toolbar class.
 *
 * Facilitates the programmatical construction of toolbars.
 *
 * @category OntoWiki
 * @package OntoWiki_Classes
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Norman Heino <norman.heino@gmail.com>
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
    const EXPORT    =   6;
    const SUBMIT    =  10;
    const RESET     =  11;
    const SEPARATOR = 100;
    
    /**
     * Default button configurations
     * @var array 
     */
    protected $_defaultButtons = array(
        self::CANCEL  => array('name' => 'Cancel', 'image' => 'cancel', 'class' => 'edit cancel'), 
        self::SAVE    => array('name' => 'Save Changes', 'image' => 'save2', 'class' => 'edit save'), 
        self::EDIT    => array('name' => 'Edit', 'image' => 'edit', 'class' => 'edit-enable'), 
        self::ADD     => array('name' => 'Add', 'image' => 'add'), 
        self::EDITADD => array('name' => 'Add', 'image' => 'editadd'), 
        self::DELETE  => array('name' => 'Delete', 'image' => 'delete', 'class' => 'delete'), 
        self::SUBMIT  => array('name' => 'Submit', 'class' => 'submit', 'image' => 'go2'), 
        self::RESET   => array('name' => 'Reset', 'class' => 'reset', 'image' => 'reset'),
        self::EXPORT  => array('name' => 'Export', 'class' => 'export', 'image' => 'save')
    );
    
    /** 
     * Array of toolbar buttons
     * @var array 
     */
    protected $_buttons = array();
    
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
     *         - url:       the URL to be fetched when the button has been clicked.
     *         - title:     value for the HTML title attribute (displayed as a tooltip in most browsers).
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
     *         - name:      the button's name
     *         - class:     the button's css class(es)
     *         - id:        the button's css id
     *         - url:       the URL to be fetched when the button has been clicked.
     *         - title:     value for the HTML title attribute (displayed as a tooltip in most browsers).
     *         - image:     the button's theme image name (w/o icon- and .png, eg. 'edit' for 'icon-edit.png')
     *         - image_url: the complete URL of the button's image. Use this to define custom images that are
     *                      stored anywhere on the web.
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
     * Sets the URL base for the current theme
     *
     * @since 0.9.5
     * @param string $themeUrlBase The URL base into the theme dir
     * @return OntoWiki_Toolbar
     */
    public function setThemeUrlBase($themeUrlBase)
    {
        $this->_themeUrlBase = (string)$themeUrlBase;
        
        return $this;
    }
    
    /**
     * Sets the translation object for the current UI language
     *
     * @since 0.9.5
     * @param Zend_Translate $translate The translation object
     * @return OntoWiki_Toolbar
     */
    public function setTranslate(Zend_Translate $translate)
    {
        $this->_translate = $translate;
        
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
                throw new OntoWiki_Exception("Missing options for button '$type'.");
            }
            
            if (!array_key_exists('name', $options)) {
                $options['name'] = $type;
            }
        }
        
        // translate name
        if (array_key_exists('name', $options)) {
            if ($this->_translate instanceof Zend_Translate) {
                $label = $this->_translate->translate($options['name']);
            } else {
                $label = $options['name'];
            }
        } else {
            $label = null;
        }
        
        // set class
        if (array_key_exists('+class', $options)) {
            $addedClasses = $options['+class'];
        }
        
        // set class
        if (array_key_exists('class', $options)) {
            $class = $options['class'];
            
            if (isset($addedClasses)) {
                $class = $class
                       . ' '
                       . $addedClasses;
            }
        } else {
            if (isset($addedClasses)) {
                $class = $addedClasses;
            } else {
                $class = null;
            }
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
        
        if (array_key_exists('title', $options)) {
            $title = 'title="' . $options['title'] . '"';
        } else {
            $title = null;
        }
        
        // set image
        if (array_key_exists('image_url', $options)) {
            $image = $options['image_url'];
        } else if (array_key_exists('image', $options)) {
            $image = $this->_themeUrlBase . 'images/icon-' . $options['image'] . '.png';
        } else {
            $image = null;
        }
        
        // construct button link
        $button = sprintf('<a class="button %s" %s %s %s><img src="%s"/><span>&nbsp;%s</span></a>', 
                          $class, 
                          $id, 
                          $href,
                          $title,  
                          $image, 
                          $label);
        
        return $button;
    }
}


