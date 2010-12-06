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

    public function getTitle() {
        return "Navigation";
    }


    /**
     * Returns the menu of the module
     *
     * @return string
     */
    public function getMenu() {
		// check if menu must be shown
		if(!$this->_privateConfig->defaults->showMenu) return new OntoWiki_Menu();
		
        // build main menu (out of sub menus below)
        $mainMenu = new OntoWiki_Menu();

        // edit sub menu
        if ($this->_owApp->erfurt->getAc()->isModelAllowed('edit', $this->_owApp->selectedModel) ) {
            $editMenu = new OntoWiki_Menu();
            $editMenu->setEntry('Add Resource here', "javascript:navigationAddElement()");
            $mainMenu->setEntry('Edit', $editMenu);
        }

        // count sub menu
        $countMenu = new OntoWiki_Menu();
        $countMenu->setEntry('10', "javascript:navigationEvent('setLimit', 10)")
            ->setEntry('20', "javascript:navigationEvent('setLimit', 20)")
            ->setEntry('30', "javascript:navigationEvent('setLimit', 30)");

        // toggle sub menu
        $toggleMenu = new OntoWiki_Menu();
        // hidden elements
        $toggleMenu->setEntry('Hidden Elements', "javascript:navigationEvent('toggleHidden')");
        // empty elements
        $toggleMenu->setEntry('Empty Elements', "javascript:navigationEvent('toggleEmpty')");
        // implicit
        $toggleMenu->setEntry('Implicit Elements', "javascript:navigationEvent('toggleImplicit')");

        // view sub menu
        $viewMenu = new OntoWiki_Menu();
        $viewMenu->setEntry('Number of Elements', $countMenu);
        $viewMenu->setEntry('Toggle Elements', $toggleMenu);
        $viewMenu->setEntry('Reset Navigation', "javascript:navigationEvent('reset')");
        $mainMenu->setEntry('View', $viewMenu);

        // navigation type submenu
        /*$sortMenu = new OntoWiki_Menu();
        foreach ($this->_privateConfig->sorting as $key => $config) {
            $sortMenu->setEntry($config->name, "javascript:navigationEvent('setSort', '$config->type')");
        }
        $mainMenu->setEntry('Sort', $sortMenu);*/

        // navigation type submenu
        $typeMenu = new OntoWiki_Menu();
        foreach ($this->_privateConfig->config as $key => $config) {
            if($this->_privateConfig->defaults->checkTypes){
                if(isset($config->checkVisibility) && $config->checkVisibility == false){
                    $typeMenu->setEntry($config->name, "javascript:navigationEvent('setType', '$key')");
                }else if( $this->checkConfig($config) > 0 ){
                    $typeMenu->setEntry($config->name, "javascript:navigationEvent('setType', '$key')");
                }
            }else{
                $typeMenu->setEntry($config->name, "javascript:navigationEvent('setType', '$key')");
            }
        }
        $mainMenu->setEntry('Type', $typeMenu);

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
        
        $sessionKey = 'Navigation' . (isset($config->session->identifier) ? $config->session->identifier : '');        
        $stateSession = new Zend_Session_Namespace($sessionKey);
        if( isset($stateSession) && ( $stateSession->model == (string)$this->_owApp->selectedModel ) ){
            // load setup
            $this->view->inlineScript()->prependScript(
                '/* from modules/navigation/ */'.PHP_EOL.
                'var navigationStateSetupString = \''.$stateSession->setup.'\';'.PHP_EOL.
                'var navigationStateSetup = $.evalJSON(navigationStateSetupString);' .PHP_EOL
            );
            // load view
            $this->view->stateView = $stateSession->view;
            // set js actions
            $this->view->inlineScript()->prependScript(
                '$(document).ready(function() { navigationPrepareList(); } );'.PHP_EOL
            );
        }
        
        // init view from scratch
        $this->view->inlineScript()->prependScript(
            '$(document).ready(function() { navigationEvent(\'init\'); } );'.PHP_EOL
        );

        $data['session'] = $this->session->navigation;
        $content = $this->render('navigation', $data, 'data'); // 
        return $content;
    }
	
    public function shouldShow(){
        if (isset($this->_owApp->selectedModel)) {
            return true;
        } else {
            return false;
        }
    }

    private function checkConfig($config){
        $resVar = new Erfurt_Sparql_Query2_Var('resourceUri');
        $typeVar = new Erfurt_Sparql_Query2_IriRef(EF_RDF_TYPE);

        $query = new Erfurt_Sparql_Query2();
        $query->addProjectionVar($resVar)->setDistinct(true);

        $union = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
        foreach ($config->hierarchyTypes as $type) {
            $u1 = new Erfurt_Sparql_Query2_GroupGraphPattern();
            $u1->addTriple( $resVar,
                $typeVar,
                new Erfurt_Sparql_Query2_IriRef($type)
            );
            $union->addElement($u1);
        }
        $query->addElement($union);
        $query->setLimit(1);

        $all_results = $this->_owApp->selectedModel->sparqlQuery($query);
        /*$this->_owApp->logger->info(
            'Navigation Query: ' .PHP_EOL . $query->__toString()
        );
        $this->_owApp->logger->info(
            'Navigation Query Results: ' .PHP_EOL . print_r($all_results)
        );*/

        return count($all_results);
    }
}


