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
            
            $stmt[ $patternUri ][ EF_RDFS_LABEL ][] = array( 
            	'type' => 'literal' ,
            	'value' => $basicPattern->getLabel()
            );
            $stmt[ $patternUri ][ EF_RDFS_COMMENT ][] = array(
            	'type' => 'literal' ,
            	'value' => $basicPattern->getDescription()
            );

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
                } else {
                    //do nothing
                }
                
                $stmt[$patternUri][$this->_serialization_schema['hasPatternVariable']][] = array(
	                'type' => 'uri' ,
	                'value' => $varUri
	            );
                  
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
    
    /**
     * 
     * @param $uri
     */
    public function loadFromStoreAsRdf($uri) {
	    
	    $prefix = '';
	    $prefixlen = 0;
	    
        $upart = explode('/',$uri);
        for ($i = 0; $i < sizeof($upart); $i++) {
            $prefix = implode('/',array_slice($upart,0,$i));
            if ( in_array( $prefix, $this->_serialization_schema ) ) {
                $prefixlen = strlen($prefix);
            }
        }
        
        if ( $uri[$prefixlen] === '#' || $uri[$prefixlen] === '/' ) {
            $label = $uri[$prefixlen] . urlencode(substr($uri, $prefixlen + 1));
        } else {
            $label = urlencode(substr($uri,$prefixlen));
        }
        
        $uri = $prefix . $label;
	        
        
        $query = new Erfurt_Sparql_Query2();
        
        $query->addFrom($this->_serialization_schema['ns']);
        
        $vars = array();
        $vars['S']  = new Erfurt_Sparql_Query2_Var('S');
        $vars['P']  = new Erfurt_Sparql_Query2_Var('P');
        $vars['O']  = new Erfurt_Sparql_Query2_Var('O');
        $vars['K']  = new Erfurt_Sparql_Query2_Var('K');
                                                        
        $vars['sp'] = new Erfurt_Sparql_Query2_Var('sp');
        $vars['sb'] = new Erfurt_Sparql_Query2_Var('sb');
        
        $uris = array();
        $uris['in'] = new Erfurt_Sparql_Query2_IriRef($uri);
        
        $query->addProjectionVar($vars['S']);
        $query->addProjectionVar($vars['P']);
        $query->addProjectionVar($vars['O']);
        
        $union = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern();
        
        $pattern = new Erfurt_Sparql_Query2_GroupGraphPattern();
        
        $pattern->addTriple(
            $uris['in'],
            new Erfurt_Sparql_Query2_IriRef($this->_serialization_schema['hasSubPattern']),
            $vars['sp']
        );        
        $pattern->addTriple(
            $vars['sp'],
            new Erfurt_Sparql_Query2_IriRef($this->_serialization_schema['hasBasicPattern']),
            $vars['sb']
        );
        $pattern->addTriple(
            $vars['sb'],
            new Erfurt_Sparql_Query2_IriRef($this->_serialization_schema['hasUpdateQuery']),
            $vars['K']
        );
        $pattern->addTriple(
            $vars['S'],
            $vars['P'],
            $vars['O']
        );
        $pattern->addFilter( new Erfurt_Sparql_Query2_sameTerm($vars['S'], $vars['K']) );
        
        $union->addElement($pattern);
        
        $pattern = new Erfurt_Sparql_Query2_GroupGraphPattern();
        
        $pattern->addTriple(
            $uris['in'],
            new Erfurt_Sparql_Query2_IriRef($this->_serialization_schema['hasSubPattern']),
            $vars['sp']
        );        
        $pattern->addTriple(
            $vars['sp'],
            new Erfurt_Sparql_Query2_IriRef($this->_serialization_schema['hasBasicPattern']),
            $vars['sb']
        );
        $pattern->addTriple(
            $vars['sb'],
            new Erfurt_Sparql_Query2_IriRef($this->_serialization_schema['hasSelectQuery']),
            $vars['K']
        );
        $pattern->addTriple(
            $vars['S'],
            $vars['P'],
            $vars['O']
        );
        $pattern->addFilter( new Erfurt_Sparql_Query2_sameTerm($vars['S'], $vars['K']) );
        
        $union->addElement($pattern);
        
        $pattern = new Erfurt_Sparql_Query2_GroupGraphPattern();
        
        $pattern->addTriple(
            $uris['in'],
            new Erfurt_Sparql_Query2_IriRef($this->_serialization_schema['hasSubPattern']),
            $vars['sp']
        );        
        $pattern->addTriple(
            $vars['sp'],
            new Erfurt_Sparql_Query2_IriRef($this->_serialization_schema['hasBasicPattern']),
            $vars['sb']
        );
        $pattern->addTriple(
            $vars['sb'],
            new Erfurt_Sparql_Query2_IriRef($this->_serialization_schema['hasPatternVariable']),
            $vars['K']
        );
        $pattern->addTriple(
            $vars['S'],
            $vars['P'],
            $vars['O']
        );
        $pattern->addFilter( new Erfurt_Sparql_Query2_sameTerm($vars['S'], $vars['K']) );
        
        $union->addElement($pattern);

        $pattern = new Erfurt_Sparql_Query2_GroupGraphPattern();
        
        $pattern->addTriple(
            $uris['in'],
            new Erfurt_Sparql_Query2_IriRef($this->_serialization_schema['hasSubPattern']),
            $vars['sp']
        );        
        $pattern->addTriple(
            $vars['sp'],
            new Erfurt_Sparql_Query2_IriRef($this->_serialization_schema['hasBasicPattern']),
            $vars['K']
        );
        $pattern->addTriple(
            $vars['S'],
            $vars['P'],
            $vars['O']
        );
        $pattern->addFilter( new Erfurt_Sparql_Query2_sameTerm($vars['S'], $vars['K']) );
        
        $union->addElement($pattern);

        $pattern = new Erfurt_Sparql_Query2_GroupGraphPattern();
        
        $pattern->addTriple(
            $uris['in'],
            new Erfurt_Sparql_Query2_IriRef($this->_serialization_schema['hasSubPattern']),
            $vars['K']
        );        
        $pattern->addTriple(
            $vars['S'],
            $vars['P'],
            $vars['O']
        );
        $pattern->addFilter( new Erfurt_Sparql_Query2_sameTerm($vars['S'], $vars['K']) );
        
        $union->addElement($pattern);
        
        $pattern = new Erfurt_Sparql_Query2_GroupGraphPattern();
            
        $pattern->addTriple(
            $vars['S'],
            $vars['P'],
            $vars['O']
        );
        $pattern->addFilter( new Erfurt_Sparql_Query2_sameTerm($uris['in'], $vars['S']) );
        
        $union->addElement($pattern);
        
        $pattern = new Erfurt_Sparql_Query2_GroupGraphPattern();
        $pattern->addElement($union);
        
        $query->setWhere($pattern);

        $info = $this->_store->sparqlQuery($query);
        
        foreach ($info as $result) {
            
            if ($result['P'] === EF_RDF_TYPE) {
                $types[md5($result['S'])] = $result['O'];
            }
            
            $data[md5($result['S'])][$result['P']][] = $result['O'];
            $resources[md5($result['S'])] = $result['S'];
            
        }
        
        $hash = array_search($this->_serialization_schema['ComplexPattern'], $types);
        
        $complexPattern = new ComplexPattern();
        
        $complexPattern->setLabel($data[$hash][EF_RDFS_LABEL][0]);
        $complexPattern->setDescription($data[$hash][EF_RDFS_COMMENT][0]);
        
        function resolveRecursive($hash,$resources,$data) {
            
             $ret = array();
            
            foreach ($data[$hash] as $p => $x) {
                foreach ($x as $val) {
                    if (array_key_exists(md5($val),$resources) ) {
                        $ret[$p][] = resolveRecursive(md5($val),$resources,$data);
                    }  else {
                        $ret[$p][] = $val;
                    }
                }
            }
            
            return $ret;
            
        }
        
        $pdata = resolveRecursive($hash,$resources,$data);
        
        foreach ($pdata[$this->_serialization_schema['hasSubPattern']] as $sp) {
            $i = $sp[$this->_serialization_schema['sequenceId']][0];
            $bp = $sp[$this->_serialization_schema['hasBasicPattern']][0];
            
            $basicPattern = new BasicPattern();
            
            foreach ($bp[$this->_serialization_schema['hasPatternVariable']] as $var) {
                $name = $var[EF_RDFS_LABEL][0];
                $desc = $var[EF_RDFS_LABEL][0];
                $type = substr($var[EF_RDF_TYPE][0],strlen($this->_serialization_schema['PatternVariable']) + 1); 
                $basicPattern->addVariable($name,$type,$desc);
            }
            
            foreach ($bp[$this->_serialization_schema['hasSelectQuery']] as $var) {
                $basicPattern->addSelectQuery($var[EF_RDFS_LABEL][0]);
            }
            
            foreach ($bp[$this->_serialization_schema['hasUpdateQuery']] as $var) {
                switch ($var[EF_RDF_TYPE][0]) {
                    case $this->_serialization_schema['UpdateQuery_Insert']:
                        $type = 'INSERT';
                        break;
                    case $this->_serialization_schema['UpdateQuery_Delete']:
                        $type = 'DELETE';
                        break;
                    default:
                        break;
                }
                $query  = $var[$this->_serialization_schema['updatePatternSubject']][0] . ' ';
                $query .= $var[$this->_serialization_schema['updatePatternPredicate']][0] . ' ';
                $query .= $var[$this->_serialization_schema['updatePatternObject']][0];
                $basicPattern->addUpdateQuery($query,$type);
            }

            $basicPattern->setLabel($bp[EF_RDFS_LABEL][0]);
            $basicPattern->setDescription($bp[EF_RDFS_COMMENT][0]);

            $complexPattern->setElement((int)$sp[$this->_serialization_schema['sequenceId']][0],$basicPattern);
            
        }
        var_dump($complexPattern);
    }
    
}
