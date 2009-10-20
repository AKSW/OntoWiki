<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @category   OntoWiki
 * @package    OntoWiki
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version   $Id: Model.php 4095 2009-08-19 23:00:19Z christian.wuerker $
 */

/**
 * OntoWiki model base class.
 *
 * @category   OntoWiki
 * @package    OntoWiki
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author    Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_Model
{
    /**
     * The Erfurt store
     * @var Erfurt_Store
     */
    protected $_store = null;
    
    /**
     * The OntoWiki Application config
     * @var Zend_Config
     */
    protected $_config = null;
    
    /**
     * Whether inference features are turned on
     * @var boolean
     */
    protected $_inference = true;
    
    /**
     * The Application logger
     * @var Zend_Log
     */
    protected $_logger = null;
    
    /**
     * The language currently set
     * @var string
     */
    protected $_lang = null;
    
    /**
     * Model instance
     * @var Erfurt_Rdf_Model
     */
    protected $_model = null;
    
    /**
     * The current named graph URI
     * @var string
     */
    protected $_graph = null;
    
    /**
     * The Erfurt event dispatcher
     * @var Erfurt_Event_Dispatcher
     */
    protected $_eventDispatcher = null;
    
    /**
     * Constructor
     */
    public function __construct(Erfurt_Store $store, $graph)
    {
        // system variables
        $this->_store           = $store;
        $this->_config          = OntoWiki_Application::getInstance()->config;
        $this->_logger          = OntoWiki_Application::getInstance()->logger;
        $this->_eventDispatcher = Erfurt_Event_Dispatcher::getInstance();
        
        $this->_lang = $this->_config->languages->locale;
        
        if (isset($this->_config->system->inference) && !(bool)$this->_config->system->inference) {
            $this->_inference = false;
        }
        
        // data variables
        $this->_graph = $graph->getModelIri();
        $this->_model = $graph;
        
        $this->_titleHelper = new OntoWiki_Model_TitleHelper($this->_model);
        
        // $this->_titleProperties = array_flip($this->_config->properties->title->toArray());
        $this->_titleProperties = array_flip($graph->getTitleProperties());
    }
}

