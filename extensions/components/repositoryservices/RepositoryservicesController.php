<?php

require_once 'Zend/Controller/Action.php';
require_once 'OntoWiki/Controller/Component.php';
require_once 'OntoWiki/Toolbar.php';
require_once 'Erfurt/Sparql/SimpleQuery.php';
require_once 'Zend/Http/Client.php';
require_once 'Zend/Json/Encoder.php';
require_once 'Zend/Json/Decoder.php';



/**
 * OntoWiki index controller.
 * 
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_repository
 * @author     Feng Qiu <qiu_feng39@hotmail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: ServiceController.php 3031 2009-05-05 17:31:06Z norman.heino $
 */
class RepositoryservicesController extends OntoWiki_Controller_Component
{
	
	//const TAG_BASE_URL = 'http://ontowiki.net/tag';
	
	
   
   
    
    /**
     * Wait for the request from pluginclient and send the information
     * about the tags
     */
    public function tagsAction()
    {
    	$repository_base_url = $this->_privateConfig->repository->graphiri;
    	$tag_base_url = $this->_privateConfig->repository->tag_base_url;
        // service controller needs no view renderer
        $this->_helper->viewRenderer->setNoRender();
        // disable layout for Ajax requests
        $this->_helper->layout()->disableLayout();

        $store    = OntoWiki_Application::getInstance()->erfurt->getStore();
        $response = $this->getResponse();
        
        // The parameters 
        $num_of_tags  = $this->_request->getParam('count', 10);
        $class = $this->_request->getParam('class', '');
        $selected_tags = $this->_request->getParam('tags', '');
		if($selected_tags!=''){
			$selected_tags = Zend_Json::decode($selected_tags);
		}
		else{
			$selected_tags = array();
		}
		
        require_once 'Erfurt/Sparql/SimpleQuery.php';
        if(count($selected_tags)==0){
        	$query = Erfurt_Sparql_SimpleQuery::initWithString(
			        		'SELECT DISTINCT ?tag
			                FROM <' . $repository_base_url . '>
			                WHERE {
			                    ?node <'. $tag_base_url .'> ?tag.
			                }'
	        );
	        $tags = $store->sparqlQuery($query);
        }
        else{
        	$tags = array();
        	for($i=0;$i<count($selected_tags);$i++){
        		$a_selected_tag = $selected_tags[$i];
        		$query = Erfurt_Sparql_SimpleQuery::initWithString(
				        		"SELECT DISTINCT ?tag
				                FROM <".$repository_base_url.">
				                WHERE {
				                    ?node <" . $tag_base_url . "> ?tag.
				                    ?node <" . $tag_base_url . "> \"$a_selected_tag\"^^xsd:string.
				                }"
		        );
		        $tags = array_merge($store->sparqlQuery($query),$tags);
		        $tags = $this->unique_tags($tags);
        	}    
        }
        //echo "Tags:".print_r($tags)."<br/><br/>";
        $tag_with_frequence = array();
        $tags_results = array();
        if(count($tags)>0){
        	$bigger = (count($tags)>$num_of_tags ? $num_of_tags:count($tags));
        	for($i=0; $i<$bigger; $i++){
        		$a_tag = $tags[$i];
        		$tag_name = $a_tag['tag'];
        		$where = "WHERE {
		                ?node <". $tag_base_url ."> \"".$tag_name."\"^^xsd:string.
		            }";
		        $count = $store->countWhereMatches($repository_base_url,$where,"?node");
        		$tag_with_frequence[$tag_name] = $count;
        	}
        	ksort($tag_with_frequence);
        	
        	$tags_results = $this->sort_tags($tag_with_frequence);
        	//echo"Tags_results".print_r($tags_results)."<br/><br/>";
        }
        
        // send the response
        $response->setHeader('Content-Type', 'application/json');
        $response->setBody(Zend_Json::encode($tags_results));
        $response->sendResponse();
        exit;
    }
    
    
    
    /**
     * A function to get the weight
     *
     * @param array $tags_with_frequence
     */
    public function sort_tags($in_alphabet){
    	//The parameters
    	$count = $this->_request->getParam('count', 10);
    	$class = $this->_request->getParam('class', '');
    	$tags = $this->_request->getParam('tags', '');
    	if($tags != ''){
    		$tags = Zend_Json::decode($tags);
    	}
    	else{
    		$tags = array();
    	}
    	
    	$in_frequence = $in_alphabet;
    	arsort($in_frequence);
    	$tags_results = array();
    	$a_in_alphabet = current($in_alphabet);

    	while(list($a,$b) = each($in_alphabet)){
    		$weight = $count-1;
    		reset($in_frequence);
    		while(list($c, $d) = each($in_frequence)) {
    			if($a == $c){
    				$tags_results[] = array(	
    					'tag' => $a,
    					'weight' => $weight,
    					'frequence' => $b);
    			}
    			$weight--;
    		}
    	}
    	
    	
    	
    	return $tags_results;
    }
	
    /**
     * Unique the tags in array, to save the time
     *
     * @param unknown_type $tags
     * @return unknown
     */
    public function unique_tags($tags){
    	$temp_tags = array();
    	$results_tags = array();
    	for($i=0;$i<count($tags);$i++){
    		$temp_tags[] = $tags[$i]['tag'];
    	}
    	$temp_tags = array_unique($temp_tags);
    	foreach($temp_tags as $a_tag){
    		$results_tags[] = array('tag' => $a_tag);
    	}
    	return $results_tags;
    }
    
}

