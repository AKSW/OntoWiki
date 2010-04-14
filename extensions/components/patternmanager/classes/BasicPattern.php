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

    private $_engine                 = null;
    
    private $_variables_resources    = array();
    
    private $_variables_literals     = array();
    
    private $_variables_bound        = array();
    
    private $_variables_temp         = array();
    
    private $_intermediate_result    = array();
    
    private $_selectquery            = '';
    
    private $_updatequery            = array(
    	'INSERT' => array(), 
    	'DELETE' => array()
    );

    public function __construct($variables = null, $selectquery = null, $updatequery = null) {
    
        foreach ($variables as $variable => $type) {
        
            if ($type === 'TEMP') {
                $this->_variables_temp[] = $variable;
            } elseif ($type === 'RESOURCE') {
                $this->_variables_resources[] = $variable;
            } elseif ($type === 'LITERAL') {
                $this->_variables_literals[] = $variable;
            }
            
        }
        
        if ($selectquery !== null) {
            $this->_selectquery = $selectquery;
        }
        
        if ($updatequery !== null) {
            $this->_updatequery = $updatequery;
        }
    }
    
    public function setEngine($engine) {
        $this->_engine = $engine;
    }
    
    public function getEngine() {
        return $this->_engine;
    }
    
    public function setSelectQuery() {
    
    }
    
    public function getSelectQuery() {
    
    }
    
    public function setUpdateQuery() {
    
    }
    
    public function getUpdateQuery() {
    
    }
    
    public function getVariables($includeBound = true, $noTemp = true) {
        
        $result = array();
        
        foreach ($this->_variables_resources as $var) {
            if ( $includeBound && array_key_exists($var,$this->_variables_bound) ) {
                $result[$var] = array('varname' => $var , 'bound' => true , 'type' => 'RESOURCE');
            } else {
                $result[$var] = array('varname' => $var , 'bound' => false , 'type' => 'RESOURCE');
            }
        }
        
        foreach ($this->_variables_literals as $var) {
            if ( $includeBound && array_key_exists($var,$this->_variables_bound) ) {
                $result[$var] = array('varname' => $var , 'bound' => true , 'type' => 'LITERAL');
            } else {
                $result[$var] = array('varname' => $var , 'bound' => false , 'type' => 'LITERAL');
            }
        }
        
        if (!$noTemp) {
            foreach ($this->_variables_temp as $var) {
                $result[$var] = array('varname' => $var , 'bound' => null , 'type' => 'TEMP');
            }
        }
        
        return $result;
    }
    
    public function addVariable() {
    
    }
    
    public function removeVariable() {
    
    }
    
    public function bindVariable($name, $value) {
    
        if ( in_array($name,$this->_variables_resources) ) {
            $this->_variables_bound[$name] = array('value' => $value , 'type' => 'uri');
        } elseif ( in_array($name,$this->_variables_literals) ) {
            $this->_variables_bound[$name] = array('value' => $value , 'type' => 'literal');
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
	            
	            if ($value['type'] === 'uri') {
	                $valueStr = '<' . $value['value'] . '>';
	            } elseif ($value['type'] === 'literal' ) {
	                $valueStr = '"' . $value['value'] . '"';
	            } else {
	                
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
    
    public function executeUpdate() {

        if ( empty($this->_intermediate_result) && !$this->executeSelect() ) {
            
            return false;
            
        }
        
        $insert = array();
        $delete = array();
        
        foreach ($this->_updatequery['INSERT'] as $tPattern) {
            
            $parts = explode(' ', $tPattern);
            
            $found = false;
            
            $activeResult = array();
            
            foreach ($this->_intermediate_result as $result) {
                
                if (
                    (
                     in_array($parts[0] , $result['head']['vars']) ||
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
            } elseif ( $parts[0] === 'TEMPURI' ) {
                $resultLoop = $resultLoop | 1;
                $mode = 1;
            } elseif ( array_key_exists($parts[0], $this->_variables_bound) ) {
                $parts[0] = $this->_variables_bound[$parts[0]];
            } else {
                // do nothing
            }
            
            if ( $found && in_array($parts[1], $activeResult['head']['vars']) ) {
                $resultLoop = $resultLoop | 2;
            } elseif ( $parts[1] === 'TEMPURI' ) {
                $resultLoop = $resultLoop | 2;
                
            } elseif ( array_key_exists($parts[1], $this->_variables_bound) ) {
                $parts[1] = $this->_variables_bound[$parts[1]];
            } else {                
                switch ($parts[1]) {
                    case 'a': {
                        $parts[1] = array( 'value' => EF_RDF_TYPE, 'type' => 'uri' );
                        break;
                    }
                    default: {
                        $parts[1] = array( 'value' => $parts[1] );
                        break;
                    }
                }
            }

            if ( $found && in_array($parts[2], $activeResult['head']['vars']) ) {
                $resultLoop = $resultLoop | 4;
            } elseif ( $parts[2] === 'TEMPURI' ) {
                $resultLoop = $resultLoop | 4;
                $mode = 1;
            } elseif ( array_key_exists($parts[2], $this->_variables_bound) ) {
                $parts[2] = $this->_variables_bound[$parts[2]];
            } else {
                if ( $parts[2][0] === '"' && $parts[2][strlen($parts[2]) - 1] === '"') {
                    $parts[2] = array( 'value' => substr($parts[2], 1, strlen( $parts[2] ) - 2) , 'type' => 'literal' );
                } else {
                    $parts[2] = array( 'value' => $parts[2], 'type' => 'uri' );
                }
            }
            
            switch ($resultLoop) {
                case 0:
                    $insert[ $parts[0]['value'] ][ $parts[1]['value'] ][] = $parts[2];
                    break;
                case 1:
                    foreach ($activeResult['results']['bindings'] as $row) {
                        $insert[ $row[$parts[0]]['value'] ][ $parts[1]['value'] ][] = $parts[2];
                    }
                    break;
                case 2:
                    foreach ($activeResult['results']['bindings'] as $row) {
                        $insert[ $parts[0]['value'] ][ $row[$parts[1]]['value'] ][] = $parts[2];
                    }
                    break;
                case 3:
                    foreach ($activeResult['results']['bindings'] as $row) {
                        $insert[ $row[$parts[0]]['value'] ][ $row[$parts[1]]['value'] ][] = $parts[2];
                    }
                    break;
                case 4:
                    foreach ($activeResult['results']['bindings'] as $row) {
                        $insert[ $parts[0]['value'] ][ $parts[1]['value'] ][] = $row[$parts[2]];
                    }
                    break;
                case 5:
                    foreach ($activeResult['results']['bindings'] as $row) {
                        $insert[ $row[$parts[0]]['value'] ][ $parts[1]['value'] ][] = $row[$parts[2]];
                    }
                    break;
                case 6:
                    foreach ($activeResult['results']['bindings'] as $row) {
                        $insert[ $parts[0]['value'] ][ $row[$parts[1]]['value'] ][] = $row[$parts[2]];
                    }
                    break;
                case 7:
                    foreach ($activeResult['results']['bindings'] as $row) {
                        $insert[ $row[$parts[0]]['value'] ][ $row[$parts[1]]['value'] ][] = $row[$parts[2]];
                    }
                    break;
            }
            
        }
            
        foreach ($this->_updatequery['DELETE'] as $tPattern) {
            
            $parts = explode(' ', $tPattern);
            
            $found = false;
            
            $activeResult = array();
            
            foreach ($this->_intermediate_result as $result) {
                
                if (
                    ( in_array($parts[0] , $result['head']['vars']) ||
                      in_array($parts[1] , $result['head']['vars']) ||
                      in_array($parts[2] , $result['head']['vars'])
                    ) &&
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
            
            if ( $found && in_array($parts[0], $activeResult['head']['vars']) ) {
                $resultLoop = $resultLoop | 1;
            } elseif ( array_key_exists($parts[0], $this->_variables_bound) ) {
                $parts[0] = $this->_variables_bound[$parts[0]];
            } else {
                // do nothing
            }
            
            if ( $found && in_array($parts[1], $activeResult['head']['vars']) ) {
                $resultLoop = $resultLoop | 2;
            } elseif ( array_key_exists($parts[1], $this->_variables_bound) ) {
                $parts[1] = $this->_variables_bound[$parts[1]];
            } else {
                switch ($parts[1]) {
                    case 'a': {
                        $parts[1] = array( 'value' => EF_RDF_TYPE, 'type' => 'uri');
                        break;
                    }
                    default: {
                        $parts[1] = array( 'value' => $parts[1]);
                        break;
                    }
                }
            }

            if ( $found && in_array($parts[2], $activeResult['head']['vars']) ) {
                $resultLoop = $resultLoop | 4;
            } elseif ( array_key_exists($parts[2], $this->_variables_bound) ) {
                $parts[2] = $this->_variables_bound[$parts[2]];
            } else {
                if ( $parts[2][0] === '"' && $parts[2][strlen($parts[2]) - 1] === '"') {
                    $parts[2] = array( 'value' => substr($parts[2], 1, strlen( $parts[2] ) - 2) , 'type' => 'literal' );
                } else {
                    $parts[2] = array( 'value' => $parts[2], 'type' => 'uri' );
                }
            }

            switch ($resultLoop) {
                case 0:
                    $delete[$parts[0]['value']][$parts[1]['value']][] = $parts[2];
                    break;
                case 1:
                    foreach ($activeResult['results']['bindings'] as $row) {
                        $delete[ $row[$parts[0]]['value'] ][ $parts[1]['value'] ][] = $parts[2];
                    }
                    break;
                case 2:
                    foreach ($activeResult['results']['bindings'] as $row) {
                        $delete[ $parts[0]['value'] ][ $row[$parts[1]]['value'] ][] = $parts[2];
                    }
                    break;
                case 3:
                    foreach ($activeResult['results']['bindings'] as $row) {
                        $delete[ $row[$parts[0]]['value'] ][ $row[$parts[1]]['value'] ][] = $parts[2];
                    }
                    break;
                case 4:
                    foreach ($activeResult['results']['bindings'] as $row) {
                        $delete[ $parts[0]['value'] ][ $parts[1]['value'] ][] = $row[$parts[2]];
                    }
                    break;
                case 5:
                    foreach ($activeResult['results']['bindings'] as $row) {
                        $delete[ $row[$parts[0]]['value'] ][ $parts[1]['value'] ][] = $row[$parts[2]];
                    }
                    break;
                case 6:
                    foreach ($activeResult['results']['bindings'] as $row) {
                        $delete[ $parts[0]['value'] ][ $row[$parts[1]]['value'] ][] = $row[$parts[2]];
                    }
                    break;
                case 7:
                    foreach ($activeResult['results']['bindings'] as $row) {
                        $delete[ $row[$parts[0]]['value'] ][ $row[$parts[1]]['value'] ][] = $row[$parts[2]];
                    }
                    break;
            }
        }
        
        return $this->_engine->updateGraph($insert,$delete);
        
    }
    
    public function execute() {
    
    }

}
