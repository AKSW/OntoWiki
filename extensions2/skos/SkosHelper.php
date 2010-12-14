<?php

require_once 'OntoWiki/Component/Helper.php';

/**
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_skos
 */
class SkosHelper extends OntoWiki_Component_Helper
{
    public function init()
    {
        $owApp = OntoWiki::getInstance();

        if (null === $owApp->selectedModel) {
            return;
        }

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

			// concept or conceptScheme?
            if ( !strcmp($this->_privateConfig->concept, $class) || !strcmp($this->_privateConfig->conceptscheme, $class) ) {
                
                // we have a skos:Concept
                // register new tab
                require_once 'OntoWiki/Navigation.php';
                OntoWiki_Navigation::register('skos', array(
                    'controller' => 'skos',
                    'action'     => 'skosobject',
                    'name'       => 'SKOS',
                    'priority'   => -1,
                    'active'     => false));
            }
        }
    }
}

