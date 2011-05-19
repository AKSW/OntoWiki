<?php

/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_patternmanager
 */
require_once 'classes/PatternEngine.php';

class PatternmanagerHelper extends OntoWiki_Component_Helper
{
    public function __construct()
    {
        // menu is only visible if user is at least allowed to view the pattern
        $ac = Erfurt_App::getInstance()->getAc();
        if ($ac->isActionAllowed(PatternEngineAc::RIGHT_VIEW_STR)) {
            $owApp = OntoWiki::getInstance();

            // register with extras menu
            $translate  = $owApp->translate;
            $url        = new OntoWiki_Url(array('controller' => 'patternmanager', 'action' => 'index'),array());
            $url       .= '/index';
            $extrasMenu = OntoWiki_Menu_Registry::getInstance()->getMenu('application')->getSubMenu('Extras');
            $extrasMenu->setEntry($translate->_('Evolution Pattern Manager'), (string) $url);
        }
    }
}
