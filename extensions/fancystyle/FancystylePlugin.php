<?php
require_once 'OntoWiki/Plugin.php';
/**
 * Plugin to load a additional css with some fancy rounded corners and maybe more thats not release-friendly looking
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_plugins
 */
class FancystylePlugin extends OntoWiki_Plugin
{

    public function onAfterInitController($event)
    {
        $this->view->headLink()->appendStylesheet($this->_pluginUrlBase.'fancystyle.css');
    }
}
