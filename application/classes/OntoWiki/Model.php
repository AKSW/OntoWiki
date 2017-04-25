<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2009-2016, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki model base class.
 *
 * @category  OntoWiki
 * @package   OntoWiki_Classes
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author    Norman Heino <norman.heino@gmail.com>
 * @property Zend_Log $_logger
 * @property Erfurt_Event_Dispatcher $_eventDispatcher
 * @property Zend_Config $_config The application configuration.
 */
class OntoWiki_Model
{
    /**
     * The Erfurt store
     *
     * @var Erfurt_Store
     */
    protected $_store = null;

    /**
     * Whether inference features are turned on
     *
     * @var boolean
     */
    protected $_inference = true;

    /**
     * Model instance
     *
     * @var Erfurt_Rdf_Model
     */
    protected $_model = null;

    /**
     * The current named graph URI
     *
     * @var string
     */
    protected $_graph = null;

    /**
     * Constructor
     */
    public function __construct(Erfurt_Store $store, Erfurt_Rdf_Model $graph)
    {
        // system variables
        $this->_store           = $store;

        if (isset($this->_config->system->inference) && !(bool)$this->_config->system->inference) {
            $this->_inference = false;
        }

        // data variables
        $this->_graph = $graph->getModelIri();
        $this->_model = $graph;

        $this->_titleHelper = new OntoWiki_Model_TitleHelper($this->_model, $store);

        $this->_titleProperties = array_flip($graph->getTitleProperties());
    }

    /**
     * get the store that hosts this model
     *
     * @return Erfurt_Store
     */
    public function getStore()
    {
        return $this->_store;
    }

    /**
     * get the raw model/graph
     *
     * @return Erfurt_Rdf_Model
     */
    public function getGraph()
    {
        return $this->_model;
    }

    /**
     * Simulates properties that reference global objects.
     *
     * The globals are *not* stored as properties of this objects, otherwise
     * these globals (and the whole object graph that is connected to them)
     * are serialized when this object is stored in the session.
     *
     * @param string $name
     * @return Erfurt_Event_Dispatcher|Zend_Log|Zend_Config|null
     */
    public function __get($name)
    {
        switch ($name) {
            case '_logger':
                return OntoWiki::getInstance()->logger;
            case '_eventDispatcher':
                return Erfurt_Event_Dispatcher::getInstance();
            case '_config':
                return OntoWiki::getInstance()->config;
        }
        return null;
    }

}
