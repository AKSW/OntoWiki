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
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
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

    public function literalTag($desc = null, $contentProperties = null, $options = array())
    {
        if (!$desc) {
            return '';
        }

        if (!isset($options['class'])) {
            $class = '';
        } else {
            $class = $options['class'];
        }

        if (!isset($options['tag'])) {
            $tag = 'div';
        } else {
            $tag = $options['tag'];
        }

        if (!isset($options['prefix'])) {
            $prefix = '';
        } else {
            $prefix = $options['prefix'];
        }

        if (!isset($options['suffix'])) {
            $suffix = '';
        } else {
            $suffix = $options['suffix'];
        }

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
            $tmpArray[] = $contentProperties;
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
            $literalValue = $firstLiteral['value'];

            // filter by using available extensions
            if (isset($firstLiteral['datatype'])) {
                $datatype = $firstLiteral['datatype'];
                $content = $this->view->displayLiteralPropertyValue(
                    $literalValue, $mainProperty, $datatype);
            } else {
                $content = $this->view->displayLiteralPropertyValue(
                    $literalValue, $mainProperty);
            }

            // execute the helper markup on the content
            $content = $this->view->executeHelperMarkup($content);

            $curie = $this->view->curie($mainProperty);
            return "$prefix<$tag class='$class' property='$curie'>$content</$tag>$suffix";
        }

    }
}
