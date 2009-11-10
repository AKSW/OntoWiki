<?php

require_once 'OntoWiki/Component/Helper.php';
require_once 'OntoWiki/Menu/Registry.php';
/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_querybuilding
 */
class QuerybuildingHelper extends OntoWiki_Component_Helper
{
    public function __construct()
    {	
        $owApp = OntoWiki::getInstance();
        
        // if a model has been selected
        if ($owApp->selectedModel) {
            // register with extras menu
            $extrasMenu = OntoWiki_Menu_Registry::getInstance()->getMenu('application')->getSubMenu('Extras');
            $extrasMenu->setEntry('Query Building', $owApp->config->urlBase . 'querybuilding/listquery');
            $extrasMenu->removeEntry('SPARQL Query Editor'); // now not need anymore
        }
    }
}
