<?php

require_once 'OntoWiki/Menu.php';
require_once 'OntoWiki/Menu/Registry.php';

/**
 * OntoWiki menu – easyInferenceMenu
 *
 * show menu of easyinferncemodule
 *
 * @package    easyinference
 * @author     swp-09-7
 */

class EasyInferenceMenu extends OntoWiki_Menu_Registry
{
    
    public function __construct()
    {
        $this->setMenu('easyInfernceMenu', $this->_getEasyInfernceMenu());
    }
    
    public function _getEasyInfernceMenu()
    {        
    	$moduleMenu = new OntoWiki_Menu();
        $moduleMenu->setEntry('Add', "javascript:tabTo('add');");
        $moduleMenu->setEntry('Delete', "javascript:tabTo('delete');");
        $moduleMenu->setEntry('Generate', "javascript:tabTo('generate');");
        $moduleMenu->setEntry('Status', "javascript:tabTo('activate');");
        
        return $moduleMenu;
    }
}


