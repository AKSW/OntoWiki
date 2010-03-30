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

    private $_store;
    
    private $_graph;
    
    private $_versioning;
    
    public function __construct() {
    
    }

    public function setBackend($erfurt) {
    
        $this->_store      = $erfurt->getStore();
        
        $this->_versioning = $erfurt->getVersioning();
        
    }
    
    public function getStore() {

        return $this->_store;

    }
    
    public function setDefaultGraph($graph) {
    
        $this->_graph = (string) $graph;
        
    }
    
    public function getDefaultGraph() {
    
        return $this->_graph;
        
    }
    
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
    
    public function queryGraph($selectQuery) {

        // add from for query (dirrrty)
        $selectQuery = str_replace('WHERE','FROM <' . $this->_graph . '> WHERE', $selectQuery);
        
        $result = $this->_store->sparqlQuery($selectQuery, array('result_format' => 'extended'));
        
        return $result;
        
    }
    
    public function updateGraph($insert, $delete) {

        $result = $this->_store->addMultipleStatements($this->_graph, $insert);
        
        $result = $this->_store->deleteMultipleStatements($this->_graph, $delete);
        
        return true;
        
    }
    
}
