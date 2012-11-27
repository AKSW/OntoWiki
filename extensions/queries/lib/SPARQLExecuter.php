<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * @category   OntoWiki
 * @package    Extensions_Queries_Lib
 */
class SPARQLExecuter{

    var $options;

    public function __construct($options){
        $this->options=$options;
    }

    function executeSparqlQuery( $graphUri = 'NULL',  $sparqlQuery, $timeoutms=15000)
    {
        $dsn = $this->options->get('odbc_dsn');
        $user=$this->options->get('odbc_user');
        $pw=$this->options->get('odbc_pw');

        // echo $dsn;die;

        if (!function_exists('odbc_connect')) {
            echo 'Virtuoso adapter requires PHP ODBC extension to be loaded.';
            exit;
        }

        // try to connect
        $con = @odbc_connect($dsn, $user, $pw);

        if (null == $con) {
            echo 'Unable to connect to Virtuoso Universal Server via ODBC: '."\n";
            exit;
        }


        $timeout = "set RESULT_TIMEOUT= ".$timeoutms;
        $result = @odbc_exec($con,$timeout);

        $sparqlQuery = ' define output:format "RDF/XML" '.  $sparqlQuery;
        // $virtuosoPl = 'CALL DB.DBA.SPARQL_EVAL(\'' . $sparqlQuery . '\', ' . $graphUri . ', 0)';
        $virtuosoPl = 'SPARQL define input:default-graph-uri <' . $graphUri . '>' . $sparqlQuery;
        //$http =  "sparql?default-graph-uri=".$graphUri."&query=".urlencode($sparqlQuery)."&timeout=2000";
        //echo '<xmp>'.$http;die;
        //echo "<xmp>". $virtuosoPl;die;
        $result = @odbc_exec($con, $virtuosoPl);

        if (false === $result) {
            $msg = odbc_errormsg() . ' (' . odbc_error() . ')';
                    //require_once 'Erfurt/Exception.php';
                    //throw new Erfurt_Exception(
            $error = ( 'ERROR: ' . $msg . "\n" . "Query:\n" . trim($sparqlQuery)."\n");

            echo $error."\n";              

        }

        odbc_longreadlen($result, 10000000);

        $result = $this->odbcResultToArray($result);
        $row = current($result);
        $xml = current($row);
        require_once 'XmlConverter.php';
        $conv   = new XmlConverter();
        $result = $conv->toArray($xml);

        return $result;
    }




    private function odbcResultToArray($odbcResult, $columnsAsKeys = true, $rowsAsArrays = true)
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




    function executeSparqlQuery1($endpointURI, $graphUri = 'NULL',  $sparqlQuery, $timeoutms=15000){

        $url = $endpointURI."/sparql?query=";
        $defaultgraphURI = (strlen($graphUri)==0)?"":"&default-graph-uri=".$graphUri;
        $format="&format=JSON";
        $url .= urlencode($sparqlQuery).$defaultgraphURI.$format;

        //return $url;
        //echo $url."\n";
        $c = curl_init();
        //$headers = array("Accept: application/sparql-results+xml");

        $headers = array("Content-Type: application/sparql-results+json");
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
        print_r($contents);die;
        curl_close($c);
        return $contents;
    }
}
