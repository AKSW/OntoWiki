<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * The ckan component helper (to insert a menu entry)
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_ckan
 * @subpackage helper
 */

class CkanHelper extends OntoWiki_Component_Helper
{
    /*
     * handler for the on onCreateMenu event
     */
    public function onCreateMenu($event)
    {
        // do not allow registration of resources ...
        if ($event->isModel) {
            return;
        }

        // localhost models can't be registered at CKAN
        $modelUri = (string) $event->resource;
        if (substr_count($modelUri, 'http://localhost') > 0) {
            return;
        }

        // no menu entry if the model is not part of the base url
        $baseUrl = OntoWiki::getInstance()->config->urlBase;
        if (substr_count($modelUri, $baseUrl) !== 1) {
            return;
        }

        // no menu entry if we do not have a linked data server online
        $extensionManager = OntoWiki::getInstance()->extensionManager;
        if (!$extensionManager->isExtensionRegistered('linkeddataserver')) {
            return;
        }

        // finally, create the holy menu entry and PREPEND it on top of the menu
        $url = new OntoWiki_Url(
            array('controller' => 'ckan', 'action' => 'register'),
            array('m')
        );
        $url->setParam('m', $modelUri);
        $event->menu->prependEntry('Register Knowledge Base @ CKAN', (string) $url);
    }
}

