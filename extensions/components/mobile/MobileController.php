<?php
require_once 'OntoWiki/Controller/Component.php';
require_once 'OntoWiki/Navigation.php';

class MobileController extends OntoWiki_Controller_Component {
    /**
     * Initialize and add dashboard to actions
     */
    public function init() {
        parent :: init();
        // disable standart view
        $this->_helper->layout()->disableLayout();
    }

    /**
     * Main action. Shows dashboards and fills it with data
     */
    public function initmobileAction() {
        $this->view->out = "echo 'ok_inside'";
        
        $this->addModuleContext('main.mobile');
        // Set window title
        //$this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('Mobile OntoWiki'));
		
        //$this->view->headLink()->appendStylesheet($this->_componentUrlBase . 'resources/plugin.css');
        //$this->view->headScript()->appendFile($this->_componentUrlBase . 'resources/data.js');
    }
}