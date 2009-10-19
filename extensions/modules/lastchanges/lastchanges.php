<?php

require_once 'OntoWiki/Module.php';

/**
 * OntoWiki module – lastchanges
 *
 * show last activities in a knowledge base and link to the resources
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_lastchanges
 * @author     Sebastian Dietzold <dietzold@informatik.uni-leipzig.de>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id:  $
 */
class LastchangesModule extends OntoWiki_Module
{
    public function init()
    {
        // enabling versioning
        $this->versioning = $this->_erfurt->getVersioning();
        if (!$this->versioning->isVersioningEnabled()) {
            $this->view->warningmessage = 'Versioning / history is currently disabled. This means, you can not see the latest changes.';
        } else {
			// The system config is used to get the user title
			// TODO: How can switch from ACL to Non-ACL use?
			$this->systemModel = new Erfurt_Rdf_Model('http://localhost/OntoWiki/Config/', 'http://localhost/OntoWiki/Config/');

			if($this->getContext() == "main.window.dashmodelinfo"){
				$this->user = $this->_erfurt->getAuth()->getIdentity()->getUri();
				$this->results = $this->versioning->getHistoryForUserDash($this->user);
			}else{
				$this->model = $this->_owApp->selectedModel;
				
				$this->results = $this->versioning->getConciseHistoryForGraph($this->model->getModelIri());
			}
        }
    }
    
    public function getTitle()
    {
        return 'Latest Changes';
    }
    
    public function getContents()
    {
        if ($this->results) {
			require_once 'OntoWiki/Model/Resource.php';
			require_once 'OntoWiki/Utils.php';
			
			$changes = array();
			foreach ($this->results as $change) {
			
				if($this->getContext() == "main.window.dashmodelinfo"){
					//id, resource, tstamp, action_type
					$change['useruri'] = $this->user;
					$this->model = null;
				}
			
				$change['aresource'] = new OntoWiki_Resource((string) $change['useruri'], $this->systemModel);
				$change['author'] = $change['aresource']->getTitle() ? $change['aresource']->getTitle() : OntoWiki_Utils::getUriLocalPart($change['aresource']);

					//$change['date'] = OntoWiki_Utils::dateDifference($change['tstamp'], null, 3);

				$change['resource'] = new OntoWiki_Resource((string) $change['resource'], $this->model);
				$change['rname'] = $change['resource']->getTitle() ? $change['resource']->getTitle() : OntoWiki_Utils::contractNamespace($change['resource']->getIri());

				$changes[] = $change;
			}
			$this->view->changes = $changes;
        } else {
            $this->view->infomessage = 'There are no changes yet.';
        }
		
        return $this->render('lastchanges');
    }
    
}


