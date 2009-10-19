<?php

require_once 'Zend/Http/Client.php';
require_once 'Erfurt/Wrapper/Exception.php';
require_once 'extensions/wrapper/iClient.php';
/**
 * @category   OntoWiki
 * @package    OntoWiki_extensions_wrapper
 */
class DiscogsClient extends Zend_Http_Client implements iClient
{
  
  private $_apiKey;
  
  public function __construct($uri, $api_key)
  {
    
    if (!$api_key) {
      throw new Erfurt_Wrapper_Exception('No API Key specified!');
    }
    
    $this->_apiKey = $api_key; 
    
    parent::__construct($uri);
  }
  
  public function setUri($uri)
  {
    parent::setUri(str_replace(' ', '+', $uri));
  }

  public function get($uri, $raw_data = null)
  {
    if (strpos($uri, 'artist')) {
      
      if ($raw_data) {
        return $this->parseArtist($uri, $raw_data);
      } else {
        return $this->getArtist($uri);
      }
      
    } elseif (strpos($uri, 'release')) {
      
      if ($raw_data) {
        return $this->parseRelease($uri, $raw_data);
      } else {
        return $this->getRelease($uri);
      }

    } elseif (strpos($uri, 'label')) {

      if ($raw_data) {
        return $this->parseLabel($uri, $raw_data);
      } else {
        return $this->getLabel($uri);
      }

    } else {
      $msg = 'Requested object type not available (' . $uri . ')!'; 
      throw new Erfurt_Wrapper_Exception($msg);
    }
  }
  
  public function request($method = null)
  {
    $this->resetParameters();
    $this->setParameterGet(
        array(
            'f' => 'xml',
            'api_key' => $this->_apiKey
        )
    );
    return parent::request($method);
  }
  
  public function getArtist($uri)
  {
    $this->setUri($uri);
    return $this->request();
  }
  
  public function getRelease($uri)
  {
    $this->setUri($uri);
    return $this->request();
  }
  
  public function getLabel($uri)
  {
    $this->setUri($uri);
    return $this->request();
  }
  
  public function getTrack($uri)
  {
  }
  
  // subfunction for parsing single artist (subclass of foaf:Agent)
  private function _parseMusicArtist($uri, $raw_data, $result)
  {
    // rdf:type
    $result[$uri]['http://www.w3.org/1999/02/22-rdf-syntax-ns#type'] = array();
    $result[$uri]['http://www.w3.org/1999/02/22-rdf-syntax-ns#type'][] = array(
      'type'  => 'uri',
      'value' => 'http://purl.org/ontology/mo/MusicArtist'
    );
    
    // foaf:name
    $result[$uri]['http://xmlns.com/foaf/0.1/name'] = array();
    $result[$uri]['http://xmlns.com/foaf/0.1/name'][]= array(
      'type'  => 'literal',
      'value' => str_replace(
                   array('%2B', '%2b', 'http://discogs.com/artist/'),
                   array(' ', ' ', ''),
                   $uri
                 )
    );
    
    // foaf:givenname
    // foaf:surname
    
    // mo:member_of
    $pattern = '|<groups>(.*)</groups>|s';
    preg_match($pattern, $raw_data, $match);
    
    if ($match) {
      $pattern = '|<name>([^>]*)</name>|s';
      preg_match_all($pattern, $match[1], $match);
      if ($match) {
        $result[$uri]['http://purl.org/ontology/mo/member_of'] = array();
        foreach ($match[1] as $name) {
          $result[$uri]['http://purl.org/ontology/mo/member_of'][]= array(
            'type'  => 'uri',
            'value' => 'http://discogs.com/artist/' . $name
          );
        }
      }
    }
    
    return $result;
  }
  
  // subfunction for parsing music group (subclass of MusicArtist)
  private function _parseMusicGroup($uri, $raw_data, $result)
  {
    // rdf:type
    $result[$uri]['http://www.w3.org/1999/02/22-rdf-syntax-ns#type'] = array();
    $result[$uri]['http://www.w3.org/1999/02/22-rdf-syntax-ns#type'][] = array(
      'type'  => 'uri',
      'value' => 'http://purl.org/ontology/mo/MusicGroup'
    );
    
    // foaf:name
    $result[$uri]['http://xmlns.com/foaf/0.1/name'] = array();
    $result[$uri]['http://xmlns.com/foaf/0.1/name'][]= array(
      'type'  => 'literal',
      'value' => str_replace(
                   array('%2B', '%2b', 'http://discogs.com/artist/'),
                   array(' ', ' ', ''),
                   $uri
                 )
    );
    
    // mo:member
    $pattern = '|<members>(.*)</members>|s';
    preg_match($pattern, $raw_data, $match);
    
    if ($match) {
      $pattern = '|<name>([^>]*)</name>|s';
      preg_match_all($pattern, $match[1], $match);
      if ($match) {
        $result[$uri]['http://purl.org/ontology/mo/member'] = array();
        foreach ($match[1] as $name) {
          $result[$uri]['http://purl.org/ontology/mo/member'][]= array(
            'type'  => 'uri',
            'value' => 'http://discogs.com/artist/' . $name
          );
        }
      }
    }
    
    return $result;    
  }
  
  public function parseArtist($uri, $raw_data)
  {
    $result[$uri] = array();

    // mo:discogs
    $result[$uri]['http://purl.org/ontology/mo/discogs'] = array();
    $result[$uri]['http://purl.org/ontology/mo/discogs'][]= array(
      'type'  => 'literal',
      'value' => str_replace('http://discogs', 'http://www.discogs', $uri)
    );
    
    // mo:myspace
    // foaf:weblog
    // foaf:homepage
    // foaf:holdsAccount
    $pattern = '|<url>([^<]+)</url>|s';
    preg_match_all($pattern, $raw_data, $match);
    
    if ($match) {
      $result[$uri]['http://xmlns.com/foaf/0.1/weblog'] = array();
      $result[$uri]['http://purl.org/ontology/mo/myspace'] = array();
      $result[$uri]['http://xmlns.com/foaf/0.1/holdsAccount'] = array();
      $result[$uri]['http://xmlns.com/foaf/0.1/homepage'] = array();
      foreach ($match[1] as $url) {
        if (strpos($url, 'blogspot')) {
          $result[$uri]['http://xmlns.com/foaf/0.1/weblog'][] = array(
            'type'  => 'literal',
            'value' => $url
          );
        } elseif (strpos($url, 'myspace')) {
          $result[$uri]['http://purl.org/ontology/mo/myspace'][] = array(
            'type'  => 'literal',
            'value' => $url
          );
        } elseif (strpos($url, 'twitter')) {
          $result[$uri]['http://xmlns.com/foaf/0.1/holdsAccount'][] = array(
            'type'  => 'uri',
            'value' => $url
          );
        } else {
          $result[$uri]['http://xmlns.com/foaf/0.1/homepage'][] = array(
            'type'  => 'literal',
            'value' => $url
          );
        }
      }
    }

    // MusicGroup or MusicArtist?      
    $pattern = '|<members>(.*)</members>|s';
    preg_match($pattern, $raw_data, $match);
    
    if ($match) {
      $result = $this->_parseMusicGroup($uri, $raw_data, $result);
    } else {
      $result = $this->_parseMusicArtist($uri, $raw_data, $result);
    }
    
    // mo:made
    $pattern = '|<release id="([^"]+)"|s';
    preg_match_all($pattern, $raw_data, $match);
    if ($match) {
      $result[$uri]['http://xmlns.com/foaf/0.1/made'] = array();
      foreach ($match[1] as $rel_id) {
        $result[$uri]['http://xmlns.com/foaf/0.1/made'][]= array(
          'type'  => 'uri',
          'value' => 'http://discogs.com/release/' . $rel_id
        );
      }
    }
    
    // mo:image
    $pattern = '|<image.+uri="([^"]+)"|';
    preg_match($pattern, $raw_data, $match);
    if ($match) {
      $result[$uri]['http://purl.org/ontology/mo/image'] = array();
      $result[$uri]['http://purl.org/ontology/mo/image'][] = array(
        'type'  => 'uri',
        'value' => $match[1]
      );
    }
    
    return $result;
  }
  
  public function parseRelease($uri, $raw_data)
  {
    $result[$uri] = array();

    // rdf:type
    $result[$uri]['http://www.w3.org/1999/02/22-rdf-syntax-ns#type'] = array();
    $result[$uri]['http://www.w3.org/1999/02/22-rdf-syntax-ns#type'][] = array(
      'type'  => 'uri',
      'value' => 'http://purl.org/ontology/mo/MusicalManifestation'
    );
    
    $va = false;
    
    $doc = new DOMDocument();
    $doc->loadXml($raw_data);
    $xpath = new DOMXPath($doc);
    
    // dc:title
    $nl = $xpath->query('/resp/release/title');
    $result[$uri]['http://purl.org/dc/elements/1.1/title'] = array();
    $result[$uri]['http://purl.org/dc/elements/1.1/title'][]= array(
      'type'  => 'literal',
      'value' => $nl->item(0)->nodeValue
    );

    // foaf:maker
    $nl = $xpath->query('/resp/release/artists/artist/name');
    foreach ($nl as $node) {
      if ($node->nodeValue != 'Various') {
        $result[$uri]['http://xmlns.com/foaf/0.1/maker'] = array();
        $result[$uri]['http://xmlns.com/foaf/0.1/maker'][]= array(
          'type'  => 'uri',
          'value' => 'http://discogs.com/artist/' . $node->nodeValue
        );
      } else {
        $va = true;
      }
    }
    
    // mo:catalogue_number
    $result[$uri]['http://purl.org/ontology/mo/catalogue_number'] = array();
    $nl = $xpath->query('/resp/release/labels/label/@catno');
    foreach ($nl as $node) {
      $result[$uri]['http://purl.org/ontology/mo/catalogue_number'][]= array(
        'type'  => 'literal',
        'value' => $node->nodeValue
      );
    }
    
    // mo:label
    $result[$uri]['http://purl.org/ontology/mo/label'] = array();
    $nl = $xpath->query('/resp/release/labels/label/@name');
    foreach ($nl as $node) {
      $result[$uri]['http://purl.org/ontology/mo/label'][]= array(
        'type'  => 'uri',
        'value' => 'http://discogs.com/label/' . $node->nodeValue
      );
    }
    
    // mo:release_type
    $result[$uri]['http://purl.org/ontology/mo/release_type'] = array();
    $nl = $xpath->query('/resp/release/formats/format/descriptions/description');
    foreach ($nl as $node) {
      $result[$uri]['http://purl.org/ontology/mo/release_type'][] = array(
        'type'  => 'literal',
        'value' => $node->nodeValue
      );  
    }
    
    // mo:image
    $result[$uri]['http://purl.org/ontology/mo/image'] = array();
    $nl = $xpath->query('/resp/release/images/image/@uri');
    foreach ($nl as $node) {
      $result[$uri]['http://purl.org/ontology/mo/image'][] = array(
        'type'  => 'uri',
        'value' => $node->nodeValue
      );
    }
    
    // mo:track
    $result[$uri]['http://purl.org/ontology/mo/track'] = array();
    $nl = $xpath->query('/resp/release/tracklist/track');
    
    foreach ($nl as $node) {
      $track_pos   = $xpath->query('position', $node)->item(0)->nodeValue;
      $track_title = $xpath->query('title', $node)->item(0)->nodeValue;
      $track_maker = $xpath->query('artists/artist/name', $node);
      
      $track_uri   = $uri . '/' . $track_title;
      
      $result[$uri]['http://purl.org/ontology/mo/track'][] = array(
        'type'  => 'uri',
        'value' => $track_uri
      );
      
      $result[$track_uri]['http://www.w3.org/1999/02/22-rdf-syntax-ns#type'] = array();
      $result[$track_uri]['http://www.w3.org/1999/02/22-rdf-syntax-ns#type'][] = array(
        'type'  => 'uri',
        'value' => 'http://purl.org/ontology/mo/Track'
      );
      
      $result[$track_uri]['http://purl.org/dc/elements/1.1/title'] = array();
      $result[$track_uri]['http://purl.org/dc/elements/1.1/title'][] = array(
        'type'  => 'literal',
        'value' => $track_title
      );
      
      $result[$track_uri]['http://purl.org/ontology/mo/track_number'] = array();
      $result[$track_uri]['http://purl.org/ontology/mo/track_number'][] = array(
        'type'  => 'literal',
        'value' => $track_pos
      );
      
      if ($va) {
        $result[$track_uri]['http://xmlns.com/foaf/0.1/maker'] = array();
        foreach ($track_maker as $artist) {
          $result[$track_uri]['http://xmlns.com/foaf/0.1/maker'][]= array(
            'type'  => 'uri',
            'value' => 'http://discogs.com/artist/' . $artist->nodeValue
          );
        }
      }
    }
    
    return $result;
  }
  
  public function parseLabel($uri, $raw_data)
  {

    $result[$uri] = array();
    
    // rdf:type
    $result[$uri]['http://www.w3.org/1999/02/22-rdf-syntax-ns#type'] = array();
    $result[$uri]['http://www.w3.org/1999/02/22-rdf-syntax-ns#type'][] = array(
      'type'  => 'uri',
      'value' => 'http://xmlns.com/foaf/0.1/Organization'
    );
    
    // foaf:name
    $pattern = '|<name>([^<]+)</name>|s';
    preg_match($pattern, $raw_data, $match);
    
    if ($match) {
      $result[$uri]['http://xmlns.com/foaf/0.1/name'] = array();
      $result[$uri]['http://xmlns.com/foaf/0.1/name'][] = array(
        'type'  => 'literal',
        'value' => $match[1]
      );
    }
    
    // mo:myspace
    // foaf:weblog
    // foaf:homepage
    // foaf:holdsAccount
    $pattern = '|<url>([^<]+)</url>|s';
    preg_match_all($pattern, $raw_data, $match);
    
    if ($match) {
      $result[$uri]['http://xmlns.com/foaf/0.1/weblog'] = array();
      $result[$uri]['http://purl.org/ontology/mo/myspace'] = array();
      $result[$uri]['http://xmlns.com/foaf/0.1/holdsAccount'] = array();
      $result[$uri]['http://xmlns.com/foaf/0.1/homepage'] = array();
      foreach ($match[1] as $url) {
        if (strpos($url, 'blogspot')) {
          $result[$uri]['http://xmlns.com/foaf/0.1/weblog'][] = array(
            'type'  => 'literal',
            'value' => $url
          );
        } elseif (strpos($url, 'myspace')) {
          $result[$uri]['http://purl.org/ontology/mo/myspace'][] = array(
            'type'  => 'literal',
            'value' => $url
          );
        } elseif (strpos($url, 'twitter')) {
          $result[$uri]['http://xmlns.com/foaf/0.1/holdsAccount'][] = array(
            'type'  => 'uri',
            'value' => $url
          );
        } else {
          $result[$uri]['http://xmlns.com/foaf/0.1/homepage'][] = array(
            'type'  => 'literal',
            'value' => $url
          );
        }
      }
    }

    // mo:is_label_of
    $pattern = '|<release id="([^"]+)"|s';
    preg_match_all($pattern, $raw_data, $match);
    if ($match) {
      $result[$uri]['http://purl.org/ontology/mo/is_label_of'] = array();
      foreach ($match[1] as $rel_id) {
        $result[$uri]['http://purl.org/ontology/mo/is_label_of'][]= array(
          'type'  => 'uri',
          'value' => 'http://discogs.com/release/' . $rel_id
        );
      }
    }

    return $result;
  }
  
  public function parseTrack($uri, $raw_data)
  {
    
  }
  
    
}

?>