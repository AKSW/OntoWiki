<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki module – similarinstances
 *
 * Add instance properties to the list view
 *
 * @category   OntoWiki
 * @package    Extensions_Resourcemodule
 * @author     Norman Heino <norman.heino@gmail.com>
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class SimilarinstancesModule extends OntoWiki_Module
{

    public function getTitle()
    {
        return "Similar Instances";
    }

    public function shouldShow()
    {
        if ($this->_privateConfig->show->similarinstances == true) {
            return true;
        } else {
            return false;
        }
    }

    public function getContents()
    {
        $query = new Erfurt_Sparql_SimpleQuery();

        $results  = false;
        $similars = array();
        $typesArr = array();
        $types    = $this->_getTypes();
        $url      = new OntoWiki_Url(array('route' => 'properties'), array('r'));
        $listUrl  = new OntoWiki_Url(array('route' => 'instances'), array());

        $titleHelper = new OntoWiki_Model_TitleHelper($this->_owApp->selectedModel);
        $titleHelper->addResources($types);

        foreach ($types as $typeUri) {
            if (!array_key_exists($typeUri, $typesArr)) {
                $typesArr[$typeUri] = $typeUri;
            }

            $query->resetInstance()
                  ->setProloguePart('SELECT DISTINCT ?uri')
                  ->setWherePart('WHERE {
                      ?uri a <' . $typeUri . '> .
                      FILTER (!sameTerm(?uri, <' . (string) $this->_owApp->selectedResource . '>))
                      FILTER (isURI(?uri))
                  }')
                  ->setLimit(OW_SHOW_MAX + 1);

            if ($instances = $this->_owApp->selectedModel->sparqlQuery($query)) {
                $results = true;
                $url->setParam('r', $typeUri, true); // create properties url for the class
                $typesArr[$typeUri] = array(
                    'uri'      => $typeUri,
                    'url'      => (string) $url,
                    'title'    => $titleHelper->getTitle($typeUri, $this->_lang),
                    'has_more' => false
                );

                // has_more is used for the dots
                if (count($instances) > OW_SHOW_MAX) {
                    $typesArr[$typeUri]['has_more'] = true;
                    $instances = array_splice ( $instances, 0, OW_SHOW_MAX);
                }

                $instTitleHelper = new OntoWiki_Model_TitleHelper($this->_owApp->selectedModel);
                $instTitleHelper->addResources($instances, 'uri');

                $conf['filter'][0] = array(
                    'mode' => 'rdfsclass',
                    'rdfsclass' => $typeUri,
                    'action' => 'add'
                );

                // the list url is used for the context menu link
                $listUrl->setParam('instancesconfig', json_encode($conf), true);
                $listUrl->setParam('init', true, true);
                $typesArr[$typeUri]['listUrl'] = (string) $listUrl;


                foreach ($instances as $row) {
                    $instanceUri = $row['uri'];
                    // set URL
                    $url->setParam('r', $instanceUri, true);

                    if (!array_key_Exists($typeUri, $similars)) {
                        $similars[$typeUri] = array();
                    }

                    // add instance
                    $similars[$typeUri][$instanceUri] = array(
                        'uri'   => $instanceUri,
                        'title' => $instTitleHelper->getTitle($instanceUri, $this->_lang),
                        'url'   => (string) $url,
                    );
                }
            }
        }

        $this->view->types    = $typesArr;
        $this->view->similars = $similars;

        if (!$results) {
            $this->view->message = 'No matches.';
        }

        return $this->render('similarinstances');
    }

    public function getStateId()
    {
        $id = $this->_owApp->selectedModel
            . $this->_owApp->selectedResource;

        return $id;
    }

    private function _getTypes()
    {
        $typesInferred = array();

        $query = new Erfurt_Sparql_SimpleQuery();

        $query->setProloguePart('SELECT DISTINCT ?uri')
              ->setWherePart('
                WHERE {
                    <' . (string) $this->_owApp->selectedResource . '> a ?uri.
                    ?similar a ?uri.
                    FILTER isUri(?uri)
                }');

        if ($result = $this->_owApp->selectedModel->sparqlQuery($query)) {
            $types = array();
            foreach ($result as $row) {
                array_push($types, $row['uri']);
            }

            $typesInferred = $this->_store->getTransitiveClosure(
                (string) $this->_owApp->selectedModel,
                EF_RDFS_SUBCLASSOF,
                $types,
                false
            );
        }

        return array_keys($typesInferred);
    }
}


