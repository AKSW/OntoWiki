<?php
/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_plugins
 */
class MarkdownPlugin extends OntoWiki_Plugin {

    public function init() {
        $this->properties = array_values($this->_privateConfig->properties->toArray());
        $this->datatypes = array_values($this->_privateConfig->datatypes->toArray());
    }

    public function onDisplayLiteralPropertyValue($event) {
        if (in_array($event->property, $this->properties)) {
            require_once("parser/markdown.php");
            return Markdown($event->value);
        }
        if (in_array($event->datatype, $this->datatypes)) {
            require_once("parser/markdown.php");
            return Markdown($event->value);
        }
    }
}
