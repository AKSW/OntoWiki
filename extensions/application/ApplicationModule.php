<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki module – application
 *
 * Provides the OntoWiki application menu and a search field
 *
 * @category   OntoWiki
 * @package    Extensions_Application
 */
class ApplicationModule extends OntoWiki_Module
{
    public function init()
    {

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
        if (
            $this->_privateConfig->hideForAnonymousOnNoModels &&
            $this->_owApp->user->isAnonymousUser()
        ) {
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
            'actionUrl'       => $this->_config->urlBase . 'application/search/',
            'modelSelected'   => isset($this->_owApp->selectedModel),
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

        $content = $this->render('application', $data);

        return $content;
    }

    public function allowCaching()
    {
        // no caching
        return false;
    }
}
