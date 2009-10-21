<?php

/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_plugins
 */
class MailtolinkPlugin extends OntoWiki_Plugin
{   
    public function onDisplayObjectPropertyValue($event)
    {
        if (!strstr($event->value, 'mailto:')) {
            $mailUri = 'mailto:' . $event->value;
        } else {
            $mailUri = $event->value;
        }
        if (in_array($event->property, $this->_privateConfig->properties->toArray(), true)) {
            return '<a href="' . $mailUri . '">' . $event->value . '</a>';
        }
    }
    
    public function onDisplayLiteralPropertyValue($event)
    {
        if (!strstr($event->value, 'mailto:')) {
            $mailUri = 'mailto:' . $event->value;
        } else {
            $mailUri = $event->value;
        }
        if (in_array($event->property, $this->_privateConfig->properties->toArray(), true)) {
            return '<a href="' . $mailUri . '">' . $event->value . '</a>';
        }
    }
}

