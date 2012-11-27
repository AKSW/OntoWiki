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
class Options {

    var $options;

    function __construct($options){
        $this->options = $options;
    }



    function get($what){
        return $this->options[$what];
    }

    function add($key, $value){
        $this->options[$key] = $value;
    }



    function getNamespaces(){
        if($this->options['mode']=='dbpedia'){
            $ns = array();
            $ns['cat'] = 'http://dbpedia.org/resource/Category:';
            $ns['db'] = 'http://dbpedia.org/resource/';
            $ns['db-ont'] = 'http://dbpedia.org/ontology/';
            $ns['dbpprop '] = 'http://dbpedia.org/property/';

            $ns['owl'] = 'http://www.w3.org/2002/07/owl#';
            $ns['rdf'] = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
            $ns['rdfs'] = 'http://www.w3.org/2000/01/rdf-schema#';

            $ns['foaf'] = 'http://xmlns.com/foaf/0.1/';
            $ns['dc'] = 'http://purl.org/dc/elements/1.1/';
            $ns['skos'] = 'http://www.w3.org/2004/02/skos/core#';
            $ns['sembib'] = 'http://semanticbible.org/ns/2006/NTNames#';
            $ns['yago'] = 'http://dbpedia.org/class/yago/';
            return $ns;
        }
        else{
            //TODO
            $owApp = OntoWiki::getInstance();
            return array_flip($owApp->selectedModel->getNamespaces());
        }
    }

    function getDefaultNamespace(){
        $owApp = OntoWiki::getInstance();

        // (CR) needed due to bug in model import in zenddb store
        $base = $owApp->selectedModel->getBaseIri();
        if ( empty($base) ) {
            $base = $owApp->selectedModel->getModelIri();
        } else {
            // do nothing
        }

        //TODO remove after issue 371 has been fixed
        if(trim($base) ==="")
            $base = $owApp->selectedModel->getModelIri();
        //print_r($owApp->selectedModel);
        //echo $owApp->selectedModel->getModelIri(); die;
        $last = $base[strlen($base)-1];
        if($last == '#' || $last =='/'){
            return $base;
        }else{
            return $base."#";
        }

    }

    /*

       function getIni($ini_file){
       global $querybuilder_ini;
       if(!isset($querybuilder_ini)){
       $querybuilder_ini =  parse_ini_file($ini_file, true);	
       }
       return $querybuilder_ini;
       }

       function getEndpointURI($ini_file){
       $querybuilder_ini = getIni($ini_file);
       return $querybuilder_ini['main']['default-endpoint-uri'];
       }

       function getDefaultgraphURI($ini_file){
       $querybuilder_ini = getIni($ini_file);
       return $querybuilder_ini['main']['default-graph-uri'];

       }

       function isUseBifContains($ini_file){
       $querybuilder_ini = getIni($ini_file);
       return $querybuilder_ini['main']['usebifcontains'];
       }

       function getStrategy($ini_file){
       $querybuilder_ini = getIni($ini_file);
       return $querybuilder_ini['main']['strategy'];
       }

       function isUseCount($ini_file){
       $querybuilder_ini = getIni($ini_file);
       return $querybuilder_ini['main']['usecount'];
       }

       function isUseOrderBy($ini_file){
       $querybuilder_ini = getIni($ini_file);
       return $querybuilder_ini['main']['useorderby'];
       }

       function isUseMeta($ini_file){
       $querybuilder_ini = getIni($ini_file);
       return $querybuilder_ini['main']['usemeta'];
       }
     */
}
