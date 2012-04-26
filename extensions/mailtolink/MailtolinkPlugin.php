<?php

/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_plugins
 */
class MailtolinkPlugin extends OntoWiki_Plugin
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
        if (!substr($event->value, 0, 7) === 'mailto:') {
            $mailUri = 'mailto:' . $event->value;
        } else {
            $mailUri = $event->value;
        }
        if (isset($this->_properties[$event->property])) {
            return '<a href="' . $mailUri . '">' . $event->value . '</a>';
        }
    }
    
    public function onDisplayLiteralPropertyValue($event)
    {
        if (!substr($event->value, 0, 7) === 'mailto:') {
            $mailUri = 'mailto:' . $event->value;
        } else {
            $mailUri = $event->value;
        }
        if (isset($this->_properties[$event->property])) {
            return '<a href="' . $mailUri . '">' . $event->value . '</a>';
        }
    }
}

