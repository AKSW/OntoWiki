<?php 

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

 /**
  * CSV Importer component helper.
  *
  * @category OntoWiki
  * @package Extensions
  * @subpackage Csvimport
  * @copyright Copyright (c) 2010, {@link http://aksw.org AKSW}
  * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
  */
class CsvimportHelper extends OntoWiki_Component_Helper
{
    public function init()
    {
        // TODO: register menu entry
        $menuRegistry = '';

        //Adding MenuEntries on the basis of the private plugin configuration
        $appMenu    = OntoWiki_Menu_Registry::getInstance()->getMenu('application');
        $extrasMenu = $appMenu->getSubMenu('Extras');                    
        #$extrasMenu->appendEntry( 
        #            $this->_owApp->translate->_('Import CSV Data', $this->_owApp->config->languages->locale),
        #            new OntoWiki_Url(array('controller' => 'csvimport'), array() )
        #);





    }
    
    public function onCreateMenu($event)
    {
        if ($event->isModel) {
            $url = new OntoWiki_Url(array('controller' => 'csvimport'), array());
            $menu = $event->menu;
            $menu->appendEntry('Import CSV Data', (string) $url);
        }
    }
}
