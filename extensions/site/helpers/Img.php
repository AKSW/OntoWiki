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
     * the main img method, mentioned parameters are:
     * - src      - the URL of the image to load
     * - class    - the value of the class attribute
     * - alt      - the value of the alt attribute
     * - filter   - the filter string for the IPC (if enabled)
     * - prefix   - string at the beginning
     * - suffix   - string at the end
     */
    public function img($options = array())
    {
        $owapp       = OntoWiki::getInstance();
        $store       = $owapp->erfurt->getStore();
        $model       = $owapp->selectedModel;
        $extManager  = $owapp->extensionManager;
        $titleHelper = new OntoWiki_Model_TitleHelper($model);

        // check for options and assign local vars or null
        $src      = (isset($options['src']))    ? (string) $options['src']         : null;
        $class    = (isset($options['class']))  ? ' class="'.$options['class'].'"' : '';
        $alt      = (isset($options['alt']))    ? ' alt="'.$options['alt'].'"'     : '';
        $filter   = (isset($options['filter'])) ? $options['filter']               : null;
        $prefix   = (isset($options['prefix'])) ? $options['prefix']               : '';
        $suffix   = (isset($options['suffix'])) ? $options['suffix']               : '';

        // if an uri is given, we do not need to search for
        if (!isset($src)) {
            // @todo: resolve images from the resource
            throw new Exception('currently this helper needs a src parameter');
        }

        if ($filter && $extManager->isExtensionRegistered('ipc')) {
            $ipcUrl = $owapp->config->urlBase . '/ipc/get';
            $ipcUrl = $ipcUrl . '?img='. urlencode($src) .'&filter='. urlencode($filter);
            $src    = $ipcUrl;
        }

        return "$prefix<img$class$alt src=\"$src\" />$suffix";
    }

    /*
     * view setter (dev zone article: http://devzone.zend.com/article/3412)
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }
}
