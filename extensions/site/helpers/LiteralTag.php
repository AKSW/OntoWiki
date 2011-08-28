<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki LiteralTag view helper
 *
 * returns the content of a specific property of a given resource as an RDFa 
 * annotated tag with (optional) given css classes
 *
 * @category OntoWiki
 * @package  OntoWiki_extensions_components_site
 */
class Site_View_Helper_LiteralTag extends Zend_View_Helper_Abstract
{
    // current view, injected with setView from Zend
    public $view;

    /*
     * view setter (dev zone article: http://devzone.zend.com/article/3412)
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    /*
     * the main tah method, mentioned parameters are:
     * - class (css class)
     * - tag (the used tag, e.g. div)
     * - prefix, suffix
     * - iprefix, isuffix
     */
    public function literalTag($desc = null, $contentProperties = null, $options = array())
    {
        $store       = OntoWiki::getInstance()->erfurt->getStore();
        $model       = OntoWiki::getInstance()->selectedModel;
        $titleHelper = new OntoWiki_Model_TitleHelper($model);

        if (!$desc) {
            return '';
        }

        // check for options and assign local vars or null
        $class   = (isset($options['class']))   ? $options['class']   : '';
        $tag     = (isset($options['tag']))     ? $options['tag']     : 'div';
        $prefix  = (isset($options['prefix']))  ? $options['prefix']  : '';
        $suffix  = (isset($options['suffix']))  ? $options['suffix']  : '';
        $iprefix = (isset($options['iprefix'])) ? $options['iprefix'] : '';
        $isuffix = (isset($options['isuffix'])) ? $options['isuffix'] : '';

        if (!$contentProperties) {
            // used default property resources
            $contentProperties = array();
            $contentProperties[] = 'http://aksw.org/schema/content';
            $contentProperties[] = 'http://lod2.eu/schema/content';
            $contentProperties[] = 'http://rdfs.org/sioc/ns#content';
            $contentProperties[] = 'http://purl.org/dc/terms/description';
        } else if (is_string($contentProperties)) {
            // string to array
            $tmpArray = array();
            $tmpArray[] = Erfurt_Uri::getFromQnameOrUri($contentProperties, $model);
            $contentProperties = $tmpArray;
        }

        // select the main property from existing ones
        $mainProperty = null; // the URI of the main content property
        foreach ($contentProperties as $contentProperty) {
            if (isset($desc[$contentProperty])) {
                $mainProperty = $contentProperty;
                break;
            }
        }

        // filter and render the (first) literal value of the main property
        // TODO: striptags and tidying as extension
        if ($mainProperty) {
            $firstLiteral = $desc[$mainProperty][0];
            $content = $firstLiteral['value'];

            // filter by using available extensions
            if (isset($firstLiteral['datatype'])) {
                $datatype = $firstLiteral['datatype'];
                $content = $this->view->displayLiteralPropertyValue($content, $mainProperty, $datatype);
            } else {
                $content = $this->view->displayLiteralPropertyValue($content, $mainProperty);
            }

            // execute the helper markup on the content (after the extensions)
            $content = $this->view->executeHelperMarkup($content);

            $curie = $this->view->curie($mainProperty);
            return "$prefix<$tag class='$class' property='$curie'>$iprefix$content$isuffix</$tag>$suffix";
        }

    }
}
