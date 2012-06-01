<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once 'OntoWiki/Module.php';

/**
 * OntoWiki module – lastcomments
 *
 * show last comments on resources of one knowledge base
 *
 * @category   OntoWiki
 * @package    Extensions_Community
 * @author     Sebastian Dietzold <dietzold@informatik.uni-leipzig.de>
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class LastcommentsModule extends OntoWiki_Module
{
    // TODO: use i18n in Module and template
    public function init()
    {
        // require_once 'OntoWiki/Model.php';
        $this->store       = $this->_owApp->erfurt->getStore();
        $this->model       = $this->_owApp->selectedModel;
        // The system config is used to get the user title
	// TODO: How can switch from ACL to Non-ACL use?
        $this->systemModel = new Erfurt_Rdf_Model('http://localhost/OntoWiki/Config/', 'http://localhost/OntoWiki/Config/');

        /* prepare schema elements */
        // TODO: This should be used from the CommunityController
        $aboutProperty   = $this->_privateConfig->about->property;
        $creatorProperty = $this->_privateConfig->creator->property;
        $commentType     = $this->_privateConfig->comment->type;
        $contentProperty = $this->_privateConfig->content->property;
        $dateProperty    = $this->_privateConfig->date->property;

        $realLimit       = $this->_privateConfig->limit + 1; // used for query to check for "more"

        // get the latest comments
        $commentSparql = 'SELECT DISTINCT ?resource ?author ?comment ?content ?date #?alabel
            WHERE {
                ?comment <' . $aboutProperty . '> ?resource.
                ?comment a <' . $commentType . '>.
                ?comment <' . $creatorProperty . '> ?author.
                ?comment <' . $contentProperty . '> ?content.
                ?comment <' . $dateProperty . '> ?date.
            }
            ORDER BY DESC(?date)
            LIMIT ' . $realLimit;

        #var_dump($commentSparql); die();
        require_once 'Erfurt/Sparql/SimpleQuery.php';
        $query = Erfurt_Sparql_SimpleQuery::initWithString($commentSparql);
        
		if($this->getContext() == "main.window.dashmodelinfo"){
			$this->results = Erfurt_App::getInstance()->getStore()->sparqlQuery($query);
		}else{
			$this->results = $this->model->sparqlQuery($query);
		}
    }
    
    public function getTitle()
    {
        return 'Latest Discussions';
    }
    
    public function getContents()
    {
        if ($this->results) {
            require_once 'OntoWiki/Resource.php';
            require_once 'OntoWiki/Utils.php';

            $comments = array();
            foreach ($this->results as $comment) {
                require_once 'OntoWiki/Resource.php';
                $comment['aresource'] = new OntoWiki_Resource((string) $comment['author'], $this->systemModel);
                $comment['author'] = $comment['aresource']->getTitle() ? $comment['aresource']->getTitle() : OntoWiki_Utils::getUriLocalPart($comment['author']);

                $comment['date'] = OntoWiki_Utils::dateDifference($comment['date'], null, 3);
                $comment['content'] = OntoWiki_Utils::shorten($comment['content'], 50);

                $comment['resource'] = new OntoWiki_Resource((string) $comment['resource'], $this->model);
                $comment['rname'] = $comment['resource']->getTitle() ? $comment['resource']->getTitle() : OntoWiki_Utils::contractNamespace($comment['resource']->getIri());

                $comments[] = $comment;
            }

            /* use only config limit comments */
            if (count($comments) > $this->_privateConfig->limit) {
                $comments = array_slice($comments , 0, $this->_privateConfig->limit);
                #$this->view->more = true;
                // TODO: add a link to the modul "see all comments"
            }

            $this->view->comments = $comments;
        } else {
            $this->view->infomessage = 'There are no discussions yet, but you can use the Community tab on any resource to create one.';
        }

        return $this->render('lastcomments');
    }
    
}


