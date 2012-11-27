<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki resource hierarchy model class.
 *
 * Represents a hierarchy of resources, e.g. a class tree.
 *
 * @category OntoWiki
 * @package OntoWiki_Classes_Model
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_Model_Hierarchy extends OntoWiki_Model
{    
    /**
     * Direction of the hierarchy relation is downwards.
     */
    const DIRECTION_DOWN = 1;
    
    /**
     * Direction of the hierarchy relation is upwards.
     */
    const DIRECTION_UP = 2;
    
    /**
     * Default hierarchy options
     * @var array
     */
    private $_options = array(
        'object_types'   => array(EF_RDFS_CLASS, EF_OWL_CLASS), 
        'ignore_ns'      => array(EF_RDF_NS, EF_RDFS_NS, EF_OWL_NS), // EF_OWL_THING
        'sub_relation'   => EF_RDFS_SUBCLASSOF, 
        'sub_direction'  => self::DIRECTION_DOWN, 
        'inst_relation'  => EF_RDF_TYPE, 
        'inst_direction' => self::DIRECTION_DOWN, 
        'entry'          => null
    );
    
    /**
     * Result array
     * @var array
     */
    private $_hierarchyResults = null;
    
    /**
     * Root of the hierarchy
     * @var Erfurt_Rdf_Resource
     */
    private $_current = null;
    
    /**
     * URL object used to built URLs
     * @var OntoWiki_Url
     */
    private $_url = null;
    
    /**
     * Current session instance
     * @var Zend_Session
     */
    private $_session = null;
    
    protected $_titleHelper = null;
    
    /**
     * Constructor
     *
     * @see OntoWiki_Model_Abstract
     */
    public function __construct(Erfurt_Store $store, $graph, array $options = array())
    {
        parent::__construct($store, $graph);
        $this->_options = array_merge($this->_options, $options);
        $this->_current = (string)OntoWiki::getInstance()->selectedResource;
        $this->_session = OntoWiki::getInstance()->session;
        
        // HACK: if the graph itself is one that is normally ignored in other
        // models, don't ignore it
        if (in_array($this->_graph, $this->_options['ignore_ns'])) {
            // TODO: remove only graph and imported namespaces
            $this->_options['ignore_ns'] = array();
        }
    }
    
    /**
     * Returns whether the model contains data.
     *
     * @return boolean
     */
    public function hasData()
    {
        if (!$this->_hierarchyResults) {
            $this->getHierarchy();
        }
        
        return !empty($this->_hierarchyResults);
    }
    
    /**
     * Returns the resource hierarchy.
     *
     * @return array
     */
    public function getHierarchy()
    {
        if (!$this->_hierarchyResults) {
            $query = $this->_buildQuery();
            
            $this->_url = new OntoWiki_Url(array('route' => 'instances'), array('r', 'init'));
            
            if ($result = $this->_model->sparqlQuery($query)) {
                $this->_hierarchyResults = array();
                
                // set titles
                foreach ((array)$result as $row) {
                    $this->_titleHelper->addResource($row['classUri']);
                    if (!empty($row['sub'])) {
                        $this->_titleHelper->addResource($row['sub']);
                    }
                }
                
                foreach ((array)$result as $row) {
                    $classUri = $row['classUri'];
                    
                    if (!array_key_exists($classUri, $this->_hierarchyResults)) {
                        $this->_hierarchyResults[$classUri] = $this->_getEntry($classUri, !empty($row['sub']));
                        $this->_titleHelper->addResource($classUri);
                    }
                    
                    // do subclasses exists?
                    if (!empty($row['sub'])) {
                        $subClassUri = $row['sub'];
                        
                        $this->_hierarchyResults[$classUri]['children'][$subClassUri] = 
                                            $this->_getEntry($subClassUri, !empty($row['subsub']));
                    }
                }
            }
        }
        
        return $this->_hierarchyResults;
    }
    
    protected function _getEntry($resultUri, $hasChildren = false)
    {
        $classes = '';
        if ($resultUri == $this->_current) {
            $classes .= ' selected';
        }
        
        if ($hasChildren) {
            $classes .= ' has-children';
        }
        
        $this->_url->setParam('r', $resultUri, true);
        
        $entry = array(
            'uri'          => $resultUri, 
            'url'          => (string)$this->_url, 
            'classes'      => trim($classes), 
            'title'        => $this->_titleHelper->getTitle($resultUri, $this->_config->languages->locale), 
            'children'     => array(), 
            'has_children' => $hasChildren, 
            'open'         => is_array($this->_session->hierarchyOpen) && in_array($resultUri, $this->_session->hierarchyOpen)
        );
        
        return $entry;
    }
    
    protected function _buildQuery()
    {
        $query = new Erfurt_Sparql_SimpleQuery();
        $prologue = 'SELECT DISTINCT ?classUri ?sub ?subsub ?subsubsub';
        $query->setProloguePart($prologue);
        
        $whereSpecs = array();
        $whereSpec  = '';
        foreach ($this->_options['object_types'] as $type) {
            $whereSpecs[] = '{?classUri a <' . $type . '>}';
        }
        
        // optional inference
        if (!$this->_options['entry'] && $this->_inference) {
            // instance retrieval is only applicable for classes
            if ($this->_options['sub_relation'] == EF_RDFS_SUBCLASSOF) {
                $whereSpecs[] = '{?instance a ?classUri.}';
            }
            // entities with a subtype must be a type
            $whereSpecs[] = '{?subtype <' . $this->_options['sub_relation'] . '> ?classUri.}';
        }
        
        $whereSpec = implode(' UNION ', $whereSpecs);
        $whereSpec .= ' FILTER (isURI(?classUri))';
        
        // dont't show rdfs/owl entities and subtypes in the first level
        if (!$this->_options['entry']) {
            $whereSpec .= ' FILTER (regex(str(?super), "^' . EF_OWL_NS . '") || !bound(?super))';
        }
        $whereSpec .= ' OPTIONAL {?sub <' . $this->_options['sub_relation'] . '> ?classUri. 
                            OPTIONAL {?subsub <' . $this->_options['sub_relation'] . '> ?sub.
                                  OPTIONAL {?subsubsub <' . $this->_options['sub_relation'] . '> ?subsub}
                            }
                        }';
        $whereSpec .= ' OPTIONAL {?classUri <' . $this->_options['sub_relation'] . '> ?super. FILTER(isUri(?super))}';
        
        // namespaces to be ignored, rdfs/owl-defined objects
        foreach ($this->_options['ignore_ns'] as $ignore) {
            $whereSpec .= ' FILTER (!regex(str(?classUri), "^' . $ignore . '"))';
        }
        
        // entry point into the class tree
        if ($this->_options['entry']) {
            $whereSpec .= ' FILTER (str(?super) = str(<' . $this->_options['entry'] . '>))';
        }
        
        $query->setWherePart('WHERE {' . $whereSpec . '}');
        
        return $query;
    }
}

