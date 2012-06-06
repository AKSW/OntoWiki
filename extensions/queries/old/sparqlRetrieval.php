<?php


function executeSparqlQuery($endpointURI, $graphUri = 'NULL',  $sparqlQuery, $usetimeout=true)
{	//echo $graphUri;die;
    $dsn = 'VOS6';
    $user = 'dba';
    $pw = 'dba';
    
    if (!function_exists('odbc_connect')) {
        echo 'Virtuoso adapter requires PHP ODBC extension to be loaded.';
        return;
    }
    
    // try to connect
    $con = @odbc_connect($dsn, $user, $pw);
    
    if (null == $con) {
         echo 'Unable to connect to Virtuoso Universal Server via ODBC: '."\n";
        return;
    }
    
    /*$normalmode = true;
    if ($normalmode) {
        if (!is_string($graphUri) || $graphUri == '') {
            $graphUri = 'NULL';
        } else if ($graphUri != 'NULL') {
           // $graphUri = '\'' . $graphUri . '\'';
        }
     */
     if($usetimeout){   
        $timeout = "set RESULT_TIMEOUT=5000";
         //$con->_longRead = true;
        $result = @odbc_exec($con,$timeout);
     }  
        $sparqlQuery = ' define output:format "RDF/XML" '.  $sparqlQuery;
        //$virtuosoPl = 'CALL DB.DBA.SPARQL_EVAL(\'' . $sparqlQuery . '\', ' . $graphUri . ', 0)';
        $virtuosoPl = 'SPARQL define input:default-graph-uri <' . $graphUri . '>' . $sparqlQuery;
    	//echo '<xmp>'.$virtuosoPl;die;
        $result = @odbc_exec($con, $virtuosoPl);
        
         if (false === $result) {
			$msg = odbc_errormsg() . ' (' . odbc_error() . ')';
			//require_once 'Erfurt/Exception.php';
			//throw new Erfurt_Exception(
			$error = ( 'ERROR: ' . $msg . "\n" . "Query:\n" . trim($sparqlQuery)."\n");
		  
			echo $error."\n";              
        
    	}
        
       // echo "<xmp>";
        //echo odbc_result_all  ( $result  );
        //echo $virtuosoPl ; die;
        odbc_longreadlen($result, 10000000);
        
        $result = odbcResultToArray($result);
         //var_dump( $result);die;
        $row = current($result);
        $xml = current($row);
        //echo $xml;die;
        require_once 'XmlConverter.php';
        $conv   = new XmlConverter();
        $result = $conv->toArray($xml);
        //$result = json_encode($result);
      // echo $result;die;
       
  /*  } else {
        $virtuosoPl = 'SPARQL define input:default-graph-uri <' . $graphUri . '>' . $sparqlQuery;
        // $virtuosoPl = 'SPARQL ' . $sparqlQuery;
        $result = @odbc_exec($con, $virtuosoPl);
    }*/
    
    
    
    return $result;
}
    
    
    
    
    
 function executeSparqlQuery1($endpointURI, $defaultgraphURI, $sparqlQueryString){
            
            $url = $endpointURI."/sparql?query=";
            
            //echo $query."\n";
            $defaultgraphURI = (strlen($defaultgraphURI)==0)?"":"&default-graph-uri=".$defaultgraphURI;
            $format="&format=JSON";
            $url .= urlencode($sparqlQueryString).$defaultgraphURI.$format;
            //return $url;
            //echo $url."\n";
            $c = curl_init();
            //$headers = array("Accept: application/sparql-results+xml");
            
            $headers = array("Content-Type: application/sparql-results+json");
            $headers = array("set RESULT_TIMEOUT:5");
            //$headers = array("Content-Type: rdf");
            
            /*Accept: application/xml
            text/html
                application/sparql-results+json
application/javascript
XML         application/sparql-results+xml
TURTLE      text/rdf+n3
            */
            curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($c, CURLOPT_URL, $url);
            curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
            $contents = curl_exec($c);
            //file_put_contents("log/curl.log",$url."\n".$contents);
            curl_close($c);
            return $contents;
        }
    
    
    
    
function odbcResultToArray($odbcResult, $columnsAsKeys = true, $rowsAsArrays = true)
{
    $result         = $odbcResult;
    $resultArray    = array();
    $resultRow      = array();
    $resultRowNamed = array();
    
    // get number of fields (columns)
    $numFields = odbc_num_fields($result);

    if ($numFields === 0) {
        return $resultArray;
    }        
   
    while (odbc_fetch_into($result, $resultRow)) {
        if ($numFields == 1 && !$rowsAsArrays) {
            // add first row field to result array
            array_push($resultArray, $resultRow[0]);
        } else {
            // copy column names to array indices
            for ($i = 0; $i < $numFields; ++$i) {
                if ($columnsAsKeys) {
                    $colName = odbc_field_name($result, $i + 1);
                    $resultRowNamed[$colName] = $resultRow[$i];
                } else {
                    $resultRowNamed[] = $resultRow[$i];
                }
            }
            
            // add row to result array
            array_push($resultArray, $resultRowNamed);
        }
    }
    
    return $resultArray;
}
    
    
