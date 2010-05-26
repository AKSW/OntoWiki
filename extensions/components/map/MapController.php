<?php
// vim: sw=4:sts=4:expandtab

/**
 * Map component controller.
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_map
 * @author Natanael Arndt <arndtn@gmail.com>
 * @version $Id$
 * TODO comments
 */
class MapController extends OntoWiki_Controller_Component
{
    private $model;
    private $resource;
    private $store;

    private $instances = null;
    private $resources = null;
    private $resourceVar = 'resource';

    private $config;

    public static $maxResources = 1000;

    public function init()
    {
        parent::init();
        if(is_object($this->_owApp->selectedResource)){
            $this->resource = $this->_owApp->selectedResource->getIri();
        }
        $this->model    = $this->_owApp->selectedModel;
        $this->store    = $this->_erfurt->getStore();
    }

    /**
     * Shows the plain map without markers.
     * Markers are fetched via Ajax by means of the markerActions.
     */
    public function displayAction()
    {
        $this->addModuleContext('main.window.map');
        $this->view->placeholder('main.window.title')->set('OntoWiki Map Component');

        $jsonRequestUrl = new OntoWiki_Url(array('controller' => 'map', 'action' => 'marker'), array());
        $jsonRequestUrl->setParam('use_limit', 'off', true);
        $jsonRequestUrl->setParam('extent', '__extent__', true);
        if($this->_request->getControllerName() == 'resource' && $this->_request->getActionName() == 'properties') {
            $jsonRequestUrl->setParam('single_instance', 'on', true);
        }


        $this->view->jsonRequestUrl   = $jsonRequestUrl;
        $this->view->componentUrlBase = $this->_componentUrlBase;
        $this->view->extent           = $this->_getMaxExtent();
        $this->view->config           = $this->_privateConfig;

        $this->_owApp->logger->debug('MapComponent/displayAction: maximal map extention: ' . var_export($this->view->extent, true));
    }


    /**
     * returns the plain map without markers, as html.
     * Markers are fetched via Ajax by means of the markerActions.
     * this function is mostly similar to the displayAction in its code.
     * I think the inlineAction will be used in the diyplaAction in the future
     */
    public function inlineAction()
    {
        $this->_helper->layout->disableLayout();
        
        // default values from configuration
        $jsonRequestUrl = new OntoWiki_Url(array('controller' => 'map', 'action' => 'marker'), array('single_instance'));
        $jsonRequestUrl->setParam('clustering', 'off', true);
        $jsonRequestUrl->setParam('use_limit', 'on', true);
        $jsonRequestUrl->setParam('extent', '__extent__', true);

        $this->view->jsonRequestUrl   = $jsonRequestUrl;
        $this->view->componentUrlBase = $this->_componentUrlBase;
        $this->view->extent           = $this->_getMaxExtent();
        $this->view->config           = $this->_privateConfig;

        $this->_owApp->logger->debug('MapComponent/inlineAction: maximal map extention: ' . var_export($this->view->extent, true));
    }

    /**
     * Retrieves map markers for the current resource and sends a JSON array with markers
     */
    public function markerAction()
    {
        require_once $this->_componentRoot . 'classes/Marker.php';
        require_once $this->_componentRoot . 'classes/Clusterer.php';
        require_once $this->_componentRoot . 'classes/GeoCoder.php';
        
        // tells the OntoWiki to not apply the template to this action
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();

        if (isset($this->_request->extent)) {
            //$extent = $this->getParam('extent', true);
            $extent   = explode( ",", $this->_request->extent );
            $viewArea = array(
                    "top"    => $extent[0],
                    "right"  => $extent[1],
                    "bottom" => $extent[2],
                    "left"   => $extent[3]);
        } else {
            $viewArea = array(
                    "top"    => 90,
                    "right"  => 180,
                    "bottom" => -90,
                    "left"   => -180 );
        }

        if($this->resources === null) {
            $this->_getResources( $viewArea );
        }
        
        $markers = array();

        if($this->resources) {

            foreach ($this->resources as $r) {
            	
            	/**
            	 * for single instances
            	 * @var String contains the uri of the current property
            	 */
                $uri = isset($r[$this->resourceVar]) ? $r[$this->resourceVar] : $this->resource;

                if (empty ($r['lat']) || empty ($r['long'])) {
                   if(isset($r['lat2']) && isset($r['long2']) && !empty($r['lat2']) && !empty($r['long2'])) {
                        $lat = $r['lat2'];
                        $long = $r['long2'];
                   }
                } else {
                    $lat = $r['lat'];
                    $long = $r['long'];
                }

                if (!empty($lat) && !empty($long)) {
                    $marker = new Marker($uri);
                    $marker->setLat($lat);
                    $marker->setLon($long);
                    $marker->setIcon(null);

                    $markers[] = $marker;
                }
                unset($lat);
                unset($long);
            }

            /**
             * cluster the markers
             */
            if ($this->_request->clustering != 'off') {
                $clustererGridCount = $this->_privateConfig->clusterer->gridCount;
                $clustererMaxMarkers = $this->_privateConfig->clusterer->maxMarkers;

                $clusterer = new Clusterer( $clustererGridCount, $clustererMaxMarkers );
                $clusterer->setViewArea( $viewArea );
                $clusterer->setMarkers( $markers );
                $clusterer->ignite( );
                $markers = $clusterer->getMarkers( );
            }
        }

        $this->_owApp->logger->debug('MapComponent/markerAction responds with ' . count($markers) . ' Markers in the viewArea: ' . var_export($viewArea, true));

        // $this->_response->setHeader('Content-Type', 'application/json', true);
        $this->_response->setBody(json_encode($markers));
    }

    /**
     * TODO implement this function
     */
    public function configAction()
    {
        // this function gets and sends some persistent configuration values
        // $this->view->OpenLayersVersion = 
    }

    public function __call($method, $args)
    {
        $this->_forward('view');
    }

    /**
     * Get the markers in the specified area
     * TODO implement using the viewArea
     */
    private function _getResources( $viewArea = false ) {
        /**
         * read configuration
         */
        $latProperties  = $this->_privateConfig->property->latitude->toArray();
        $longProperties = $this->_privateConfig->property->longitude->toArray();
        $latProperty    = $latProperties[0];
        $longProperty   = $longProperties[0];

        if ($this->_request->single_instance != 'on') {
            $latVar         = new Erfurt_Sparql_Query2_Var('lat');
            $longVar        = new Erfurt_Sparql_Query2_Var('long');

            //the future is now!
            if($this->instances === null) {
                $this->_owApp->logger->debug('MapComponent/_getResources: memory_get_usage: ' . memory_get_usage());
                $this->_owApp->logger->debug('MapComponent/_getResources: clone this->_session->instances');
                $this->_owApp->logger->debug('MapComponent/_getResources: this->_session->instances has a size of ' . strlen(serialize($this->_session->instances)));
                if(strlen(serialize($this->_session->instances)) < 1024) {
                    $this->_owApp->logger->debug('MapComponent/_getResources: ' . $this->_session->instances);
                }
                $this->instances = clone $this->_session->instances;
                $this->_owApp->logger->debug('MapComponent/_getResources: memory_get_usage: ' . memory_get_usage());
            } else {
                $this->_owApp->logger->debug('MapComponent/_getResources: this->instances already set');
                // don't load instances again
            }

            if($this->_request->use_limit == 'off') {
                $this->instances->setLimit(self::$maxResources);
                $this->instances->setOffset(0);
            } else {
                // use the limit and offset set in the instances
            }

            $query          = $this->instances->getResourceQuery();
            //$this->_owApp->logger->debug('MapComponent/_getResources: session query: ' . var_export((string)$query, true));

            $query->removeAllOptionals()->removeAllProjectionVars();

            $query->addProjectionVar($this->instances->getResourceVar());
            $query->addProjectionVar($latVar);
            $query->addProjectionVar($longVar);

            $queryOptionalCoke     = new Erfurt_Sparql_Query2_OptionalGraphPattern();
            $queryOptionalPepsi    = new Erfurt_Sparql_Query2_OptionalGraphPattern();

            $node = new Erfurt_Sparql_Query2_Var('node'); // should be $node = new Erfurt_Sparql_Query2_BlankNode('bn'); but i heard this is not supported yet by zendb
            $queryOptionalCoke->addTriple($this->instances->getResourceVar(), $latProperty, $latVar);
            $queryOptionalCoke->addTriple($this->instances->getResourceVar(), $longProperty, $longVar);
            $queryOptionalPepsi->addTriple($this->instances->getResourceVar(), new Erfurt_Sparql_Query2_Var('pred') , $node);
            $queryOptionalPepsi->addTriple($node, $latProperty, $latVar);
            $queryOptionalPepsi->addTriple($node, $longProperty, $longVar);

            $query->setQueryType(Erfurt_Sparql_Query2::typeSelect);
            $queryDirect = clone $query;
            $queryIndire = clone $query;
            $queryDirect->addElement($queryOptionalCoke);
            $queryIndire->addElement($queryOptionalPepsi);
            $this->_owApp->logger->debug('MapComponent/_getResources sent directQuery: "' . $queryDirect . '" to get markers.');
            $this->_owApp->logger->debug('MapComponent/_getResources sent indirectQuery: "' . $queryIndire . '" to get markers.');

            /* get result of the query */
            $resourcesDir    = $this->_owApp->erfurt->getStore()->sparqlQuery($queryDirect);
            $resourcesInd    = $this->_owApp->erfurt->getStore()->sparqlQuery($queryIndire);

            $this->resourceVar  = $this->instances->getResourceVar()->getName();

            /**
             * merge theses two results
             */
            //$resourcesDir = $this->cpVarToKey($resourcesDir, $this->resourceVar);
            //$resourcesInd = $this->cpVarToKey($resourcesInd, $this->resourceVar);
            
            $this->resources = array_merge_recursive($resourcesDir, $resourcesInd);

            /**
             * If you get problems with multiple coordinates for one resource you have to remove all array values with non string-keys
             */

        } else if ($this->_request->single_instance == 'on') {
            //$query = new Erfurt_Sparql_SimpleQuery();
            $directQueryString = '
            SELECT ?lat ?long
            WHERE {
            	<' . $this->resource . '> <' . $latProperty . '> ?lat;
            							  <' . $longProperty . '> ?long.
        	}';
            $indireQueryString = '
            SELECT ?lat ?long
            WHERE {
            	<' . $this->resource . '> ?p ?node.
            	?node <' . $latProperty . '> ?lat;
            		  <' . $longProperty . '> ?long.
        	}';
            $this->_owApp->logger->debug('MapComponent/_getResources direct query "' . $directQueryString . '".');
            $this->_owApp->logger->debug('MapComponent/_getResources indirect query "' . $indireQueryString . '".');
            $queryDirect = Erfurt_Sparql_SimpleQuery::initWithString($directQueryString);
            $queryIndire = Erfurt_Sparql_SimpleQuery::initWithString($indireQueryString);

            /* get result of the query */
            $this->resources   = $this->_owApp->erfurt->getStore()->sparqlQuery($queryDirect);
            
            if (empty($this->resources[0]['lat']) OR empty($this->resources[0]['long'])) {
            	$this->resources = $this->_owApp->erfurt->getStore()->sparqlQuery($queryIndire);
            }
            
        } else {
            $this->_owApp->logger->debug('MapComponent/_getResources request single_instace contains neither "on" nor "off".');
        }

        $this->_owApp->logger->debug('MapComponent/_getResources got respons "' . var_export($this->resources, true) . '".');

    }

    /**
     * Calculates the maximum distance of the markers, to get the optimal viewArea/extent for initial map view.
     * This function has many code duplicats, needs a rework.
     * @return array {"top" (max. lat.), "right"  (max. long.), "bottom" (min. lat.), "left" (min. long.)}
     */
    private function _getMaxExtent () {

        if($this->resources === null) {
            $this->_getResources( );
        }

//        $this->_owApp->logger->debug('MapComponent/_getMaxExtent: resources: ' . var_export($this->resources, true));

        $lat = array();
        $long = array();
        foreach($this->resources as $r) {
            if(!empty($r['lat'])) {
                $lat[] = $r['lat'];
            }
            if(!empty($r['lat2'])) {
                $lat[] = $r['lat2'];
            }
            if(!empty($r['long'])) {
                $long[] = $r['long'];
            }
            if(!empty($r['long2'])) {
                $long[] = $r['long2'];
            }
        }

        if(count($lat) > 0 AND count($long) > 0) {
            $return = array(
                    "top"    => max($lat),
                    "right"  => max($long),
                    "bottom" => min($lat),
                    "left"   => min($long)
                );
        } else {
            /**
             * set default possition, if no resource is selected
             */
            $return = array(
                    "top"    => $this->_privateConfig->default->latitude,
                    "right"  => $this->_privateConfig->default->longitude,
                    "bottom" => $this->_privateConfig->default->latitude,
                    "left"   => $this->_privateConfig->default->longitude
                );
        }

        $this->_owApp->logger->debug('MapComponent/_getMaxExtent: extent: ' . var_export($return, true));

        return $return;
    }

    /**
     * Copies a uri from its value field in the resultset to the key of the array-element.
     * The $key identifies the key to the uri.
     * $array = array(
     * 	0 => array(
     * 		'resourceUri' => 'http://comiles.eu/~natanael/foaf.rdf#me',
     * 		'long' => '12.3456',
     * 		'lat' => '12.3456'
     * 	)
     * );
     * $key = 'resourceUri';
     * 
     * will become
     * 
     * $array = array(
     * 	'http://comiles.eu/~natanael/foaf.rdf#me' => array(
     * 		'resourceUri' => 'http://comiles.eu/~natanael/foaf.rdf#me',
     * 		'long' => '12.3456',
     * 		'lat' => '12.3456'
     * 	)
     * );
     * 
     * @param array $array The Resultset, which is returned by a sparqlquery
     * @param String $key of the array element holding the URI
     */
    private function cpVarToKey($array, $key){
        for($i = 0; $i < count($array); $i++) {
            if(isset($array[$array[$i][$key]])) {
                $array[$array[$i][$key]] = array_merge($array[$array[$i][$key]], $array[$i]);
            } else {
                $array[$array[$i][$key]] = $array[$i];
            }
            unset($array[$i]);
        }
    }
}

