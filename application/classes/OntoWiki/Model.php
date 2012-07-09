<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki model base class.
 *
 * @category OntoWiki
 * @package OntoWiki_Classes
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Norman Heino <norman.heino@gmail.com>
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
    public function __construct(Erfurt_Store $store, Erfurt_Rdf_Model $graph)
    {
        // system variables
        $this->_store           = $store;
        $this->_config          = OntoWiki::getInstance()->config;
        $this->_logger          = OntoWiki::getInstance()->logger;
        $this->_eventDispatcher = Erfurt_Event_Dispatcher::getInstance();

        if (isset($this->_config->system->inference) && !(bool)$this->_config->system->inference) {
            $this->_inference = false;
        }

        // data variables
        $this->_graph = $graph->getModelIri();
        $this->_model = $graph;

        $this->_titleHelper = new OntoWiki_Model_TitleHelper($this->_model, $store);

        // $this->_titleProperties = array_flip($this->_config->properties->title->toArray());
        $this->_titleProperties = array_flip($graph->getTitleProperties());
    }

    /**
     * get the store that hosts this model
     * @return Erfurt_Store
     */
    public function getStore()
    {
        return $this->_store;
    }

    /**
     * get the raw model/graph
     * @return Erfurt_Rdf_Model
     */
    public function getGraph()
    {
        return $this->_model;
    }
}

