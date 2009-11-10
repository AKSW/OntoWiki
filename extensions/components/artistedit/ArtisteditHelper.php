<?php

require_once 'OntoWiki/Component/Helper.php';

/**
 * Helper class for the Artist Editor component.
 * Checks whether the current resource is an instance of mo:MusicArtist
 * and registers the Artist Editor component if so.
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_foafedit
 * @author example by Sebastian D extended by Kurt Jacobson
 */
class ArtisteditHelper extends OntoWiki_Component_Helper
{
    public function init()
    {
        $owApp = OntoWiki::getInstance();

        if ($owApp->selectedModel) {
            $store    = $owApp->erfurt->getStore();
            $resource = (string) $owApp->selectedResource;

            require_once 'Erfurt/Sparql/SimpleQuery.php';
            $query = new Erfurt_Sparql_SimpleQuery();

            // build SPARQL query for getting class (rdf:type) of current resource
            $query->setProloguePart('SELECT DISTINCT ?t')
                  ->setWherePart('WHERE {<' . $resource . '> a ?t.}');

            // query the store
            if ($result = $owApp->selectedModel->sparqlQuery($query)) {
                $row = current($result);
                $class = $row['t'];

                // get all super classes of the class
                $super = $store->getTransitiveClosure(
                    (string) $owApp->selectedModel,
                    EF_RDFS_SUBCLASSOF,
                    $class,
                    false);

                // merge direct type
                $types = array_merge(array($class), array_keys($super));
                if (in_array($this->_privateConfig->artist, $types)) {
                    // we have a mo:MusicArtist
                    // register new tab
                    require_once 'OntoWiki/Navigation.php';
                    OntoWiki_Navigation::register('artistedit', array(
                        'controller' => 'artistedit',
                        'action'     => 'artist',
                        'name'       => 'Artist Editor',
                        'priority'   => -1,
                        'active'     => false));
                }
            }
        }
    }
}