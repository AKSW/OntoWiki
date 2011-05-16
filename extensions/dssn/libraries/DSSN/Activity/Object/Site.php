<?php
/**
 * An activity object note
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_dssn
 * @copyright  Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class DSSN_Activity_Object_Site extends DSSN_Activity_Object
{
    /*
     * the content of the note is the status message
     */
    private $content = '';

    public function getDirectImports() {
        $myImports = array (
            DSSN_AAIR_content   => 'setContent',
        );
        $parentImports = parent::getDirectImports();
        return array_merge($myImports, $parentImports);
    }
    public function getTurtleTemplate()
    {
        /* default template only a rdf:type statement */
        $template  = <<<EndOfTemplate
            ?resource rdf:type ?type ;
                rdfs:label ?content .
EndOfTemplate;
        return $template;
    }
    public function getTurtleTemplateVars()
    {
        $vars             = array();
        $vars['resource'] = $this->getIri();
        $vars['type']     = $this->getType();
        return $vars;
    }
    /**
     * Get content.
     *
     * @return content.
     */
    function getContent()
    {
        return $this->getIri();
    }

    /**
     * Set content.
     *
     * @param content the value to set.
     */
    function setContent($content)
    {
        //$this->content = $content;
    }

    function getTypeLabel()
    {
        return 'website';
    }

}
