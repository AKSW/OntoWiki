<?php

/**
 * Plugin to reorder properties data on PropertiesAction.
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_plugins
 */
class SortpropertiesPlugin extends OntoWiki_Plugin
{   
    public function onPropertiesActionData($event)
    {

        if ($this->_privateConfig->sort->property) {

            $store  = Erfurt_App::getInstance()->getStore();
            $config = Erfurt_App::getInstance()->getConfig();

            $data = $event->predicates;

            foreach ( $data as $graphUri => $predicates) {
                $query = new Erfurt_Sparql_SimpleQuery();
                $query->setProloguePart('SELECT DISTINCT *')
                      ->addFrom((string) $graphUri)
                      ->setWherePart('WHERE { ?p <' . $this->_privateConfig->sort->property . '> ?o . }');
                
                $result = $store->sparqlQuery($query);

                if ( !empty($result) ) {

                    $order = array();

                    foreach ($result as $v) {
                        $order[$v['p']] = $v['o'];
                    }

                    $predicateOrder = array();

                    foreach (array_keys($predicates) as $predicate) {
                        if (array_key_exists($predicate,$order)) {
                            $predicateOrder[]   = (int) $order[$predicate];
                        }  else {
                            $predicateOrder[]   = 0;
                        }
                    }

                    array_multisort($predicateOrder,SORT_DESC, SORT_STRING,$predicates);
                    $data[$graphUri] = $predicates;

                }

            }

            $event->predicates = $data;

            return true;
        }
    }
}
