<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki Literal view helper
 *
 * returns the content of a specific property of a given resource as an RDFa 
 * annotated tag with (optional) given css classes and other parameters
 * this helper is usable as {{literal ...}} markup in combination with
 * ExecuteHelperMarkup
 *
 * @category OntoWiki
 * @package  OntoWiki_extensions_components_site
 */
class Site_View_Helper_Literal extends Zend_View_Helper_Abstract
{
    /*
     * current view, injected with setView from Zend
     */
    public $view;

    public $contentProperties = array(
        'http://ns.ontowiki.net/SysOnt/Site/content',
        'http://purl.org/rss/1.0/modules/content/encoded',
        'http://rdfs.org/sioc/ns#content',
        'http://purl.org/dc/terms/description',
        'http://www.w3.org/2000/01/rdf-schema#comment',
    );

    /*
     * the main tah method, mentioned parameters are:
     * - uri - which resource the literal is from (empty means selected * Resource)
     * - property - qname/uri of property to use
     * - class (css class)
     * - tag (the used tag, e.g. span)
     * - prefix - string at the beginning
     * - suffix - string at the end
     * - iprefix - string between tag and content at the beginning
     * - isuffix - string betwee content and tag at the end
     */
    public function literal($options = array())
    {
        $store       = OntoWiki::getInstance()->erfurt->getStore();
        $model       = OntoWiki::getInstance()->selectedModel;
        $titleHelper = new OntoWiki_Model_TitleHelper($model);

        // check for options and assign local vars or default values
        $class   = (isset($options['class']))   ? $options['class']   : '';
        $tag     = (isset($options['tag']))     ? $options['tag']     : 'span';
        $prefix  = (isset($options['prefix']))  ? $options['prefix']  : '';
        $suffix  = (isset($options['suffix']))  ? $options['suffix']  : '';
        $iprefix = (isset($options['iprefix'])) ? $options['iprefix'] : '';
        $isuffix = (isset($options['isuffix'])) ? $options['isuffix'] : '';

        // choose, which uri to use: option over helper default over view value
        $uri = (isset($this->resourceUri))           ? $this->resourceUri : null;
        $uri = (isset($options['selectedResource'])) ? (string) $options['selectedResource'] : $uri;
        $uri = (isset($options['uri']))              ? (string) $options['uri'] : $uri;
        $uri = Erfurt_Uri::getFromQnameOrUri($uri, $model);

        // choose, which properties to use (todo: allow multple properties)
        $contentProperties = (isset($options['property'])) ? array( $options['property']) : null;
        $contentProperties = (!$contentProperties) ? $this->contentProperties : $contentProperties;

        foreach ($contentProperties as $key => $value) {
            try {
                $validatedValue = Erfurt_Uri::getFromQnameOrUri($value, $model);
                $contentProperties[$key] = $validatedValue;
            } catch (Exception $e) {
                unset($contentProperties[$key]);
            }
        }

        // create description from resource URI
        $resource     = new OntoWiki_Resource($uri, $model);
        $description  = $resource->getDescription();
        $description  = $description[$uri];

        // select the main property from existing ones
        $mainProperty = null; // the URI of the main content property
        foreach ($contentProperties as $contentProperty) {
            if (isset($description[$contentProperty])) {
                $mainProperty = $contentProperty;
                break;
            }
        }

        // filter and render the (first) literal value of the main property
        // TODO: striptags and tidying as extension
        if ($mainProperty) {
            $firstLiteral = $description[$mainProperty][0];
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
        } else {
            return '';
        }

    }

    /*
     * view setter (dev zone article: http://devzone.zend.com/article/3412)
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
        $this->resourceUri  = (string) $view->resourceUri;
    }

}
