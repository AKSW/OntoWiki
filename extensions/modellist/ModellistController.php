<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * Controller for OntoWiki Modellist Extended Module
 *
 * @category OntoWiki
 * @package  Extensions_Modellist
 * @author   {@link http://codezen.ru Tim Ermilov}
 */
class ModellistController extends OntoWiki_Controller_Component
{
    /*
     * Initializes Modellist Controller,
     * creates class vars for current store, session and models list
     */
    public function init()
    {
        parent::init();
        $sessionKey = 'Modellist' . (isset($config->session->identifier) ? $config->session->identifier : '');
        $this->stateSession = new Zend_Session_Namespace($sessionKey);
        $this->_store = $this->_owApp->erfurt->getStore();
    }

    /*
     * The main action which is retrieved via ajax
     */
    public function exploreAction()
    {
        // disable standart navigation
        OntoWiki::getInstance()->getNavigation()->disableNavigation();
        // translate navigation title to selected language
        $this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('Model list'));
        $setup = $this->_request->setup;
        $setupMd5 = md5(json_encode($setup));

        if($this->stateSession->setupMd5 == $setupMd5) {
            return $this->view->prerendered = $this->stateSession->view;
        }

        // get offset
        $offset = !empty($setup['offset']) ? $setup['offset'] : 0;
        // get limit (default to 10)
        $limit = !empty($setup['limit']) ? $setup['limit'] : 10;
        // search string
        $searchString = !empty($setup['searchString']) ? $setup['searchString'] : null;

        $models = array();
        $selectedModel = $this->_owApp->selectedModel ? $this->_owApp->selectedModel->getModelIri() : null;

        $lang = $this->_config->languages->locale;
        $titleMode = $this->_privateConfig->defaults->titleMode;

        $useGraphUriAsLink = false;
        if (isset($this->_privateConfig->useGraphUriAsLink) && (bool)$this->_privateConfig->useGraphUriAsLink) {
            $useGraphUriAsLink = true;
        }

        // TODO: handle hidden models
        //if (isset($this->session->showHiddenGraphs) && $this->session->showHiddenGraphs == true) {
        //} else {
        //}
        $queryString = "SELECT DISTINCT ?g WHERE { GRAPH ?g { ?s ?p ?o } } ORDER BY ?g OFFSET ".$offset." LIMIT ".($limit+1);
        $query = Erfurt_Sparql_SimpleQuery::initWithString($queryString);
        // get extended results
        $allResults = $this->_store->sparqlQuery($query);
        $graphUris = array();
        foreach($allResults as $index => $obj) {
            $graphUris[] = $obj["g"];
        }

        foreach ($graphUris as $graphUri) {
            $linkUrl = $this->_config->urlBase . 'model/select/?m=' . urlencode($graphUri);
            if ($useGraphUriAsLink) {
                if (isset($this->_config->vhosts)) {
                    $vHostsArray = $this->_config->vhosts->toArray();
                    foreach ($vHostsArray as $vHostUri) {
                        if (strpos($graphUri, $vHostUri) !== false) {
                            // match
                            $linkUrl = $graphUri;
                            break;
                        }
                    }
                }
            }

            $temp             = array();
            $temp['url']      = $linkUrl;
            $temp['graphUri'] = $graphUri;
            $temp['selected'] = ($selectedModel == $graphUri ? 'selected' : '');

            // use URI if no title exists
            $label = $graphUri;
            $temp['label'] = $label;
            $models[] = $temp;
        }

        // build initial view
        $this->view->entries = $models;

        // set view variable for the show more button
        $entriesCount = count($this->view->entries);
        if (($entriesCount > $limit)) {
            $output = array_slice($this->view->entries, 0, $limit);

            // return only $_limit entries
            $this->view->entries    = $output;
            $this->view->showMeMore = true;
        } else {
            $this->view->showMeMore = false;
        }

        // prepare title helper if needed
        if($titleMode == "titleHelper") {
            $titleHelper = new OntoWiki_Model_TitleHelper();
            $titleHelper->addResources(array_keys($this->view->entries));
            foreach($this->view->entries as $key => $entry) {
                $graphUri = $entry['graphUri'];
                $label = $titleHelper->getTitle($graphUri, $lang);
                if(!empty($label)) {
                    $this->view->entries[$key]['label'] = $label;
                }
            }
        }

        // prepend old list if needed
        $needPrepend = $offset > 0;
        if($needPrepend) {
            $storedGraphs = json_decode($this->stateSession->graphUris);
            $entries = array();
            foreach($storedGraphs as $entry) {
                $entries[] = get_object_vars($entry);
            }
            $this->view->entries = array_merge($entries, $this->view->entries);
        }

        // save state to session
        $this->savestateServer($this->view, $setup);
    }

    /*
     * Saves current view, setup and model to state to use it on refresh
     */
    protected function savestateServer($view, $setup)
    {
        // encode setup to json
        $offset = !empty($setup['offset']) ? $setup['offset'] : 0;
        $limit = !empty($setup['limit']) ? $setup['limit'] : 10;
        $setup = json_encode($setup);
        $setupMd5 = md5($setup);
        // replace \' and \" to ' and "
        $replaceFrom = array("\\'", '\\"');
        $replaceTo   = array("'", '"');
        $setup       = str_replace($replaceFrom, $replaceTo, $setup);

        // save view, setup and current model to state
        $this->stateSession->view  = $view->render("explore.phtml");
        $this->stateSession->setup = $setup;
        $this->stateSession->setupMd5 = $setupMd5;
        $this->stateSession->limit = $limit;
        $this->stateSession->offset = $offset;
        $this->stateSession->model = (string)$this->_owApp->selectedModel;
        $this->stateSession->graphUris = json_encode($view->entries);
    }
}
