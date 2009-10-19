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
       
    public function getpossiblevaluesAction(){
    	$store       = $this->_owApp->erfurt->getStore();
        $graph       = $this->_owApp->selectedModel;
        $resource    = $this->_owApp->selectedClass;
        $options = array(
            'rdf_type' => (string) $resource,
            'member_predicate' => EF_RDF_TYPE, // TODO make this variable for handling collections...
            'withChilds' => true,
            'limit' => 0,
            'offset' => 0,
            'shownProperties' => is_array($this->_session->shownProperties) ? $this->_session->shownProperties : array(),
            'shownInverseProperties' => is_array($this->_session->shownInverseProperties) ? $this->_session->shownInverseProperties : array(),
            'filter' => is_array($this->_session->filter) ? $this->_session->filter : array(),
	);
		
        // instantiate model
        require_once 'OntoWiki/Model/Instances.php';
        
        $instances   = new OntoWiki_Model_Instances($store, $graph, $options);
    	
    	$predicate = $this->_request->getParam('predicate', '');
        $inverse = $this->_request->getParam('inverse', '');
    	
        if($inverse == "true"){
            $this->view->values = $instances->getSubjects($predicate);
        } else {
            $this->view->values = $instances->getObjects($predicate);
        }

        require_once 'OntoWiki/Model/TitleHelper.php';
        $titleHelper = new OntoWiki_Model_TitleHelper($graph);
        foreach($this->view->value as $value){
            if($value['type'] == 'uri'){
                $titleHelper->addResource($value['value']);
            }
        }
        foreach($this->view->value as $key => $value){
            if($value['type'] == 'uri'){
                $this->view->value[$key]['title'] = $titleHelper->getTitle($value['value']);
            }
        }
    }
    
    
}

