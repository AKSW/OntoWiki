<?php

require_once 'OntoWiki/Component/Helper.php';
require_once 'OntoWiki/Menu/Registry.php';
/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_plugins
 */
class PluginsHelper extends OntoWiki_Component_Helper
{
    public function __construct()
    {
        $owApp = OntoWiki_Application::getInstance();

        // register with extras menu only if user is allowed to use the PluginManager
        if ($owApp->erfurt->isActionAllowed('PluginManagement')){
          $translate  = $owApp->translate;
          $url        = new OntoWiki_Url(array('controller' => 'plugins', 'action' => 'categories'));
          $extrasMenu = OntoWiki_Menu_Registry::getInstance()->getMenu('application')->getSubMenu('Extras');
          $extrasMenu->setEntry($translate->_('Plugin Manager'), (string) $url);
        }
    }
}
