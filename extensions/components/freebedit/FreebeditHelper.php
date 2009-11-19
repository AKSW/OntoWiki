<?php

require_once 'OntoWiki/Component/Helper.php';

/**
 * * Created on 30 Sep 2009
 *
 * @author: Kurt Jacobson - kurtjx at gmail
 *
 * a component meant to emulate the functionality of Freebase views
 * by providing a set of 'default' properties associated with various
 * classes
 */
class FreebeditHelper extends OntoWiki_Component_Helper
{
    public function init()
    {
    	$owApp = OntoWiki_Application::getInstance();

        if ($owApp->selectedModel) {
            $store    = $owApp->erfurt->getStore();
            $resource = (string) $owApp->selectedResource;

            require_once 'Erfurt/Sparql/SimpleQuery.php';
            $query = new Erfurt_Sparql_SimpleQuery();

            // build SPARQL query for getting class (rdf:type) of current resource
            $query->setProloguePart('SELECT DISTINCT ?t')
                  ->setWherePart('WHERE {<' . $resource . '> a ?t.}');

            // query the store
            if ($result = $owApp->selectedModel->sparqlQuery($query)) {
                $row = current($result);
                $class = $row['t'];


                if (!in_array('http://www.w3.org/2002/07/owl#Class', array($class))) {
			    	// always include this editor tab
			    	require_once 'OntoWiki/Navigation.php';
			        OntoWiki_Navigation::register('freebedit', array(
			            'controller' => 'freebedit',
			            'action'     => 'thing',
			            'name'       => 'UMG View',
			            'priority'   => -1,
			            'route'      => 'properties',   // Hijack OntoWiki's default route
			            ));
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
                            $request->setControllerName('freebedit')
                                    ->setActionName('thing');
                        }
                    } catch (Zend_Controller_Router_Exception $e) {
                        // do nothing if route fails
                    }

                    // we must set a new route so that the navigation class knows,
                    // we are hijacking the default route
                    $route = new Zend_Controller_Router_Route(
                        'view/*',                       // hijack 'view' shortcut
                        array(
                            'controller' => 'freebedit', // map to 'foafedit' controller and
                            'action'     => 'thing'    // 'person' action
                        )
                    );

                    // add the new route
                    $router->addRoute('properties', $route);
                }
            }
        }
    }
}
?>
