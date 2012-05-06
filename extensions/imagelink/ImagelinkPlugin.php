<?php

/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_plugins
 */
class ImagelinkPlugin extends OntoWiki_Plugin
{
    private $_properties = null;

    public function init()
    {
        $configValues = $this->_privateConfig->properties->toArray();
        $this->_properties = array_combine($configValues, $configValues);
        return $this->_properties;
    }

    public function onDisplayObjectPropertyValue($event)
    {
        if (isset($this->_properties[$event->property])) {
            return '<img class="object" src="' . $event->value . '" alt="image of ' . $event->value . '"/>';
        }
    }
    
    public function onDisplayLiteralPropertyValue($event)
    {
        if (isset($this->_properties[$event->property])) {
            return '<img class="object" src="' . $event->value . '" alt="image of ' . $event->value . '"/>';
        }
    }
}

