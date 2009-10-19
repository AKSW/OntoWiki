<?php
require_once 'Erfurt/Wrapper.php';

/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_wrapper
 */
class GeocoderWrapper extends Erfurt_Wrapper
{
	//protected $_cachedData = array();
	var $location;
	
    public function getDescription()
    {
        return 'GeoCoder-Wrapper: Ermittelt Koordinaten zu Ortsangaben';
    }

    public function getName()
    {
        return 'GeoCoder';
    }

    public function isHandled($uri, $graphUri)
    {		
		$id = $this->_cache->makeId($this, 'isHandled', array($uri, $graphUri));
		$cache = $this->_cache->load($id);
		if($cache !== false)
		{
			$this->location = $cache;
			return true;
		}
		
		$this->sGetProperty($uri, $graphUri);
		
		/*Caching*/
		$id = $this->_cache->makeId($this, 'isHandled', array($uri, $graphUri));
		$this->_cache->save($this->location, $id);
		 
		return true;
    }

    public function isAvailable($uri, $graphUri)
    {
		$id = $this->_cache->makeId($this, 'isHandled', array($uri, $graphUri));
		$cache = $this->_cache->load($id);
		if($cache !== false)
		{
			$this->location = $cache;
		} else {
			$this->sGetProperty($uri, $graphUri);
		}
		
		$wgs_coords = $this->placeToLL($this->location);
		
		// Did the Geocoding service return something?; (0,0) is in the middle of the ocean so this is never a valid result
		if(($wgs_coords[0]==0 || $wgs_coords[0]=='') && ($wgs_coords[1]=='' || $wgs_coords[1]==0))
			return false;
		else
			return true;
			
		/*Caching*/
		$id = $this->_cache->makeId($this, 'isAvailable', array($uri, $graphUri));
		$this->_cache->save($wgs_coords, $id);
		
		
		
		
    }

    public function run($uri, $graphUri)
    {
		$id = $this->_cache->makeId($this, 'isAvailable', array($uri, $graphUri));
		$cache = $this->_cache->load($id);
		if($cache !== false)
		{
			$wgs_coords = $cache;
		} else {
			$this->sGetProperty($uri, $graphUri);
			$wgs_coords = $this->placeToLL($this->location);
		}
		// Weil Caching nicht mehr funktioniert
		$this->sGetProperty($uri, $graphUri);
		$wgs_coords = $this->placeToLL($this->location);
		
		$lat  = $wgs_coords[0];
		$long = $wgs_coords[1];
		
		$add = array();
		$add[$uri][$this->_config->lat_property][0]['value']  = $lat;
		$add[$uri][$this->_config->lat_property][0]['type']   = 'literal';
		$add[$uri][$this->_config->long_property][0]['value'] = $long;
		$add[$uri][$this->_config->long_property][0]['type']  = 'literal';
		
		$fullresult = array();
		$fullresult['status_codes'] = array(Erfurt_Wrapper::NO_MODIFICATIONS, Erfurt_Wrapper::RESULT_HAS_ADD, Erfurt_Wrapper::RESULT_HAS_ADDED_COUNT);
		$fullresult['status_desc'] = 'Location found.';
		$fullresult['added_count'] = 2;
		$fullresult['add'] = $add;
		
		return $fullresult;
    }
	
	public function placeToLL($place)
	{
		if('osm' == $this->_config->service)
		{
			return $this->placeToLLOSM($place);
		}
		if('gn' == $this->_config->service)
		{
			return $this->placeToLLGN($place);
		} 
		else  // statt if('g' == $this->_config->service) => Google ist Fallback
		{
			$data = $this->placeToLLG($place);
			$return =  array();
			$return[0] = $data[2];
			$return[1] = $data[3];
			return $return;
		}
	}
	
	/** GeoCoding via Google Maps API*/
	public function placeToLLG($place)
	{
		$key = $this->_config->google_maps_api_key;
		
		$q = urlencode($place);
		//$sensor = "false";
		//$output = "csv";
		$url="http://maps.google.com/maps/geo?q=".$q."&output=csv&sensor=&key=".$key;
		
		$ch = curl_init();		
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER,0);
			curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			$response = curl_exec($ch);
		curl_close($ch);
		
		$data =  explode(',',$response);
		return $data;
	}
	
	
	/** GeoCoding via NamFinder (OpenStreetMap)*/
	function placeToLLOSM($place)
	{
		$q = urlencode($place);
		
		$url="http://gazetteer.openstreetmap.org/namefinder/search.xml?find=".$q."&max=1&any=0";
		
		$ch = curl_init();		
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER,0);
			curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			$response = curl_exec($ch);
		curl_close($ch);
		
		ereg( "lat='(-?[0-9]+\.*[0-9]*)'", $response, $matches);
		$data[0] = $matches[1];
		
		ereg( "lon='(-?[0-9]+\.*[0-9]*)'", $response, $matches);
		$data[1] = $matches[1];
		
		return $data;
	}
	
	
	/** GeoCoding via GeoNames*/
	function placeToLLGN($place)
	{	
		$q = urlencode($place);
		
		$url="http://ws.geonames.org/search?q=".$q."&maxRows=1&isNameRequired=1&style=short";
		echo $url;
		
		$ch = curl_init();		
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER,0);
			curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

			$response = curl_exec($ch);
		curl_close($ch);
		
		
		$huh = ereg( "<lat>(.*)</lat>", $response, $matches);
		$data[0] = $matches[1];
		
		$huh = ereg( "<lng>(.*)</lng>", $response, $matches);
		$data[1] = $matches[1];
		
		return $data;
	}

	
	/** Hat das Subjekt eine passende Property (d.h. in wrapper.ini spzifizierte)?
	 * @return true gdw passende Property gefunden, entsprechendes Objekt wird nach $this->location
	 * gechrieben. */
	public function sGetProperty($uri, $graphUri)
	{ 
		// sparql zum schauen ob das Subjekt verwertbare Prädikate hat
		require_once 'Erfurt/Sparql/SimpleQuery.php';
		$query = new Erfurt_Sparql_SimpleQuery();
		        
		$query->setProloguePart('PREFIX swrc: <http://swrc.ontoware.org/ontology#>  SELECT DISTINCT ?o');
		$query->addFrom($graphUri); // PREFIX conferences: <http://3ba.se/conferences/>
		
		
		$props_array = array();
		$props_array = $this->_config->loc_props->toArray();
		$props_count = count($props_array);
		$wherePart = "WHERE {";
		for($i=0; $i < $props_count; $i++)
		{
			if($i != 0) $wherePart = $wherePart."UNION";
			$value = $props_array[$i];
			$wherePart = $wherePart."{ <$uri> <$value> ?o .}";
			
		}
		$wherePart = $wherePart."}";
			
		$query->setWherePart($wherePart);
			
		$store = Erfurt_App::getInstance()->getStore();
		$result = $store->sparqlQuery($query);
		
		if (count($result) == 0) {
			return false;
		}
		
		$row = current($result);
		$this->location = $row['o'];
		
		return $row['o'];
	}
}