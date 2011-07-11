<?
	@session_start();
	require_once("PrefixHandler.php");
	//require_once("Logger.php");
	require_once("Options.php");
	require_once("Dbpedia.php");
	require_once("QueryOptimizer.php");
	include('sparqlRetrieval.php');
	
	
	if(!isset($debug))$debug = false;
	global $debug;
	/***
	 * OPTIONS
	 * **/
	$options = new Options();
	$ph = new PrefixHandler($options);
	$prefixes = $ph->getPrefixesForSPARQLQuery();
	
	$q = $_REQUEST['q'];
	$json = $_REQUEST['json'];
	$limit = $_REQUEST['limit'];
	
	isVariable($q);
	$json = str_replace('\"','"', str_replace("\'","'",$json));
	//echo $json;die;
	$patterns = json_decode($json, true );
	$qo = new QueryOptimizer($patterns, $q, $limit, $options);
	$currentType = $qo->currentType;
	//cache($qo, $q, $ph);
	
	
	$sparqlQueryString = $qo->getAutocompleteQuery();
	if($debug)echo $sparqlQueryString;
	
	$defaultgraphURI = $options->get('default-graph-uri');
	$endpointURI = $options->get('default-endpoint-uri');
    // $jsonResult = executeSparqlQuery($endpointURI, $defaultgraphURI, $sparqlQueryString);
   
	$jsonArray = executeSparqlQuery($endpointURI, $defaultgraphURI, $sparqlQueryString);
	//print_r($jsonArray);
	//echo "testing";die;
	//Logger::toFile("log/autocompletion.log","query needed: ".Logger::getTimeElapsed()." sec\n\n");
	//echo $xmlSparqlResults;
    // $jsonArray = json_decode($jsonResult,true);
	
	if(!is_array($jsonArray)){
		//echo "there will be a nicer error message, soon\n";
		echo $jsonArray."\n";
		die;
		}
	
	if(count($jsonArray['results']['bindings'])==0){
		echo "no results| \n";
		die;
		
		}
	
	
	if($options->get('usecount')){
			$list = toListWithCount($jsonArray, $ph);
			if(!is_array($list)){
				print_r( $jsonArray);
				die;
			}
			
			if(!$options->get('useorderby')){ uasort($list,'compare_by_count');}
	}else {
		 	$list = toListNoCount($jsonArray, $ph);
		 	if(!is_array($list)){
				print_r( $jsonArray);
				die;
			}
		}
	
	if(is_array($list)){
			//toCache('lastresult', $list);
			
		}
	//print_r($list);
	doOutput($list, $ph, $currentType);
		
	
		
	function dbpedia($patterns){
		
		}	
		
	
		
	
	function isVariable($q){
			if(startsWith(trim($q), "?")){
				die;
				}
		}
		
	function compare_by_count($a, $b) {
		$sortkey = 'count';
		if ($a[$sortkey] == $b[$sortkey]){return 0;}
    	else {return  ($a[$sortkey] > $b[$sortkey] ) ? -1 : 1; }
			
		} # sort alphabetically by name usort($data, 'compare_lastname');
		
	
	function toListWithCount($jsonArray, $prefixHandler){
		$list = array();
		//echo $jsonArray;
		$bindings= $jsonArray['results']['bindings'];
		foreach ($bindings as $binding){
				if(isset($binding['count']['value'])){
					$tmp['value'] = $binding['suggest']['value'];
					
					$tmp['type'] = $binding['suggest']['type'];
					
					$tmp['lang'] = @$binding['suggest']['xml:lang'];
					$tmp['datatype'] = @$binding['suggest']['datatype'];
										
					$tmp['count'] = $binding['count']['value'];
					
					
					$list[] = $tmp;
				}
			}
		return $list;	
	}
	

	
	function toListNoCount($jsonArray, $prefixHandler){
		$list = array();
		//echo $jsonArray;
		$bindings= $jsonArray['results']['bindings'];
		foreach ($bindings as $binding){
					/*if(isset($binding['count']['value'])){
						 $uri = $prefixHandler->applyPrefixToURIString($binding['suggest']['value']);
						}*/
					$uri = $binding['suggest']['value'];
					
					if(!isset($list[$uri]['count']) ){
							$list[$uri]['count'] = 1;
						}
					else{
							@$list[$uri]['count'] += 1;
						}
					$list[$uri]['type'] = $binding['suggest']['type'];
					
					$list[$uri]['lang'] = @$binding['suggest']['xml:lang'];
					$list[$uri]['datatype'] = @$binding['suggest']['datatype'];
					//$list[] = $prefixHandler->applyPrefixToURIString($binding['suggest']['value']);
			}
		
		//$count = array_count_values($list);
		//Logger::toFile("log/autocompletion.log","count:\n\n");
		//Logger::arrayToFile("log/autocompletion.log",$count);
		//var_dump(array_reverse($list));
		$out = array();
		foreach(array_keys($list) as $key){
			$tmparr = $list[$key];
			$tmparr['value']=$key;
			$out[]=$tmparr;
			}
		
		global $debug;
		if($debug)print_r($out);
		uasort($out,'compare_by_count');	
		
		return $out;	
	}
	
	function doOutput($list, $ph){
		//print_r($list);
		foreach($list as $one){
				$uri = $one['value'];
				$count = " (".$one['count'].")";
				if(startsWith($uri, "http://dbpedia.org/resource/")|| 
				   startsWith($uri, "http://dbpedia.org/property/")
				){
					$prefixeduri = str_replace("http://dbpedia.org/resource/","",str_replace("http://dbpedia.org/property/","",$uri));
				}else{
					$prefixeduri = $ph->applyPrefixToURIString($uri);
				}
				
				$localname = urldecode($ph->removePrefixFromURI($prefixeduri));
				$localname = str_replace("\n","",$localname);
				echo  $localname.$count."|";
				echo  urldecode($prefixeduri)."|";
				echo  @$one['type'] ."|";
				echo  @$one['lang'] ."|";
				echo  @$one['datatype']."|";
				echo "\n";	
				
		}
		
	}
	
	
 	function startsWith($Haystack, $Needle){
  	  	// Recommended version, using strpos
 	   		return strpos($Haystack, $Needle) === 0;
		}
 	
 	//for cache q is Haystack Session is Needle
 	function startsWithIgnoreCase($Haystack, $Needle){
 		if(strlen($Needle)==0){return false;}
  	  	// Recommended version, using strpos
 	   		return strpos(strtolower($Haystack), strtolower($Needle)) === 0;
		}
	
	
		
	function cache( $queryoptimizer, $q, $prefixHandler){
		
			$patterns = $queryoptimizer->patterns;
			
			if(		isset($_SESSION['patterns'])	&&
					$_SESSION['patterns'] == $patterns &&
					startsWithIgnoreCase($q, $_SESSION['q'] )
				) {
					
					$res = fromCache('lastresult');
					
					$x = 0;
					$list = array();
					foreach($res as $one ){
							$plain = $prefixHandler->removeNamespaceFromURI($one['value']);
							//echo "aa:".$plain."\n";
							if(startsWithIgnoreCase($plain,$q )){
								$list[] = $one;
								}
							//$prefix
							//if(startsWith($one['']))
						}
					if(count($list)>=1){
						echo "from cache:|\n";
						doOutput($list,$prefixHandler);
						$_SESSION['patterns'] = $patterns;
						$_SESSION['q'] = $q;
						die;
						}
				}
			
			
			$_SESSION['patterns'] = $patterns;
			$_SESSION['q'] = $q;
			 
		}	
		
	function toCache($key, $content){
			$_SESSION[$key] =  $content;
		}
		
	function fromCache($key){
			return $_SESSION[$key] ;
		}
	
	

	
?>
