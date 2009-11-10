<?php
// vim: sw=4:sts=4:expandtab

if (!defined("EOL")) {
    define("EOL","\n");
}

require_once 'OntoWiki/Component/Helper.php';

/**
 * Helper class for the FOAF Editor component.
 * Checks whether the current resource is an instance of foaf:Person
 * and registers the FOAF Editor component if so.
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_map
 * @author Norman Heino <norman.heino@gmail.com>
 * @version $Id: FoafeditHelper.php 3053 2009-05-08 12:15:51Z norman.heino $
 */
class MapHelper extends OntoWiki_Component_Helper
{

    public function init()
    {

        $onSwitch = false;  // decide, if map should be on

        if (isset($this->_privateConfig->switch->on) AND $this->_privateConfig->switch->on == 'ever') {
            $onSwitch = true;
        }

        if (!$onSwitch) {
            $onSwitch = $this->shouldShow();
        }

        if ($onSwitch) {
            // register new tab
            require_once 'OntoWiki/Navigation.php';

            if (!OntoWiki_Navigation::isRegistered('map')) {
                OntoWiki_Navigation::register('map', array(
                            'controller' => 'map', 
                            'action'     => 'display', 
                            'name'       => 'Map', 
                            'priority'   => 20,
                            'active'     => false));
            }
        }
    }

    public function shouldShow () 
    {

        $owApp = OntoWiki::getInstance();

        //var_dump($owApp);

        if($owApp->selectedModel) {

            require_once 'Erfurt/Sparql/SimpleQuery.php';

            $store    = $owApp->erfurt->getStore();
            $resource = (string) $owApp->selectedResource;

            // build the query to get all marker resources
            $query = new Erfurt_Sparql_SimpleQuery( );

            $query->setProloguePart( 'SELECT ?p' );

            $where = "WHERE {"      . EOL;
            $where.= "	?s ?p ?o."  . EOL;
            $where.= "	FILTER("    . EOL;

            $latitude	= $this->_privateConfig->property->latitude->toArray();
            $longitude	= $this->_privateConfig->property->longitude->toArray();

            for ($i = 0; $i < count($latitude); $i++) {
                $lat = $latitude[$i];
                $long = $longitude[$i];
                if( $i != 0 ) {
                    $where.= " || ";
                }
                $where.= "		sameTerm(?p, <" . $lat . ">) ||" . EOL;
                $where.= "		sameTerm(?p, <" . $long . ">)"   . EOL;
            }

            $where.= ") }";

            $query->setWherePart($where);
            $query->setLimit(1);
            // ask for the properties
            return $owApp->selectedModel->sparqlQuery($query);
        } else {
            return false;
        }

        // this function should use the following code, but because there are no instances at the moment this wouldn't work

        /*
           $query = clone $this->_owApp->instances->getResourceQuery();
           $query->removeAllOptionals()->removeAllProjectionVars();

           $ggp1 = new Erfurt_Sparql_Query2_GroupGraphPattern();
           $ggp2 = new Erfurt_Sparql_Query2_GroupGraphPattern(); 

           $ggp1->addTriple(
           $this->_owApp->instances->getResourceVar(),
           'http://www.w3.org/2003/01/geo/wgs84_pos#long',
           new Erfurt_Sparql_Query2_Var('long'));
           $ggp1->addTriple(
           $this->_owApp->instances->getResourceVar(),
           'http://www.w3.org/2003/01/geo/wgs84_pos#lat',
           new Erfurt_Sparql_Query2_Var('lat'));

           $node = new Erfurt_Sparql_Query2_Var('node'); // should be $node = new Erfurt_Sparql_Query2_BlankNode('bn'); but i heard this is not supported yet by zendb
           $ggp2->addTriple($this->_owApp->instances->getResourceVar(), new Erfurt_Sparql_Query2_Var('pred') , $node);
           $ggp2->addTriple($node, 'http://www.w3.org/2003/01/geo/wgs84_pos#long', new Erfurt_Sparql_Query2_Var('long2'));
           $ggp2->addTriple($node, 'http://www.w3.org/2003/01/geo/wgs84_pos#lat', new Erfurt_Sparql_Query2_Var('lat2'));

           $union = new Erfurt_Sparql_Query2_GroupOrUnionGraphPattern(); 

           $union->addElement($ggp1)->addElement($ggp2);

           $query->addElement($union);
           $query
           ->setLimit(0)
           ->setOffset(0)
           ->removeAllProjectionVars()
           ->removeAllOptionals();

           $query->setQueryType(Erfurt_Sparql_Query2::typeAsk);
        //echo htmlentities($query);

        //for some reason sparqlAsk wants a SimpleQuery
        $simpleQuery = Erfurt_Sparql_SimpleQuery::initWithString($query);
        $ret = $this->_owApp->erfurt->getStore()->sparqlAsk($simpleQuery);
        return is_bool($ret) ? $ret : false;

        //or a lot easier if ignoring transitive map-infos
        foreach($this->_owApp->instances->getAllProperties() as $property){
        if($property['uri'] == 'http://www.w3.org/2003/01/geo/wgs84_pos#long' || $property['uri'] == 'http://www.w3.org/2003/01/geo/wgs84_pos#lat')
        return true;
        }

        return false;
         */
    }
}

