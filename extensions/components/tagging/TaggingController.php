<?php

require_once 'OntoWiki/Controller/Component.php';
require_once 'Erfurt/Sparql/SimpleQuery.php';
require_once 'OntoWiki/Model/TitleHelper.php';

/**
 * Controller for OntoWiki Tagging Modules
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_tagging
 * @copyright  Copyright (c) 2010, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

class TaggingController extends OntoWiki_Controller_Component
{

    public $newmessage = '';
    public $messagetype = '';

    private $model;
    private $store;
    private $translate;
    private $ac;
    private $resource;
    private $session;

    public function init()
    {
        parent::init();
        $this->store       = $this->_owApp->erfurt->getStore();
        $this->translate   = $this->_owApp->translate;
        $this->resource    = $this->_owApp->selectedResource;
        $this->session     = $this->_owApp->session;

        $this->model = $this->_owApp->selectedModel;
        if (isset($this->_request->m)) {
            $this->model = $store->getModel($this->_request->m);
        }
        if (empty($this->model)) {
            require_once 'OntoWiki/Exception.php';
            throw new OntoWiki_Exception('Missing parameter m (model)
            and no selected model in session!');
            exit;
        }

        $this->ac = $this->_erfurt->getAc();

    }

    /**
     * Returns an array of tags assoziated with the resource
     * returns an empty array if no tag available
     *
     * @param string $resource the resource URI
     */
    private function getTagsForResource ($resource = '') {

        $tags = array();

        // get all tagresources and properties for this resource
        // note: property is used for RDFa and maybe
        // later more than prop is fetched here
        $tagsQuery = "SELECT DISTINCT * WHERE {
            <".$resource."> <".$this->_privateConfig->tagproperty."> ?uri .
            <".$resource."> ?property ?uri .
        }";
        $tagsresult = $this->model->sparqlQuery($tagsQuery);
        if (!empty($tagsresult)) {

            // ok, we have tags, so start and feed the titleHelper
            $titleHelper = new OntoWiki_Model_TitleHelper($this->model);
            foreach ($tagsresult as $tag) {
                $titleHelper->addResource($tag['uri']);
            }

            // for link creation
            $linkurl = new OntoWiki_Url(array('route' => 'properties'), array('r'));

            // now fetch the titles and feed the view result
            $tagcount = 0;
            $unsortedTags = array();
            $unsortedKeys = array();
            foreach ($tagsresult as $tag) {
                /*
                 * prepare tagdata (uri and property is
                 * already set from the query
                 */
                $tag['title'] = $titleHelper->getTitle($tag['uri']);
                $tag['link'] = (string) $linkurl->setParam('r', $tag['uri'], true);

                // add tag to result tags and feed title sort array
                $unsortedTags[ $tag['title'].$tagcount ] = $tag;
                $unsortedKeys[] = $tag['title'].$tagcount;
                $tagcount++;
            }

            // sort the tagsoutput according to the key (title+counter)
            // maybe here we can multisort?
            natcasesort($unsortedKeys);
            foreach ($unsortedKeys as $key) {
                $tags[] = $unsortedTags[$key];
            }
            #var_dump($tags);
        }
        return $tags;
    }

    /**
     * Add new tag for resource
     * @return unknown_type
     */
    public function addtagAction()
    {
        // Model Based Access Control
        if (!$this->ac->isModelAllowed('edit', $this->model->getModelIri()) ) {
            require_once 'Erfurt/Ac/Exception.php';
            throw new Erfurt_Ac_Exception('You are not allowed to add
            tags in this model.');
        }

        // tagging controller needs no view renderer
        $this->_helper->viewRenderer->setNoRender();
        // disable layout for Ajax requests
        // $this->_helper->layout()->disableLayout();

        $store    = $this->store;
        $response = $this->getResponse();
        $resource = $this->resource;
        $modelURI = $this->model->getModelIri();
        $conf     = $this->_privateConfig;
        $request  = $this->_request;

        // fetch resources parameter
        if (isset($request->resources)) {
            $resources = $request->getParam('resources');
        } else {
            require_once 'OntoWiki/Exception.php';
            throw new OntoWiki_Exception('Missing parameter resources!');
            exit;
        }

        // fetch tagresource and/or tag parameter
        if (isset($request->tagresource) || isset($request->tag)) {
            if (isset($request->tag)) {
                $tag = trim($request->getParam('tag'));
            } else {
                $tag = null;
            }

            if (isset($request->tagresource)) {
                $tagresource = $request->getParam('tagresource');
            } else {
                $tagresource = null;
            }
        }

        // check, if newtag true or false
        if (isset($request->newtag)) {
            $newtag = (bool) $request->getParam('newtag');
        } else {
            $newtag = (bool) $conf->defaults->newtag;
        }

        // if both tagresource and tag are missing
        if ($tagresource == null && $tag == null) {
            $this->messagetype = 'error';
            $this->newmessage = 'Please provide a valid tagname';
        }

        $jsonResources = json_decode($resources);
        foreach ($jsonResources as $singleResource) {
            // newtag is true, so we create a new tag
            if ($newtag) {
                /**
                 * Choosing a case
                 *
                 * 1. given resource, tagresource and tag
                 * 2. given resource and tag
                 * 3. given resource and tagresource
                 */

                // given resource, tagresource and tag
                if ($singleResource != null && $tagresource != null && $tag != null) {
                    // add tag using resource, tagresource and tag
                    $this->taggingResTagresTag($singleResource, $tagresource, $tag);
                }
                // Given resource and tag
                if ($singleResource != null && $tagresource == null && $tag != null) {
                    // add tag using resource and tag
                    $this->taggingResTag($singleResource, $tag);
                }
                // given resource and tagresource
                if ($singleResource != null && $tagresource != null && $tag == null) {
                    // add tag using resource and tagresource
                    $this->taggingResTagres($singleResource, $tagresource);
                }
                /*
                 *  newtag is false, so we are looking for existing tag,
                 *  which can be used
                 */
            } else {
                // get tagresource for the selected tag
                $trQuery = 'SELECT ?tagresource WHERE {
                    ?tagresource <'.$conf->tagname.'> ?literal
                    FILTER (regex(?literal, "^'.$tag.'$", "i")) . 
                } LIMIT 1';

                $trExisting = $this->model->sparqlQuery($trQuery);
                // if tagresource not empty, then a tag with the same name exist
                if (!empty($trExisting) && $tag != null) {
                    // get the tagresource which will be used
                    foreach ($trExisting as $key => $value) {
                        $tagresource = (string) $value['tagresource'];
                    }

                    // check, if resource already tagged with this tagresource
                    $where = 'WHERE {?s ?p ?o FILTER (
                        sameTerm(?s, <'.$singleResource.'>) &&
                        sameTerm(?p, <'.$conf->tagproperty.'>) && 
                        sameTerm(?o, <'.$tagresource.'>)
                    ) . }';
                    $count = $store->countWhereMatches($modelURI, $where, "*");

                    // if the resource is not tagged with this tagresource
                    if ($count == 0) {
                        $this->taggingResTagres($singleResource, $tagresource);
                    } else {
                        $this->messagetype = 'info';
                        $this->newmessage = 'The resource is already tagged with this tag';
                    }
                } else {
                    /*
                     * the newtag parameter is not set, but doesn't matter....
                     * the resource will be tagged
                     *
                     * Again 3 cases
                     * 1. given resource, tagresource and tag
                     * 2. given resource and tagresource
                     * 3. given resource and tag
                     */
                    if ($singleResource != null && $tagresource != null && $tag != null) {
                        // add tag using resource, tagresource and tag
                        $this->taggingResTagresTag($singleResource, $tagresource, $tag);
                    } else if ($singleResource != null && $tagresource != null && $tag == null) {
                        // add tag using resource and tagresource
                        $this->taggingResTagres($singleResource, $tagresource);
                    } else if ($singleResource != null && $tagresource == null && $tag != null) {
                        // add tag using resource and tag
                        $this->taggingResTag($singleResource, $tag);
                    }
                }
            }

            // Render the tags
            $tags = $this->listtagsAction();
        }

    }

    /**
     * Remove a tag
     */
    public function deltagAction()
    {
        // Model Based Access Control
        if (!$this->ac->isModelAllowed('edit', $this->model->getModelIri()) ) {
            require_once 'Erfurt/Ac/Exception.php';
            throw new Erfurt_Ac_Exception('You are not allowed to
            delete tags in this model.');
        }

        // tagging controller needs no view renderer
        $this->_helper->viewRenderer->setNoRender();
        // disable layout for Ajax requests
        // $this->_helper->layout()->disableLayout();

        $store       = $this->store;
        $response    = $this->getResponse();
        $resource    = $this->resource;
        $model       = $this->model;
        $modelURI    = $this->model->getModelIri();
        $conf        = $this->_privateConfig;

        // fetch resource parameter
        if (isset($this->_request->resources)) {
            $resources = $this->_request->getParam('resources');
        } else {
            require_once 'OntoWiki/Exception.php';
            throw new OntoWiki_Exception('Missing parameter resources!');
            exit;
        }

        // fetch tag parameter
        if (isset($this->_request->tag)) {
            $tag = $this->_request->getParam('tag');
        } else {
            $tag = null;
        }

        // fetch tagresource parameter

        if (isset($this->_request->tagresource)) {
            $tagresource = $this->_request->getParam('tagresource');
        } else {
            $tagresource = null;
        }

        if ($tagresource == null && $tag == null) {
            require_once 'OntoWiki/Exception.php';
            throw new OntoWiki_Exception('Please select parameter tag or
            tagresource or both!');
            exit;
        }

        // preparing versioning
        $versioning                 = $this->_erfurt->getVersioning();
        $actionSpec                 = array();
        $actionSpec['modeluri']     = (string) $this->model;
        $actionSpec['resourceuri']  = $resource;

        $jsonResources = json_decode($resources);
        foreach ($jsonResources as $singleRes) {
            /**
             * Choosing a fall
             *
             * 1. given resource, tagresource and tag
             * 2. given resource and tag
             * 3. given resource and tagresource
             */

            if (isset($this->_request->resources) &&
            isset($this->_request->tagresource) &&
            isset($this->_request->tag)) {
                 
                /*
                 * check how many triples with this resource,
                 * tagresource and tag are existing
                 */
                $countQuery = 'SELECT * WHERE {?s ?twt ?p . ?p ?tagname ?o .
                FILTER(
                    sameTerm(?s, <'.$singleRes.'>) &&
                    sameTerm(?p, <'.$tagresource.'>) &&
                    sameTerm(?o, <'.$tag.'>)
                )}';
                $countRes = count($this->model->sparqlQuery($countQuery));

                /*
                 * check if tagresource->tagname->tag was changed
                 * $countTCh
                 */
                $countTCh = $this->checkTagnameChanged($tagresource);

                /*
                 * check if tagresource->type->Tag was changed
                 * $countTyCh
                 */
                $countTyCh = $this->checkTagresrourceTypeChanged($tagresource);

                if ($countRes == 0) {
                    $this->messagetype = 'error';
                    $this->newmessage = 'There is nothing to delete.';
                } else if ($countRes == 1 && $countTCh == 0 && $countTyCh == 0) {
                    $this->remTagCompletly($singleRes, $tagresource, $tag);
                } else {
                    $this->remTwT($singleRes, $tagresource);
                }
            } else if ( isset($this->_request->resources) &&
            !isset($this->_request->tagresource) &&
            isset($this->_request->tag)) {
                var_dump("Debug");
                // get tagresource for the given tag and resource

                $trQuery = 'SELECT ?tagresource WHERE {
                <'.$singleRes.'> <'.$conf->tagproperty.'> ?tagresource .
                ?tagresource <'.$this->_privateConfig->tagname.'> "'.$tag.'"
            }';
                $tagresourceExist = $this->model->sparqlQuery($trQuery);
                if (!empty($tagresourceExist)) {
                    $tagresource = $tagresourceExist[0]['tagresource'];
                } else {
                    $this->messagetype = 'error';
                    $this->newmessage = 'There is nothing to delete.';
                }

                // how many resources are tagged with this tag
                $countRes = count($tagresourceExist);

                /*
                 * check if tagresource->tagname->tag was changed
                 * $countTCh
                 */
                $countTCh = $this->checkTagnameChanged($tagresource);

                /*
                 * check if tagresource->type->Tag was changed
                 * $countTyCh
                 */
                $countTyCh = $this->checkTagresrourceTypeChanged($tagresource);

                /**
                 * Delete tag completly when:
                 * 1. $countResources = 1
                 * 2. $countTagChanged = 0
                 * 3. $countTagresourceTypeChanged = 0
                 */

                if ($countRes == 0) {
                    $this->messagetype = 'error';
                    $this->newmessage = 'There is nothing to delete.';
                } else if ($countRes==1 && $countTCh==0 && $countTyCh==0) {
                    $this->remTagCompletly($singleRes, $tagresource, $tag);
                } else if ($countRes>=2 || $countTCh>=1 || $countTyCh>=1) {
                    $this->remTwT($singleRes, $tagresource);
                } else {
                    require_once 'OntoWiki/Exception.php';
                    throw new OntoWiki_Exception('An error has occurred.');
                    exit;
                }
            } else if ( isset($this->_request->resources) &&
            isset($this->_request->tagresource) &&
            !isset($this->_request->tag)) {

                // given resource and tag
                $newtag = $this->getTagForTagresource($tagresource);
                if ($newtag == '') {
                    $this->remTwT($singleRes, $tagresource);
                } else {
                    // check, if tags ist used by more than one resource
                    $query = 'SELECT DISTINCT ?r WHERE {
                    ?r <'.$conf->tagproperty.'> <'.$tagresource.'> .
                }';
                    $countRes = count($this->model->sparqlQuery($query));

                    /*
                     * check if tagresource->tagname->tag was changed
                     * $countTCh
                     */
                    $countTCh = $this->checkTagnameChanged($tagresource);

                    /*
                     * check if tagresource->type->Tag was changed
                     * $countTyCh
                     */
                    $countTyCh = $this->checkTagresrourceTypeChanged($tagresource);

                    if ($countRes == 0) {
                        $this->messagetype = 'error';
                        $this->newmessage = 'There is nothing to delete.';
                    } else if ($countRes==1 && $countTCh==0 && $countTyCh==0) {
                        $this->remTagCompletly($singleRes, $tagresource, $newtag);
                    } else if ($countRes>=2 || $countTCh>=1 || $countTyCh>=1) {
                        $this->remTwT($singleRes, $tagresource);
                    }
                }

            }
        }
         
        // Render the tags
        $tags = $this->listtagsAction();
    }

    /**
     * list all tags by rendering an RDFa enhanced ol list
     */
    public function listtagsAction()
    {
        // fetch resource parameter
        if (isset($this->_request->resources)) {
            $resources = $this->_request->getParam('resources');
        } else {
            require_once 'OntoWiki/Exception.php';
            throw new OntoWiki_Exception('Missing parameter $resources!');
            exit;
        }

        $jsonResources = json_decode($resources);
        foreach ($jsonResources as $singleResource) {
            // set view results
            $this->view->tags = $this->getTagsForResource($singleResource);
            if (empty($this->view->tags)) {
                $this->newmessage = $this->translate->_('No tags yet.');
                $this->messagetype = 'info';
            }

            if (!empty($this->newmessage)) {
                $this->view->message = $this->newmessage;
                if (!empty($this->messagetype)) {
                    $this->view->messagetype = $this->messagetype;
                } else {
                    $this->view->messagetype = 'info';
                }
            }

            // Render the Tags
            $listTagsTemplate = 'tagging/listtags.phtml';

            // tagging controller needs no view renderer
            $this->_helper->viewRenderer->setNoRender();
            $this->_response->setBody($this->view->render($listTagsTemplate));
        }
    }

    public function exploreAction() {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();

        $listHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('List');
        $instances = $listHelper->getLastList();

        if(!($instances instanceof OntoWiki_Model_Instances)){
            $this->_response->setBody("Error: List not found");
            return;
        }

        //set default cloudproperty
        if (!is_array($this->session->cloudproperties) || empty($this->session->cloudproperties)) {
            $this->session->cloudproperties = array($this->_privateConfig->tagproperty => array('label' => 'Tags', "uri" => $this->_privateConfig->tagproperty, "isInverse" => false));
        }

        //find selected tags by looking into filters
        $selectedTags = array();
        $selectedTagsInverse = array();
        $filters = $instances->getFilter();

        if (is_array($filters)) {
            foreach ($filters as $key => $filter) {
                $parts = explode('-', $filter['id']);
                if (count($parts) >= 3 && $parts[0] == 'explore') {
                    if ($parts[1] == 'normal') {
                        $selectedTags[] = $parts[2];
                    } elseif ($parts[1] == 'inverse') {
                        $selectedTagsInverse[] = $parts[2];
                    }
                }
            }
        }

        //init view vars
        $this->view->tags = array();
        $this->view->propertylabels = array();
        $limit = isset($this->_request->count) ?
            $this->_request->count :
            (isset($this->session->tagginglimit) ?
                $this->session->tagginglimit :
                $this->_privateConfig->defaults->count
            );
        $this->session->tagginglimit = $limit;
        $sort = isset($this->_request->sort) ?
            $this->_request->sort :
            (isset($this->session->taggingsort) ?
                $this->session->taggingsort :
                $this->_privateConfig->defaults->sort
            );
        $this->session->taggingsort = $sort;

        foreach ($this->session->cloudproperties as $cloudproperty) {
            //get values
            $values = $instances->getPossibleValues($cloudproperty["uri"], false, $cloudproperty["isInverse"]);
            //returns sparql-variable-solutions
            // (e.g. array("value" => <uri|literal>,
            //"type" => "uri"|"literal"|"typed-literal",
            //"xml:lang" => "de" [, "datatype" => <uri>]))

            if (!empty($values)) {
                //weighting in 3 steps

                //step 1: count frequency
                $count = array();
                foreach ($values as $key => $value) {
                    if (!isset($count[$value["value"]])) {
                        $count[$value["value"]] = 1;
                    } else {
                        $count[$value["value"]]++;
                    }

                    //by the way look if selected
                    if (($cloudproperty["isInverse"] && in_array($value["value"], $selectedTagsInverse)) || (!$cloudproperty["isInverse"] && in_array($value["value"], $selectedTags))) {
                        $values[$key]['selected'] = true;
                    } else {
                        $values[$key]['selected'] = false;
                    }
                }

                //step 2: remove duplicates
                $known = array();
                $new = array();
                foreach ($values as $key => $value) {
                    if (!in_array($value['value'], $known)) {
                        $known[] = $value['value'];
                        $new[] = $value;
                    }
                }
                $values = $new;

                //step 3: calculate weight
                $min = min($count);
                $max = max($count);
                foreach ($values as $key => $value) {
                    $values[$key]['weight'] = $this->calcWeight($min, $max, $this->_privateConfig->tagweightsMin, $this->_privateConfig->tagweightsMax, $count[$value["value"]]);
                }

                //get titles
                $titleHelper = new OntoWiki_Model_TitleHelper($this->model);
                foreach ($values as $value) {
                    if ($value['type'] == 'uri') {
                        $titleHelper->addResource($value['value']);
                    }
                }

                foreach ($values as $key=>$value) {
                    if ($value['type'] == 'uri') {
                        $values[$key]['title'] = $titleHelper->getTitle($value['value']);
                    } else {
                        $values[$key]['title'] = $value['value'];
                    }
                }

                //we have to delete tags because of the default limit of 10 taggs
                //we delete unimportant (low-weight) tags first so sort by weight then delete
                $presorted = false;
                if (count($values) > $limit) {
                    usort($values, array( $this, 'compare_weight'));
                    $presorted = true;
                    $values = array_slice($values, 0, $limit, true);
                }

                //sort
                if ($sort == "name") {
                    usort($values, array( $this, 'compare_title'));
                } else {
                    if (!$presorted) {
                        usort($values, array( $this, 'compare_weight'));
                    }
                }
            }

            //save data to view
            $this->view->tags[] = array("data" => $values, "info" => $cloudproperty);
        }

        $this->_response->setBody($this->view->render('tagging/exploretags.phtml'));
    }

    public function taggingtorelationAction () {
        // use:
        // Model Based Access Control
        if (!$this->ac->isModelAllowed('edit', $this->model->getModelIri()) ) {
            require_once 'Erfurt/Ac/Exception.php';
            throw new Erfurt_Ac_Exception('You are not allowed to add
            tags in this model.');
        }

        // tagging controller needs no view renderer
        $this->_helper->viewRenderer->setNoRender();
        // disable layout for Ajax requests
        // $this->_helper->layout()->disableLayout();

        $request  = $this->_request;
        $store          = $this->store;
        $model          = $this->model;

        // fetch parameters
        if (isset($request->oldRelation)) {
            $oldRelation = $request->getParam('oldRelation');
        } else {
            require_once 'OntoWiki/Exception.php';
            throw new OntoWiki_Exception('Missing parameter oldRelation!');
            exit;
        }
        if (isset($request->oldObject)) {
            $oldObject = $request->getParam('oldObject');
        } else {
            require_once 'OntoWiki/Exception.php';
            throw new OntoWiki_Exception('Missing parameter oldObject!');
            exit;
        }
        if (isset($request->newRelation)) {
            $newRelation = $request->getParam('newRelation');
        } else {
            require_once 'OntoWiki/Exception.php';
            throw new OntoWiki_Exception('Missing parameter newRelation!');
            exit;
        }
        if (isset($request->newObject)) {
            $newObject = $request->getParam('newObject');
        } else {
            require_once 'OntoWiki/Exception.php';
            throw new OntoWiki_Exception('Missing parameter newObject!');
            exit;
        }

        // get all resources tagged with this tag
        require_once 'OntoWiki/Model/Instances.php';
        $options = array(
            'type' => (string) $this->_owApp->selectedClass,
            'member_predicate' => EF_RDF_TYPE,
            'withChilds' => true,
            'limit' => 0,
            'offset' => 0,
            'shownProperties' => array(),
            'shownInverseProperties' => array(),
            'filter' => is_array($this->_session->filter) ?
                $this->_session->filter : array(),
        );

        $instances   = new OntoWiki_Model_Instances($store, $model, $options);
        $query = clone $instances->getResourceQuery();
        $query
        ->setDistinct(true)
        ->setLimit(0)
        ->setOffset(0)
        ->removeAllOptionals()->removeAllProjectionVars();
        //echo htmlentities($query);
        $resVar = new Erfurt_Sparql_Query2_Var('resources');
        $query->addTriple($resVar,
            new Erfurt_Sparql_Query2_IriRef($oldRelation),
            new Erfurt_Sparql_Query2_IriRef($oldObject));
        $query->addProjectionVar($resVar);
        # echo htmlentities($query);die;
        $getRes = $model->sparqlQuery($query);        
        
        if (!empty($getRes)) {
            foreach ($getRes as $row) {
                // preparing versioning
                $versioning                 = $this->_erfurt->getVersioning();
                $actionSpec                 = array();
                $actionSpec['modeluri']     = (string) $this->model;
                $actionSpec['resourceuri']  = $row['resources'];
                
                // delete statements
                $actionSpec['type']         = 134; // resource untaggt
                $versioning->startAction($actionSpec);
                $this->model->deleteMatchingStatements($row['resources'],
                    $oldRelation,
                    array('value' => $oldObject, 'type' => 'uri'));
                $versioning->endAction($actionSpec);

                $actionSpec['type']         = 133; // resource untaggt
                $versioning->startAction($actionSpec);
                $this->model->deleteMatchingStatements($oldObject, null, null);
                # echo htmlentities($row['resources'].
                # ' '.$oldRelation.
                # ' '.$oldObject).'<br/>';
                $versioning->endAction($actionSpec);
                
                // add statements
                $actionSpec['type']         = 136; // resource untaggt
                $versioning->startAction($actionSpec);
                $this->model->addStatement($row['resources'],
                    $newRelation,
                    array('value' => $newObject, 'type' => 'uri'));
                $versioning->endAction($actionSpec);
                                
                # echo htmlentities($row['resources'].
                #' '.$newRelation.
                #' '.$newObject).'<br/>';
            }
            echo "Convert completed successful";
        } else {
            echo "There isn't existing tag, which can be converted.";
        }
    }

    public function literaltoobjectAction() {
        // Model Based Access Control
        if (!$this->ac->isModelAllowed('edit', $this->model->getModelIri()) ) {
            require_once 'Erfurt/Ac/Exception.php';
            throw new Erfurt_Ac_Exception('You are not allowed to add
            tags in this model.');
        }

        // tagging controller needs no view renderer
        $this->_helper->viewRenderer->setNoRender();
        // disable layout for Ajax requests
        // $this->_helper->layout()->disableLayout();

        $request  = $this->_request;
        $store          = $this->store;
        $model          = $this->model;
        $conf           = $this->_privateConfig;

        // fetch parameter oP (oP = oldProperty)
        if (isset($request->oP)) {
            $oP = $request->getParam('oP');
        } else {
            require_once 'OntoWiki/Exception.php';
            throw new OntoWiki_Exception('Missing parameter oP (oldProperty)!');
            exit;
        }

        // fetch parameter nP (nP = newProperty)
        if (isset($request->nP)) {
            $nP = $request->getParam('nP');
        } else {
            $nP = $request->oldProperty;
        }

        // fetch parameter nC (nC = newClass)
        if (isset($request->nC)) {
            $nC = $request->getParam('nC');
        } else {
            require_once 'OntoWiki/Exception.php';
            throw new OntoWiki_Exception('Missing parameter nC (newClass)!');
            exit;
        }

        // fetch parameter nNP (nNP = newNamingProperty)
        if (isset($request->nNP)) {
            $nNP = $request->getParam('nNP');
        } else {
            $nNP = $conf->defaults->resOf;
        }

        // get all tagresources with labels
        require_once 'OntoWiki/Model/Instances.php';
        $options = array(
            'type' => (string) $this->_owApp->selectedClass,
            'member_predicate' => EF_RDF_TYPE,
            'withChilds' => true,
            'limit' => 0,
            'offset' => 0,
            'shownProperties' => array(),
            'shownInverseProperties' => array(),
            'filter' => is_array($this->_session->filter) ?
                $this->_session->filter : array(),
        );

        $instances = new OntoWiki_Model_Instances($store, $model, $options);
        $query = clone $instances->getResourceQuery();
        $query
        ->setDistinct(false)
        ->setLimit(0)
        ->setOffset(0)
        ->removeAllOptionals()->removeAllProjectionVars();

        $resVar = new Erfurt_Sparql_Query2_Var('res');
        $tagResVar = new Erfurt_Sparql_Query2_Var('tr');
        $labelVar = new Erfurt_Sparql_Query2_Var('label');

        $query->addTriple($resVar,
            new Erfurt_Sparql_Query2_IriRef($conf->tagproperty),
            $tagResVar);
        $query->addTriple($tagResVar,
            new Erfurt_Sparql_Query2_IriRef($oP),
            $labelVar);
        $query->addFilter(new Erfurt_Sparql_Query2_isLiteral($labelVar));

        $query->addProjectionVar($resVar);
        $query->addProjectionVar($tagResVar);
        $query->addProjectionVar($labelVar);
        # echo htmlentities($query);die;

        $getRes = $model->sparqlQuery($query);
        $resources=array();
        foreach ($getRes as $row) {
            $newTr = $this->generateURI();
            $resources[$row['tr']] = array('newTr' => $newTr,
                'label' => $row['label']);
        }
        # var_dump($resources);

        // preparing versioning
        $versioning                 = $this->_erfurt->getVersioning();
        $actionSpec                 = array();
        $actionSpec['modeluri']     = (string) $this->model;
        $actionSpec['resourceuri']  = $resources;
        
        foreach ($resources as $key => $value) {
            
            // add statements
            $actionSpec['type']         = 137; // object relation created
            $versioning->startAction($actionSpec);            
            $this->model->addStatement($key,
            $nP,
            array('value' => $value['newTr'], 'type' => 'uri'));
            
            $this->model->addStatement($value['newTr'],
            $nNP,
            array('value' => $value['label'], 'type' => 'literal'));
            $versioning->endAction($actionSpec);

            $actionSpec['type']         = 138; // class relation created
            $versioning->startAction($actionSpec); 
            $this->model->addStatement($value['newTr'],
            $conf->resOf,
            array('value' => $nC, 'type' => 'uri'));
            $versioning->endAction($actionSpec);

            $actionSpec['type']         = 139; // old literal relation deleted
            $versioning->startAction($actionSpec);             
            $this->model->deleteMatchingStatements(null,
            $oP,
            array('value' => $value['label'], 'type' => 'literal'));
            $versioning->endAction($actionSpec);
        }
        
        echo "Successful";
    }

    /**
     * Autocomplete
     * @return unknown_type
     */
    public function autocompleteAction() {
        require_once 'Erfurt/Sparql/SimpleQuery.php';

        // Model Based Access Control
        if (!$this->ac->isModelAllowed('edit', $this->model->getModelIri()) ) {
            require_once 'Erfurt/Ac/Exception.php';
            throw new Erfurt_Ac_Exception('You are not allowed to add
            tags in this model.');
        }

        // tagging controller needs no view renderer
        $this->_helper->viewRenderer->setNoRender();
        // disable layout for Ajax requests
        // $this->_helper->layout()->disableLayout();

        $request  = $this->_request;
        $store    = $this->store;
        $model    = $this->model;
        $modelURI = $this->model->getModelIri();
        $conf     = $this->_privateConfig;

        // selected model check
        if ($modelURI == null) {
            require_once 'OntoWiki/Exception.php';
            throw new OntoWiki_Exception('Please select model!');
            exit;
        }

        // fetch term parameter
        if (isset($this->_request->q)) {
            $q = $this->_request->getParam('q');
        } else {
            require_once 'OntoWiki/Exception.php';
            throw new OntoWiki_Exception('Missing parameter q (search term)!');
            exit;
        }

        $query = 'SELECT DISTINCT ?tr ?literal WHERE {
            ?tr <'.$conf->tagname.'> ?literal
            FILTER (regex(?literal, "^'.$q.'", "i")) . 
        }'; 
        $temp = $model->sparqlQuery($query);

        $tags = array();

        foreach ($temp as $key => $value) {
            $row = $value['literal'].'|'.$value['tr'];
            $tags[] = $row;
        }
        
        // var_dump($tags);die;
        echo json_encode(implode(PHP_EOL, $tags));
        
        exit;
    }

    /**
     * Calculate tag weight
     *
     * @param $_maxValue - max count for a tag
     * @param $_minValue - min count for a tag
     * @param $minFontSize - tagweightsMin from component.ini
     * @param $maxFontSize - tagweightsMax from component.ini
     * @param $freq - occurrences of a tag in the selected class
     * @return float (rounded) $weight the weight to generate set
     * the CSS class.
     */
    private function calcWeight($_maxValue, $_maxValue, $minFontSize,
    $maxFontSize, $freq) {
        // to avoid division by zero
        $weight = 0;
        if ((log10($_maxValue) + $minFontSize)==0) {
            $weight = round((
            ($maxFontSize - $minFontSize) * (log10($freq))) /
            (log10($_maxValue) + $minFontSize+1));
        } else if ((log10($_maxValue) + $minFontSize) > 0) {
            $weight = round((($maxFontSize - $minFontSize) * (log10($freq))) /
            (log10($_maxValue) + $minFontSize));
        }

        return $weight;
    }

    /**
     * Output error when tagcloud creation failed,
     * because there isn't existing tags
     */
    private function tagCloudFailed() {
        $this->newmessage = $this->translate->_('No tags to generate tagcloud.
            Click on resources to add!');
        $this->messagetype = 'info';

        if (!empty($this->newmessage)) {
            $this->view->message = $this->newmessage;
            if (!empty($this->messagetype)) {
                $this->view->messagetype = $this->messagetype;
            } else {
                $this->view->messagetype = 'info';
            }
        }

        // render template
        $exploreTagsTemplate = 'tagging/exploretags.phtml';
        $this->_response->setBody($this->view->render($exploreTagsTemplate));
    }

    /**
     * Compare function to sort tags by uri
     *
     * @param $a
     * @param $b
     * @return unknown_type
     */
    private function compareByUri($toBeUnique) {
        $seen=array();

        foreach ($toBeUnique as $key=>$val) {
            if (isset($seen[$val['uri']])) {
                // remove dupe
                unset($toBeUnique[$key]);
            } else {
                // remember this
                $seen[$val['uri']]=$key;
            }
        }

        unset($seen); //don't need this any more

        return $toBeUnique;
    }

    /**
     * Compare function to sort tags by name
     *
     * @param $a
     * @param $b
     * @return unknown_type
     */
    private function compare_label($a, $b)
    {
        return strnatcmp($a['tag'], $b['tag']);
    }

    /**
     * Compare function to sort by selected tags
     *
     * @param $a
     * @param $b
     * @return unknown_type
     */
    private function compare_selected($a, $b)
    {
        return strnatcmp($a['selected'], $b['selected']);
    }

    /**
     * Compare function to sort tags by weight
     *
     * @param $a
     * @param $b
     * @return unknown_type
     */
    private function compare_weight($a, $b)
    {
        return strnatcmp($b['weight'], $a['weight']);
    }

    /**
     *
     * Compare function to sort tags by weight
     *
     * @param $a
     * @param $b
     * @return unknown_type
     */
    private function compare_title($a, $b)
    {
        return strnatcmp($b['title'], $a['title']);
    }

    /**
     * Generate URI
     * @return string $uri generated URI
     */
    private function generateURI() {
        // get model URI
        $modelURI    = $this->model->getModelIri();
        // get username
        $user = $this->_owApp->getUser()->getUsername();

        /**
         * generate new unique tagresource using
         * model and hash from (user+date)
         */
        $uri = $modelURI.md5($user." ".date("F j, Y, g:i:s:u a"));

        return $uri;
    }

    /**
     * Generate TagResource for tag, which will be added
     *
     * @return string $tagResource the new generated tagresource URI
     */
    private function generateTagResource() {
        // get model URI
        $modelURI    = $this->model->getModelIri();
        // get username
        $user = $this->_owApp->getUser()->getUsername();

        /**
         * generate new unique tagresource using
         * model, prefix "tags" and hash from (user+date)
         */
        $tagResource = $modelURI."tags/".md5($user." ".
        date("F j, Y, g:i:s:u a"));

        return $tagResource;
    }

    /**
     * Get tagresource for given tag
     *
     * @param string $tag the tag label (literal)
     * @return the tagresource URI
     */
    private function getTagResource($tag) {
        $tagresource = array();
        $query = 'SELECT ?tagresource WHERE {
            ?resource <'.$this->_privateConfig->tagproperty.'> ?tagresource . 
            ?tagresource <'.$this->_privateConfig->tagname.'> "'.$tag.'"}
            LIMIT 1';
        $tempuri = $this->model->sparqlQuery($query);
        #var_dump($tempuri);
        foreach ($tempuri as $tagres) {
            $tagresource[] = $tagres['tagresource'];
        }
        # var_dump($tagresource);
        return $tagresource;
    }

    /**
     * Get tag for given tagresource
     *
     * @param string $tagresource the tagresource URI
     * @return the tag for specific tagresource
     */
    private function getTagForTagresource($tagresource) {
        $tag = '';
        $query = 'SELECT ?tag WHERE {
            <'.$tagresource.'> <'.$this->_privateConfig->tagname.'> ?tag}';
        $tempuri = $this->model->sparqlQuery($query);
        #var_dump($tempuri);
        foreach ($tempuri as $tagres) {
            $tag = $tagres['tag'];
        }
        # var_dump($tag);
        return $tag;
    }

    /**
     * Check which parameters are set in URL.
     * Save parameters in session.
     */
    private function get_Parameters() {
        if (isset($this->_request->count)) {
            $count = $this->_request->getParam('count');
            $this->_session->count = $count;
        }
        if (isset($this->_request->currentClass)) {
            $currentClass = $this->_request->getParam('currentClass');
            $this->_session->currentClass = $currentClass;
        }
        if (isset($this->_request->selectedTags)) {
            $selectedTags = $this->_request->getParam('selectedTags');
            $this->_session->selectedTags = $selectedTags;
        }
        if (isset($this->_request->sort)) {
            $sort = $this->_request->getParam('sort');
            $this->_session->sort = $sort;
        }
        if (isset($this->_request->types)) {
            $types = $this->_request->getParam('types');
            $this->_session->types = array($types);
        }
    }

    /**
     * Check which parameters are in the session.
     * Get all missing parameters from component.ini
     */
    private function set_Parameters() {
        $selectedTags = '';
        if (isset($this->_session->count)) {
            $count = $this->_session->count;
        } else {
            $count = $this->_privateConfig->defaults->count;
        }
        if (isset($this->_session->currentClass)) {
            $currentClass = $this->_session->currentClass;
        } else {
            $currentClass = $this->_owApp->selectedClass;
        }
        if (isset($this->_session->selectedTags) &&
        ($this->_session->selectedTags != '' ||
        $this->_session->selectedTags != null)) {
            /** {"0":"http://dbpedia.org/resource/Leipzig",
             "1":"http://dbpedia.org/resource/Berlin"}*/
            $tempselection = json_decode($this->_session->selectedTags);
            foreach ($tempselection as $key => $value) {
                $selectedTags[$key] = $value;
            }
        } else {
            $selectedTags = '';
        }
        if (isset($this->_session->sort)) {
            $sort = $this->_session->sort;
        } else {
            $sort = $this->_privateConfig->defaults->sort;
        }
        if (isset($this->_session->types)) {
            $types = $this->_session->types;
        } else {
            $types = $this->_privateConfig->defaults->types;
        }

        // save all parameters as array in the session
        $this->_session->tagging = (array('count' => $count,
            'currentClass' => $currentClass,
            'selectedTags' => $selectedTags,
            'sort' => $sort,
            'types' => $types));
        return $this->_session->tagging;
    }

    /**
     * Delete the statement resource-taggedWithTag-tagresource
     *
     * @param string $resource the resource URI
     * @param string $tagresource the tagresource URI
     * @return unknown_type
     */
    private function remTwT($resource,$tagresource) {
        // preparing versioning
        $versioning                 = $this->_erfurt->getVersioning();
        $actionSpec                 = array();
        $actionSpec['modeluri']     = (string) $this->model;
        $actionSpec['resourceuri']  = $this->resource;

        $actionSpec['type']         = 134; // resource untagged
        $versioning->startAction($actionSpec);
        $this->model->deleteStatement($resource,
        $this->_privateConfig->tagproperty,
        array('value' => $tagresource, 'type' => 'uri'));
        $versioning->endAction($actionSpec);

        $this->messagetype = 'info';
        $this->newmessage = 'Resource untagged';
    }

    /**
     * Delete tag completly
     * This function delete two statements:
     * 1. resource-taggedWithTag-tagresource)
     * 2. tagresource-name-tag
     *
     * @param string $resource the resource URI
     * @param string $tagresource the tagresource URI
     * @param string $tag the tag label
     * @return unknown_type
     */
    private function remTagCompletly($resource,$tagresource,$tag) {
        // preparing versioning
        $versioning                 = $this->_erfurt->getVersioning();
        $actionSpec                 = array();
        $actionSpec['modeluri']     = (string) $this->model;
        $actionSpec['resourceuri']  = $this->resource;

        $actionSpec['type']         = 134; // resource untagged
        $versioning->startAction($actionSpec);
        $this->model->deleteStatement($resource,
        $this->_privateConfig->tagproperty,
        array('value' => $tagresource, 'type' => 'uri'));
        $versioning->endAction($actionSpec);

        $actionSpec['type']         = 133; // tag removed
        $versioning->startAction($actionSpec);
        $this->model->deleteStatement($tagresource,
        $this->_privateConfig->tagname,
        array('value' => $tag, 'type' => 'literal'));
        $this->model->deleteStatement($tagresource,
        $this->_privateConfig->resOf,
        array('value' => $this->_privateConfig->tagclass,
                  'type' => 'uri'));
        $versioning->endAction($actionSpec);
        $this->messagetype = 'info';
        $this->newmessage = 'Tag removed';
    }

    /**
     * Check if the tagname of the tagresource was changed,
     * that means, the tagname is used as subject of another
     * statement.
     *
     * @param string $tagresource the tagresource URI
     * @return int: 0, if tagname was not changed
     */
    private function checkTagnameChanged($tagresource) {
        // check if tagresource->tagname->tag was changed
        $tagQuery = 'SELECT ?o WHERE {
            <'.$tagresource.'> <'.$this->_privateConfig->tagname.'> ?tag .
            ?tag ?p ?o .
        }';
        $tagChanged = $this->model->sparqlQuery($tagQuery);
        $countTagChanged = count($tagChanged);

        return $countTagChanged;
    }

    /**
     * check if the type of the tagresource was changed,
     * that means, the type is used as subject of another
     * statement.
     *
     * @param string $tagresource the tagresource URI
     * @return 0, if tagresource type was not changed
     */
    private function checkTagresrourceTypeChanged($tagresource) {
        // check if tagresource->type->Tag was changed
        $trTypeQuery = 'SELECT ?o WHERE {
            <'.$tagresource.'> <'.$this->_privateConfig->tagclass.'> ?trtype .
            ?trtype ?p ?o .
        }';
        $trTypeChanged = $this->model->sparqlQuery($trTypeQuery);
        $countTrTypeChanged = count($trTypeChanged);

        return $countTrTypeChanged;
    }

    /**
     * Add new tag by given resource, tagresource and tag
     * @param string $resources the resources URI
     * @param string $tagresource the tagresource URI
     * @param string $tag the tag label
     * @return unknown_type
     */
    private function taggingResTagresTag($resources,$tagresource,$tag) {
        $conf     = $this->_privateConfig;
        $store    = $this->store;
        $modelURI = $this->model->getModelIri();
        // preparing versioning
        $versioning                 = $this->_erfurt->getVersioning();
        $actionSpec                 = array();
        $actionSpec['modeluri']     = (string) $this->model;
        $actionSpec['resourceuri']  = $resources;

        $where = 'WHERE {
                    <'.$resources.'> <'.$conf->tagproperty.'> ?tagresource .
                    <'.$tagresource.'> <'.$conf->tagname.'> ?tag.
                    ?tagresource <'.$conf->tagname.'> "'.$tag.'"
        }';

        // The number of founded triples
        $count = $store->countWhereMatches($modelURI, $where, "?tagresource");
        if ($modelURI != null) {
            if ($count == 0) {
                // Add statements
                $actionSpec['type']         = 132; // resource getaggt
                $versioning->startAction($actionSpec);
                $this->model->addStatement($resources,
                $conf->tagproperty,
                array ('value' => $tagresource, 'type' => 'uri'));
                $versioning->endAction($actionSpec);
                 
                $actionSpec['type']         = 131; // tag created
                $versioning->startAction($actionSpec);
                $this->model->addStatement($tagresource,
                $conf->tagname,
                array('value' => $tag, 'type' => 'literal'));
                $this->model->addStatement($tagresource,
                $conf->resOf,
                array('value' => $conf->tagclass, 'type' => 'uri'));
                $versioning->endAction($actionSpec);

                $this->messagetype = 'success';
                $this->newmessage = 'The tag was added.';

            } else {
                $this->messagetype = 'info';
                $this->newmessage = 'The tag already exist.';
            }
        } else {
            require_once 'OntoWiki/Exception.php';
            throw new OntoWiki_Exception('Select knoledge base!');
        }
    }

    /**
     * Add tag by given resource and tag
     * @param string $resources the resources URI
     * @param string $tag the tag label
     * @return unknown_type
     */
    private function taggingResTag($resources,$tag) {
        $conf     = $this->_privateConfig;
        $store    = $this->store;
        $modelURI = $this->model->getModelIri();

        // preparing versioning
        $versioning                 = $this->_erfurt->getVersioning();
        $actionSpec                 = array();
        $actionSpec['modeluri']     = (string) $this->model;
        $actionSpec['resourceuri']  = $resources;

        $generatedResource = $this->generateTagResource();
        $actionSpec['type']         = 132; // resource getaggt
        $versioning->startAction($actionSpec);
        $this->model->addStatement($resources,
        $conf->tagproperty,
        array('value' => $generatedResource, 'type' => 'uri'));
        $versioning->endAction($actionSpec);
         
        $actionSpec['type']         = 131; // tag created
        $versioning->startAction($actionSpec);
        $this->model->addStatement($generatedResource,
        $conf->resOf,
        array('value' => $conf->tagclass, 'type' => 'uri'));
        $this->model->addStatement($generatedResource,
        $conf->tagname,
        array('value' => $tag, 'type' => 'literal'));
        $versioning->endAction($actionSpec);
         
        $this->messagetype = 'success';
        $this->newmessage = 'The tag was added.';
    }

    /**
     * Add tag by given resource and tagresource
     * @param string $resources the resources URI
     * @param string $tagresource the tagresource URI
     * @return unknown_type
     */
    private function taggingResTagres($resources,$tagresource) {
        $conf     = $this->_privateConfig;
        $store    = $this->store;
        $modelURI = $this->model->getModelIri();

        // preparing versioning
        $versioning                 = $this->_erfurt->getVersioning();
        $actionSpec                 = array();
        $actionSpec['modeluri']     = (string) $this->model;
        $actionSpec['resourceuri']  = $resources;

        $where = "WHERE {
                    ?s ?p ?o . 
                    FILTER (
                     sameTerm(?s, <".$resources.">) &&
                     sameTerm(?p, <".$conf->tagproperty.">) &&
                     sameTerm(?o, <".$tagresource.">)
                    )
                }";

        $count = $store->countWhereMatches($modelURI, $where, "?s ?p ?o");
        if ($count == 0) {
            $actionSpec['type']         = 132; // ressource getaggt
            $versioning->startAction($actionSpec);
            $this->model->addStatement($resources,
            $conf->tagproperty,
            array ('value' => $tagresource, 'type' => 'uri'));
            $versioning->endAction($actionSpec);

            $this->messagetype = 'success';
            $this->newmessage = 'The tag was added.';
        } else {
            $this->messagetype = 'info';
            $this->newmessage = 'Tag already existing.';
        }
    }

}
