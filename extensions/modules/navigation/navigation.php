<?php

/**
 * OntoWiki module – Navigation
 *
 * this is the main navigation module
 *
 * @category   OntoWiki
 * @package    extensions_modules_navigation
 * @author     Sebastian Dietzold <sebastian@dietzold.de>
 * @copyright  Copyright (c) 2009, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class NavigationModule extends OntoWiki_Module
{
    public function init() {
        $this->view->headScript()->appendFile($this->view->moduleUrl . 'navigation.js');
        $this->view->headLink()->appendStylesheet($this->view->moduleUrl . 'navigation.css');
    }

    /**
     * Returns the menu of the module
     *
     * @return string
     */
    public function getMenu() {
        // navigation type submenu
	$typeMenu = new OntoWiki_Menu();
        foreach ($this->_privateConfig->config as $key => $config) {
            $typeMenu->setEntry($config->name, "javascript:navigationSetParam('type', '$key')");
        }

        // count sub menu
	$countMenu = new OntoWiki_Menu();
        $countMenu->setEntry('10', "javascript:navigationSetParam('count', 10)")
            ->setEntry('20', "javascript:navigationSetParam('count', 20)")
            ->setEntry('30', "javascript:navigationSetParam('count', 30)")
            ->setEntry('all', "javascript:navigationSetParam('count', 'all')");

        // sort sub menu
        $sortTagcloud = new OntoWiki_Menu();
        $sortTagcloud->setEntry('by name', "javascript:navigationSetParam('sort', 'name')")
            ->setEntry('by frequency', "javascript:navigationSetParam('sort', 'frequency')");

        // view sub menu
        $viewMenu = new OntoWiki_Menu();
        $viewMenu->setEntry('Reset Navigation', 'javascript:navigationReset()')
             ->setEntry('Number of entries', $countMenu)
             ->setEntry('Sort', $sortTagcloud);

        // build menu out of sub menus
        $mainMenu = new OntoWiki_Menu();
        $mainMenu->setEntry('Type', $typeMenu);
        $mainMenu->setEntry('View', $viewMenu);

        return $mainMenu;
    }
    
    /**
     * Returns the content
     */
    public function getContents() {
        $data['config'] = $this->_privateConfig->config;
        $content = $this->render('navigation', $data, 'data');
        return $content;
    }
	
    public function shouldShow(){
        if (isset($this->_owApp->selectedModel)) {
            return true;
        } else {
            return false;
        }
    }
}


