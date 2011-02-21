<?php

require_once 'OntoWiki/Controller/Component.php';

/**
 * Controller for OntoWiki Filter Module
 * the filter module is a gui for list modification which takes place in the ListSetupHelper
 * this controller is a helper for this module to supply possible values for a property
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_filter
 * @author     Jonas Brekle <jonas.brekle@gmail.com>
 * @copyright  Copyright (c) 2010, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $$
 */
class FilterController extends OntoWiki_Controller_Component
{
       
    public function getpossiblevaluesAction() {
        $this->view->mobile = Zend_Controller_Front::getInstance()->getRequest()->isMobile();
        
        $listHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('List');
        if($listHelper->listExists($this->_request->getParam('list'))){
            $instances   = $listHelper->getList($this->_request->getParam('list'));
        } else {$this->view->values = array(); return;}
        
        $predicate = $this->_request->getParam('predicate', '');
        $inverse = $this->_request->getParam('inverse', '');
        
        $this->view->values = $instances->getPossibleValues($predicate, true, $inverse == "true");
        
        require_once 'OntoWiki/Model/TitleHelper.php';
        $titleHelper = new OntoWiki_Model_TitleHelper($this->_owApp->selectedModel);
        foreach ($this->view->values as $value) {
            if ($value['type'] == 'uri') {
                $titleHelper->addResource($value['value']);
            }
        }
        foreach ($this->view->values as $key => $value) {
            if ($value['type'] == 'uri') {
                $this->view->values[$key]['title'] = $titleHelper->getTitle($value['value']);
            } else {
                $this->view->values[$key]['title'] = $value['value'];
            }
        }
    }
    
    
}

