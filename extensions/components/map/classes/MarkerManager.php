<?php

require_once $this->_componentRoot.'classes/Marker.php';
require_once $this->_componentRoot.'classes/Clusterer.php';
require_once $this->_componentRoot.'classes/GeoCoder.php';

/**
 * MarkerManager-Class of the OW MapPlugin
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_map
 * @author OW MapPlugin-Team <mashup@comiles.eu>
 * @version 1.0.0
 * @package MapPlugin
 */
class MarkerManager {
	private $markers = array( );
	private $icons = array( );
	private $model;
	private $config; 

	/**
	 * Maybe we should put the createMarkers() and/or createEdges() function 
	 * into the constructor or call them from the constructor.
	 * @param $model	the URI to the actual selected model
	 * @param $config
	 * @param $erfurt	the erfurt model
	 */
	public function __construct( $model, $config, $erfurt ) {
		$this->model = $model;
		$this->config = $config;

		// get the marker & cluster icons from the MapPlugin knowledgebase
		// the places we look for them ( the order is important)
		/*
		$places[] = "http://ns.aksw.org/MapPlugin/";
		//      $places[] = "http://localhost/OntoWiki/Config/";
		$places[] = $this->model->getModelURI();
		foreach($places as $p){
			// get all icons we can find, we dont distinguish between instance and class
			$query = new Erfurt_Sparql_SimpleQuery();
			$query->setProloguePart('SELECT ?instance ?icon');
			$query->addFrom($p);
			$query->setWherePart('WHERE { ?instance <http://xmlns.com/foaf/0.1/depiction> ?icon }');
			$endpoint = new Erfurt_Sparql_Endpoint_Default($this->config);

			// 		$endpoint -> setQuery($query);
			// 		$endpoint -> addModel($p);
			// 		$endpoint -> setRenderer('Default');

			$rs = $store->sparqlQuery($query);

			foreach( $rs as $r){
				$this->icons[$r['?inst']->getURI()] = $r['?icon']->getLabel();
			}
		}
		*/

		$actionConfig = $erfurt->getAc()->getActionConfig('MapPlugin');
		$this->icons['default'] = $actionConfig['defaultIcon'];
		$this->icons['cluster'] = $actionConfig['clusterIcon'];
	}

	/**
	 * I don't know, if we need to desctruct something, maybe we should close 
	 * some datebase connections or sthlt.
	 */
	public function __destruct( ) {
	}

	/**
	 * Calculates which markers to return
	 * @param $viewArea representation of the piece of the world we are currently looking at the moment
	 * @return array of markers which are supposed to be displayed on the map
	 */
	public function getMarkers( $viewArea, $clusterOn = true, $clustGridCount = 3, $clustMaxMarkers = 2 ) {
		/**
		 * check if all 4 viewArea values are present, else set the viewArea to the bestViewArea
		 */
		if(!isset($viewArea[3])) {
			$viewArea = $this->getBestViewArea();
		}
		$top	= $viewArea[0];
		$right	= $viewArea[1];
		$bottom	= $viewArea[2];
		$left	= $viewArea[3];
		//if($top < $bottom ) { $tmp = $top; $top = $bottom; $bottom = $tmp; }
		//if($right < $left ) { $tmp = $right; $right = $left; $left = $tmp; }
		//we don't care about the date line
		//why don't we need this? (Natanael)
		$viewArea = array( "top" => $top, "right" => $right, "bottom" => $bottom, "left" => $left  );

		/**
		 * remove all Markers outside the viewArea from $this->markers
		 */
		$markersVisible = array();
		for($i = 0; $i < count($this->markers); $i++){
			if(
				$this->markers[$i]->getLat() < $viewArea['top'] AND
				$this->markers[$i]->getLat() > $viewArea['bottom'] AND
				(
					(
						$this->markers[$i]->getLon() < $viewArea['right'] AND
						$this->markers[$i]->getLon() > $viewArea['left']
					) OR
					(
						$viewArea['left'] > $viewArea['right'] AND
						(
							$this->markers[$i]->getLon() < $viewArea['right'] OR
							$this->markers[$i]->getLon() > $viewArea['left']
						)
					)
				)
			) {
				$markersVisible[] = &$this->markers[$i];
			}
		}

		/**
		 * get the default gridCount and maxMarkers from the config ontology for the Clusterer
		 */

		/**
		 * check if the cluster is switched on, if so create a cluster and cluster the markers
		 * else do nothing and return the markers
		 */
		if ($clusterOn) {
			/**
			 * Instantiate a new Clusterer object 
			 */
			$clusterer = new Clusterer( $clustGridCount, $clustMaxMarkers );
			$clusterer->setViewArea( $viewArea );
			$clusterer->setMarkers( $markersVisible );//$this->markers );
			$clusterer->ignite( );
			$markersVisible = $clusterer->getMarkers( );
		}
		return $markersVisible;
	}

	/**
	 * this function creates the markers from the ontology specified by the $uri
	 * @param $uri
	 * @param $filter
	 */
	public function createMarkers( $uri, $filter ) {
		// create a GeoCode object used to GeoCode Markers
		$geoCoder = new GeoCoder( $this->model );
		// stores every SPARQL-Query we want to make and the icon which belongs to the class
		$output = array(); 
		// stores all markers
		$marker_array = array(); 
		$filter_prop = key($filter);

		// only one filter used at the moment
		// TODO do this right
		// there is no filterfunction in OntoWiki 1 at the moment
		// 	if ($filter_prop) {
		// 	    if (strpos($filter_prop , ':')) {
		// 		$filter_qr = '. OPTIONAL{?inst '. $filter_prop .' ?filter}';
		// 	    } else {
		// 		$filter_qr = '. OPTIONAL{ ?inst ?uri ?filter .
		// 		    ?uri <http://www.w3.org/2000/01/rdf-schema#label> 
		// 		    "'. $filter_prop .'}"';
		// 	    }
		// 	}

		$uri_list = array();
		if ($uri != $this->model->getModelURI()) {
			// will give us only those instances which belong to $uri and 
			// contain lat. and long. display one instance
			// has $uri a superclass ?
			$inst_query = 'SELECT * WHERE { <'. $uri .'> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> ?super}';
			// has $uri a instances 
			$test_qr_inst = 'SELECT * WHERE { ?inst <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <'. $uri .'> }';
			// has $uri a subclass
			$test_qr_subclass = 'SELECT * WHERE { ?sub <http://www.w3.org/2000/01/rdf-schema#subClassOf> <'. $uri .'> }';
			$rs = $this->model->sparqlQuery($inst_query);
			$rs_inst = $this->model->sparqlQuery($test_qr_inst);
			$rs_sub = $this->model->sparqlQuery($test_qr_subclass);
			// is $uri a instance ?
			if (0 != count($rs) && 0 == count($rs_inst) && 0 == count($rs_sub)) { 
				// display one instance
				$inst_qr = 
					"SELECT * WHERE { 
						OPTIONAL{ <". $uri ."> <http://www.w3.org/2003/01/geo/wgs84_pos#long> ?long. }
							OPTIONAL{ <". $uri ."> <http://www.w3.org/2003/01/geo/wgs84_pos#lat> ?lat. }
							<". $uri ."> <http://www.w3.org/2000/01/rdf-schema#label> ?label; <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> ?class.}";
						$output[] = $inst_qr;

			} else {
				//create query for class + subclasses
				$uri_list[] = $uri; 
				// if the list still contains unchecked URIs -> will be 
				// checked for further subclasses
				for ( $i = 0; $i < sizeof($uri_list); $i++ ){
					$check_uri = $uri_list[$i];
					$sub_search = 'SELECT * WHERE { ?subclass <http://www.w3.org/2000/01/rdf-schema#subClassOf>  <'. $check_uri .'> }';
					$next_subclass = $this->model->sparqlQuery($sub_search);
					// add subclasses to the end of the list
					foreach ($next_subclass as $ns) {
						$uri_list[] = $ns['subclass']->getURI();
					}
				}

				// get all instances related to the URIs in the uri_list
				// (with the query)
				foreach ( $uri_list as $uri ) {
					$instances = '
						PREFIX swrc: <http://swrc.ontoware.org/ontology#>
						PREFIX wgs84_pos: <http://www.w3.org/2003/01/geo/wgs84_pos#>
						SELECT * WHERE { ?inst <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <'. $uri .'>.
						?inst <http://www.w3.org/2000/01/rdf-schema#label> ?label.
						?inst <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> ?class 
						OPTIONAL{ ?inst wgs84_pos:long ?long } OPTIONAL{ ?inst wgs84_pos:lat ?lat } '. $filter_qr .' }';
					$output[] = $instances;
				}
			}
		} else {// will give us all instances from the active model(knowledgbase)
			$instances =
				"SELECT * WHERE { ?inst <http://www.w3.org/2003/01/geo/wgs84_pos#long> ?long;
			<http://www.w3.org/2003/01/geo/wgs84_pos#lat> ?lat;
			<http://www.w3.org/2000/01/rdf-schema#label> ?label;
			<http://www.w3.org/1999/02/22-rdf-syntax-ns#type> ?class.}";
			$output[] = $instances;
		}
		foreach ($output as $o) {
			// a query foreach class we found, we know the icon which belongs
			// to the class
			$qr = $this->model->sparqlQuery($o);
			foreach ($qr as $f) {
				// TODO do this right
				/*if ($f['filter']) {
					if ($f['filter']->getLabel() != $filter[$filter_prop]) {
						continue;
					}
				}*/
				// we do this because the sparlq-query for one instance
				// dosn't have a "?inst" (no way of getting the URI)
				if($f['inst'] ){
					$uri = $f['inst']->getURI();
				} 
				$temp = new Marker($uri);
				$temp->setLabel($f['label']->getLabel());
				// are there any icons connected to the instance ?
				// if note use the default-marker (-> else )
				if($this->icons[$f['class']->getURI()] || $this->icons[$uri]) {
					// if we have a specific icon for the instance we use it 
					// otherwise we take the icon of the class(if there is one)
					if($this->icons[$uri]){
						$temp->setIcon($this->icons[$uri]);
					} else {
						$temp->setIcon($this->icons[$f['class']->getURI()]);
					}
				} else {
					// set the default marker
					$temp->setIcon($this->icons['default']);
				}
				if ($f['lat'] && $f['long']) {
					$temp->setLat($f['lat']->getLabel());
					$temp->setLon($f['long']->getLabel());
				} else {
					$result = $geoCoder->geoCode( &$temp );
					// $result is boolean indicating geoCode() was successfull
				}

				$marker_array[] = $temp;
			}
		}
		$this->markers = $marker_array;
	}

	/**
	 * Calculates the best view area for the given markers.
	 * @return array which keeps the values for top, right, bottom and left border (in this order)
	 */
	public function getBestViewArea( ) {
		if (count($this->markers) > 0) {

			// Calculation for longitude:

			$markersTmp = array();
			$markersSorted = array();

			// Put all markers in a new temporary array
			for($i = 0; $i < count($this->markers); $i++) {
				$markersTmp[] = &$this->markers[$i];
			}

			// Sort markers by longitude and store them in markersSorted
			$min = 0;
			$minIndex = 0;
			$k = 0;
			while( $k < count($this->markers)) {
				$min = 181;
				for($i = 0; $i < count($this->markers); $i++) {
					if (isset($markersTmp[$i])) {
						if ($markersTmp[$i]->getLon( ) < $min) {
							$min = $markersTmp[$i]->getLon( );
							$minIndex = $i;
						}
					}
				}
				$markersSorted[] = &$markersTmp[$minIndex];
				unset($markersTmp[$minIndex]);
				$k++;
			}

			// Find maximum difference in longitude between two adjacent markers
			$max = 0; $maxIndex = 0;
			for($i = 0; $i < count($markersSorted) - 1; $i++) {
				if ($markersSorted[$i+1]->getLon( ) - $markersSorted[$i]->getLon( ) > $max) {
					$max = $markersSorted[$i+1]->getLon( ) - $markersSorted[$i]->getLon( );
					$maxIndex = $i;
				}
			}
			// Don't forget the difference between the last and first marker (180? -> -180?!)
			if ($markersSorted[0]->getLon( ) + 360 - $markersSorted[count($markersSorted) - 1]->getLon( ) > $max) {
				$max = $markersSorted[0]->getLon( ) + 360 - $markersSorted[count($markersSorted) - 1]->getLon( );
				$maxIndex = count($markersSorted) - 1;
			}

			// assign left and right border, calculate the longitude center and difference
			if ($maxIndex == count($markersSorted) - 1) {
				$right = $markersSorted[$maxIndex]->getLon( );
				$left = $markersSorted[0]->getLon( );
				$centerLon = ($left + $right) / 2;
				$diffLon = $right - $left;
			} else {
				$right = $markersSorted[$maxIndex]->getLon( );
				$left = $markersSorted[$maxIndex+1]->getLon( );
				$centerLon = ($left + $right + 360) / 2;
				while( $centerLon > 180) {
					$centerLon -= 360;
				}
				$diffLon = $right + 360 - $left;
			}

/*
		echo "\nLeft: ".$left;
		echo "\nRight: ".$right;
		echo "\nMaximale Differenz Lon: ".$max." an Stelle ".$maxIndex.":";
		for($i = 0; $i < count($markersSorted); $i++) {
		echo "\nmarkersSorted[ ".$i."]: ".$markersSorted[$i]->getLon( );
		}
 */

			// Calculation for latitude

			// Find marker with the minimal latitude -> bottom
			$bottom = $this->markers[0]->getLat( );
			for($i = 1; $i < count(markers); $i++) {
				if ($this->markers[$i]->getLat( ) < $bottom) {
					$bottom = $this->markers[$i]->getLat( );
				}
			}

			// Find marker with the maximal latitude -> top
			$top = $this->markers[0]->getLat( );
			for($i = 1; $i < count($this->markers); $i++) {
				if ($this->markers[$i]->getLat( ) > $top) {
					$top = $this->markers[$i]->getLat( );
				}
			}

			// Calculate the latitude center and difference
			$centerLat = ($top + $bottom) / 2;
			$diffLat = $top - $bottom;
		} else {
			// No existing marker
			$centerLat = 0;
			$centerLon = 0;
			$diffLat = 360;
			$diffLon = 180;
		}

		// Create array to return all values
		$bestViewArea = array( $top, $right, $bottom, $left );

		// $arrayCenterDiff = array( $centerLat, $centerLon, $diffLat, $diffLon );
		// print_r( $arrayCenterDiff );
		return $bestViewArea;
	}    
}
?>
