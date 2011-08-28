<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki NavigationList view helper
 *
 * returns an ol/ul list of a given rdf:seq resource
 *
 * @category OntoWiki
 * @package  OntoWiki_extensions_components_site
 */
class Site_View_Helper_NavigationList extends Zend_View_Helper_Abstract
{
    /*
     * current view, injected with setView from Zend
     */
    public $view;

    /*
     * The resource URI which is used for the SPARQL query
     */
    private $navResource;

    /*
     * the used list tag (ol/ul)
     */
    private $listTag         = 'ul';

    /*
     * css class value for the list item
     */
    private $listClass       = '';

    /*
     * css class value for active li item
     */
    private $activeItemClass = 'active';

    /*
     * the currently active resource
     */
    private $activeUrl       = '';

    /*
     * a string which is prepended to the list
     */
    private $prefix          = ''; 

    /*
     * a string which is appended to the list
     */
    private $suffix          = '';

    /*
     * main call method, takes an URI and an options array.
     * possible options array key:
     * - listTag - the used html tag (ol, ul)
     * - listClass - the used css class for the list
     * - activeItemClass - the used css class for the active item
     * - activeUrl - the active item
     * - prefix - a prefix string outside of the list
     * - suffix - a suffix string outside of the list
     * - titleProperty - an additional VIP title property
     *
     */
    public function navigationList($navResource = null, $options = array())
    {
        $owapp       = OntoWiki::getInstance();
        $store       = $owapp->erfurt->getStore();
        $view        = $owapp->view;
        $titleHelper = new OntoWiki_Model_TitleHelper($owapp->selectedModel);

        if (!$navResource) {
            return '';
        } else {
            $this->navResource = $navResource;
        }

        // overwrite standard options with given ones
        if (isset($options['listTag'])) {
            $this->listTag = $options['listTag'];
        }
        if (isset($options['listClass'])) {
            $this->listClass = $options['listClass'];
        }
        if (isset($options['activeItemClass'])) {
            $this->activeItemClass = $options['activeItemClass'];
        }
        if (isset($options['activeUrl'])) {
            $this->activeUrl = $options['activeUrl'];
        }
        if (isset($options['prefix'])) {
            $this->prefix = $options['prefix'];
        }
        if (isset($options['suffix'])) {
            $this->suffix = $options['suffix'];
        }
        if (isset($options['titleProperty'])) {
            $titleHelper->prependTitleProperty($options['titleProperty']);
        }

        $query = '
            PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
            SELECT ?item ?prop
            WHERE {
               <'. $this->navResource .'> ?prop ?item.
               ?prop a rdfs:ContainerMembershipProperty.
            }
        ';

        try {
            $result = $store->sparqlQuery($query);
        } catch (Exception $e) {
            // executions failed (return error message)
            return $e->getMessage();
        }

        // array of urls and labels which represent the navigation menu
        $navigation = array();
        // round one: fill navigation array with urls as well as fill the titleHelper
        foreach ($result as $row) {
            // works only for URIs ...
            if (Erfurt_Uri::check($row['item'])) {
                // prepare variables
                $url      = $row['item'];
                $property = $row['prop'];

                // fill the titleHelper
                $titleHelper->addResource($url);

                // split property and use numeric last part for navigation order.
                // example property: http://www.w3.org/2000/01/rdf-schema#_1
                $pieces = explode ('_' , $property);
                if (isset($pieces[1]) && is_numeric($pieces[1])) {
                    // file the navigation array
                    $navigation[$pieces[1]] = array(
                        'url' => $url,
                        'label' => $pieces[1]
                    );
                }
            }
        }

        // round two: fill navigation array with labels from the titleHelper
        foreach ($navigation as $key => $value) {
            $label = $titleHelper->getTitle($value['url']);
            $navigation[$key]['label'] = $label;
        }

        // round three: sort navigation according to the index
        ksort($navigation);

        return $this->render($navigation);
    }

    /*
     * view setter (dev zone article: http://devzone.zend.com/article/3412)
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    /*
     * render a html snippet from the navigation structure array
     */
    private function render($navigation = array())
    {
        $return = '';
        foreach ($navigation as $item) {
            if ($item['url'] == $this->activeUrl) {
                $return .= '<li class="'.$this->activeItemClass.'">';
            } else {
                $return .= '<li>';
            }
            $return .= '<a href="'.$item['url'].'">';
            $return .= $item['label'];
            $return .= '</a>';
            $return .= '</li>';
            $return .= PHP_EOL;
        }
        $return  = '<' . $this->listTag . '>' . PHP_EOL . $return;
        $return .= '<' . $this->listTag . '>' . PHP_EOL;

        $return = $this->prefix . $return . $this->suffix;
        return $return;
    }

}
