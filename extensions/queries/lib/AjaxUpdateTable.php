<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

//require_once("lib/Sajax.php");
require_once("PrefixHandler.php");
require_once("Options.php");
require_once("QueryOptimizer.php");

/**
 * @category   OntoWiki
 * @package    Extensions_Queries_Lib
 */
class AjaxUpdateTable {


    var $debug = false;
    var $options;
    var $ph;
    var $patterns;
    var $limit;

    public function __construct( $json, $limit, $config,  $debug = false)
    {

        $this->options = new Options($config);
        $owApp = OntoWiki::getInstance();
        $graphUri = $owApp->selectedModel->getModelIri();
        $this->options->add("graphUri", $graphUri );

        $this->ph = new PrefixHandler($this->options);

        $json = str_replace('\"','"', str_replace("\'","'",$json));
        //echo $json;
        $this->patterns = json_decode($json, true );
        //print_r($json); die;
        $this->limit = $limit;
    }

    public function getPrefixHandler(){
        return $this->ph;
    }

    /**
     * to return sparql query result as array
     */
    public function getResultAsArray()
    {
        $owApp = OntoWiki::getInstance();
        $store  = $owApp->erfurt->getStore();

        $qo = new QueryOptimizer($this->patterns,  $this->limit, $this->options, $this->ph);
        $query = $qo->getSPARQLQuery();
        try{
            $sparqlResult = $store->sparqlQuery($query, array('use_ac' => false));
        }catch (Exception $e){
            $errorMsg =  "SPARQL query failed: normally the following characters are not allowed in the input fields:\n ()\n";
            return $errorMsg. ($e->getMessage());
        }
        return $sparqlResult;
    }

    function updateTable() {
        $owApp = OntoWiki::getInstance();
        $store  = $owApp->erfurt->getStore();
        //$q="";
        $qo = new QueryOptimizer($this->patterns,  $this->limit, $this->options, $this->ph);
        $query = $qo->getSPARQLQuery();
        //$execute= new SPARQLExecuter($this->options);


        $json = $store->sparqlQuery($query, array('result_format' => 'json', 'use_ac' => false));
        //$json = str_replace('\"','"', str_replace("\'","'",$json));

        $jsonArray = json_decode($json, true );
        //echo "<xmp>";
        //print_r($jsonArray);die;
        //$jsonArray = $execute->executeSparqlQuery( $graphURI,$sparqlQuery);
        $table = $this->toTable($jsonArray, $query);

        return $table;
    }

    function getSPARQLQuery(){

        $qo = new QueryOptimizer($this->patterns, $this->limit, $this->options, $this->ph);
        //$qo = new QueryOptimizer($this->patterns, $q, $this->limit, $this->options);
        return $qo->getSPARQLQuery();
    }

    function getQueryOptimizer(){

        return $qo;
    }

    function toTable($jsonArray, $query){

        //echo "<xmp>";
        //var_dump($jsonArray);exit();
        if(!is_array($jsonArray)){
            //$jsonArray = str_replace("\n","<br>",$jsonArray);
            return "<thead> <tr> <th>Error</th> </tr> </thead> <tbody> <tr> <td><xmp>".$jsonArray."</xmp></td> </tr> </tbody> ";
        }
        $vars = $jsonArray['head']['vars'];
        //TODO only keep as long as mysql is broken
        $vars = self::nicenVars($vars);

        $bindings= $jsonArray['results']['bindings'];


        if(count($bindings) == 0){
            return "<thead> <tr> <th>Empty result set</th> </tr> </thead> <tbody> <tr> <td><xmp>".$query->__toString()."</xmp></td> </tr> </tbody> ";
        }

        $table = "<thead> <tr> \n";

        foreach ($vars as $var){
            $table.="<th>".$var."</th>\n";
            //echo $table;
        }
        $table.="</tr> </thead> <tbody> \n ";

        foreach ($bindings as $binding){

            $table.="<tr>\n";
            foreach ($vars as $var){
                $value = $binding[$var]['value'];
                //print_r($binding);die;
                if(($binding[$var]['type']) == "uri"){
                    $link = "";
                    if(($tmp=$this->ph->getLinkedDataURI($value))!==0){
                        $link = "<a href=\"".$tmp."\" target=\"_blank\" >";
                        $link.= "&nbsp;&nbsp;<img src=\"css/link.png\" title=\"Show LinkedData\" ></a>";
                    }

                    $field = $this->ph->applyPrefixToURIString($value).$link;
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

    static function startsWith($Haystack, $Needle){
        // Recommended version, using strpos
        return strpos($Haystack, $Needle) === 0;
    }

    //TODO only keep as long as mysql is broken
    function nicenVars($vars) {

        $ret = array();
        foreach ($vars as $varString){
            if ( $varString[0] === '?') {
                $ret[] = substr($varString, 1);
            } else {
                $ret[] = $varString;
            }
        }
        return $ret;

    }
}
