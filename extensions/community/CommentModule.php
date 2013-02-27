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
 * @author     Sebastian Tramp <tramp@informatik.uni-leipzig.de>
 * @author     Natanael Arndt <arndtn@gmail.com>
 */
class CommentModule extends OntoWiki_Module
{
    public function init()
    {
        $this->store = $this->_owApp->erfurt->getStore();
        $this->model = $this->_owApp->selectedModel;

        // The system config is used to get the user title
        // TODO: How can switch from ACL to Non-ACL use?
        $systemModelUri = 'http://localhost/OntoWiki/Config/';
        $this->systemModel = new Erfurt_Rdf_Model($systemModelUri, $systemModelUri);

        /* prepare schema elements */
        // TODO: This should be used from the CommunityController
        $aboutProperty   = $this->_privateConfig->about->property;
        $creatorProperty = $this->_privateConfig->creator->property;
        $commentType     = $this->_privateConfig->comment->type;
        $contentProperty = $this->_privateConfig->content->property;
        $dateProperty    = $this->_privateConfig->date->property;

        $realLimit = $this->_privateConfig->limit + 1; // used for query to check for "more"

        // get the latest comments
        $commentSparql
            = 'SELECT DISTINCT ?resource ?author ?comment ?content ?date #?alabel
            WHERE {
                ?comment <' . $aboutProperty . '> ?resource.
                ?comment a <' . $commentType . '>.
                ?comment <' . $creatorProperty . '> ?author.
                ?comment <' . $contentProperty . '> ?content.
                ?comment <' . $dateProperty . '> ?date.
            }
            ORDER BY DESC(?date)
            LIMIT ' . $realLimit;

        $query = Erfurt_Sparql_SimpleQuery::initWithString($commentSparql);

        if ($this->getContext() == "main.window.dashmodelinfo") {
            $this->results = Erfurt_App::getInstance()->getStore()->sparqlQuery($query);
        } else {
            $this->results = $this->model->sparqlQuery($query);
        }
    }

    public function getTitle()
    {
        return 'Comment';
    }

    public function getContents()
    {
        if ($this->getContext() != 'main.window.community') {
            // output last comments
            if ($this->results) {
                $comments = array();
                foreach ($this->results as $comment) {
                    $comment['aresource'] = new OntoWiki_Resource(
                        (string)$comment['author'], $this->systemModel
                    );
                    if ($comment['aresource']->getTitle()) {
                        $comment['author'] = $comment['aresource']->getTitle();
                    } else {
                        OntoWiki_Utils::getUriLocalPart($comment['author']);
                    }

                    $comment['date']    = OntoWiki_Utils::dateDifference($comment['date'], null, 3);
                    $comment['content'] = OntoWiki_Utils::shorten($comment['content'], 50);

                    $comment['resource'] = new OntoWiki_Resource(
                        (string)$comment['resource'], $this->model
                    );
                    if ($comment['resource']->getTitle()) {
                        $comment['rname'] = $comment['resource']->getTitle();
                    } else {
                        OntoWiki_Utils::contractNamespace($comment['resource']->getIri());
                    }

                    $comments[] = $comment;
                }

                /* use only config limit comments */
                if (count($comments) > $this->_privateConfig->limit) {
                    $comments = array_slice($comments, 0, $this->_privateConfig->limit);
                    // TODO: add a link to the modul "see all comments"
                }

                $this->view->comments = $comments;
            } else {
                $this->view->infomessage
                    = 'There are no discussions yet, but you can use the Community tab on any resource to create one.';
            }

            $content = $this->render('templates/lastcomments');
        } else {
            $content = '';
        }

        // comment form part
        if ((isset($this->_owApp->selectedModel))
            && ($this->_owApp->erfurt->getAc()->isModelAllowed('edit', $this->_owApp->selectedModel))
        ) {
            $url = new OntoWiki_Url(array('controller' => 'community', 'action' => 'comment'), array());
            $this->view->actionUrl = (string)$url;

            $content .= $this->render('templates/comment');
        }

        return $content;
    }
}

