<?php
/**
 * An activity
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_dssn
 * @copyright  Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class DSSN_Activity
{
    private $actor  = null;
    private $verb   = null;
    private $object = null;

    function __construct()
    {
        // code...
    }

    public function toRDF()
    {
        // code...
    }

    public function toAtom()
    {
        // code...
    }
 
    /**
     * Get actor.
     *
     * @return actor.
     */
    function getActor()
    {
        return $this->actor;
    }

    /**
     * Set actor.
     *
     * @param actor the value to set.
     */
    function setActor(DSSN_Activity_Actor $actor)
    {
        $this->actor = $actor;
    }

    /**
     * Get verb.
     *
     * @return verb.
     */
    function getVerb()
    {
        return $this->verb;
    }

    /**
     * Set verb.
     *
     * @param verb the value to set.
     */
    function setVerb(DSSN_Activity_Verb $verb)
    {
        $this->verb = $verb;
    }

    /**
     * Get object.
     *
     * @return object.
     */
    function getObject()
    {
        return $this->object;
    }

    /**
     * Set object.
     *
     * @param object the value to set.
     */
    function setObject(DSSN_Activity_Object $object)
    {
        $this->object = $object;
    }
}
