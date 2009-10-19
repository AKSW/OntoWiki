<?php

require_once 'OntoWiki/Component/Helper.php';
require_once 'OntoWiki/Menu/Registry.php';
/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_graphicalquerybuilder
 */
class GraphicalquerybuilderHelper extends OntoWiki_Component_Helper
{
    public function __construct()
    {
        $owApp = OntoWiki_Application::getInstance();
        
        // if a model has been selected
        if (isset($owApp->selectedModel)) {
            // register with extras menu
            $translate  = $owApp->translate;
            $url        = new OntoWiki_Url(array('controller' => 'graphicalquerybuilder', 'action' => 'display'));
            $extrasMenu = OntoWiki_Menu_Registry::getInstance()->getMenu('application')->getSubMenu('Extras');
            //$extrasMenu->setEntry("Graphical Query Builder", (string) $url);
        }
    }
}
