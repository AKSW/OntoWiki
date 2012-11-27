<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki module – bookmarklet
 *
 * Shows a bookmarklet link on model info
 *
 * @category OntoWiki
 * @package Extensions_Bookmarklet
 * @author Norman Heino <norman.heino@gmail.com>
 * @author Sebastian Tramp <tramp@informatik.uni-leipzig.de>
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class BookmarkletModule extends OntoWiki_Module
{
    public function getTitle()
    {
        return 'Bookmarklet';
    }

    public function getContents()
    {
        $this->view->infoMessage = 'Use this Bookmarklet to add content to this Knowledge Base.';
        $this->view->rdfAuthorBase = $this->_config->libraryUrlBase . 'RDFauthor/';
        $this->view->defaultGraph = (string)OntoWiki::getInstance()->selectedModel;
        $this->view->defaultUpdateService = $this->_config->urlBase . 'update/';
        $this->view->defaultQueryService = $this->_config->urlBase . 'sparql/';
        $this->view->ontoWikiUrl = $this->_config->urlBase;
        
        $frontController = Zend_Controller_Front::getInstance();
        $request = $frontController->getRequest();
        
        return $this->render('bookmarklet');
    }

    public function shouldShow(){
        // do not show if model is not writeable

        if ( $this->_owApp->selectedModel->isEditable() ) {
            return true;
        } else {
            return false;
        }
    }

}

