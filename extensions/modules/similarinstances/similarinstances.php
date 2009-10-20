<?php

/**
 * OntoWiki module – similarinstances
 *
 * Add instance properties to the list view
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_similarinstances
 * @author     Norman Heino <norman.heino@gmail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: similarinstances.php 4092 2009-08-19 22:20:53Z christian.wuerker $
 */
class SimilarinstancesModule extends OntoWiki_Module
{    
    public function getContents()
    {
        $query = new Erfurt_Sparql_SimpleQuery();
        
        $results  = false;
        $similars = array();
        $typesArr = array();
        $types    = $this->_getTypes();
        $url      = new OntoWiki_Url(array('route' => 'properties'), array('r'));
        $listUrl  = new OntoWiki_Url(array('route' => 'instances'), array());
        
        $titleHelper = new OntoWiki_Model_TitleHelper($this->_owApp->selectedModel);
        $titleHelper->addResources($types);
        
        foreach ($types as $typeUri) {
            if (!array_key_exists($typeUri, $typesArr)) {
                $typesArr[$typeUri] = $typeUri;
            }
            
            $query->resetInstance()
                  ->setProloguePart('SELECT DISTINCT ?uri')
                  ->setWherePart('WHERE {
                      ?uri a <' . $typeUri . '> . 
                      FILTER (!sameTerm(?uri, <' . (string) $this->_owApp->selectedResource . '>))
                      FILTER (isURI(?uri))
                  }')
                  ->setLimit(OW_SHOW_MAX + 1);
            
            if ($instances = $this->_owApp->selectedModel->sparqlQuery($query)) {
                $results = true;
                $typesArr[$typeUri] = array(
                    'title'    => $titleHelper->getTitle($typeUri, $this->_lang), 
                    'has_more' => false
                );
                
                $instTitleHelper = new OntoWiki_Model_TitleHelper($this->_owApp->selectedModel);
                $instTitleHelper->addResources($instances, 'uri');
                
                // HACK: allow to show more w/o actually counting
                if (count($instances) == OW_SHOW_MAX + 1) {
                    unset($instances[OW_SHOW_MAX]);
                    $listUrl->setParam('r', $typeUri, true);
                    $typesArr[$typeUri]['has_more'] = (string) $listUrl;
                }
                
                foreach ($instances as $row) {
                    $instanceUri = $row['uri'];
                    // set URL
                    $url->setParam('r', $instanceUri, true);
                    
                    if (!array_key_Exists($typeUri, $similars)) {
                        $similars[$typeUri] = array();
                    }
                    
                    // add instance
                    $similars[$typeUri][$instanceUri] = array(
                        'uri'   => $instanceUri, 
                        'title' => $instTitleHelper->getTitle($instanceUri, $this->_lang), 
                        'url'   => (string) $url, 
                    );
                }
            }
        }
        
        $this->view->types    = $typesArr;
        $this->view->similars = $similars;
        
        if (!$results) {
            $this->view->message = 'No matches.';
        }
        
        return $this->render('similarinstances');
    }
    
    public function getStateId()
    {
        $id = $this->_owApp->selectedModel
            . $this->_owApp->selectedResource;
        
        return $id;
    }
    
    private function _getTypes()
    {
        $typesInferred = array();
        
        $query = new Erfurt_Sparql_SimpleQuery();
        
        $query->setProloguePart('SELECT DISTINCT ?uri')
              ->setWherePart('
                WHERE {
                    <' . (string) $this->_owApp->selectedResource . '> a ?uri.
                    ?similar a ?uri.
                    FILTER isUri(?uri)
                }');
        
        if ($result = $this->_owApp->selectedModel->sparqlQuery($query)) {
            $types = array();
            foreach ($result as $row) {
                array_push($types, $row['uri']);
            }
            
            $typesInferred = $this->_store->getTransitiveClosure(
                (string) $this->_owApp->selectedModel, 
                EF_RDFS_SUBCLASSOF, 
                $types, 
                false
            );
        }
        
        return array_keys($typesInferred);
    }
}


