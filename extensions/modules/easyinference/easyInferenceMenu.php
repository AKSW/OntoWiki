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

class EasyInferenceMenu extends OntoWiki_Menu_Registry {

    public function __construct() {

       /* $moduleMenu = new OntoWiki_Menu();
        $moduleMenu->setEntry('Add', 'javascript:tabTo(\'add\');');
        $moduleMenu->setEntry('Delete', 'javascript:tabTo(\'delete\');');
        $moduleMenu->setEntry('Generate', 'javascript:tabTo(\'generate\');');
        $moduleMenu->setEntry('Status', 'javascript:tabTo(\'activate\');');

        $button = new OntoWiki_Menu();
        $button->appendEntry('Action', $moduleMenu);

        $this->setMenu('easyInfernceMenu', $button);*/

        $ruleMenu = new OntoWiki_Menu();
        $ruleMenu->setEntry('Add', 'javascript:tabTo(\'add\');');
        $ruleMenu->setEntry('Delete', 'javascript:tabTo(\'delete\');');
        $button1 = new OntoWiki_Menu();
        $button1->appendEntry('Manage Rules', $ruleMenu);

        $activate = new OntoWiki_Menu();
        $activate->setEntry('Generate', 'javascript:tabTo(\'generate\');');
        $activate->setEntry('Status', 'javascript:tabTo(\'activate\');');

        $button1->appendEntry('Activate', $activate);
        

        $this->setMenu('easyInfernceMenu', $button1);
    }


}


