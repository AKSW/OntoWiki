<?php

/**
 * Class for Access Control on Pattern Engine and Pattern Management Classes
 * 
 * @copyright  Copyright (c) 2010 {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @package
 * @subpackage
 * @author     Christoph Rieß <c.riess.dev@googlemail.com>
 */

class PatternEngineAc {
    
    
    /**
     * Bit-constants for three levels of Ac on PatternEngine
     * 
     * @var bitwiese different constants (1,2,4,8,... etc)
     */
    const RIGHT_VIEW = 1;
    const RIGHT_EDIT = 2;
    const RIGHT_EXEC = 4;
    
    /**
     * String constants for three levels of Ac on PatternEngine
     * 
     * @var string identifiers (schema EvolutionEngine_xyz)
     */
    const RIGHT_VIEW_STR = 'EvolutionEngine_View';
    const RIGHT_EDIT_STR = 'EvolutionEngine_Edit';
    const RIGHT_EXEC_STR = 'EvolutionEngine_Exec';
    
    private $_allowedActions = array();
    
    private $_maskActions = 0;
    
    private $_externalAc = null;
    
    private $_isInit = false;
    
    /**
     * Constructor for PatternEngineAc
     */
    public function __construct() {
        $this->_init();
    }
    
    /**
     *  Private init function
     */
    private function _init() {
        
        if ($this->_isInit) {
            // do nothing
        } elseif ($this->_externalAc) {
            
            if ($this->_externalAc->isActionAllowed(self::RIGHT_EDIT_STR)) {
                $this->_maskActions |= self::RIGHT_EDIT;
                $this->_allowedActions[self::RIGHT_EDIT_STR] = true;
            } else {
                
            }

            if ($this->_externalAc->isActionAllowed(self::RIGHT_EXEC_STR)) {
                $this->_maskActions |= self::RIGHT_EXEC;
                $this->_allowedActions[self::RIGHT_EXEC_STR] = true;
            } else {
                
            }

            if ($this->_externalAc->isActionAllowed(self::RIGHT_VIEW_STR)) {
                $this->_maskActions |= self::RIGHT_VIEW;
                $this->_allowedActions[self::RIGHT_VIEW_STR] = true;
            } else {
                
            }
            
            $this->_isInit = true;

        } else {
            // do nothing
        }
        
    }
    
    /**
     * Checks whether a given action is allowed or not.
     * 
     * @param string $actionStr string for questionable ac action
     * @return boolean true if action allowed false otherwise
     */
    public function isActionAllowed($actionStr) {
        
        if (!$this->_isInit) {
            $this->_init();
        }
        
        if (array_key_exists($actionStr,$this->_allowedActions) && $this->_allowedActions[$actionStr]) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Sets the external Ac (Erfurt_Ac_Default)
     * 
     * @param $ac
     */
    public function setExternalAc($ac) {
        $this->_externalAc = $ac;
    }
    
    /**
     * Gets the external Ac
     * 
     * @return object the ac-object (or null)
     */
    public function getExternalAc() {
        return $this->_externalAc;
    }
    
}
