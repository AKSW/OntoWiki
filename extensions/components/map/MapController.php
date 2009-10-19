<?php
// vim: sw=4:sts=4:expandtab

if (!defined("EOL")) {
    define("EOL","\n");
}

require_once 'Erfurt/Sparql/SimpleQuery.php';
require_once 'OntoWiki/Controller/Component.php';

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
    private $indirectProperties = null;

    public function init()
    {
        parent::init();
//        if(!isset($this->_owApp->instances)) echo "not set";
        $this->resource = $this->_owApp->selectedResource->getIri();
        $this->model    = $this->_owApp->selectedModel;
        $this->store    = $this->_erfurt->getStore();
    }

    /**
     * Shows the plain map without markers.
     * Markers are fetched via Ajax by means of the markerActions.
     */
    public function displayAction()
    {
        $this->view->placeholder('main.window.title')->set('OntoWiki Map Component');

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
        $this->view->icon_selected          = $this->_privateConfig->icon_selected;
        $this->view->cluster_selected       = $this->_privateConfig->cluster_selected;

        if (count($this->getIndirectProperties( )) > 1) {
            $this->view->indirect           = $this->getIndirectProperties( );
        } else {
            $this->view->indirect           = false;
        }

        if (isset($this->_request->selectedIndirect)) {
            $this->view->selectedIndirect   = $this->_request->selectedIndirect;
        }

        /* doesn't work at the moment, because the menu can't be accessed from javascript at runtime */
        /*
           $menu = new OntoWiki_Menu();
           $layerMenu = new OntoWiki_Menu();
           $layerMenu->setEntry('Map-Layer0','');
           $layerMenu->setEntry('Map-Layer1','');
           $layerMenu->setEntry('Map-Layer2','');
           $layerMenu->setEntry('Map-Layer3','');
           $layerMenu->setEntry('Map-Layer4','');
           $menu->setEntry('Map-Layer', $layerMenu);
           $this->view->placeholder('main.window.menu')->set($menu->toArray());
           $this->view->layerMenuId = "ontowiki_menu_".strtolower('Map-Layer');
         */
        $this->view->defaultLayer = $this->_privateConfig->default->layer;

        $jsonRequestUrl = new OntoWiki_Url(array('controller' => 'map', 'action' => 'marker'), array('r'));
        $jsonRequestUrl->setParam('datatype', "json", true);
        $jsonRequestUrl->setParam('extent', "__extent__", true);

        if (count ($this->getIndirectProperties ( )) > 1) {
            $jsonRequestUrl->setParam('selectedIndirect', "__indirect__", true);
        }
        $this->view->jsonRequestUrl = $jsonRequestUrl;


        $this->view->extent 		= $this->_getMaxExtent();
        if ($this->_request->datatype != "json" && $this->_request->var_dump == "true") {
            echo "extent view array" . EOL;
            var_dump($this->view->extent);
        }
        /*
           I don't know, why I've done this
           $this->view->defaultLat		= $this->view->extent['top'] - $this->view->extent['bottom'];
           $this->view->defaultLong	= $this->view->extent['right'] - $this->view->extent['left'];
         */
    }

    public function inlineAction()
    {
        $this->_helper->layout->disableLayout();

        $this->view->componentUrlBase = $this->_componentUrlBase;

        // default values from configuration
        $this->view->apikey                 = $this->_privateConfig->apikey;
        $this->view->defaultLat             = $this->_privateConfig->default->latitude;
        $this->view->defaultLong            = $this->_privateConfig->default->longitude;
        $this->view->icon                   = $this->_privateConfig->icon;
        $this->view->cluster                = $this->_privateConfig->cluster;
        $this->view->icon_selected          = $this->_privateConfig->icon_selected;
        $this->view->cluster_selected       = $this->_privateConfig->cluster_selected;

        if (count($this->getIndirectProperties( )) > 1) {
            $this->view->indirect           = $this->getIndirectProperties( );
        } else {
            $this->view->indirect           = false;
        }

        if (isset($this->_request->selectedIndirect)) {
            $this->view->selectedIndirect   = $this->_request->selectedIndirect;
        }

        $this->view->defaultLayer = $this->_privateConfig->default->layer;

        $jsonRequestUrl = new OntoWiki_Url(array('controller' => 'map', 'action' => 'marker'), array('r'));
        $jsonRequestUrl->setParam('datatype', "json", true);
        $jsonRequestUrl->setParam('extent', "__extent__", true);
        $jsonRequestUrl->setParam('clustering', "off", true);

        if (count ($this->getIndirectProperties ( )) > 1) {
            $jsonRequestUrl->setParam('selectedIndirect', "__indirect__", true);
        }
        $this->view->jsonRequestUrl = $jsonRequestUrl;


        $this->view->extent 		= $this->_getMaxExtent();
        /*
        $this->view->extent 		= array(
                    "top"    => $this->_privateConfig->default->latitude,
                    "right"  => $this->_privateConfig->default->longitude,
                    "bottom" => $this->_privateConfig->default->latitude,
                    "left"   => $this->_privateConfig->default->longitude);
        */
        if ($this->_request->datatype != "json" && $this->_request->var_dump == "true") {
            echo "extent view array" . EOL;
            var_dump($this->view->extent);
        }
        /*
           I don't know, why I've done this
           $this->view->defaultLat		= $this->view->extent['top'] - $this->view->extent['bottom'];
           $this->view->defaultLong	= $this->view->extent['right'] - $this->view->extent['left'];
         */
    }

    /**
     * Retrieves map markers for the current resource and sends a JSON array with markers
     */
    public function markerAction()
    {
        //		require_once $this->_componentRoot.'classes/MarkerManager.php';
        require_once $this->_componentRoot.'classes/Marker.php';
        require_once $this->_componentRoot.'classes/Clusterer.php';
        require_once $this->_componentRoot.'classes/GeoCoder.php';
        require_once 'OntoWiki/Model/TitleHelper.php';

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
                    "left"   => 180 );
        }

        $markers = array();

        $result = $this->getMarkerResult( $viewArea );

        if($result) {
            $titleHelper = new OntoWiki_Model_TitleHelper($this->model);
            foreach ($result as $row) {
                $uri = isset($row['instance']) ? $row['instance'] : $this->resource;
                $titleHelper->addResource($uri);
            }
            foreach ($result as $row) {

                $url = new OntoWiki_Url(array('route' => 'properties'), array('r'));
                $uri = isset($row['instance']) ? $row['instance'] : $this->resource;
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

        //	var_dump($markers);

        if ($this->_request->datatype != "json" && $this->_request->var_dump == "true") {
            print_r($viewArea);
            var_dump($markers);
        } else {
            // send JSON repsonse
            // $this->_response->setHeader('Content-Type', 'application/json', true);
            $this->_response->setBody(json_encode($markers));
        }
    }

    public function configAction()
    {
        // this function gets and sends some persistent configuration values
    }

    public function __call($method, $args)
    {
        $this->_forward('view');
    }

    private function getMarkerResult( $viewArea = false, $limit = false, $order = false ) {

        // build the query to get all marker resources
        $query = new Erfurt_Sparql_SimpleQuery( );

        $indirect = $this->getIndirectProperties ( );

        // if indirect properties are found:
        if (count ($indirect) > 0) {
            $query->setProloguePart( 'SELECT ?instance ?label ?lat ?long ?indirectLat ?indirectLong' );
            if (isset($this->_request->selectedIndirect)) {
                $selectedIndirect = $indirect[$this->_request->selectedIndirect];
            } else {
                $selectedIndirect = $indirect[0];
            }
        } else {
            $query->setProloguePart( 'SELECT ?instance ?label ?lat ?long' );
        }
        //		$query->addFrom("<" . $this->model->getModelIri() . ">");

        $where = "WHERE {" . EOL;

        // get the transitive closure of subclasses of the selected resource
        $types		= array_keys($this->store->getTransitiveClosure($this->model->getModelIri(), EF_RDFS_SUBCLASSOF, array($this->resource), true));
        $typesWhere	= " UNION { ?instance a <" . implode( ">. } UNION { ?instance a <", $types ) . ">. } ";

        if ($this->_request->filter != "false") {
            $filter = true;
        } else {
            $filter = false;
        }

        // the filter doesn't work
        $filter = false;

        if ($viewArea && $filter === true) {
            if ($viewArea['left'] > $viewArea['right']) {
                $tmp               = $viewArea['left'];
                $viewArea['left']  = $viewArea['right'];
                $viewArea['right'] = $tmp;
            }

            $viewAreaFilter = "FILTER (" . EOL;
            $viewAreaFilter.= "					?lat  < " . $viewArea['top'] . EOL;
            $viewAreaFilter.= "					&& ?lat  > " . $viewArea['bottom'] . EOL;
            $viewAreaFilter.= "					&& ?long < " . $viewArea['right'] . EOL;
            $viewAreaFilter.= "					&& ?long > " . $viewArea['left'] . EOL;
            $viewAreaFilter.= "				)" . EOL;

            $viewAreaIndirectFilter = "				FILTER (" . EOL;
            $viewAreaIndirectFilter.= "					?indirectLat  < " . $viewArea['top'] . EOL;
            $viewAreaIndirectFilter.= "					&& ?indirectLat  > " . $viewArea['bottom'] . EOL;
            $viewAreaIndirectFilter.= "					&& ?indirectLong < " . $viewArea['right'] . EOL;
            $viewAreaIndirectFilter.= "					&& ?indirectLong > " . $viewArea['left'] . EOL;
            $viewAreaIndirectFilter.= "				)" . EOL;
        } else {
            $viewAreaFilter = "";
            $viewAreaIndirectFilter = "";
        }

        $latitude	= $this->_privateConfig->property->latitude->toArray();
        $longitude	= $this->_privateConfig->property->longitude->toArray();

        if( $this->_request->datatype != "json" && $this->_request->var_dump == "true" ) {
            var_dump( $latitude );
            var_dump( $longitude );
        }

        $skelWhere = "		{"																	 . EOL;
        $skelWhere.= "			{ ?instance a ?b. FILTER (sameTerm (?instance, <%s>))} %s" 		 . EOL;
        $skelWhere.= "			OPTIONAL {"														 . EOL;
        $skelWhere.= "				?instance <%s> ?label."										 . EOL;
        $skelWhere.= "			} OPTIONAL {"													 . EOL;
        $skelWhere.= "				?instance <%s> ?lat;"										 . EOL;
        $skelWhere.= "				          <%s> ?long."										 . EOL;
        $skelWhere.= "				%s"															 . EOL;
        $skelWhere.= "				%s"															 . EOL;
        $skelWhere.= "			}"																 . EOL;
        $skelWhere.= "		 }"																	 . EOL;

        for ($i = 0; $i < count($latitude); $i++) {
            $lat  = $latitude[$i];
            $long = $longitude[$i];
            if ($i != 0) {
                $where.= " UNION ";
            }

            if (!empty($selectedIndirect)) {
                $indirectWhere = "} OPTIONAL {" . EOL;
                $indirectWhere.= "				?instance <" . $selectedIndirect . "> ?place." . EOL;
                $indirectWhere.= "				?place <" . $lat . "> ?indirectLat;" . EOL;
                $indirectWhere.= "				       <" . $long . "> ?indirectLong." . EOL;
                $indirectWhere.= $viewAreaIndirectFilter;
            } else {
                $indirectWhere = "";
            }

            $where.= sprintf($skelWhere, $this->resource, $typesWhere, EF_RDFS_LABEL, $lat, $long, $viewAreaFilter, $indirectWhere, $viewAreaIndirectFilter);
        }

        $where.= "}";

        $query->setWherePart( $where );

        if ($limit) {
            $query->setLimit( $limit );
        }

        if ($order) {
            $query->setOrderClause( $order );
        }

        if ($this->_request->datatype != "json" && $this->_request->var_dump == "true") {
            var_dump($this->model->getModelIri());
            var_dump((string) $query);
        }

        $result = $this->model->sparqlQuery($query);

        if ($this->_request->datatype != "json" && $this->_request->var_dump == "true") {
            var_dump($result);
        }
        return $result;
    }

    private function getIndirectProperties ()
    {
        if ($this->indirectProperties === null) {
            $latitude	= $this->_privateConfig->property->latitude->toArray();
            $longitude	= $this->_privateConfig->property->longitude->toArray();

            $query = new Erfurt_Sparql_SimpleQuery();

            $query->setProloguePart('SELECT DISTINCT ?p');

            $where = "WHERE" . EOL;
            $where.= "{" . EOL;

            $skelWhere = "{" . EOL;
            $skelWhere.= "	?s ?p ?o." . EOL;
            $skelWhere.= "	?o <%s> ?lat;" . EOL;
            $skelWhere.= "		<%s> ?long." . EOL;
            $skelWhere.= "}" . EOL;

            for ($i = 0; $i < count($latitude); $i++) {
                $lat = $latitude[$i];
                $long = $longitude[$i];
                if( $i != 0 ) $where.= " UNION ";
                $where.= sprintf($skelWhere, $lat, $long);
            }

            $where.= "}" . EOL;

            $query->setWherePart($where);

            $result = $this->model->sparqlQuery($query);

            $return = array();

            foreach ($result as $arr) {
                $return[] = $arr["p"];
            }

            if( $this->_request->datatype != "json" && $this->_request->var_dump == "true") {
                var_dump($return);
            }
            $this->indirectProperties = $return;
        } else {
            $return = $this->indirectProperties;
        }
        return $return;
    }

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

        $indirect = $this->getIndirectProperties ( );

        // if indirect properties are found:
        if (count($indirect) > 0) {
            if (isset($this->_request->selectedIndirect)) {
                $selectedIndirect = $indirect[$this->_request->selectedIndirect];
            } else {
                $selectedIndirect = $indirect[0];
            }
            $indirectTopQuery    = new Erfurt_Sparql_SimpleQuery( );
            $indirectRightQuery  = new Erfurt_Sparql_SimpleQuery( );
            $indirectBottomQuery = new Erfurt_Sparql_SimpleQuery( );
            $indirectLeftQuery   = new Erfurt_Sparql_SimpleQuery( );
            $indirectTopQuery->setProloguePart( 'SELECT ?instance ?lat ?long' );
            $indirectRightQuery->setProloguePart( 'SELECT ?instance ?lat ?long' );
            $indirectBottomQuery->setProloguePart( 'SELECT ?instance ?lat ?long' );
            $indirectLeftQuery->setProloguePart( 'SELECT ?instance ?lat ?long' );
        }

        $where         = "WHERE {" . EOL;
        $indirectWhere = "WHERE {" . EOL;

        // get the transitive closure of subclasses of the selected resource
        $types		= array_keys($this->store->getTransitiveClosure($this->model->getModelIri(), EF_RDFS_SUBCLASSOF, array($this->resource), true));
        $typesWhere	= " UNION { ?instance a <" . implode( ">. } UNION { ?instance a <", $types ) . ">. } ";

        $latitude	= $this->_privateConfig->property->latitude->toArray();
        $longitude	= $this->_privateConfig->property->longitude->toArray();

        if( $this->_request->datatype != "json" && $this->_request->var_dump == "true" ) {
            var_dump( $latitude );
            var_dump( $longitude );
        }

        $skelWhere = "		{"																	 . EOL;
        $skelWhere.= "			{ ?instance a ?b. FILTER (sameTerm (?instance, <%s>))} %s" 		 . EOL;
        $skelWhere.= "			?instance <%s> ?lat;"									 . EOL;
        $skelWhere.= "			          <%s> ?long."									 . EOL;
        $skelWhere.= "		 }"																	 . EOL;

        if (isset($selectedIndirect)) {
            $indirectSkelWhere = "		{"																 . EOL;
            $indirectSkelWhere.= "			{ ?instance a ?b. FILTER (sameTerm (?instance, <%s>))} %s" 	 . EOL;
            $indirectSkelWhere.= "				?instance <" . $selectedIndirect . "> ?place."			 . EOL;
            $indirectSkelWhere.= "				?place <%s> ?lat;"										 . EOL;
            $indirectSkelWhere.= "				       <%s> ?long."										 . EOL;
            $indirectSkelWhere.= "		}"																 . EOL;
        }

        for ($i = 0; $i < count($latitude); $i++) {
            $lat = $latitude[$i];
            $long = $longitude[$i];
            if ($i != 0) $where.= " UNION ";
            $where .= sprintf($skelWhere, $this->resource, $typesWhere, $lat, $long);
            if (isset($selectedIndirect)) {
                $indirectWhere.= sprintf($indirectSkelWhere, $this->resource, $typesWhere, $lat, $long);
            }
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

        if(isset($selectedIndirect)) {
            $indirectWhere.= "}";

            $indirectTopQuery->setWherePart(  $indirectWhere );
            $indirectRightQuery->setWherePart( $indirectWhere );
            $indirectBottomQuery->setWherePart(  $indirectWhere );
            $indirectLeftQuery->setWherePart( $indirectWhere );

            $indirectTopQuery->setOrderClause("DESC(?lat)"); 
            $indirectRightQuery->setOrderClause("DESC(?long)");
            $indirectBottomQuery->setOrderClause("ASC(?lat)"); 
            $indirectLeftQuery->setOrderClause("ASC(?long)");

            $indirectTopQuery->setLimit( 1 );
            $indirectRightQuery->setLimit( 1 );
            $indirectBottomQuery->setLimit( 1 );
            $indirectLeftQuery->setLimit( 1 );

            $top = $this->model->sparqlQuery($indirectTopQuery);
            $right = $this->model->sparqlQuery($indirectRightQuery);
            $bottom = $this->model->sparqlQuery($indirectBottomQuery);
            $left = $this->model->sparqlQuery($indirectLeftQuery);

            if(isset($return['top']) AND isset($return['right']) AND isset($return['bottom']) AND $return['left']) {
                if(isset($top[0]) AND isset($right[0]) AND isset($bottom[0]) AND $left[0]) {
                    if($top[0]['lat'] > $return['top']) $return['top'] = $top[0]['lat'];
                    if($right[0]['long'] > $return['right']) $return['right'] = $right[0]['long'];
                    if($bottom[0]['lat'] < $return['bottom']) $return['bottom'] = $bottom[0]['lat'];
                    if($left[0]['long'] < $return['left']) $return['left'] = $left[0]['long'];
                }
            } else if(isset($top[0]) AND isset($right[0]) AND isset($bottom[0]) AND $left[0]) {
                $return = array(
                        "top"    => $top[0]['lat'],
                        "right"  => $right[0]['long'],
                        "bottom" => $bottom[0]['lat'],
                        "left"   => $left[0]['long']);
            }
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

        if ($this->_request->datatype != "json" && $this->_request->var_dump == "true") {
            echo "extent array" . EOL;
            var_dump($return);
        }

        return $return;

    }
}
