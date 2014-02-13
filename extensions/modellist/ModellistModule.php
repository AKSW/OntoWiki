<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011-2016, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki module – modellist
 *
 * Shows a list of all models in a store
 *
 * @category   OntoWiki
 * @package    Extensions_Modellist
 * @author     Norman Heino <norman.heino@gmail.com>
 * @author     Philipp Frischmuth <pfrischmuth@googlemail.com>
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class ModellistModule extends OntoWiki_Module
{
    public function init()
    {
        $this->view->headScript()->appendFile($this->view->moduleUrl . 'modellist.js');

        $menuRegistry = OntoWiki_Menu_Registry::getInstance();
        $menuRegistry->getMenu('application')->getSubMenu('View')->setEntry('Hide Knowledge Bases Box', '#');
    }


    public function shouldShow()
    {
        // show only if there are models (visible or hidden)
        if ($this->_erfurt->getAc()->isActionAllowed('ModelManagement')) {
            return true;
        }

        return false;
    }

    /**
     * Returns the menu of the module
     *
     * @return string
     */
    public function getMenu()
    {
        if ($this->_erfurt->getAc()->isActionAllowed('ModelManagement')) {
            $editMenu = new OntoWiki_Menu();
            $editMenu->setEntry('Create Knowledge Base', $this->_config->urlBase . 'model/create');
        }

        $viewMenu = new OntoWiki_Menu();
        $session  = new Zend_Session_Namespace(_OWSESSION);
        if (!isset($session->showHiddenGraphs) || $session->showHiddenGraphs == false) {
            $viewMenu->setEntry('Show Hidden Knowledge Bases', array('class' => 'modellist_hidden_button show'));
        } else {
            $viewMenu->setEntry('Hide Hidden Knowledge Bases', array('class' => 'modellist_hidden_button'));
        }
        $viewMenu->setEntry('Reset Knowledge Bases', array('class' => 'modellist_reset_button'));

        // build menu out of sub menus
        $mainMenu = new OntoWiki_Menu();

        if (isset($editMenu)) {
            $mainMenu->setEntry('Edit', $editMenu);
        }
        $mainMenu->setEntry('View', $viewMenu);

        return $mainMenu;
    }

    /**
     * Returns the content for the model list.
     */
    public function getContents()
    {
        $sessionKey   = 'Modellist' . (isset($config->_session->identifier) ? $config->_session->identifier : '');
        $stateSession = new Zend_Session_Namespace($sessionKey);
        if (isset($stateSession) && isset($stateSession->setup)) {
            // load setup
            $this->view->inlineScript()->prependScript(
                '/* from modules/modellist/ */' . PHP_EOL .
                'var modellistStateSetup = ' . $stateSession->setup . ';' . PHP_EOL
            );
            // load view
            $this->view->stateView = $stateSession->view;
        }

        return $this->render('modellist', null, 'models');
    }

    public function getStateId()
    {
        $session = new Zend_Session_Namespace(_OWSESSION);
        if (isset($session->showHiddenGraphs) && $session->showHiddenGraphs == true) {
            $showHidden = 'true';
        } else {
            $showHidden = 'false';
        }

        $id = (string)$this->_owApp->getUser()->getUri()
            . $this->_owApp->selectedModel
            . $showHidden;

        return $id;
    }

    public function getTitle()
    {
        return "Knowledge Bases";
    }

}
