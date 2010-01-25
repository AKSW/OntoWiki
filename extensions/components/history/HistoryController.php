<?php

/**
 * History component controller.
 * 
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_history
 * @author     Christoph Rieß <c.riess.dev@googlemail.com>
 * @copyright  Copyright (c) 2009, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: HistoryController.php 4090 2009-08-19 22:10:54Z christian.wuerker $
 */

class HistoryController extends OntoWiki_Controller_Component
{

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

        // redirecting to home if no model/resource is selected
        if (empty($model) || (empty($this->_owApp->selectedResource) && empty($params['r']))) {
            $this->_abort('No model/resource selected.', OntoWiki_Message::ERROR);
        }

        // getting page (from and for paging)
        if (!empty($params['p']) && (int) $params['p'] > 0) {
            $page = (int) $params['p'];
        } else {
            $page = 1;
        }

        // setting default title
        $title = $resource->getTitle() ? $resource->getTitle() : OntoWiki_Utils::contractNamespace($resource->getIri());
        $windowTitle = sprintf($translate->_('Versions for %1$s'), $title);

        // enabling versioning
        $versioning = $this->_erfurt->getVersioning();
        $versioning->setLimit($limit);

        if (!$versioning->isVersioningEnabled()) {
            $this->_abort('Versioning/History is currently disabled', null, false);
        }

        // setting if class or instances
        if ($this->_owApp->lastRoute === 'instances') {
            // loading some more required classes
            // getting transitive closure for types
            $types   = array_keys(
                $store->getTransitiveClosure(
                    $model->getModelIri(),
                    EF_RDFS_SUBCLASSOF,
                    array((string) $resource),
                    true
                )
            );
            $types[] = $this->_owApp->selectedClass;

            // adding title indicating versions of instances are shown
            $windowTitle .= ' - ' . $translate->_('Instances');
    
            // query to get all instances for transitive closure of a type
            // (see above for transitive closure)
            $query = Erfurt_Sparql_SimpleQuery::initWithString(
                'SELECT * 
                 WHERE {
                    ?resourceUri a ?type.
                    FILTER (sameTerm(?type, <' . implode('>) || sameTerm(?type, <', $types) . '>))
                 }'
            );

            $instanceList = $model->sparqlQuery($query);

            $resources = array();
            foreach ($instanceList as $instance) {
                $resources[] = $instance['resourceUri'];
            }

            $historyArray = $versioning->getHistoryForResourceList(
                $resources,
                (string) $this->_owApp->selectedModel,
                $page
            );
            
        } else {
            $historyArray = $versioning->getHistoryForResource(
                (string)$resource,
                (string)$this->_owApp->selectedModel,
                $page
            );
        }

        if (sizeof($historyArray) == ( $limit + 1 ) ) {
            $count = $page * $limit + 1;
            unset($historyArray[$limit]);
        } else {
            $count = ($page - 1) * $limit + sizeof($historyArray);
        }

        $idArray = array();
        $userArray = $this->_erfurt->getUsers();
        
        // Load IDs for rollback and Username Labels for view
        foreach ($historyArray as $entry) {
            $idArray[] = (int) $entry['id'];

            if ($entry['useruri'] == $this->_erfurt->getConfig()->ac->user->anonymousUser) {
                $userArray[$entry['useruri']] = 'Anonymous';
            } elseif ($entry['useruri'] == $this->_erfurt->getConfig()->ac->user->superAdmin) {
                $userArray[$entry['useruri']] = 'SuperAdmin';
            } elseif (
                is_array($userArray[$entry['useruri']]) &&
                array_key_exists('userName',$userArray[$entry['useruri']])
            ) {
                $userArray[$entry['useruri']] = $userArray[$entry['useruri']]['userName'];
            }
        }

        $this->view->userArray = $userArray;
        $this->view->idArray = $idArray;
        $this->view->historyArray = $historyArray;

        if (empty($historyArray))  {
            $this->_owApp->appendMessage(
                new OntoWiki_Message(
                    'No matches.' ,
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
        $statusBar->append(OntoWiki_Pager::get($count,$limit));

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
        if (!array_key_exists('actionid',$params) || empty($resource) || empty($graphuri)) {
            $this->_abort('missing parameters.', OntoWiki_Message::ERROR);
        }
        
        // set active tab to history
        Ontowiki_Navigation::setActive('history');

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
                    'Rolled back action(s): ' . implode(', ',$successIDs) ,
                    OntoWiki_Message::SUCCESS
                )
            );
        }

        if (!empty($errorIDs)) {
            $this->_owApp->appendMessage(
                new OntoWiki_Message(
                    'Error on rollback of action(s): ' . implode(', ',$errorIDs) ,
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
        $params         = $this->_request->getParams();

        if (empty($params['id'])) {
            $this->_abort('missing parameters.');
        } else {
            $actionID = (int) $params['id'];
        }

        // disabling layout as it is used as a service
        $this->_helper->layout()->disableLayout();
        $this->view->isEmpty = true;

        // enabling versioning
        $versioning = $this->_erfurt->getVersioning();

        $detailsArray = $versioning->getDetailsForAction($actionID);
        
        $stAddArray     = array();
        $stDelArray     = array();
        $stOtherArray   = array();

        function toFlatArray($serializedString) {
            $walkArray = unserialize($serializedString);
            foreach ($walkArray as $subject => $a)  {
                foreach ($a as $predicate => $b) {
                    foreach ($b as $object) {
                        return array($subject, $predicate, $object['value']);
                    }
                }
            }
        }

        foreach ($detailsArray as $entry) {
            $this->view->isEmpty = false;
            $type = (int) $entry['action_type'];
            if ( $type        === Erfurt_Versioning::STATEMENT_ADDED ) {
                $stAddArray[]   = toFlatArray($entry['statement_hash']);
            } elseif ( $type  === Erfurt_Versioning::STATEMENT_REMOVED ) {
                $stDelArray[]   = toFlatArray($entry['statement_hash']);
            } else {
                $stOtherArray[] = toFlatArray($entry['statement_hash']);
            }
        }

        $this->view->translate      = $this->_owApp->translate;
        $this->view->actionID       = $actionID;
        $this->view->stAddArray     = $stAddArray;
        $this->view->stDelArray     = $stDelArray;
        $this->view->stOtherArray   = $stOtherArray;

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
                $msg ,
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
}


