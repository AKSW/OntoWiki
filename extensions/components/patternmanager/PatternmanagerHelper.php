<?php

/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_patternmanager
 */
class PatternmanagerHelper extends OntoWiki_Component_Helper
{
    public function __construct()
    {
        $owApp = OntoWiki::getInstance();
        // if a model has been selected
        if ($owApp->selectedModel != null) {
            // register with extras menu
            $translate  = $owApp->translate;
            $url        = new OntoWiki_Url(array('controller' => 'patternmanager', 'action' => 'index'),array());
            $url       .= '/index';
            $extrasMenu = OntoWiki_Menu_Registry::getInstance()->getMenu('application')->getSubMenu('Extras');
            $extrasMenu->setEntry($translate->_('Evolution Pattern Manager'), (string) $url);
        }
    }
}
