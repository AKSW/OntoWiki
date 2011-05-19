<?php
require_once 'OntoWiki/Plugin.php';

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

