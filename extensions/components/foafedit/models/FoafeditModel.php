<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version $Id:$
 */

/**
 * OntoWiki model base class.
 *
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @category OntoWiki
 * @package Components
 * @subpackage Foafedit
 * @author Norman Heino <norman.heino@gmail.com>
 */
class FoafeditModel
{
    protected $_graph = null;
    protected $_uri = null;
    protected $_properties = array();
    protected $_propertyValues = null;
    protected $_store = null;
    protected $_titleHelper = null;
    
    public function __construct($model, $resource)
    {
        $this->_graph = (string) $model;
        $this->_uri = (string) $resource;
        $this->_store = $model->getStore();
        
        require_once 'OntoWiki/Model/TitleHelper.php';
        $this->_titleHelper = new OntoWiki_Model_TitleHelper();
    }
    
    public function addProperty($uri, $name)
    {
        if (!array_key_exists($uri, $this->_properties)) {
            $this->_properties[$uri] = $name;
            $this->_titleHelper->addResource($uri);
        }
    }
    
    public function addProperties(array $uriValuedPropertyArray)
    {
        foreach ($uriValuedPropertyArray as $name => $uri) {
            $this->addProperty($uri, $name);
        }
    }
    
    public function getPropertyValues()
    {
        if (null === $this->_propertyValues) {
            $query = $this->_buildQuery();
            
            $this->_propertyValues = array();
            if ($result = $this->_store->sparqlQuery($query, array('result_format' => 'extended'))) {
                foreach ($result['bindings'] as $row) {
                    $property = $row['property']['value'];
                    
                    if (array_key_exists($property, $this->_properties)) {
                        // property is of interest
                        $name = $this->_properties[$property];
                        
                        // create if necessary
                        if (!array_key_exists($name, $this->_propertyValues)) {
                            $this->_propertyValues[$name] = array();
                        }
                        
                        // append value
                        array_push($this->_propertyValues[$name], $row['value']);
                    }
                }
            }
        }
        
        return $this->_propertyValues;
    }
    
    protected function _buildQuery()
    {
        if (count($this->_properties)) {            
            require_once 'Erfurt/Sparql/SimpleQuery.php';
            $query = new Erfurt_Sparql_SimpleQuery();
            $query->addFrom($this->_graph)
                  ->setProloguePart('SELECT DISTINCT ?property ?value')
                  ->setWherePart('WHERE {<' . $this->_uri . '> ?property ?value.}');
            
            return $query;
        }
    }
}