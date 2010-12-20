<?php 
require_once 'OntoWiki/Plugin.php';

/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_plugins
 */
class BreadcrumbsPlugin extends OntoWiki_Plugin
{
    public function onDisplayMainWindowTitle($event)
    {
        return 'Title';
    }
}

