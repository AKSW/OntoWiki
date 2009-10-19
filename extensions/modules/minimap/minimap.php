<?php
// vim: sw=4:sts=4:expandtab
require_once 'OntoWiki/Module.php';

/**
 * OntoWiki module – minimap
 *
 * display a minimap of the currently visible resources (if any)
 *
 * @package    ontowiki
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: minimap.php 4241 2009-10-05 22:33:25Z arndtn $
 */
class MinimapModule extends OntoWiki_Module
{
    public function init()
    {
        // load js
        #$this->view->headScript()->appendFile('http://maps.google.com/maps?file=api&v=2&hl=de&key=' . $this->_privateConfig->apikey->google);
        //$this->view->headScript()->appendFile($this->view->moduleUrl . 'resources/lib/OpenStreetMap.js');
        #$this->view->headScript()->appendFile($this->view->moduleUrl . 'resources/lib/OpenLayers.js');
        #$this->view->headScript()->appendFile($this->view->moduleUrl . 'minimap.js');
    }

    public function getContents()
    {
        if(isset($this->_owApp->instances)) {

            /*
               $query = clone $this->_owApp->instances->getResourceQuery();
               $query->removeAllOptionals()->removeAllProjectionVars();

               $ggp1 = new Erfurt_Sparql_Query2_GroupGraphPattern();

               $ggp1->addTriple($this->_owApp->instances->getResourceVar(), 'http://www.w3.org/2003/01/geo/wgs84_pos#long', new Erfurt_Sparql_Query2_Var('long'));
               $ggp1->addTriple($this->_owApp->instances->getResourceVar(), 'http://www.w3.org/2003/01/geo/wgs84_pos#lat', new Erfurt_Sparql_Query2_Var('lat'));
               $ggp1->addTriple($this->_owApp->instances->getResourceVar(), 'http://www.w3.org/2000/01/rdf-schema#label', new Erfurt_Sparql_Query2_Var('label'));

               $query->addElement($ggp1);

               $query->setQueryType(Erfurt_Sparql_Query2::typeSelect);
               $simpleQuery = Erfurt_Sparql_SimpleQuery::initWithString($query);
               $ret = $this->_owApp->erfurt->getStore()->sparqlAsk($simpleQuery);

            //var_dump($ret);
            $this->view->markers = $ret;
            $this->view->icon = $this->_privateConfig->icon;
             */
//            $mapConfig = $this->_owApp->componentManager->getComponentPrivateConfig('map');
//            $this->view->headScript()->appendFile('http://maps.google.com/maps?file=api&v=2&hl=de&key=' . $mapConfig->apikey->google);
            return $this->render('minimap');
        } 
    }

    public function shouldShow() {
        //        require_once 'extensions/components/MapHelper.php';
        if(class_exists('MapHelper')) {
            $helper = new MapHelper($this->_owApp->componentManager);
            return $helper->shouldShow();
        } else {
            return false;
        }
    }


    public function getStateId() {
        $id = $this->_owApp->selectedModel
            . $this->_owApp->selectedResource;
        
        return $id;
    }
}


