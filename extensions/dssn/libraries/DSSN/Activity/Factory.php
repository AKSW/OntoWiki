<?php
/**
 * Factory for activities (depends on OntoWiki DSSN action addactivity)
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_dssn
 * @copyright  Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class DSSN_Activity_Factory
{
    /*
     * the ontowiki app object
     */
    private $ontowiki;

    public function __construct($ontowiki)
    {
        if ($ontowiki instanceof OntoWiki) {
            $this->ontowiki = $ontowiki;
        } else {
            throw new Exception('developer error: Factory constructor needs an ontowiki object');
        }
        //$store     = $this->ontowiki->erfurt->getStore();
        //$model     = $this->ontowiki->selectedModel;
        //$translate = $this->ontowiki->translate;
    }

    /*
     * fetch a resource from the store
     */
    public function getFromStore($iri = null, $model = null)
    {
        if ($iri == null) {
            throw new Exception('getFromStore needs an IRI string');
        }

        if ($model == null) {
            $store     = $this->ontowiki->erfurt->getStore();
            $model     = $this->ontowiki->selectedModel;
        }
        if (!$model instanceof Erfurt_Rdf_Model){
            throw new Exception('getFromStore needs a model');
        }

        // query for the activity (Q: restrict ?p here? actor can be big!)
        $query = <<<EndOfTemplate
            SELECT ?p ?o ?p2 ?o2
            WHERE {
                <$iri> ?p ?o.
                OPTIONAL {?o ?p2 ?o2}
            }
EndOfTemplate;
        $data = $model->sparqlQuery($query, array('result_format' => 'extended'));

        // fill the sparql result into an phprdf array / ARC2 index
        $index = new DSSN_Model();
        foreach ($data['results']['bindings'] as $key => $binding) {
            // the fake subject binding
            $s = array ( 'type' => 'uri', 'value'=> $iri);
            // add S P O (direct triple of the activity)
            $index->addStatementFromExtendedFormatArray(
                $s, $binding['p'], $binding['o']
            );
            // add O P2 O2 (triple of the activity objects (verb, actor, ...)
            if (isset($binding['p2'])) {
                $index->addStatementFromExtendedFormatArray(
                    $binding['o'], $binding['p2'], $binding['o2']
                );
            }
        }

        return $this->newFromModel($iri, $index);
    }

    /*
     * gets an ARC2 index / phprdf array and creates an activity from that
     */
    public function newFromModel($iri = null, DSSN_Model $model)
    {
        //var_dump($model->getStatements()); return;
        if ($iri == null) {
            throw new Exception('getFromModel needs an IRI string');
        }
        $return = new DSSN_Activity($iri);
        $return->importLiterals($model);

        // check for actor, use factory and set actor to activity
        if ($model->countSP( $iri, DSSN_AAIR_activityActor) != 1) {
            throw new Exception('need exactly ONE aair:activityActor statement');
        } else {
            $actorIri   = $model->getValue($iri, DSSN_AAIR_activityActor);
            if ($model->hasSP($actorIri, DSSN_RDF_type)) {
                $actor = DSSN_Resource::initFromType(
                    $model->getValues($actorIri, DSSN_RDF_type)
                );
                $actor->setIri($actorIri);
                $actor->fetchDirectImports($model);
                $return->setActor($actor);
            } else {
                throw new Exception('need at least one rdf:type statement');
            }
        }

        // check for object, use factory and set object to activity
        if ($model->countSP( $iri, DSSN_AAIR_activityObject) != 1) {
            throw new Exception('need exactly ONE aair:activityObject statement');
        } else {
            $objectIri   = $model->getValue($iri, DSSN_AAIR_activityObject);
            if ($model->hasSP($objectIri, DSSN_RDF_type)) {
                $object = DSSN_Resource::initFromType(
                    $model->getValue($objectIri, DSSN_RDF_type)
                );
                $object->setIri($objectIri);
                $object->fetchDirectImports($model);
                $return->setObject($object);
            } else {
                var_dump($model); exit;
                //throw new Exception('need at least one rdf:type statement for '.$objectIri);
            }
        }

        // check for verb, use factory and set verb to activity
        if ($model->countSP( $iri, DSSN_AAIR_activityVerb) != 1) {
            throw new Exception('need exactly ONE aair:activityVerb statement');
        } else {
            $verbIri   = $model->getValue($iri, DSSN_AAIR_activityVerb);
            $verb = DSSN_Resource::initFromType($verbIri);
            $verb->setIri($verbIri);
            $verb->fetchDirectImports($model);
            $return->setVerb($verb);
        }

        return $return;
    }

    /*
     * input is form request from the ShareitModule
     */
    public function newFromShareItModule($request)
    {
        $type = $request->getParam('activity-type');
        if ($type == '') {
            throw new Exception('request error: no activity type parameter');
        } else {
            switch ($type) {
                case 'status':
                    $activity = $this->newStatus(
                        (string) $request->getParam('share-status'),
                        (string) $this->ontowiki->user->getUri(),
                        (string) $this->ontowiki->user->getUsername()
                    );
                    break;
                case 'link':
                    $activity = $this->newSharedLink(
                        (string) $request->getParam('share-link-url'),
                        (string) $request->getParam('share-link-name'),
                        (string) $this->ontowiki->user->getUri(),
                        (string) $this->ontowiki->user->getUsername()
                    );
                    break;
                default:
                    throw new Exception('request error: unknown activity type '.$type.' given.');
                    break;
            }
        }
        var_dump($activity->toRDF());
        return $activity;
    }

    /*
     * creates a new shared link activity
     */
    public function newSharedLink($targetUrl = null, $targetName = null, $actorIri = null, $actorName = null)
    {
        //throw new Exception("debug: $targetUrl, $targetName, $actorIri, $actorName");
        if ($targetUrl == null) {
            throw new Exception('request error: no target url given');
        } elseif ($targetName == null) {
            throw new Exception('request error: no target name given');
        } else {
            $activity = new DSSN_Activity;
            $activity->setVerb(new DSSN_Activity_Verb_Share);

            $object = new DSSN_Activity_Object_Bookmark;
            $object->setIri($targetUrl);
            $object->setLabel($targetName);
            $object->setThumbnail('http://cligs.websnapr.com/?size=t&url='.$targetUrl);
            $activity->setObject($object);

            $actor = new DSSN_Activity_Actor_User;
            if ($actorIri == null) {
                $actorIri = (string) $this->ontowiki->user->getUri();
            }
            if ($actorName == null) {
                $actorName = (string) $this->ontowiki->user->getGetUsername();
            }
            $actor->setIri($actorIri);
            $actor->setName($actorName);
            $activity->setActor($actor);
            return $activity;
        }
    }

    /*
     * creates a new status note from the current ontowiki user
     */
    public function newStatus($content = null, $actorIri = null, $actorName = null)
    {
        if ($content == null) {
            throw new Exception('request error: no content given');
        } else {
            $activity = new DSSN_Activity;
            $activity->setVerb(new DSSN_Activity_Verb_Post);

            $object = new DSSN_Activity_Object_Note;
            $object->setContent($content);
            $activity->setObject($object);

            $actor = new DSSN_Activity_Actor_User;
            if ($actorIri == null) {
                $actorIri = (string) $this->ontowiki->user->getUri();
            }
            if ($actorName == null) {
                $actorName = (string) $this->ontowiki->user->getUsername();
            }
            $actor->setIri($actorIri);
            $actor->setName($actorName);
            $activity->setActor($actor);
            return $activity;
        }
    }

    /*
     * creates a static example
     */
    static public function newExample()
    {
        DSSN_Utils::setConstants();
        $activity = new DSSN_Activity;
        $verb  = new DSSN_Activity_Verb_Post;
        $activity->setVerb($verb);

        $actor = new DSSN_Activity_Actor_User('http://sebastian.tramp.name');
        $actor->setName('Sebastian Tramp');
        $activity->setActor($actor);

        $object = new DSSN_Activity_Object_Note;
        $object->setContent('my feelings today ...');
        $activity->setObject($object);

        //$context = new DSSN_Activity_Context_Time;
        //$context->setDate(time());
        //$activity->addContext($context);

        return $activity;
    }
}
