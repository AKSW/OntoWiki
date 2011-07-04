<?
	//require_once("lib/Sajax.php");
	require_once("PrefixHandler.php");
	require_once("QueryOptimizer.php");
	require_once("Logger.php");
	require_once("Options.php");
	
	include('sparqlRetrieval.php');
	
	//$function = $_REQUEST['func'];
	$json = $_REQUEST['json'];
	$limit = $_REQUEST['limit'];
	//print_r($_REQUEST);die;
	echo updateTable($json, $limit);
	return;
	
	
	function updateTable($json, $limit = 10) {
		//Logger::toFile("log/updateTable.log",$json."\n\n");
		/***
		 * OPTIONS
		 * **/
		$options = new Options();
		$prefixHandler = new PrefixHandler($options);
		$prefixes = $prefixHandler->getPrefixesForSPARQLQuery();
		
		$defaultgraphURI = $options->get('default-graph-uri');
		$endpointURI = $options->get('default-endpoint-uri');
		
		
		$json = str_replace('\"','"',$json);
		
		
		$patterns = json_decode($json, true   );
		//var_dump( $patterns); die;
		$q="";
	
		$qo = new QueryOptimizer($patterns, $q, $limit, $options);

		$sparqlQueryString = $qo->getSPARQLQuery($patterns, $limit, $prefixHandler);
	//	$sparqlQueryString = $prefixes."SELECT * WHERE { \n".$querypatterns." } LIMIT ".$limit;
		//Logger::toFile("log/updateTable.log",$sparqlQueryString."\n\n");
		 $sparqlQueryString = str_replace("Category%3A","Category:",$sparqlQueryString);
        // $jsonSparqlResults = executeSparqlQuery($endpointURI, $defaultgraphURI, $sparqlQueryString);
		$jsonArray = executeSparqlQuery($endpointURI, $defaultgraphURI, $sparqlQueryString);
		//echo $xmlSparqlResults;
		//var_dump( $jsonArray); die;
        // $jsonArray = json_decode($jsonSparqlResults,true);
		//Logger::arrayToFile("log/updateTable.log",$jsonArray);
		$table = toTable($jsonArray, $prefixHandler);
		//Logger::toFile("log/updateTable.log",$table."\n\n");
		return $table;
	}
	
	
		
	
	
	function toTable($jsonArray, $prefixHandler){
		
		
		//var_dump($jsonArray);die;
		if(!is_array($jsonArray)){
			//$jsonArray = str_replace("\n","<br>",$jsonArray);
			return "<thead> <tr> <th>Error</th> </tr> </thead> <tbody> <tr> <td><xmp>".$jsonArray."</xmp></td> </tr> </tbody> ";
			}
			$vars = $jsonArray['head']['vars'];
			
			$bindings= $jsonArray['results']['bindings'];
			
			if(count($bindings) == 0){
				return "<thead> <tr> <th>Empty result set</th> </tr> </thead> <tbody> <tr> <td><xmp>".tostr($jsonArray)."</xmp></td> </tr> </tbody> ";
				}
			
			$table = "<thead> <tr> \n";
			foreach ($vars as $var){
				$table.="<th>".$var."</th>\n";
				}
			$table.="</tr> </thead> <tbody> \n ";
			
			foreach ($bindings as $binding){
				$table.="<tr>\n";
				foreach ($vars as $var){
					$value = $binding[$var]['value'];
					if(($binding[$var]['type']) == "uri"){
						$link = "";
						if(($tmp=$prefixHandler->getLinkedDataURI($value))!==0){
								$link = "<a href=\"".$tmp."\" target=\"_blank\" >";
								$link.= "&nbsp;&nbsp;<img src=\"css/link.png\" title=\"Show LinkedData\" ></a>";
							}
						
						$field = $prefixHandler->applyPrefixToURIString($value).$link;
						}
					else{
						$field = $value;
						}
					//echo "\n\n";
					
					$table.="<td>".urldecode($field)."</td>\n";
				}
				$table.="</tr>\n";
			}
			$table.="</tbody> ";
    
			return $table;
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
