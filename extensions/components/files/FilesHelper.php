<?php

require_once 'OntoWiki/Component/Helper.php';
require_once 'OntoWiki/Menu/Registry.php';
/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_files
 */
class FilesHelper extends OntoWiki_Component_Helper
{
    public function __construct()
    {
        $owApp = OntoWiki_Application::getInstance();
        // if a model has been selected
        if ($owApp->selectedModel != null) {
            // register with extras menu
            $translate  = $owApp->translate;
            $url        = new OntoWiki_Url(array('controller' => 'files', 'action' => 'manage'));
            $extrasMenu = OntoWiki_Menu_Registry::getInstance()->getMenu('application')->getSubMenu('Extras');
            $extrasMenu->setEntry($translate->_('File Manager'), (string) $url);
        }
    }
}
