<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
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

        $this->session = new Zend_Session_Namespace(_OWSESSION);
        $this->allGraphUris = $this->_store->getAvailableModels(true);
        $this->visibleGraphUris = $this->_store->getAvailableModels(false);

        if (isset($this->session->showHiddenGraphs) && $this->session->showHiddenGraphs == true) {
            $this->graphUris = $this->allGraphUris;
        } else {
            $this->graphUris = $this->visibleGraphUris;
        }
    }


    public function shouldShow()
    {
        // show only if there are models (visible or hidden)
        if (($this->allGraphUris) || ($this->_erfurt->getAc()->isActionAllowed('ModelManagement')) ) {
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
        $session = new Zend_Session_Namespace(_OWSESSION);
        if (!isset($session->showHiddenGraphs) || $session->showHiddenGraphs == false) {
            $viewMenu->setEntry('Show Hidden Knowledge Bases', array('class' => 'modellist_hidden_button show'));
        } else {
            $viewMenu->setEntry('Hide Hidden Knowledge Bases', array('class' => 'modellist_hidden_button'));
        }

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
        $models = array();
        $selectedModel = $this->_owApp->selectedModel ? $this->_owApp->selectedModel->getModelIri() : null;

        $lang = $this->_config->languages->locale;

        $titleHelper = new OntoWiki_Model_TitleHelper();
        $titleHelper->addResources(array_keys($this->graphUris));

        foreach ($this->graphUris as $graphUri => $true) {
            $temp = array();
            $temp['url']      = $this->_config->urlBase . 'model/select/?m=' . urlencode($graphUri);
            $temp['graphUri'] = $graphUri;
            $temp['selected'] = ($selectedModel == $graphUri ? 'selected' : '');

            // use URI if no title exists
            $label = $titleHelper->getTitle($graphUri, $lang);
            $temp['label'] = !empty($label) ? $label : $graphUri;

            $temp['backendName'] = $true;

            $models[] = $temp;
        }

        $content = $this->render('modellist', $models, 'models');

        return $content;
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

    public function getTitle() {
        return "Knowledge Bases";
    }

}
