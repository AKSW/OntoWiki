<?php
/**
 * An activity object Bookmark
 *
 * Bookmark - pointer to some URL -- typically a web page
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_dssn
 * @seeAlso    http://xmlns.notu.be/aair/#term_Bookmark
 * @copyright  Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class DSSN_Activity_Object_Bookmark extends DSSN_Activity_Object
{
    private $thumbnail  = '';
    private $label      = '';

    public function getTurtleTemplate()
    {
        /* default template only a rdf:type statement */
        $template  = <<<EndOfTemplate
            ?resource rdf:type ?type ;
                aair:thumbnail ?thumbail;
                rdfs:label ?label.
EndOfTemplate;
        return $template;
    }
    public function getTurtleTemplateVars()
    {
        $vars              = array();
        $vars['resource']  = $this->getIri();
        $vars['type']      = $this->getType();
        $vars['label']     = $this->getLabel();
        $vars['thumbnail'] = $this->getThumbnail();
        return $vars;
    }

    function getTypeLabel()
    {
        return 'bookmark';
    }


    /**
     * Get thumbnail.
     *
     * @return thumbnail.
     */
    function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * Set thumbnail.
     *
     * @param thumbnail the value to set.
     */
    function setThumbnail($thumbnail)
    {
        $this->thumbnail = $thumbnail;
    }

    /**
     * Get label.
     *
     * @return label.
     */
    function getLabel()
    {
        return $this->label;
    }

    /**
     * Set label.
     *
     * @param label the value to set.
     */
    function setLabel($label)
    {
        $this->label = $label;
    }

    function getFeedType()
    {
        return 'bookmark';
    }
}
