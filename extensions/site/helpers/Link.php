<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki Link view helper
 *
 * returns a link to a specific resource
 * this helper is usable as {{link ...}} markup in combination with 
 * ExecuteHelperMarkup
 *
 * @category OntoWiki
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class Site_View_Helper_Link extends Zend_View_Helper_Abstract
{
    /*
     * current view, injected with setView from Zend
     */
    public $view;

    /*
     * view setter (dev zone article: http://devzone.zend.com/article/3412)
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    /*
     * the main link method, mentioned parameters are:
     * - literal
     * - property
     */
    public function link($options = array())
    {
        $store       = OntoWiki::getInstance()->erfurt->getStore();
        $model       = OntoWiki::getInstance()->selectedModel;
        $titleHelper = new OntoWiki_Model_TitleHelper($model);

        if (!isset($options['literal'])) {
            throw new Exception('link helper needs parameter: literal');
        }
        $literal  = $options['literal'];

        if (isset($options['property'])) {
            $property = $options['property'];
        } else {
            $property = '?property';
        }

        $query = '';
        foreach ($model->getNamespaces() as $ns => $prefix) {
            $query .= 'PREFIX ' . $prefix . ': <' . $ns . '>' . PHP_EOL;
        }
        $query .= 'SELECT DISTINCT ?resourceUri WHERE {?resourceUri '.$property.' ?o0 
                    FILTER (!isBLANK(?resourceUri)) 
                    FILTER (REGEX(?o0, "^'.$literal.'$", "i")) 
                    }  LIMIT 1';

        $result = $store->sparqlQuery($query);
        if (!$result) {
            return $literal;
        } else {
            $uri   = $result[0]['resourceUri'];

            if (isset($options['text'])) {
                $text = $options['text'];
            } else {
                $text = $titleHelper->getTitle($uri);
            }

            return "<a href='$uri'>$text</a>";
        }
    }
}
