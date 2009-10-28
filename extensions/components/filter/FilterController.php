<?php

require_once 'OntoWiki/Controller/Component.php';

/**
 * Controller for OntoWiki Filter Module
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_filter
 * @author     Denis Hauser <denis.gartner@googlemail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $$
 */
class FilterController extends OntoWiki_Controller_Component
{
       
    public function getpossiblevaluesAction() {
        $instances   = $this->_session->instances;
        
        $predicate = $this->_request->getParam('predicate', '');
        $inverse = $this->_request->getParam('inverse', '');
        
        if ($inverse == "true") {
            $this->view->values = $instances->getSubjects($predicate);
        } else {
            $this->view->values = $instances->getObjects($predicate);
        }

        require_once 'OntoWiki/Model/TitleHelper.php';
        $titleHelper = new OntoWiki_Model_TitleHelper($graph);
        foreach ($this->view->value as $value) {
            if ($value['type'] == 'uri') {
                $titleHelper->addResource($value['value']);
            }
        }
        foreach ($this->view->value as $key => $value) {
            if ($value['type'] == 'uri') {
                $this->view->value[$key]['title'] = $titleHelper->getTitle($value['value']);
            } else {
                $this->view->value[$key]['title'] = $value['value'];
            }
        }
    }
    
    
}

