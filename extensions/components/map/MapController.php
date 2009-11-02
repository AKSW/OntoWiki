<?php
// vim: sw=4:sts=4:expandtab

/* Hint: use PHP_EOL which is defined to the 
   system's line ending character ;) */
if (!defined("EOL")) {
    define("EOL","\n");
}

/**
 * Map component controller.
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_map
 */
class MapController extends OntoWiki_Controller_Component
{
    private $model;
    private $resource;
    private $store;

    private $resources = null;
    private $resourceVar = 'resource';

    private $config;

    public function init()
    {
        parent::init();
        $this->resource = $this->_owApp->selectedResource->getIri();
        $this->model    = $this->_owApp->selectedModel;
        $this->store    = $this->_erfurt->getStore();

        $this->config   = array(
            'icon'          => $this->_privateConfig->icon,
            'clusterIcon'   => $this->_privateConfig->cluster,
            'apikeys'       => array(
                'google'        => $this->_privateConfig->apikey->google,
            ),
            'lat'           => $this->_privateConfig->default->latitude,
            'long'          => $this->_privateConfig->default->longitude
        );
    }

    /**
     * Shows the plain map without markers.
     * Markers are fetched via Ajax by means of the markerActions.
     */
    public function displayAction()
    {
        $this->view->placeholder('main.window.title')->set('OntoWiki Map Component');

        $instances = $this->_session->instances;
        $instances->setLimit(100); // TODO should unset limit, but not supported by Query2 at the moment
        $instances->setOffset(0);
        $this->_session->instances =  $instances;

        $query = (string)$instances->getResourceQuery();
        $this->_owApp->logger->debug('MapComponent/displayAction: session query: ' . var_export($query, true));
        
        $this->view->componentUrlBase = $this->_componentUrlBase;
/*
        $this->view->headLink()->appendStylesheet($this->_componentUrlBase.'css/OpenLayers.css');
        $this->view->headScript()->appendFile('http://maps.google.com/maps?file=api&v=2&hl=de&key=' . $this->_privateConfig->apikey->google);
        $this->view->headScript()->appendFile($this->_componentUrlBase.'resources/lib/OpenLayers.js');
        $this->view->headScript()->appendFile($this->_componentUrlBase.'resources/lib/OpenStreetMap.js');
        $this->view->headScript()->appendFile($this->_componentUrlBase.'resources/classes/MapManager.js');
 */
        // default values from configuration
        $this->view->defaultLat             = $this->_privateConfig->default->latitude;
        $this->view->defaultLong            = $this->_privateConfig->default->longitude;
        $this->view->icon                   = $this->_privateConfig->icon;
        $this->view->cluster                = $this->_privateConfig->cluster;
        $this->view->apikey                 = $this->_privateConfig->apikey;

        /* doesn't work at the moment, because the menu can't be accessed from javascript at runtime */
        /* add ontowiki-style layer switcher */
        $this->view->defaultLayer           = $this->_privateConfig->default->layer;

        $jsonRequestUrl = new OntoWiki_Url(array('controller' => 'map', 'action' => 'marker'));
        $jsonRequestUrl->setParam('extent', "__extent__", true);

        $this->view->jsonRequestUrl = $jsonRequestUrl;

        $this->view->extent         = $this->_getMaxExtent();

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

        $this->view->componentUrlBase = $this->_componentUrlBase;
        
        $this->_owApp->logger->debug('MapComponent/inlineAction session: rdf_type => ' . var_export($this->_owApp->selectedClass, true));

        // default values from configuration
        $jsonRequestUrl = new OntoWiki_Url(array('controller' => 'map', 'action' => 'marker'));
        $jsonRequestUrl->setParam('clustering', "off", true);
        $jsonRequestUrl->setParam('extent', "__extent__", true);

        $this->_owApp->logger->debug('MapComponent/inlineAction session: rdf_type => ' . var_export($this->_owApp->selectedClass, true));

        $this->view->jsonRequestUrl     = $jsonRequestUrl;
        $this->view->apikey             = $this->_privateConfig->apikey;
        $this->view->defaultLat         = $this->_privateConfig->default->latitude;
        $this->view->defaultLong        = $this->_privateConfig->default->longitude;
        $this->view->icon               = $this->_privateConfig->icon;
        $this->view->cluster            = $this->_privateConfig->cluster;
        $this->view->icon_selected      = $this->_privateConfig->icon_selected;
        $this->view->cluster_selected   = $this->_privateConfig->cluster_selected;
        $this->view->defaultLayer       = $this->_privateConfig->default->layer;
        $this->view->extent             = $this->_getMaxExtent();

        $this->_owApp->logger->debug('MapComponent/inlineAction: maximal map extention: ' . var_export($this->view->extent, true));
        $this->_owApp->logger->debug('MapComponent/inlineAction session: rdf_type => ' . var_export($this->_owApp->selectedClass, true));
    }

    /**
     * Retrieves map markers for the current resource and sends a JSON array with markers
     */
    public function markerAction()
    {
        $this->_owApp->logger->debug('MapComponent/markerAction session: rdf_type => ' . var_export($this->_owApp->selectedClass, true));
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
            $titleHelper = new OntoWiki_Model_TitleHelper($this->model);

            foreach ($this->resources as $r) {
                $uri = isset($r[$this->resourceVar]) ? $r[$this->resourceVar] : $this->resource;
                $titleHelper->addResource($uri); // do I realy need titles here?
            }

            foreach ($this->resources as $r) {
                $url = new OntoWiki_Url(array('route' => 'properties'));
                $uri = isset($r[$this->resourceVar]) ? $r[$this->resourceVar] : $this->resource;
                $url->setParam('r', $uri, true);

                if (empty ($r['lat']) || empty ($r['long'])) {
                    if(!empty($r['lat2']) && !empty($r['long2'])) {
                        $lat = $r['lat2'];
                        $long = $r['long2'];
                    }
                } else {
                    $lat = $r['lat'];
                    $long = $r['long'];
                }

                if (!empty($lat) && !empty($long)) {
                    $marker = new Marker($uri);
                    $marker->setLabel($titleHelper->getTitle($uri)); // do I realy need titles here?
                    $marker->setLat($lat);
                    $marker->setLon($long);
                    $marker->setUrl((string) $url);
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

        $latVar         = new Erfurt_Sparql_Query2_Var('lat');
        $longVar        = new Erfurt_Sparql_Query2_Var('long');
        $lat2Var        = new Erfurt_Sparql_Query2_Var('lat2');
        $long2Var       = new Erfurt_Sparql_Query2_Var('long2');

        //the future is now!
        $instances      = $this->_session->instances;
        $query          = $instances->getResourceQuery();
        
        $query->removeAllOptionals()->removeAllProjectionVars();

        $query->addProjectionVar($instances->getResourceVar());
        $query->addProjectionVar($latVar);
        $query->addProjectionVar($longVar);
        $query->addProjectionVar($lat2Var);
        $query->addProjectionVar($long2Var);

        $queryOptional     = new Erfurt_Sparql_Query2_OptionalGraphPattern();

        $node = new Erfurt_Sparql_Query2_Var('node'); // should be $node = new Erfurt_Sparql_Query2_BlankNode('bn'); but i heard this is not supported yet by zendb
        $queryOptional->addTriple($instances->getResourceVar(), $latProperty, $latVar);
        $queryOptional->addTriple($instances->getResourceVar(), $longProperty, $longVar);
        $queryOptional->addTriple($instances->getResourceVar(), new Erfurt_Sparql_Query2_Var('pred') , $node);
        $queryOptional->addTriple($node, $latProperty, $lat2Var);
        $queryOptional->addTriple($node, $longProperty, $long2Var);

        $query->addElement($queryOptional);
        $query->setQueryType(Erfurt_Sparql_Query2::typeSelect);
        $this->_owApp->logger->debug('MapComponent/_getResources sent "' . $query . '" to get markers.');

        /* get result of the query */
        $this->resources    = $this->_owApp->erfurt->getStore()->sparqlQuery($query);

        $this->_owApp->logger->debug('MapComponent/_getResources got respons "' . var_export($this->resources, true) . '".');

        $this->resourceVar  = $instances->getResourceVar()->getName();
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
}

