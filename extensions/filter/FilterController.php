<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once 'OntoWiki/Controller/Component.php';

/**
 * Controller for OntoWiki Filter Module
 * the filter module is a gui for list modification which takes place in the ListSetupHelper
 * this controller is a helper for this module to supply possible values for a property
 *
 * @category   OntoWiki
 * @package    Extensions_Filter
 * @author     Jonas Brekle <jonas.brekle@gmail.com>
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class FilterController extends OntoWiki_Controller_Component
{
    public function getpossiblevaluesAction()
    {
        $listHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('List');
        $listName = $this->_request->getParam('list');
        if ($listHelper->listExists($listName)) {
            $list   = $listHelper->getList($listName);
            //TODO the store is not serialized anymore. it is missing by default. Controllers have to set it
            //how to determine here?
            //use the default store here - but also Sparql-Adapters are possible
            $list->setStore($this->_erfurt->getStore());
        } else {
            $this->view->values = array();
            return;
        }

        $predicate = $this->_request->getParam('predicate', '');
        $inverse = $this->_request->getParam('inverse', '');

        $this->view->values = $list->getPossibleValues($predicate, true, $inverse == "true");

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

