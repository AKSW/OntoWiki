<?php

/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_plugins
 */
class MarkdownPlugin extends OntoWiki_Plugin
{

	protected $_config = null;

    public function init()
    {
        $this->properties = array_values($this->_privateConfig->properties->toArray());
    }

    public function onDisplayLiteralPropertyValue($event)
    {
		if (in_array($event->property, $this->properties)) {
			require_once("parser/markdown.php");
			return Markdown($event->value);
		}
    }
}
