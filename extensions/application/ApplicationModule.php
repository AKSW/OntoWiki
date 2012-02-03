<?php

/**
 * OntoWiki module – application
 *
 * Provides the OntoWiki application menu and a search field
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_application
 * @copyright  Copyright (c) 2010, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class ApplicationModule extends OntoWiki_Module
{   
    public function init(){
        /*$this->view->headScript()->appendScript('
        $(document).ready(function(){
            $("#applicationsearch input").keyup(function(e) {
                if(e.keyCode == 13) {
                    alert($(this).val());
                }
            });
        });
        ');*/
        
        $this->view->headScript()->appendFile($this->view->moduleUrl . 'modellist.js');

        $this->session = new Zend_Session_Namespace(_OWSESSION);
        //$this->allGraphUris = $this->_store->getAvailableModels(true);
        $this->visibleGraphUris = $this->_store->getAvailableModels(false);

        //if (isset($this->session->showHiddenGraphs) && $this->session->showHiddenGraphs == true) {
        //    $this->graphUris = $this->allGraphUris;
        //} else {
            $this->graphUris = $this->visibleGraphUris;
        //}
    }

    /**
     * Returns the title of the module
     *
     * @return string
     */
    public function getTitle()
    {
        $title = 'OntoWiki';
        
        if (!($this->_owApp->user instanceof Erfurt_Auth_Identity)) {
            return $title;
        }
                
        if ($this->_owApp->user->isOpenId() || $this->_owApp->user->isWebId()) {
            if ($this->_owApp->user->getLabel() !== '') {
                $userName = $this->_owApp->user->getLabel();
                $userName = OntoWiki_Utils::shorten($userName, 25);
            } else {
                $userName = OntoWiki_Utils::getUriLocalPart($this->_owApp->user->getUri());
                $userName = OntoWiki_Utils::shorten($userName, 25);
            }
        } else {
            if ($this->_owApp->user->getUsername() !== '') {
                $userName = $this->_owApp->user->getUsername();
                $userName = OntoWiki_Utils::shorten($userName, 25);
            } else {
                $userName = OntoWiki_Utils::getUriLocalPart($this->_owApp->user->getUri());
                $userName = OntoWiki_Utils::shorten($userName, 25);
            }
        }
        
        if (isset($userName) && $userName !== 'Anonymous') {
            $title .= ' (' . $userName . ')';
        }
        
        return $title;
    }

    /**
     * Maybe we should disable the app module in some case?
     *
     * @return string
     */
    public function shouldShow()
    {
        if ( ($this->_privateConfig->hideForAnonymousOnNoModels) &&
                 ($this->_owApp->user->isAnonymousUser()) ) {
            // show only if there are models (visible or hidden)
            if ($this->_store->getAvailableModels(true)) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * Returns the menu of the module
     *
     * @return string
     */
    public function getMenu()
    {
        return OntoWiki_Menu_Registry::getInstance()->getMenu('application');
    }
    
    /**
     * Returns the content for the model list.
     */
    public function getContents()
    {
        $data = array(
            'actionUrl'        => $this->_config->urlBase . 'application/search/',
            'modelSelected'    => isset($this->_owApp->selectedModel), 
            'searchtextinput' => $this->_request->getParam('searchtext-input')
        );
        
        if (null !== ($logo = $this->_owApp->erfurt->getStore()->getLogoUri())) {
            $data['logo']     = $logo;
            $data['logo_alt'] = 'Store Logo';
        }
        if ($this->_owApp->selectedModel) {
            $data['showSearch'] = true;
        } else {
            $data['showSearch'] = false;
        }
        
        $models = array();
        $selectedModel = $this->_owApp->selectedModel ? $this->_owApp->selectedModel->getModelIri() : null;

        $lang = $this->_config->languages->locale;

        $titleHelper = new OntoWiki_Model_TitleHelper();
        $titleHelper->addResources(array_keys($this->graphUris));

        foreach ($this->graphUris as $graphUri => $true) {
            $temp = array();
            $temp['url']      = $this->_config->urlBase . 'model/select/?m=' . urlencode($graphUri);
            $temp['graphUri'] = $graphUri;
            $temp['selected'] = ($selectedModel == $graphUri ? 'true' : '');

            // use URI if no title exists
            $label = $titleHelper->getTitle($graphUri, $lang);
            $temp['label'] = !empty($label) ? $label : $graphUri;

            $temp['backendName'] = $true;

            $models[] = $temp;
        }
        
        $data['models'] = $models;
        
        $content = $this->render('application', $data);
        
        return $content;
    }
    
    public function allowCaching()
    {
        // no caching
        return false;
    }
}


