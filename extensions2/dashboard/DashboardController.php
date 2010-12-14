<?php
require_once 'OntoWiki/Controller/Component.php';
require_once 'OntoWiki/Navigation.php';

class DashboardController extends OntoWiki_Controller_Component {
    /**
     * Initialize and add dashboard to actions
     */
    public function init() {
        parent :: init();

        // disable tabs
		OntoWiki_Navigation::disableNavigation();
    }

    /**
     * Main action. Shows dashboards and fills it with data
     */
    public function showdashAction() {
		// check if user is logged on
		$this->view->anon = false;
        $user = OntoWiki::getInstance()->erfurt->getAuth()->getIdentity();
        // if a model has been selected
        if ($user->isAnonymousUser()){
			// Set window title
			$this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('Error!'));
			$this->view->anon = true;
			return 0;
		}
	
        // Set window title
        $this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('Dashboard'));
		
		$this->view->headLink()->appendStylesheet($this->_componentUrlBase . 'resources/plugin.css');
		$this->view->headScript()->appendFile($this->_componentUrlBase . 'resources/data.js');
		
		$this->addModuleContext('main.window.dashmodelinfo');
    }
}