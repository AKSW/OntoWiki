<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki module – comment
 *
 * Allows to post a comment about a resource and show thes last activities refering to this resource
 *
 * @category   OntoWiki
 * @package    Extensions_Community
 * @author     Norman Heino <norman.heino@gmail.com>
 * @author     Sebastian Tramp <mail@sebastian.tramp.name>
 * @author     Natanael Arndt <arndtn@gmail.com>
 */
class CommentModule extends OntoWiki_Module
{
    public function init()
    {
    }

    public function getTitle()
    {
        return 'Latest Comments';
    }

    public function getContents()
    {
        $content = '';

        // comment form part
        if ((isset($this->_owApp->selectedModel))
            && ($this->_owApp->erfurt->getAc()->isModelAllowed('edit', $this->_owApp->selectedModel))
        ) {
            $limit = $this->_privateConfig->limit;
            $actionUrl = new OntoWiki_Url(array('controller' => 'community', 'action' => 'comment'), array());
            $listUrl = new OntoWiki_Url(array('controller' => 'community', 'action' => 'list'), array());
            $listUrl->setParam('climit', $limit, true);
            $this->view->actionUrl = (string)$actionUrl;
            $this->view->listUrl = (string)$listUrl;
            $this->view->context = $this->getContext();

            $content = $this->render('templates/comment');
        }

        if ($this->getContext() != 'main.window.community') {
            $helper = $this->_owApp->extensionManager->getComponentHelper('community');
            $comments = $helper->getList($this->view);

            if ($comments === null) {
                $this->view->infomessage = 'There are no discussions yet.';
            } else {
                $this->view->comments = $comments;
            }

            $content .= $this->render('templates/lastcomments');
        }

        return $content;
    }
}
