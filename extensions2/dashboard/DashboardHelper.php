<?php

require_once 'OntoWiki/Component/Helper.php';
require_once 'OntoWiki/Menu/Registry.php';
/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_querybuilding
 */
class DashboardHelper extends OntoWiki_Component_Helper
{
    public function __construct()
    {	
        $owApp = OntoWiki::getInstance();
        $user = $owApp->erfurt->getAuth()->getIdentity();
        // if a model has been selected
        if (!$user->isAnonymousUser()) {
            // register with extras menu
            $extrasMenu = OntoWiki_Menu_Registry::getInstance()->getMenu('application')->getSubMenu('Extras');
            $extrasMenu->setEntry('Dashboard', $owApp->config->urlBase . 'dashboard/showdash');
        }
    }
}
