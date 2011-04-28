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
                    $activity = $this->newStatus($request);
                    //$activity = $this->newExample();
                    break;
                default:
                    throw new Exception('request error: unknown activity type');
                    break;
            }
        }
        return $activity;
    }

    public function newStatus($request)
    {
        $content = $request->getParam('share-status');
        if ($content == '') {
            throw new Exception('request error: no content given');
        } else {
            $activity = new DSSN_Activity;
            $activity->setVerb(new DSSN_Activity_Verb_Post);

            $object = new DSSN_Activity_Object_Note;
            $object->setContent($content);
            $activity->setObject($object);

            $actorIri = $this->ontowiki->user->getUri();
            $actor = new DSSN_Activity_Actor_User($actorIri);
            $activity->setActor($actor);
            return $activity;
        }
    }

    public function newExample()
    {
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
