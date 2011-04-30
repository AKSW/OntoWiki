<?php
/**
 * An activity object
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_dssn
 * @copyright  Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class DSSN_Activity_Object extends DSSN_Resource
{
    private $name        = null;
    private $description = null;

    function __construct()
    {
        // code...
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

    /**
     * Get description.
     *
     * @return description.
     */
    function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description.
     *
     * @param description the value to set.
     */
    function setDescription($description)
    {
        $this->description = $description;
    }

}
