<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_site
 * @copyright Copyright (c) 2009, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * A helper class for the site component.
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_site
 * @copyright  Copyright (c) 2009, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @subpackage component
 */
class SiteHelper extends OntoWiki_Component_Helper
{
    public function onPostBootstrap($event)
    {
        $router = $event->bootstrap->getResource('Router');
        if ($router->hasRoute('empty')) {
            //TODO: this should not be the default site but the site which match the model of the selected resource
            $site = $this->_privateConfig->defaultsite;
            $emptyRoute = new Zend_Controller_Router_Route(
                    '',
                    array(
                        'controller' => 'site',
                        'action' => $site)
                    );
            $router->addRoute('empty', $emptyRoute);
        }
    }
    
    // http://localhost/OntoWiki/SiteTest/
    public function onShouldLinkedDataRedirect($event)
    {
        $event->request->setControllerName('site');
        $event->request->setActionName($this->_privateConfig->defaultsite);
        $event->request->setDispatched(false);
        return false;
    }
}
