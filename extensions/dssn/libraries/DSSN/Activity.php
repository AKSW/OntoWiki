<?php
/**
 * An activity
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_dssn
 * @copyright  Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class DSSN_Activity extends DSSN_Resource
{
    private $actor     = null;
    private $verb      = null;
    private $object    = null;
    private $published = null;


    public function getSubResources()
    {
        return array(
            $this->getActor(),
            $this->getVerb(),
            $this->getObject()
        );
    }

    public function getTurtleTemplate()
    {
        $now = date('c', time());
        $template  = <<<EndOfTemplate
            ?activityIri a aair:Activity ;
                atom:published "$now"^^xsd:dateTime ;
                aair:activityVerb   ?verbIri ;
                aair:activityActor  ?actorIri ;
                aair:activityObject ?objectIri .
EndOfTemplate;
        return $template;
    }

    public function getTurtleTemplateVars()
    {
        $vars                = array();
        $vars['activityIri'] = $this->getIri();
        $vars['verbIri']     = $this->getVerb()->getIri();
        $vars['actorIri']    = $this->getActor()->getIri();
        $vars['objectIri']   = $this->getObject()->getIri();
        return $vars;
    }

    /**
     * Get published.
     *
     * @return published.
     */
    public function getPublished()
    {
        /* set to current time if not set by now */
        if ($this->published == null) {
            $this->setPublished(date('c', time()));
        }
        return $this->published;
    }

    /**
     * Set published.
     *
     * @param published the value to set (as ISO 8601 dateTime string).
     */
    public function setPublished($published)
    {
        $this->published = $published;
    }

    /**
     * Get actor.
     *
     * @return actor.
     */
    public function getActor()
    {
        return $this->actor;
    }

    /**
     * Set actor.
     *
     * @note  if an IRI string is given a DSSN_Activity_Actor_User is created
     * @param actor is a DSSN_Activity_Actor object or an IRI string
     */
    public function setActor ($actor = null)
    {
        if (is_string($actor)) {
            $actor = new DSSN_Activity_Actor_User($actor);
        }
        if ($actor instanceof DSSN_Activity_Actor) {
            $this->actor = $actor;
        } else {
            throw Exception ('setActor needs an DSSN_Activity_Actor or an IRI string as parameter');
        }
    }

    /**
     * Get verb.
     *
     * @return verb.
     */
    public function getVerb()
    {
        return $this->verb;
    }

    /**
     * Set verb.
     *
     * @param verb the value to set.
     */
    public function setVerb(DSSN_Activity_Verb $verb)
    {
        $this->verb = $verb;
    }

    /**
     * Get object.
     *
     * @return object.
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Set object.
     *
     * @param object the value to set.
     */
    public function setObject(DSSN_Activity_Object $object)
    {
        $this->object = $object;
    }

}
