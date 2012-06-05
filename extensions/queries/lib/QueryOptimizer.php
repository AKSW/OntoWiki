<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once("PrefixHandler.php");
require_once("Options.php");

/**
 * @category   OntoWiki
 * @package    Extensions_Queries_Lib
 */
class QueryOptimizer{


    var $options;
    var $q;
    var $limit;
    //var $jsonPatterns;
    var $patterns;
    var $currentType;
    var $ph;
    var $filterterms=array();
    var $varterms=array();
    var $graphUri;
    var $tmpvarcounter = 0 ;
    var $propns ;
    var $resns;



    function __construct( $patterns, $limit,  $options, $prefixhandler){
        $this->patterns = $patterns;
        //$this->q = $q;
        $this->limit = $limit;
        $this->options = $options;
        //print_r($options);
        $this->ph = $prefixhandler;
        $this->graphUri = $this->options->get("graphUri");
        if(	$this->options->get('mode')=='dbpedia'){
            $this->propns = "http://dbpedia.org/property/";
            $this->resns = "http://dbpedia.org/resource/";
        }
        else{
            $this->propns =$options->getDefaultNamespace();
            $this->resns = $options->getDefaultNamespace();

        }


        //$this->jsonPatterns = $patterns;
    }





    //this function sets currenttype and q
    function patternsToQueryReplaceCurrent(){
        $result = "";

        foreach ($this->patterns as $pattern){
            $s = $pattern['s'];
            $p = $pattern['p'];
            $o = $pattern['o'];
            //print_r($patterns);

            if(isset($pattern['search'] )){
                if($pattern['search'] == "s"){
                    $this->currentType = "s";
                    $this->q = $s;
                    $s = "?suggest";}
                else if($pattern['search'] == "p"){
                    $this->currentType = "p";
                    $this->q = $p;
                    $p = "?suggest";}
                else if($pattern['search'] == "o"){
                    $this->currentType = "o";
                    $this->q = $o;
                    $o = "?suggest";}

            }


            $result.="\t";
            $result.= $this->patternPart2SPARQL($s,  "s" )." ";
            $result.= $this->patternPart2SPARQL($p,   "p" )." ";
            $result.= $this->patternPart2SPARQL($o,  "o", $pattern)." . ";
            $result.="\n";

        }//end foreach
        return $result;
    }


    function getAutocompleteQuery(){
        //print_r($this->patterns);
        $this->patterns = $this->patternsToQueryReplaceCurrent();
        $useCountInQuery= $this->options->get('usecount');
        $useOrderBy = $this->options->get('useorderby');
        $strategy = $this->options->get('strategy');
        $defaultlimit = $this->options->get('defaultlimit');

        require_once 'Erfurt/Sparql/SimpleQuery.php';
        $query = new Erfurt_Sparql_SimpleQuery();
        $query->setLimit($defaultlimit);

        if($useCountInQuery){
            $query->setProloguePart("SELECT ?suggest  count(?suggest) as ?count ");
            if($useOrderBy)$query->setOrderClause( "DESC(?count)");
            //$end = "} ". (($useOrderBy)?"ORDER BY DESC(?count) ":""). "LIMIT ".$this->limit;
        }else{
            $d = ($this->options->get('usedistinct'))?' DISTINCT ':''; 
            $query->setProloguePart("SELECT $d ?suggest ");
            //$end = "} ";
        }
        $where = " ".trim($this->patterns)." ";



        if($strategy == "bifcontains"){
            $where .= $this->getBifConstains();
        }else if($strategy == "regex"){
            $this->addRegexToFilter();

        }else if($strategy == "virtuoso"){
            $this->addLikeToFilter();
            //$middle = $this->getLikeFilter();
        }

        //$filter = "";
        if(count($this->filterterms)>=1) {
            $where .= "\tFILTER \n".QueryOptimizer::expandTerms($this->filterterms)." .\n";
        }

        //print_r($query->__toString());die;

        $query ->addFrom((string) $this->graphUri);
        $query ->setWherePart("WHERE { \n".$where." \n} ");

        return $query;
    }



    function getBifConstains(){
        $result  =  "\t?suggest rdfs:label ?lit. \n";
        $result .=  "\t?lit bif:contains '\"".$this->q."*\"'. \n";
        return $result;
    }	


    function addRegexToFilter( ){
        $rawsearchword = trim($this->q);
        $currentType = $this->currentType;
        //is empty 
        if(strlen($rawsearchword)== 0 ){
            return "";
        }
        $encoded = $this->prepareSearchword($rawsearchword);
        $final = self::firstToLowerAndUpper($encoded);
        if($currentType == "s" || $currentType == "p"){
            $this->filterterms[] = "regex(str(?suggest),'http://.*[#|/]".$final.".*')";
        }else{
            $arr = array();
            $arr[] = "regex(str(?suggest),'http://.*[#|/]".$final.".*')";
            $arr[] =  "regex(?suggest,'^".$final.".*')";
            $this->filterterms[] = QueryOptimizer::expandTerms($arr, "||");
            //$this->filterterms[] = "regex(?suggest,'^J')";
        }
        //$this->filterterms[] = "regex(str(?suggest),'http://.*j.*')";
        //return "\tFILTER regex(str(?suggest),'http://.*".$this->q.".*').\n";
    }

    function prepareSearchword($search){
        //	 echo $search; 
        $currentType = $this->currentType;
        $encoded = $search;

        if($currentType == "s" || $currentType=="p"){
            if($this->ph->isPrefixedURI($encoded )){
                $encoded = $this->ph->removePrefixFromURI($encoded);
            }
            $encoded = urlencode($encoded);
        }
        if($currentType == "o" && $this->ph->isPrefixedURI($encoded))	{
            $encoded = $this->ph->removePrefixFromURI($encoded);
            $encoded = urlencode($encoded);
        }
        //echo $q; die;
        if($this->options->get("mode")=="dbpedia"){
            $encoded = str_replace("Category%3A","Category:",$encoded);
        }

        return $encoded;
    }	

    function addLikeToFilter(){
        $rawsearchword = trim($this->q);
        $currentType = $this->currentType;
        //is empty 
        if(strlen($rawsearchword)== 0 ){
            return "";
        }

        $encoded = $this->prepareSearchword($rawsearchword);
        //TODO big letters after _	
        /*
           $up = strtoupper($q[0]);
           $down = strtolower($q[0]);
           $new = ($up == $down)?$up:"[".$up."|".$down."]";
           $qo = $new.substr($q,1);
           $qp = $down.substr($q,1);
           $q = $up.substr($q,1);
         */

        if($currentType == "s"){
            $final = self::firstToUpper($encoded);
            $arr = array();
            if($this->options->get("mode")=="dbpedia"){
                $ns = $this->options->get("ssearch");
                foreach($ns as $one){
                    $arr[] = "?suggest LIKE <".$one.$final."%> ";
                }
            }else{
                $arr[] = "?suggest LIKE <%/".$final."%> ";
                $arr[] = "?suggest LIKE <%#".$final."%> ";
            }
            $this->filterterms[] = QueryOptimizer::expandTerms($arr, "||");
            //return "\tFILTER (?suggest LIKE 'http://dbpedia.org/resource/".$q."%' ).\n";
        }else if($currentType == "p"){
            $final = self::firstToLower($encoded);
            $arr = array();
            if($this->options->get("mode")=="dbpedia"){
                $ns = $this->options->get("psearch");
                foreach($ns as $one){
                    $arr[] = "?suggest LIKE <".$one.$final."%> ";
                }
            }else{
                $arr[] = "?suggest LIKE <%/".$final."%> ";
                $arr[] = "?suggest LIKE <%#".$final."%> ";
            }
            $this->filterterms[] = QueryOptimizer::expandTerms($arr, "||");
            //$tmp = "\tFILTER (";
            //$arr[] = "?suggest LIKE 'http://dbpedia.org/ontology/".$q."%' ";
            //$arr[] = "?suggest LIKE 'http://dbpedia.org/property/".$q."%' ";
            //$arr[] = "?suggest LIKE <http://dbpedia.org/property/".$q."> ";
            //$arr[] = "?suggest LIKE 'http://xmlns.com/foaf/0.1/".$q."%' ";
            //$tmp.= " ).\n";
            //return $tmp;
        }else if($currentType == "o"){
            $final1 = self::firstToUpper($encoded);
            $final2 = self::firstToLowerAndUpper($encoded);
            $arr = array();
            if($this->options->get("mode")=="dbpedia"){
                $ns = $this->options->get("osearch");
                foreach($ns as $one){
                    $arr[] = "?suggest LIKE <".$one.$final1."%> ";
                }
            }else{
                $arr[] = "?suggest LIKE <%/".$final1."%> ";
                $arr[] = "?suggest LIKE <%#".$final1."%> ";

            }
            $arr[] = "?suggest LIKE '".$final2."%' ";
            $this->filterterms[] = QueryOptimizer::expandTerms($arr, "||");


        }
    }

    static function firstToUpper($q){
        if($q == "")return "";
        $up = strtoupper($q[0]);
        return $up.substr($q,1);
    }
    static function firstToLower($q){
        if($q == "")return "";
        $down = strtolower($q[0]);
        return $down.substr($q,1);
    }
    static function firstToLowerAndUpper($q){
        if($q == "")return "";
        $up = strtoupper($q[0]);
        $down = strtolower($q[0]);
        $both = ($up == $down)?$up:"[".$up."|".$down."]";
        return $both.substr($q,1);
    }

    static function expandTerms ($terms, $operator = "&&"){
        $result="";
        for ($x=0;$x<count($terms);$x++){

            $result.= "(".$terms[$x].")";
            $result.= ($x+1==count($terms)) ? "" : " ".$operator." ";
            $result.= "\n";

        }
        return "(".$result.")";
    }


    /*
       static function getCurrentType($patterns){
       select q."P" as p, count(p) as c, i."RI_NAME"
       from "DB"."DBA"."RDF_QUAD" as q, "DB"."DBA"."RDF_IRI" as i
       WHERE {i==p}
       GROUP BY p ORDER by c DESC

       $result = "";
       foreach ($patterns as $pattern){
       if(isset($pattern['current'])){
       return $pattern['current'];
       }

       }//end foreach
       return "";
       }*/

    function getSPARQLQuery(){

        $querypatterns = $this->patternsToQuery($this->patterns);
        $vars = implode(" ", array_unique ( $this->varterms));
        $prologue = "SELECT ".$vars."" ;
        $where = "";
        $where .="WHERE { \n".$querypatterns;
        if(count($this->filterterms)>=1) {
            $where .= "\tFILTER \n".QueryOptimizer::expandTerms($this->filterterms)." .\n";
        }
        $where .=" } LIMIT ".$this->limit;

        require_once 'Erfurt/Sparql/SimpleQuery.php';
        $query = new Erfurt_Sparql_SimpleQuery();
        $query->setProloguePart($prologue)
            ->addFrom((string) $this->graphUri)
            ->setWherePart($where);
        return $query;

    }	

    function patternsToQuery(){
        $result = "";
        //print_r($this->patterns);die;
        foreach ($this->patterns as $pattern){
            $s = $pattern['s'];
            $p = $pattern['p'];
            $o = $pattern['o'];
            //echo "pat";
            //print_r($pattern);

            if(self::startsWith($s,'?')){
                $this->varterms[] = $s;
            }
            if(self::startsWith($p,'?')){
                $this->varterms[] = $p;
            }
            if(self::startsWith($o,'?')){
                $this->varterms[] = $o;
            }

            $result.= "\t";
            $result.= $this->subjectPart2SPARQL($s)." ";
            $result.= $this->predicatePart2SPARQL($p)." ";
            $result.= $this->objectPart2SPARQL($o, $pattern)." . ";
            $result.= "\n";
        }
        return $result;
    }

    function subjectPart2SPARQL($part){

        if( self::startsWith($part,'?')){
            return $part;
        }

        if($this->ph->isPrefixedURI($part)){
            $encoded = $this->ph->urlencodePrefixedURI($part);
            $uri = $this->ph->expandPrefixOfURI($encoded);
            return '<'.$uri.'>';
        }
        else {
            return '<'.$this->resns.urlencode($part).'>';
        }

    }

    function predicatePart2SPARQL($part){

        if( self::startsWith($part,'?')){
            return $part;
        }
        if($this->ph->isPrefixedURI($part)){
            $encoded = $this->ph->urlencodePrefixedURI($part);
            $uri = $this->ph->expandPrefixOfURI($encoded);
            return '<'.$uri.'>';
        }
        else {
            return '<'.$this->propns.urlencode($part).'>';
        }

    }

    function objectPart2SPARQL($part, $pattern){

        if( self::startsWith($part,'?')){
            return $part;
        }

        //ATTENTION this will cause problems with 
        //literals  that accidentally  contain prefixes
        //which hopefully should never happen
        if($this->ph->isPrefixedURI($part)){
            $encoded = $this->ph->urlencodePrefixedURI($part);
            $uri =$this->ph->expandPrefixOfURI($encoded);
            return '<'.$uri.'>';
        }

        //FOR OBJECTS which are literals
        /*
           $s = str_replace("Category%3A","Category:",$s);
           $p = str_replace("Category%3A","Category:",$p);
           $o = str_replace("Category%3A","Category:",$o);
         */
        if ( isset($pattern['otype']) && $pattern['otype']=="uri") {
            if($this->ph->isPrefixedURI($part)){
                $encoded = $this->ph->urlencodePrefixedURI($part);
                $uri =$this->ph->expandPrefixOfURI($encoded);
                return '<'.$uri.'>';
            }else{
                return '<'.$this->resns.urlencode($part).'>';
            }
        }

        else if ( isset($pattern['otype']) && $pattern['otype']=="literal")  {
            $retval =  '"'.$part.'"';
            $lang =  (strlen($pattern['lang'])==0)?"":"@".$pattern['lang'];
            return $retval.$lang;
        }else if (isset($pattern['otype']) && $pattern['otype']=="typed-literal"){
            $retval = '"'.$part.'"';
            return $retval.'^^<'.$pattern['datatype'].'>';
            //todo
        }else{
            //DEFAULT the DIRTY WAY
            $retval = "?tmpobjvar".$this->tmpvarcounter;
            $this->tmpvarcounter+=1;
            $arr[] = 'str('.$retval.') =  "'.$part.'"';
            $arr[] = ''.$retval.' =  <'.$this->resns.$part.'>';
            $this->filterterms[] = $this->expandTerms($arr, "||");
            return $retval;
        }
    }

    function patternPart2SPARQL($part,  $type , $pattern = NULL){

        if( self::startsWith($part,'?')){
            return $part;
        }
        /*else if( self::startsWith($part,"http://")){
          return '<'.urlencode($part).'>';		
          }
         */
        if($type == "s" || $type == "p"){
            if($this->ph->isPrefixedURI($part)){
                $encoded = $this->ph->urlencodePrefixedURI($part);
                $uri = $this->ph->expandPrefixOfURI($encoded);
                return '<'.$uri.'>';
            }
            else if ($type == "s"){
                return '<'.$this->resns.urlencode($part).'>';
            }
            else if ($type == "p"){
                return '<'.$this->propns.urlencode($part).'>';
            }

        }	


        else{
            //ATTENTION this will cause problems with 
            //literals  that accidentally  contain prefixes
            //which hopefully should never happen
            if($this->ph->isPrefixedURI($part)){
                $encoded = $this->ph->urlencodePrefixedURI($part);
                $uri =$this->ph->expandPrefixOfURI($encoded);
                return '<'.$uri.'>';
            }


        }	

        //FOR OBJECTS which are literals
        /*
           $s = str_replace("Category%3A","Category:",$s);
           $p = str_replace("Category%3A","Category:",$p);
           $o = str_replace("Category%3A","Category:",$o);
         */
        if($pattern!=NULL){
            //	print_r( $pattern);
            if ( isset($pattern['otype']) && $pattern['otype']=="uri") {
                if($this->ph->isPrefixedURI($part)){
                    $encoded = $this->ph->urlencodePrefixedURI($part);
                    $uri =$this->ph->expandPrefixOfURI($encoded);

                    return '<'.$uri.'>';
                }else{
                    return '<'.$this->resns.urlencode($part).'>';
                }

            }

            if ( isset($pattern['otype']) && $pattern['otype']=="literal")  {
                //	

                $retval =  '"'.$part.'"';
                $lang =  (strlen($pattern['lang'])==0)?"":"@".$pattern['lang'];
                return $retval.$lang;
            }else if (isset($pattern['otype']) && $pattern['otype']=="typed-literal"){
                $retval = '"'.$part.'"';
                return $retval.'^^<'.$pattern['datatype'].'>';
                //todo
            }else{
                //DEFAULT the DIRTY WAY
                $retval = "?tmpobjvar".$this->tmpvarcounter;
                $this->tmpvarcounter+=1;
                $arr[] = 'str('.$retval.') =  "'.$part.'"';
                //$arr[] = 'str('.$retval.') =  "'.$this->resns.$part.'"';
                $arr[] = ''.$retval.' =  <'.$this->resns.$part.'>';

                $this->filterterms[] = $this->expandTerms($arr, "||");

                //echo $part."\n";
                //$this->varterms[]=$retval;
                return $retval;
                //	$retval = '"'.$part.'"';

                //	return $retval;
            }
            //DIRTY 
            /*	
             */	
        }



    }

    /*
       if($prefixHandler->isPrefixedURI($part)){
       return '<'.$prefixHandler->expandPrefixOfURI($part).'>';
//return $part;	
}
else if($isObject){
$retval = '"'.$part.'"';
if($pattern)
return ;

     */


private static function startsWith($Haystack, $Needle){
    // Recommended version, using strpos
    return strpos($Haystack, $Needle) === 0;
}
}
