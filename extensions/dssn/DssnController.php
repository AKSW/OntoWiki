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

    public $listname = "dssn-activities";

    /*
     * working model for dssn
     */
    public $model = false;

    public function init() {
        parent::init();

        // register DSSN classes
        $this->registerLibrary();

        // check for model etc.
        $this->setupWiki();

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
        OntoWiki_Navigation::register('profile', array(
            'route'      => null,
            'controller' => 'dssn',
            'action'     => 'profile',
            'name'       => 'Profile' ));
        OntoWiki_Navigation::register('setup', array(
            'route'      => null,
            'controller' => 'dssn',
            'action'     => 'setup',
            'name'       => 'Setup' ));

        // add dssn specific styles and javascripts
        $this->view->headLink()->appendStylesheet($this->_componentUrlBase . 'css/dssn.css');
        $this->view->headScript()->appendFile($this->_componentUrlBase . 'js/dssn.js');
    }

    /*
     * activity stream atom feed action
     */
    public function feedAction() 
    {
        // service controller needs no view renderer
        $this->_helper->viewRenderer->setNoRender();
        // disable layout for Ajax requests
        $this->_helper->layout()->disableLayout();

        $response  = $this->getResponse();
        $model     = $this->model;
        $output    = false;

        try {
            // TODO: filter this query (activities by OW owner only)
            $query = Erfurt_Sparql_SimpleQuery::initWithString('
                SELECT DISTINCT ?resourceUri
                WHERE {
                    ?resourceUri <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://xmlns.notu.be/aair#Activity> .
                        ?resourceUri <http://www.w3.org/2005/Atom/published> ?published .
                        ?resourceUri <http://xmlns.notu.be/aair#activityVerb> ?verb .
                        ?resourceUri <http://xmlns.notu.be/aair#activityActor> ?actor .
                        ?resourceUri <http://xmlns.notu.be/aair#activityObject> ?object
                }
                ORDER BY DESC(?published)
                LIMIT 10'
            );
            $results = $model->sparqlQuery($query);
// TODO: if empty $feed is never filled... error
            if ($results) {
                $feed = new DSSN_Activity_Feed();
                $feed->setTitle('Activity Feed @ ' . $this->model . ' (OntoWiki)');
                $feed->setLinkSelf($this->_config->urlBase . 'dssn/feed');
                $feed->setLinkHtml($this->_config->urlBase . 'dssn/news');
                $feed->setLinkHub($this->_config->urlBase . 'pubsub/hubbub');

                $factory  = new DSSN_Activity_Factory($this->_owApp);
                foreach ($results as $key => $result) {
                    $iri      = $result['resourceUri'];
                    $activity = $factory->getFromStore($iri, $model);
                    $feed->addActivity($activity);
                }
            }

        } catch (Exception $e) {
            // encode the exception for http response
            $output = $e->getMessage();
            $response->setRawHeader('HTTP/1.1 500 Internal Server Error');
            $response->sendResponse();
            exit;
        }

        // Send the response containing the feed.
        $this->_response->setHeader('Content-Type', 'application/atom+xml', true);
        $this->_response->setBody($feed->toXml());
        return;
    }

    /*
     * Setup / Configuration
     */
    public function setupAction() {
        $translate  = $this->_owApp->translate;

        $this->view->placeholder('main.window.title')->set($translate->_('Setup / Configure your DSSN Client'));
        $this->addModuleContext('main.window.dssn.setup');

        //$factory  = new DSSN_Activity_Factory($this->_owApp);
        //$activity = $factory->getFromStore('http://example.org/Activities/e21c7abc6a9b97e8edd30508fede5384');

        $factory  = new DSSN_Activity_Factory($this->_owApp);
        $activity = $factory->newStatus("test content");
        $entry    = $activity->toAtomEntry();
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->appendChild($dom->importNode($entry, true));
        echo var_dump($dom->saveXML());

        //$model  = $this->model;
        //$store  = $this->_owApp->erfurt->getStore();
        //$store->addMultipleStatements((string) $model, $activity->toRDF());
    }

    /*
     * Profile View / Editor
     */
    public function profileAction() {
        $translate  = $this->_owApp->translate;

        $this->view->placeholder('main.window.title')->set($translate->_('Profile Viewer / Editor'));
        $this->addModuleContext('main.window.dssn.profile');
    }

    /*
     * news & activities tab
     */
    public function newsAction() {
        $translate  = $this->_owApp->translate;

        $this->view->placeholder('main.window.title')->set($translate->_('News & Activities'));

        if($this->_owApp->selectedModel == null){
            throw new OntoWiki_Exception("no model selected");
        }

        // inserts the activity stream list
        $this->createActivityList();

        $this->addModuleContext('main.window.dssn.news');
    }

    /*
     * add activity by post
     */
    public function addactivityAction() {
        // service controller needs no view renderer
        $this->_helper->viewRenderer->setNoRender();
        // disable layout for Ajax requests
        $this->_helper->layout()->disableLayout();

        $response  = $this->getResponse();
        $output    = false;

        try {
            $factory  = new DSSN_Activity_Factory($this->_owApp);
            $activity = $factory->newFromShareItModule($this->_request);

            $model  = $this->model;
            $store  = $this->_owApp->erfurt->getStore();
            $store->addMultipleStatements((string) $model, $activity->toRDF());

            $event = new Erfurt_Event('onInternalFeedDidChange');
            $event->feedUrl = $this->_owApp->getUrlBase() . 'dssn/feed';
            $event->trigger();

            $output   = array (
                'message' => 'Activity saved',
                'class'   => 'success'
            );

            // create an event so that the hub can send the data
            $event = new Erfurt_Event('pubsub-onFeedChange');
            $event->feedUrl = $this->_config->urlBase . '/dssn/feed';
            $event->trigger();

        } catch (Exception $e) {
            // encode the exception for http response
            $output = array (
                'message' => $e->getMessage(),
                'class'   => 'error'
            );
            $response->setRawHeader('HTTP/1.1 500 Internal Server Error');
        }

        // send the response
        //$response->setHeader('Content-Type', 'application/json');
        $response->setBody(json_encode($output));
        $response->sendResponse();
        exit;
    }

    /*
     * list and add friends / contacts tab
     */
    public function networkAction() {

        $translate   = $this->_owApp->translate;
        $store       = $this->_owApp->erfurt->getStore();
        $model       = $this->model;

        $this->view->placeholder('main.window.title')->set($translate->_('Network'));

        $res = $store->sparqlQuery('PREFIX foaf:<http://xmlns.com/foaf/0.1/> SELECT ?me FROM <'.$this->_owApp->selectedModel->getModelIri().'> WHERE {<'.$this->_owApp->selectedModel->getModelIri().'> a foaf:PersonalProfileDocument . <'.$this->_owApp->selectedModel->getModelIri().'> foaf:primaryTopic ?me}');
        if(is_array($res) && !empty ($res)){
            $me = $res[0]['me'];
        } else {
            $this->_sendResponse(false, "the model must contain a foaf profile", OntoWiki_Message::ERROR);
            return;
        }
        $this->_handleNewFriend($me);

        $config = $this->_privateConfig;
        $store  = $this->_owApp->erfurt->getStore();
        $model  = $this->model;
        $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('List');

        // list parameters
        $listname     = "list_dssn_network";
        $template = "list_dssn_network_main";

        //get the persons
        if(!$helper->listExists($listname)) {
            // create a new list from scratch if we do not have one
            $list = new OntoWiki_Model_Instances($store, $model, array());

            // restrict to persons
            $list->addTypeFilter(DSSN_FOAF_Person);

            //restrict to persons that i know
            $list->addFilter(DSSN_FOAF_knows, true, "knows", "equals", $me, null, 'uri');

            //get properties
            $list->addShownProperty(DSSN_FOAF_depiction);
            $list->addShownProperty(DSSN_FOAF_nick);

            // add the list to the session
            $helper->addListPermanently($listname, $list, $this->view, $template, $config);
        } else {
            // catch the name list from the session
            $list = $helper->getList($listname);
            //echo htmlentities($list->getQuery());
            // re-add the list to the page
            $helper->addList($listname, $list, $this->view, $template, $config);
        }
        //var_dump((string) $list->getResourceQuery());
        //var_dump((string) $list->getQuery());
        $this->addModuleContext('main.window.dssn.network');
    }


    private function _handleNewFriend($me)
    {
        $store = $this->_erfurt->getStore();
        if(($friendUri = $this->getParam("friend-input")) != null){
            $importIntoGraphUri = $friendUri;
            if($store->isModelAvailable($importIntoGraphUri)){
                $this->_sendResponse(false, 'already imported', OntoWiki_Message::INFO);
            } else {
                // create model
                $graph = $store->getNewModel($importIntoGraphUri);
                //hide
                $graph->setOption($this->_config->sysont->properties->hidden, array(array(
                            'value'    => 'true',
                            'type'     => 'literal',
                            'datatype' => EF_XSD_BOOLEAN
                        )));
                //declare import of new model to current
                $store->addStatement($this->_owApp->selectedModel->getModelIri(), $me,  EF_OWL_NS.'imports', array('value'=>$importIntoGraphUri, 'type'=>'uri'));
                
                //connect me to that person
                $store->addStatement($this->_owApp->selectedModel->getModelIri(), $me, DSSN_FOAF_knows, array('value'=>$friendUri, 'type'=>'uri'));

                //fill new model via linked data
                require_once $this->_owApp->extensionManager->getExtensionPath("datagathering") . DIRECTORY_SEPARATOR . "DatagatheringController.php";
                $res = DatagatheringController::import($importIntoGraphUri, $friendUri, $friendUri);

                //get feed
                $res = $store->sparqlQuery('SELECT ?feed FROM <'.$importIntoGraphUri.'> WHERE {<'.$importIntoGraphUri.'> <http://ns.aksw.org/hasFeed> ?feed }');
                if(is_array($res) && !empty ($res)){
                    $topicUrl = $res[0]['feed'];
                } else {
                    $this->_sendResponse(false, "the model must link to a feed", OntoWiki_Message::ERROR);
                    return;
                }
                
                $hubUrl = null; 
                try {
                    $feed = new Zend_Feed_Atom($topicUrl);

                    $hubUrl = $feed->link('hub');
                    if (null == $hubUrl) {
                        $this->_owApp->appendMessage(
                            new OntoWiki_Message('Feed has no hub.', OntoWiki_Message::ERROR)
                        );
                        $this->_log('Feed has no hub: ' . $topicUrl); 
                        return;
                    }
                } catch (Exception $e) {
                    $this->_owApp->appendMessage(
                        new OntoWiki_Message('Failed to retrieve feed.', OntoWiki_Message::ERROR)
                    );
                    $this->_log('Failed to retrieve feed: ' . $e->getMessage()); 
                    return;
                }
                
                //subscribe to its feed
                $callbackUrl = PubsubController::getCallbackUrl();
                $s = new Subscriber($hubUrl, $callbackUrl);
                ob_start();
                $response = $s->subscribe($topicUrl);
                $result = ob_get_clean();

                //$this->_log('Subscriber Result: ' . $result);

                $success = false;
                if ($response == false) {
                    $success = true;
                }
                
                $err = true;
                if($success == false){
                    $err = true;
                    $this->_sendResponse(true,'could not subscribe with pubsubhubub.', OntoWiki_Message::INFO);
                } else if($res == DatagatheringController::IMPORT_OK){
                    $err = false;
                    $this->_sendResponse(true,'Data was found for the given URI. Statements were added.', OntoWiki_Message::INFO);
                } else if($res == DatagatheringController::IMPORT_WRAPPER_ERR){
                    $this->_sendResponse(false, 'The wrapper had an error.', OntoWiki_Message::ERROR);
                } else if($res == DatagatheringController::IMPORT_NO_DATA){
                    $this->_sendResponse(false, 'No statements were found.', OntoWiki_Message::ERROR);
                } else if($res == DatagatheringController::IMPORT_WRAPPER_INSTANCIATION_ERR){
                    $this->_sendResponse(false, 'could not get wrapper instance.', OntoWiki_Message::ERROR);
                    //$this->_response->setException(new OntoWiki_Http_Exception(400));
                } else if($res == DatagatheringController::IMPORT_NOT_EDITABLE){
                    $this->_sendResponse(false, 'you cannot write to this model.', OntoWiki_Message::ERROR);
                    //$this->_response->setException(new OntoWiki_Http_Exception(403));
                } else if($res == DatagatheringController::IMPORT_WRAPPER_EXCEPTION){
                    $this->_sendResponse(false, 'the wrapper run threw an error.', OntoWiki_Message::ERROR);
                } else {
                    $this->_sendResponse(false, 'unexpected return value.', OntoWiki_Message::ERROR);
                }

                if($err){
                    //rollback changes
                    $store->deleteModel($importIntoGraphUri);
                }
            }
        }
    }
    
    public static function reloadFriendModel($uri){
        DatagatheringController::import($uri, $uri, $uri, true, array(), array(), 'linkeddata', 'none', 'update');
    }

    private function _sendResponse($returnValue, $message = null, $messageType = OntoWiki_Message::SUCCESS)
    {
        if (null !== $message) {
            $translate = $this->_owApp->translate;

            $message = $translate->_($message);
            $this->_owApp->appendMessage(
                new OntoWiki_Message($message, $messageType)
            );
        }
    }

    /*
     * checks for user/model etc. (and creates them if needed)
     */
    private function setupWiki()
    {
        $ow          = OntoWiki::getInstance();
        $store       = $ow->erfurt->getStore();
        $this->model = $ow->selectedModel;
        $webid       = $ow->user->getUri();

        if (!isset($this->model)) {
            try {
                $models = $store->getAvailableModels(true);
                if (isset($models[$webid])) {
                    // try to load the webid model
                    $this->model = $store->getModel($webid);
                } elseif (isset($models[$this->_config->urlBase])) {
                    // try to load the model which has sem urlBase URI
                    $this->model = $store->getModel($this->_config->urlBase);
                } else {
                    // try to create a new model (url = webid)
                    $newModel = $store->getNewModel($webid);
                    $this->model = $store->getModel($webid);
                }
                $ow->selectedModel = $this->model;
            } catch (Exception $e) {
                $message = $e->getMessage();
                die('There is no space available for you here: ' . $message);
            }
        }
    }

    /*
     * This adds a new path and namespace to the autoloader
     */
    private function registerLibrary()
    {
        $newIncludePath = ONTOWIKI_ROOT . '/extensions/dssn/libraries/lib-dssn-php';
        set_include_path(get_include_path() . PATH_SEPARATOR . $newIncludePath);
        // see http://framework.zend.com/manual/en/zend.loader.load.html
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace('DSSN_');
        DSSN_Utils::setConstants();
    }

    /*
     * uses the listHelper to re-get / create the activity stream
     */
    private function createActivityList() {
        // tool setup
        $config = $this->_privateConfig;
        $store  = $this->_owApp->erfurt->getStore();
        $model  = $this->model;
        $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('List');

        // list parameters
        $listname     = $this->listname;
        $template = "list_dssn_activities_main";

        //react on filter activity module requests
        $name = $this->getParam("name");
        $value = $this->getParam("value");

        if($name !== null && $value !== null && $helper->listExists($listname)){
            $list = $helper->getList($listname);
            switch ($name){
                case "activityverb":
                    if (!empty($_SESSION['DSSN_activityverb'])) {
                        $splitted= explode("/", $_SESSION['DSSN_activityverb']);
                        $id = $splitted[0];
                        $list->removeFilter($id);
                    }
                    if($value !== "all"){
                        $parts= explode("/",$value,2);
                        $uriparts =  explode("/",$parts[1]);
                        $label = end($uriparts);
                        $id = $list->addFilter(DSSN_AAIR_activityVerb, false, $label, "equals", $value, null, "uri");
                        $_SESSION['DSSN_activityverb'] = $id."/".$value;
                    } else {
                        $_SESSION['DSSN_activityverb'] = "all";
                    }
                    break;
                case "activityobject":
                    if (!empty($_SESSION['DSSN_activityobject'])) {
                        $splitted= explode("/", $_SESSION['DSSN_activityobject']);
                        $id = $splitted[0];
                        $list->removeFilter($id);
                    }
                    if($value !== "all"){
                        $triples = array(
                            new Erfurt_Sparql_Query2_Triple(
                                new Erfurt_Sparql_Query2_Var('object'),
                                new Erfurt_Sparql_Query2_IriRef(DSSN_RDF_type),
                                new Erfurt_Sparql_Query2_IriRef($value)
                            )
                        );
                        $id = $list->addTripleFilter($triples);
                        $_SESSION['DSSN_activityobject'] = $id."/".$value;
                    } else {
                        $_SESSION['DSSN_activityobject'] = "all";
                    }
                    break;
            }
        }

        //get the activities
        //if(!$helper->listExists($listname)) {
        if(true) {
            // create a new list from scratch if we do not have one
            $list = new OntoWiki_Model_Instances($store, $model, array());

            // restrict to activities
            $list->addTypeFilter(DSSN_AAIR_Activity);

            // build the triple pattern
            $triplePattern = array();
            $resVar = $list->getResourceVar();

            // ?s atom:published ?published (bound)
            $publishedVar = new Erfurt_Sparql_Query2_Var('published');
            $publishedIri = new Erfurt_Sparql_Query2_IriRef(DSSN_ATOM_published);
            $triplePattern[] = new Erfurt_Sparql_Query2_Triple(
                $resVar, $publishedIri, $publishedVar);

            // ?s aair:activityVerb ?verb (bound)
            $verbVar = new Erfurt_Sparql_Query2_Var('verb');
            $verbIri = new Erfurt_Sparql_Query2_IriRef(DSSN_AAIR_activityVerb);
            $triplePattern[] = new Erfurt_Sparql_Query2_Triple(
                $resVar, $verbIri, $verbVar);

            // ?s aair:activityActor ?actor (bound)
            $actorVar = new Erfurt_Sparql_Query2_Var('actor');
            $actorIri = new Erfurt_Sparql_Query2_IriRef(DSSN_AAIR_activityActor);
            $triplePattern[] = new Erfurt_Sparql_Query2_Triple(
                $resVar, $actorIri, $actorVar);

            // ?s aair:activityObject ?object (bound)
            $objectVar = new Erfurt_Sparql_Query2_Var('object');
            $objectIri = new Erfurt_Sparql_Query2_IriRef(DSSN_AAIR_activityObject);
            $triplePattern[] = new Erfurt_Sparql_Query2_Triple(
                $resVar, $objectIri, $objectVar);

            $list->addTripleFilter($triplePattern);

            // add FILTER (?published < now)
            //$list->addFilter ($uris->published, false, "filterPublished", "smaller", (string) time(), null, 'literal', 'int');

            // value query variables
            $list->addShownProperty(DSSN_ATOM_published, 'published');
            $list->addShownProperty(DSSN_AAIR_activityActor, 'actor');
            $list->addShownProperty(DSSN_AAIR_activityObject, 'object');
            $list->addShownProperty(DSSN_AAIR_activityVerb, 'verb');

            // currently, indirect properties do not work :-(
            //// add complex shown properties (indirect)
            //// ?actor  aair:avatar ?avatar
            //$prop1 = array();
            //$prop1[] = new Erfurt_Sparql_Query2_Triple($resVar, $actorIri, $actorVar); //this triple is a duplicate, but will be optimized out and may be cleaner that way
            //$avatarIri = new Erfurt_Sparql_Query2_IriRef(DSSN_AAIR_avatar);
            //$avatarVar = new  Erfurt_Sparql_Query2_Var('avatar');
            //$prop1[] = new Erfurt_Sparql_Query2_Triple($actorVar, $avatarIri, $avatarVar);
            //$list->addShownPropertyCustom($prop1, $avatarVar);

            //// ?object a ?objectType
            //$prop2 = array();
            //$prop2[] = new Erfurt_Sparql_Query2_Triple($resVar, $objectIri, $objectVar); // also duplicate
            //$typeIri = new Erfurt_Sparql_Query2_IriRef(EF_RDF_TYPE);
            //$typeVar = new  Erfurt_Sparql_Query2_Var('objectType');
            //$prop2[] = new Erfurt_Sparql_Query2_Triple($objectVar, $typeIri, $typeVar);
            //$list->addShownPropertyCustom($prop2, $typeVar);

            //// ?object aair:content ?content
            //$prop3 = array();
            //$prop3[] = new Erfurt_Sparql_Query2_Triple($resVar, $objectIri, $objectVar); // also duplicate
            //$contentIri = new Erfurt_Sparql_Query2_IriRef(DSSN_AAIR_content);
            //$contentVar = new  Erfurt_Sparql_Query2_Var('content');
            //$prop3[] = new Erfurt_Sparql_Query2_Triple($objectVar, $contentIri, $contentVar);
            //$list->addShownPropertyCustom($prop3, $contentVar);

            //// ?object aair:thumbnail ?thumbnail
            //$prop4 = array();
            //$prop4[] = new Erfurt_Sparql_Query2_Triple($resVar, $objectIri, $objectVar); // also duplicate
            //$thumbnailIri = new Erfurt_Sparql_Query2_IriRef(DSSN_AAIR_thumbnail);
            //$thumbnailVar = new  Erfurt_Sparql_Query2_Var('thumbnail');
            //$prop4[] = new Erfurt_Sparql_Query2_Triple($objectVar, $thumbnailIri, $thumbnailVar);
            //$list->addShownPropertyCustom($prop4, $thumbnailVar);

            // add order by published timestamp
            $list->setOrderVar($publishedVar, false);

            // add the list to the session
            $helper->addListPermanently($listname, $list, $this->view, $template, $config);
        } else {
            // catch the name list from the session
            $list = $helper->getList($listname);
            echo htmlentities($list->getResourceQuery());
            // re-add the list to the page
            $helper->addList($listname, $list, $this->view, $template, $config);
        }

        //var_dump((string) $list->getResourceQuery());
        //var_dump((string) $list->getQuery());
    }


}

