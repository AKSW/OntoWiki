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

    private $_varTypes = array (
        'RESOURCE'   => 'uri',
        'LITERAL'    => 'literal',
        'TEMP'       => 'temp',
        'GRAPH'      => false,
        'LANG'       => false,
        'DATATYPE'   => false,
        'CLASS'      => 'uri',
        'PROPERTY'   => 'uri',
        'REGEXP'     => false
    );

    private $_label                  = '';

    private $_description            = '';

    private $_engine                 = null;

    private $_variables_free         = array();

    private $_variables_bound        = array();

    private $_variables_temp         = array();

    private $_variables_descriptions = array();

    private $_intermediate_result    = array();

    private $_selectquery            = null;

    private $_updatequery            = array();

    /**
     *
     * Constructor
     */
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
    public function setSelectQuery($query) {
        $this->_selectquery = $query;
    }

    /**
     *
     * @return array of select queries
     */
    public function getSelectQuery() {
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
     * array[var_name]['type']    => string type of var
     * array[var_name]['desc']    => string textual description of var
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

        if ( array_key_exists($type,$this->_varTypes) ) {

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
            $this->_variables_bound[$name] = array('value' => $value , 'type' => $this->_varTypes[$this->_variables_free[$name]]);
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

	    $wherePart = $this->_selectquery;

	    if ( empty($wherePart) ) {
	        $this->_intermediate_result = array();
	        return true;
	    }

        $query = 'SELECT DISTINCT ';
        foreach( $this->_variables_temp as $var) {

            $query .= "?".$var." ";
        }

        if (preg_match('/s*(FROM\s+\S+\s+)*WHERE\s+\{.*\}/i',$wherePart) !== 0) {
            // do nothing
        } else {
            $wherePart = ' WHERE ' . $wherePart;
        }

        foreach ($this->_variables_bound as $var => $value) {

            $valueStr = '';

            switch ($value['type']) {
                case 'GRAPH' :
                    $valueStr .= '<' . $value['value'] . '>';
                    break;
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
                '%' . $var . '%',
                ' ' . $valueStr . ' ',
                $wherePart
            );

        }

        foreach ($this->_variables_temp as $var) {

            $wherePart = str_replace(
                '%' . $var . '%',
                ' ?' . $var . ' ',
                $wherePart
            );

        }

        $query .= $wherePart;

        $result = $this->_engine->queryGraph($query);

        $this->_intermediate_result = $result;

	    return true;

    }

    /**
     * Executes data update operations for this pattern by using calling the engine
     * with updateGraph($insert, $delete, $graph (optional))
     *
     * @see PatternEngine::updateGraph()
     */
    public function executeUpdate() {

        // assume boolean true return
        $return = true;

        if ( empty($this->_intermediate_result) && !$this->executeSelect() ) {
            return false;
        }

        foreach ($this->_updatequery as $qHash => $tPattern) {

            $stmt = array('insert' => array(), 'delete' => array());

            $type  = $tPattern['type'];

            $parts = $this->parsePattern($tPattern['pattern']);

            $mask = 0;

            if ($this->checkTemp($parts[0])) {
                $mask = $mask | 1;
            }

            if ($this->checkTemp($parts[1])) {
                $mask = $mask | 2;
            }

            if ($this->checkTemp($parts[2])) {
                $mask = $mask | 4;
            }

            if (sizeof($parts) == 4) {
                $graph = $parts[3]['value'];
            } else {
                $graph = null;
            }

            $func = PatternFunction::getInstance();

            if ($mask) {
                foreach ($this->_intermediate_result['results']['bindings'] as $row) {

                    if ($mask & 1) {

                        if ($parts[0]['type'] === 'function') {
                            $subject = $func->executeFunction($parts[0],$row, true);
                        } else {
                            $subject = $row[$parts[0]['value']]['value'];
                        }

                    } else {
                        if ($parts[0]['type'] === 'function') {
                            $subject = $func->executeFunction($parts[0],null,true);
                        } else {
                            $subject = $parts[0]['value'];
                        }
                    }

                    if ($mask & 2) {

                        if ($parts[1]['type'] === 'function') {
                            $predicate = $func->executeFunction($parts[1],$row, true);
                        } else {
                            $predicate = $row[$parts[1]['value']]['value'];
                        }

                    } else {
                        $predicate = $parts[1]['value'];
                    }

                    if ($mask & 4) {

                        if ($parts[2]['type'] === 'function') {
                            $object = $func->executeFunction($parts[2],$row);
                        } else {
                            $object = $row[$parts[2]['value']];
                        }

                    } else {
                        $object = $parts[2];
                    }
                    
                    // fixes issue #895 (will need erfurt fix in future)
                    if ( $object !== null && array_key_exists('xml:lang', $object) ) {
                        $object['lang'] = $object['xml:lang'];
                        unset($object['xml:lang']);
                    }

                    if ($type !== null && $subject !== null && $predicate !== null && $object !== null) {
                        $stmt[$type][$subject][$predicate][] = $object;
                    } else {
                        // possible optional (OPTIONAL keyword in SPARQL query) result set
                        // Don't include into statement updates
                    }
                }
            } else {
                
                // check static pattern in S,P,O for functions -> see issue #906
                if ($parts[0]['type'] === 'function') {
                    $subject = $func->executeFunction($parts[0],array(),true);
                } else {
                    $subject = $parts[0]['value'];
                }
                
                if ($parts[1]['type'] === 'function') {
                    $predicate = $func->executeFunction($parts[1],array(),true);
                } else {
                    $predicate = $parts[1]['value'];
                }
                
                if ($parts[2]['type'] === 'function') {
                    $object = $func->executeFunction($parts[2]);
                } else {
                    $object = $parts[2];
                }
                
                // fixes issue #895
                if ( array_key_exists('xml:lang', $object) ) {
                    $object['lang'] = $object['xml:lang'];
                    unset($object['xml:lang']);
                }
                
                $stmt[$type][$subject][$predicate][] = $object;
            }

            $result = $this->_engine->updateGraph($stmt['insert'],$stmt['delete'],$graph);

            // set return to false if any non positive results occured
            if ($result != true) {
                $return = false;
            }

        }

        return $return;

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
            	'S'     => null,
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

            $data['S'] = $this->getSelectQuery();

            sort($data['V']);
            sort($data['U']);
            //sort($data['S']);

            $data['label'] = $this->getLabel();
            $data['desc']  = $this->getDescription();

            if ($asJson) {
                return json_encode($data);
            } else {
                return $data;
            }
    }

    /**
     *
     * @param string $pattern
     * @return array containing ordered parts of tripel-/quadrupelpattern
     */
    private function parsePattern($pattern) {

        $matches = array();
        preg_match_all('/\S+/i',$pattern,$matches);
        $parts = array();

        foreach ($matches[0] as $part) {
            if ( $part[0] === '"' && $part[strlen($part) - 1] === '"') {
                $parts[] = array( 'value' => substr($part, 1, strlen( $part ) - 2) , 'type' => 'literal' );
            } elseif ( $part[0] === '<' && $part[strlen($part) - 1] === '>') {
                $parts[] = array( 'value' => substr($part, 1, strlen( $part ) - 2) , 'type' => 'uri' );
            } elseif (preg_match('/^[A-Z]+\(.*\)$/i',$part) ) {
                $parts[] = $this->builtinFunction($part,$pattern);
            } elseif ( $part[0] === '%' && $part[strlen($part) - 1] === '%' ) {
                $current = substr($part, 1, strlen( $part ) - 2);
                if (in_array($current, $this->_variables_temp)) {
                    $var = array('value' => $current, 'type' => 'temp');
                    $parts[] = $var;
                } elseif (array_key_exists($current,$this->_variables_bound)) {
                    $parts[] = $this->_variables_bound[$current];
                } else {
                    $parts[] = array( 'value' => $current, 'type' => 'variable');
                }
            } else {
                throw new RuntimeException('undefined entity: ' . $part);
            }
        }

        return $parts;

    }

    private function builtinFunction($part,$origin) {
        $found = false;
        $paramStart = -1;
        $paramEnd   = -1;

        $name = '';
        $value = '';

        for ($i = 0; $i < strlen($part); $i++) {
            if ($part[$i] === '(' && $paramStart < 0) {
                $paramStart = $i;

            }
            if ($part[strlen($part) - $i - 1] === ')' && $paramEnd < 0) {
                $paramEnd = strlen($part) - $i - 1;
            }

        }

        if ($paramStart > 0 && $paramEnd > 0) {

            $name = strtolower(substr($part,0,$paramStart ));
            $value = substr($part,$paramStart + 1,$paramEnd - $paramStart - 1);
        }

        $depth = 0;
        $escaped = false;
        $split = array();

        for ($i = 0; $i < strlen($value); $i++) {

            if (!$escaped) {
                if ($value[$i] === '"') {
                    $escaped = true;
                }
            } else {
                if ( $value[$i] === '"' && $value[$i-1] !== '\\') {
                    $escaped = false;
                } else {
                    continue;
                }
            }

            if ($value[$i] === '(') {
                $depth++;
            }
            if ($value[$i] === ')') {
                $depth--;
            }
            if ( ($depth === 0) && $value[$i] === ',') {
                $split[] = $i;
            }

        }

        $split[] = strlen($value);


        $func = PatternFunction::getInstance();

        if ( !$func->isFunctionAvailable($name) ) {
            throw new RuntimeException('BasicPattern invalid builtinFunction name ' . $name);
        }

        $last = -1;
        $params = array();

        foreach ($split as $i) {
            $last ++;
            $current = substr($value,$last,$i - $last);
            $last = $i;
            if ( $current[0] === '"' && $current[strlen($current) - 1] === '"' ) {
                $params[] = array( 'value' => substr($current, 1, strlen( $current ) - 2) , 'type' => 'literal' );
            } elseif ( $current[0] === '<' && $current[strlen($current) - 1] === '>') {
                $params[] = array( 'value' => substr($current, 1, strlen( $current ) - 2) , 'type' => 'uri' );
            } elseif ( preg_match('/^[A-Z]+\(.*\)$/i',$current) ) {
                $params[] = $this->builtinFunction($current,$origin);
            } elseif ( $current[0] === '%' && $current[strlen($current) - 1] === '%' ) {
                $current = substr($current, 1, strlen( $current ) - 2);
                if (in_array($current, $this->_variables_temp)) {
                    $var = array('value' => $current, 'type' => 'temp');
                    $params[] = $var;
                } elseif (array_key_exists($current,$this->_variables_bound)) {
                    $params[] = $this->_variables_bound[$current];
                } else {
                    $params[] = array( 'value' => $current, 'type' => 'variable');
                }
            } else {
                throw new RuntimeException('undefined entity: ' . $current);
            }
        }

        if ($paramStart === ($paramEnd - 1) ) {
            return array('type' => 'function', 'name' => $name, 'param' => array());
        } else {
            return array('type' => 'function', 'name' => $name, 'param' => $params);
        }

    }

    /**
     *
     * Checks whether given variable in $entity is temp variable; Or if it's
     * a builtin function check for temp variables in function tree.
     * @param array $entity
     * @return true if variable is used as temp
     */
    private function checkTemp($entity) {
        if ($entity['type'] !== 'temp' && $entity['type'] !== 'function') {
            return false;
        } else {
            if ($entity['type'] === 'temp') {
                return true;
            }
            if ($entity['type'] === 'function') {
                foreach ($entity['param'] as $param) {
                    if ($this->checkTemp($param)) {
                        return true;
                    }
                }

                return false;
            }
        }

    }

}
