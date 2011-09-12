<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki renderx view helper
 *
 * selects a template (e.g. based on the site:(class)template properties)
 * and render this template via partial
 *
 * @note: name is renderx since render already exists
 * @todo: use subClass hierarchy (mosts specific template + more generic template)
 *
 * @category OntoWiki
 * @package  OntoWiki_extensions_components_site
 */
class Site_View_Helper_Renderx extends Zend_View_Helper_Abstract
{
    /*
     * current view, injected with setView from Zend
     */
    public $view;

    /*
     * used erfurt model, taken from the view object
     */
    private $model;

    /*
     * the default template (will be overwritten)
     */
    private $template = '/types/default.phtml';

    /*
     * used templateData, taken from the view object
     * and overwritten if there is a new resource is given
     */
    public $templateData = array();

    /*
     * URI of the to rendered resource
     */
    private $resourceUri;

    /*
     * an array of mappings (key = class URI, value = template name)
     */
    private $mappings = null;

    /*
     * used schema URIs
     */
    protected $templatePropClass    = 'http://ns.ontowiki.net/SysOnt/Site/classTemplate';
    protected $templatePropResource = 'http://ns.ontowiki.net/SysOnt/Site/template';
    protected $typeProp             = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type';

    /*
     * the main method, mentioned parameters are:
     * - template
     */
    public function renderx($options = array())
    {
        $this->template =
            (isset($options['template'])) ? $options['template'] : $this->selectTemplate();

        $this->prepareTemplateData();

        // try to do a partial or output error details
        try {
            $return = $this->view->partial($this->template, $this->templateData);
        } catch (Exception $e) {
            $summary = 'Error while trying to render "'.$this->resource->getTitle().'"';
            $return  = '<details><summary>'.$summary.'</summary>' . PHP_EOL;
            $return .= $e->getMessage() . PHP_EOL;
            $return .= '</details>' . PHP_EOL;
        }
        return $return;
    }

    /*
     * selects a template based on query results or keeps the default template
     */
    private function selectTemplate()
    {
        $mappings = $this->getMappings();
        $description = $this->getDescription();

        // try to map each rdf:type property value
        if (isset($description[$this->typeProp])) {
            foreach ($description[$this->typeProp] as $class) {
                $classUri = $class['value'];
                if (isset($mappings[$classUri])) {
                    // overwrite, if class has an template entry
                    $this->template = $this->view->siteId .'/types/'. $mappings[$classUri] .'.phtml';
                }
            }
        }
        return $this->template;
    }

    /*
     * prepares / overwrites the template data
     */
    private function prepareTemplateData()
    {
        $this->templateData['title']       = $this->resource->getTitle();
        $this->templateData['resourceUri'] = $this->resourceUri;
        $this->templateData['description'] = $this->getDescription();
    }

    /*
     * returns or fetches and returns the mapping array
     */
    public function getMappings()
    {
        if ($this->mappings == null) {
            // prepare the sparql query
            // this query should be very cacheable ...
            $query = '
                PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
                SELECT DISTINCT ?class ?template
                WHERE {
                    ?class <'. $this->templatePropClass .'> ?template .
                    }';

            // fetch results
            $store = OntoWiki::getInstance()->erfurt->getStore();
            $result = $store->sparqlQuery($query);

            // fill the mappings array
            $this->mappings = array();
            foreach ($result as $mapping) {
                $uri      = $mapping['class'];
                $template = $mapping['template'];
                $this->mappings[$uri] = $template;
            }
        }

        return $this->mappings;
    }

    /*
     * generates and return the description of $this->resourceUri
     */
    private function getDescription()
    {
        $this->resource     = new OntoWiki_Resource($this->resourceUri, $this->model);
        $this->description  = $this->resource->getDescription();
        $this->description  = $this->description[$this->resourceUri];
        return $this->description;
    }

    /*
     * view setter (dev zone article: http://devzone.zend.com/article/3412)
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view         = $view;
        $this->model        = $view->model;
        $this->templateData = $view->templateData;
        $this->resourceUri  = (string) $view->resourceUri;
    }

}
