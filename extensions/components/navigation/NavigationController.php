<?php
/**
 * Controller for OntoWiki Navigation Module
 *
 * @category   OntoWiki
 * @package    extensions_components_navigation
 * @author     Sebastian Dietzold <dietzold@informatik.uni-leipzig.de>
 * @copyright  Copyright (c) 2009, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

class NavigationController extends OntoWiki_Controller_Component
{
    private $store;
    private $translate;
    private $session;
    private $ac;
    private $model;

    public function init()
    {
        parent::init();
        $this->store = $this->_owApp->erfurt->getStore();
        $this->translate = $this->_owApp->translate;
        $this->session = $this->_owApp->session;
        $this->ac = $this->_erfurt->getAc();

        $this->model = $this->_owApp->selectedModel;
        if (isset($this->_request->m)) {
            $this->model = $store->getModel($this->_request->m);
        }
        if (empty($this->model)) {
            throw new OntoWiki_Exception('Missing parameter m (model) and no selected model in session!');
            exit;
        }
        // Model Based Access Control
        if (!$this->ac->isModelAllowed('view', $this->model->getModelIri()) ) {
            throw new Erfurt_Ac_Exception('You are not allowed to read this model.');
        }
    }

    /*
     * The main action which is retrieved via json
     */
    public function exploreAction() {
        OntoWiki_Navigation::disableNavigation();
        $this->view->placeholder('main.window.title')
            ->set($this->translate->_('Navigation'));

        if (empty($this->_request->setup)) {
            throw new OntoWiki_Exception('Missing parameter setup !');
            exit;
        }

        $this->view->entries = $this->queryNavigationEntries($this->_request->setup);
        return;
    }

    /*
     * Queries all navigation entries according to a given setup
     */
    protected function queryNavigationEntries($setup) {
        $linkurl = new OntoWiki_Url(array('route' => 'properties'), array('r'));

        $query = 'SELECT DISTINCT ?navEntry WHERE {
            ?navEntry <'.EF_RDF_TYPE.'> <'.EF_OWL_CLASS.'>.
            FILTER (isIRI(?navEntry)) .
        }';
        $results = $this->model->sparqlQuery ($query) ;

        $titleHelper = new OntoWiki_Model_TitleHelper($this->model);
        foreach ($results as $result) {
            $titleHelper->addResource($result['navEntry']);
        }
        $entries = array();
        foreach ($results as $result) {
            $uri = $result['navEntry'];
            $entry = array();
            $entry['title'] = $titleHelper->getTitle($uri);
            $entry['link'] = (string) $linkurl->setParam('r', $uri, true);
            $entries[$uri] = $entry;
        }

        return $entries;
    }

}
