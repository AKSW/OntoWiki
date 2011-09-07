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

    public $site;

    public function init()
    {
        $this->store = OntoWiki::getInstance()->erfurt->getStore();
    }

    public function renderx($options = array())
    {
        $this->selectTemplate();
        $this->prepareTemplateData();
        return $this->view->partial($this->template, $this->templateData);
    }

    private function selectTemplate()
    {
        $this->template = $this->siteId .'/types/'. 'document' .'.phtml';
    }

    private function prepareTemplateData()
    {
        $this->resource = new OntoWiki_Resource($this->resourceUri, $this->model);
        $description    = $this->resource->getDescription();

        $this->templateData['title']       = $this->resource->getTitle();
        $this->templateData['resourceUri'] = $this->resourceUri;
        $this->templateData['description'] = $description[$this->resourceUri];
    }


    /*
     * view setter (dev zone article: http://devzone.zend.com/article/3412)
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view         = $view;
        $this->siteId       = $view->siteId;
        $this->model        = $view->model;
        $this->templateData = $view->templateData;
        $this->resourceUri  = (string) $view->resourceUri;
    }

}
