<?php
/**
 * An activity actor (group, person, ...)
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_dssn
 * @copyright  Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class DSSN_Activity_Actor extends DSSN_Resource
{
    private $name  = null;
    private $email = null;

    /**
     * Get email.
     *
     * @return email.
     */
    function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email.
     *
     * @param email the value to set.
     */
    function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get name.
     *
     * @return name.
     */
    function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param name the value to set.
     */
    function setName($name)
    {
        $this->name = $name;
    }
}
