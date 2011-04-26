<?php
/**
 * An activity object note
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_dssn
 * @copyright  Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class DSSN_Activity_Object_Note extends DSSN_Activity_Object
{
    private $content = null;

    function __construct()
    {
        // code...
    }
 
    /**
     * Get content.
     *
     * @return content.
     */
    function getContent()
    {
        return $this->content;
    }

    /**
     * Set content.
     *
     * @param content the value to set.
     */
    function setContent($content)
    {
        $this->content = $content;
    }
}
