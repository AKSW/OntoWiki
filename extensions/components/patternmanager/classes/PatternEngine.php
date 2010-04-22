<?php

/**
 * Class implementing Pattern Engine
 * 
 * @copyright  Copyright (c) 2010 {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @package
 * @subpackage
 * @author     Christoph Rieß <c.riess.dev@googlemail.com>
 */

class PatternEngine {

    const PROCESS_MODE_SELECT = 1;
    
    const PROCESS_MODE_UPDATE = 2;

    const PROCESS_MODE_FULL   = 3;
    
    private $_serialization_schema = array(
        'ns'                     => 'http://ns.ontowiki.net/SysOnt/EvolutionPattern/' ,
        'PatternVariable'        => 'http://ns.ontowiki.net/SysOnt/EvolutionPattern/PatternVariable' ,
        'SelectQuery'            => 'http://ns.ontowiki.net/SysOnt/EvolutionPattern/SelectQuery' ,
        'UpdateQuery_Insert'     => 'http://ns.ontowiki.net/SysOnt/EvolutionPattern/UpdateQuery_Insert' ,
        'UpdateQuery_Delete'     => 'http://ns.ontowiki.net/SysOnt/EvolutionPattern/UpdateQuery_Delete' ,
        'SubPattern'		     => 'http://ns.ontowiki.net/SysOnt/EvolutionPattern/SubPattern' ,
        'ComplexPattern'         => 'http://ns.ontowiki.net/SysOnt/EvolutionPattern/ComplexPattern' ,
        'BasicPattern'           => 'http://ns.ontowiki.net/SysOnt/EvolutionPattern/BasicPattern' ,
        'hasPatternVariable'     => 'http://ns.ontowiki.net/SysOnt/EvolutionPattern/hasPatternVariable' ,
        'hasUpdateQuery'         => 'http://ns.ontowiki.net/SysOnt/EvolutionPattern/hasUpdateQuery' ,
        'hasBasicPattern'        => 'http://ns.ontowiki.net/SysOnt/EvolutionPattern/hasBasicPattern' ,
        'hasSubPattern'          => 'http://ns.ontowiki.net/SysOnt/EvolutionPattern/hasSubPattern' ,
        'hasSelectQuery'	     => 'http://ns.ontowiki.net/SysOnt/EvolutionPattern/hasSelectQuery' ,
        'updatePatternObject'    => 'http://ns.ontowiki.net/SysOnt/EvolutionPattern/updatePatternObject' ,
        'updatePatternPredicate' => 'http://ns.ontowiki.net/SysOnt/EvolutionPattern/updatePatternPredicate' ,
        'updatePatternSubject'   => 'http://ns.ontowiki.net/SysOnt/EvolutionPattern/updatePatternSubject' ,
        'sequenceId'			 => 'http://ns.ontowiki.net/SysOnt/EvolutionPattern/sequenceId' ,
    );

    private $_store;
    
    private $_graph;
    
    private $_versioning;
    
    /**
     * 
     */
    public function __construct() {
    
    }
    
    /**
     * 
     * @param unknown_type $erfurt
     */
    public function setBackend($erfurt) {
    
        $this->_store      = $erfurt->getStore();
        
        $this->_versioning = $erfurt->getVersioning();
        
    }
    
    /**
     * 
     */
    public function getStore() {

        return $this->_store;

    }
    
    /**
     * 
     * @param $graph
     */
    public function setDefaultGraph($graph) {
    
        $this->_graph = (string) $graph;
        
    }
    
    /**
     * 
     */
    public function getDefaultGraph() {
    
        return $this->_graph;
        
    }
    
    /**
     * 
     * @param $pattern
     * @param $mode
     */
    public function processPattern($pattern, $mode = self::PROCESS_MODE_FULL) {
    
        if ($pattern instanceof ComplexPattern) {
            $pattern->setEngine($this);
        } elseif ($pattern instanceof BasicPattern) {
            $pattern->setEngine($this);
        } else {
            throw new Erfurt_Exception('unknown input for processPattern() in PatternEngine.');
        }
        
        $vSpec = array(
        	'modeluri'      => $this->_graph,
            'type'          => 3000,
            'resourceuri'   => '*'
        );

        // starting evo pattern action
        $this->_versioning->startAction($vSpec);

        $pattern->execute();
        
        // ending evo pattern action
        $this->_versioning->endAction();

    }
    
    /**
     * 
     * @param $selectQuery
     */
    public function queryGraph($selectQuery) {

        // add from for query (dirrrty)
        $selectQuery = str_replace('WHERE','FROM <' . $this->_graph . '> WHERE', $selectQuery);
        
        $result = $this->_store->sparqlQuery($selectQuery, array('result_format' => 'extended'));
        
        return $result;
        
    }
    
    /**
     * 
     * @param $insert
     * @param $delete
     */
    public function updateGraph($insert, $delete) {

        $result = $this->_store->addMultipleStatements($this->_graph, $insert);
        
        $result = $this->_store->deleteMultipleStatements($this->_graph, $delete);
        
        return true;
        
    }
    
    /**
     * 
     * @param unknown_type $pattern
     * @param unknown_type $format
     */
    public function saveToStore($pattern, $format = 'rdf') {
        switch ($format) {
            case 'rdf' :
                return $this->saveToStoreAsRdf($pattern);
            default :
                throw new Exception('Format for pattern serialization unsupported');
        }
        
    }
    
    /**
     * 
     * @param $id
     * @param $format
     */
    public function loadFromStore($id, $format = 'rdf')
    {
        
    }

    /**
     * 
     * @param unknown_type $pattern
     */
    private function saveToStoreAsRdf($pattern) {

        // create empty statements array
        $stmt = array();
        
        // create uri and statements for ComplexPattern
        $cpatternLabel = $pattern->getLabel();
        $cpatternUri = $this->_serialization_schema['ComplexPattern'] . '/' . urlencode($cpatternLabel);
        
        $stmt[ $cpatternUri ][ EF_RDFS_LABEL ][] = array(
        	'type' => 'literal' ,
        	'value' => $cpatternLabel
        );
        
        $stmt[ $cpatternUri ][ EF_RDFS_COMMENT ][] = array(
            'type' => 'literal' ,
            'value' => $pattern->getDescription()
        );
        
        $stmt[ $cpatternUri ][ EF_RDF_TYPE ][] =  array(
        	'type' => 'uri' ,
        	'value' => $this->_serialization_schema['ComplexPattern']
        );
        
        // iterate over subpatterns (which are basic patterns with sequence id)
        foreach ($pattern->getElements() as $i => $basicPattern) {
            
            // get variables for BasicPattern
            $vars = $basicPattern->getVariables(true, false);
            
            $patternLabel =  $basicPattern->getLabel();
            $patternUri = $this->_serialization_schema['BasicPattern'] . '/' . urlencode($patternLabel);

            foreach ($vars as $variable) {
                
                $varLabel = $variable['name'];
                $varUri = $this->_serialization_schema['PatternVariable'] . '/' . $varLabel;
                if ( !array_key_exists($varUri, $stmt) ) {
	                $stmt[ $varUri ][ EF_RDF_TYPE ][] = array(
	                    'type' => 'uri' ,
	                    'value' => $this->_serialization_schema['PatternVariable'] . '_' . $variable['type']
	                );
	                
	                $stmt[ $varUri ][ EF_RDFS_LABEL ][] = array(
	                    'type' => 'literal' ,
	                    'value' => $varLabel
	                );
	                
	                $stmt[ $varUri ][ EF_RDFS_COMMENT ][] = array(
	                    'type' => 'literal' ,
	                    'value' => $variable['desc']
	                );
	                
	                $stmt[$patternUri][$this->_serialization_schema['hasPatternVariable']][] = array(
	                	'type' => 'uri' ,
	                	'value' => $varUri
	                );
                } else {
                    //do nothing
                }
                  
            }
            
            // get selection query for BasicPattern
            $select = $basicPattern->getSelectQueries();

            foreach ($select as $query) {
                
                $selectLabel = md5($query);
                $selectUri = $this->_serialization_schema['SelectQuery'] . '/' .  $selectLabel;
                $stmt[ $selectUri ] =
                array(
                    EF_RDFS_LABEL => array(
                        array ( 'type' => 'literal' , 'value' => $query)
                    )
                );
                $stmt[ $patternUri ][ $this->_serialization_schema['hasSelectQuery'] ][] =
                    array( 'type' => 'uri' , 'value' => $selectUri);
            }
            
            //get update query for BasicPattern
            $update = $basicPattern->getUpdateQueries();

            // handle insert part
            foreach ($update['INSERT'] as $insertPattern ) {
                $parts = explode(' ',$insertPattern);
                
                $insertLabel = md5($insertPattern);
                $insertUri = $this->_serialization_schema['UpdateQuery_Insert'] . '/' . $insertLabel;
                $stmt[ $insertUri ] =
                array(
                    EF_RDF_TYPE => array(
                        array('type' => 'uri', 'value' => $this->_serialization_schema['UpdateQuery_Insert'])
                    ) ,
                    $this->_serialization_schema['updatePatternSubject'] => array (
                        array('type' => 'literal' , 'value' => $parts[0])
                    ) ,
                    $this->_serialization_schema['updatePatternPredicate'] => array (
                        array('type' => 'literal' , 'value' => $parts[1])
                    ) ,
                    $this->_serialization_schema['updatePatternObject'] => array (
                        array('type' => 'literal' , 'value' => $parts[2])
                    ) ,
                );
                $stmt[ $patternUri ][ $this->_serialization_schema['hasUpdateQuery'] ][] = 
                    array( 'type' => 'uri' , 'value' => $insertUri);
            }
            
            // handle delete part
            foreach ($update['DELETE'] as $deletePattern ) {
                $parts = explode(' ',$deletePattern);
                
                $deleteLabel = md5($deletePattern);
                $deleteUri = $this->_serialization_schema['UpdateQuery_Delete'] . '/' . $deleteLabel;
                $stmt[ $deleteUri ] =
                array(
                    EF_RDF_TYPE => array(
                        array('type' => 'uri', 'value' => $this->_serialization_schema['UpdateQuery_Delete'])
                    ) ,
                    $this->_serialization_schema['updatePatternSubject'] => array (
                        array('type' => 'literal' , 'value' => $parts[0])
                    ) ,
                    $this->_serialization_schema['updatePatternPredicate'] => array (
                        array('type' => 'literal' , 'value' => $parts[1])
                    ) ,
                    $this->_serialization_schema['updatePatternObject'] => array (
                        array('type' => 'literal' , 'value' => $parts[2])
                    ) ,
                );
                $stmt[ $patternUri ][ $this->_serialization_schema['hasUpdateQuery'] ][] = 
                    array( 'type' => 'uri' , 'value' => $deleteUri);
            }
            
            $stmt[$patternUri][EF_RDF_TYPE][] = array (
            	'type' => 'uri' ,
            	'value' => $this->_serialization_schema['BasicPattern']
            );
            
            $subPatternUri = $patternUri . '/' . $i;
            
            $stmt[ $subPatternUri ][ EF_RDF_TYPE ][] =
                array('type' => 'uri' , 'value' => $this->_serialization_schema['SubPattern']);
            $stmt[ $subPatternUri ][ $this->_serialization_schema['sequenceId'] ][] =
                array('type' => 'literal' , 'value' => $i);
            $stmt[ $subPatternUri ][ $this->_serialization_schema['hasBasicPattern'] ][] =
                array('type' => 'uri' , 'value' => $patternUri);
            $stmt[ $cpatternUri ][  $this->_serialization_schema['hasSubPattern'] ][] =
                array('type' => 'uri' , 'value' => $subPatternUri );
        }
        
        $this->_store->addMultipleStatements( $this->_serialization_schema['ns'], $stmt, false);
    }
    
}
