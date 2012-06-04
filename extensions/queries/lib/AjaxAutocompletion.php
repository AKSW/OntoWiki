<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

//@session_start();
require_once("PrefixHandler.php");
require_once("Options.php");
require_once("QueryOptimizer.php");
require_once("SPARQLExecuter.php");

/**
 * @category   OntoWiki
 * @package    Extensions_Queries_Lib
 */
class AjaxAutocompletion{
    var $debug ;
    var $options;
    var $ph;
    var $q;
    var $patterns;
    var $limit;
    var $propns ;
    var $resns;
    var $query="";

    public function __construct($q, $json, $limit, $config, $debug = false){
        $this->debug =  $debug;
        $this->options = new Options($config);
        $owApp = OntoWiki::getInstance();
        $graphUri = $owApp->selectedModel->getModelIri();
        $this->options->add("graphUri", $graphUri );

        $this->ph = new PrefixHandler($this->options);

        $this->q = $q;
        $json = str_replace('\"','"', str_replace("\'","'",$json));
        $this->patterns = json_decode($json, true );
        //print_r($this->patterns);die;
        $this->limit = $limit;

        if(	$this->options->get('mode')=='dbpedia'){
            $this->propns = "http://dbpedia.org/property/";
            $this->resns = "http://dbpedia.org/resource/";
        }
        else{
            $this->propns =$this->options->getDefaultNamespace();
            $this->resns = $this->options->getDefaultNamespace();

        }
    }


    public function getAutocompletionList(){
        if($this->startsWith(trim($this->q), "?")){
            return "";
        }
        //$qo = new QueryOptimizer($this->patterns, $this->q, $this->limit, $this->options,  $this->ph);
        $qo = new QueryOptimizer($this->patterns, $this->limit, $this->options,  $this->ph);
        //$currentType = $qo->currentType;
        $sparqlQuery = $qo->getAutocompleteQuery();
        $this->query = $sparqlQuery;
        //echo "aa";
        //if($this->debug)echo($sparqlQuery->__toString());
        //if($this->debug)echo $sparqlQueryString;
        if($this->options->get("mode")=="dbpedia"){
            $execute= new SPARQLExecuter($this->options);
            $graphURI = $this->options->get('default-graph-uri');
            //echo $graphURI;die;
            $jsonArray = $execute->executeSparqlQuery( $graphURI,$sparqlQuery, 6000);
            //$jsonArray = $execute->executeSparqlQuery1( 'http://db0.aksw.org:8890/',$graphURI,$sparqlQuery, 3000);
            //print_r($jsonArray);
        }else{
            $owApp = OntoWiki::getInstance();
            $store  = $owApp->erfurt->getStore();
            $json = $store->sparqlQuery($sparqlQuery, array('result_format' => 'json', 'use_ac' => false));
            $jsonArray = json_decode($json, true );
            //$jsonArray['bindings'] = $jsonArray['results']['bindings'];
        }


        if(!is_array($jsonArray)){
            //echo "there will be a nicer error message, soon\n";
            //TODO error handling
            return $jsonArray."\n";
            die;
        }

        if(count($jsonArray['results']['bindings'])==0){
            return "no results| \n";
        }

        if($this->options->get('usecount')){
            $list = $this->toListWithCount($jsonArray);
        }else {
            $list = $this->toListNoCount($jsonArray);
        }

        if(!is_array($list)){
            //TODO error handling
            print_r( $jsonArray);
            die;
        }
        //	print_r($jsonArray);
        if(!$this->options->get('useorderby')){ 
            //uasort($list,'this->compare_by_count');
            uasort($list, array(&$this, 'compare_by_count'));
            //print_r($list);
        }
        return $this->doOutput($list);
    }

    public function getQuery(){
        return $this->query;
    }




    function compare_by_count($a, $b) {
        $sortkey = 'count';
        if ($a[$sortkey] == $b[$sortkey]){return 0;}
        else {return  ($a[$sortkey] > $b[$sortkey] ) ? -1 : 1; }

    } # sort alphabetically by name usort($data, 'compare_lastname');


    function toListWithCount($jsonArray){
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



    function toListNoCount($jsonArray){
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


        return $out;	
    }

    function doOutput($list){
        $result = "";
        $x = 0;
        foreach($list as $one){
            $uri = $one['value'];
            if($one['type'] == "uri"){
                $meta = "object";
            }else if( $one['type'] == "literal"){
                $meta = "".$one['type']."";
            }else{
                $datatype = $one['datatype'];
                if(($pos = strpos($datatype, '#' )) !== false){
                    $datatype =  substr ($datatype, $pos+1);
                    $meta = $datatype;
                }

            }
            $meta = " (".$one['count']." ".$meta.")";
            //TODO remove this
            /*
               if($this->startsWith($uri, $this->resns) 
               || $this->startsWith($uri, $this->propns)
               ){  $prefixeduri = str_replace($this->propns,"",str_replace( $this->resns,"",$uri));
               }else{

               }
             */
            $prefixeduri = $this->ph->applyPrefixToURIString($uri);
            //$localname = urldecode($this->ph->removePrefixFromURI($prefixeduri));
            $localname = urldecode($this->ph->removePrefixFromURI($prefixeduri));
            $localname = str_replace("\n","",$localname);
            $result .= '<b>'.$localname.'</b>'.$meta."|";
            $result .= urldecode($prefixeduri)."|";
            $result .= @$one['type'] ."|";
            $result .= @$one['lang'] ."|";
            $result .=  @$one['datatype']."|";
            $result .="\n";	
            if($x>=$this->limit){
                break;
            }
            $x++;

        }
        return $result;

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
}
