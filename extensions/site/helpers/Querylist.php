<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki Querylist view helper
 *
 * this helper executes a SPARQL query, renders each row with a given template
 * and outputs the resulting string
 *
 * @category OntoWiki
 * @package  OntoWiki_extensions_components_site
 */
class Site_View_Helper_Querylist extends Zend_View_Helper_Abstract
{
    /*
     * current view, injected with setView from Zend
     */
    public $view;

    public function querylist($query, $template, $templateOptions = array())
    {
        $owapp       = OntoWiki::getInstance();
        $store       = $owapp->erfurt->getStore();
        $titleHelper = new OntoWiki_Model_TitleHelper($owapp->selectedModel);

        try {
            $result = $store->sparqlQuery($query);
        } catch (Exception $e) {
            // executions failed (return nothing)
            return $e->getMessage();
        }

        // pre-fill the title helper
        foreach ($result as $row) {
            foreach ($row as $value) {
                if (Erfurt_Uri::check($value)) {
                    $titleHelper->addResource($value);
                }
            }
        }

        $return  = '';
        $count   = count($result);
        $current = 0;
        $odd     = true;
        foreach ($result as $row) {
            // shift status vars
            $current++;
            $odd = !$odd;

            // prepare a first / last hint for the template
            $listhint = ($current == 1) ? 'first' : null;
            $listhint = ($current == $count) ? 'last' : $listhint;
            $row['listhint'] = $listhint;

            // prepare other template vars
            $row['oddclass'] = $odd ? 'odd' : 'even';
            $row['rowcount'] = $count;
            $row['current']  = $current;
            $row['title']    = $titleHelper->getTitle($row['resourceUri']);
            $row             = array_merge($row, $templateOptions);

            // render the template
            $return         .= $this->view->partial($template, $row);
        }

        return $return;
    }

    /*
     * view setter (dev zone article: http://devzone.zend.com/article/3412)
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }
}
