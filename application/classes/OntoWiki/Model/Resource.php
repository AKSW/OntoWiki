<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki resource model class.
 *
 * Represents a resources and its properties.
 *
 * @category OntoWiki
 * @package Model
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_Model_Resource extends OntoWiki_Model
{
    /**
     * The resource URI
     * @var string
     */
    protected $_uri = null;
    
    /**
     * Array with predicate data
     * @var array
     */
    protected $_predicateResults = null;
    
    /**
     * Array with value data
     * @var array
     */
    protected $_valueResults = null;
    
    /**
     * Whether data has been fetched
     * @var boolean
     */
    protected $_queryResults = null;
    
    /**
     * Array of predicates to be ignored
     * @var array
     */
    protected $_ignoredPredicates = array();

    /**
     * Array of predicates to be ignored
     * @var array
     */
    protected $_limit = OW_SHOW_MAX;


    
    /**
     * Constructor
     */
    public function __construct(Erfurt_Store $store, $graph, $uri, $limit = false)
    {
        parent::__construct($store, $graph);
        $this->_uri = (string)$uri;
        
        $this->_titleHelper = new OntoWiki_Model_TitleHelper($this->_model);
        if($limit !== false){
            $this->_limit = $limit;
        }

        //TODO fix query
        $queryHidden = 'PREFIX sysont: <http://ns.ontowiki.net/SysOnt/> SELECT ?p WHERE {?p sysont:hidden ?o }';
        $res = $store->sparqlQuery($queryHidden, array("result_format" => STORE_RESULTFORMAT_EXTENDED));
        if(isset($res['bindings'])){
            $bindings    = $res['bindings'];
        } else if(isset($res['results']['bindings'])){
            $bindings    = $res['results']['bindings'];
        } else {
            require_once 'OntoWiki/Model/Exception.php';
            throw new OntoWiki_Model_Exception('invalid query result.');
        }
        foreach($bindings as $b){
            $this->_ignoredPredicates[] = $b['p']['value'];
        }
    }
    
    /**
     * Returns an array of predicates and predicate infos for the current resource.
     *
     * @return array
     */
    public function getPredicates()
    {
        if (null === $this->_predicateResults) {
            $this->_predicateResults = array();
            
            // get results
            $results = $this->getQueryResults();
            
            // url object to build URLs
            $url = new OntoWiki_Url(array('route' => 'properties'), array('r'));
            
            foreach ($results as $graph => $resultsForGraph) {
                // set up title helper
                $this->_titleHelper->addResources($resultsForGraph, 'predicate');
                $this->_titleHelper->addResources($resultsForGraph, 'object');
            }
            
            foreach ($results as $graph => $resultsForGraph) {                
                $this->_predicateResults[$graph] = array();
                
                foreach ($resultsForGraph as $row) {
                    $predicateUri = $row['predicate']['value'];

                    if (!array_key_exists($predicateUri, $this->_predicateResults)) {
                        // title
                        $predicateTitle = $this->_titleHelper->getTitle($predicateUri, $this->_lang);
                        // url
                        $url->setParam('r', $predicateUri, true);
                        
                        $this->_predicateResults[$graph][$predicateUri] = array(
                            'uri'      => $predicateUri, 
                            'curi'     => OntoWiki_Utils::compactUri($predicateUri), 
                            'url'      => (string)$url, 
                            'title'    => $predicateTitle, 
                            'has_more' => false
                        );
                    }
                }
            }
        }
        
        return $this->_predicateResults;
    }
    
    public function getQueryResults()
    {
        // query if necessary
        if (null === $this->_queryResults) {
            $this->_queryResults = array();
            
            foreach ($this->_buildQueries() as $graph => $query) {
                $options = array(
                    'result_format'          => 'extended', 
                    'use_owl_imports'        => false,
                    'use_additional_imports' => false
                );
                
                $currentResults = $this->_store->sparqlQuery($query, $options);
                
                if (isset($currentResults['results']['bindings'])) {
                    $this->_queryResults[$graph] = $currentResults['results']['bindings'];
                } else {
                    $this->_queryResults[$graph] = array();
                }
            }
            
            // remove empty results
            $this->_queryResults = array_filter($this->_queryResults);
            // var_dump($this->_queryResults);
        }
        
        return $this->_queryResults;
    }
    
    /**
     * Returns an array of predicate values for the current resource.
     * The array is indexed with the predicate's URIs.
     *
     * @return array
     */
    public function getValues()
    {
        if (null === $this->_valueResults) {
            $this->_valueResults = array();
            
            // get results
            $results = $this->getQueryResults();
            
            // load predicates first
            $this->getPredicates();
            
            // URL object to build URLs
            $url = new OntoWiki_Url(array('route' => 'properties'), array('r'));
            
            // keep track of URI objects already used
            $objects = array();
            
            foreach ($results as $graph => $resultsForGraph) {
                $this->_valueResults[$graph] = array();
                
                foreach ($resultsForGraph as $row) {
                    $predicateUri = $row['predicate']['value'];

                    if (!array_key_exists($predicateUri, $objects)) {
                        $objects[$predicateUri] = array();
                    }

                    // create space for value information if not exists
                    if (!array_key_exists($predicateUri, $this->_valueResults[$graph])) {
                        $this->_valueResults[$graph][$predicateUri] = array();
                    }

                    // default values
                    $value = array(
                        'content'  => null, 
                        'object'   => null, 
                        'object_hash' => null,
                        'datatype' => null, 
                        'lang'     => null, 
                        'url'      => null, 
                        'uri'      => null, 
                        'curi'     => null
                    );

                    
                    switch ($row['object']['type']) {
                        case 'uri':
                            // every URI objects is only used once for each statement
                            if (in_array($row['object']['value'], $objects[$predicateUri])) {
                                continue;
                            }

                            // URL
                            $url->setParam('r', $row['object']['value'], true);
                            $value['url'] = (string)$url;

                            // URI
                            $value['uri'] = $row['object']['value'];

                            // title
                            $title = $this->_titleHelper->getTitle($row['object']['value'], $this->_lang);

                            /**
                             * @trigger onDisplayObjectPropertyValue Triggered if an object value of some 
                             * property is returned. Plugins can attach to this trigger in order to modify 
                             * the value that gets displayed.
                             * Event payload: value, property, title and link
                             */
                            // set up event
                            $event = new Erfurt_Event('onDisplayObjectPropertyValue');
                            $event->value    = $row['object']['value'];
                            $event->property = $predicateUri;
                            $event->title    = $title;
                            $event->link     = (string)$url;

                            // trigger
                            $value['object'] = $event->trigger();

                            if (!$event->handled()) {
                                // object (modified by plug-ins)
                                $value['object'] = $title;
                            }

                            array_push($objects[$predicateUri], $row['object']['value']);

                            break;

                        case 'typed-literal':
                            $event = new Erfurt_Event('onDisplayLiteralPropertyValue');
                            $value['datatype'] = OntoWiki_Utils::compactUri($row['object']['datatype']);
                            $literalString = Erfurt_Utils::buildLiteralString($row['object']['value'],
                                                                              $row['object']['datatype']);
                            $value['object_hash'] = md5($literalString);

                            $event->value    = $row['object']['value'];
                            $event->datatype = $row['object']['datatype'];
                            $event->property = $predicateUri;
                            $value['object'] = $event->trigger();
                            // keep unmodified value in content
                            $value['content'] = $row['object']['value'];

                            if (!$event->handled()) {
                                // object (modified by plug-ins)
                                $value['object'] = $row['object']['value'];
                            }

                            break;
                        case 'literal':
                            // original (unmodified) for RDFa
                            $value['content'] = $row['object']['value'];
                            $literalString = Erfurt_Utils::buildLiteralString($row['object']['value'],
                                                                              null,
                                                                              isset($row['object']['xml:lang']) ? $row['object']['xml:lang'] : null);
                            $value['object_hash'] = md5($literalString);

                            /**
                             * @trigger onDisplayLiteralPropertyValue Triggered if a literal value of some 
                             * property is returned. Plugins can attach to this trigger in order to modify 
                             * the value that gets displayed.
                             */
                            $event = new Erfurt_Event('onDisplayLiteralPropertyValue');
                            $event->value    = $row['object']['value'];
                            $event->property = $predicateUri;

                            // set literal language
                            if (isset($row['object']['xml:lang'])) {
                                $value['lang'] = $row['object']['xml:lang'];
                                $event->language = $row['object']['xml:lang'];
                            }
                            // trigger
                            $value['object']  = $event->trigger();
                            // keep unmodified value in content
                            $value['content'] = $row['object']['value'];

                            // set default if event has not been handled
                            if (!$event->handled()) {
                                $value['object'] = $row['object']['value'];
                            }
                            break;
                    }
                    


                    // push it only if it doesn't exceed number of items to display
                    if (count($this->_valueResults[$graph][$predicateUri]) < $this->_limit) {
                        array_push($this->_valueResults[$graph][$predicateUri], $value);
                    } else { $this->_predicateResults[$graph][$predicateUri]['has_more'] = true;}
                    if(count($this->_valueResults[$graph][$predicateUri]) > 1) {
                        // create the "has more link" (used for area context menu as well)
                        // do it only once per predicate
                        if (!isset($this->_predicateResults[$graph][$predicateUri]['has_more_link'])) {
                            //when all values are literal, we dont use a link to the list,but to the query editor
                            $allValuesAreLiterals = true;
                            foreach($this->_valueResults[$graph][$predicateUri] as $value){
                                if(isset($value['uri'])){
                                    $allValuesAreLiterals = false;
                                }
                            }
                            if(!$allValuesAreLiterals){
                                $hasMoreUrl = new OntoWiki_Url(array('route' => 'instances', 'action' => 'list'), array());
                                $filterExp = json_encode(array(
                                    'filter' => array(
                                        array (
                                            'action' => 'add',
                                            'mode' => 'box',
                                            'id' => 'allvalues',
                                            'property' => $predicateUri,
                                            'isInverse' => true,
                                            'propertyLabel' => "value",
                                            'filter' => 'equals',
                                            'value1' => $this->_uri,
                                            'value2' => null,
                                            'valuetype' => 'uri',
                                            'literaltype' => null,
                                            'hidden' => false
                                        )
                                    )
                                ));

                                $hasMoreUrl->setParam(
                                    'instancesconfig',
                                    $filterExp
                                )->setParam(
                                    'init',
                                    true
                                );
                            } else {
                                $hasMoreUrl = new OntoWiki_Url(array('controller' => 'queries', 'action' => 'editor'), array());
                                $hasMoreUrl->setParam(
                                    'query',
                                    'SELECT ?value WHERE {<'.$this->_uri.'> <'.$predicateUri.'> ?value}'
                                )->setParam(
                                    'immediate',
                                    true
                                );
                            }
                            
                            $this->_predicateResults[$graph][$predicateUri]['has_more_link'] = (string)$hasMoreUrl;
                        }
                    }
                }
            }

            return $this->_valueResults;
        }
    }
    
    /**
     * Builds the SPARQL query
     */
    private function _buildQueries()
    {
        $query  = new Erfurt_Sparql_Query2();
        
        $uri = new Erfurt_Sparql_Query2_IriRef($this->_uri);
        $pred_var = new Erfurt_Sparql_Query2_Var("predicate");
        $obj_var = new Erfurt_Sparql_Query2_Var("object");
        
        $query
            ->addTriple($uri, $pred_var, $obj_var);
        $query->addFilter(
            new Erfurt_Sparql_Query2_UnaryExpressionNot(
               new Erfurt_Sparql_Query2_isBlank(
                   $obj_var
               )
           )
        );

        if(!empty($this->_ignoredPredicates)){
            $or = new Erfurt_Sparql_Query2_ConditionalAndExpression();
            $filter = new Erfurt_Sparql_Query2_Filter($or);
            foreach($this->_ignoredPredicates as $ignored){
                    $or->addElement(
                        new Erfurt_Sparql_Query2_UnaryExpressionNot(
                            new Erfurt_Sparql_Query2_sameTerm(
                                $pred_var,
                                new Erfurt_Sparql_Query2_IriRef($ignored)
                            )
                        )
                    );
            }
            $query->getWhere()->addElement($filter);
        }
        
        $query
            ->setDistinct(true)
            ->addProjectionVar($pred_var)
            ->addProjectionVar($obj_var)
            ->getOrder()
                ->add($pred_var);

        $queries = array();
        $closure = Erfurt_App::getInstance()->getStore()->getImportsClosure(
            $this->_model->getModelUri(),
            true
        );
        $queryGraphs = array_merge(array($this->_graph), $closure);

        foreach ($queryGraphs as $currentGraph) {
            $query->setFroms(array($currentGraph));
            $queries[$currentGraph] = clone $query;
        }
        
        return $queries;
    }
}
