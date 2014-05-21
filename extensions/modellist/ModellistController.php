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
        $this->session = new Zend_Session_Namespace(_OWSESSION);

        $store = $this->_owApp->erfurt->getStore();

        if (isset($this->session->showHiddenGraphs) && $this->session->showHiddenGraphs == true) {
            $this->graphUris = $store->getAvailableModels(true);
        } else {
            $this->graphUris = $store->getAvailableModels(false);
        }
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

        foreach ($this->graphUris as $graphUri => $true) {
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

            $temp['backendName'] = $true;
            $models[] = $temp;

            // check if we're full and break
            if(count($models) > ($limit + $offset)) {
                break;
            }
        }

        // build initial view
        $this->view->entries = $models;

        // set view variable for the show more button
        $entriesCount = count($this->view->entries) - $offset;
        if (($entriesCount > $limit)) {
            $output = array_slice($this->view->entries, 0, $offset + $limit);

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

        // save state to session
        $this->savestateServer($this->view, $setup);
    }

    /*
     * Saves current view, setup and model to state to use it on refresh
     */
    protected function savestateServer($view, $setup)
    {
        // encode setup to json
        $setup = json_encode($setup);
        // replace \' and \" to ' and "
        $replaceFrom = array("\\'", '\\"');
        $replaceTo   = array("'", '"');
        $setup       = str_replace($replaceFrom, $replaceTo, $setup);

        // save view, setup and current model to state
        $sessionKey         = 'Modellist' . (isset($config->session->identifier) ? $config->session->identifier : '');
        $this->stateSession = new Zend_Session_Namespace($sessionKey);
        $this->stateSession->view  = $view->render("explore.phtml");
        $this->stateSession->setup = $setup;
        $this->stateSession->model = (string)$this->_owApp->selectedModel;
    }
}
