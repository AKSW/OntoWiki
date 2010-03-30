<?php

/**
 * Class for Complex Patterns consisiting of multiple basic patterns.
 * Subpatterns (parts) of a complex pattern are being executed in a
 * batch. This class provides ability to perform operations on all
 * available subpatterns at once.
 * 
 * @copyright   Copyright (c) 2010 {@link http://aksw.org AKSW}
 * @license     http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @package
 * @subpackage
 * @author      Christoph Rieß <c.riess.dev@googlemail.com>
 */

class ComplexPattern {

    /**
     * @var array Stores subpattern object in well-defined order
     */
    private $_subPattern = array();
    
    /**
     * @var Pattern Engine
     */
    private $_engine = null;

    /**
     * Creates complex pattern initally
     */
    public function __construct() {
    
    }
    
    public function parseFromJson($json) {
        
        $data = json_decode($json, true);

        foreach ($data as $subpattern) {
            
            $pattern = new BasicPattern(
                $subpattern['V'],
                $subpattern['S'],
                $subpattern['U']
            );
            $this->appendElement($pattern);
            
        }
        
    }
    
    public function setEngine($engine) {
    
        $this->_engine = $engine;
        
        foreach ($this->_subPattern as $pattern) {
            $pattern->setEngine($engine);
        }
        
    }
    
    public function getEngine() {
        return $this->_engine;
    }

    public function getElement($index) {
    
    }
    
    public function setElement($index, $element) {
    
    }
    
    public function appendElement($element) {
        $this->_subPattern[] = $element;
    }
    
    public function getVariables($withBound = true, $noTemp = true) {
        
        $result = array();
        $intersection = array();
        
        foreach ($this->_subPattern as $pattern) {
            
            foreach ($pattern->getVariables($withBound, $noTemp) as $set) {
                if ( in_array($set,$result) ) {
                    // existing don't set again
                } else {
                    $result[$set['varname']] = $set;
                }
                
            }
            
            if (isset($count)) {
                $count++;
                $intersection = array_intersect($intersection, $pattern->getVariables($withBound, $noTemp));
            } else {
                $intersection = $pattern->getVariables($withBound, $noTemp);
            }
            
            $count = 1;
            
        }
        
        return $result;
    
    }
    
    public function bindVariable($var, $value) {
    
        foreach ($this->_subPattern as $pattern) {
            
            try {
                $pattern->bindVariable($var,$value);
            } catch (RuntimeException $e) {
                // do nothing
            }
            
        }

    }
    
    public function executeSelect() {
    
        foreach ($this->_subPattern as $pattern) {
            $pattern->executeSelect();
        }
    
    }
    
    public function executeUpdate() {
    
        foreach ($this->_subPattern as $pattern) {
            $pattern->executeUpdate();
        }
    
    }
    
    public function execute() {
        
        foreach ($this->_subPattern as $pattern) {
            $pattern->executeSelect();
            $pattern->executeUpdate();
        }
    
    }

}
