<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * History component controller.
 * 
 * @category   OntoWiki
 * @package    Extensions_History
 * @author     Christoph Rieß <c.riess.dev@googlemail.com>
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class HistoryController extends OntoWiki_Controller_Component
{
    public function feedAction()
    {
        $model       = $this->_owApp->selectedModel;
        $resource    = $this->_owApp->selectedResource;
        $limit       = 20;
        $rUri        = (string)$resource;
        $rUriEncoded = urlencode($rUri);
        $mUri        = (string)$model;
        $mUriEncoded = urlencode($mUri);
        $translate   = $this->_owApp->translate;

        $store       = $this->_erfurt->getStore();

        $ac          = $this->_erfurt->getAc();
        $params      = $this->_request->getParams();

        if (!$model || !$resource) {
            throw new Ontowiki_Exception('need parameters m and r');
        }

        $versioning = $this->_erfurt->getVersioning();
        $versioning->setLimit($limit);
        if (!$versioning->isVersioningEnabled()) {
            throw new Ontowiki_Exception('versioning disabled in config');
        }

        $title = $resource->getTitle();
        if (null == $title) {
            $title = OntoWiki_Utils::contractNamespace($resource->getIri());
        }
        $feedTitle = sprintf($translate->_('Versions for %1$s'), $title);

        $historyArray = $versioning->getHistoryForResource((string)$resource, (string)$model, 1);

        $idArray = array();
        $userArray = $this->_erfurt->getUsers();
        $titleHelper = new OntoWiki_Model_TitleHelper();
        // Load IDs for rollback and Username Labels for view
        foreach ($historyArray as $key => $entry) {
            $idArray[] = (int) $entry['id'];
            // if(!$singleResource){
            //    $historyArray[$key]['url'] = $this->_config->urlBase . "view?r=" . urlencode($entry['resource']);
            //    $titleHelper->addResource($entry['resource']);
            // }
            if ($entry['useruri'] == $this->_erfurt->getConfig()->ac->user->anonymousUser) {
                $userArray[$entry['useruri']] = 'Anonymous';
            } elseif ($entry['useruri'] == $this->_erfurt->getConfig()->ac->user->superAdmin) {
                $userArray[$entry['useruri']] = 'SuperAdmin';
            } elseif (
                is_array($userArray[$entry['useruri']]) &&
                array_key_exists('userName', $userArray[$entry['useruri']])
            ) {
                $userArray[$entry['useruri']] = $userArray[$entry['useruri']]['userName'];
            }
        }

        $linkUrl = $this->_config->urlBase . "history/list?r=$rUriEncoded&mUriEncoded";
        $feedUrl = $this->_config->urlBase . "history/feed?r=$rUriEncoded&mUriEncoded";
        $feed = new Zend_Feed_Writer_Feed();
        $feed->setTitle($feedTitle);
        $feed->setLink($linkUrl);
        $feed->setFeedLink($feedUrl, 'atom');
        //$feed->addHub("http://pubsubhubbub.appspot.com/");
        $feed->addAuthor(
            array(
                'name' => 'OntoWiki',
                'uri'  => $feedUrl
            )
        );
        $feed->setDateModified(time());

        foreach ($historyArray as $historyItem) {
            $title = $translate->_('HISTORY_ACTIONTYPE_'.$historyItem['action_type']);

            $entry = $feed->createEntry();
            $entry->setTitle($title);
            $entry->setLink($this->_config->urlBase . 'view?r='.$rUriEncoded."&id=".$historyItem['id']);
            $entry->addAuthor(
                array(
                    'name' => $userArray[$historyItem['useruri']],
                    'uri'  => $historyItem['useruri']
                )
            );

            $entry->setDateModified($historyItem['tstamp']);
            $entry->setDateCreated($historyItem['tstamp']);
            $entry->setDescription($title);

            $content = '';
            $result = $this->getActionTriple($historyItem['id']);
            $content .= json_encode($result);

            $entry->setContent(htmlentities($content));

            $feed->addEntry($entry);
        }

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $this->getResponse()->setHeader('Content-Type', 'application/atom+xml');

        $out = $feed->export('atom');

        $pattern = '/updated>\n(.+?)link rel="alternate"/';
        $replace = "updated>\n$1link";
        $out = preg_replace($pattern, $replace, $out);

        echo $out;

        return;

        // Do we need this stuff below?
        // ----------------------------
        /* $this->view->userArray = $userArray;
        $this->view->idArray = $idArray;
        $this->view->historyArray = $historyArray;
        $this->view->singleResource = $singleResource;
        $this->view->titleHelper = $titleHelper;

        if (empty($historyArray)) {
            $this->_owApp->appendMessage(
                new OntoWiki_Message(
                    'No matches.',
                    OntoWiki_Message::INFO
                )
            );
        }

        if ($this->_erfurt->getAc()->isActionAllowed('Rollback')) {
            $this->view->rollbackAllowed = true;
            // adding submit button for rollback-action
            $toolbar = $this->_owApp->toolbar;
            $toolbar->appendButton(
                OntoWiki_Toolbar::SUBMIT,
                array('name' => $translate->_('Rollback changes'), 'id' => 'history-rollback')
            );
            $this->view->placeholder('main.window.toolbar')->set($toolbar);
        } else {
            $this->view->rollbackAllowed = false;
        }

        // paging
        $statusBar = $this->view->placeholder('main.window.statusbar');
        // the normal page_param p collides with the generic-list param p
        OntoWiki_Pager::setOptions(array('page_param'=>'page'));
        $statusBar->append(OntoWiki_Pager::get($count,$limit));

        // setting view variables
        $url = new OntoWiki_Url(array('controller' => 'history', 'action' => 'rollback'));

        $this->view->placeholder('main.window.title')->set($windowTitle);

        $this->view->formActionUrl = (string) $url;
        $this->view->formMethod    = 'post';
        // $this->view->formName      = 'instancelist';
        $this->view->formName      = 'history-rollback';
        $this->view->formEncoding  = 'multipart/form-data';
         */
    }

    /**
     *  Listing history for selected Resource
     */
    public function listAction()
    {
        $model       = $this->_owApp->selectedModel;
        $translate   = $this->_owApp->translate;
        $store       = $this->_erfurt->getStore();
        $resource    = $this->_owApp->selectedResource;
        $ac          = $this->_erfurt->getAc();
        $params      = $this->_request->getParams();
        $limit       = 20;

        $rUriEncoded = urlencode((string)$resource);
        $mUriEncoded = urlencode((string)$model);
        $feedUrl = $this->_config->urlBase . "history/feed?r=$rUriEncoded&mUriEncoded";

        $this->view->headLink()->setAlternate($feedUrl, 'application/atom+xml', 'History Feed');

        // redirecting to home if no model/resource is selected
        if (
            empty($model) ||
            (
                empty($this->_owApp->selectedResource) &&
                empty($params['r']) &&
                $this->_owApp->lastRoute !== 'instances'
            )
        ) {
            $this->_abort('No model/resource selected.', OntoWiki_Message::ERROR);
        }

        // getting page (from and for paging)
        if (!empty($params['page']) && (int) $params['page'] > 0) {
            $page = (int) $params['page'];
        } else {
            $page = 1;
        }

        // enabling versioning
        $versioning = $this->_erfurt->getVersioning();
        $versioning->setLimit($limit);

        if (!$versioning->isVersioningEnabled()) {
            $this->_abort('Versioning/History is currently disabled', null, false);
        }

        $singleResource = true;
        // setting if class or instances
        if ($this->_owApp->lastRoute === 'instances') {
            // setting default title
            $title = $resource->getTitle() ?
                $resource->getTitle() :
                OntoWiki_Utils::contractNamespace($resource->getIri());
            $windowTitle = $translate->_('Versions for elements of the list');

            $listHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('List');
            $listName = "instances";
            if ($listHelper->listExists($listName)) {
                $list = $listHelper->getList($listName);
                $list->setStore($store);
            } else {
                $this->_owApp->appendMessage(
                    new OntoWiki_Message('something went wrong with the list of instances', OntoWiki_Message::ERROR)
                );
            }

            $query = clone $list->getResourceQuery();
            $query->setLimit(0);
            $query->setOffset(0);
            //echo htmlentities($query);

            $results = $model->sparqlQuery($query);
            $resourceVar = $list->getResourceVar()->getName();

            $resources = array();
            foreach ($results as $result) {
                $resources[] = $result[$resourceVar];
            }
            //var_dump($resources);

            $historyArray = $versioning->getHistoryForResourceList(
                $resources,
                (string) $this->_owApp->selectedModel,
                $page
            );
            //var_dump($historyArray);

            $singleResource = false;
        } else {
            // setting default title
            $title = $resource->getTitle() ?
                $resource->getTitle() :
                OntoWiki_Utils::contractNamespace($resource->getIri());
            $windowTitle = sprintf($translate->_('Versions for %1$s'), $title);

            $historyArray = $versioning->getHistoryForResource(
                (string)$resource,
                (string)$this->_owApp->selectedModel,
                $page
            );
        }

        if (sizeof($historyArray) == ( $limit + 1 )) {
            $count = $page * $limit + 1;
            unset($historyArray[$limit]);
        } else {
            $count = ($page - 1) * $limit + sizeof($historyArray);
        }

        $idArray = array();
        $userArray = $this->_erfurt->getUsers();
        $titleHelper = new OntoWiki_Model_TitleHelper();
        // Load IDs for rollback and Username Labels for view
        foreach ($historyArray as $key => $entry) {
            $idArray[] = (int) $entry['id'];
            if (!$singleResource) {
                $historyArray[$key]['url'] = $this->_config->urlBase . "view?r=" . urlencode($entry['resource']);
                $titleHelper->addResource($entry['resource']);
            }

            if ($entry['useruri'] == $this->_erfurt->getConfig()->ac->user->anonymousUser) {
                $userArray[$entry['useruri']] = 'Anonymous';
            } else if ($entry['useruri'] == $this->_erfurt->getConfig()->ac->user->superAdmin) {
                $userArray[$entry['useruri']] = 'SuperAdmin';
            } else if (is_array($userArray[$entry['useruri']])) {
                if (isset($userArray[$entry['useruri']]['userName'])) {
                    $userArray[$entry['useruri']] = $userArray[$entry['useruri']]['userName'];
                } else {
                    $titleHelper->addResource($entry['useruri']);
                    $userArray[$entry['useruri']] = $titleHelper->getTitle($entry['useruri']);
                }
            }
        }
        $this->view->userArray = $userArray;
        $this->view->idArray = $idArray;
        $this->view->historyArray = $historyArray;
        $this->view->singleResource = $singleResource;
        $this->view->titleHelper = $titleHelper;

        if (empty($historyArray)) {
            $this->_owApp->appendMessage(
                new OntoWiki_Message(
                    'No history for the selected resource(s).',
                    OntoWiki_Message::INFO
                )
            );
        }

        if ($this->_erfurt->getAc()->isActionAllowed('Rollback')) {
            $this->view->rollbackAllowed = true;
            // adding submit button for rollback-action
            $toolbar = $this->_owApp->toolbar;
            $toolbar->appendButton(
                OntoWiki_Toolbar::SUBMIT,
                array('name' => $translate->_('Rollback changes'), 'id' => 'history-rollback')
            );
            $this->view->placeholder('main.window.toolbar')->set($toolbar);
        } else {
            $this->view->rollbackAllowed = false;
        }

        // paging
        $statusBar = $this->view->placeholder('main.window.statusbar');
        // the normal page_param p collides with the generic-list param p
        OntoWiki_Pager::setOptions(array('page_param'=>'page'));
        $statusBar->append(OntoWiki_Pager::get($count, $limit));

        // setting view variables
        $url = new OntoWiki_Url(array('controller' => 'history', 'action' => 'rollback'));

        $this->view->placeholder('main.window.title')->set($windowTitle);

        $this->view->formActionUrl = (string) $url;
        $this->view->formMethod    = 'post';
        // $this->view->formName      = 'instancelist';
        $this->view->formName      = 'history-rollback';
        $this->view->formEncoding  = 'multipart/form-data';
    }

    /**
     *  Restoring actions that are specified within the POST parameter
     */
    public function rollbackAction()
    {
        $resource    = $this->_owApp->selectedResource;
        $graphuri    = (string) $this->_owApp->selectedModel;
        $translate   = $this->_owApp->translate;
        $params      = $this->_request->getParams();

        // abort on missing parameters
        if (!array_key_exists('actionid', $params) || empty($resource) || empty($graphuri)) {
            $this->_abort('missing parameters.', OntoWiki_Message::ERROR);
        }

        // set active tab to history
        OntoWiki::getInstance()->getNavigation()->setActive('history');

        // setting default title
        $title = $resource->getTitle() ? $resource->getTitle() : OntoWiki_Utils::contractNamespace($resource->getIri());
        $windowTitle = sprintf($translate->_('Versions for %1$s'), $title);
        $this->view->placeholder('main.window.title')->set($windowTitle);

        // setting more view variables
        $url = new OntoWiki_Url(array('controller' => 'view', 'action' => 'index' ), null);
        $this->view->backUrl = (string) $url;

        // set translate on view
        $this->view->translate = $this->_owApp->translate;

        // abort on insufficient rights
        if (!$this->_erfurt->getAc()->isActionAllowed('Rollback')) {
            $this->_abort('not allowed.', OntoWiki_Message::ERROR);
        }

        // enabling versioning
        $versioning = $this->_erfurt->getVersioning();

        if (!$versioning->isVersioningEnabled()) {
            $this->_abort('versioning / history is currently disabled.', null, false);
        }

        $successIDs = array();
        $errorIDs = array();
        $actionids = array();

        // starting rollback action
        $actionSpec = array(
            'modeluri'      => $graphuri ,
            'type'          => Erfurt_Versioning::STATEMENTS_ROLLBACK,
            'resourceuri'   => (string) $resource
        );

        $versioning->startAction($actionSpec);

        // Trying to rollback actions from POST parameters (style: serialized in actionid)
        foreach (unserialize($params['actionid']) as $id) {
                if ( $versioning->rollbackAction($id) ) {
                    $successIDs[] = $id;
                } else {
                    $errorIDs[] = $id;
                }
        }

        // ending rollback action
        $versioning->endAction();

        // adding messages for errors and success
        if (!empty($successIDs)) {
            $this->_owApp->appendMessage(
                new OntoWiki_Message(
                    'Rolled back action(s): ' . implode(', ', $successIDs),
                    OntoWiki_Message::SUCCESS
                )
            );
        }

        if (!empty($errorIDs)) {
            $this->_owApp->appendMessage(
                new OntoWiki_Message(
                    'Error on rollback of action(s): ' . implode(', ', $errorIDs),
                    OntoWiki_Message::ERROR
                )
            );
        }
    }

    /**
     * Service to generate small HTML for Action-Details-AJAX-Integration inside Ontowiki
     */
    public function detailsAction()
    {
        $params = $this->_request->getParams();

        if (empty($params['id'])) {
            $this->_abort('missing parameters.');
        } else {
            $actionID = (int) $params['id'];
        }

        // disabling layout as it is used as a service
        $this->_helper->layout()->disableLayout();
        $this->view->isEmpty = true;

        $results = $this->getActionTriple($actionID);
        if( $results != null ) $this->view->isEmpty = false;

        $this->view->translate      = $this->_owApp->translate;
        $this->view->actionID       = $actionID;
        $this->view->stAddArray     = $results['added'];
        $this->view->stDelArray     = $results['deleted'];
        $this->view->stOtherArray   = $results['other'];
    }

    private function toFlatArray($serializedString)
    {
        //$a = array();
        $walkArray = unserialize($serializedString);
        foreach ($walkArray as $subject => $a) {
            foreach ($a as $predicate => $b) {
                foreach ($b as $object) {
                    //$a[] = array($subject, $predicate, $object['value']);
                    return array($subject, $predicate, $object['value']);
                }
            }
        }
        //return $a;
    }

    private function getActionTriple($actionID)
    {
        // enabling versioning
        $versioning = $this->_erfurt->getVersioning();

        $detailsArray = $versioning->getDetailsForAction($actionID);

        $stAddArray     = array();
        $stDelArray     = array();
        $stOtherArray   = array();

        foreach ($detailsArray as $entry) {
            $type = (int) $entry['action_type'];
            if ( $type        === Erfurt_Versioning::STATEMENT_ADDED ) {
                $stAddArray[]   = $this->toFlatArray($entry['statement_hash']);
            } elseif ( $type  === Erfurt_Versioning::STATEMENT_REMOVED ) {
                $stDelArray[]   = $this->toFlatArray($entry['statement_hash']);
            } else {
                $stOtherArray[] = $this->toFlatArray($entry['statement_hash']);
            }
        }

        return array(
            'id' => $actionID,
            'added' => $stAddArray,
            'deleted' => $stDelArray,
            'other' => $stOtherArray
        );
    }

    /**
     * Shortcut for adding messages
     */
    private function _abort($msg, $type = null, $redirect = null)
    {
        if (empty($type)) {
            $type = OntoWiki_Message::INFO;
        }

        $this->_owApp->appendMessage(
            new OntoWiki_Message(
                $msg,
                $type
            )
        );

        if (empty($redirect)) {
            if ($redirect !== false) {
                $this->_redirect($this->_config->urlBase);
            }
        } else {
            $this->redirect((string)$redirect);
        }

        return true;
    }
    //TODO generate feed about resource
}
