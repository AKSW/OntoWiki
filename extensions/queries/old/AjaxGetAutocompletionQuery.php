<?
	//require_once("lib/Sajax.php");
	require_once("PrefixHandler.php");
	require_once("QueryOptimizer.php");
	require_once("Logger.php");
	require_once("Options.php");
	
	include('sparqlRetrieval.php');
	
	$json = $_REQUEST['json'];
	$limit = $_REQUEST['limit'];
		echo getAutocompletionQuery($json, $limit);
	
	
		
	function getAutocompletionQuery($json, $limit = 10){
		
			$json = str_replace('\"','"',$json);
			$patterns = json_decode($json, true  );
			
			$q = getSearchWordFromPatterns($patterns);
			
			
			$options = new Options();
			$ph = new PrefixHandler($options);
			$prefixes = $ph->getPrefixesForSPARQLQuery();
			$strategy = $options->get('strategy');
			$useCountInQuery = $options->get('usecount');
			$useOrderBy = $options->get('useorderby');
			
			$qo = new QueryOptimizer($patterns, $q, $limit, $options);
			
			$sparqlQueryString = $qo->getAutocompleteQuery();
			//$sparqlQueryString =  $prefixes.getQueryForStrategy($strategy, $patterns, $q, $limit, $useCountInQuery, $useOrderBy);
			  //getQueryForStrategy($strategy, $patterns, $q, $limit, $useCountInQuery, $useOrderBy);
		//	$sparqlQueryString = $prefixes.createQueryUsingBifContainsAndCount($patterns, $q, $limit );
			//Logger::toFile("log/getAutocompletionQuery.log",$sparqlQueryString."\n\n");
			return $sparqlQueryString;
		}
		
	function getSearchWordFromPatterns($patterns){
			$result = "None Found";
			foreach ($patterns as $pattern){
				$s = $pattern['s'];
				$p = $pattern['p'];
				$o = $pattern['o'];
				//print_r($patterns);
				
				if($pattern['current'] == "s"){
					return $s;
				}else if($pattern['current'] == "p"){
					return $p;
				}else if($pattern['current'] == "o"){
					return $o;
					}
			}//end foreach
			return $result;
		}
		
	
	
	
	function tostr($x){
			ob_start();
			print_r($x);
		 return ob_get_clean();	
		}
	
	
/*
	sajax_init();
	//$sajax_debug_mode = 1;
	sajax_export("getResults");
	sajax_export("getAutocompletionQuery");
	sajax_export("getSPARQLQuery");
	sajax_handle_client_request();
*/
	
?>
