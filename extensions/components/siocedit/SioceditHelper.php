<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2009, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once 'OntoWiki/Component/Helper.php';

/**
 * Helper class for the SIOC Editor component.
 *
 * @copyright Copyright (c) 2009, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @category OntoWiki
 * @package Extensions
 * @subpackage Siocedit
 * @author Christoph Rieß <c.riess.dev@googlemail.com>
 */
class SioceditHelper extends OntoWiki_Component_Helper
{
    public function init()
    {
        // get the main application
        $owApp = OntoWiki::getInstance();    
             
        // we need a graph to work with
        if ($owApp->selectedModel) {
        
            // generate uri array
            $siocNS = $this->_privateConfig->ns;
            
            $siocUriArray = array();
            
            foreach ($this->_privateConfig->class->toArray() as $action => $part) {
                $siocUriArray[$siocNS . $part] = $action;
            }

            $store    = $owApp->erfurt->getStore();
            $query    = new Erfurt_Sparql_SimpleQuery();
            $resource = (string)$owApp->selectedResource;
            
            // build SPARQL query for getting class (rdf:type) of current resource
            $query->setProloguePart('SELECT DISTINCT ?t')
                  ->setWherePart('WHERE {<' . $resource . '> a ?t.}');
            
            // query the store
            $result = $owApp->selectedModel->sparqlQuery($query);
            
            // check for uri of type available
            if (!empty($result)) {
                $row = reset($result);
                $uri = $row['t'];
            } else {
                $uri = false;
            }
            
            // check if it is sioc uri as defined in Array
            if ( $uri && array_key_exists($uri,$siocUriArray) ) {
            
                // Add siocedit tab
                OntoWiki_Navigation::register('siocedit', array(
                    'controller' => 'siocedit',                // siocedit controller
                    'action'     => $siocUriArray[$uri],       // appropriate action
                    'route'      => 'properties',              // Hijack OntoWiki's default route
                    'name'       => 'SIOC Editor',
                    'priority'   => -1));
                
                // OntoWiki's standard routes are configured in application/config/default.ini
                // see http://framework.zend.com/manual/en/zend.controller.router.html#zend.controller.router.routes
                
                // get current route info
                $front  = Zend_Controller_Front::getInstance();
                $router = $front->getRouter();
                
                // is the current route the one we want to hijack?
                try {
                    if ($router->getCurrentRouteName() == 'properties') {
                        // redirect request to foafedit/person
                        $request = $front->getRequest();
                        $request->setControllerName('siocedit')
                                ->setActionName($siocUriArray[$uri]);
                    }
                } catch (Zend_Controller_Router_Exception $e) {
                    // do nothing if route fails
                }
                
                // we must set a new route so that the navigation class knows, 
                // we are hijacking the default route
                $route = new Zend_Controller_Router_Route(
                    'view/*',                       // hijack 'view' shortcut
                    array(
                        'controller' => 'foafedit',         // map to 'foafedit' controller and
                        'action'     => $siocUriArray[$uri] // 'person' action
                    )
                );

                // add the new route
                $router->addRoute('properties', $route);
                    
            }
            
/*            if ($result = $owApp->selectedModel->sparqlQuery($query)) {
                $row = current($result);
                $class = $row['t'];

                // get all super classes of the class
                $super = $store->getTransitiveClosure(
                    (string) $owApp->selectedModel, 
                    EF_RDFS_SUBCLASSOF, 
                    $class, 
                    false);

                $types = array($class);
                foreach ($super as $typeInfo) {
                    $types[] = $typeInfo['parent'];
                }
                
                $types = array_combine($types, $types);
                
                if (array_key_exists($this->_privateConfig->person, $types)) {
                    // we have a foaf:Person
                    // register new tab
                    OntoWiki_Navigation::register('siocedit', array(
                        'controller' => 'siocedit',     // foafedit controller
                        'action'     => 'person',       // person action
                        'route'      => 'properties',   // Hijack OntoWiki's default route
                        'name'       => 'SIOC Editor', 
                        'priority'   => -1));
                    
                    // OntoWiki's standard routes are configured in application/config/default.ini
                    // see http://framework.zend.com/manual/en/zend.controller.router.html#zend.controller.router.routes
                    
                    // get current route info
                    $front  = Zend_Controller_Front::getInstance();
                    $router = $front->getRouter();
                    
                    // is the current route the one we want to hijack?
                    try {
                        if ($router->getCurrentRouteName() == 'properties') {
                            // redirect request to foafedit/person
                            $request = $front->getRequest();
                            $request->setControllerName('siocedit')
                                    ->setActionName('person');
                        }
                    } catch (Zend_Controller_Router_Exception $e) {
                        // do nothing if route fails
                    }
                    
                    // we must set a new route so that the navigation class knows, 
                    // we are hijacking the default route
                    $route = new Zend_Controller_Router_Route(
                        'view/*',                       // hijack 'view' shortcut
                        array(
                            'controller' => 'foafedit', // map to 'foafedit' controller and
                            'action'     => 'person'    // 'person' action
                        )
                    );

                    // add the new route
                    $router->addRoute('properties', $route);
                }
            }*/
        }
    }
}

