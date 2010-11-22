<?
	//require_once("lib/Sajax.php");
	require_once("PrefixHandler.php");
	require_once("QueryOptimizer.php");
	require_once("Logger.php");
	require_once("Options.php");
	
	include('sparqlRetrieval.php');
	
	$json = $_REQUEST['json'];
	$limit = $_REQUEST['limit'];
	echo getSPARQLQuery($json, $limit);
	
	
	function getSPARQLQuery($json, $limit = 10){
			$options = new Options();
			$prefixHandler = new PrefixHandler($options);
			$prefixes = $prefixHandler->getPrefixesForSPARQLQuery();
			
			$defaultgraphURI = $options->get('default-graph-uri');
			$endpointURI = $options->get('default-endpoint-uri');
			
			$q="";
			
			$json = str_replace('\"','"',$json);
			$patterns = json_decode($json, true   );
			
			$qo = new QueryOptimizer($patterns, $q, $limit, $options);

			//$sparqlQueryString = $prefixes.$qo->getSPARQLQuery($patterns, $limit, $prefixHandler);
			$sparqlQueryString = $qo->getSPARQLQuery($patterns, $limit, $prefixHandler);

			
		//	$sparqlQueryString = $prefixes."SELECT * WHERE { \n".$querypatterns."} LIMIT ".$limit;
			//Logger::toFile("log/getSPARQLQuery.log", $sparqlQueryString."\n\n");
			//$sparqlQueryString=str_replace("\n","<br>",$sparqlQueryString);
			return $sparqlQueryString;
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
