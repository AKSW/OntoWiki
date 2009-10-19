<?php

require_once 'OntoWiki/Module.php';

/**
 * OntoWiki module – linkinhere
 *
 * Add instance properties to the list view
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_linkinghere
 * @author     Norman Heino <norman.heino@gmail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: linkinghere.php 4092 2009-08-19 22:20:53Z christian.wuerker $
 */
class LinkinghereModule extends OntoWiki_Module
{    
    /**
     * Constructor
     */
    public function init()
    {
        require_once 'Erfurt/Sparql/SimpleQuery.php';
        $query = new Erfurt_Sparql_SimpleQuery();

        $query->setProloguePart('SELECT DISTINCT ?uri')
              ->setWherePart('WHERE {
                    ?subject ?uri <' . (string) $this->_owApp->selectedResource . '> .
                    FILTER (isURI(?subject))
                }')
              ->setLimit(OW_SHOW_MAX);

        $this->predicates = $this->_owApp->selectedModel->sparqlQuery($query);
    }

    public function shouldShow()
    {
        // show only if there are predicates
        if ($this->predicates) {
            return true;
        } else {
            return false;
        }
    }

    public function getContents()
    {
        require_once 'OntoWiki/Model/TitleHelper.php';
        $titleHelper = new OntoWiki_Model_TitleHelper($this->_owApp->selectedModel);

        $query = new Erfurt_Sparql_SimpleQuery();
        
        $results = false;
                
        $predicates = $this->predicates;
        $properties = array();
        $instances  = array();
        $url        = new OntoWiki_Url(array('route' => 'properties'), array('r'));

        $titleHelper->addResources($predicates, 'uri');

        foreach ($predicates as $predicate) {
            $predicateUri = $predicate['uri'];

            $properties[$predicateUri] = $titleHelper->getTitle($predicateUri, $this->_lang);

            $query->resetInstance()
                  ->setProloguePart('SELECT DISTINCT ?uri')
                  ->setWherePart('WHERE {
                        ?uri <' . $predicateUri . '> <' . (string) $this->_owApp->selectedResource . '> .
                        FILTER (isURI(?uri))
                    }')
                  ->setLimit(OW_SHOW_MAX + 1);

            if ($subjects = $this->_owApp->selectedModel->sparqlQuery($query)) {
                $results = true;

                $subjectTitleHelper = new OntoWiki_Model_TitleHelper($this->_owApp->selectedModel);
                $subjectTitleHelper->addResources($subjects, 'uri');

                foreach ($subjects as $subject) {
                    $subjectUri = $subject['uri'];
                    $subject['title'] = $subjectTitleHelper->getTitle($subjectUri, $this->_lang);

                    // set URL
                    $url->setParam('r', $subjectUri, true);
                    $subject['url'] = (string) $url;

                    if (array_key_exists($predicateUri, $instances)) {
                        if (!array_key_exists($subjectUri, $instances[$predicateUri])) {
                            $instances[$predicateUri][$subjectUri] = $subject;
                        }
                    } else {
                        $instances[$predicateUri] = array(
                            $subjectUri => $subject
                        );
                    }
                }
            }
        }

        $this->view->properties = $properties;
        $this->view->instances  = $instances;
        
        if (!$results) {
            $this->view->message = 'No matches.';
        }
        
        return $this->render('linkinghere');
    }
    
    public function getStateId() {
        $id = $this->_owApp->selectedModel->getModelIri()
            . $this->_owApp->selectedResource;
        
        return $id;
    }
}


