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
class Minimap2Module extends OntoWiki_Module
{
    public function init()
    {
        $key = 'ABQIAAAAynyr50kleiy5o-uQwf2tTBRnE7oD7ulPnCTyuIl4SzAcqF9XxBSKle9ZP7LcJUMzGgym1wQT_TV0Uw';
        
        $this->view->headScript()->appendFile($this->view->moduleUrl . 'minimap2.js');
        $this->view->headScript()->appendFile('http://maps.google.com/maps?file=api&v=2&key=' . $key . '&sensor=false');
            $this->view->headScript()->appendFile('http://gmaps-utility-library.googlecode.com/svn/trunk/markermanager/release/src/markermanager.js');
        
    }

    public function getContents()
    {
        $points = array(
            array(127.0, 233.0),
            array(245.0, 236.0)
        );
        
        $this->view->points = $points;
        
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
            return $this->render('minimap2');
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
