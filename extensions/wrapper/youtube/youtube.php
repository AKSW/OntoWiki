<?php
require_once 'Erfurt/Wrapper.php';

/**
 * @category   ontowiki
 * @package    extension
 * @subpackage wrapper
 * @author     Sven Windisch <sven@semantosoph.net>
 * @copyright  Copyright (c) 2009 {@link http://aksw.org aksw}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: youtube.php 2009-08-04$
 */
class YoutubeWrapper extends Erfurt_Wrapper
{
    // ------------------------------------------------------------------------
    // --- Protected properties -----------------------------------------------
    // ------------------------------------------------------------------------
    
	protected $_name = 'Youtube';
	protected $_videoPattern = '/youtube\.(com|de)\/watch\?v=[^\/]{11}/';
    
    // ------------------------------------------------------------------------
    // --- Public methods -----------------------------------------------------
    // ------------------------------------------------------------------------
    
    public function init($config)
	{
        parent::init($config);
    }
	
    public function getDescription()
    {
        return 'This wrapper checks for data on YouTube for a given URI.';
    }
    
    public function getName()
    {
        return $this->_name;
    }
    
    public function isHandled($uri, $graphUri)
    {
		
    	$isHandled = false;
		$uri = urldecode($uri);
		
		if (preg_match($this->_videoPattern, $uri)) {
			
			// $uri is the URI of the subject. If it matches with the $_videoPattern, the subject is most likely a youtube video.
			$isHandled = true;
			
		}
		return $isHandled;
    }    


    public function isAvailable($uri, $graphUri) {
    	
        $retVal = false;
        $data = array();

		$uri = urldecode($uri);
        
        // Check whether there is a cache hit...
        $id = $this->_cache->makeId($this, 'isAvailable', array($uri, $graphUri));
  
		if ($result = $this->_cache->test($id)) {
			return true;
        }
        
		require_once 'Zend/Loader.php';
		Zend_Loader::loadClass('Zend_Gdata_YouTube');
		$yt = new Zend_Gdata_YouTube();

		$videoUri = explode('=', $uri);
		$videoId = $videoUri[1];

		$videoEntry = $yt->getVideoEntry($videoId);
		
		if (isset($videoEntry)) {
			$data = array();
			
			// See http://code.google.com/intl/de/apis/youtube/2.0/developers_guide_php.html#Video_Entry_Contents
			// for more possible data entries
			$data['videoId'] = $videoEntry->getVideoId();
			$data['title'] = $videoEntry->getVideoTitle();
			$data['description'] = $videoEntry->getVideoDescription();
			$data['viewCount'] = $videoEntry->getVideoViewCount();
	        $this->_cache->save($data, $id);
			$retVal = true;
		}
        
        return $retVal;

    }
    
    public function run($uri, $graphUri)
    {

        $uri = urldecode($uri);
        
		$id = $this->_cache->makeId($this, 'isAvailable', array($uri, $graphUri));
        $data = $this->_cache->load($id);

		if (isset($data)) {
			
			$result = array();
			$result[$uri] = array();

                        $result[$uri][EF_RDF_TYPE] = array();
	                    $result[$uri][EF_RDF_TYPE][] = array(
	                        'type'  => 'uri',
	                        'value' => 'http://rdfs.org/sioc/ns#Item'
	                    );
			
			if (isset($data['videoId'])) {
				$result[$uri]['http://purl.org/dc/elements/1.1/identifier'] = array();
	                    $result[$uri]['http://purl.org/dc/elements/1.1/identifier'][] = array(
	                        'type'  => 'literal',
	                        'value' => $data['videoId']
	                    );
			}

			if (isset($data['title'])) {
				$result[$uri]['http://purl.org/dc/elements/1.1/title'] = array();
	                    $result[$uri]['http://purl.org/dc/elements/1.1/title'][] = array(
	                        'type'  => 'literal',
	                        'value' => $data['title']
	                    );
			}
		
			if (isset($data['description'])) {
				$result[$uri]['http://purl.org/dc/elements/1.1/description'] = array();
	                    $result[$uri]['http://purl.org/dc/elements/1.1/description'][] = array(
	                        'type'  => 'literal',
	                        'value' => $data['description']
	                    );
			}
		
			if (isset($data['viewCount'])) {
				$result[$uri]['http://rdfs.org/sioc/ns#num_views'] = array();
	                    $result[$uri]['http://rdfs.org/sioc/ns#num_views'][] = array(
	                        'type'  => 'literal',
	                        'value' => $data['viewCount']
	                    );
			}
		
			
	        $fullResult = array();
	        $fullResult['status_codes'] = array(
	            Erfurt_Wrapper::NO_MODIFICATIONS, 
	            Erfurt_Wrapper::RESULT_HAS_ADD
	        );
	        $fullResult['status_description'] = "Data found for URI $uri";
	        $fullResult['add'] = $result;

		} else {

	        $fullResult = array();
	        $fullResult['status_codes'] = array(
	            Erfurt_Wrapper::NO_MODIFICATIONS
	        );
	        $fullResult['status_description'] = "No data available for URI $uri";

		}
        
        return $fullResult;
    }
   }
