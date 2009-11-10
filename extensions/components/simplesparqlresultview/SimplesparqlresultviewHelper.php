<?php

require_once 'OntoWiki/Component/Helper.php';
require_once 'OntoWiki/Menu/Registry.php';
require_once 'OntoWiki/Menu.php';
/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_files
 */
class SimplesparqlresultviewHelper extends OntoWiki_Component_Helper
{
    public function __construct()
    {
        $owApp = OntoWiki::getInstance();
        // if a model has been selected
        if ($owApp->selectedModel != null) {
        }
    }
}
