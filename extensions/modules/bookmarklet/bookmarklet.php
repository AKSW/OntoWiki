<?php
/**
 * OntoWiki module – bookmarklet
 *
 * Shows a bookmarklet link on model info
 *
 * @category OntoWiki
 * @package OntoWiki_Extensions_Modules_Bookmarklet
 * @author Sebastian Dietzold <dietzold@informatik.uni-leipzig.de>
 *  @author Norman Heino <norman.heino@gmail.com>
 * @copyright Copyright (c) 2009, {@link http://aksw.org AKSW}
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
        $this->view->ontoWikiUrl = $this->_config->urlBase;
        
        $frontController = Zend_Controller_Front::getInstance();
        $request = $frontController->getRequest();
        
        return $this->render('bookmarklet');
    }
}

