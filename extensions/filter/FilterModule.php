<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once 'OntoWiki/Module.php';

/**
 * OntoWiki module – filter
 *
 * Add instance properties to the list view
 *
 * @category   OntoWiki
 * @package    Extensions_Filter
 * @author     Norman Heino <norman.heino@gmail.com>
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class FilterModule extends OntoWiki_Module
{
    protected $_instances = null;
    public function init()
    {

    }
    
    
    public function getTitle()
    {
        return 'Filter';
    }
    
    public function getContents()
    {
        $listHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('List');
        $this->_instances = $listHelper->getLastList();

        if(!($this->_instances instanceof OntoWiki_Model_Instances)){
            return "Error: List not found";
        }

        $this->store = $this->_owApp->erfurt->getStore();
        $this->model = $this->_owApp->selectedModel;
        $this->titleHelper = new OntoWiki_Model_TitleHelper($this->_owApp->selectedModel);

        $this->view->headLink()->appendStylesheet($this->view->moduleUrl . 'resources/filter.css');
        //$this->view->headScript()->appendFile($this->view->moduleUrl . 'resources/jquery.dump.js');
        
        $this->view->properties = $this->_instances->getAllProperties(false);
        $this->view->inverseProperties = $this->_instances->getAllProperties(true);
        
        $this->view->actionUrl      = $this->_config->staticUrlBase . 'index.php/list/';
        $this->view->s      = $this->_request->s;
        
        $this->view->filter = $this->_instances->getFilter();
        if (is_array( $this->view->filter)) {
            foreach ( $this->view->filter as $key => $filter) {
                switch($filter['mode']){
                    case 'box':
                        if ($filter['property']) {
                            $this->view->filter[$key]['property'] = trim($filter['property']);
                            $this->titleHelper->addResource($filter['property']);
                        }
                        if ($filter['valuetype'] == 'uri' && !empty($filter['value1'])) {
                            $this->titleHelper->addResource($filter['value1']);
                        }
                        if ($filter['valuetype'] == 'uri' && !empty($filter['value2'])) {
                            $this->titleHelper->addResource($filter['value2']);
                        }
                    break;
                    case 'rdfsclass':
                        $this->titleHelper->addResource($filter['rdfsclass']);
                    break;
                }
            }
        }

        $this->view->titleHelper = $this->titleHelper;

        $this->view->headScript()->appendFile($this->view->moduleUrl . 'resources/filter.js');

        $content = $this->render('filter/filter');
        return $content;
    }

    public function getMenu() {
	$edit = new OntoWiki_Menu();
        $edit->setEntry('Add', 'javascript:showAddFilterBox()')
                  ->setEntry('Remove all', 'javascript:removeAllFilters()');
        
	$main = new OntoWiki_Menu();
        $main->setEntry('Edit', $edit);
        
        return $main;
    }
}

