<?php

require_once 'OntoWiki/Controller/Component.php';
require_once 'OntoWiki/Toolbar.php';

/**
 * Controller for OntoWiki Filter Module
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_files
 * @author     Michael Martin
 * @copyright  Copyright (c) 2009, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class CacheconfigurationController extends OntoWiki_Controller_Component {

    public function init() {

        parent::init();
        $this->owApp = OntoWiki::getInstance(); 

        // disable tabs
        require_once 'OntoWiki/Navigation.php';
        OntoWiki_Navigation::disableNavigation();

        // get translation object
        $this->translate = $this->_owApp->translate;

        //set title of main window ...
        $this->view->placeholder('main.window.title')->set($this->translate->_('Cache Configuration', $this->_config->languages->locale));

    }

    /**
     * @access private
     *
     */
    public function initAction() {

        //DISPATCHING SOME ACTIONS
        if (isset($this->_request->actionType)) {

            if ( $this->_request->actionType == "viewTriple" ) {
                $limit      = $this->_request->getParam('limit', null, false);
                $occurence  = $this->_request->getParam('occurence', null, false);

                $forms['showTriple']['limit']       = $limit;
                $forms['showTriple']['occurence']   = $occurence;

                $this->view->tripleList = $this->_getCacheTriplePattern($limit, $occurence);
            }

            if ( $this->_request->actionType == "createViews" ) {

                $limit      = $this->_request->getParam('limit', null, false);
                $occurence  = $this->_request->getParam('occurence', null, false);

                $forms['createViews']['limit']      = $limit;
                $forms['createViews']['occurence']  = $occurence;

                $this->view->viewsList = $this->_createViews($limit, $occurence);
            }

        }
        $url    = new OntoWiki_Url(array('controller' => 'cacheconfiguration', 'action' => 'init'));
        $forms['showTriple']['action']  = (string) $url;
        $forms['createViews']['action'] = (string) $url;
        $this->view->forms = $forms;
    }

    private function _getCacheTriplePattern ($limit, $occurence) {

        $queryCache = $this->_erfurt->getQueryCache();
        $ret        = $queryCache->getUsedTriplePattern($limit, $occurence);
        return $ret;

    }

    private function _createViews ($limit, $occurence) {

        $queryCache = $this->_erfurt->getQueryCache();
        $ret        = $queryCache->createMaterializedViews($limit, $occurence);
        return $ret;

    }
}

