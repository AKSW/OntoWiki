<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * @category   OntoWiki
 * @package    Extensions_Weblink
 */
class WeblinkPlugin extends OntoWiki_Plugin
{
    protected $_config = null;

    public function init()
    {
        $this->_config = OntoWiki::getInstance()->config;
        
        $properties = array_values($this->_privateConfig->weblink->toArray());
        $this->_properties = array_combine($properties, $properties);
    }
    
    public function onDisplayObjectPropertyValue($event)
    {
        if (array_key_exists($event->property, $this->_properties)) {
            return '<a resource="'. $event->value .'" class="hasMenu" href="' . $event->value . '">' . OntoWiki_Utils::shorten($event->value, 60) . '</a>';
        }
    }
    
    public function onDisplayLiteralPropertyValue($event)
    {
        if (array_key_exists($event->property, $this->_properties) && Erfurt_Uri::check($event->value)) {
            return '<a href="' . $event->value . '">' . OntoWiki_Utils::shorten($event->value, 60) . '</a>';
        }
    }
}

