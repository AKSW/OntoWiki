<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki query view helper
 *
 * returns a query result list rendered by a specific template
 * this helper is usable as {{query ...}} markup in combination with
 * ExecuteHelperMarkup
 *
 * @category OntoWiki
 * @package  OntoWiki_extensions_components_site
 */
class Site_View_Helper_Query extends Zend_View_Helper_Abstract
{
    /*
     * current view, injected with setView from Zend
     */
    public $view;

    /*
     * the main query method, mentioned parameters are:
     * - where
     * - template
     * - limit
     */
    public function query($options = array())
    {
        $this->templateData = $this->view->getHelper('Renderx')->templateData;
        $store              = OntoWiki::getInstance()->erfurt->getStore();
        $model              = OntoWiki::getInstance()->selectedModel;
        $titleHelper        = new OntoWiki_Model_TitleHelper($model);

        // check for options and assign local vars or null
        $where    = (isset($options['where']))    ? $options['where']    : '?resourceUri a foaf:Project.';
        $template = (isset($options['template'])) ? $options['template'] : 'li';
        $limit    = (isset($options['limit']))    ? $options['limit']    : 100;

        // create template name {site}/items/{name}.phtml
        $siteId   = $this->templateData['siteId'];
        $template = $siteId . '/items/' . $template . '.phtml';

        // build the query including PREFIX declarations
        $query = '';
        foreach ($model->getNamespaces() as $ns => $prefix) {
            $query .= 'PREFIX ' . $prefix . ': <' . $ns . '>' . PHP_EOL;
        }
        $query .= 'SELECT DISTINCT ?resourceUri WHERE {' . PHP_EOL;
        $query .= $where . PHP_EOL;
        $query .= 'FILTER (!isBLANK(?resourceUri))' . PHP_EOL;
        $query .= '}  LIMIT ' . $limit . PHP_EOL;
        return $this->view->querylist($query, $template, $options);
    }

    /*
     * view setter (dev zone article: http://devzone.zend.com/article/3412)
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }
}
