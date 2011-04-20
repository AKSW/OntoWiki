<?php
/**
 * distributed semantic social network client
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_dssn
 * @copyright  Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class DssnController extends OntoWiki_Controller_Component {

    public function init() {
        parent::init();

        // create the navigation tabs
        OntoWiki_Navigation::reset();
        OntoWiki_Navigation::register('news', array(
            'route'      => null,
            'controller' => 'dssn',
            'action'     => 'news',
            'name'       => 'News & Activities' ));
        OntoWiki_Navigation::register('contacts', array(
            'route'      => null,
            'controller' => 'dssn',
            'action'     => 'network',
            'name'       => 'Network' ));

        // add dssn specific styles and javascripts
        $this->view->headLink()->appendStylesheet($this->_componentUrlBase . 'js/dssn.js');
        $this->view->headLink()->appendStylesheet($this->_componentUrlBase . 'css/dssn.css');
    }

    /*
     * news & activities tab
     */
    public function newsAction() {
        $translate  = $this->_owApp->translate;

        $this->view->placeholder('main.window.title')->set($translate->_('News & Activities'));
        $this->addModuleContext('main.window.dssn.news');

        // inserts the activity stream list
        $this->createActivityList();
    }

    /*
     * list and add friends / contacts tab
     */
    public function networkAction() {
        $translate   = $this->_owApp->translate;
        $store       = $this->_owApp->erfurt->getStore();
        $model       = $this->_owApp->selectedModel;

        $this->view->placeholder('main.window.title')->set($translate->_('Network'));
        $this->addModuleContext('main.window.dssn.network');
    }

    /*
     * uses the listHelper to re-get / create the activity stream
     */
    private function createActivityList() {
        // tool setup
        $config = $this->_privateConfig;
        $uris   = $config->uris;
        $store  = $this->_owApp->erfurt->getStore();
        $model  = $this->_owApp->selectedModel;
        $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('List');

        // list parameters
        $name     = "dssn-activities";
        $template = "list_dssn_activities_main";

        if(!$helper->listExists($name)) {
            // create a new list from scratch if we do not have one
            $list = new OntoWiki_Model_Instances($store, $model, array());

            // restrict to activities
            $list->addTypeFilter($uris->Activity);

            // build the triple pattern
            $triplePattern = array();

            // ?s atom:published ?published (bound)
            $publishedVar = new Erfurt_Sparql_Query2_Var('published');
            $publishedIri = new Erfurt_Sparql_Query2_IriRef($uris->published);
            $triplePattern[] = new Erfurt_Sparql_Query2_Triple(
                $list->getResourceVar(), $publishedIri, $publishedVar);

            // ?s aair:activityVerb ?verb (bound)
            $verbVar = new Erfurt_Sparql_Query2_Var('verb');
            $verbIri = new Erfurt_Sparql_Query2_IriRef($uris->activityVerb);
            $triplePattern[] = new Erfurt_Sparql_Query2_Triple(
                $list->getResourceVar(), $verbIri, $verbVar);

            // ?s aair:activityActor ?actor (bound)
            $actorVar = new Erfurt_Sparql_Query2_Var('actor');
            $actorIri = new Erfurt_Sparql_Query2_IriRef($uris->activityActor);
            $triplePattern[] = new Erfurt_Sparql_Query2_Triple(
                $list->getResourceVar(), $actorIri, $actorVar);

            // ?s aair:activityObject ?object (bound)
            $objectVar = new Erfurt_Sparql_Query2_Var('object');
            $objectIri = new Erfurt_Sparql_Query2_IriRef($uris->activityObject);
            $triplePattern[] = new Erfurt_Sparql_Query2_Triple(
                $list->getResourceVar(), $objectIri, $objectVar);

            $list->addTripleFilter($triplePattern);

            // TODO: add FILTER (?published < "$now")
            //$list->addFilter ($property, $isInverse, $propertyLabel, $filter, $value1 = null, $value2 = null, $valuetype = 'literal', $literaltype = null, $hidden = false, $id = null, $negate = false)

            // value query variables
            $list->addShownProperty($uris->published, 'published');
            $list->addShownProperty($uris->activityActor, 'actor');
            $list->addShownProperty($uris->activityObject, 'object');
            $list->addShownProperty($uris->activityVerb, 'verb');

            // TODO: additional optional!! projection vars:
            // ?actor  aair:avatar ?avatar (optional)
            // ?object a ?objectType (optional)
            // ?object aair:content ?content (optional)
            // ?object aair:thumbnail ?thumbnail (optional)

            // add order by published timestamp
            $list->setOrderVar($publishedVar, false);

            // add the list to the session
            $helper->addListPermanently($name, $list, $this->view, $template, $config);
        } else {
            // catch the name list from the session
            $list = $helper->getList($name);
            // re-add the list to the page
            $helper->addList($name, $list, $this->view, $template, $config);
        }
        //var_dump((string) $list->getResourceQuery());
    }

}

