<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki DisplayMainContent view helper
 *
 * outputs the main content of a given resource
 *
 * @category OntoWiki
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class Site_View_Helper_ReturnMainContent extends Zend_View_Helper_Abstract
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

    public function returnMainContent($desc = null) {

        if (!$desc) {
            echo '';
            return;
        }

        // used resources
        // TODO: move to config
        $contentProperties = array();
        $contentProperties[] = 'http://aksw.org/schema/content';
        $contentProperties[] = 'http://lod2.eu/schema/content';
        $contentProperties[] = 'http://rdfs.org/sioc/ns#content';
        $contentProperties[] = 'http://purl.org/dc/terms/description';
        $contentProperties[] = 'http://aksw.org/schema/abstract';

        // select the main property from existing ones
        $mainProperty = null; // the URI of the main content property
        foreach ($contentProperties as $contentProperty) {
            if (isset($desc[$contentProperty])) {
                $mainProperty = $contentProperty;
                break;
            }
        }

        // filter and render the (first) literal value of the main property
        if ($mainProperty) {
            $firstLiteral = $desc[$mainProperty][0];
            $literalValue = $firstLiteral['value'];

            if (isset($firstLiteral['datatype'])) {
                $datatype = $firstLiteral['datatype'];
                $content = $this->view->displayLiteralPropertyValue($literalValue, $mainProperty, $datatype);
            } else {
                $content = $this->view->displayLiteralPropertyValue($literalValue, $mainProperty);
            }
            // filter by using available extensions
            //var_dump($literalValue, $datatype, $mainProperty);

            // render as div element with RDFa annotations
            // TODO: striptags and tidying as extension
            echo '<div property="'. $this->view->curie($mainProperty) . '">';
            echo $content . '</div>' . PHP_EOL;
        }

    }
}
