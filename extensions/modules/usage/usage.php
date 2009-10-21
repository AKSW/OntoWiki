<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_usage
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version   $Id: usage.php 4092 2009-08-19 22:20:53Z christian.wuerker $
 */


/**
 * OntoWiki usage module
 *
 * Adds the "Usage as Property" box to the properties context
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_usage
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @category  extensions
 * @package   modules
 * @author    Norman Heino <norman.heino@gmail.com>
 * @author    Sebastian Dietzold <dietzold@informatik.uni-leipzig.de>
 */
class UsageModule extends OntoWiki_Module
{
    /** @var array */
    protected $_instances = null;
    
    /** @var OntoWiki_Model */
    protected $_model = null;
    
    /** @var array */
    protected $_objects = null;
    
    /**
     * Constructor
     */
    public function init()
    {
        // instances (subjects)
        $query1 = new Erfurt_Sparql_SimpleQuery();
        $query1->setProloguePart('SELECT DISTINCT ?uri')
              ->setWherePart('WHERE {
                    ?uri <' . (string) $this->_owApp->selectedResource . '> ?object.
                    FILTER (isURI(?uri))
                }')
            ->setLimit(OW_SHOW_MAX);
        $this->_instances = $this->_owApp->selectedModel->sparqlQuery($query1);
        
        // objects
        $query2 = new Erfurt_Sparql_SimpleQuery();
        $query2->setProloguePart('SELECT DISTINCT ?uri')
              ->setWherePart('WHERE {
                    ?subject <' . (string) $this->_owApp->selectedResource . '> ?uri.
                    FILTER (isURI(?uri))
                }')
            ->setLimit(OW_SHOW_MAX);
        $this->_objects = $this->_owApp->selectedModel->sparqlQuery($query2);
    }
    
    public function shouldShow()
    {
        if (!empty($this->_instances) || !empty($this->_objects)) {
            return true;
        }
        
        return false;
    }

    public function getTitle()
    {
        $title = $this->view->_($this->title) . ' (' 
               . count($this->_instances) . '/' 
               . count($this->_objects) . ')';
        
        return $title;
    }


    public function getContents()
    {
        $url = new OntoWiki_Url(array('route' => 'properties'));
        
        if (!empty($this->_instances)) {
            $instances = array();
            
            $instancesTitleHelper = new OntoWiki_Model_TitleHelper($this->_owApp->selectedModel);
            $instancesTitleHelper->addResources($this->_instances, 'uri');
            
            foreach ($this->_instances as $instance) {
                $instanceUri = $instance['uri'];
                
                if (!array_key_exists($instanceUri, $instances)) {
                    // URL
                    $url->setParam('r', $instanceUri, true);
                    
                    $instances[$instanceUri] = array(
                        'uri'   => $instanceUri, 
                        'title' => $instancesTitleHelper->getTitle($instanceUri, $this->_lang), 
                        'url'   => (string) $url 
                    );
                }
            }
            $this->view->instances = $instances;
        }
                
        if (!empty($this->_objects)) {
            $objects = array();
            
            $objectTitleHelper = new OntoWiki_Model_TitleHelper($this->_owApp->selectedModel);
            $objectTitleHelper->addResources($this->_objects, 'uri');
            
            foreach ($this->_objects as $object) {
                $objectUri = $object['uri'];
                
                if (!array_key_exists($objectUri, $objects)) {
                    // URL
                    $url->setParam('r', $objectUri, true);
                    
                    $objects[$objectUri] = array(
                        'uri' => $objectUri, 
                        'title' => $objectTitleHelper->getTitle($objectUri, $this->_lang), 
                        'url' => (string) $url
                    );
                }
            }
            $this->view->objects = $objects;
        }
        
        if (empty($this->_instances) and empty($this->_objects)) {
            $this->view->message = 'No matches.';
        }
        
        // render data into template
        return $this->render('usage');
    }
    
    public function getStateId() {
        $id = $this->_owApp->selectedModel
            . $this->_owApp->selectedResource;
        
        return $id;
    }
}


