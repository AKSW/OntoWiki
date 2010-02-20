<?php
// vim: sw=4:sts=4:expandtab

require_once 'OntoWiki/Component/Helper.php';

/**
 * Helper class for the Map component.
 * Checks whether there are geospacial properties in result of the currant QueryObject
 * and registers the Map tab component if so.
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_map
 * @author Natanael Arndt <arndtn@gmail.com>
 * @version $Id$
 * TODO comments
 */
class MapHelper extends OntoWiki_Component_Helper
{

    /**
     * Object holding the Instances with direct geo properties (e.g.: geo:long, geo:lat)
     * and the other one with indirect geo properties (e.g.: foaf:based_near)
     */
    private $dirInstances = null;
    private $indInstances = null;

    public function init()
    {
        $onSwitch = false;  // decide, if map should be on

        if (isset($this->_privateConfig->show->tab)){
            if ($this->_privateConfig->show->tab == 'ever') {
                $onSwitch = true;
            } else if ($this->_privateConfig->show->tab == 'never') {
                $onSwitch = false;
            } else {
                $onSwitch = $this->shouldShow();
            }
        } else {
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

        /*
         * don't show on model, application, error, debug, module and index controller
         */
        $owApp = OntoWiki::getInstance();
        $session = $owApp->session;

        $front  = Zend_Controller_Front::getInstance();

        if (!$front->getRequest()->isXmlHttpRequest() && isset($session->instances)) {

            $latProperties  = $this->_privateConfig->property->latitude->toArray();
            $longProperties = $this->_privateConfig->property->longitude->toArray();
            $latProperty    = $latProperties[0];
            $longProperty   = $longProperties[0];

            $latVar         = new Erfurt_Sparql_Query2_Var('lat');
            $longVar        = new Erfurt_Sparql_Query2_Var('long');
            $lat2Var        = new Erfurt_Sparql_Query2_Var('lat2');
            $long2Var       = new Erfurt_Sparql_Query2_Var('long2');

            if($this->dirInstances === null) {    
                $this->dirInstances = clone $session->instances;
                $owApp->logger->debug('MapHelper/shouldShow: clone this->_session->instances');
            } else {
                $owApp->logger->debug('MapHelper/shouldShow: this->dirInstances already set');
                // don't load instances again
            }

            if($this->indInstances === null) {    
                $this->indInstances = clone $session->instances;
                $owApp->logger->debug('MapHelper/shouldShow: clone this->_session->instances');
            } else {
                $owApp->logger->debug('MapHelper/shouldShow: this->indInstances already set');
                // don't load instances again
            }

            $this->dirInstances->setLimit(1);
            $this->dirInstances->setOffset(0);
            $this->indInstances->setLimit(1);
            $this->indInstances->setOffset(0);

            /**
             * Direct Query, to check for direct geoproperties
             */
            $dirQuery  = $this->dirInstances->getResourceQuery();
            $dirQuery->setQueryType(Erfurt_Sparql_Query2::typeSelect); /* would like to ask but ask lies */
            $dirQuery->removeAllOptionals()->removeAllProjectionVars();

            /**
             * Indirect Query, to check for indirect geoproperties
             */
            $indQuery  = $this->indInstances->getResourceQuery();
            $indQuery->setQueryType(Erfurt_Sparql_Query2::typeSelect); /* would like to ask but ask lies */
            $indQuery->removeAllOptionals()->removeAllProjectionVars();

            $dirQuery->addProjectionVar($this->dirInstances->getResourceVar());
            $dirQuery->addProjectionVar($latVar);
            $dirQuery->addProjectionVar($longVar);

            $indQuery->addProjectionVar($this->indInstances->getResourceVar());
            $indQuery->addProjectionVar($lat2Var);
            $indQuery->addProjectionVar($long2Var);

            $dirQuery->addTriple($this->dirInstances->getResourceVar(), $latProperty, $latVar);
            $dirQuery->addTriple($this->dirInstances->getResourceVar(), $longProperty, $longVar);

            $node     = new Erfurt_Sparql_Query2_Var('node'); // should be $node = new Erfurt_Sparql_Query2_BlankNode('bn'); but i heard this is not supported yet by zendb
            $indQuery->addTriple($this->indInstances->getResourceVar(), new Erfurt_Sparql_Query2_Var('pred') , $node);
            $indQuery->addTriple($node, $latProperty, $lat2Var);
            $indQuery->addTriple($node, $longProperty, $long2Var);

            $owApp->logger->debug('MapHelper/shouldShow: sent "' . $dirQuery . '" to know if SpacialThings are available.');
            $owApp->logger->debug('MapHelper/shouldShow: sent "' . $indQuery . '" to know if SpacialThings are available.');

            /* get result of the query */
            $dirResult   = $this->_owApp->erfurt->getStore()->sparqlQuery($dirQuery);
            $indResult   = $this->_owApp->erfurt->getStore()->sparqlQuery($indQuery);

            $owApp->logger->debug('MapHelper/shouldShow: got respons "' . var_export($dirResult, true) . '".');
            $owApp->logger->debug('MapHelper/shouldShow: got respons "' . var_export($indResult, true) . '".');

            if ($dirResult OR $indResult) {
                $result = true;
            } else {
                $result = false;
            }

            return $result;

        } else {
            if($front->getRequest()->isXmlHttpRequest()) {
                $owApp->logger->debug('MapHelper/shouldShow: xmlHttpRequest → no map.');
            } else if(!isset($session->instances)) {
                $owApp->logger->debug('MapHelper/shouldShow: no instances object set in session → no instances to show → no map.');
            } else {
                $owApp->logger->debug('MapHelper/shouldShow: decided to hide the map, but not because of a XmlHttpRequest and not, because there is no valide session.');
            }

            return false;
        }
    }
}

