<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once 'OntoWiki/Plugin.php';

/**
 * @category   OntoWiki
 * @package    Extensions_Semanticsitemap
 */
class SemanticsitemapPlugin extends OntoWiki_Plugin
{

    public function onRouteStartup(){
        // get current route info
        $front  = Zend_Controller_Front::getInstance();
        $router = $front->getRouter();
        
        // we must set a new route so that the navigation class knows, 
        $route = new Zend_Controller_Router_Route(
            'sitemap.xml',                       // hijack 'sitemap.xml' shortcut
            array(
                'controller' => 'semanticsitemap', // map to 'semanticsitemap' controller and
                'action'     => 'sitemap'    // 'sitemap' action
            )
        );

        // add the new route
        $router->addRoute('showsitemap', $route);
    }
}

