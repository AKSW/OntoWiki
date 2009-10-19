<?php
require_once 'Erfurt/Wrapper.php';

/**
 * Initial version of a wrapper for Musicbrainz.
 * Currently this is only a demo. It shows how a wrapper can handle data
 * itself, as well as quering the store and removing data.
 * 
 * @category   OntoWiki
 * @package    OntoWiki_extensions_wrapper
 * @author     Thomas König <koenig.thomas@googlemail.com>
 * @copyright  Copyright (c) 2009 {@link http://aksw.org aksw}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    ???
 */
class MusicbrainzWrapper extends Erfurt_Wrapper
{
	//URL
	//musicbrainz webpage
	//http://musicbrainz.org/artist/092ca127-2e07-4cbd-9cba-e412b4ddddd9.html  --> artist
	//http://musicbrainz.org/release/71c3797a-52e2-4cc7-9d2d-711be98c321d.html --> album
	//http://musicbrainz.org/track/213ca793-fd94-49ad-93ef-fcd0e8520033.html   --> track
	
	//rdf data (url to artist, artist name, date, type (single / group))
	//http://musicbrainz.org/mm-2.1/*/092ca127-2e07-4cbd-9cba-e412b4ddddd9 --> * = artist / album / track
	
	//more rdf data, add /number (1, 2, 3, 4) at the end
	//http://musicbrainz.org/mm-2.1/*/092ca127-2e07-4cbd-9cba-e412b4ddddd9/1 --> only url to artist
	//http://musicbrainz.org/mm-2.1/*/092ca127-2e07-4cbd-9cba-e412b4ddddd9/2 --> same as without number
	//http://musicbrainz.org/mm-2.1/*/092ca127-2e07-4cbd-9cba-e412b4ddddd9/3 --> artist, album: additional urls for albums/tracks; track: same as /2
	//http://musicbrainz.org/mm-2.1/*/092ca127-2e07-4cbd-9cba-e412b4ddddd9/4 --> artist, album: additional info for every album/track; track: shows artist
	
	//redirect to musicbrainz page
	//http://musicbrainz.org/*/092ca127-2e07-4cbd-9cba-e412b4ddddd9 --> * = artist / album / track
	
	//var
    protected $_cachedData   = array();  //cache
	protected $_pattern      = null;     //url pattern
	
    
	//---------------------------------------------------------------------------------------------
    public function getDescription()
    {
        return 'A simple wrapper for Musicbrainz.';
    }
    
	//---------------------------------------------------------------------------------------------
    public function getName()
    {
        return 'Musicbrainz';
    }
    
	//---------------------------------------------------------------------------------------------
    public function init($config)
    {
        parent::init($config);
        
		$this->_pattern = "/^http:\/\/musicbrainz.org\/(mm-2.1)?\/?(artist|track|release|album)\/([^.^\/]+).?(html|htm)?\/?\d?/";
    }
	
	//---------------------------------------------------------------------------------------------
	public function isHandled($uri, $graphUri)
    {
		if (preg_match($this->_pattern, $uri))
			return true;
		else
			return false;
    }
    
	//---------------------------------------------------------------------------------------------
	public function isAvailable($uri, $graphUri)
	{
		//check cache
		$id     = $this->_cache->makeId($this, 'isAvailable', array($uri, $graphUri));
        $result = $this->_cache->load($id);
        if ($result !== false)
		{
            if (!isset($this->_cachedData[$graphUri]))
			{
                $this->_cachedData[$graphUri] = array($uri => $result['data']);
            }
			else
			{
                $this->_cachedData[$graphUri][$uri] = $result['data'];
            }
            
            return $result['value'];
        }
		
		//prepare arrays for http-output
	    $retVal = false;
	    $data   = array();
		
		//check if uri is valid and then get http-output
		$match  = array();
	    if (preg_match($this->_pattern, $uri, $match))
		{
			//parts of the uri-pattern
			$complete_address           = $match[0] != null ? $match[0] : "";
			$mm21                       = $match[1] != null ? $match[1] : "";
			$artist_or_album_or_track   = $match[2] != null ? $match[2] : "";
			$mbid                       = $match[3] != null ? $match[3] : "";
			$html                       = $match[4] != null ? $match[4] : "";
			
			//build uri
			if ($artist_or_album_or_track == "release") $artist_or_album_or_track = "album";
			$url1 = 'http://musicbrainz.org/mm-2.1/' . $artist_or_album_or_track . '/' . $mbid;
		
			//setup http client
	        require_once 'Zend/Http/Client.php';
	        $client = new Zend_Http_Client($url1, array('maxredirects' => 5, 'timeout' => 30));
	            
			//send request
			$response = $client->request();

			//get response
			if ($response->getStatus() === 200)
			{
				$result = $response->getBody();
				$data['status'] = $result;
				$retVal = true;
			}

	        //Cache the retrieved data if possible.
			if (!isset($this->_cachedData[$graphUri]))
			{
	            $this->_cachedData[$graphUri] = array($uri => $data);
	        }
			else
			{
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
        if ($result !== false)
		{
            return $result;
        }
        
		//url was loaded before (and is available)
        if ($this->isAvailable($uri, $graphUri))
		{
			//read from cache
            $data = $this->_cachedData[$graphUri][$uri];
			$raw_data = $data['status'];
			
			//check if uri is valid
			$match  = array();
		    if (preg_match($this->_pattern, $uri, $match))
			{
				//parts of the uri-pattern
				$complete_address           = $match[0] != null ? $match[0] : "";
				$mm21                       = $match[1] != null ? $match[1] : "";
				$artist_or_album_or_track   = $match[2] != null ? $match[2] : "";
				$mbid                       = $match[3] != null ? $match[3] : "";
				$html                       = $match[4] != null ? $match[4] : "";
				
				//build uri: rdf data
				if ($artist_or_album_or_track == "release") $artist_or_album_or_track = "album";
				$url1 = 'http://musicbrainz.org/mm-2.1/' . $artist_or_album_or_track . '/' . $mbid;
				//$uri  = $url1;
				
				//build uri: webpage
				if ($artist_or_album_or_track == "album") $artist_or_album_or_track = "release";
				$musicbrainz_webpage = "http://musicbrainz.org/" . $artist_or_album_or_track . "/" . $mbid . ".html";
						
				//parsed a / an ...
				
				//=====================================================================================
				//... ARTIST
				if (strpos($url1, "artist") != null)
				{
					//use deep url for more information
					$url2 = $url1 . "/3";

					//setup http client
					require_once 'Zend/Http/Client.php';
					$client = new Zend_Http_Client($url2, array('maxredirects' => 5, 'timeout' => 30) );
					
					//send request
					$response = $client->request();

					//get response
					if ($response->getStatus() === 200)
					{
						$raw_data = $response->getBody();
					}
				
					//parse rdf data
				    preg_match('|<dc:title>([^"]+)</dc:title>|',            $raw_data,    $artist);
					preg_match('|<mm:beginDate>([^"]+)</mm:beginDate>|',    $raw_data,    $beginDate);
					preg_match('|<mm:artistType rdf:resource="([^"]+)"/>|', $raw_data,    $artistType);
					preg_match('|<mm:albumList>(.+)</mm:albumList>|s',      $raw_data,    $albumRdf);
						preg_match_all('|<rdf:li rdf:resource="(.+)"/>|',   $albumRdf[1], $albumUrls, PREG_PATTERN_ORDER);
					preg_match('|<dc:comment>(.+)</dc:comment>|',           $raw_data,    $comment);
					
					//create array
					$fullResult = array
					(
						'status_codes' => array(Erfurt_Wrapper::NO_MODIFICATIONS, Erfurt_Wrapper::RESULT_HAS_ADD),
						'status_description'  => 'Musicbrainz artist data found',
						'add' => array
						(
							$uri => array
							(
								//foaf:name (artist name)
								'http://xmlns.com/foaf/0.1/name'                  => array(array('value' => utf8_encode($artist[1]),                   'type' => 'literal')),
								//mo:musicbrainz (url)
								'http://purl.org/ontology/mo/musicbrainz'         => array(array('value' => $musicbrainz_webpage,                      'type' => 'literal'))
							)
						)
					);
					
					//rdf:type: MusicArtist | MusicGroup				
					//date:     foaf:birthday | mo:beginsatDateTime (when group was founded OR when single artist was born)
					if (strpos($artistType[1], "TypeGroup") != null)
					{
						$fullResult['add'][$uri]['http://www.w3.org/1999/02/22-rdf-syntax-ns#type'] = array(array('value' => 'http://purl.org/ontology/mo/MusicGroup', 'type' => 'uri'));
						$fullResult['add'][$uri]['http://purl.org/ontology/mo/beginsAtDateTime'] = array(array('value' => utf8_encode($beginDate[1]), 'type' => 'literal'));
					}
					elseif (strpos($artistType[1], "TypePerson") != null)
					{
						$fullResult['add'][$uri]['http://www.w3.org/1999/02/22-rdf-syntax-ns#type'] = array(array('value' => 'http://purl.org/ontology/mo/MusicArtist', 'type' => 'uri'));
						$fullResult['add'][$uri]['http://xmlns.com/foaf/0.1/birthday'] = array(array('value' => utf8_encode($beginDate[1]), 'type'  => 'literal'));
					}
					
					//foaf:made (album releases)
					if (count($albumUrls) >= 2)
					{
						if (count($albumUrls[1]) >= 1)
						{
							$fullResult['add'][$uri]['http://xmlns.com/foaf/0.1/made'] = array();
							foreach ($albumUrls[1] as $album)
							{
								$fullResult['add'][$uri]['http://xmlns.com/foaf/0.1/made'][] = array('value' => $album, 'type'  => 'uri');
							}
						}
					}

					//dc:description (comment to the artist | group)
					if (count($comment) >= 2)
					{
						$fullResult['add'][$uri]['http://purl.org/dc/elements/1.1/description'] = array(array('value' => utf8_encode($comment[1]), 'type'  => 'literal'));
					}
					
					//save cache and return result array
					$this->_cache->save($fullResult, $id);
			        return $fullResult;
				} //ARTIST
				
				//=====================================================================================
				//... ALBUM
				else if (strpos($url1, "album") != null)
				{
					//use deep url for more information
					$url2 = $url1 . "/4";

					//setup http client
					require_once 'Zend/Http/Client.php';
					$client = new Zend_Http_Client($url2, array('maxredirects' => 5, 'timeout' => 30) );
					
					//send request
					$response = $client->request();

					//get response
					if ($response->getStatus() === 200)
					{
						$raw_data = $response->getBody();
					}
				
					//parse rdf data
				    preg_match('|<dc:title>([^"]+)</dc:title>|',                                      $raw_data,      $album);
					preg_match('|<mm:releaseDateList>(.+)</mm:releaseDateList>|s',                    $raw_data,      $releaseRdf);
						preg_match_all('|<dc:date>(.+)</dc:date>|',                                   $releaseRdf[1], $releaseDate,    PREG_PATTERN_ORDER);
						preg_match_all('|<mm:country>(.+)</mm:country>|',                             $releaseRdf[1], $releaseCountry, PREG_PATTERN_ORDER);	
					preg_match('|<mm:trackList>(.+)</mm:trackList>|s',                                $raw_data,      $trackRdf);
						preg_match_all('|<rdf:li rdf:resource="(.+)"/>|',                             $trackRdf[1],   $trackUrls,      PREG_PATTERN_ORDER);
					preg_match_all('|<mm:Artist rdf:about="(.+)">[^<]+<dc:title>([^<]+)</dc:title>|', $raw_data,      $artist,         PREG_PATTERN_ORDER);
					preg_match('|<az:Asin>(.+)</az:Asin>|',                                           $raw_data,      $amazonAsin);
					
					//create array
					$fullResult = array
					(
						'status_codes' => array(Erfurt_Wrapper::NO_MODIFICATIONS, Erfurt_Wrapper::RESULT_HAS_ADD),
						'status_description'  => 'Musicbrainz album data found',
						'add' => array
						(
							$uri => array
							(
								//rdf:type (MusicalManifestation)
								'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' => array(array('value' => 'http://purl.org/ontology/mo/MusicalManifestation', 'type'  => 'uri')),
								//dc:title (Album name)
								'http://purl.org/dc/elements/1.1/title'           => array(array('value' => utf8_encode($album[1]),                             'type' => 'literal')),
								//mo:musicbrainz (url)
								'http://purl.org/ontology/mo/musicbrainz'         => array(array('value' => $musicbrainz_webpage,                               'type' => 'literal'))
							)
						)
					);
					
					//?? (releases)
					//looks like: [date] in [country], e.g. "2008-01-10 in GB"
					if (count($releaseDate) >= 2)
					{
						$fullResult['add'][$uri]['http://www.w3.org/2000/01/rdf-schema#comment'] = array();
						for ($i=0; $i < count($releaseDate[1]); $i++)
						{
							//release date and country
							$rel_date    = $releaseDate[1][$i];
							$rel_country = $releaseCountry[1][$i];
							
							//will be shown:
							//released 1997-11-28 in DE
							$text        = "released ";
							
							//release date may be 0 or "" -> don't write to database
							if (($rel_date != "0") && ($rel_date != ""))
							{
								$text .= $rel_date . " ";
							}
							
							//country may be 0 or "" -> don't write to database
							if (($rel_country != "0") && ($rel_country != ""))
							{
								$text .= "in $rel_country";
							}
							
							//add to array
							$fullResult['add'][$uri]['http://www.w3.org/2000/01/rdf-schema#comment'][] = array('value' => utf8_encode($text), 'type'  => 'literal');
						}
					}
					
					//mo:track (track uris)
					if (count($trackUrls) >= 2)
					{
						if (count($trackUrls[1]) >= 1)
						{
							$fullResult['add'][$uri]['http://purl.org/ontology/mo/track'] = array();
							foreach ($trackUrls[1] as $track)
							{
								$fullResult['add'][$uri]['http://purl.org/ontology/mo/track'][] = array('value' => $track, 'type'  => 'uri');
							}
						}
					}
					
					//foaf:maker (artist url)
					//artist[1][] --> urls
					//artist[3][] --> artist name
					if (count($artist) >= 2)
					{
						$fullResult['add'][$uri]['http://xmlns.com/foaf/0.1/maker'] = array();
						foreach ($artist[1] as $artist_url)
						{
							$fullResult['add'][$uri]['http://xmlns.com/foaf/0.1/maker'][] = array('value' => $artist_url, 'type'  => 'uri');
						}
					}
					
					//mo:amazon_asin (amazon asin)
					if (count($amazonAsin) >= 2)
					{
						$fullResult['add'][$uri]['http://purl.org/ontology/mo/amazon_asin'] = array(array('value' => utf8_encode($amazonAsin[1]), 'type'  => 'literal'));
					}
					
					//save cache and return result array
					$this->_cache->save($fullResult, $id);
			        return $fullResult;
				} //ALBUM
				
				//=====================================================================================
				//... TRACK
				else if (strpos($url1, "track") != null)
				{
					//use deep url for more information
					$url2 = $url1 . "/4";

					//setup http client
					require_once 'Zend/Http/Client.php';
					$client = new Zend_Http_Client($url2, array('maxredirects' => 5,'timeout' => 30) );
					
					//send request
					$response = $client->request();

					//get response
					if ($response->getStatus() === 200)
					{
						$raw_data = $response->getBody();
					}
				
					//parse rdf data
					preg_match_all('|<mm:Track rdf:about="(.+)">[^<]+<dc:title>([^<]+)</dc:title>|',  $raw_data, $trackinfo,  PREG_PATTERN_ORDER);
					preg_match_all('|<mm:Artist rdf:about="(.+)">[^<]+<dc:title>([^<]+)</dc:title>|', $raw_data, $artistinfo, PREG_PATTERN_ORDER);
					preg_match('|<mm:duration>([^"]+)</mm:duration>|',                                $raw_data, $duration);
					
					//create array
					$fullResult = array
					(
						'status_codes' => array(Erfurt_Wrapper::NO_MODIFICATIONS, Erfurt_Wrapper::RESULT_HAS_ADD),
						'status_description'  => 'Musicbrainz track data found',
						'add' => array
						(
							$uri => array
							(
								//rdf:type (Track)
								'http://www.w3.org/1999/02/22-rdf-syntax-ns#type' => array(array('value' => 'http://purl.org/ontology/mo/Track', 'type'  => 'uri')),
								//dc:title (track name)
								'http://purl.org/dc/elements/1.1/title'           => array(array('value' => utf8_encode($trackinfo[2][0]),       'type' => 'literal')),
								//mo:durationXSD (track duration)
								'http://purl.org/ontology/mo/durationXSD'         => array(array('value' => utf8_encode($duration[1]),           'type' => 'literal')),
								//mo:musicbrainz (url)
								'http://purl.org/ontology/mo/musicbrainz'         => array(array('value' => $musicbrainz_webpage,                'type' => 'literal')),
								//foaf:maker(artist name)
								'http://xmlns.com/foaf/0.1/maker'                 => array(array('value' => utf8_encode($artistinfo[2][0]),      'type' => 'literal'),
								                                                           array('value' => $artistinfo[1][0],                   'type' => 'uri')),
							)
						)
					);
					
					//save cache and return result array
					$this->_cache->save($fullResult, $id);
			        return $fullResult;
				} //TRACK
			} //pattern supported
		} //isAvailable in cache
	} //run()
} //class
