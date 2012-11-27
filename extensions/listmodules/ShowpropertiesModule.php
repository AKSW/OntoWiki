<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki module – showproperties
 *
 * Add instance properties to the list view
 *
 * @category   OntoWiki
 * @package    Extensions_Listmodules
 * @author     Norman Heino <norman.heino@gmail.com>
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class ShowpropertiesModule extends OntoWiki_Module
{
    protected $_instances;

    public function init()
    {
        $listHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('List');
        $this->_instances = $listHelper->getLastList();
    }
    
    public function getTitle()
    {
        return 'Show Properties';
    }

    public function shouldShow()
    {
        return ($this->_instances instanceof OntoWiki_Model_Instances) && $this->_instances->hasData();
    }

    public function getContents()
    {
        $this->view->headScript()->appendFile($this->view->moduleUrl . 'showproperties.js');

        $allShownProperties =  $this->_instances->getShownPropertiesPlain();
        $shownProperties = array();
        $shownInverseProperties = array();
        foreach ($allShownProperties as $prop) {
            if ($prop['inverse']) {
                $shownInverseProperties[] = $prop['uri'];
            } else {
                $shownProperties[] = $prop['uri'];
            }
        }
        $this->view->headScript()->appendScript(
           'var shownProperties = ' . json_encode($shownProperties).';
            var shownInverseProperties = ' . json_encode($shownInverseProperties) . ';'
        );

        $url = new OntoWiki_Url(array('controller' => 'resource','action' => 'instances'));
        $url->setParam('instancesconfig', json_encode(array('filter'=>array(array('id'=>'propertyUsage','action'=>'add','mode'=>'query','query'=> (string) $this->_instances->getAllPropertiesQuery(false))))));
        $url->setParam('init', true);
        $this->view->propertiesListLink = (string) $url;
        $url->setParam('instancesconfig', json_encode(array('filter'=>array(array('id'=>'propertyUsage','action'=>'add','mode'=>'query','query'=> (string) $this->_instances->getAllPropertiesQuery(true))))));
        $this->view->inversePropertiesListLink = (string) $url;

        if ($this->_privateConfig->filterhidden || $this->_privateConfig->filterlist) {
            $this->view->properties = $this->filterProperties($this->_instances->getAllProperties(false));
            $this->view->reverseProperties = $this->filterProperties($this->_instances->getAllProperties(true));
        } else {
            $this->view->properties = $this->_instances->getAllProperties(false);
            $this->view->reverseProperties = $this->_instances->getAllProperties(true);
        }
        
        return $this->render('showproperties');
    }
    
    public function getStateId()
    {
        $id = $this->_owApp->selectedModel
            . $this->_owApp->selectedResource;
        
        return $id;
    }
    
    private function filterProperties($properties)
    {
        $uriToFilter = array();
        $filteredProperties = array();

        if ($this->_privateConfig->filterhidden) {
            $store = $this->_owApp->erfurt->getStore();
            //query for hidden properties
            $query = new Erfurt_Sparql_SimpleQuery();
            $query->setProloguePart('PREFIX sysont: <http://ns.ontowiki.net/SysOnt/>
                                     SELECT ?uri')
                  ->setWherePart('WHERE {?uri sysont:hidden \'true\'.}');
            $uriToFilter = $store->sparqlQuery($query);
        }

        if ($this->_privateConfig->filterlist) {
            //get properties to hide from privateconfig
            $toFilter = $this->_privateConfig->property->toArray();
            foreach($toFilter as $element) {
                array_push ($uriToFilter,array('uri' => $element));
            }
        }

        foreach($properties as $property) {
            $toFilter=false;
            foreach($uriToFilter as $element) {
                if ($element['uri']==$property['uri']) {
                    $toFilter=true;
                    break;
                }
            }
            if (!$toFilter) {
                array_push ($filteredProperties, $property);
            }
        }
        return $filteredProperties;
    }
}
