<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once("Options.php");

/**
 * @category   OntoWiki
 * @package    Extensions_Queries_Lib
 */
class PrefixHandler{

    //of the form owl -> http://www.w3.org/2002/07/owl#
    var $namespaces = array();
    var $options ;
    var $defaultnamespace;
    var $assumedFiller = false;

    function __construct($options){
        $this->options = $options;
        $this->namespaces = $options->getNamespaces();
        //TODO
        $this->defaultnamespace = $options->getDefaultNamespace();
        //echo $options->get('default-prefix')."sss".$this->defaultnamespace."\n";
        //die;
        //print_r($this->namespaces);
        //die;
    }

    function getLinkedDataURI($uri){
        $showlinks = $this->options->get('showlinks');
        $before = $this->options->get('before');
        $replace = $this->options->get('replace');
        $after = $this->options->get('after');
        if($showlinks){
            if($replace && $this->startsWith($uri, $before)){
                return str_replace($before,$after,$uri);
            }else{
                return $uri;	
            }
        }else{
            return 0;
        }
    }

    function getPrefixesForSPARQLQuery(){
        //print_r($this->namespaces);

        $keys = array_keys($this->namespaces);
        $prefix = "";
        foreach($keys  as $key){
            $prefix.= "PREFIX ".$key.": <".$this->namespaces[$key]."> \n";
        }
        return $prefix;

    }


    // replaces namespace with prefix
    function applyPrefixToURIString($uriString){
        //echo $this->defaultnamespace."\n";die;
        if($this->startsWith($uriString,$this->defaultnamespace) ){
            $uriString = str_replace($this->defaultnamespace, "", $uriString );
            return $uriString;
            if(	$uriString == $this->defaultnamespace
                    ||  ($this->assumedFiller && $uriString == substr($this->defaultnamespace,0,-1))){
                //echo $uriString."\n";
                // do nothing as uri is model
            }else {
                if(!$this->assumedFiller){
                    //echo "min one".substr($this->defaultnamespace,0,-1)."\n";
                    $filler =  substr($uriString,strlen($this->defaultnamespace),1);
                    if($filler == "#" || $filler == "/"){
                        $this->defaultnamespace .= $filler;
                        $this->assumedFiller = true;
                    }
                }
                //echo "aaa".$this->defaultnamespace."\n";
                //echo "bef".$uriString."\n";
                $uriString = str_replace($this->defaultnamespace, "", $uriString );
                //str_replace(
                //echo "aaa".$this->defaultnamespace."\n";
                //echo "af".$uriString."\n";
                return $uriString;
            }

        }
        /*$skipdefaultprefix = $this->options->get('use-default-prefix');
          if($skipdefaultprefix){
          if($this->startsWith($uriString,$this->defaultnamespace)){
          return str_replace($this->defaultnamespace,"",$uriString);
          }
          }*/

        $keys = array_keys($this->namespaces);
        foreach($keys  as $key){
            if($this->startsWith($uriString,$this->namespaces[$key] )){
                $uriString = str_replace($this->namespaces[$key],$key.":",$uriString);
                return $uriString;
            }

        }
        return $uriString;
    }


    //true if uri is prefixed
    function isPrefixedURI($uriString){
        //echo ( $uriString);
        $prefixes = array_keys($this->namespaces);
        //print_r($prefixes);
        $check = substr($uriString,0,strpos($uriString,":"));
        //echo  $check;

        //echo in_array($check,$prefixes);
        return  in_array($check,$prefixes);
    }	

    function urlencodePrefixedURI($uriString){
        $prefixes = array_keys($this->namespaces);
        //print_r($prefixes);
        $pos = strpos($uriString,":");
        $check = substr($uriString,0,$pos);
        $local = substr($uriString,$pos+1);

        //echo  $check;

        //echo in_array($check,$prefixes);
        if(  in_array($check,$prefixes))
        {
            //return str_replace("Category%3A","Category:",$check.":".urlencode($local));

            return $check.":".urlencode($local);
        }

    }



    function removePrefixFromURI($uriString){
        $prefixes = array_keys($this->namespaces);

        foreach($prefixes  as $prefix){
            if($this->startsWith($uriString, $prefix.":")){
                $uriString = str_replace($prefix.":","",$uriString);
                return $uriString;
            }

        }
        return $uriString;

    }	

    function removeNamespaceFromURI($uriString){
        foreach($this->namespaces  as $ns){
            if($this->startsWith($uriString, $ns)){
                $uriString = str_replace($ns,"",$uriString);
                return $uriString;
            }

        }
        return $uriString;

    }	

    function expandPrefixOfURI($uriString){
        $prefixes = array_keys($this->namespaces);

        foreach($prefixes  as $prefix){
            if($this->startsWith($uriString, $prefix.":")){
                $uriString = str_replace($prefix.":",$this->namespaces[$prefix],$uriString);
                return $uriString;
            }

        }
        /*if($this->options->get('use-default-prefix')){
          return $this->defaultnamespace.$uriString;
          };*/
        return $uriString;

    }

    function startsWith($Haystack, $Needle){
        // Recommended version, using strpos
        return strpos($Haystack, $Needle) === 0;
    }
}
