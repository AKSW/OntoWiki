<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * Plugin to reorder properties data on PropertiesAction.
 *
 * @category   OntoWiki
 * @package    Extensions_Sortproperties
 */
class SortpropertiesPlugin extends OntoWiki_Plugin
{
    public function onPropertiesActionData($event)
    {
        if ($this->_privateConfig->sort->property) {

            $store  = Erfurt_App::getInstance()->getStore();
            $config = Erfurt_App::getInstance()->getConfig();

            $data = $event->predicates;

            foreach ($data as $graphUri => $predicates) {
                $query = new Erfurt_Sparql_SimpleQuery();
                $query->setProloguePart('SELECT DISTINCT *')
                    ->setWherePart('WHERE { ?p <' . $this->_privateConfig->sort->property . '> ?o . }');

                $result = $store->sparqlQuery($query);

                if (!empty($result)) {

                    $order = array();

                    foreach ($result as $v) {
                        $order[$v['p']] = $v['o'];
                    }

                    $predicateOrder = array();

                    foreach (array_keys($predicates) as $predicate) {
                        if (array_key_exists($predicate, $order)) {
                            $predicateOrder[] = (int)$order[$predicate];
                        } else {
                            $predicateOrder[] = 0;
                        }
                    }
                    array_multisort($predicateOrder, SORT_DESC, SORT_NUMERIC, $predicates);
                    $data[$graphUri] = $predicates;

                }

            }

            $event->predicates = $data;

            return true;
        }
    }

    public function onSortPropertiesRDFauthorData($event)
    {
        if ($this->_privateConfig->sort->property) {

            $store  = Erfurt_App::getInstance()->getStore();
            $config = Erfurt_App::getInstance()->getConfig();

            $data = $event->data;

            // cast stdClass to array
            $data = json_decode(json_encode($data), true);


            $query = new Erfurt_Sparql_SimpleQuery();
            $query->setProloguePart('SELECT DISTINCT *')
                ->setWherePart('WHERE { ?p <' . $this->_privateConfig->sort->property . '> ?o . }');

            $result = $store->sparqlQuery($query);

            if (!empty($result)) {

                $order = array();

                foreach ($result as $v) {
                    $order[$v['p']] = $v['o'];
                }

                $predicateOrder = array();

                foreach($data as $key => $resource) {
                    if (Erfurt_Uri::check($key)) {
                        foreach($resource as $predicateUri => &$values) {
                            if (array_key_exists($predicateUri, $order)) {
                                $predicateOrder[$predicateUri] = (int)$order[$predicateUri];
                            } else {
                                $predicateOrder[$predicateUri] = 0;
                            }
                        }
                    }
                }

                // create non associative array for sorted json output
                $predicates = reset($data);
                arsort($predicateOrder, SORT_NUMERIC);
                $predicateOrder = array_keys($predicateOrder);

                $data['propertyOrder'] = $predicateOrder;
                $event->output   = $data;
                return true;
            }
        }
    }
}
