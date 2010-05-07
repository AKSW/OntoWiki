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
    
    private $_privateConfig;
    
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
     * @param $config
     */
    public function setConfig($config) {
        $this->_privateConfig = $config;
    }
    
    /**
     * 
     * @param unknown_type $erfurt
     */
    public function setBackend($erfurt) {
    
        $this->_store      = $erfurt->getStore();
        
        $this->_versioning = $erfurt->getVersioning();
        
        if ( $this->_store->isModelAvailable($this->_privateConfig->storeModel,false) ) {
            // do nothing
        } else {
            // disable versioning for import
            $this->_versioning->enableVersioning(false);
            // create model
            $model = $this->_store->getNewModel(
                $this->_privateConfig->storeModel,
                '',
                Erfurt_Store::MODEL_TYPE_OWL,
                false
            );
            // import data
            $this->_store->importRdf(
                $this->_privateConfig->storeModel,
                dirname(__FILE__) . '/schema/Model-EvolutionPattern.rdf',
                'rdf',
                Erfurt_Syntax_RdfParser::LOCATOR_FILE,
                false
            );
            //set to hidden
            $model->setOption($erfurt->getConfig()->sysont->properties->hidden);
            // enable versioning again
            $this->_versioning->enableVersioning(true);
        }
        
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
    public function loadFromStore($id, $format = 'rdf') {
        switch ($format) {
            case 'rdf' :
                return $this->loadFromStoreAsRdf($id);
            default :
                throw new Exception('Format for pattern serialization unsupported');
        }
    }

    /**
     * 
     * @param unknown_type $pattern
     */
    private function saveToStoreAsRdf($pattern) {

        // create empty statements array
        $stmt = array();
        
        $schema = $this->_privateConfig->rdf->toArray();
        
        // create uri and statements for ComplexPattern
        $cpatternLabel = $pattern->getLabel();
        $cpatternDesc  = $pattern->getDescription();
        $cpatternUri = $schema['ComplexPattern'] . '/' . md5($cpatternLabel);
        
        $stmt[ $cpatternUri ][ EF_RDFS_LABEL ][] = array(
        	'type' => 'literal' ,
        	'value' => $cpatternLabel
        );
        
        $stmt[ $cpatternUri ][ EF_RDFS_COMMENT ][] = array(
            'type' => 'literal' ,
            'value' => $cpatternDesc
        );
        
        $stmt[ $cpatternUri ][ EF_RDF_TYPE ][] =  array(
        	'type' => 'uri' ,
        	'value' => $schema['ComplexPattern']
        );
        
        // iterate over subpatterns (which are basic patterns with sequence id)
        foreach ($pattern->getElements() as $i => $basicPattern) {
            
            // get variables for BasicPattern
            $vars = $basicPattern->getVariables(true, false);
            
            $patternLabel =  $basicPattern->getLabel();
            $patternDesc  = $basicPattern->getDescription();
            $patternUri = $schema['BasicPattern'] . '/' . md5($basicPattern->toArray(true));
            
            $stmt[ $patternUri ][ EF_RDFS_LABEL ][] = array( 
            	'type' => 'literal' ,
            	'value' => $patternLabel
            );
            $stmt[ $patternUri ][ EF_RDFS_COMMENT ][] = array(
            	'type' => 'literal' ,
            	'value' => $patternDesc
            );

            foreach ($vars as $variable) {
                
                $varLabel = $variable['name'];
                $varUri = $schema['PatternVariable'] . '/' . md5(implode(' ',$variable));
                if ( !array_key_exists($varUri, $stmt) ) {
	                $stmt[ $varUri ][ EF_RDF_TYPE ][] = array(
	                    'type' => 'uri' ,
	                    'value' => $schema['PatternVariable'] . '_' . $variable['type']
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
                
                $stmt[$patternUri][$schema['hasPatternVariable']][] = array(
	                'type' => 'uri' ,
	                'value' => $varUri
	            );
                  
            }
            
            // get selection query for BasicPattern
            $select = $basicPattern->getSelectQueries();

            foreach ($select as $query) {
                
                $selectLabel = $query;
                $selectUri = $schema['SelectQuery'] . '/' .  md5($selectLabel);
                $stmt[ $selectUri ] =
                array(
                    EF_RDF_TYPE   => array(
                        array('type' => 'uri', 'value' => $schema['SelectQuery'])
                    ),
                    EF_RDFS_LABEL => array(
                        array ( 'type' => 'literal' , 'value' => $query)
                    )
                );
                $stmt[ $patternUri ][ $schema['hasSelectQuery'] ][] =
                    array( 'type' => 'uri' , 'value' => $selectUri);
            }
            
            //get update query for BasicPattern
            $update = $basicPattern->getUpdateQueries();

            // handle update part
            foreach ($update as $pattern ) {
                $parts = explode(' ',$pattern['pattern']);
                
                $insertLabel = $pattern['pattern'] . ' - ' . $pattern['type'];
                $insertUri = $schema['UpdateQuery_Insert'] . '/' . md5($insertLabel);
                if ($pattern['type'] === 'insert') {
                    $type = $schema['UpdateQuery_Insert'];
                } elseif( $pattern['type'] === 'delete' ) {
                    $type = $schema['UpdateQuery_Delete'];
                } else {
                    // defaulting to thing
                    $type = EF_OWL_THING;
                }
                
                $stmt[ $insertUri ] =
                array(
                    EF_RDF_TYPE => array(
                        array('type' => 'uri', 'value' => $type)
                    ) ,
                    $schema['updatePatternSubject'] => array (
                        array('type' => 'literal' , 'value' => $parts[0])
                    ) ,
                    $schema['updatePatternPredicate'] => array (
                        array('type' => 'literal' , 'value' => $parts[1])
                    ) ,
                    $schema['updatePatternObject'] => array (
                        array('type' => 'literal' , 'value' => $parts[2])
                    ) ,
                );
                $stmt[ $patternUri ][ $schema['hasUpdateQuery'] ][] = 
                    array( 'type' => 'uri' , 'value' => $insertUri);
            }
            
            $stmt[$patternUri][EF_RDF_TYPE][] = array (
            	'type' => 'uri' ,
            	'value' => $schema['BasicPattern']
            );
            
            
            $subPatternLabel = $i . ' - ' . $patternLabel;
            $subPatternUri   = $schema['BasicPattern'] . '/' . md5($subPatternLabel);
            
            $stmt[ $subPatternUri ][ EF_RDF_TYPE ][] =
                array('type' => 'uri' , 'value' => $schema['SubPattern']);
            $stmt[ $subPatternUri ][ $schema['sequenceId'] ][] =
                array('type' => 'literal' , 'value' => $i);
            $stmt[ $subPatternUri ][ $schema['hasBasicPattern'] ][] =
                array('type' => 'uri' , 'value' => $patternUri);
            $stmt[ $cpatternUri ][  $schema['hasSubPattern'] ][] =
                array('type' => 'uri' , 'value' => $subPatternUri );
        }
        
        // check if resources are existing (to prevent adding same statements over and over again)
        $keys = array_keys($stmt);
        $filter = '';
        foreach ($keys as $uri) {
            $filter .= '  sameTerm(?s,<' . $uri . '>) || ' . PHP_EOL;
        }
        $filter = substr($filter,0,strrpos($filter, '|') - 1);
        $query = 'SELECT DISTINCT ?s ?t FROM <' . $this->_privateConfig->storeModel . '> WHERE {
        	?s ?p ?o . ?s <' . EF_RDF_TYPE . '> ?t .
        	FILTER (' . $filter . ') }';

        $data = $this->_store->sparqlQuery($query);
        
        foreach ($data as $row) {
            unset($stmt[$row['s']]);
        }

        $this->_store->addMultipleStatements( $this->_privateConfig->storeModel, $stmt, false);
    }
    
    /**
     * 
     * @param $uri
     */
    public function loadFromStoreAsRdf($uri) {
	    
	    $prefix = '';
	    $prefixlen = 0;
	    
	    $schema = $this->_privateConfig->rdf->toArray();
	    
        $upart = explode('/',$uri);
        for ($i = 0; $i < sizeof($upart); $i++) {
            $prefix = implode('/',array_slice($upart,0,$i));
            if ( in_array( $prefix, $schema ) ) {
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
        
        $query->addFrom($this->_privateConfig->storeModel);
        
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
            new Erfurt_Sparql_Query2_IriRef($schema['hasSubPattern']),
            $vars['sp']
        );        
        $pattern->addTriple(
            $vars['sp'],
            new Erfurt_Sparql_Query2_IriRef($schema['hasBasicPattern']),
            $vars['sb']
        );
        $pattern->addTriple(
            $vars['sb'],
            new Erfurt_Sparql_Query2_IriRef($schema['hasUpdateQuery']),
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
            new Erfurt_Sparql_Query2_IriRef($schema['hasSubPattern']),
            $vars['sp']
        );        
        $pattern->addTriple(
            $vars['sp'],
            new Erfurt_Sparql_Query2_IriRef($schema['hasBasicPattern']),
            $vars['sb']
        );
        $pattern->addTriple(
            $vars['sb'],
            new Erfurt_Sparql_Query2_IriRef($schema['hasSelectQuery']),
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
            new Erfurt_Sparql_Query2_IriRef($schema['hasSubPattern']),
            $vars['sp']
        );        
        $pattern->addTriple(
            $vars['sp'],
            new Erfurt_Sparql_Query2_IriRef($schema['hasBasicPattern']),
            $vars['sb']
        );
        $pattern->addTriple(
            $vars['sb'],
            new Erfurt_Sparql_Query2_IriRef($schema['hasPatternVariable']),
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
            new Erfurt_Sparql_Query2_IriRef($schema['hasSubPattern']),
            $vars['sp']
        );        
        $pattern->addTriple(
            $vars['sp'],
            new Erfurt_Sparql_Query2_IriRef($schema['hasBasicPattern']),
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
            new Erfurt_Sparql_Query2_IriRef($schema['hasSubPattern']),
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
        
        $hash = array_search($schema['ComplexPattern'], $types);
        
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
        
        if (!isset($pdata[$schema['hasSubPattern']])) {
            $pdata[$schema['hasSubPattern']] = array();
        }
        
        foreach ($pdata[$schema['hasSubPattern']] as $sp) {
            $i = $sp[$schema['sequenceId']][0];
            $bp = $sp[$schema['hasBasicPattern']][0];
            
            $basicPattern = new BasicPattern();
            
            // check if there are pattern variables (init empty else)
            if (!isset($bp[$schema['hasPatternVariable']])) {
                $bp[$schema['hasPatternVariable']] = array();
            }
            
            foreach ($bp[$schema['hasPatternVariable']] as $var) {
                $name = $var[EF_RDFS_LABEL][0];
                $desc = $var[EF_RDFS_COMMENT][0];
                $type = substr($var[EF_RDF_TYPE][0],strlen($schema['PatternVariable']) + 1); 
                $basicPattern->addVariable($name,$type,$desc);
            }
            
            // check if there are pattern select queries (init empty else)
            if (!isset($bp[$schema['hasSelectQuery']])) {
                $bp[$schema['hasSelectQuery']] = array();
            }

            foreach ($bp[$schema['hasSelectQuery']] as $var) {
                $basicPattern->addSelectQuery($var[EF_RDFS_LABEL][0]);
            }
            
            // check if there are pattern update queries (init empty else)
            if (!isset($bp[$schema['hasUpdateQuery']])) {
                $bp[$schema['hasUpdateQuery']] = array();
            }

            foreach ($bp[$schema['hasUpdateQuery']] as $var) {
                switch ($var[EF_RDF_TYPE][0]) {
                    case $schema['UpdateQuery_Insert']:
                        $type = 'insert';
                        break;
                    case $schema['UpdateQuery_Delete']:
                        $type = 'delete';
                        break;
                    default:
                        break;
                }
                $query  = $var[$schema['updatePatternSubject']][0] . ' ';
                $query .= $var[$schema['updatePatternPredicate']][0] . ' ';
                $query .= $var[$schema['updatePatternObject']][0];
                $basicPattern->addUpdateQuery($query,$type);
            }

            $basicPattern->setLabel($bp[EF_RDFS_LABEL][0]);
            $basicPattern->setDescription($bp[EF_RDFS_COMMENT][0]);

            $complexPattern->setElement((int)$sp[$schema['sequenceId']][0],$basicPattern);
            
        }
        
        return $complexPattern;
    }
    
    /**
     * 
     * @param $type
     */
    public function listFromStore($type = 'rdf') {
        
        if ($type === 'rdf') {
            
            $schema = $this->_privateConfig->rdf->toArray();
            
            if ($this->_store->isModelAvailable($this->_privateConfig->storeModel, false) ) {
                
                $model = $this->_privateConfig->storeModel;
                
		        $query = new Erfurt_Sparql_Query2();
		        $query->addFrom($model);
		        $query->addTriple(
		            new Erfurt_Sparql_Query2_Var('pattern'),
		            new Erfurt_Sparql_Query2_IriRef(EF_RDF_TYPE),
		            new Erfurt_Sparql_Query2_IriRef($schema['ComplexPattern'])
		        );
		        $query->addTriple(
		            new Erfurt_Sparql_Query2_Var('pattern'),
		            new Erfurt_Sparql_Query2_IriRef(EF_RDFS_LABEL),
		            new Erfurt_Sparql_Query2_Var('label')
		        );
		        
		        // TODO Paging
		        //$query->setLimit($limit + 1);
		        //$query->setOffset($offset);
		        
		        $result  = array();
		        $count   = 0;
		        $nrArray = array();

		        foreach ($this->_store->sparqlQuery($query,array(STORE_USE_AC => false)) as $row) {
		            
		            if ( !array_key_exists($row['pattern'],$nrArray) ) {
		                $result[$row['pattern']]['uri'] = $row['pattern'];
		                $nrArray[$row['pattern']] = $count;
		            }
		            
		            $result[$row['pattern']]['label'] = $row['label'];
		
		            $execUrl = new OntoWiki_Url(array('controller' => 'patternmanager','action' => 'exec'));
		            $execUrl->setParam('pattern', $row['pattern']);
		            $result[$row['pattern']]['exec_url'] = (string) $execUrl;
		                
		            $viewUrl = new OntoWiki_Url(array('controller' => 'patternmanager','action' => 'view'));
		            $viewUrl->setParam('pattern', $row['pattern']);
		            $result[$row['pattern']]['view_url'] = (string) $viewUrl;
                }

                return $result;
        
            }
            
        } else {
            throw new Exception('Format for pattern serialization unsupported');
        }
        
    }
    
    /**
     * 
     */
    private function getIdentifiers() {
        $query = 'SELECT DISTINCT ?s FROM <' . $this->_privateConfig->storeModel . '> WHERE {
        	?s a ?x . 
        	FILTER (
        		!sameTerm(?x,<' . EF_OWL_CLASS . '>) &&
        		!sameTerm(?x,<' . EF_OWL_DATATYPE_PROPERTY . '>) &&
        		!sameTerm(?x,<' . EF_OWL_OBJECT_PROPERTY . '>) &&
        		!sameTerm(?x,<' . EF_OWL_ONTOLOGY . '>)
        	) }';
        
        $info = $this->_store->sparqlQuery($query);
        
        $ret = array();
        
        foreach ($info as $subject) {
            $ret[] = $subject;
        }
        
        return $ret;
    }
    
}
