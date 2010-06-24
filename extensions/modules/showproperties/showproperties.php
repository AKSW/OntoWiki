<?php

/**
 * OntoWiki module – showproperties
 *
 * Add instance properties to the list view
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_showproperties
 * @author     Norman Heino <norman.heino@gmail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: showproperties.php 4222 2009-10-02 10:54:38Z sebastian.dietzold $
 */
class ShowpropertiesModule extends OntoWiki_Module
{
    public function init()
    {
        // load js
        $this->view->headScript()->appendFile($this->view->moduleUrl . 'showproperties.js');
        
        $session = $this->_owApp->session;

        $allShownProperties =  $session->instances->getShownPropertiesPlain();
        $shownProperties = array();
        $shownInverseProperties = array();
        foreach ($allShownProperties as $prop) {
            if($prop['inverse']){
                $shownInverseProperties[] = $prop['uri'];
            } else {
                $shownProperties[] = $prop['uri'];
            }
        }
        $this->view->headScript()->appendScript(
           'var shownProperties = ' . json_encode($shownProperties).';
            var shownInverseProperties = ' . json_encode($shownInverseProperties) . ';'
        );
    }
    
    public function getContents()
    {
        $session = $this->_owApp->session;
        if (isset($session->instances)) {
            if($this->_privateConfig->filterhidden || $this->_privateConfig->filterlist)
            {
            $this->view->properties = $this->filterProperties($session->instances->getAllProperties(false));
            $this->view->reverseProperties = $this->filterProperties($session->instances->getAllProperties(true));
            }
            else
            {
            $this->view->properties = $session->instances->getAllProperties(false);
            $this->view->reverseProperties = $session->instances->getAllProperties(true);
            }
            
            return $this->render('showproperties');
        }
        
        return 'No instances object';
    }
    
    public function getStateId()
    {
        $id = $this->_owApp->selectedModel
            . $this->_owApp->selectedResource;
        
        return $id;
    }

    
    private function filterProperties($properties) {

    $uriToFilter = array();
    $filteredProperties = array();

    if($this->_privateConfig->filterhidden)
    {
        $store = $this->_owApp->erfurt->getStore();
        //query for hidden properties
        $query = new Erfurt_Sparql_SimpleQuery();
        $query->setProloguePart('PREFIX sysont: <http://ns.ontowiki.net/SysOnt/>
                                 SELECT ?uri')
              ->setWherePart('WHERE {?uri sysont:hidden \'true\'.}');
        $uriToFilter = $store->sparqlQuery($query);
    }

    if($this->_privateConfig->filterlist)
    {
        //get properties to hide from privateconfig
        $toFilter = $this->_privateConfig->property->toArray();
        foreach($toFilter as $element)
        {
        array_push ($uriToFilter,array('uri' => $element));
        }
    }

    foreach($properties as $property) {
        $toFilter=false;
        foreach($uriToFilter as $element) {
            if($element['uri']==$property['uri']) {
                $toFilter=true;
                break;
            }
        }
        if(!$toFilter) {
            array_push ($filteredProperties, $property);
        }
    }
    return $filteredProperties;
    }

}


