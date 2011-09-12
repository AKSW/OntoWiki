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
 * @package  OntoWiki_extensions_components_site
 */
class Site_View_Helper_Link extends Zend_View_Helper_Abstract
{
    /*
     * current view, injected with setView from Zend
     */
    public $view;

    /*
     * the main link method, mentioned parameters are:
     * - literal  - if no uri/qname is given a search is executed
     * - property - search can be limited to a property
     * - text     - the link text (instead of title)
     * - uri      - a uri or qname of the resource
     * - class    - css class(es) of the link
     * - prefix   - string at the beginning
     * - suffix   - string at the end
     * - iprefix  - string between tag and content at the beginning
     * - isuffix  - string betwee content and tag at the end
     * - direct   - set to something (e.g true) if you do not want OntoWiki URLs
     */
    public function link($options = array())
    {
        $store       = OntoWiki::getInstance()->erfurt->getStore();
        $model       = OntoWiki::getInstance()->selectedModel;
        $titleHelper = new OntoWiki_Model_TitleHelper($model);

        // check for options and assign local vars or null
        $uri      = (isset($options['uri']))      ? (string) $options['uri'] : null;
        $literal  = (isset($options['literal']))  ? $options['literal']      : null;
        $text     = (isset($options['text']))     ? $options['text']         : null;
        $property = (isset($options['property'])) ? $options['property']     : null;
        $class    = (isset($options['class']))    ? ' class="'.$options['class'].'"' : '';
        $prefix   = (isset($options['prefix']))   ? $options['prefix']       : '';
        $suffix   = (isset($options['suffix']))   ? $options['suffix']       : '';
        $iprefix  = (isset($options['iprefix']))  ? $options['iprefix']      : '';
        $isuffix  = (isset($options['isuffix']))  ? $options['isuffix']      : '';
        $direct   = (isset($options['direct']))   ? true                     : false;

        // resolve short forms (overwrite full name values with short forms values)
        $uri      = (isset($options['r'])) ? (string) $options['r'] : $uri;
        $literal  = (isset($options['l'])) ? $options['l']          : $literal;
        $text     = (isset($options['t'])) ? $options['t']          : $text;
        $property = (isset($options['p'])) ? $options['p']          : $property;

        // if an uri is given, we do not need to search for
        if (isset($uri)) {
            // resolve qnames and check uri input
            $uri = Erfurt_Uri::getFromQnameOrUri((string) $uri, $model);
        } else {
            // if no uri is given, we need to search by using the literal
            if (!isset($literal)) {
                throw new Exception('The link helper needs at least one parameter literal or uri');
            }

            // if a property is given, use <properyuri> instead of a variable part in the query
            $property = ($property) ? '<'.Erfurt_Uri::getFromQnameOrUri($property, $model).'>' : '?property';

            // build the query including PREFIX declarations
            $query = '';
            foreach ($model->getNamespaces() as $ns => $nsprefix) {
                $query .= 'PREFIX ' . $nsprefix . ': <' . $ns . '>' . PHP_EOL;
            }
            $query .= 'SELECT DISTINCT ?resourceUri WHERE {?resourceUri '.$property.' ?literal
                FILTER (!isBLANK(?resourceUri))
                FILTER (REGEX(?literal, "^'.$literal.'$", "i"))
                }  LIMIT 1';

            $result = $store->sparqlQuery($query);
            if (!$result) {
                // resource not found, so return plain literal or given text
                return (isset($text)) ? $text : $literal;
            } else {
                $uri   = $result[0]['resourceUri'];
            }
        }

        // generate the link URL from the resource URI
        if ($direct == true) {
            $url = $uri;
        } else {
            $url = new OntoWiki_Url(array('route' => 'properties'), array('r'));
            $url->setParam('r', $uri, true);
            $url = (string) $url;
        }

        // link text comes from title helper or option
        $text = (isset($text)) ? $text : $titleHelper->getTitle($uri);

        return "$prefix<a$class href='$url'>$iprefix$text$isuffix</a>$suffix";
    }

    /*
     * view setter (dev zone article: http://devzone.zend.com/article/3412)
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }
}
