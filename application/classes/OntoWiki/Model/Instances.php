<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki resource list model class.
 *
 * Represents a list of resources (of a certain rdf:type) and their properties.
 *
 * @category OntoWiki
 * @package Model
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_Model_Instances extends OntoWiki_Model
{
    /**
     *  rdf:type for the resources of interest
     * @var string
     */
    protected $_type = null;
    /**
     *  array of subclasses of $_type
     * @var array
     */
    protected $_subClasses = array();
    /**
     *  rdf:type for the resources of interest
     * @var Erfurt_Sparql_Query2_IriRef
     */
    protected $_memberPredicate = null;
    
 
    protected $_allProperties;
    protected $_allPropertiesUptodate  = false;
    
    /**
     * Properties whose values are to be fetched for each resource.
     * @var array
     */
    protected $_shownProperties = array();
    protected $_shownPropertiesConverted;
    protected $_shownPropertiesConvertedUptodate  = false;
    protected $_ignoredShownProperties = array(
        EF_RDF_TYPE => array(
            'varName' => '__TYPE'
        )
    );
    
    /**
     * values of the set properties for all resources
     */
    protected $_values;
    protected $_valuesUptodate = false;
    
    /**
     * all resources
     */
    protected $_resources;
    protected $_resourcesUptodate = false;
    protected $_resourcesConverted;
    protected $_resourcesConvertedUptodate = false;
    protected $_resourceVar = null;
    
    /**
     * Result array - what comes back when evaluating the query.
     * @var array
     */
    protected $_results = null;
    protected $_resultsUptodate = false;
    
    /**
     * @var Erfurt_Sparql_Query2_Abstraction
     */
    protected $_resourceQuery;
    /**
     * @var Erfurt_Sparql_Query2
     */
    protected $_valueQuery;
    
    
    /**
     * Constructor
     */
    public function __construct(Erfurt_Store $store, $graph, $options = array())
    {
        parent::__construct($store, $graph);
        
        $type                   = isset($options['type']) ?                   $options['type'] : OntoWiki_Application::getInstance()->selectedClass;
        $withChilds             = isset($options['withChilds']) ?             $options['withChilds'] : true;
        $member_predicate       = isset($options['memberPredicate']) ?        $options['memberPredicate'] : EF_RDF_TYPE;
        $limit                  = isset($options['limit']) ?                  $options['limit'] : 0;
        $offset                 = isset($options['offset']) ?                 $options['offset'] : 0;
        $shownProperties        = isset($options['shownProperties']) ?        $options['shownProperties'] : array();
        $shownInverseProperties = isset($options['shownInverseProperties']) ? $options['shownInverseProperties'] : array();
        $sessionfilter          = isset($options['filter']) ?                 $options['filter'] : array();

        $this->_resourceQuery =  new Erfurt_Sparql_Query2();
        
        if(is_string($member_predicate))
            $member_predicate = new Erfurt_Sparql_Query2_IriRef($member_predicate);
        
        $this->type = is_string($type) ? new Erfurt_Sparql_Query2_IriRef($type) : $type;
        
        $this->_resourceVar = new Erfurt_Sparql_Query2_Var("resourceUri");
        
        if(!($member_predicate instanceof Erfurt_Sparql_Query2_Verb)){
            throw new RuntimeException('Option "member_predicate" passed to Ontowiki_Model_Instances must be an instance of Erfurt_Sparql_Query2_IriRef or string instance of '.typeHelper($member_predicate).' given');
        }
        
        if($withChilds){
	    $this->subClasses = array_keys($store->getTransitiveClosure($graph->getModelIri(), EF_RDFS_SUBCLASSOF, array($this->type->getIri()), true));
 	}
 		
        if(count($this->subClasses)>1){ //the class itself is somehow included in the subclasses...
            $typeVar = new Erfurt_Sparql_Query2_Var($this->type);
            $typePart= new Erfurt_Sparql_Query2_Triple($this->_resourceVar, $member_predicate, $typeVar);
            $this->_resourceQuery->getWhere()->addElement($typePart);
            $or = new Erfurt_Sparql_Query2_ConditionalOrExpression(); 
            foreach($this->subClasses as $subclass){
                $or->addElement(new Erfurt_Sparql_Query2_sameTerm($typeVar, new Erfurt_Sparql_Query2_IriRef($subclass)));
            }
            $filter = new Erfurt_Sparql_Query2_Filter($or);
            $this->_resourceQuery->getWhere()->addElement($filter);
        } else {
            $typePart= new Erfurt_Sparql_Query2_Triple($this->_resourceVar, $member_predicate, $this->type);
            $this->_resourceQuery->getWhere()->addElement($typePart);
        }
        
        //show resource uri
        $this->_resourceQuery->addProjectionVar($this->_resourceVar);
        
        $this->_resourceQuery->setLimit($limit)
              ->setOffset($offset)
              ->setDistinct(true)
              ->getOrder()
                 ->add($this->_resourceVar);
        
        
        // add filters
        foreach ($sessionfilter as $onefilter) {
            $this->addFilter(
                $onefilter["id"],
                $onefilter["property"],
                $onefilter["isInverse"],
                $onefilter["propertyLabel"],
                $onefilter["filter"],
                $onefilter["value1"],
                $onefilter["value2"],
                $onefilter["valuetype"],
                $onefilter["literaltype"]
            );
        }
        
        //echo 'resource query: <pre>'; echo htmlentities($this->_resourceQuery); echo '</pre>';
        
        //build value query
        $this->_valueQuery = new Erfurt_Sparql_Query2();
        
        $resources = $this->getShownResources();
        //echo 'resources: <pre>'; print_r($resources); echo '</pre>';
        
        
        foreach($resources as $key => $resource){
        	$resources[$key] = new Erfurt_Sparql_Query2_SameTerm($this->_resourceVar, new Erfurt_Sparql_Query2_IriRef($resource));
        }
        
        $this->_valueQuery->addProjectionVar($this->_resourceVar)
        	->getWhere()
        		->addElement(new Erfurt_Sparql_Query2_Filter(empty($resources) ? new Erfurt_Sparql_Query2_BooleanLiteral(false) : new Erfurt_Sparql_Query2_ConditionalOrExpression($resources)));
        
        //always query for type (not optional)
        // TODO: __TYPE should be used from the ignoredShownProp array
        // TODO: this triple should be completly generated from ignoredShownProp array
        // to allow more than one ignored prop ...
        $typeVar = new Erfurt_Sparql_Query2_Var('__TYPE');
        $this->_valueQuery->addTriple(
            $this->_resourceVar,
            new Erfurt_Sparql_Query2_IriRef(EF_RDF_TYPE),
            $typeVar
        );
        $this->_valueQuery->addProjectionVar($typeVar);
        
        // add properties to query for
        foreach ($shownProperties as $propUri) {
            $this->addShownProperty($propUri, null, false);
        } 
        foreach ($shownInverseProperties as $propUri) {
            $this->addShownProperty($propUri, null, true);
        }
        
        //echo 'resource query: <pre>'; echo htmlentities($this->_resourceQuery); echo '</pre>';
        
        //echo 'value query: <pre>'; echo htmlentities($this->_valueQuery); echo '</pre>';
    }
    
    /**
     * Adds a property to the properties fetched for every resource.
     *
     * @param $propertyUri The URI of the property
     * @param $propertyName Name to be used for the variable
     * @return OntoWiki_Model_ResourceList
     */
    public function addShownProperty($propertyUri, $propertyName = null, $inverse = false, $datatype = null)
    {
        if (!$propertyName) {
            $propertyName = preg_replace('/^.*[#\/]/', '', $propertyUri);
            $propertyName = str_replace('-', '', $propertyName);
        }
        $used = false;
        foreach($this->_shownProperties as $shownProp){
        	if($shownProp['name'] == $propertyName)$used = true;
        }
        
        if ($used) {
            $counter = 2;
            while ($used) {
                $name = $propertyName . $counter++;
	            $used = false;
	            foreach($this->_shownProperties as $shownProp){
			        if($shownProp['name'] == $name)$used = true;
			    }
	        }

            $propertyName = $name;
        }
        
		$ret = Erfurt_Sparql_Query2_Abstraction_ClassNode::addShownPropertyHelper($this->_valueQuery, $this->_resourceVar, $propertyUri, $propertyName, $inverse);
        
        $this->_shownProperties[] = array('uri' => $propertyUri,'name' => $propertyName, 'inverse' => $inverse, 'datatype' => $datatype, 'varName' => $ret['var']->getName());
		
		$this->_valuesUptodate = false; // getValues will not use the cache next time
		$this->_resultsUptodate = false;
		
        return $this;
    }
    
    public function getResults(){
    	if(!$this->_resultsUptodate){
    		$this->_results = $this->_model->sparqlQuery($this->_valueQuery, array('result_format' => 'extended'));
    		$this->_resultsUptodate = true;
    	}
            
		return $this->_results;
    }
    
    /**
     * 
     * @param string $filter_exp a serialized array
     */
    public function addFilter($id, $property, $isInverse, $propertyLabel, $filter, $value1, $value2, $valuetype, $literaltype){
    		
    	$prop = new Erfurt_Sparql_Query2_IriRef($property);
    	//echo "<pre>"; print_r($parts);echo "</pre>"; exit;
    	switch($valuetype){
    		case 'uri':
    			$value1 = new Erfurt_Sparql_Query2_IriRef($value1);
    			if(!empty($value2))$value2 = new Erfurt_Sparql_Query2_IriRef($value2);
    		break;
    		case 'literal':
    		    if(!empty($literaltype)){
                        //with language tags
                        $value1 = new Erfurt_Sparql_Query2_RDFLiteral($value1, $literaltype);
    			if(!empty($value2))$value2 = new Erfurt_Sparql_Query2_RDFLiteral($value2, $literaltype);
                    } else {
                        //no language tags
                        $value1 = new Erfurt_Sparql_Query2_RDFLiteral($value1);
    			if(!empty($value2))$value2 = new Erfurt_Sparql_Query2_RDFLiteral($value2);
                    }
    		break;
    		case 'typed-literal':
    		    if(in_array($literaltype, Erfurt_Sparql_Query2_RDFLiteral::$knownShortcuts)){
	    		    $value1 = new Erfurt_Sparql_Query2_RDFLiteral($value1, $literaltype);
	    			if(!empty($value2))$value2 = new Erfurt_Sparql_Query2_RDFLiteral($value2, $literaltype);
    		    } else {
    		    	$value1 = new Erfurt_Sparql_Query2_RDFLiteral($value1, new Erfurt_Sparql_Query2_IriRef($literaltype));
	    			if(!empty($value2))$value2 = new Erfurt_Sparql_Query2_RDFLiteral($value2, new Erfurt_Sparql_Query2_IriRef($literaltype));
    		    }
    		break;
    		default:
    			throw new RuntimeException('called Ontowiki_Model_Instances::addFilter with unknown param-value: valuetype = "'.$valuetype.'"');
    		break;
    	}
    	
    	switch($filter){
    		case 'contains':
    			$var = new Erfurt_Sparql_Query2_Var($propertyLabel);
    			if(!$isInverse){
                            $this->_resourceQuery->addTriple($this->_resourceVar, $prop, $var);
                        } else {
                            $this->_resourceQuery->addTriple($var, $prop, $this->_resourceVar);
                        }

    			$this->_resourceQuery->addFilter(new Erfurt_Sparql_Query2_Regex(new Erfurt_Sparql_Query2_Str($var), $value1));
    		break;
    		case 'equals':
    			if($valuetype=="literal"){
                            $valueVar = new Erfurt_Sparql_Query2_Var($propertyLabel);
                            if(!$isInverse){
                                $this->_resourceQuery->addTriple($this->_resourceVar, $prop, $valueVar);
                            } else {
                                throw new RuntimeException("literal as value for an inverse property is a literal subject");
                            }
                        $this->_resourceQuery->addFilter(new Erfurt_Sparql_Query2_Regex($valueVar, new Erfurt_Sparql_Query2_RDFLiteral('^'.$value1->getValue().'$')));
                        } else {
                            if(!$isInverse){
                                $this->_resourceQuery->addTriple($this->_resourceVar, $prop, $value1);
                            } else {
                                $this->_resourceQuery->addTriple($value1, $prop, $this->_resourceVar);
                            }
                        }
    		break;
    		default:
    			throw new RuntimeException('called Ontowiki_Model_Instances::addFilter with unknown param-value: filtertype='.$filter);
    		break;
    		
    	}
    	
    	//echo '<pre>'; echo htmlentities($this->_resourceQuery); echo '</pre>'; 
    	$this->_valuesUptodate = false;
    	$this->_resourcesUptodate = false;
    	$this->_resultsUptodate = false;
    }
    
    public function getQuery()
    {
    	return $this->_valueQuery;
    }
    public function getResourceQuery()
    {
    	return $this->_resourceQuery;
    }
    
    /**
     * @return Erfurt_sparql_Query2_Var the var that is used as subject in the query
     */
    public function getResourceVar()
    {
    	return $this->_resourceVar;
    }
    
    /**
     * Returns the property values for all resources at once.
     *
     * @return array
     */
    public function getValues()
    {
            if($this->_valuesUptodate){return $this->_values;}
			if(empty($this->_resources)) return array();
			
            $this->getResults(); 
                                 
            $result = $this->_results['bindings'];
            
            $titleHelper = new OntoWiki_Model_TitleHelper($this->_model);
            
            foreach ($result as $row) {
                foreach ($this->_shownProperties as $propertyUri => $property) {
                    if (isset($row[$property['name']]) && $row[$property['name']]['type'] == 'uri') {
                        $titleHelper->addResource($row[$property['name']]['value']);
                    }
                }
                foreach ($this->_ignoredShownProperties as $propertyUri => $property) {
                    if (isset($row[$property['varName']]) && $row[$property['varName']]['type'] == 'uri') {
                        $titleHelper->addResource($row[$property['varName']]['value']);
                    }
                }
            }
            
            $valueResults = array();
            foreach ($result as $row) {
                $resourceUri = $row['resourceUri']['value'];
                
                if (!array_key_exists($resourceUri, $valueResults)) {
                    $valueResults[$resourceUri] = array();
                }
                
                $url = new OntoWiki_Url(array('route' => 'properties'), array('r'));
                
                $value = null;
                $link  = null;
                $uri   = null;
                
                foreach ($row as $varName => $data) {
                    if (true) {
                        if (!array_key_exists($varName, $valueResults[$resourceUri])) {
                            $valueResults[$resourceUri][$varName] = array();
                        }
                        
                        if ($data['type'] == 'uri') {
                            // object type is uri --> handle object property
                            $objectUri = $data['value'];
                            $url->setParam('r', $objectUri, true);
                            $link = (string)$url;


                            // set up event
                            $event = new Erfurt_Event('onDisplayObjectPropertyValue');
                            
                            //find uri
                            foreach($this->_shownProperties as $property){
                            	if($varName == $property['varName']){
                            		$event->property = $property['uri'];
                            	}
                            }
                            $event->value    = $objectUri;

                            // trigger
                            $value = $event->trigger();

                            // set default if event has not been handled
                            if (!$event->handled()) {
                                $value = $titleHelper->getTitle($objectUri, $this->_lang);
                            } 
                        } else {
                            // object is a literal
                            $object = $data['value'];
                            
                            // set up event
                            $event = new Erfurt_Event('onDisplayLiteralPropertyValue');
                            $event->property = $propertyUri;
                            $event->value    = $object;
                            $event->setDefault($object);
                            
                            // trigger
                            $value = $event->trigger();
                        }
                    }
                    
                    if(!isset($valueResults[$resourceUri][$varName]) || empty($valueResults[$resourceUri][$varName]) || $valueResults[$resourceUri][$varName][count($valueResults[$resourceUri][$varName])-1]['value'] != $value){
	                    $valueResults[$resourceUri][$varName][] = array(
	                      'value' => $value, 
	                      'url'   => $link, 
	                      'uri'   => $data['value']
	                    );
                    }
                    $value = null;
                    $link  = null;
                    $uri   = null;
                }
                
            }
        //echo 'converted: <pre>';  print_r($valueResults);  echo '</pre>';
        $this->_values = $valueResults;
        $this->_valuesUptodate = true;
        return $valueResults;
    }
    
    public function getAllProperties(){
    	//TODO fix here
    	
    	$query = clone $this->_resourceQuery;
    	$query->removeAllProjectionVars()->removeAllOptionals()->setDistinct(true)->setLimit(0)->setOffset(0);
    	$predVar = new Erfurt_Sparql_Query2_Var('p');
    	$query->getWhere()->addElement(
            new Erfurt_Sparql_Query2_Triple(
                $this->_resourceVar,
                $predVar,
                new Erfurt_Sparql_Query2_Var('o')
            )
        );
    	$query->addProjectionVar($predVar)->getOrder()->clear()->add($predVar);
    	$results = $this->_model->sparqlQuery($query, array('result_format' => 'extended'));
    	//echo '<pre>'; echo htmlentities($query); print_r($results); echo '</pre>';

    	$properties = array();
    	foreach($results['bindings'] as $row){
    		$properties[] = array('uri' => $row['p']['value']);
    	}

    	return $this->convertProperties($properties);
    }

    public function getAllReverseProperties(){
    	$query = clone $this->_resourceQuery;
    	$query->removeAllProjectionVars()->removeAllOptionals()->setDistinct(true)->setLimit(0)->setOffset(0);
    	$predVar = new Erfurt_Sparql_Query2_Var('predicate');
    	$query->getWhere()->addElement(
            new Erfurt_Sparql_Query2_Triple(
                new Erfurt_Sparql_Query2_Var('obj'),
                $predVar,
                $this->_resourceVar
            )
        );
    	$query->addProjectionVar($predVar)->getOrder()->clear()->add($predVar);
    	$results = $this->_model->sparqlQuery($query, array('result_format' => 'extended'));
    	//echo '<pre>'; echo htmlentities($query); echo '</pre>';

    	$properties = array();
    	foreach($results['bindings'] as $row){
    		$properties[] = array('uri' => $row['predicate']['value']);
    	}

    	return $this->convertProperties($properties);
    }

    public function getObjects($property, $distinct = true){
    	if(is_string($property)){
    		$property = new Erfurt_Sparql_Query2_IriRef($property);
    	}
    	if(!($property instanceof Erfurt_Sparql_Query2_IriRef)){
    		throw new RuntimeException('Argument 1 passed to OntoWiki_Model_Instances::getObjects must be instance of string or Erfurt_Sparql_Query2_IriRef, '.typeHelper($property).' given');
    	}

        $query = clone $this->_resourceQuery;
    	$query->removeAllProjectionVars()->removeAllOptionals()->setDistinct($distinct)->setLimit(0);
    	
        $valueVar = new Erfurt_Sparql_Query2_Var('obj');

    	$query->addTriple($this->_resourceVar, $property, $valueVar);
    	$query->addProjectionVar($valueVar);
    	$results = $this->_model->sparqlQuery($query, array('result_format' => 'extended'));

    	$properties = array();
    	foreach($results['bindings'] as $row){
    		$properties[] = $row['obj'];
    	}

    	return $properties;
    }
    public function getSubjects($property, $distinct = true){
    	if(is_string($property)){
    		$property = new Erfurt_Sparql_Query2_IriRef($property);
    	}
    	if(!($property instanceof Erfurt_Sparql_Query2_IriRef)){
    		throw new RuntimeException('Argument 1 passed to OntoWiki_Model_Instances::getSubjects must be of string or Erfurt_Sparql_Query2_IriRef');
    	}

        $query = clone $this->_resourceQuery;
    	$query->removeAllProjectionVars()->removeAllOptionals()->setDistinct($distinct)->setLimit(0);

        $valueVar = new Erfurt_Sparql_Query2_Var('subj');
    	
    	$query->addTriple($valueVar, $property, $this->_resourceVar);
    	$query->addProjectionVar($valueVar);
    	$results = $this->_model->sparqlQuery($query, array('result_format' => 'extended'));
    	//echo '"subj query"<pre>'; echo htmlentities($query); echo '</pre>';
    	$properties = array();
    	foreach($results['bindings'] as $row){
    		$properties[] = $row['subj'];
    	}

    	return $properties;
    }
    
    protected function convertProperties($properties)
    {
        $titleHelper = new OntoWiki_Model_TitleHelper($this->_model);
    
        $uris = array();
        foreach($properties as $property){
        	$uris[] = $property['uri'];
        }
    
        $titleHelper->addResources($uris);
    
        $url = new OntoWiki_Url(array('route' => 'properties'), array('r'));
    
       	$propertyResults = array();
       	$i = 0;
        foreach ($properties as $property) {
            if(in_array($property['uri'], $this->_ignoredShownProperties)){
            	continue;
            }

            // set URL
            $url->setParam('r', $property['uri'], true);
   
           $property['url'] = (string)$url;
        
           $property['curi'] = OntoWiki_Utils::contractNamespace($property['uri']);
      
           $property['title'] = $titleHelper->getTitle($property['uri'], $this->_lang);
       
           $propertyResults[] = $property;
        }
    
        return $propertyResults;
    }
    
    /**
     * Returns information about the properties fetched (title etc.)
     *
     * @return array
     */
    public function getShownProperties()
    {
        if($this->_shownPropertiesConvertedUptodate){
            return $this->_shownPropertiesConverted;
        }
            
        $this->getResults();
            
        $converted = $this->convertProperties($this->_shownProperties);
        $this->_shownPropertiesConverted = $converted;
        $this->_shownPropertiesConvertedUptodate = true;
        return $converted;
    }
    
    public function getShownPropertiesPlain(){
    	return $this->_shownProperties;
    }
    
    
    public function convertResources($resources){
    	$url = new OntoWiki_Url(array('route' => 'properties'), array('r'));
            
            // add titles
            $this->_titleHelper->addResources($resources);
            $resourceResults = array();
            
            foreach ($resources as $uri) {
                
                if (!array_key_exists($uri, $resourceResults)) {
                    $resourceResults[$uri] = array();
                }
                
                // URL
                $url->setParam('r', $uri, true);
                $resourceResults[$uri]['url'] = (string)$url;
                
                // title
                $resourceResults[$uri]['title'] = $this->_titleHelper->getTitle($uri, $this->_lang);
            }
        return $resourceResults;
    }
    
    public function getShownResources(){
        if(!$this->_resourcesUptodate){
    		//echo '$this->_resourceQuery :'.htmlentities($this->_resourceQuery);
    		$result = $this->_model->sparqlQuery($this->_resourceQuery, array('result_format' => 'extended'));
			$this->_resources = array();
    		foreach ($result['bindings'] as $row) {
                $uri = $row['resourceUri']['value'];
                $this->_resources[] = $uri;
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
    public function getResources()
    {
       	if($this->_resourcesConvertedUptodate){
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
    public function hasData()
    {
        $this->getResults();
        return !empty($this->_results['bindings']);
    }
    
    /**
     * Sets the maximum number of resources to fetch for one page.
     *
     * @param int $limit
     * @return OntoWiki_Model_ResourceList
     */
    public function setLimit($limit)
    {
        $this->_query->setLimit($limit);
        
        return $this;
    }
    
    /**
     * Sets the number of resources to be skipped for the current page.
     *
     * @param int $offset
     * @return OntoWiki_Model_ResourceList
     */
    public function setOffset($offset)
    {
        $this->_query->setOffset($offset);
        
        return $this;
    }
    
    /** Gets the maximum number of resources to fetch for one page.
     *
     * @param int $limit
     * @return OntoWiki_Model_ResourceList
     */
    public function getLimit()
    {
        return $this->_query->getLimit();
    }
    
    /**
     * Gets the number of resources to be skipped for the current page.
     *
     * @param int $offset
     * @return OntoWiki_Model_ResourceList
     */
    public function getOffset()
    {
        return $this->_query->getOffset();
    }
    
}

