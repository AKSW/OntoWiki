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
class CustomfilterModule extends OntoWiki_Module
{
    protected $_instances = null;
    public function init()
    {

    }
    
    
    public function getTitle()
    {
        return 'Custom Filter';
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
        $this->view->headScript()->appendFile($this->view->moduleUrl . 'resources/jquery.dump.js');
        
        
        $this->view->headScript()->appendFile($this->view->moduleUrl . 'resources/filter.js');

        $this->view->definedfilters = $this->_privateConfig->customfilter->toArray();
        
        $content = $this->render('filter/complexfilter');
        return $content;
    }

    
}

