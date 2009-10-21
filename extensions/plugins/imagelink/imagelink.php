<?php

/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_plugins
 */
class ImagelinkPlugin extends OntoWiki_Plugin
{    
    public function onDisplayObjectPropertyValue($event)
    {
        if (in_array($event->property, $this->_privateConfig->properties->toArray(), true)) {
            return '<img class="object" src="' . $event->value . '" alt="image of ' . $event->value . '"/>';
        }
    }
    
    public function onDisplayLiteralPropertyValue($event)
    {
       if (in_array($event->property, $this->_privateConfig->properties->toArray(), true)) {
            return '<img class="object" src="' . $event->value . '" alt="image of ' . $event->value . '"/>';
        }
    }
}

