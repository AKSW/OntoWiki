<?php

/**
 * OntoWiki module – properties
 *
 * Shows some properties of a class
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_predicates
 * @author     Norman Heino <norman.heino@gmail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: properties.php 3014 2009-05-04 09:28:54Z norman.heino $
 */
class PredicatesModule extends OntoWiki_Module
{    
    public function getContents()
    {
        // build query
        $query = new Erfurt_Sparql_SimpleQuery();
        $query->setProloguePart('SELECT ?predicate ?object')
              ->setWherePart('WHERE {
                  <' . $this->_owApp->selectedResource . '> ?predicate ?object .
                  FILTER (!sameTerm(?predicate, <' . EF_RDF_TYPE . '>))
                  FILTER (!isBlank(?object))
                }');
        
        // get results
        if ($results = $this->_owApp->selectedModel->sparqlQuery($query, array('result_format' => 'extended'))) {
            $results = $results['bindings'];
            
            $titleHelper = new OntoWiki_Model_TitleHelper($this->_owApp->selectedModel);
            
            foreach ($results as $row) {
                if (isset($row['predicate'])) {
                    $titleHelper->addResource($row['predicate']['value']);
                }
                
                if (isset($row['object']['type']) && $row['object']['type'] == 'uri') {
                    $titleHelper->addResource($row['object']['value']);
                }
            }
            
            $url = new OntoWiki_Url(array('route' => 'properties'), array('r'));
            
            $properties = array();
            foreach ($results as $uri => $row) {
                $predicateUri = $row['predicate']['value'];
                
                if (!array_key_exists($predicateUri, $properties)) {
                    $properties[$predicateUri] = array(
                        'title'  => $titleHelper->getTitle($predicateUri, $this->_lang), 
                        'values' => array()
                    );
                }
                
                $object = $row['object'];
                if (isset($object['type']) && $object['type'] == 'uri') {
                    // URI object
                    $objectUri = $object['value'];
                    $url->setParam('r', $objectUri, true);
                    $entry = array(
                        'uri'    => $objectUri, 
                        'object' => $titleHelper->getTitle($objectUri, $this->_lang), 
                        'url'    => (string) $url
                    );
                } else {
                    // literal object
                    $entry = array(
                        'uri'    => null, 
                        'object' => $object['value'], 
                        'url'    => null
                    );
                }
                
                array_push($properties[$predicateUri]['values'], $entry);
            }
            
            $this->view->properties = $properties;
        }
        
        if (!isset($properties) or empty($properties)) {
            $this->view->message = 'No matches.';
        }
        
        return $this->render('predicates');
    }
    
    public function getStateId() {
        $id = $this->_owApp->selectedModel->getModelIri()
            . $this->_owApp->selectedResource;
        
        return $id;
    }
}


