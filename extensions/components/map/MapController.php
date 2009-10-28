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

        unset($this->_owApp->session->instancelimit);
        unset($this->_owApp->session->instanceoffset);

        $this->view->componentUrlBase = $this->_componentUrlBase;

        $this->view->headLink()->appendStylesheet($this->_componentUrlBase.'css/OpenLayers.css');
        $this->view->headScript()->appendFile('http://maps.google.com/maps?file=api&v=2&hl=de&key=' . $this->_privateConfig->apikey->google);
        $this->view->headScript()->appendFile($this->_componentUrlBase.'resources/lib/OpenLayers.js');
        $this->view->headScript()->appendFile($this->_componentUrlBase.'resources/lib/OpenStreetMap.js');
        $this->view->headScript()->appendFile($this->_componentUrlBase.'resources/classes/MapManager.js');

        $this->view->headScript()->appendScript('
            $(document).ready(function() {setMapHeight();initMap();});
        ');

        // default values from configuration
        $this->view->defaultLat             = $this->_privateConfig->default->latitude;
        $this->view->defaultLong            = $this->_privateConfig->default->longitude;
        $this->view->icon                   = $this->_privateConfig->icon;
        $this->view->cluster                = $this->_privateConfig->cluster;

        /* doesn't work at the moment, because the menu can't be accessed from javascript at runtime */
        /* add ontowiki-style layer switcher */
        $this->view->defaultLayer           = $this->_privateConfig->default->layer;

        $jsonRequestUrl = new OntoWiki_Url(array('controller' => 'map', 'action' => 'marker'), array());//array('r'));
        $jsonRequestUrl->setParam('datatype', "json", true);
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
        $jsonRequestUrl = new OntoWiki_Url(array('controller' => 'map', 'action' => 'marker'), array());//array('r'));
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

        $markers = array();

        $result = $this->_getMarkerResult( $viewArea );
        $instanceVar = $result[1];
        $result = $result[0];

        if($result) {
            $titleHelper = new OntoWiki_Model_TitleHelper($this->model);
            foreach ($result as $row) {
                $uri = isset($row[$instanceVar]) ? $row[$instanceVar] : $this->resource;
                $titleHelper->addResource($uri);
            }
            foreach ($result as $row) {

                $url = new OntoWiki_Url(array('route' => 'properties'), array());//array('r'));
                $uri = isset($row[$instanceVar]) ? $row[$instanceVar] : $this->resource;
                $url->setParam('r', $uri, true);

                if (empty ($row['lat']) || empty ($row['long'])) {
                    if(!empty($row['indirectLat']) && !empty($row['indirectLong'])) {
                        $lat = $row['indirectLat'];
                        $long = $row['indirectLong'];
                    }
                } else {
                    $lat = $row['lat'];
                    $long = $row['long'];
                }

                if (!empty($lat) && !empty($long)) {
                    $marker = new Marker($uri);
                    $marker->setLabel($titleHelper->getTitle($uri));
                    $marker->setLat($lat);
                    $marker->setLon($long);
                    $marker->setUrl((string) $url);
                    $marker->setIcon(null);

                    $markers[] = $marker;
                }
                unset($lat);
                unset($long);
            }

            if ($this->_request->clustering != 'off') {
                $clustererGridCount = $this->_privateConfig->clusterer->gridCount;
                $clustererMaxMarkers = $this->_privateConfig->clusterer->maxMarkers;

                $clusterer = new Clusterer( $clustererGridCount, $clustererMaxMarkers );
                $clusterer->setViewArea( $viewArea );
                //$clusterer->setMarkers( $markersVisible );//$this->markers );
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
     */
    private function _getMarkerResult( $viewArea = false, $limit = false, $order = false ) {

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
        $query = $instances->getResourceQuery();
        
        $query->removeAllOptionals()->removeAllProjectionVars();

        $query->addProjectionVar($instances->getResourceVar());
        $query->addProjectionVar($latVar);
        $query->addProjectionVar($longVar);
        $query->addProjectionVar($lat2Var);
        $query->addProjectionVar($long2Var);

        $directQuery     = new Erfurt_Sparql_Query2_GroupGraphPattern();
        $indirectQuery   = new Erfurt_Sparql_Query2_GroupGraphPattern(); 

        $directQuery->addTriple(
                $instances->getResourceVar(),
                $latProperty,
                $latVar);
        $directQuery->addTriple(
                $instances->getResourceVar(),
                $longProperty,
                $longVar);

        $node = new Erfurt_Sparql_Query2_Var('node'); // should be $node = new Erfurt_Sparql_Query2_BlankNode('bn'); but i heard this is not supported yet by zendb
        $indirectQuery->addTriple($instances->getResourceVar(), new Erfurt_Sparql_Query2_Var('pred') , $node);
        $indirectQuery->addTriple($node, $latProperty, $lat2Var);
        $indirectQuery->addTriple($node, $longProperty, $long2Var);

        $union = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern(); 

        $union->addElement($directQuery)->addElement($indirectQuery);

        $query->addElement($union);
        //$query->getOrder()->add($instances->getResourceVar());
        $query->setQueryType(Erfurt_Sparql_Query2::typeSelect);
        //echo $query;
        $this->_owApp->logger->debug('MapComponent/_getMarkerResult sent "' . $query . '" to get markers.');

        /* get result of the query */
        //for some reason sparqlAsk wants a SimpleQuery
        
        $result = $this->_owApp->erfurt->getStore()->sparqlQuery($query);

        return array($result, $instances->getResourceVar()->getName());
    }

    /**
     * Calculates the maximum distance of the markers, to get the optimal viewArea/extent for initial map view.
     * This function has many code duplicats, needs a rework.
     * @return array {"top" (max. lat.), "right"  (max. long.), "bottom" (min. lat.), "left" (min. long.)}
     */
    private function _getMaxExtent () {

        // build the querys to get the maximal markers
        $topQuery    = new Erfurt_Sparql_SimpleQuery( );
        $rightQuery  = new Erfurt_Sparql_SimpleQuery( );
        $bottomQuery = new Erfurt_Sparql_SimpleQuery( );
        $leftQuery   = new Erfurt_Sparql_SimpleQuery( );
        $topQuery->setProloguePart( 'SELECT ?instance ?lat ?long' );
        $rightQuery->setProloguePart( 'SELECT ?instance ?lat ?long' );
        $bottomQuery->setProloguePart( 'SELECT ?instance ?lat ?long' );
        $leftQuery->setProloguePart( 'SELECT ?instance ?lat ?long' );

        $where         = "WHERE {" . EOL;

        // get the transitive closure of subclasses of the selected resource
        $types		= array_keys($this->store->getTransitiveClosure($this->model->getModelIri(), EF_RDFS_SUBCLASSOF, array($this->resource), true));
        $typesWhere	= " UNION { ?instance a <" . implode( ">. } UNION { ?instance a <", $types ) . ">. } ";

        $latitude	= $this->_privateConfig->property->latitude->toArray();
        $longitude	= $this->_privateConfig->property->longitude->toArray();

        $this->_owApp->logger->debug('MapComponent/_getMaxExtent: latitude: ' . var_export($latitude, true));
        $this->_owApp->logger->debug('MapComponent/_getMaxExtent: longitude: ' . var_export($longitude, true));

        $skelWhere = "		{"																	 . EOL;
        $skelWhere.= "			{ ?instance a ?b. FILTER (sameTerm (?instance, <%s>))} %s" 		 . EOL;
        $skelWhere.= "			?instance <%s> ?lat;"									 . EOL;
        $skelWhere.= "			          <%s> ?long."									 . EOL;
        $skelWhere.= "		 }"																	 . EOL;

        for ($i = 0; $i < count($latitude); $i++) {
            $lat = $latitude[$i];
            $long = $longitude[$i];
            if ($i != 0) $where.= " UNION ";
            $where .= sprintf($skelWhere, $this->resource, $typesWhere, $lat, $long);
        }

        $where        .= "}";

        $topQuery->setWherePart( $where );
        $rightQuery->setWherePart( $where );
        $bottomQuery->setWherePart( $where );
        $leftQuery->setWherePart( $where );

        $topQuery->setOrderClause("DESC(?lat)");
        $rightQuery->setOrderClause("DESC(?long)");
        $bottomQuery->setOrderClause("ASC(?lat)");
        $leftQuery->setOrderClause("ASC(?long)");

        $topQuery->setLimit( 1 );
        $rightQuery->setLimit( 1 );
        $bottomQuery->setLimit( 1 );
        $leftQuery->setLimit( 1 );
        
        $this->_owApp->logger->debug('MapComponent/_getMaxExtent: topQuery: ' . var_export((string)$topQuery, true));
        $this->_owApp->logger->debug('MapComponent/_getMaxExtent: rightQuery: ' . var_export((string)$rightQuery, true));
        $this->_owApp->logger->debug('MapComponent/_getMaxExtent: bottomQuery: ' . var_export((string)$bottomQuery, true));
        $this->_owApp->logger->debug('MapComponent/_getMaxExtent: leftQuery: ' . var_export((string)$leftQuery, true));

        $top = $this->model->sparqlQuery($topQuery);
        $right = $this->model->sparqlQuery($rightQuery);
        $bottom = $this->model->sparqlQuery($bottomQuery);
        $left = $this->model->sparqlQuery($leftQuery);

        if(isset($top[0]) AND isset($right[0]) AND isset($bottom[0]) AND $left[0]) {
            $return = array(
                    "top"    => $top[0]['lat'],
                    "right"  => $right[0]['long'],
                    "bottom" => $bottom[0]['lat'],
                    "left"   => $left[0]['long']);
        }

        if (!isset($return)) {
            /**
             * set default possition, if no resource is selected
             */
            $return = array(
                    "top"    => $this->_privateConfig->default->latitude,
                    "right"  => $this->_privateConfig->default->longitude,
                    "bottom" => $this->_privateConfig->default->latitude,
                    "left"   => $this->_privateConfig->default->longitude);
        }

        $this->_owApp->logger->debug('MapComponent/_getMaxExtent: extent: ' . var_export($return, true));

        return $return;

    }
}

