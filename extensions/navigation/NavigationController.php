<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * Controller for OntoWiki Navigation Module
 *
 * @category OntoWiki
 * @package  Extensions_Navigation
 * @author   {@link http://sebastian.tramp.name Sebastian Tramp}
 */
class NavigationController extends OntoWiki_Controller_Component
{
    private $_store;
    private $_translate;
    private $_ac;
    private $_model;
    /* an array of arrays, each has type and text */
    private $_messages = array();
    /* the setup consists of state and config */
    private $_setup = null;
    private $_limit = 50;

    /*
     * Initializes Naviagation Controller,
     * creates class vars for current store, session and model
     */
    public function init()
    {
        parent::init();
        $this->_store     = $this->_owApp->erfurt->getStore();
        $this->_translate = $this->_owApp->translate;
        $this->_ac        = $this->_erfurt->getAc();

        $sessionKey = 'Navigation' . (isset($config->session->identifier) ? $config->session->identifier : '');
        $this->stateSession = new Zend_Session_Namespace($sessionKey);

        $this->_model = $this->_owApp->selectedModel;
        if (isset($this->_request->m)) {
            $this->_model = $_store->getModel($this->_request->m);
        }
        if (empty($this->_model)) {
            throw new OntoWiki_Exception(
                'Missing parameter m (model) and no selected model in session!'
            );
        }
        // create title helper
        $this->titleHelper = new OntoWiki_Model_TitleHelper($this->_model);

        // Model Based Access Control
        if (!$this->_ac->isModelAllowed('view', $this->_model->getModelIri())) {
            throw new Erfurt_Ac_Exception('You are not allowed to read this model.');
        }
    }

    /*
     * The main action which is retrieved via ajax
     */
    public function exploreAction()
    {
        // disable standart navigation
        OntoWiki::getInstance()->getNavigation()->disableNavigation();
        // log action
        //$this->_owApp->logger->info('NavigationController Stage 1');
        // translate navigation title to selected language
        $this->view->placeholder('main.window.title')->set($this->_translate->_('Navigation'));

        // check if setup is present
        if (empty($this->_request->setup)) {
            throw new OntoWiki_Exception('Missing parameter setup !');
        }
        // decode setup from JSON into array
        $this->_setup = json_decode($this->_request->getParam('setup'));

        // check if setup was not converted
        if ($this->_setup == false) {
            throw new OntoWiki_Exception(
                'Invalid parameter setup (json_decode failed): ' . $this->_request->setup
            );
        }

        // overwrite the hard limit with the given one
        if (isset($this->_setup->state->limit)) {
            $this->_limit = $this->_setup->state->limit;
        }

        // build initial view
        $this->view->entries = $this->_queryNavigationEntries($this->_setup);

        // if lastEvent was "show me more" do not show root element
        /*if( $this->_setup->state->lastEvent == 'more' ){
            $this->view->showRoot = false;
        }*/

        // trigger before output event
        $this->startOutput($this->_setup);

        // set view variable for the show more button
        if ( (count($this->view->entries) > $this->_limit) && $this->_setup->state->lastEvent != "search") {
            // return only $_limit entries
            $this->view->entries = array_slice($this->view->entries, 0, $this->_limit);
            $this->view->showMeMore = true;
        } else {
            $this->view->showMeMore = false;
        }

        // if there's no entries, show text
        if (empty($this->view->entries)) {
            if (isset($this->_setup->state->searchString)) {
                $this->_messages[] = array( 'type' => 'info', 'text' => 'No result for this search.');
            } else {
                $this->_messages[] = array( 'type' => 'info', 'text' => 'Nothing to navigate here.');
            }
        }

        // the root entry (parent of the shown elements)
        if (isset($this->_setup->state->parent)) {
            $this->view->rootEntry = array();
            $this->view->rootEntry['uri'] = $this->_setup->state->parent;
            $this->view->rootEntry['url'] = $this->_getListLink($this->_setup->state->parent, $this->_setup);
            $this->view->rootEntry['title'] = $this->_getTitle(
                $this->_setup->state->parent,
                isset($this->_setup->config->titleMode) ? $this->_setup->config->titleMode : null,
                null
            );
        }

        // if search string is set, show it in view
        if (isset($this->_setup->state->searchString)) {
            $this->view->searchString = $this->_setup->state->searchString;
        }

        // if rootName is set, show it in view
        if (isset($this->_setup->config->rootName)) {
            $this->view->rootName = $this->_setup->config->rootName;
        }

        // if rootURI is set, apply it to rootName in view
        if (isset($this->_setup->config->rootURI)) {
            $this->view->rootLink = $this->_getListLink(
                $this->_setup->config->rootURI,
                $this->_setup
            );
        }

        // set view messages and setup
        $this->view->messages = $this->_messages;
        $this->view->setup    = $this->_setup;

        // trigger after end output
        $this->endOutput($this->_setup);

        // save state to session
        $this->savestateServer($this->view, $this->_setup);
    }

    private function startOutput($config)
    {
        $event = new Erfurt_Event('onNavigationStartOutput');
        $event->config = $config;
        $event->trigger();
    }

    private function endOutput($config)
    {
        $event = new Erfurt_Event('onNavigationEndOutput');
        $event->config = $config;
        $event->trigger();
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
        $this->stateSession->view  = $view->render("navigation/explore.phtml");
        $this->stateSession->setup = $setup;
        $this->stateSession->model = (string) $this->_model;
    }

    /*
     * Queries all navigation entries according to a given setup
     */
    protected function _queryNavigationEntries($setup)
    {
        if (
            isset($setup->config->cache) &&
            $setup->config->cache == true
        ) {
            $cache      = $this->_owApp->erfurt->getCache(); // Object cache
            $queryCache = $this->_owApp->erfurt->getQueryCache(); // query cache
        }

        // set cache id
        $cid = 'nav_'.md5(serialize($setup).$this->_model);

        $this->_owApp->logger->info(
            'NavigationController _queryNavigationEntries Input: ' .PHP_EOL . print_r($setup,true)
        );//*/

        // try to load results from cache
        if (
            isset($setup->config->cache) &&
            $setup->config->cache == true
        ) {
            if ( $entriesCached = $cache->load($cid)) {
                return $entriesCached;
            }

            // start transaction
            $queryCache->startTransaction($cid);
        }

        // if user searched for something
        if ($setup->state->lastEvent == "search") {
            // search request
            // @todo: also search request should not show ignored entities
            $resVar  = new Erfurt_Sparql_Query2_Var('resourceUri');
            $typeVar = new Erfurt_Sparql_Query2_IriRef(EF_RDF_TYPE);
            $query   = new Erfurt_Sparql_Query2();
            $query->addProjectionVar($resVar)->setDistinct(true);

            $pattern = $this->_store->getSearchPattern(
                $setup->state->searchString,
                (string) $this->_model
            );
            $query->addElements($pattern);

            $union = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();

            foreach ($setup->config->hierarchyTypes as $type) {
                $groupPattern = new Erfurt_Sparql_Query2_GroupGraphPattern();
                $groupPattern->addTriple(
                    $resVar,
                    $typeVar,
                    new Erfurt_Sparql_Query2_IriRef($type)
                );
                $union->addElement($groupPattern);
            }
            $query->addElement($union);

            // set to _limit+1, so we can see if there are more than $_limit entries
            $query->setLimit($this->_limit + 1);

        } else {
            // if there's no top query assigned and hideDefaultHierarchy is false
            // generate query based on setup
            if (
                (
                    !isset($setup->config->hideDefaultHierarchy) ||
                    $setup->config->hideDefaultHierarchy == false
                ) && !isset($setup->config->query->top)
            ) {
                $query = $this->_buildQuery($setup, false);
            } elseif (isset($setup->config->query->top)) {
                // if top query is assigned - use it
                $query = Erfurt_Sparql_SimpleQuery::initWithString($setup->config->query->top);
            } else {
                $query = null;
            }
        }

        // if there's something wrong with query generation - exit
        if ($query == null) {
            return;
        }

        // error logging
        $this->_owApp->logger->info(
            'NavigationController _queryNavigationEntries Query: ' .$query->__toString()
        );//*/

        // get extended results
        $allResults = $this->_model->sparqlQuery(
            $query,
            array('result_format' => 'extended')
        );

        // create res array
        $results = array();

        // parse to needed format
        foreach ($allResults['results']['bindings'] as $res) {
            if ($res['resourceUri']['type'] != 'bnode') {
                $results[]['resourceUri'] = $res['resourceUri']['value'];
            } else {
                $results[]['resourceUri'] = $this->_getSubclass(
                    str_replace("_:", "", $res['resourceUri']['value']),
                    $setup
                );
            }
        }

        // if we need to show implicit elements
        $showImplicit = false;
        if (!isset($setup->config->rootElement)) {
            if (!isset($setup->state->showImplicit)) {
                if (
                    isset($setup->config->showImplicitElements) &&
                    $setup->config->showImplicitElements == true
                ) {
                    $showImplicit = true;
                }
            } else {
                if ($setup->state->showImplicit == true) {
                    $showImplicit = true;
                }
            }
        }

        // if we need to show implicit elements
        // generate additional query for them
        //echo $showImplicit==true ? "true": "false";
        if ($showImplicit) {
            // new query for regular event
            if ($setup->state->lastEvent != "search") {
                $query = $this->_buildQuery($setup, true);
                $resultsImplicit = $this->_model->sparqlQuery(
                    $query,
                    array('result_format' => 'extended')
                );
            } else {
                // new query for search
                $query           = $this->_buildStringSearchQuery($setup);
                $resultsImplicit = $this->_model->sparqlQuery(
                    $query,
                    array('result_format' => 'extended')
                );
            }

            // append implicit classes to results
            foreach ($resultsImplicit['results']['bindings'] as $res) {
                if (!in_array($res['resourceUri']['value'], $results)) {
                    if ($res['resourceUri']['type'] != 'bnode') {
                        $results[]['resourceUri'] = $res['resourceUri']['value'];
                    } else {
                        $results[]['resourceUri'] = $this->_getSubclass(
                            str_replace(
                                "_:",
                                "",
                                $res['resourceUri']['value']
                            ),
                            $setup
                        );
                    }
                }
            }
        }

        // log results
        $this->_owApp->logger->info(
            'NavigationController _queryNavigationEntries Result: '  . PHP_EOL . print_r($allResults,true)
        );//*/

        // set titleMode from config or set it to null if config is not assigned
        if (isset($setup->config->titleMode)) {
            $mode = $setup->config->titleMode;
        } else {
            $mode = null;
        }

        // if title mode set to titlehelper
        // get titles of all resources
        if ($mode == "titleHelper") {
            //$this->_owApp->logger->info('TITLE HELPER.');
            //$this->_owApp->logger->info('TITLE HELPER REs: '.print_r($results,true));
            if (isset($results)) {
                foreach ($results as $result) {
                    //$this->_owApp->logger->info('TITLE HELPER: '.$result['resourceUri']);
                    //$this->_owApp->logger->info('TITLE HELPER: '.$result['subResourceUri']);
                    $this->titleHelper->addResource($result['resourceUri']);
                }
            }
            // add parent to titleHelper to get title
            if (isset($setup->state->parent)) {
                $this->titleHelper->addResource($setup->state->parent);
            }
        }

        // create new array for navigation entries
        $entries = array();

        // if there's no query results - exit
        if ($results == null) {
            return;
        }

        // parse all results to entries
        foreach ($results as $result) {
            $entry = array();

            // assing resource URI
            $uri = $result['resourceUri'];
            $entry = array();
            $entry['title'] = $this->_getTitle($uri, $mode, $setup);
            // get resource ling
            $entry['link'] = $this->_getListLink($uri, $setup);

            // chech if there's need to look for subresources
            $checkSubs = false;
            if (
                isset($setup->config->checkSub) &&
                $setup->config->checkSub == true
            ) {
                $checkSubs = true;
            }

            // if needed look for subresources
            if ($checkSubs) {
                // build sub query
                $query = $this->_buildSubCheckQuery($uri, $setup);
                // get results
                $results = $this->_model->sparqlQuery($query);
                // assigh count of results
                $entry['sub'] = count($results);
            } else {
                // if there's no need to look for subres
                // just set var to 1, so that "go deeper" arrow
                // will be allways visible
                $entry['sub'] = 1;
            }

            // check if filtering empty is enabled
            $filterEmpty = false;
            if (!isset($setup->state->showEmpty)) {
                if (
                    isset($setup->config->showEmptyElements) &&
                    $setup->config->showEmptyElements == false
                ) {
                    $filterEmpty = true;
                }
            } else {
                if ($setup->state->showEmpty == false) {
                    $filterEmpty = true;
                }
            }

            // do filter empty if needed
            $show = true;
            if ($filterEmpty) {
                // generate query
                $query = $this->_buildCountQuery($uri, $setup);
                // get results
                $results = $this->_model->sparqlQuery($query);
                // depending on result format set count
                if (isset($results[0]['callret-0'])) {
                    $count = $results[0]['callret-0'];
                } else {
                    $count = count($results);
                }
                // if count is 0 do not show entry
                if ($count == 0) {
                    $show = false;
                }
            }

            if (
                isset($setup->config->checkUsage) &&
                $setup->config->checkUsage == true
            ) {
                // gen query
                $query = $this->_buildUsageQuery($uri, $setup);
                // get results
                $results = $this->_model->sparqlQuery($query);
                // depending on result format set count
                if (isset($results[0]['callret-0'])) {
                    $count = $results[0]['callret-0'];
                } else {
                    $count = count($results);
                }
                // if count is 0 do not show entry
                if ($count == 0) {
                    $show = false;
                }
            }
            // apply $show flag
            if($show) $entries[$uri] = $entry;
        }

        $this->_owApp->logger->info('ENTRIES: '.print_r($entries,true));

        if (isset($setup->config->cache) && $setup->config->cache == true) {
            // save results to cache
            $cache->save($entries, $cid);
            // end cache transaction
            $queryCache->endTransaction($cid);
        }

        return $entries;
    }

    protected function _getSubclass($node, $setup)
    {
        $subVar    = new Erfurt_Sparql_Query2_Var('subResourceUri');
        $searchVar = new Erfurt_Sparql_Query2_BlankNode($node);
        $query     = new Erfurt_Sparql_Query2();
        $query->addProjectionVar($subVar);
        $query->setDistinct();

        $elements = array();

        if (isset($setup->config->hierarchyRelations->in)) {
            if (count($setup->config->hierarchyRelations->in) > 1) {
                // init union var
                $unionSub = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
                // parse config gile
                foreach ($setup->config->hierarchyRelations->in as $rel) {
                    // sub stuff
                    $groupPattern = new Erfurt_Sparql_Query2_GroupGraphPattern();
                    // add triplen
                    $groupPattern->addTriple(
                        $subVar,
                        new Erfurt_Sparql_Query2_IriRef($rel),
                        $searchVar
                    );
                    // add triplet to union var
                    $unionSub->addElement($groupPattern);
                }
                $elements[] = $unionSub;
            } else {
                $rel = $setup->config->hierarchyRelations->in;
                // add optional sub relation
                $elements[] = new Erfurt_Sparql_Query2_Triple(
                    $subVar,
                    new Erfurt_Sparql_Query2_IriRef($rel[0]),
                    $searchVar
                );
            }
        }

        if (isset($setup->config->hierarchyRelations->out)) {
            if (count($setup->config->hierarchyRelations->out) > 1) {
                // init union var
                $unionSub = new Erfurt_Sparql_Query2_GroupGraphPattern();
                // parse config gile
                foreach ($setup->config->hierarchyRelations->out as $rel) {
                    // sub stuff
                    $optionalPattern = new Erfurt_Sparql_Query2_OptionalGraphPattern();
                    // add triplen
                    $optionalPattern->addTriple(
                        $searchVar,
                        new Erfurt_Sparql_Query2_IriRef($rel),
                        $subVar
                    );
                    // add triplet to union var
                    $unionSub->addElement($optionalPattern);
                }
                $elements[] = $unionSub;
            } else {
                $rel = $setup->config->hierarchyRelations->out;
                // add optional sub relation
                $elements[] = new Erfurt_Sparql_Query2_Triple(
                    $searchVar,
                    new Erfurt_Sparql_Query2_IriRef($rel[0]),
                    $subVar
                );
            }
        }
        $query->addElements($elements);

        $result = $this->_model->sparqlQuery($query);

        return $result[0]['subResourceUri'];
    }

    protected function _getTitle($uri, $mode, $setup)
    {
        $name = '';
        // set default mode if none is set
        if (!isset($mode) || $mode == null) {
            $mode = "baseName";
        }

        // get title
        if ($mode == "titleHelper") {
            $name = $this->titleHelper->getTitle($uri, OntoWiki::getInstance()->language);
        } elseif ($mode == "baseName") {
            if (strrpos($uri, '#') > 0) {
                $name = substr($uri, strrpos($uri, '#')+1);
            } elseif (strrpos($uri, '/') > 0) {
                $name = substr($uri, strrpos($uri, '/')+1);
            } else {
                $name = $uri;
            }
        } else {
            $name = 'error';
        }

        // count entries
        if (
            isset($setup->config->showCounts) &&
            $setup->config->showCounts == true
        ) {
            $query   = $this->_buildCountQuery($uri, $setup);
            $results = $this->_model->sparqlQuery($query);
            if (isset($results[0]['callret-0'])) {
                $count = $results[0]['callret-0'];
            } else {
                $count = count($results);
            }

            if( $count > 0 ) $name .= ' ('.$count.')';
        }

        return $name;
    }

    /*
     * Builds query for main actions (root, nav. deeper)
     */
    protected function _buildQuery($setup, $forImplicit = false)
    {
        if (
            isset($setup->config->query->deeper) &&
            isset($setup->state->parent)
        ) {
            //$replace = ;
            $queryString = str_replace(
                "%resource%",
                $setup->state->parent,
                $setup->config->query->deeper
            );
            $query = Erfurt_Sparql_SimpleQuery::initWithString($queryString);
        } else {
            $query = new Erfurt_Sparql_Query2();
            $query->addElements(
                NavigationHelper::getSearchTriples(
                    $setup,
                    $forImplicit,
                    $this->_config->store->backend
                )
            );
            //$query->setCountStar(true);
            $query->setDistinct(true);
            $query->addProjectionVar(new Erfurt_Sparql_Query2_Var('resourceUri'));
            //$query->addProjectionVar(new Erfurt_Sparql_Query2_Var('subResourceUri'));
            // set to _limit+1, so we can see if there are more than $_limit entries
            //$query->setLimit($this->_limit + 1);
        }
        // sorting
        if (isset($setup->state->sorting)) {
            $query->getOrder()->add(new Erfurt_Sparql_Query2_Var('sortRes'), "ASC");
        } elseif (isset($setup->config->ordering->relation)) { // set ordering
            $orderVar = new Erfurt_Sparql_Query2_Var('order');
            $query->getWhere()->addElement(
                new Erfurt_Sparql_Query2_OptionalGraphPattern(
                    array(
                        new Erfurt_Sparql_Query2_Triple(
                            new Erfurt_Sparql_Query2_Var('resourceUri'),
                            new Erfurt_Sparql_Query2_IriRef($setup->config->ordering->relation),
                            $orderVar
                        )
                    )
                )
            );
            $query->getOrder()->add(
                $orderVar,
                $setup->config->ordering->modifier
            );
        }

        // set offset
        if (isset($setup->state->offset) && $setup->state->lastEvent == 'more') {
            $query->setLimit($this->_limit + $setup->state->offset + 1);
        } else {
            $query->setLimit($this->_limit + 1);
        }

        return $query;
    }

    /*
     * Builds search query string
     */
    protected function _buildStringSearchQuery($setup)
    {
        // define vars
        $searchVar = new Erfurt_Sparql_Query2_Var('resourceUri');
        $subVar = new Erfurt_Sparql_Query2_Var('sub');

        // define query
        $query = new Erfurt_Sparql_Query2();
        $query->addProjectionVar($searchVar);
        $query->setDistinct();

        // init union var
        $union = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
        // parse config
        if (isset($setup->config->hierarchyRelations->out)) {
            foreach ($setup->config->hierarchyRelations->out as $rel) {
                // create new graph pattern
                $groupPattern = new Erfurt_Sparql_Query2_GroupGraphPattern();
                // add triplen
                $groupPattern->addTriple(
                    $searchVar,
                    new Erfurt_Sparql_Query2_IriRef($rel), //EF_RDF_TYPE),
                    $subVar
                );
                // add triplet to union var
                $union->addElement($groupPattern);
            }
        }
        // parse config
        if (isset($setup->config->hierarchyRelations->in)) {
            foreach ($setup->config->hierarchyRelations->in as $rel) {
                // create new graph pattern
                $groupPattern = new Erfurt_Sparql_Query2_GroupGraphPattern();
                // add triplen
                $groupPattern->addTriple(
                    $subVar,
                    new Erfurt_Sparql_Query2_IriRef($rel), //EF_RDF_TYPE),
                    $searchVar
                );
                // add triplet to union var
                $union->addElement($groupPattern);
            }
        }
        // parse config
        if (isset($setup->config->instanceRelation->out)) {
            foreach ($setup->config->instanceRelation->out as $rel) {
                // create new graph pattern
                $groupPattern = new Erfurt_Sparql_Query2_GroupGraphPattern();
                // add triplen
                $groupPattern->addTriple(
                    $subVar,
                    new Erfurt_Sparql_Query2_IriRef($rel), //EF_RDF_TYPE),
                    $searchVar
                );
                // add triplet to union var
                $union->addElement($groupPattern);
            }
        }

        // parse config
        if (isset($setup->config->instanceRelation->in)) {
            foreach ($setup->config->instanceRelation->in as $rel) {
                // create new graph pattern
                $groupPattern = new Erfurt_Sparql_Query2_GroupGraphPattern();
                // add triplen
                $groupPattern->addTriple(
                    $searchVar,
                    new Erfurt_Sparql_Query2_IriRef($rel), //EF_RDF_TYPE),
                    $subVar
                );
                // add triplet to union var
                $union->addElement($groupPattern);
            }
        }

        $query->addElement($union);
        // add regex filter for search string
        $query->addFilter(
            new Erfurt_Sparql_Query2_Regex(
                new Erfurt_Sparql_Query2_Str($searchVar),
                new Erfurt_Sparql_Query2_RDFLiteral($setup->state->searchString)
            )
        );

        return $query;
    }

    /*
     * Builds counting query for given $uri
     */
    protected function _buildCountQuery($uri, $setup)
    {
        $query = new Erfurt_Sparql_Query2();
        $query->addProjectionVar(new Erfurt_Sparql_Query2_Var('resourceUri'));
        $query->setCountStar(true);
        $query->setDistinct();
        $query->addElements(NavigationHelper::getInstancesTriples($uri, $setup));

        // error logging
        $this->_owApp->logger->info(
            'NavigationController: COUNTQUERY: ' .$query->__toString()
        );//*/

        return $query;
    }

    /*
     * Builds usage query for given $uri
     */
    protected function _buildUsageQuery($uri, $setup)
    {
        $query = new Erfurt_Sparql_Query2();
        $query->addProjectionVar(new Erfurt_Sparql_Query2_Var('resourceUri'));
        $query->setCountStar(true);
        $query->setDistinct();
        return $query;
    }

    /*
     * Builds query to check for subresources for $uri based on $setup
     */
    protected function _buildSubCheckQuery($uri, $setup)
    {
        $subVar     = new Erfurt_Sparql_Query2_Var('subResourceUri');
        $searchVar  = new Erfurt_Sparql_Query2_Var('resourceUri');
        //$classVar = new Erfurt_Sparql_Query2_Var('classUri');
        $query      = new Erfurt_Sparql_Query2();
        $query->addProjectionVar($subVar);
        $query->setDistinct();

        $this->_owApp->logger->info("data: ".print_r($query,true));
        $elements = array();
        $in = array();
        $out = array();

        if (isset($setup->config->hierarchyRelations->in)) {
            if (count($setup->config->hierarchyRelations->in) > 1) {
                // init union var
                $unionSub = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
                // parse config gile
                foreach ($setup->config->hierarchyRelations->in as $rel) {
                    // sub stuff
                    $groupPattern = new Erfurt_Sparql_Query2_GroupGraphPattern();
                    // add triplen
                    $groupPattern->addTriple(
                        $subVar,
                        new Erfurt_Sparql_Query2_IriRef($rel),
                        $searchVar
                    );
                    // add triplet to union var
                    $unionSub->addElement($groupPattern);
                }
                $in[] = $unionSub;
            } else {
                $rel = $setup->config->hierarchyRelations->in;
                // add optional sub relation
                // create optional graph to load sublacsses of selected class
                $queryOptional = new Erfurt_Sparql_Query2_GroupGraphPattern();
                $queryOptional->addTriple(
                    $subVar,
                    new Erfurt_Sparql_Query2_IriRef($rel[0]),
                    $searchVar
                );
                $in[] = $queryOptional;
            }
        }
        if (isset($setup->config->hierarchyRelations->out)) {
            if (count($setup->config->hierarchyRelations->out) > 1) {
                // init union var
                $unionSub = new Erfurt_Sparql_Query2_GroupGraphPattern();
                // parse config gile
                foreach ($setup->config->hierarchyRelations->out as $rel) {
                    // sub stuff
                    $optPattern = new Erfurt_Sparql_Query2_OptionalGraphPattern();
                    // add triple
                    $optPattern->addTriple(
                        $searchVar,
                        new Erfurt_Sparql_Query2_IriRef($rel),
                        $subVar
                    );
                    // add triplet to union var
                    $unionSub->addElement($optPattern);
                }
                $out[] = $unionSub;
            } else {
                $rel = $setup->config->hierarchyRelations->out;
                // add optional sub relation
                // create optional graph to load sublacsses of selected class
                $queryOptional = new Erfurt_Sparql_Query2_GroupGraphPattern();
                $queryOptional->addTriple(
                    $searchVar,
                    new Erfurt_Sparql_Query2_IriRef($rel[0]),
                    $subVar
                );
                $out[] = $queryOptional;
            }
        }
        $inout = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
        $inout->addElements($in);
        $inout->addElements($out);
        $elements[] = $inout;
        /*$elements[] = new Erfurt_Sparql_Query2_Triple(
            $searchVar,
            new Erfurt_Sparql_Query2_IriRef(EF_RDF_TYPE),
            $classVar
        );*/
        // add filter
        $elements[] = new Erfurt_Sparql_Query2_Filter(
            new Erfurt_Sparql_Query2_sameTerm($searchVar, new Erfurt_Sparql_Query2_IriRef($uri))
        );
        $query->addElements($elements);
        $query->setLimit(1);

        // log results
        $this->_owApp->logger->info(
            'NavigationController CHECK SUB: '  . PHP_EOL . $query->__toString()
        );

        return $query;
    }

    /*
     * This method returns the link to the resource list action
     * according to a given URI in the navigation module and a
     * given navigation setup
     */
    protected function _getListLink ($uri, $setup)
    {
        $owUrl = new OntoWiki_Url(
            array('route' => 'instances'),
            array()
        );
        $return = (string) $owUrl;

        // at the moment, we use r= here, not class=
        $return .= "?init";
        $conf = array();
        // there is a shortcut for rdfs classes
        if (isset($setup->config->list->config)) {
            $configString = str_replace("|", '"', $setup->config->list->config);
            $configString = str_replace("%resource%", $uri, $configString);
            $conf         = json_decode($configString);
        } else if (isset($setup->config->list->query)) {
            // show properties
            if (isset($setup->config->list->shownProperties)) {
                $configString = str_replace("|", '"', $setup->config->list->shownProperties);
                $configString = str_replace("%resource%", $uri, $configString);
                $conf['shownProperties'][] = json_decode($configString);
            }

            // query
            $configQuery = str_replace("%resource%", $uri, $setup->config->list->query);
            $configQuery = str_replace("\n", " ", $configQuery);

            $conf['filter'][] = array(
                'mode' => 'query',
                'query' => $configQuery
            );
        } else {
            if (
                isset($setup->config->instanceRelation->out) &&
                isset($setup->config->instanceRelation->in) &&
                ($setup->config->instanceRelation->out[0] == EF_RDF_TYPE) &&
                ($setup->config->hierarchyRelations->in[0] == EF_RDFS_SUBCLASSOF )
            ) {
                $conf['filter'][] = array(
                    'mode' => 'rdfsclass',
                    'rdfsclass' => $uri,
                    'action' => 'add'
                );
            } else {
                $conf['filter'][] = array(
                    'mode' => 'cnav',
                    'cnav' => $setup,
                    'uri'  => $uri,
                    'action' => 'add'
                );
            }
        }
        //$this->_owApp->logger->info("conf: ".print_r($conf,true));

        return $return . "&instancesconfig=".urlencode(json_encode($conf));
    }
}
