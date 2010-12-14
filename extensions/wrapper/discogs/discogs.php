<?php

require_once OntoWiki::getInstance()->extensionManager->getExtensionPath.'/MusicWrapper.php';
require_once 'DiscogsClient.php';

/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_wrapper
 */ 

// TODO: add comments
// TODO: assign track number to track URIs for unambiguousness
// TODO: how to check whether cache is up to date or not?   -> lifetime?
// TODO: handle content containing apostrophes              -> core problem
// TODO: handle URIs containing white space or colons       -> core problem

class DiscogsWrapper extends MusicWrapper
{  
  public function getDescription()
  {
    return 'A wrapper to retrieve Discogs data.';
  }
  
  public function getName()
  {
    return 'Discogs';
  }
  
  public function init($config)
  {
    parent::init($config);
    // we do not want resource URIs to begin with "www",
    // so let the HTTP Client do the redirecting
    $this->_pattern =
      "*^http://discogs.com/(artist/.+|release/[^/]+|label/.+)$*";
  }
  
  public function isAvailable($uri, $graphUri)
  {
    // (1) item is available when found in cache
    $cacheId  = $this->_cache->makeId($this, __METHOD__, array($uri, $graphUri));
    $cacheVal = $this->_cache->load($cacheId);
    
    if ($cacheVal) {
      // set cached data for parsing in run()
      $this->_setLocalCache($uri, $graphUri, $cacheVal['data']);
      return true;
    }

    // (2) item is available when uri is found
    $client = new DiscogsClient(
        $uri,
        $this->_config->api_key
    );

    $response = $client->request();
    if ($response->getStatus() !== 200) {
      return false;
    }
    $raw_data = $response->getBody();
    
    $this->_cache->save(
      array('value' => true, 'data' => $raw_data),
      $cacheId
    );
    
    // set cached data for parsing in run()
    $this->_setLocalCache($uri, $graphUri, $raw_data);
    return true;
  }
  
  public function run($uri, $graphUri)
  {
    // return parsed results if available in cache
    $cacheId  = $this->_cache->makeId($this, __METHOD__, array($uri, $graphUri));
    $cacheVal = $this->_cache->load($cacheId);

    if ($cacheVal) {
      return $cacheVal;
    }

    // otherwise get parsed data
    $client = new DiscogsClient(
        $uri,
        $this->_config->api_key
    );
    
    // actually this is available... only needed for loading cached data
    if ($this->isAvailable($uri, $graphUri)) {
        $data = $this->_cachedData[$graphUri][$uri];
    }

    $result = array();
    $result['status_codes'] = array();
    
    // TODO: check whether statements have been added or removed 
    $result['status_codes'][] = Erfurt_Wrapper::RESULT_HAS_ADD;
    $result['status_description'] = "Discogs data found for URI $uri";
    $result['add'] = $client->get($uri, $data);

    $this->_cache->save($result, $cacheId);
    
    return $result;
    
  }
  
}

?>