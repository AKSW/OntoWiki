<?php

/**
 * Class for Basic Patterns
 * 
 * @copyright  Copyright (c) 2010 {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @package
 * @subpackage
 * @author     Christoph Rieß <c.riess.dev@googlemail.com>
 */

class BasicPattern {
    
    private $_builtinFunctions       = array(
    	'TEMPURI'
    );
    
    private $_varTypes               = array (
        'RESOURCE',
        'LITERAL',
        'TEMP',
        'GRAPH',
        'LANG',
        'DATATYPE',
        'CLASS',
        'PROPERTY',
        'REGEXP'
    );
    
    private $_label                  = '';
    
    private $_description            = '';

    private $_engine                 = null;
    
    private $_variables_free         = array();
    
    private $_variables_bound        = array();
    
    private $_variables_temp         = array();
    
    private $_variables_descriptions = array();
    
    private $_intermediate_result    = array();
    
    private $_selectquery            = array();
    
    private $_updatequery            = array();

    public function __construct() {

    }
    
    /**
     * Sets the label of BasicPattern
     * 
     * @param string $label
     */
    public function setLabel($label) {
        $this->_label = (string) $label;
    }
    
    /**
     * Gets the label of BasicPattern
     * 
     * @return string $label
     */
    public function getLabel() {
        return (string) $this->_label;
    }
    
    /**
     * Sets the description of BasicPattern
     * 
     * @param string $desc
     */
    public function setDescription($desc) {
        $this->_description = (string) $desc;
    }
    
    /**
     * Gets the description of BasicPattern
     * 
     * @return string $desc
     */
    public function getDescription() {
        return (string) $this->_description;
    }
    
    /**
     * 
     * @param $engine
     */
    public function setEngine($engine) {
        $this->_engine = $engine;
    }
    
    /**
     * 
     * @return $engine
     */
    public function getEngine() {
        return $this->_engine;
    }
    
    /**
     * 
     * @param $query
     */
    public function addSelectQuery($query) {
        $this->_selectquery[] = $query;
    }
    
    /**
     * 
     * @return array of select queries
     */
    public function getSelectQueries() {
        return $this->_selectquery;
    }
    
    /**
     * Adds a unique update query by hashing the signature (pattern + type)
     * 
     * @param $pattern
     * @param $type
     * @return boolean true if it was added, false if it already existed or else
     */
    public function addUpdateQuery($pattern,$type) {
        $id = md5($pattern . $type);
        if ( isset ($this->_updatequery[$id]) ) {
            // do nothing
        } else {
            $this->_updatequery[$id] = array('pattern' => $pattern, 'type' => $type);
            return true;
        }
        
        return false;
    }
    
    /**
     * @return array of update queries
     */
    public function getUpdateQueries() {
        return $this->_updatequery;
    }
    
    /**
     * Returns variables of BasicPattern in following array format:
     * 
     * var_name =  name of the variable (identifier in Select- and UpdateQueries)
     * 
     * array[var_name]['name'] 	 => id-key (= var_name)
     * array[var_name]['bound']  => boolean isBound
     * array[var_name]['type]    => string type of var
     * array[var_name]['desc]    => string textual description of var
     * 
     * @param boolean $includeBound include already bound variables
     * @param boolean $noTemp don't include TEMP variables
     */
    public function getVariables($includeBound = true, $noTemp = true) {
        
        $result = array();

        foreach ($this->_variables_free as $var => $type) {
            if ( $includeBound && array_key_exists( $var , $this->_variables_bound ) ) {
                $result[$var] = array(
                	'name'   => $var ,
                	'bound'  => true ,
                	'type'   => $type ,
                    'desc'	 => $this->_variables_descriptions[$var]
                );
            } else {
                $result[$var] = array(
                	'name' => $var ,
                	'bound' => false ,
                	'type' => $type ,
                    'desc'	 => $this->_variables_descriptions[$var]
                );
            }
        }
        
        if (!$noTemp) {
            foreach ($this->_variables_temp as $var) {
                $result[$var] = array(
                	'name' => $var ,
                	'bound' => null ,
                	'type' => 'TEMP',
                    'desc' => $this->_variables_descriptions[$var]
                );
            }
        }
        
        return $result;
    }
    
    /**
     * 
     * @param string $name
     * @param string $type
     */
    public function addVariable($name, $type, $desc = '') {
        
        $this->_variables_descriptions[$name] = $desc;
        
        if ( in_array($type,$this->_varTypes) ) {
        
	        if ($type === 'TEMP') {
	            $this->_variables_temp[] = $name;
	        } else {
	            $this->_variables_free[$name] = $type;
	        }
        
        } else {
            throw new Exception('BasicPattern unkown variable type; Allowed types are: ' . implode(', ',$this->_varTypes));
        }
    
    }
    
    /**
     * Remove specific named variable from pattern
     * @param $name
     */
    public function removeVariable($name) {
        
        if ( array_key_exists($name, $this->_variables_free) ) {
            unset($this->_variables_free[$name]);
            unset($this->_variables_descriptions[$name]);
        } elseif ( in_array($name, $this->_variables_temp) ) {
            $id = array_search($name, $this->_variables_temp);
            unset($this->_variables_temp[$id]);
            unset($this->_variables_descriptions[$name]);
        }
    
    }
    
    public function bindVariable($name, $value) {
    
        if ( array_key_exists($name, $this->_variables_free) ) {
            $this->_variables_bound[$name] = array('value' => $value , 'type' => $this->_variables_free[$name]);
        } else {
            throw new RuntimeException('Unknown Variable to bind in BasicPattern.');
        }
    
    }
    
    public function unbindVariable() {
        
        if ( array_key_exists($name, $this->_variables_bound) ) {
            unset($this->_variables_bound[$name]);
        } else {
            throw new RuntimeException('Unbound Variable.');
        }
    
    }
    
    public function executeSelect() {
        
	    foreach ($this->_selectquery as $wherePart) {
	    
	        $query = 'SELECT * ';
	        
	        /*
	        foreach ($this->_variables_temp as $var) {
	            $query .= '?' . $var . ' ';
	        }
	        */
	        
	        $wherePart = ' WHERE ' . $wherePart;
	        
	        foreach ($this->_variables_bound as $var => $value) {
	            
	            $valueStr = '';
	            
	            switch ($value['type']) {
	                case 'RESOURCE' :
	                    $valueStr .= '<' . $value['value'] . '>';
	                    break;
	                case 'LITERAL' :
	                    $valueStr .= '"' . $value['value'] . '"';
	                    break;
	                default :
	                    $valueStr .= '<' . $value['value'] .'>';
	                    break;
	            }
	        
	            $wherePart = str_replace(
	                ' ' . $var . ' ',
	                ' ' . $valueStr . ' ',
	                $wherePart
	            );
	
	        }
	        
	        foreach ($this->_variables_temp as $var) {
	        
	            $wherePart = str_replace(
	                ' ' . $var . ' ',
	                ' ?' . $var . ' ',
	                $wherePart
	            );
	            
	        }
	        
	        $query .= $wherePart;

	        $result = $this->_engine->queryGraph($query);

	        $this->_intermediate_result[] = $result;
	        
        }
	        
	    return true;
    
    }
    
    /**
     * Executes data update operations for this pattern by using calling the engine with updateGraph($ins, $del)
     * 
     * @see PatternEngine::updateGraph()
     */
    public function executeUpdate() {

        if ( empty($this->_intermediate_result) && !$this->executeSelect() ) {
            
            return false;
            
        }
        
        $stmt = array('insert' => array(), 'delete' => array());
        
        foreach ($this->_updatequery as $qHash => $tPattern) {
            
            $type = $tPattern['type'];
            
            $parts = explode(' ', $tPattern['pattern']);

            $found = false;
            
            $activeResult = array();
            
            foreach ($this->_intermediate_result as $result) {
                
                if ( 
                    ( in_array($parts[0] , $result['head']['vars']) ||
                      in_array($parts[1] , $result['head']['vars']) ||
                      in_array($parts[2] , $result['head']['vars']) )
                    &&
                    $found
                ) {
                    throw new RuntimeException('found cross result set update pattern');
                    return false;
                } elseif (
                    in_array($parts[0] , $result['head']['vars']) ||
                    in_array($parts[1] , $result['head']['vars']) ||
                    in_array($parts[2] , $result['head']['vars'])
                ) {
                    $found = true;
                    $activeResult = $result;
                } else {
                    // do nothing
                }
                
            }
            
            $resultLoop = 0;
            
            $mode = 0;
            
            if ( $found && in_array($parts[0], $activeResult['head']['vars']) ) {
                $resultLoop = $resultLoop | 1;
            } elseif ( array_key_exists($parts[0], $this->_variables_bound) ) {
                $parts[0] = $this->_variables_bound[$parts[0]];
            } else {
                if ( $parts[1][0] === '<' && $parts[1][strlen($parts[1]) - 1] === '>') {
                    $parts[2] = array( 'value' => substr($parts[1], 1, strlen( $parts[1] ) - 2) , 'type' => 'uri' );
                }
            }
            
            if ( $found && in_array($parts[1], $activeResult['head']['vars']) ) {
                $resultLoop = $resultLoop | 2;
            } elseif ( array_key_exists($parts[1], $this->_variables_bound) ) {
                $parts[1] = $this->_variables_bound[$parts[1]];
            } else {                
                if ( $parts[1][0] === '<' && $parts[1][strlen($parts[1]) - 1] === '>') {
                    $parts[1] = array( 'value' => substr($parts[1], 1, strlen( $parts[1] ) - 2) , 'type' => 'uri' );
                }
            }

            if ( $found && in_array($parts[2], $activeResult['head']['vars']) ) {
                $resultLoop = $resultLoop | 4;
            } elseif ( array_key_exists($parts[2], $this->_variables_bound) ) {
                $parts[2] = $this->_variables_bound[$parts[2]];
            } else {
                if ( $parts[2][0] === '"' && $parts[2][strlen($parts[2]) - 1] === '"') {
                    $parts[2] = array( 'value' => substr($parts[2], 1, strlen( $parts[2] ) - 2) , 'type' => 'literal' );
                } elseif ($parts[2][0] === '<' && $parts[2][strlen($parts[2]) - 1] === '>') {
                    $parts[2] = array( 'value' => substr($parts[2], 1, strlen( $parts[2] ) - 2) , 'type' => 'uri' );
                } else {
                    // do nothing
                }
            }
            
            switch ($resultLoop) {
                case 0:
                    $stmt[$type][ $parts[0]['value'] ][ $parts[1]['value'] ][] = $parts[2];
                    break;
                case 1:
                    foreach ($activeResult['results']['bindings'] as $row) {
                        $object = $parts[2];
                        $stmt[$type][ $row[$parts[0]]['value'] ][ $parts[1]['value'] ][] = $object;
                    }
                    break;
                case 2:
                    foreach ($activeResult['results']['bindings'] as $row) {
                        $stmt[$type][ $parts[0]['value'] ][ $row[$parts[1]]['value'] ][] = $parts[2];
                    }
                    break;
                case 3:
                    foreach ($activeResult['results']['bindings'] as $row) {
                        $stmt[$type][ $row[$parts[0]]['value'] ][ $row[$parts[1]]['value'] ][] = $parts[2];
                    }
                    break;
                case 4:
                    foreach ($activeResult['results']['bindings'] as $row) {
                        $subject = $parts[0]['value'];
                        $stmt[$type][ $subject ][ $parts[1]['value'] ][] = $row[$parts[2]];
                    }
                    break;
                case 5:
                    foreach ($activeResult['results']['bindings'] as $row) {
                        $stmt[$type][ $row[$parts[0]]['value'] ][ $parts[1]['value'] ][] = $row[$parts[2]];
                    }
                    break;
                case 6:
                    foreach ($activeResult['results']['bindings'] as $row) {
                        $stmt[$type][ $parts[0]['value'] ][ $row[$parts[1]]['value'] ][] = $row[$parts[2]];
                    }
                    break;
                case 7:
                    foreach ($activeResult['results']['bindings'] as $row) {
                        $stmt[$type][ $row[$parts[0]]['value'] ][ $row[$parts[1]]['value'] ][] = $row[$parts[2]];
                    }
                    break;
            }
            
        }
        
        return $this->_engine->updateGraph($stmt['insert'],$stmt['delete']);
        
    }
    
    public function execute() {
    
    }
    
    /**
     * Serialize a BasicPattern to an ordered array (json possible)
     * 
     * @param $asJson
     */
    public function toArray($asJson = false) {
        
            $data = array(
            	'V'     => array(),
            	'S'     => array(),
            	'U'     => array(),
                'label' => 'empty at ' . time(),
                'desc'  => 'empty at '  . time()
            );
        
            foreach ($this->getVariables(true, false) as $var) {
                $data['V'][] = $var;
            }
            foreach ($this->getUpdateQueries() as $pat) {
                $data['U'][] = $pat;
            }
            foreach($this->getSelectQueries() as $pat) {
                $data['S'][] = $pat;
            }
            
            sort($data['V']);
            sort($data['U']);
            sort($data['S']);
            
            $data['label'] = $this->getLabel();
            $data['desc']  = $this->getDescription();
            
            if ($asJson) {
                return json_encode($data);
            } else {
                return $data;
            }
    }

}
