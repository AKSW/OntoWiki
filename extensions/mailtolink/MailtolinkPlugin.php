<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * @category   OntoWiki
 * @package    Extensions_Mailtolink
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

