<?php
require_once 'Erfurt/Wrapper.php';

/**
 * Initial version of a wrapper for Twitter.
 * Currently this is only a demo. It shows how a wrapper can handle data
 * itself, as well as quering the store and removing data.
 * 
 * @category   OntoWiki
 * @package    OntoWiki_extensions_wrapper
 * @author     Philipp Frischmuth <pfrischmuth@googlemail.com>
 * @copyright  Copyright (c) 2009 {@link http://aksw.org aksw}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: twitter.php 4094 2009-08-19 22:36:13Z christian.wuerker $
 */
class TwitterWrapper extends Erfurt_Wrapper
{
    protected $_cachedData = array();
    
    protected $_pattern = null;
    
    public function getDescription()
    {
        return 'A simple wrapper for Twitter.';
    }
    
    public function getName()
    {
        return 'Twitter';
    }
    
    public function init($config)
    {
        parent::init($config);
        
        $this->_pattern = "/^(http:\/\/twitter.com\/)(\w+)$/";
    }
    
    public function isAvailable($uri, $graphUri)
    {
        $id = $this->_cache->makeId($this, 'isAvailable', array($uri, $graphUri));
        $result = $this->_cache->load($id);
        if ($result !== false) {
            if (!isset($this->_cachedData[$graphUri])) {
                $this->_cachedData[$graphUri] = array($uri => $result['data']);
            } else {
                $this->_cachedData[$graphUri][$uri] = $result['data'];
            }
            
            return $result['value'];
        }
        
        $retVal = false;
        $data = array();
        
        $match = array();
        if (preg_match($this->_pattern, $uri, $match)) {
            $name = $match[2];
            $notAllowedNames = array('home', 'users', 'statuses', 'direct_messages', 'friendships', 'friends');
            
            if (!in_array($name, $notAllowedNames)) {
                $url1 = 'http://twitter.com/statuses/user_timeline/' . $name . '.json';
                $url2 = 'http://twitter.com/friends/ids/' . $name . '.json';
                

                require_once 'Zend/Http/Client.php';
                $client = new Zend_Http_Client($url1, array(
                    'maxredirects'  => 0,
                    'timeout'       => 30
                ));
                
                if (isset($this->_config->username) && isset($this->_config->password)) {
                    $client->setAuth($this->_config->username, $this->_config->password);
                }
                
                $response = $client->request();

                if ($response->getStatus() === 200) {
                    $result = json_decode($response->getBody(), true);
                    $data['status'] = $result;
                    $retVal = true;
                }

                $client->setUri($url2);
                $response = $client->request();
                if ($response->getStatus() === 200) {
                    $result = json_decode($response->getBody(), true);
                    $data['friends'] = $result;
                    $retVal = true;
                }
                
            }
        } 
        
        if (!isset($this->_cachedData[$graphUri])) {
            $this->_cachedData[$graphUri] = array($uri => $data);
        } else {
            $this->_cachedData[$graphUri][$uri] = $data;
        }
        
        $cacheVal = array('value' => $retVal, 'data' => $data);
        $this->_cache->save($cacheVal, $id);
        return $retVal;
    }
    
    public function isHandled($uri, $graphUri)
    {
        // Use your own libs.
        require_once 'libraries/TestPhpLibrary.php';
        $libObj = new TestPhpLibrary();
        if ($libObj->testLibMethod()) {
            // Do something...
        }
        
        if (preg_match($this->_pattern, $uri)) {
            return true;
        } else {
            require_once 'Erfurt/Sparql/SimpleQuery.php';
            $query = new Erfurt_Sparql_SimpleQuery();
            
            $query->setProloguePart('PREFIX foaf: <http://xmlns.com/foaf/0.1/> SELECT ?o');
            $query->addFrom($graphUri);
            
            $wherePart = "WHERE { 
                           <$uri> foaf:accountName ?o . 
                           <$uri> foaf:accountServiceHomepage <http://twitter.com/home>
                         }";
        
            $query->setWherePart($wherePart);
        
            $store = Erfurt_App::getInstance()->getStore();
            $result = $store->sparqlQuery($query);
            
            if (count($result) > 0) {
                return true;
            }
        }
    }
    
    public function run($uri, $graphUri)
    {
        $id = $this->_cache->makeId($this, 'run', array($uri, $graphUri));
        $result = $this->_cache->load($id);
        if ($result !== false) {
            return $result;
        }
        
        if ($this->isAvailable($uri, $graphUri)) {
            $data = $this->_cachedData[$graphUri][$uri];
#echo "<pre>";
#var_dump($data);exit;
#echo "</pre>";
            $result = array();
            $result[$uri] = array();
            
            $posts = array();
            
            if (count($data['status']) > 0) {
                $user = $data['status'][0]['user'];
                
                if (isset($user['profile_image_url'])) {
                    $result[$uri]['http://rdfs.org/sioc/ns#avatar'] = array();
                    $result[$uri]['http://rdfs.org/sioc/ns#avatar'][] = array(
                        'type'  => 'uri',
                        'value' => $user['profile_image_url']
                    );
                }
                
                if (isset($user['screen_name'])) {
                    $result[$uri]['http://xmlns.com/foaf/0.1/accountName'] = array();
                    $result[$uri]['http://xmlns.com/foaf/0.1/accountName'][] = array(
                        'type'  => 'literal',
                        'value' => $user['screen_name']
                    );
                }
            }
            
            if (isset($data['friends'])) {
                require_once 'Zend/Http/Client.php';
                $client = new Zend_Http_Client(null, array(
                    'maxredirects'  => 0,
                    'timeout'       => 30
                ));
                
                $friends = array();
                foreach ($data['friends'] as $friendId) {
                    $url = $url1 = 'http://twitter.com/users/show/' . $friendId . '.json';
                    $client->setUri($url);
                    
                    $response = $client->request();
                    if ($response->getStatus() === 200) {
                        $body = json_decode($response->getBody(), true);
                        
                        if (isset($body['screen_name'])) {
                            $friends[] = 'http://twitter.com/' . $body['screen_name'];
                        }
                    } else {
                        continue;
                    }
                }
                
                $result[$uri]['http://rdfs.org/sioc/ns#follows'] = array();
                foreach ($friends as $friendUri) {
                    $result[$uri]['http://rdfs.org/sioc/ns#follows'][] = array(
                        'type'  => 'uri',
                        'value' => $friendUri
                    );
                }
            }
            
            foreach ($data['status'] as $status) {
                $postUri = 'http://twitter.com/' . $status['user']['screen_name'] . '/status/' . $status['id'];
                
                $posts[$postUri] = array();
                $posts[$postUri]['http://www.w3.org/1999/02/22-rdf-syntax-ns#type'] = array();
                $posts[$postUri]['http://www.w3.org/1999/02/22-rdf-syntax-ns#type'][] = array(
                    'type'  => 'uri',
                    'value' => 'http://rdfs.org/sioc/types#MicroblogPost'
                );
                
                $posts[$postUri]['http://rdfs.org/sioc/ns#content'] = array();
                $posts[$postUri]['http://rdfs.org/sioc/ns#content'][] = array(
                    'type'  => 'literal',
                    'value' => $status['text']
                );
                
                $posts[$postUri]['http://purl.org/dc/terms/created'] = array();
                $posts[$postUri]['http://purl.org/dc/terms/created'][] = array(
                    'type'  => 'literal',
                    'value' => $status['created_at']
                );
                
                $posts[$postUri]['http://rdfs.org/sioc/ns#has_creator'] = array();
                $posts[$postUri]['http://rdfs.org/sioc/ns#has_creator'][] = array(
                    'type'  => 'uri',
                    'value' => 'http://twitter.com/' . $status['user']['screen_name']
                );
                
                if (isset($status['in_reply_to_status_id'])) {
                    $posts[$postUri]['http://rdfs.org/sioc/ns#reply_of'] = array();
                    $posts[$postUri]['http://rdfs.org/sioc/ns#reply_of'][] = array(
                        'type'  => 'uri',
                        'value' => 'http://twitter.com/' . $status['in_reply_to_screen_name'] . 
                            '/status/' . $status['in_reply_to_status_id']
                    );
                }
                
                if (!isset($result[$uri]['http://rdfs.org/sioc/ns#creator_of'])) {
                    $result[$uri]['http://rdfs.org/sioc/ns#creator_of'] = array();
                }
                $result[$uri]['http://rdfs.org/sioc/ns#creator_of'][] = array(
                    'type'  => 'uri',
                    'value' => $postUri
                );
            }
            
            // Remove old posts
            $store = Erfurt_App::getInstance()->getStore();
            require_once 'Erfurt/Sparql/SimpleQuery.php';
            $query = new Erfurt_Sparql_SimpleQuery();
            $query->setProloguePart('PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> 
                PREFIX sioc_t: <http://rdfs.org/sioc/types#> PREFIX sioc: <http://rdfs.org/sioc/ns#> SELECT ?s');
            $query->addFrom($graphUri);
            $query->setWherePart("WHERE { ?s rdf:type sioc_t:MicroblogPost . ?s sioc:has_creator <$uri>}");
            
            $queryResult = $store->sparqlQuery($query);
            $removed = false;;
            foreach ($queryResult as $row) {
                $store->deleteMatchingStatements($graphUri, $row['s'], null, null);
                $removed = true;
            }
            
            // Add new posts
            $added = count($posts);
            $store->addMultipleStatements($graphUri, $posts);
            
            $fullResult = array();
            $fullResult['status_codes'] = array();
            
            if ($removed) {
                $fullResult['status_codes'][] = Erfurt_Wrapper::STATEMENTS_REMOVED;
                //$fullResult['status_codes'][] = Erfurt_Wrapper::RESULT_HAS_REMOVED_COUNT;
                
                //$fullResult['removed_count'] = $removed;
            }
            
            if ($added > 0) {
                $fullResult['status_codes'][] = Erfurt_Wrapper::STATEMENTS_ADDED;
                $fullResult['status_codes'][] = Erfurt_Wrapper::RESULT_HAS_ADDED_COUNT;
                
                $fullResult['added_count'] = $added;
            }
            
            if (count($fullResult['status_codes']) === 0) {
                $fullResult['status_codes'][] = Erfurt_Wrapper::NO_MODIFICATIONS;
            }
            
            $fullResult['status_description'] = "Twitter data found for URI $uri";
            $fullResult['add'] = $result;
            $fullResult['status_codes'][] = Erfurt_Wrapper::RESULT_HAS_ADD;
            
            $this->_cache->save($fullResult, $id);
            return $fullResult;
        } else {
            return false;
        }
    }
}
