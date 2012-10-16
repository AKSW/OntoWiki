<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki resource list model class.
 *
 * Represents a list of resources (specified by filters) and their properties.
 * 
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @category OntoWiki
 * @package OntoWiki_Classes_Model
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Norman Heino <norman.heino@gmail.com>
 * @author Jonas Brekle <jonas.brekle@gmail.com>
 */
class OntoWiki_Model_Instances extends OntoWiki_Model
{
    /**
     * all triple (?s ?p ?o). is added and removed to the resorceQuery on demand, but always stored here
     * @var Erfurt_Sparql_Query2_IF_TriplesSameSubject
     */
    protected $_allTriple;

    /**
     * Properties whose values are to be fetched for each resource.
     * @var array
     */
    protected $_shownProperties = array();

    /**
     * @var array
     */
    protected $_shownPropertiesConverted = array();

    /**
     * @var bool
     */
    protected $_shownPropertiesConvertedUptodate  = false;

    /**
     * @var array
     */
    protected $_ignoredShownProperties = array(
        //EF_RDF_TYPE
    );

    /**
     * values of the set properties for all resources
     */
    protected $_values;

    /**
     * @var bool
     */
    protected $_valuesUptodate = false;

    protected $_valueQueryUptodate = false;

    /**
     * all resources
     */
    protected $_resources;

    /**
     * @var bool
     */
    protected $_resourcesUptodate = false;

    /**
     *
     * @var array transformed
     */
    protected $_resourcesConverted;

    /**
     * @var bool
     */
    protected $_resourcesConvertedUptodate = false;

    /**
     * 
     * @var Erfurt_Sparql_Query2_Var the var that is used in the resourcequery to bind all uri of list elements
     */
    protected $_resourceVar = null;

    /**
     * @var array stores all configured filters
     */
    protected $_filter = array();

    /**
     * Result array - what comes back when evaluating the query.
     * @var array
     */
    protected $_results = null;

    /**
     * @var bool
     */
    protected $_resultsUptodate = false;

    /**
     * @var Erfurt_Sparql_Query2 the manged query that selects the resources in the list
     */
    protected $_resourceQuery = null;

    /**
     * @var Erfurt_Sparql_Query2_IF_TriplesSameSubject
     */
    protected $_sortTriple = null;

    /**
     * @var Erfurt_Sparql_Query2
     */
    protected $_valueQuery = null;

    /**
     * @var Erfurt_Sparql_Query2_Filter
     */
    protected $_valueQueryResourceFilter = null;

    /**
     * @var bool
     */
    protected $_useCache = true;

    /**
     * @var OntoWiki_Model_TitleHelper
     */
    protected $_titleHelper = null;

    /**
     * Constructor
     */
    public function __construct (Erfurt_Store $store, Erfurt_Rdf_Model $model, $options = array())
    {
        parent::__construct($store, $model);

        if (isset($options[Erfurt_Store::USE_CACHE])) {
            $this->_useCache = $options[Erfurt_Store::USE_CACHE];
        }

        //TODO still needed?
        $this->_defaultUrl['resource']      = new OntoWiki_Url(array('route' => 'properties'), array());
        $this->_defaultUrlParam['resource'] = 'r';

        $this->_resourceQuery   =  new Erfurt_Sparql_Query2();
        $this->_resourceVar = new Erfurt_Sparql_Query2_Var('resourceUri');

        $this->_allTriple = new Erfurt_Sparql_Query2_Triple(
            $this->_resourceVar,
            new Erfurt_Sparql_Query2_Var('p'),
            new Erfurt_Sparql_Query2_Var('o')
        );
        $this->addAllTriple();

        //show resource uri
        $this->_resourceQuery->addProjectionVar($this->_resourceVar);
        $this->_resourceQuery
            ->setLimit(10) //per default query only for 10 resources
            ->setDistinct(true);
            //->getOrder()
            //    ->add($this->_resourceVar);

        // when resourceVar is the object - prevent literals
        $this->_resourceQuery->addFilter(
            new Erfurt_Sparql_Query2_ConditionalAndExpression(
                array(
                    //new Erfurt_Sparql_Query2_isUri($this->_resourceVar),

                    new Erfurt_Sparql_Query2_UnaryExpressionNot(
                        new Erfurt_Sparql_Query2_isBlank($this->_resourceVar)
                    )
                )
            )
        );

        //build value query
        $this->_valueQuery = new Erfurt_Sparql_Query2();

        $this->_valueQuery
            ->addProjectionVar($this->_resourceVar)
            ->setDistinct(true);

        //always query for type
        $this->addShownProperty(EF_RDF_TYPE, '__TYPE');

        //set froms to the requested graph
        $this->_valueQuery->addFrom((string)$model);
        $this->_resourceQuery->addFrom((string)$model);

        $this->invalidate();
    }

    /**
     * dont keep the references to the query objects in $this->resourceQuery (the must be cloned too)
     */
    public function __clone()
    {
        foreach ($this as $key => $val) {
            if (is_object($val)||(is_array($val))) {
                $this->{$key} = unserialize(serialize($val));
                //$this->$key = clone($this->$key);
            }
        }
    }

    /**
     * redirect calls that cant be handled - both to the resource and value query. 
     * currently only methods regarding the FROM are allowed
     * @param string $name
     * @param array $arguments
     */
    public function  __call($name,  $arguments)
    {
        $allowedMethods = array(
            'addFrom', 'addFroms', 'removeFrom', 'removeFroms',
            'hasFrom', 'getFrom', 'getFroms', 'setFrom', 'setFroms'
        );
        if (in_array($name, $allowedMethods) &&
            (method_exists($this->_valueQuery, $name) && method_exists($this->_resourceQuery, $name))
        ) {
            call_user_func_array(array($this->_valueQuery, $name), $arguments);
            $ret = call_user_func_array(array($this->_resourceQuery, $name), $arguments);
            if (strpos($name, 'get') === 0 || strpos($name, 'has') === 0) {
                return $ret;
            } else {
                return $this;
            }
        } else {
            throw new RuntimeException("OntoWiki_Model_Instances: method $name does not exists");
        }
    }

    public function __sleep()
    {
        //dont save reference to the store
        return array_diff(array_keys(get_object_vars($this)), array('_store', '_titleHelper'));
    }

    public function __wakeup()
    {
        //after serialisation, the store state may has been changed
        $this->invalidate();
    }

    /**
     * 
     * Method for setting the store explicitely
     * (necessary after unserialization from session for unavailable _dbconn 
     * resource handles etc.)
     * @param Erfurt_Store $store
     * @throws Exception for parameter type being incorrect (no Erfurt_Store Object)
     * @return null
     */
    public function setStore(Erfurt_Store $store)
    {
        $this->_store = $store;
        $this->restoreTitleHelper();
    }

    /**
     * 
     * @throws OntoWiki_Exception
     */
    protected function restoreTitleHelper()
    {
        $this->_titleHelper = new OntoWiki_Model_TitleHelper($this->_model, $this->_store);
    }

    /**
     * set title helper (for unittests)
     * @param OntoWiki_Model_TitleHelper $t
     */
    public function setTitleHelper(OntoWiki_Model_TitleHelper $t)
    {
        $this->_titleHelper = $t;
    }

    /**
     * get title helper (for unittests)
     * @return OntoWiki_Model_TitleHelper 
     */
    public function getTitleHelper()
    {
        return $this->_titleHelper;
    }

    /**
     * add ?resourceUri ?p ?o to the resource query
     * TODO: support objects as resources? optionally?
     */
    public function addAllTriple()
    {
        $this->_resourceQuery->addElement($this->_allTriple);
    }

    /**
     * remove ?resourceUri ?p ?o from the resource query
     */
    public function removeAllTriple()
    {
        $this->_allTriple->remove($this->_resourceQuery);
    }

    /**
     * get the object that represents the "?resourceUri ?p ?o" from the resource query.
     * object exists, even if not part of the query
     */
    public function getAllTriple()
    {
        return $this->_allTriple;
    }

    /**
     * Adds a property to the properties fetched for every resource.
     *
     * @param $propertyUri The URI of the property
     * @param $propertyName Name to be used for the variable
     * @return OntoWiki_Model_Instances
     */
    public function addShownProperty(
        $propertyUri,
        $propertyName = null,
        $inverse = false,
        $datatype = null,
        $hidden = false
    )
    {
        if (in_array($propertyUri, $this->_ignoredShownProperties)) {
            return $this; //no action
        }

        if (!$propertyName) {
            $propertyName = preg_replace('/^.*[#\/]/', '', $propertyUri);
            $propertyName = str_replace('-', '', $propertyName);
        }

        $used = false;
        foreach ($this->_shownProperties as $shownProp) {
            if ($shownProp['name'] == $propertyName) {
                $used = true;
            }
        }
        //solve duplicate name problem by adding counter
        if ($used) {
            $counter = 2;
            while ($used) {
                $name = $propertyName . $counter++;
                $used = false;
                foreach ($this->_shownProperties as $shownProp) {
                    if ($shownProp['name'] == $name) {
                        $used = true;
                    }
                }
            }

            $propertyName = $name;
        }

        $ret = Erfurt_Sparql_Query2_Abstraction_ClassNode::addShownPropertyHelper(
            $this->_valueQuery,
            $this->_resourceVar,
            $propertyUri,
            $propertyName,
            $inverse
        );

        $this->_shownProperties[$propertyUri.'-'.($inverse?'inverse':'direct')] = array(
            'uri' => $propertyUri,
            'name' => $propertyName,
            'inverse' => $inverse,
            'datatype' => $datatype,
            'varName' => $ret['var']->getName(),
            'var' => $ret['var'],
            'optionalpart' => $ret['optional'],
            'filter' => $ret['filter'],
            'hidden' => $hidden
        );

        $this->_valuesUptodate = false; // getValues will not use the cache next time
        $this->_resultsUptodate = false;

        return $this;
    }

    /**
     * add a shown property, that is more complex.
     * provide own triples, they will wrapped in an optional and a projection var will be added.
     * @param array $triples
     * @param Erfurt_Sparql_Query2_Var $var
     * @param boolean $hidden
     * @return OntoWiki_Model_Instances
     */
    public function addShownPropertyCustom($triples, $var, $hidden = false)
    {
        //add
        $optional = new Erfurt_Sparql_Query2_OptionalGraphPattern();
        $optional->addElements($triples);
        $this->_valueQuery->getWhere()->addElement($optional);
        $this->_valueQuery->addProjectionVar($var);

        //save
        $this->_shownProperties['custom'.count($this->_shownProperties)] = array(
            'uri' => null,
            'name' => 'custom'.count($this->_shownProperties),
            'inverse' => false,
            'datatype' => null,
            'varName' => $var->getName(),
            'var' => $var,
            'optionalpart' => $triples,
            'filter' => array(),
            'hidden' => $hidden
        );
        $this->_valuesUptodate = false; // getValues will not use the cache next time
        $this->_resultsUptodate = false;

        return $this;
    }

    /**
     * remove a property that has been added before
     * @param string $key the uri
     */
    public function removeShownProperty($property, $inverse)
    {
        $key = $property.'-'.($inverse?'inverse':'direct');
        if (isset($this->_shownProperties[$key])) {
            $prop =  $this->_shownProperties[$key];
            $this->_valueQuery->removeProjectionVar($prop['var']);
            $prop['optionalpart']->remove($this->_valueQuery);
            //$prop['filter']->remove($this->_resourceQuery);
            unset($this->_shownProperties[$key]);
            return true;
        } else return false;
    }

    /**
     * queries for values (unconverted)
     * @return array
     */
    public function getResults()
    {
        $this->updateValueQuery();

        if (!$this->_resultsUptodate) {
            $this->_results = $this->_store->sparqlQuery(
                $this->_valueQuery,
                array(
                    Erfurt_Store::RESULTFORMAT => Erfurt_Store::RESULTFORMAT_EXTENDED,
                    Erfurt_Store::USE_CACHE => $this->_useCache
                )
            );
            $this->_resultsUptodate = true;
        }

        return $this->_results;
    }

    /**
     * add a filter from the filter box - these filters match some predefined schemes
     * (like "equals", "contains")
     * @param string $property
     * @param boolean $isInverse whether the property is a indirect property of the resources
     * @param string $propertyLabel label affects the created variable
     * @param string $filter the type of filter ("equals", "bound", "larger", "smaller", "between", "contains")
     * @param string $value
     * @param string $valueSecondary
     * @param string $valuetype ("uri" or "literal" or "typed-literal")
     * @param string $literaltype (if valuetype is set to "typed-literal", you can pass a URI here)
     * @param boolean $hidden  whether to hide this filter in the filter box GUI
     * @param string $id optional predined ID, will be generated normally
     * @param boolean $negate whether to invert the condition
     * @param boolean $optional
     * @return string id
     * @throws RuntimeException
     */
    public function addFilter (
       $property,
       $isInverse,
       $propertyLabel,
       $filter,
       $value = null,
       $valueSecondary = null,
       $valuetype = 'literal',
       $literaltype = null,
       $hidden = false,
       $id = null,
       $negate = false,
       $optional = false
    )
    {
        if ($id == null) {
            $id = 'box' . count($this->_filter);
        } else {
            if (isset($this->_filter[$id])) {
                $this->removeFilter($id);
            }
        }
        $prop = new Erfurt_Sparql_Query2_IriRef($property);
        if (!empty($value)) {
            switch($valuetype) {
                case 'uri':
                    $valueObj = new Erfurt_Sparql_Query2_IriRef($value);

                    if (!empty($valueSecondary)) {
                        $valueSecondaryObj = new Erfurt_Sparql_Query2_IriRef($valueSecondary);
                    }
                    break;
                case 'literal':
                    if (!empty($literaltype)) {
                        //with language tags
                        $valueObj = new Erfurt_Sparql_Query2_RDFLiteral(
                            $value,
                            $literaltype
                        );
                        if (!empty($valueSecondary)) {
                            $valueSecondaryObj = new Erfurt_Sparql_Query2_RDFLiteral(
                                $valueSecondary,
                                $literaltype
                            );
                        }
                    } else {
                        //no language tags
                        if (is_array($value)) {
                            $value = current($value);
                        }
                        $meta = '';
                        if (is_scalar($value) && !is_string($value) && !is_array($value)) {
                            $meta = gettype($value);
                        }
                        $valueObj = new Erfurt_Sparql_Query2_RDFLiteral($value, $meta);
                        if (!empty($valueSecondary)) {
                            $valueSecondaryObj = new Erfurt_Sparql_Query2_RDFLiteral($valueSecondary, $meta);
                        }
                    }
                    break;
                case 'typed-literal':
                    if (in_array($literaltype, Erfurt_Sparql_Query2_RDFLiteral::$knownShortcuts)) {
                        //is something like "bool" or "int" - will be converted from "1"^^xsd:int to 1
                        $valueObj = new Erfurt_Sparql_Query2_RDFLiteral($value, $literaltype);
                        if (!empty($valueSecondary)) {
                            $valueSecondaryObj = new Erfurt_Sparql_Query2_RDFLiteral($valueSecondary, $literaltype);
                        }
                    } else {
                        // is a uri
                        $valueObj = new Erfurt_Sparql_Query2_RDFLiteral(
                            $value,
                            new Erfurt_Sparql_Query2_IriRef($literaltype)
                        );
                        if (!empty($valueSecondary)) {
                            $valueSecondaryObj = new Erfurt_Sparql_Query2_RDFLiteral(
                                $valueSecondary,
                                new Erfurt_Sparql_Query2_IriRef($literaltype)
                            );
                        }
                    }
                    break;
                default:
                    throw new RuntimeException(
                        'called Ontowiki_Model_Instances::addFilter with '.
                        'unknown param-value: valuetype = "'.$valuetype.'"'
                    );
                    break;
            }
        }

        switch ($filter) {
            case 'contains':
                $var = new Erfurt_Sparql_Query2_Var($propertyLabel);
                if (!$isInverse) {
                    $triple = $this->_resourceQuery->addTriple(
                        $this->_resourceVar,
                        $prop,
                        $var
                    );
                } else {
                    $triple = $this->_resourceQuery->addTriple(
                        $var,
                        $prop,
                        $this->_resourceVar
                    );
                }
                $valueObj->setValue(str_replace("\\", "\\\\", preg_quote($valueObj->getValue())));
                $filterObj = $this->_resourceQuery->addFilter(
                    !$negate ?
                    new Erfurt_Sparql_Query2_Regex(
                        new Erfurt_Sparql_Query2_Str($var),
                        $valueObj
                    )
                    :
                    new Erfurt_Sparql_Query2_UnaryExpressionNot(
                        new Erfurt_Sparql_Query2_Regex(
                            new Erfurt_Sparql_Query2_Str($var),
                            $valueObj
                        )
                    )
                );
                break;
            case 'equals':
                if ($valuetype == 'literal') {
                    $valueVar = new Erfurt_Sparql_Query2_Var($propertyLabel);
                    if (!$isInverse) {
                        $triple = new Erfurt_Sparql_Query2_Triple(
                            $this->_resourceVar,
                            $prop,
                            $valueVar
                        );
                    } else {
                        throw new RuntimeException(
                            'literal as value for an inverse property '.
                            'is a literal subject which is not allowed'
                        );
                    }

                    if ($negate) {
                        $optionalGP = new Erfurt_Sparql_Query2_OptionalGraphPattern();
                        $optionalGP->addElement($triple);
                        $this->_resourceQuery->addElement($optionalGP);
                        $triple = $optionalGP;

                        if ($optional) {
                            $orExpression = new Erfurt_Sparql_Query2_ConditionalOrExpression();
                            $orExpression->addElement(
                                new Erfurt_Sparql_Query2_UnaryExpressionNot(
                                    new Erfurt_Sparql_Query2_bound($valueVar)
                                )
                            );
                            $orExpression->addElement(new Erfurt_Sparql_Query2_NotEquals($valueVar, $valueObj));

                            $filterObj = $this->_resourceQuery->addFilter($orExpression);
                        } else {
                            $filterObj = $this->_resourceQuery->addFilter(
                                new Erfurt_Sparql_Query2_NotEquals($valueVar, $valueObj)
                            );
                        }
                    } else {
                        $this->_resourceQuery->addElement($triple);
                        $filterObj = $this->_resourceQuery->addFilter(
                            new Erfurt_Sparql_Query2_Regex(
                                new Erfurt_Sparql_Query2_Str($valueVar),
                                new Erfurt_Sparql_Query2_RDFLiteral(
                                    '^'.str_replace("\\", "\\\\", preg_quote($value)).'$'
                                )
                            )
                        );
                    }
                } else {
                    if (!$isInverse) {
                        $triple = $this->_resourceQuery->addTriple(
                            $this->_resourceVar,
                            $prop,
                            $valueObj
                        );
                    } else {
                        $triple = $this->_resourceQuery->addTriple(
                            $valueObj,
                            $prop,
                            $this->_resourceVar
                        );
                    }
                }
                break;
            case 'larger':
                $var = new Erfurt_Sparql_Query2_Var($propertyLabel);
                if (!$isInverse) {
                    $triple = $this->_resourceQuery->addTriple(
                        $this->_resourceVar,
                        $prop,
                        $var
                    );
                } else {
                    $triple = $this->_resourceQuery->addTriple(
                        $var,
                        $prop,
                        $this->_resourceVar
                    );
                }

                $filterObj = $this->_resourceQuery->addFilter(
                    new Erfurt_Sparql_Query2_Larger($var, $valueObj)
                );
                break;
            case 'smaller':
                $var = new Erfurt_Sparql_Query2_Var($propertyLabel);
                if (!$isInverse) {
                    $triple = $this->_resourceQuery->addTriple(
                        $this->_resourceVar,
                        $prop,
                        $var
                    );
                } else {
                    $triple = $this->_resourceQuery->addTriple(
                        $var,
                        $prop,
                        $this->_resourceVar
                    );
                }

                $filterObj = $this->_resourceQuery->addFilter(
                    new Erfurt_Sparql_Query2_Smaller($var, $valueObj)
                );
                break;
            case 'between':
                $var = new Erfurt_Sparql_Query2_Var($propertyLabel);
                if (!$isInverse) {
                    $triple = $this->_resourceQuery->addTriple(
                        $this->_resourceVar,
                        $prop,
                        $var
                    );
                } else {
                    $triple = $this->_resourceQuery->addTriple(
                        $var,
                        $prop,
                        $this->_resourceVar
                    );
                }

                $filterObj = $this->_resourceQuery->addFilter(
                    new Erfurt_Sparql_Query2_ConditionalAndExpression(
                        array(
                            new Erfurt_Sparql_Query2_Larger($var, $valueObj),
                            new Erfurt_Sparql_Query2_Smaller($var, $valueSecondaryObj)
                        )
                    )
                );
                break;
            case 'bound':
                $var = new Erfurt_Sparql_Query2_Var($propertyLabel);

                if (!$isInverse) {
                    $triple = new Erfurt_Sparql_Query2_Triple(
                        $this->_resourceVar,
                        $prop,
                        $var
                    );
                } else {
                    $triple = new Erfurt_Sparql_Query2_Triple(
                        $var,
                        $prop,
                        $this->_resourceVar
                    );
                }
                if ($negate) {
                    $optionalGP = new Erfurt_Sparql_Query2_OptionalGraphPattern();
                    $optionalGP->addElement($triple);
                    $this->_resourceQuery->addElement($optionalGP);
                    $triple = $optionalGP; // to save this obj (see underneath 20 lines)
                } else {
                    $this->_resourceQuery->addElement($triple);
                }

                if ($negate) {
                    $filterObj = $this->_resourceQuery->addFilter(
                        new Erfurt_Sparql_Query2_UnaryExpressionNot(
                            new Erfurt_Sparql_Query2_bound($var)
                        )
                    );
                }
                break;
            default:
                throw new RuntimeException(
                    'called Ontowiki_Model_Instances::addFilter with '.
                    'unknown param-value: filtertype='.$filter
                );
                break;
        }

        //these filters bring their own triple
        $this->removeAllTriple();

        //save
        $this->_filter[$id] = array(
             'id'               => $id,
             'mode'             => 'box',
             'property'         => $property,
             'isInverse'        => $isInverse,
             'propertyLabel'    => $propertyLabel,
             'filter'           => $filter,
             'value1'           => $value,
             'value2'           => $valueSecondary,
             'valuetype'        => $valuetype,
             'literaltype'      => $literaltype,
             'hidden'           => $hidden,
             'negate'           => $negate,
             'objects'          => array($triple, isset($filterObj) ? $filterObj : null)
        );

        $this->invalidate();
        return $id;
    }

    /**
     * remove a filter by id
     * @param string $id
     * @return OntoWiki_Model_Instances $this
     */
    public function removeFilter($id)
    {
        if (isset($this->_filter[$id])) {
            foreach ($this->_filter[$id]['objects'] as $obj) {
                if ($obj instanceof Erfurt_Sparql_Query2_ElementHelper) {
                    $obj->remove($this->_resourceQuery);
                }
            }

            unset($this->_filter[$id]);

            //when all deleted
            //empty == 1 left because this last element is the "isUri and !isBlank"-filter
            if (count($this->_resourceQuery->getWhere()->getElements()) == 1) {
                $this->addAllTriple();
            }

            $this->invalidate();
            return $this;
        }
    }

    /**
     * get the internal array that holds the filters
     * @return array
     */
    public function getFilter()
    {
        return $this->_filter;
    }

    /**
     *
     * @param string $type the uri of the class
     * @param string $id
     * @param array $options
     * @return int id
     */
    public function addTypeFilter($type, $id = null, $option = array())
    {
        if ($id == null) {
            $id = 'type' . count($this->_filter);
        } else {
            if (isset($this->_filter[$id])) {
                $this->removeFilter($id);
            }
        }

        //shortcut navigation - only a rdfs class given
        $options['mode'] = 'instances';
        $options['type'] = $type;
        $options['memberPredicate'] = EF_RDF_TYPE;
        $options['withChilds'] = isset($option['withChilds']) ? $option['withChilds'] : true;

        $options['hierarchyUp'] = EF_RDFS_SUBCLASSOF;
        $options['hierarchyIsInverse'] = true;
        //$options['hierarchyDown'] = null;
        $options['direction'] = 1; // down the tree

        $memberPredicate = $options['memberPredicate'];
        if (is_string($memberPredicate)) {
            $memberPredicate = new Erfurt_Sparql_Query2_IriRef($memberPredicate);
        }

        if (!($memberPredicate instanceof Erfurt_Sparql_Query2_Verb)) {
            throw new RuntimeException(
                'Option "member_predicate" passed to Ontowiki_Model_Instances '.
                'must be an instance of Erfurt_Sparql_Query2_IriRef '.
                'or string instance of '.typeHelper($memberPredicate).' given'
            );
        }

        $type = new Erfurt_Sparql_Query2_IriRef($options['type']);
        $subClasses = array();
        if ($options['withChilds']) {
            $subClasses = array_keys(
                // get subclasses:
                $this->_store->getTransitiveClosure(
                    $this->_graph,
                    $options['hierarchyUp'],
                    array($type->getIri()),
                    $options['hierarchyIsInverse']
                )
            );
        } else if (isset($options['subtypes'])) { //dont query, take the given. maybe the new navigation can use this
            $subClasses = $options['subtypes'];
        } else {
            $subClasses = array();
        }

        if (count($subClasses) > 1) {
            // there are subclasses. "1" because the class itself is somehow included in the subclasses...
            $typeVar = new Erfurt_Sparql_Query2_Var($type);
            $triple = $this->_resourceQuery->addTriple(
                $this->_resourceVar,
                $memberPredicate,
                $typeVar
            );

            $or = new Erfurt_Sparql_Query2_ConditionalOrExpression();
            foreach ($subClasses as $subclass) {
                $or->addElement(
                    new Erfurt_Sparql_Query2_Equals(
                        $typeVar,
                        new Erfurt_Sparql_Query2_IriRef($subclass)
                    )
                );
            }

            $filterObj = $this->_resourceQuery->addFilter($or);
        } else {
            // no subclasses
            $triple = $this->_resourceQuery->addTriple(
                $this->_resourceVar,
                $memberPredicate,
                new Erfurt_Sparql_Query2_IriRef($type->getIri())
            );
        }

        //save
        $this->_filter[$id] = array(
             'id'               => $id,
             'mode'             => 'rdfsclass',
             'rdfsclass'        => $options['type'],
             'withChilds'       => $options['withChilds'],
             'objects'          => array($triple, isset($filterObj) ? $filterObj : null)
        );

        //these filters bring there own triple
        $this->removeAllTriple();

        $this->invalidate();

        return $id;
    }

    /**
     *
     * @param string $str
     * @param string $id optional
     * @return string the id used
     */
    public function addSearchFilter($str, $id = null)
    {
        if ($id == null) {
            $id = 'search' . count($this->_filter);
        } else {
            if (isset($this->_filter[$id])) {
                $this->removeFilter($id);
            }
        }
        $pattern = $this->_store->getSearchPattern(
            $str,
            $this->_graph
        );

        $vars = array();

        foreach ($pattern as $element) {
            if (method_exists($element, 'getVars')) {
                $vars = array_merge($vars, $element->getVars());
            }
        }
        $count = count($this->_filter);
        foreach ($vars as $var) {
            if ($var->getName() == 'o' || $var->getName() == 'p') {
                $var->setName($var->getName().$count);
            }
        }
        $this->_resourceQuery->addElements($pattern);

        //save
        $this->_filter[$id] = array(
             'id'               => $id,
             'mode'             => 'search',
             'searchText'       => $str,
             'objects'           => $pattern
        );

        //these filters bring there own triple
        $this->removeAllTriple();

        $this->invalidate();

        return $id;
    }

    /**
     * add arbitrary triples to the query to filter (used by the navigation)
     * @param array $triples
     * @param string $id
     * @return string the id used
     */
    public function addTripleFilter($triples, $id = null)
    {
        if ($id == null) {
            $id = 'triple' . count($this->_filter);
        } else {
            if (isset($this->_filter[$id])) {
                $this->removeFilter($id);
            }
        }
        $this->_resourceQuery->addElements($triples);

        //save
        $this->_filter[$id] = array(
             'id'               => $id,
             'mode'             => 'triples',
             'objects'           => $triples
        );

        //these filters bring there own triple
        $this->removeAllTriple();

        $this->invalidate();

        return $id;
    }

    /**
     * get the query used to get the values (shownproperties)
     * @deprecated 
     * @see getValueQuery
     * @return Erfurt_Sparql_Query2
     */
    public function getQuery()
    {
        return $this->getValueQuery();
    }

    /**
     * get the query used to get the values (shownproperties)
     * @return Erfurt_Sparql_Query2
     */
    public function getValueQuery()
    {
        $this->updateValueQuery();
        return $this->_valueQuery;
    }

    /**
     * get the query used for getting the resources. incl. filter
     * @return Erfurt_Sparql_Query2
     */
    public function getResourceQuery()
    {
        return $this->_resourceQuery;
    }

    /**
     * build a link that recreates the current state on a different system
     * @return string
     */
    public function getPermalink($listname)
    {
        $url = new OntoWiki_Url();
        $url->init = true;

        if ($this->getOffset() != 0 && $this->getLimit() != 0) {
            $url->p=(($this->getOffset() / $this->getLimit()) + 1 );
        }
        if ($this->getLimit() != 10) {
            $url->limit=$this->getLimit();
        }
        $url->list=$listname;

        $conf = array();

        if (is_array($this->_shownProperties) && count($this->_shownProperties) > 0) {
            $conf['shownProperties'] = array();

            foreach ($this->_shownProperties as $shownProperty) {
                $conf['shownProperties'][] = array(
                    'uri' => $shownProperty['uri'],
                    'label' => $shownProperty['name'],
                    'inverse' => $shownProperty['inverse'],
                    'action' => 'add'
                );
            }
        }
        if (is_array($this->_filter) && count($this->_filter) > 0) {
            $conf['filter'] = array();

            foreach ($this->_filter as $filter) {
                switch($filter['mode']){
                    case 'box':
                        $arr = array(
                            'action' => 'add',
                            'mode' => 'box'
                        );
                        $arr = array_merge($arr, $filter);
                        $conf['filter'][] = $arr;
                        break;
                    case 'search':
                        $conf['filter'][] = array(
                            'action' => 'add',
                            'mode' => 'search',
                            'searchText' => $filter['searchText']
                        );
                        break;
                    case 'rdfsclass':
                        $conf['filter'][] = array(
                            'action' => 'add',
                            'mode' => 'rdfsclass',
                            'rdfsclass' => $filter['rdfsclass']
                        );
                        break;
                    case 'triples':
                        //problem: php objects can not be json encoded ...
                        /*
                        $conf['filter'][] = array(
                            'action' => 'add',
                            'mode' => 'triples',
                            'triples' => $filter['triples']
                        );
                        */
                        break;
                }
            }
            $url->m= (string) $this->_model;
            if (!empty($conf)) {
                $url->instancesconfig = json_encode($conf);
            }
        }

        return $url;
    }

    /**
     * @return Erfurt_sparql_Query2_Var the var that is used as subject in the query
     */
    public function getResourceVar ()
    {
        return $this->_resourceVar;
    }

    /**
     * Returns the property values for all resources at once.
     *
     * @return array
     */
    public function getValues ()
    {
        if ($this->_valuesUptodate) {
            return $this->_values;
        } else $this->updateValueQuery();

        if (empty($this->_resources)) {
            return array();
        }

        $this->getResults();

        $result = $this->_results['results']['bindings'];

        //fill titlehelper
        foreach ($result as $row) {
            foreach ($this->_shownProperties as $property) {
                if (
                    isset($row[$property['varName']])
                    && $row[$property['varName']]['type'] == 'uri'
                    && substr($row[$property['varName']]['value'], 0, 2) != '_:'
                ) {
                    $this->_titleHelper->addResource($row[$property['varName']]['value']);
                }
            }
            if (isset($row['__TYPE']) &&
                 $row['__TYPE']['type'] == 'uri' //sould both be true
                ) {
                    $this->_titleHelper->addResource($row['__TYPE']['value']);
            }
            $this->_titleHelper->addResource($row[$this->_resourceVar->getName()]['value']);
        }

        $valueResults = array();
        foreach ($result as $row) {
            $resourceUri = $row[$this->_resourceVar->getName()]['value'];

            if (!array_key_exists($resourceUri, $valueResults)) {
                $valueResults[$resourceUri] = array();
            }

            $url = new OntoWiki_Url(array('route' => 'properties'), array('r'));

            $value = null;
            $link  = null;

            foreach ($row as $varName => $data) {
                if (!isset($valueResults[$resourceUri][$varName])) {
                    $valueResults[$resourceUri][$varName] = array();
                }

                if ($data['type'] == 'uri') {
                    if (substr($data['value'], 0, 2) == '_:') {
                        continue; // skip blanknode values here due to backend problems with filters
                    }

                    // object type is uri --> handle object property
                    $objectUri = $data['value'];
                    $url->setParam('r', $objectUri, true);
                    $link = (string)$url;

                    // set up event
                    $event = new Erfurt_Event('onDisplayObjectPropertyValue');

                    //find uri
                    foreach ($this->_shownProperties as $property) {
                        if ($varName == $property['varName']) {
                            $event->property = $property['uri'];
                        }
                    }

                    $event->value    = $objectUri;

                    // trigger
                    $value = $event->trigger();

                    // set default if event has not been handled
                    if (!$event->handled()) {
                        $value = $this->_titleHelper->getTitle($objectUri);
                    }
                } else {
                    // object is a literal
                    $object = $data['value'];

                    $propertyUri = null;
                    foreach ($this->_shownProperties as $property) {
                        if ($varName == $property['varName']) {
                            $propertyUri = $property['uri'];
                        }
                    }

                    if ($object !== null) {
                        // set up event
                        $event = new Erfurt_Event('onDisplayLiteralPropertyValue');
                        $event->property = $propertyUri;
                        $event->value    = $object;
                        $event->setDefault($object);

                        // trigger
                        $value = $event->trigger();
                    }
                }

                //check for dulplicate values
                if (isset($valueResults[$resourceUri][$varName])) {
                    foreach ($valueResults[$resourceUri][$varName] as $old) {
                        if ($old['origvalue'] == $data['value'] && $old['type'] == $data['type']) {
                            $link = null;
                            continue 2; // dont add this value
                        }
                    }
                }

                //add value
                $valueResults[$resourceUri][$varName][] = array(
                  'value' => $value,
                  'origvalue' => $data['value'],
                  'type'  => $data['type'],
                  'url'   => $link,
                  'uri'   => $data['value'] //TODO: rename (can be literal) -> use origvalue + type to output uri
                );

                $value = null;
                $link  = null;
            }
        }

        foreach ($this->getShownResources() as $resource) {
            if (!isset($valueResults[$resource['value']])) {
                //there are no statements about this resource
                $valueResults[$resource['value']] = array();
            }
        }

        $this->_values = $valueResults;
        $this->_valuesUptodate = true;

        return $valueResults;
    }

    public function getAllPropertiesQuery($inverse = false)
    {
        $query = clone $this->_resourceQuery;
        $query
            ->removeAllProjectionVars()
            ->setDistinct(true)
            ->setLimit(0)
            ->setOffset(0);
        $vars = $query->getWhere()->getVars();
        $resourceVar = $this->getResourceVar();
        foreach ($vars as $var) {
            if ($var->getName() == $resourceVar->getName()) {
                $var->setName('listresource');
            }
        }
        $listResource = new Erfurt_Sparql_Query2_Var('listresource');
        $predVar = new Erfurt_Sparql_Query2_Var('resourceUri');
        if (!$inverse) {
            $query->addTriple(
                $listResource,
                $predVar,
                new Erfurt_Sparql_Query2_Var('showPropsObj')
            );
        } else {
            $query->addTriple(
                new Erfurt_Sparql_Query2_Var('showPropsSubj'),
                $predVar,
                $listResource
            );
        }

        $query
            ->addProjectionVar($predVar)
            ->getOrder()
                ->clear();
        return $query;
    }

    public function getAllProperties ($inverse = false)
    {
        if (empty($this->_resources) && $this->_resourcesUptodate) {
            return array();
        }

        $query = $this->getAllPropertiesQuery($inverse);

        $results = $this->_store->sparqlQuery(
            $query,
            array(
                Erfurt_Store::RESULTFORMAT => Erfurt_Store::RESULTFORMAT_EXTENDED,
                Erfurt_Store::USE_CACHE => $this->_useCache
            )
        );

        $properties = array();
        foreach ($results['results']['bindings'] as $row) {
            $properties[] = array('uri' => $row['resourceUri']['value']);
        }

        return $this->convertProperties($properties);
    }


    /**
     * get the bound values for a predicate
     * @param Erfurt_Sparql_Query2_IriRef|string $property
     * @param boolean $distinct
     * @param boolean $inverse
     * @return array
     */
    public function getPossibleValues ($property, $distinct = true, $inverse = false)
    {
        if (is_string($property)) {
            $property = new Erfurt_Sparql_Query2_IriRef($property);
        }
        if (!($property instanceof Erfurt_Sparql_Query2_IriRef)) {
            throw new RuntimeException(
                'Argument 1 passed to OntoWiki_Model_Instances::getObjects '.
                'must be instance of string or Erfurt_Sparql_Query2_IriRef, '.
                typeHelper($property).' given'
            );
        }

        $query = clone $this->_resourceQuery;
        $query
            ->removeAllProjectionVars()
            ->setDistinct($distinct)
            ->setLimit(0)
            ->setOffset(0);

        $valueVar = new Erfurt_Sparql_Query2_Var('obj');
        if ($inverse) {
            $query->addTriple($valueVar, $property, $this->_resourceVar);
        } else {
            $query->addTriple($this->_resourceVar, $property, $valueVar);
        }
        $query->addFilter(
            new Erfurt_Sparql_Query2_ConditionalAndExpression(
                array(
                    //new Erfurt_Sparql_Query2_isUri($valueVar),
                    // when resourceVar is the object - prevent literals
                    new Erfurt_Sparql_Query2_UnaryExpressionNot(
                        new Erfurt_Sparql_Query2_isBlank($valueVar)
                    )
                )
            )
        );
        $query->addProjectionVar($valueVar);
        $results = $this->_store->sparqlQuery(
            $query,
            array(
                Erfurt_Store::RESULTFORMAT => Erfurt_Store::RESULTFORMAT_EXTENDED,
                Erfurt_Store::USE_CACHE => $this->_useCache
            )
        );

        $values = array();
        foreach ($results['results']['bindings'] as $row) {
            $values[] = $row['obj'];
        }

        return $values;
    }

    /**
     * get link-url, curi, title for an array of properties
     * @param array $properties
     * @return array
     */
    protected function convertProperties ($properties)
    {

        $uris = array();
        foreach ($properties as $property) {
            $uris[] = $property['uri'];
        }

        if (!empty($properties)) {
            $this->_titleHelper->addResources($uris);

            $url = new OntoWiki_Url(array('route' => 'properties'), array('r'));
        }

        $propertyResults = array();
        foreach ($properties as $key => $property) {
            if (in_array($property['uri'], $this->_ignoredShownProperties)) {
                continue;
            }

            // set URL
            $url->setParam('r', $property['uri'], true);

            $property['url'] = (string) $url;

            $property['curi'] = OntoWiki_Utils::contractNamespace($property['uri']);

            $property['title'] = $this->_titleHelper->getTitle($property['uri']);

            $propertyResults[$key] = $property;
        }

        return $propertyResults;
    }

    /**
     * Returns information about the properties fetched (title etc.)
     *
     * @return array
     */
    public function getShownProperties ()
    {
        if ($this->_shownPropertiesConvertedUptodate) {
            echo "reuse";
            return $this->_shownPropertiesConverted;
        }

        //$this->getResults();

        $converted = $this->convertProperties($this->_shownProperties);
        $this->_shownPropertiesConverted = $converted;
        $this->_shownPropertiesConvertedUptodate = true;

        return $converted;
    }

    /**
     * array of shownproperties (each is a array:
     * array (
     *      'uri'
            'name'
            'inverse'
            'datatype'
            'varName'
            'var' // var objectused as object
            'optionalpart' //the hole optional {?resourceUri <prop> ?var} pattern object
            'filter' // the FILTER(!isBlank(?var)) object
     * )
     * @return array
     */
    public function getShownPropertiesPlain ()
    {
        return $this->_shownProperties;
    }

    /**
     * get titles and build link-urls (for a sparql result of the resource query)
     * @param array $resources an array of resource uris
     * @return array
     */
    public function convertResources ($resources)
    {
        // add titles first, seperatly
        $uris = array();
        foreach ($resources as $resource) {
            $uris[] = $resource['value'];
        }
        $this->_titleHelper->addResources($uris);
        //$lang = OntoWiki::getInstance()->getConfig()->languages->locale;

        $resourceResults = array();
        foreach ($resources as $resource) {
            $thisResource = $resource;
            $thisResource['uri'] = $resource['value'];
            // the URL to view this resource in detail
            $url = new OntoWiki_Url(array('controller'=>'resource', 'action'=>'properties'), array());
            $url->r = $resource['value'];

            $thisResource['url'] = (string) $url;
            // title
            $thisResource['title'] = $this->_titleHelper->getTitle($resource['value']);

            $resourceResults[] = $thisResource;
        }
        return $resourceResults;
    }

    protected function getShownResources ()
    {
        if (!$this->_resourcesUptodate) {
            $result = $this->_store->sparqlQuery(
                $this->_resourceQuery,
                array(
                    Erfurt_Store::RESULTFORMAT => Erfurt_Store::RESULTFORMAT_EXTENDED,
                    Erfurt_Store::USE_CACHE => $this->_useCache
                )
            );
            $this->_resources = array();
            foreach ($result['results']['bindings'] as $row) {
                $this->_resources[] = $row[$this->_resourceVar->getName()];
            }
            $this->_resourcesUptodate = true;
        }

        return $this->_resources;
    }

    /**
     * Returns information about the resources queried
     *
     * @return array
     */
    public function getResources ()
    {
        if ($this->_resourcesConvertedUptodate) {
           return $this->_resourcesConverted;
        }

        $this->_resourcesConverted = $this->convertResources($this->getShownResources());

        $this->_resourcesConvertedUptodate = true;
        return $this->_resourcesConverted;
    }

    /**
     * Returns whether the model has data.
     *
     * @return boolean
     */
    public function hasData ()
    {
        $this->getShownResources();
        return !empty($this->_resources);
    }

    /**
     * Sets the maximum number of resources to fetch for one page.
     *
     * @param int $limit
     * @return OntoWiki_Model_Instances
     */
    public function setLimit ($limit)
    {
        if ($this->_resourceQuery->getLimit() == $limit) {
            return $this;
        }

        if ($limit < 0) {
            $limit *= -1;
        }

        $this->_resourceQuery->setLimit($limit);
        $this->invalidate();
        return $this;
    }

    /**
     * Sets the number of resources to be skipped for the current page.
     *
     * @param int $offset
     * @return OntoWiki_Model_Instances
     */
    public function setOffset ($offset)
    {
        if ($this->_resourceQuery->getOffset() == $offset) {
            return $this;
        }

        if ($offset < 0) {
            $offset *= -1;
        }

        $this->_resourceQuery->setOffset($offset);
        $this->invalidate();
        return $this;
    }

    /** Gets the maximum number of resources to fetch for one page.
     *
     * @return int
     */
    public function getLimit ()
    {
        return $this->_resourceQuery->getLimit();
    }

    /**
     * Gets the number of resources to be skipped for the current page.
     *
     * @return int
     */
    public function getOffset ()
    {
        return $this->_resourceQuery->getOffset();
    }

    /*
     * set all "uptodate" flags to false
     * @return OntoWiki_Model_Instances
     */
    protected function invalidate ()
    {
        $this->_resourcesConvertedUptodate = false;
        $this->_resourcesUptodate = false;
        $this->_shownPropertiesConvertedUptodate = false;
        $this->_resultsUptodate = false;
        $this->_valuesUptodate = false;
        $this->_valueQueryUptodate = false;
        return $this;
    }

    /**
     * if the selected resources changed (due to filters or limit or offset)
     * we have to change the value query as well (because the resources are mentioned as subjects)
     * @return OntoWiki_Model_Instances $this
     */
    protected function updateValueQuery ()
    {
        if ($this->_valueQueryUptodate) {
            return $this;
        }

        $resources = $this->getShownResources();

        foreach ($resources as $key => $resource) {
            $resources[$key] =
                new Erfurt_Sparql_Query2_SameTerm(
                    $this->_resourceVar,
                    new Erfurt_Sparql_Query2_IriRef($resource['value'])
                );
        }

        if ($this->_valueQueryResourceFilter == null) {
            $this->_valueQueryResourceFilter = new Erfurt_Sparql_Query2_Filter(
                new Erfurt_Sparql_Query2_BooleanLiteral(false)
            );
            $this->_valueQuery->addElement($this->_valueQueryResourceFilter);
        }

        $this->_valueQueryResourceFilter->setConstraint(
            empty($resources) ?
            new Erfurt_Sparql_Query2_BooleanLiteral(false) :
            new Erfurt_Sparql_Query2_ConditionalOrExpression($resources)
        );

        //fix for a strange issue where a query with only optionals fails
        //(but there is a magic/unkown condition, that makes it work for some queries!?)
        //if($this->_store->getBackendName() == 'ZendDb'){
            $hasTriple = false;
            foreach ($this->_valueQuery->getWhere()->getElements() as $element) {
                if ($element instanceof Erfurt_Sparql_Query2_IF_TriplesSameSubject) {
                    $hasTriple = true;
                    break;
                }
            }
            if (!$hasTriple) {
                $this->_valueQuery->getWhere()->addElement(
                    new Erfurt_Sparql_Query2_Triple(
                        $this->_resourceVar,
                        new Erfurt_Sparql_Query2_Var('p'),
                        new Erfurt_Sparql_Query2_Var('o')
                    )
                );
            }
        //}
        //remove duplicate triples...
        $this->_valueQuery->optimize();

        $this->_valueQueryUptodate = true;

        return $this;
    }

    /**
     * order by a property (must be set as shown property before)
     * @param type $uri the property to order by
     * @param boolean $asc true if ascending, false if descending
     */
    public function setOrderProperty($uri, $asc = true)
    {
        $this->setOffset(0);
        if ($this->_sortTriple == null) {
            $orderVar = new Erfurt_Sparql_Query2_Var('order');
            $this->_sortTriple = new Erfurt_Sparql_Query2_OptionalGraphPattern(
                array(
                    new Erfurt_Sparql_Query2_Triple(
                        $this->getResourceVar(),
                        new Erfurt_Sparql_Query2_IriRef($uri),
                        $orderVar
                    )
                )
            );
            $this->_resourceQuery->getWhere()->addElement($this->_sortTriple);
            $this->_resourceQuery->getOrder()->add(
                $orderVar,
                $asc ? Erfurt_Sparql_Query2_OrderClause::ASC : Erfurt_Sparql_Query2_OrderClause::DESC
            );
        } else {
            $this->_sortTriple->getElement(0)->setP(new Erfurt_Sparql_Query2_IriRef($uri));
            if ($asc) {
                $this->_resourceQuery->getOrder()->setAsc(0);
            } else {
                $this->_resourceQuery->getOrder()->setDesc(0);
            }
        }
        $this->invalidate();
    }

    /**
     * order by a var, that is used in the resource query
     * @param Erfurt_Sparql_Query2_Var $var the var to order by
     * @param boolean $asc true if ascending, false if descending, optional, default is true=>asc
     */
    public function setOrderVar($var, $asc = true)
    {
        $this->setOffset(0);
        if (!is_bool($asc)) {
            $asc = true;
        }
        if ($var instanceof Erfurt_Sparql_Query2_Var) {
            $this->_resourceQuery->getOrder()->setExpression(
                array(
                    'exp'=> $var,
                    'dir'=> $asc ? Erfurt_Sparql_Query2_OrderClause::ASC : Erfurt_Sparql_Query2_OrderClause::DESC
                )
            );
        } else if (is_string($var)) {
            if ($var == $this->getResourceVar()->getName()) {
                $this->setOrderVar($this->getResourceVar(), $asc);
            } else {
                /*foreach($this->_shownProperties as $prop){
                    if($prop['varName'] == $var){
                        $this->setOrderVar($prop['var'], $asc);
                        break;
                    }
                }*/
            }
        }
        $this->invalidate();
    }

    /**
     * order the resources by their URI
     * @param type $asc true if ascending, false if descending, optional, default is true=>asc
     */
    public function orderByUri($asc = true)
    {
        $this->setOrderVar($this->getResourceVar(), $asc);
    }

    /**
     * a legacy Method to determine the class, whose instances are shown in this list
     * unfortunatley , it is not as easy as this anymore - we support other list too - then -1 is returned
     * @deprecated since version 0.9.5
     * @return string|int
     */
    public static function getSelectedClass()
    {
        $listHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('List');
        $listName = 'instances';
        if ($listHelper->listExists($listName)) {
            $list = $listHelper->getList($listName);
            $filter = $list->getFilter();
            return isset($filter['type0']['rdfsclass'])
                ? $filter['type0']['rdfsclass']
                : -1;
        }
        return  -1;
    }
}
