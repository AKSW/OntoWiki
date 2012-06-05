<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once 'OntoWiki/Controller/Component.php';

/**
 * Mass Geocoding of Ressources via attributes (parameter r)
 *
 *
 * @category   OntoWiki
 * @package    Extensions_Savedqueries
 * @author     Michael Martin <martin@informatik.uni-leipzig.de>
 * @author     Sebastian Dietzold <dietzold@informatik.uni-leipzig.de>
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class SavedqueriesController extends OntoWiki_Controller_Component
{
    private $model = null;
    private $translate = null;

    // ------------------------------------------------------------------------
    // --- Component initialization -------------------------------------------
    // ------------------------------------------------------------------------
    public function init()
    {
        parent::init();
        // m is automatically used and selected
        if ((!isset($this->_request->m)) && (!$this->_owApp->selectedModel)) {
            require_once 'OntoWiki/Exception.php';
            throw new OntoWiki_Exception('No model pre-selected and missing parameter m (model)!');
            return;
        } else {
            $this->model = $this->_owApp->selectedModel;
        }

        // disable tabs
        require_once 'OntoWiki/Navigation.php';
        OntoWiki::getInstance()->getNavigation()->disableNavigation();

        // get translation object
        $this->translate = $this->_owApp->translate;

        $this->queryString = $this->_request->getParam('query', '');
        $this->queryLabel = $this->_request->getParam('label', '');

        //set title of main window ...
        $this->view->placeholder('main.window.title')->set($this->queryLabel);
    }

    /**
     * initialization of the geocoder Action
     * @access private
     *
     */
    public function initAction() {
        // create a new button on the toolbar
        $queryResult = $this->_getQueryResult($this->queryString);

		$header = array ();
        try {
		    if (is_array($queryResult) && isset ($queryResult[0]) && is_array($queryResult[0])) {
			    $header = array_keys($queryResult[0]);
		    } else if (is_bool($queryResult)) {
			    $queryResult = $queryResult ? 'yes' : 'no';
		    } else if (is_int($queryResult)) {
			    $queryResult = (string) $queryResult;
		    } else if (is_string($queryResult)) {
			    $queryResult = $queryResult;
		    } else {
			    $queryResult = 'no result';
		    }
        } catch (Exception $e) {
	        $this->view->error = $e->getMessage();
	        $header = '';
	        $queryResult = '';
        }

	    $this->view->queryResult = $queryResult;
	    $this->view->header = $header;
    }

    private function _getQueryResult( $queryString) {
        $elements = $this->model->sparqlQuery($queryString);
        return $elements;
    }
}

