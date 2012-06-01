<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * Plugin to hide property data on PropertiesAction.
 *
 * @category   OntoWiki
 * @package    Extensions_Hideproperties
 */
class HidepropertiesPlugin extends OntoWiki_Plugin {

    public function onPropertiesActionData($event) {

        if ($this->_privateConfig->hide->property) {

            $store  = Erfurt_App::getInstance()->getStore();
            $config = Erfurt_App::getInstance()->getConfig();

            $data = $event->predicates;

            foreach ( $data as $graphUri => $predicates) {
                $query = new Erfurt_Sparql_SimpleQuery();
                $query->setProloguePart('SELECT DISTINCT *')
                    ->addFrom((string) $graphUri)
                    ->setWherePart('WHERE { ?p <' . $this->_privateConfig->hide->property . '> ?o . }');

                $results = $store->sparqlQuery($query);

                if ( !empty($results) ) {

                    $publicPredicates = Array();
                   foreach($data as $element) {
                        foreach($element as $propertykey => $property) {
                            $hide=false;
                            foreach ($results as $result) {
                                if($result['p']==$property['uri'])
                                {
                                   $hide =true;
                                   break;
                                }
                            }
                            if(!$hide) {
                                $publicPredicates[$propertykey] = $property;
                            }
                        }
                    }
                $data[$graphUri]=$publicPredicates;
                }

            }
        }
        $event->predicates = $data;
        return true;
    }

}
