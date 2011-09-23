<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki Img tag view helper
 *
 * returns a img tag to a specific resource
 * this helper is usable as {{img ...}} markup in combination with
 * ExecuteHelperMarkup
 *
 * @category OntoWiki
 * @package  OntoWiki_extensions_components_site
 */
class Site_View_Helper_Img extends Zend_View_Helper_Abstract
{
    /*
     * current view, injected with setView from Zend
     */
    public $view;

    /*
     * the list of known image properties
     */
    private $properties = array(
        'http://xmlns.com/foaf/0.1/depiction',
        'http://xmlns.com/foaf/0.1/logo',
        'http://xmlns.com/foaf/0.1/img',
        'http://purl.org/ontology/mo/image',
        'http://open.vocab.org/terms/screenshot'
    );

    /*
     * the main img method, mentioned parameters are:
     * - uri      - which resource the iamge is from (empty means selected Resource)
     * - property - the property which represents the image
     * - src      - the URL of the image to load
     * - class    - the value of the class attribute
     * - alt      - the value of the alt attribute
     * - filter   - the filter string for the IPC (if enabled)
     * - prefix   - string at the beginning
     * - suffix   - string at the end
     * - nordfa   - if set to anything except null, rdfa is not rendered
     */
    public function img($options = array())
    {
        $owapp       = OntoWiki::getInstance();
        $store       = $owapp->erfurt->getStore();
        $model       = $owapp->selectedModel;
        $extManager  = $owapp->extensionManager;

        // check for options and assign local vars or null
        $src      = (isset($options['src']))    ? (string) $options['src']         : null;
        $class    = (isset($options['class']))  ? ' class="'.$options['class'].'"' : '';
        $alt      = (isset($options['alt']))    ? ' alt="'.$options['alt'].'"'     : '';
        $filter   = (isset($options['filter'])) ? $options['filter']               : null;
        $prefix   = (isset($options['prefix'])) ? $options['prefix']               : '';
        $suffix   = (isset($options['suffix'])) ? $options['suffix']               : '';
        $nordfa   = (isset($options['nordfa'])) ? true                             : false;

        // choose, which uri to use: option over helper default over view value
        $uri = (isset($this->resourceUri))           ? $this->resourceUri : null;
        $uri = (isset($options['selectedResource'])) ? (string) $options['selectedResource'] : $uri;
        $uri = (isset($options['uri']))              ? (string) $options['uri'] : $uri;
        // in case qname is given, transform to full URI
        $uri = Erfurt_Uri::getFromQnameOrUri($uri, $model);

        // look for a valid image url somewhere
        if ($src == null) {
            // choose, which properties to use for lookup (todo: allow multple properties)
            $properties = (isset($options['property'])) ? array( $options['property']) : null;
            $properties = (!$properties) ? $this->properties : $properties;

            // validate each given property
            foreach ($properties as $key => $value) {
                try {
                    $validatedValue = Erfurt_Uri::getFromQnameOrUri($value, $model);
                    $properties[$key] = $validatedValue;
                } catch (Exception $e) {
                    // unset invalid properties
                    unset($properties[$key]);
                }
            }

            // create description from resource URI
            $resource     = new OntoWiki_Resource($uri, $model);
            $description  = $resource->getDescription();
            $description  = $description[$uri];

            // select the used property
            $imgProperty = null;
            foreach ($properties as $property) {
                if (isset($description[$property])) {
                    $imgProperty = $property;
                    break;
                }
            }

            if ($imgProperty != null) {
                $imgSrc = $description[$imgProperty][0]['value'];
            } else {
                // we do not have an image src
                return '';
            }
        } else {
            $imgSrc = $src;
        }

        // modify the image imgSrc for the IPC extension
        // @todo: use an event here
        if ($filter && $extManager->isExtensionRegistered('ipc')) {
            $ipcSrc = $owapp->config->urlBase . '/ipc/get';
            $ipcSrc = $ipcSrc . '?img='. urlencode($imgSrc) .'&filter='. urlencode($filter);
            $imgSrc = $ipcSrc;
        }

        // build the image tag for output
        $return  = $prefix;
        $return .= '<img'. $class . $alt;
        if ($nordfa == false) {
            $return .= (!$src) ? ' about="'. $this->view->curie($uri) .'"' : '';
            $return .= (!$src) ? ' property="'. $this->view->curie($imgProperty) .'"' : '';
            // this property is needed since ipc maybe rewrites the src
            $return .= (!$src) ? ' resource="'. $imgSrc .'"' : '';
        }
        $return .= ' src="'. $imgSrc .'" />';
        $return .= $suffix;
        return $return;
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
