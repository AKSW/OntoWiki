<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version $Id:$
 */

require_once 'OntoWiki/Component/Helper.php';

/**
 * Helper class for the FOAF Editor component.
 * Checks whether the current resource is an instance of foaf:Person
 * and registers the FOAF Editor component if so.
 *
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @category OntoWiki
 * @package Extensions
 * @subpackage Foafedit
 * @author Norman Heino <norman.heino@gmail.com>
 */
class FoafeditHelper extends OntoWiki_Component_Helper
{
    public function init()
    {
        $owApp = OntoWiki_Application::getInstance();
        
        if ($owApp->selectedModel) {
            $store    = $owApp->erfurt->getStore();
            $resource = (string) $owApp->selectedResource;
            
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
                
                $types = array($class);
                foreach ($super as $typeInfo) {
                    $types[] = $typeInfo['parent'];
                }
                
                $types = array_combine($types, $types);
                
                if (array_key_exists($this->_privateConfig->person, $types)) {
                    // we have a foaf:Person
                    // register new tab
                    OntoWiki_Navigation::register('foafedit', array(
                        'controller' => 'foafedit', 
                        'action'     => 'person', 
                        'name'       => 'FOAF Editor', 
                        'priority'   => -1, 
                        'active'     => true));
                }
            }
        }
    }
}

