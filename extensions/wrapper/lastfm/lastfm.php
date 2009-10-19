<?php
require_once 'Erfurt/Wrapper.php';

/**
 * Initial version of a wrapper for Last FM.
 * 
 * @category   OntoWiki
 * @package    OntoWiki_extensions_wrapper
 * @author     Thomas Findling <thomas.findling@googlemail.com>
 * @copyright  Copyright (c) 2009 {@link http://aksw.org aksw}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    ???
 */
class LastfmWrapper extends Erfurt_Wrapper
{
    protected $_cachedData   = array();  //cache
    protected $_pattern      = null;     //standard url
    
	//---------------------------------------------------------------------------------------------
    public function getDescription() {
        return 'A simple wrapper for Last FM.';
    }  
	//---------------------------------------------------------------------------------------------
    public function getName() {
        return 'Last FM';
    }
	//---------------------------------------------------------------------------------------------
    public function init($config) {
        parent::init($config);
        $this->_pattern = "/^http:\/\/(www.)?(lastfm.de|lastfm.com|last.fm)\/music\/([^\/]+)\/?([^\/]+)?\/?([^\/]+)?/";
    }
	//---------------------------------------------------------------------------------------------
	public function isHandled($uri, $graphUri) {
        return preg_match($this->_pattern, $uri);
    }
	//---------------------------------------------------------------------------------------------
	public function isAvailable($uri, $graphUri) {
		//check cache
		$id     = $this->_cache->makeId($this, 'isAvailable', array($uri, $graphUri));
        $result = $this->_cache->load($id);
        if ($result !== false)
		{
            if (!isset($this->_cachedData[$graphUri])) {
                $this->_cachedData[$graphUri] = array($uri => $result['data']);
            }
			else {
                $this->_cachedData[$graphUri][$uri] = $result['data'];
            }
            return $result['value'];
        }
		
		//prepare arrays for http-output
	    $retVal = false;
	    $data   = array();
	    
		//check, if uri is valid and then get http-output
	    if (preg_match($this->_pattern, $uri, $match)) 
		{
			$apikey = $this->_config->api_key;
			if($apikey == "") exit("Last FM Wrapper: kein API-Key angegeben!");
			$url    = "http://ws.audioscrobbler.com/2.0/?api_key=" . $apikey . "&method=";
			
			switch (count($match)) {
				case 4: $url .= "artist.getinfo&artist=" . $match[3];break;
				case 5: $url .= "album.getinfo&artist="  . $match[3] . "&album=" . $match[4];break;
				case 6: $url .= "track.getinfo&artist="  . $match[3] . "&album=" . $match[4] . "&track=" . $match[5];break;
			};	

			//setup http client
			require_once 'Zend/Http/Client.php';
			$client = new Zend_Http_Client($url, array( 'maxredirects' => 5, 'timeout' => 30));
			
			//send request
			$response = $client->request();

			//get response
			if ($response->getStatus() === 200) {
				$result = $response->getBody();
				$data['status'] = $result;
				$retVal = true;
			}

	        //Cache the retrieved data if possible.
			if (!isset($this->_cachedData[$graphUri])) {
	            $this->_cachedData[$graphUri] = array($uri => $data);
	        }
			else {
	            $this->_cachedData[$graphUri][$uri] = $data;
	        }
	        
			//return data
	        $cacheVal = array('value' => $retVal, 'data' => $data);
	        $this->_cache->save($cacheVal, $id);
	        return $retVal;
	    }
		
	    return $retVal;    
	}
	
	//---------------------------------------------------------------------------------------------
	public function run($uri, $graphUri)
	{
		//load cache
        $id = $this->_cache->makeId($this, 'run', array($uri, $graphUri));
        $result = $this->_cache->load($id);
        if ($result !== false) {
            return $result;
        }
        
		//url was loaded before (and is available)
        if ($this->isAvailable($uri, $graphUri))
		{
			//read from cache
            $data = $this->_cachedData[$graphUri][$uri];
			
			preg_match($this->_pattern, $uri, $match);
			
			$raw_data = $data['status'];
			
			$taguri = $this->_config->tags;

			//=====================================================================================
			//... ARTIST
			if (count($match)==4)
			{
				$raw_data = $data['status'];
				preg_match('|<name>([^<]+)</name>|s',                 $raw_data, $name);
				//preg_match('|<mbid>([^<]+)</mbid>|s',                 $raw_data, $mbid);
				preg_match('|<url>([^<]+)</url>|s',                   $raw_data, $url);
				preg_match('|<image size="small">([^<]+)</image>|s',  $raw_data, $ismall);
				preg_match('|<image size="medium">([^<]+)</image>|s', $raw_data, $imedium);
				preg_match('|<image size="large">([^<]+)</image>|s',  $raw_data, $ilarge);
				
				//$musicbrainz = "http://musicbrainz.org/artist/" . $mbid[1];
				
				//preg_match('|<stats>(.*)</stats>|s', $raw_data, $stats);
				//	preg_match('|<listeners>([^>]*)</listeners>|s', $stats[1], $listeners);
				//	preg_match('|<playcount>([^>]*)</playcount>|s', $stats[1], $playcount);
						
				preg_match('|<similar>(.*)</similar>|s', $raw_data, $similar);
					preg_match_all('|<url>([^<]+)</url>|s', $similar[1], $similarartists);
				$allsimilar = array();
				foreach($similarartists[1] as $similarartist)
					array_push ($allsimilar, array ( 'value' => $similarartist, 'type' => 'uri'));
				
				preg_match('|<bio>(.*)</bio>|s', $raw_data, $bio);
					$bio[1] = str_replace('"',"'", $bio[1]);
					//preg_match('|<published>([^<]+)</published>|s',          $bio[1], $published);
					preg_match('|<summary><!\[CDATA\[(.*)\]\]></summary>|s', $bio[1], $summary);
					preg_match('|<content><!\[CDATA\[(.*)\]\]></content>|s', $bio[1], $content);
				
				$apikey = $this->_config->api_key;
				$url2   = "http://ws.audioscrobbler.com/2.0/?api_key=" . $apikey . "&method=";
				$url2   .= "artist.gettopalbums&artist=" . $name[1];
				
				//setup http client
				require_once 'Zend/Http/Client.php';
				$client = new Zend_Http_Client($url2, array( 'maxredirects' => 5, 'timeout' => 30));
				
				//send request
				$response = $client->request();

				//get response
				if ($response->getStatus() === 200) {
					$result_albums = $response->getBody();
				}
				
				preg_match_all('|<url>([^<]+)</url>\s+<artist>|s', $result_albums, $albumurls);
				$allalbums = array();
				foreach($albumurls[1] as $albumurl)
					array_push ($allalbums, array ( 'value' => $albumurl, 'type' => 'uri'));
				
					
				$fullResult = array (
					'status_codes' => array(Erfurt_Wrapper::NO_MODIFICATIONS, Erfurt_Wrapper::RESULT_HAS_ADD),
					'status_description' => "Last FM data found for artist ",
					'add' => array (
						$uri => array (
							'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' => array ( array ( 'value' => 'http://purl.org/ontology/mo/MusicArtist', 'type' => 'uri' ) ),
							'http://xmlns.com/foaf/0.1/made' => $allalbums,
							'http://purl.org/ontology/mo/similar_to' => $allsimilar,
							'http://xmlns.com/foaf/0.1/name' => array ( array ( 'value' => utf8_encode($name[1]), 'type' => 'literal' ) ),
							//'http://purl.org/ontology/mo/musicbrainz' => array ( array ( 'value' => $musicbrainz, 'type' => 'uri' ) ),
							'http://www.w3.org/2000/01/rdf-schema#seeAlso' => array ( array ( 'value' => $url[1],       'type' => 'uri' ) ),
							'http://purl.org/ontology/mo/image' => array ( array ( 'value' => $ismall[1], 'type' => 'uri' ) ,
																		   array ( 'value' => $imedium[1], 'type' => 'uri' ) ,
																		   array ( 'value' => $ilarge[1], 'type' => 'uri' ) ),
							//'#stats_listeners' => array ( array ( 'value' => utf8_encode($listeners[1]), 'type' => 'literal' ) ),
							//'#stats_playcount' => array ( array ( 'value' => utf8_encode($playcount[1]), 'type' => 'literal' ) ),
							//'#bio_published'   => array ( array ( 'value' => utf8_encode($published[1]), 'type' => 'literal' ) ),
							'http://www.w3.org/2000/01/rdf-schema#comment' => array ( array ( 'value' => utf8_encode($summary[1]),   'type' => 'literal' ) ),
							'http://purl.org/dc/elements/1.1/description' => array ( array ( 'value' => utf8_encode($content[1]),   'type' => 'literal' ) )
						)
					)
				); //$fullResult
			}
			//=====================================================================================
			//... ALBUM
			else if (count($match)==5)
			{
				preg_match('|<name>([^<]+)</name>|s',                      $raw_data, $name);
				preg_match('|<artist>([^<]+)</artist>|s',                  $raw_data, $artist);
				//preg_match('|<id>([^<]+)</id>|s',                          $raw_data, $ident);
				//preg_match('|<mbid>([^<]+)</mbid>|s',                      $raw_data, $mbid);
				preg_match('|<url>([^<]+)</url>|s',                        $raw_data, $url);
				//preg_match('|<releasedate>([^<]+)</releasedate>|s',        $raw_data, $releasedate);
				preg_match('|<image size="small">([^<]+)</image>|s',       $raw_data, $ismall);
				preg_match('|<image size="medium">([^<]+)</image>|s',      $raw_data, $imedium);
				preg_match('|<image size="large">([^<]+)</image>|s',       $raw_data, $ilarge);
				preg_match('|<image size="extralarge">([^<]+)</image>|s',  $raw_data, $iextra);
				//preg_match('|<listeners>([^>]*)</listeners>|s',            $raw_data, $listeners);
				//preg_match('|<playcount>([^>]*)</playcount>|s',            $raw_data, $playcount);
				
				//$musicbrainz = "http://musicbrainz.org/album/" . $mbid[1];
				$artisturl = "http://last.fm/music/" . $artist[1];
				
				preg_match('|<toptags>(.*)</toptags>|s', $raw_data, $toptags);
					preg_match_all('|<url>([^<]+)</url>|s', $toptags[1], $tags);
				$alltags = array();
				foreach($tags[1] as $tag)
					array_push ($alltags, array ( 'value' => $tag, 'type' => 'uri'));	
				
				//preg_match('|<wiki>(.*)</wiki>|s', $raw_data, $albumwiki);
				//	$albumwiki[1] = str_replace('"',"'", $albumwiki[1]);
				//	preg_match('|<published>([^<]+)</published>|s',          $albumwiki[1], $published);
				//	preg_match('|<summary><!\[CDATA\[(.*)\]\]></summary>|s', $albumwiki[1], $summary);
				//	preg_match('|<content><!\[CDATA\[(.*)\]\]></content>|s', $albumwiki[1], $content);	
			
				$fullResult = array (
					'status_codes' => array(Erfurt_Wrapper::NO_MODIFICATIONS, Erfurt_Wrapper::RESULT_HAS_ADD),
					'status_description' => "Last FM data found for album ",
					'add' => array (
						$uri => array (
							'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' => array ( array ( 'value' => 'http://purl.org/ontology/mo/MusicalManifestation', 'type' => 'uri' ) ),						
							$taguri => $alltags,
							'http://purl.org/dc/elements/1.1/title' => array ( array ( 'value' => utf8_encode($name[1]), 'type' => 'literal' ) ),
							'http://xmlns.com/foaf/0.1/maker' => array ( array ( 'value' => $artisturl, 'type' => 'uri' ) ),
							//'#ident' 		   => array ( array ( 'value' => utf8_encode($ident[1]),       'type' => 'literal' ) ),
							//'http://purl.org/ontology/mo/musicbrainz' => array ( array ( 'value' => $musicbrainz, 'type' => 'uri' ) ),
							'http://www.w3.org/2000/01/rdf-schema#seeAlso' => array ( array ( 'value' => $url[1], 'type' => 'uri' ) ),
							//'#releasedate'      => array ( array ( 'value' => utf8_encode($releasedate[1]), 'type' => 'literal' ) ),
							'http://purl.org/ontology/mo/image' => array ( array ( 'value' => $ismall[1],  'type' => 'uri' ),
																		   array ( 'value' => $imedium[1], 'type' => 'uri' ), 
																		   array ( 'value' => $ilarge[1],  'type' => 'uri' ), 
																		   array ( 'value' => $iextra[1],  'type' => 'uri' ) )
							//'#listeners'        => array ( array ( 'value' => utf8_encode($listeners[1]),   'type' => 'literal' ) ),
							//'#playcount'        => array ( array ( 'value' => utf8_encode($playcount[1]),   'type' => 'literal' ) ),
							//'#wiki_published'   => array ( array ( 'value' => utf8_encode($published[1]),   'type' => 'literal' ) ),
							//'#wiki_summary'     => array ( array ( 'value' => utf8_encode($summary[1]),     'type' => 'literal' ) ),
							//'http://purl.org/dc/elements/1.1/description' => array ( array ( 'value' => utf8_encode($content[1]),     'type' => 'literal' ) )
						)
					)
				); //$fullResult
			}
			//=====================================================================================
			//... TRACK
			else if (count($match)==6)
			{
				//preg_match('|<id>([^<]+)</id>|s',               $raw_data, $ident);
				preg_match('|<name>([^<]+)</name>|s',           $raw_data, $name);
				//preg_match('|<mbid>([^<]+)</mbid>|s',           $raw_data, $mbid);
				preg_match('|<url>([^<]+)</url>|s',             $raw_data, $url);
				preg_match('|<duration>([^<]+)</duration>|s',   $raw_data, $duration);
				//preg_match('|<listeners>([^>]*)</listeners>|s', $raw_data, $listeners);
				//preg_match('|<playcount>([^>]*)</playcount>|s', $raw_data, $playcount);
				
				//$musicbrainz = "http://musicbrainz.org/track/" . $mbid[1];
				
				preg_match('|<artist>(.*)</artist>|s', $raw_data, $trackartist);
					//preg_match('|<name>([^<]+)</name>|s', $trackartist[1], $artistname);
					//preg_match('|<mbid>([^<]+)</mbid>|s', $trackartist[1], $artistmbid);
					preg_match('|<url>([^<]+)</url>|s',   $trackartist[1], $artisturl);
				
				preg_match('|<album position="(.*)">\s|s', $raw_data, $albumpos);
				preg_match('|<album position="'.$albumpos[1].'">(.*)</album>|s', $raw_data, $trackalbum);
					//preg_match('|<artist>([^<]+)</artist>|s',             $trackalbum[1], $albumartist);
					//preg_match('|<title>([^<]+)</title>|s',               $trackalbum[1], $albumname);
					//preg_match('|<mbid>([^<]+)</mbid>|s',                 $trackalbum[1], $albummbid);
					preg_match('|<url>([^<]+)</url>|s',                   $trackalbum[1], $albumurl);
					preg_match('|<image size="small">([^<]+)</image>|s',  $trackalbum[1], $albumismall);
					preg_match('|<image size="medium">([^<]+)</image>|s', $trackalbum[1], $albumimedium);
					preg_match('|<image size="large">([^<]+)</image>|s',  $trackalbum[1], $albumilarge);
				
				preg_match('|<toptags>(.*)</toptags>|s', $raw_data, $toptags);
					preg_match_all('|<url>([^<]+)</url>|s', $toptags[1], $tags);
				$alltags = array();
				foreach($tags[1] as $tag)
					array_push ($alltags, array ( 'value' => $tag, 'type' => 'uri'));	
				
				//preg_match('|<wiki>(.*)</wiki>|s', $raw_data, $trackwiki);
				//	$trackwiki[1] = str_replace('"',"'", $trackwiki[1]);
				//	preg_match('|<published>([^<]+)</published>|s',          $trackwiki[1], $published);
				//	preg_match('|<summary><!\[CDATA\[(.*)\]\]></summary>|s', $trackwiki[1], $summary);
				//	preg_match('|<content><!\[CDATA\[(.*)\]\]></content>|s', $trackwiki[1], $content);	
			
				$fullResult = array (
					'status_codes' => array(Erfurt_Wrapper::NO_MODIFICATIONS, Erfurt_Wrapper::RESULT_HAS_ADD),
					'status_description' => "Last FM data found for track ",
					'add' => array (
						$uri => array (
							'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' => array ( array ( 'value' => 'http://purl.org/ontology/mo/Track', 'type' => 'uri' ) ),						
							$taguri => $alltags,
							//'#ident' 		     => array ( array ( 'value' => utf8_encode($ident[1]),       'type' => 'literal' ) ),
							'http://purl.org/dc/elements/1.1/title' => array ( array ( 'value' => utf8_encode($name[1]),        'type' => 'literal' ) ),
							//'http://purl.org/ontology/mo/musicbrainz' => array ( array ( 'value' => $musicbrainz, 'type' => 'uri' ) ),
							'http://www.w3.org/2000/01/rdf-schema#seeAlso' => array ( array ( 'value' => $url[1], 'type' => 'uri' ) ),
							'http://purl.org/ontology/mo/durationXSD' => array ( array ( 'value' => utf8_encode($duration[1]),    'type' => 'literal' ) ),
							//'#listeners'          => array ( array ( 'value' => utf8_encode($listeners[1]),   'type' => 'literal' ) ),
							//'#playcount'          => array ( array ( 'value' => utf8_encode($playcount[1]),   'type' => 'literal' ) ),
							//'#artist_name' 	     => array ( array ( 'value' => utf8_encode($artistname[1]),  'type' => 'literal' ) ),
							//'#artist_mbid' 	     => array ( array ( 'value' => utf8_encode($artistmbid[1]),  'type' => 'literal' ) ),
							'http://xmlns.com/foaf/0.1/maker' => array ( array ( 'value' => $artisturl[1], 'type' => 'uri' ) ),
							'http://purl.org/ontology/mo/track_number' => array ( array ( 'value' => utf8_encode($albumpos[1]),    'type' => 'literal' ) ),
							//'#album_artist'       => array ( array ( 'value' => utf8_encode($albumartist[1]), 'type' => 'literal' ) ),
							//'#album_title'        => array ( array ( 'value' => utf8_encode($albumname[1]),   'type' => 'literal' ) ),
							//'#album_mbid'         => array ( array ( 'value' => utf8_encode($albummbid[1]),   'type' => 'literal' ) ),
							//'#album' => array ( array ( 'value' => $albumurl[1], 'type' => 'uri' ) ),
							'http://purl.org/ontology/mo/image' => array ( array ( 'value' => $albumismall[1], 'type' => 'uri' ),
							                                               array ( 'value' => $albumimedium[1],'type' => 'uri' ),
																		   array ( 'value' => $albumilarge[1], 'type' => 'uri' ) )
							//'#wiki_published'     => array ( array ( 'value' => utf8_encode($published[1]),   'type' => 'literal' ) ),
							//'#wiki_summary'       => array ( array ( 'value' => utf8_encode($summary[1]),     'type' => 'literal' ) ),
							//'http://purl.org/dc/elements/1.1/description' => array ( array ( 'value' => utf8_encode($content[1]),     'type' => 'literal' ) )
						)
					)
				); //$fullResult
			}
				
			//save cache and return result array
			$this->_cache->save($fullResult, $id);
			return $fullResult;
		} //isAvailable in cache
	} //run()
} //class
