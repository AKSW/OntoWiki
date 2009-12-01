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
    protected $session = null;

    public function init() {
        $this->session = $this->_owApp->session;
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
            $typeMenu->setEntry($config->name, "javascript:navigationEvent('setType', '$key')");
        }

        // count sub menu
        $countMenu = new OntoWiki_Menu();
        $countMenu->setEntry('10', "javascript:navigationEvent('setLimit', 10)")
            ->setEntry('20', "javascript:navigationEvent('setLimit', 20)")
            ->setEntry('30', "javascript:navigationEvent('setLimit', 30)");

        // edit sub menu
        $toggleMenu = new OntoWiki_Menu();
        $toggleMenu->setEntry('Hidden Elements', "javascript:navigationEvent('toggleHidden')");
        $toggleMenu->setEntry('Empty Elements', "javascript:navigationEvent('toggleEmpty')");
        $toggleMenu->setEntry('Implicit Elements', "javascript:navigationEvent('toggleImplicit')");

        // view sub menu
        $viewMenu = new OntoWiki_Menu();
        $viewMenu->setEntry('Type', $typeMenu);
        $viewMenu->setEntry('Number of Elements', $countMenu);
        $viewMenu->setEntry('Toggle Elements', $toggleMenu);
        $viewMenu->setEntry('Reset Navigation', "javascript:navigationEvent('reset')");

        // edit sub menu
        $editMenu = new OntoWiki_Menu();
        $editMenu->setEntry('Add Element', "javascript:navigationEvent('addElement')");

        // build menu out of sub menus
        $mainMenu = new OntoWiki_Menu();
        $mainMenu->setEntry('View', $viewMenu);
        $mainMenu->setEntry('Edit', $editMenu);

        return $mainMenu;
    }
    
    /**
     * Returns the content
     */
    public function getContents() {
        // scripts and css only if module is visible
        $this->view->headScript()->appendFile($this->view->moduleUrl . 'navigation.js');
        $this->view->headLink()->appendStylesheet($this->view->moduleUrl . 'navigation.css');

        // this gives the complete config array as json to the javascript parts
        $this->view->inlineScript()->prependScript(
            '/* from modules/navigation/ */'.PHP_EOL.
            'var navigationConfigString = \''.
            json_encode($this->_privateConfig->toArray()) . '\'' .PHP_EOL.
            'var navigationConfig = $.evalJSON(navigationConfigString);' .PHP_EOL
        );
        // this gives the navigation session config to the javascript parts
        if ($this->session->navigation) {
            $this->view->inlineScript()->prependScript(
                '/* from modules/navigation/ */'.PHP_EOL.
                'var navigationConfig = $.evalJSON(\''.
                json_encode($this->_privateConfig->toArray()) . '\');' .PHP_EOL
            );
        }
        
        $stateSession = new Zend_Session_Namespace("NavigationState");
        if( isset($stateSession) ){
            if ( $stateSession->model == (string)$this->_owApp->selectedModel ) {
                // this gives the navigation session config to the javascript parts
                $this->view->inlineScript()->prependScript(
                    '/* from modules/navigation/ */'.PHP_EOL.
                    'var navigationStateSetupString = \''.$stateSession->setup.'\';'.PHP_EOL.
                    'var navigationStateSetup = $.evalJSON(navigationStateSetupString);' .PHP_EOL
                );
            }
        }

        $data['session'] = $this->session->navigation;
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


