<?php

/**
 * Class for Builtin Functions in Patterns
 * 
 * @copyright  Copyright (c) 2010 {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @package
 * @subpackage
 * @author     Christoph Rieß <c.riess.dev@googlemail.com>
 */

class PatternFunction {
    
    /**
     * 
     * Enter description here ...
     * @var array
     */
    private $_cache = array();
    
    /**
     * 
     * Enter description here ...
     * @var array
     */
    private $_cacheSize = 0;
    
    /**
     * 
     * Enter description here ...
     * @var int
     */
    const CACHE_MAX_ENTRIES = 20000;
    
    /** 
     * Singleton instance
     * @var PatternFunction
     */
    private static $_instance = null;
    
    /**
     * Constructor
     */
    private function __construct()
    {
        $this->_cache = array();
    }
    
    /**
     * Singleton instance
     *
     * @return PatternFunction
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    private function cacheTest($cId) {
        return ( array_key_exists($cId,$this->_cache) );
    }
    
    private function cacheLoad($cId) {
        return ( $this->_cache[$cId] );
    }
    
    private function cacheSave($cId,$payload) {
        
        if ($this->_cacheSize > PatternFunction::CACHE_MAX_ENTRIES) {
            $this->_cacheSize = 0;
            $this->_cache = array();
        }
        
        $this->_cache[$cId] = $payload; 
        $this->_cacheSize++;
    }
    
    /**
     * 
     * Enter description here ...
     * @param $data
     * @param $bind
     * @param $asString
     */
    public function executeFunction ($data, $bind, $asString = false) {
        $funcOptions = array();
        $options = array();
        foreach ($data['param'] as $param) {
            if ($param['type'] === 'function') {
                $options[] = $this->executeFunction($param, $bind, true);
            } elseif ( $param['type'] === 'temp') {
                $options[] = $bind[$param['value']]['value'];
            } else {
                $options[] = $param['value'];
            }
        }

        if ($this->isFunctionAvailable($data['name'])) {
            
            $callback = 'call' . ucfirst($data['name']);
            
            // caching all function results in memory
            $cacheId = $callback . implode('//',$options);
            if ( $this->cacheTest($cacheId) ) {
                $ret = $this->cacheLoad($cacheId);
            } else {
                $ret = $this->$callback($options);
                $this->cacheSave($cacheId,$ret);
            }
            
        } else {
            throw new RuntimeException('call for PatternFunction ' . $data['name'] . ' not available');
        }
        
        if ($asString) {
	        return $ret['value'];
        } else {
            return $ret;
        }
    }
    
    /**
     * Checks whether a function with a given name $funcName is available
     * inside this instance and is callable. 
     *
     * @param string $funcName function name to check for callability
     */
    public function isFunctionAvailable($funcName) {
        return is_callable( array($this,'call' . $funcName));
    }
    
    /**
     * 
     * Enter description here ...
     * @param unknown_type $options
     */
    private function callGetnamespace($options = array()) {
        $data = array('value' => 'http://fancy-ns.com/' , 'type' => 'uri');
        return $data;
    }
    
    /**
     * 
     * Enter description here ...
     * @param unknown_type $options
     */
    private function callGettempuri($options = array()) {
        $str = '';
        $prefix = 'http://defaultgraph/';
        foreach ($options as $key => $value) {
            if ($key === 0) {
                $prefix = $value;
            } else {
                $str .=  md5($value);
            }
        }
        
        if ( !preg_match('/\/$/',$prefix) && !preg_match('/#$/',$prefix) ) {
            $prefix .= '/';
        }
        
        return array ('value' => $prefix . $str, 'type' => 'uri');
    }
    
    /**
     * 
     * Enter description here ...
     * @param unknown_type $options
     */
    private function callGetlang($options = array('de')) {
        
        $ret = array('value' => $options[0]);
        
        return $ret;
    }
    
    /**
     * 
     * Enter description here ...
     * @param unknown_type $options
     */
    private function callGetprefixeduri ($options) {
        $prefix = $options[0];
        $matches = array();
        preg_match_all('/([A-Z]|[a-z]|[0-9]|%|[-_])+$/',$options[1],$matches,PREG_PATTERN_ORDER);

        return array( 'value' => $prefix . $matches[0][0] );
    }
    
    /**
     * 
     * Enter description here ...
     * @param unknown_type $options
     */
    private function callGetsmarturi($options) {
        
        require_once ONTOWIKI_ROOT . 'plugins/resourcecreationuri/classes/ResourceUriGenerator.php';
        
        $gen = new ResourceUriGenerator();
        $uri = $gen->generateUri($options[0], ResourceUriGenerator::FORMAT_SPARQL);
        
        return array('type' => 'uri' , 'value' => $uri);
        
    }
}