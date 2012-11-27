<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * OntoWiki usage module
 *
 * Adds the "Usage as Property" box to the properties context
 *
 * @category   OntoWiki
 * @package    Extensions_Resourcemodule
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author    Norman Heino <norman.heino@gmail.com>
 */
class UsageModule extends OntoWiki_Module
{
    /** @var array */
    protected $_subjects = null;

    /** @var OntoWiki_Model */
    protected $_model = null;

    /** @var array */
    protected $_objects = null;

    protected $subjectQuery= null;
    protected $objectQuery= null;

    /**
     * Constructor
     */
    public function init()
    {
    }

    private function _initQuery() {
        // instances (subjects)
        $this->subjectQuery = new Erfurt_Sparql_SimpleQuery();
        $this->subjectQuery->setProloguePart('SELECT DISTINCT ?resourceUri')
              ->setWherePart('WHERE {
                    ?resourceUri <' . (string) $this->_owApp->selectedResource . '> ?object.
                    FILTER (isURI(?resourceUri))
                }')
            ->setLimit(OW_SHOW_MAX);
        $this->_subjects = $this->_owApp->selectedModel->sparqlQuery($this->subjectQuery);

        // objects
        $this->objectQuery = new Erfurt_Sparql_SimpleQuery();
        $this->objectQuery->setProloguePart('SELECT DISTINCT ?resourceUri')
              ->setWherePart('WHERE {
                    ?subject <' . (string) $this->_owApp->selectedResource . '> ?resourceUri.
                    FILTER (isURI(?resourceUri))
                }')
            ->setLimit(OW_SHOW_MAX);
        $this->_objects = $this->_owApp->selectedModel->sparqlQuery($this->objectQuery);
    }

    public function shouldShow()
    {
        if ($this->_privateConfig->show->usage == true) {
            $this->_initQuery();
            if (!empty($this->_subjects) || !empty($this->_objects)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getTitle()
    {
        $title = $this->view->_('Usage as property') . ' (' 
               . count($this->_subjects) . '/'
               . count($this->_objects) . ')';
        
        return $title;
    }


    public function getContents()
    {
        $url = new OntoWiki_Url(array('route' => 'properties'));
        
        if (!empty($this->_subjects)) {
            $instances = array();
            
            $instancesTitleHelper = new OntoWiki_Model_TitleHelper($this->_owApp->selectedModel);
            $instancesTitleHelper->addResources($this->_subjects, 'resourceUri');
            
            foreach ($this->_subjects as $instance) {
                $instanceUri = $instance['resourceUri'];
                
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
            $objectTitleHelper->addResources($this->_objects, 'resourceUri');
            
            foreach ($this->_objects as $object) {
                $objectUri = $object['resourceUri'];
                
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
        $url = new OntoWiki_Url(array('controller' => 'resource','action' => 'instances'));
        $url->setParam('instancesconfig', json_encode(array('filter'=>array(array('id'=>'propertyUsage','action'=>'add','mode'=>'query','query'=> (string) $this->subjectQuery)))));
        $url->setParam('init', true);
        $this->view->subjectListLink = (string) $url;
        $url->setParam('instancesconfig', json_encode(array('filter'=>array(array('id'=>'propertyUsage','action'=>'add','mode'=>'query','query'=> (string) $this->objectQuery)))));
        $this->view->objectListLink = (string) $url;
        
        if (empty($this->_subjects) and empty($this->_objects)) {
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


