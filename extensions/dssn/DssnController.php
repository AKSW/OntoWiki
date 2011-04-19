<?php
/**
 * distributed semantic social network client
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_dssn
 * @copyright  Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class DssnController extends OntoWiki_Controller_Component {

    public function init() {
        parent::init();
        OntoWiki_Navigation::reset();

        // create the navigation tabs
        OntoWiki_Navigation::register('news', array('route'      => null,
            'controller' => 'dssn',
            'action'     => 'news',
            'name'   => 'News & Activities'));
        OntoWiki_Navigation::register('contacts', array('route'      => null,
            'controller' => 'dssn',
            'action'     => 'network',
            'name'   => 'Network'));

        // add dssn specific styles and javascripts
        $this->view->headLink()->appendStylesheet($this->_componentUrlBase . 'js/dssn.js');
        $this->view->headLink()->appendStylesheet($this->_componentUrlBase . 'css/dssn.css');
    }

    /*
     * news & activities tab
     */
    public function newsAction() {
        $translate  = $this->_owApp->translate;
        $store      = $this->_owApp->erfurt->getStore();
        $model      = $this->_owApp->selectedModel;
        $listHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('List');

        $this->view->placeholder('main.window.title')->set($translate->_('News & Activities'));
        $this->addModuleContext('main.window.dssn.news');
        $this->addModuleContext('main.window.list');

        $listName     = "dssn-activities";
        $listTemplate = "list_dssn_activities_main";
        if(!$listHelper->listExists($listName)){
            $list = new OntoWiki_Model_Instances($store, $model, array());
            $list->addTypeFilter($this->_privateConfig->uri->activity);
            $listHelper->addListPermanently($listName, $list, $this->view, $listTemplate);
        } else {
            $list = $listHelper->getList($listName);
            $listHelper->addList($listName, $list, $this->view, $listTemplate);
        }
    }

    /*
     * list and add friends / contacts tab
     */
    public function networkAction() {
        $translate   = $this->_owApp->translate;
        $store       = $this->_owApp->erfurt->getStore();
        $model       = $this->_owApp->selectedModel;

        $this->view->placeholder('main.window.title')->set($translate->_('Network'));
        $this->addModuleContext('main.window.dssn.network');
    }
}

