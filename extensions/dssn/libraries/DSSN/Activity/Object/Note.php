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
    /*
     * the content of the note is the status message
     */
    private $content = '';

    public function importLiterals(DSSN_Model $model) {
        $iri = $this->getIri();
        if ($model->countSP( $iri, DSSN_AAIR_content) != 1) {
            throw new Exception('need exactly ONE aair:content statement');
        } else {
            $content = $model->getValue($iri, DSSN_AAIR_content);
            $this->setContent($content);
        }
    }
    
    public function getTurtleTemplate()
    {
        /* default template only a rdf:type statement */
        $template  = <<<EndOfTemplate
            ?resource rdf:type ?type ;
                rdfs:label ?content ;
                aair:content ?content .
EndOfTemplate;
        return $template;
    }
    public function getTurtleTemplateVars()
    {
        $vars             = array();
        $vars['resource'] = $this->getIri();
        $vars['type']     = $this->getType();
        $vars['content']  = $this->getContent();
        return $vars;
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
