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
    
    public function executeFunction ($data, $bind, $asString = false) {
        
        $funcOptions = array();
        foreach ($data['param'] as $param) {
            if ($param['type'] === 'function') {
                $options[] = $this->executeFunc($param, $bind, true);
            } elseif ( $param['type'] === 'temp') {
                $options[] = $bind[$param['value']];
            } else {
                $options[] = $param['value'];
            }
        }

        if ($this->isFunctionAvailable($data['name'])) {
            $callback = 'call' . ucfirst($data['name']);
            $ret = $this->$callback($options);
        } else {
            throw new RuntimeException('call for PatternFunction ' . $data['name'] . ' not available');
        }
        
        if ($asString) {
	        return $ret['value'];
        } else {
            return $ret;
        }
    }
    
    public function isFunctionAvailable($funcName) {
        return is_callable( array($this,'call' . $funcName));
    }
    
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
    
    private function callGetlang($options = array('de')) {
        
        $ret = array('value' => $options[0]);
        
        return $ret;
    }
    
    private function callGetprefixeduri ($options) {
        $prefix = $options[0];
        $matches = array();
        preg_match_all('/([A-Z]|[a-z]|[0-9]|%|[-_])+$/',$options[1],$matches,PREG_PATTERN_ORDER);

        return array( 'value' => $prefix . $matches[0][0] );
    }
}