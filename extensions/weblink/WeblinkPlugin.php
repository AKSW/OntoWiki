<?php

/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_plugins
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
        try{
            if ( (array_key_exists($event->property, $this->_properties)) && (Zend_Uri::check($event->value) ) ){
                return '<a href="' . $event->value . '">' . OntoWiki_Utils::shorten($event->value, 60) . '</a>';
            }
        } catch(Exception $e){

        }
    }
}

