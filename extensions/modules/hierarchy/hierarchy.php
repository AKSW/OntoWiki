<?php

/**
 * OntoWiki module – hierarchy
 *
 * Showsa a customisable hierarchy of related objects. Most often used with
 * class – subclass – instance relationships, but not limited to them.
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_hierarchy
 * @author     Norman Heino <norman.heino@gmail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: hierarchy.php 4092 2009-08-19 22:20:53Z christian.wuerker $
 */
class HierarchyModule extends OntoWiki_Module
{
	const SERVICE_URL = 'hierarchy';

    public function init()
    {
        $this->view->headLink()->appendStylesheet($this->view->moduleUrl . 'hierarchy.css');
    }

	public function getTitle()
	{
	    return 'Classes';
	}
	
	/**
	 * Returns the content for the model list.
	 */
	public function getContents()
	{
	    $model = new OntoWiki_Model_Hierarchy(Erfurt_App::getInstance()->getStore(), $this->_owApp->selectedModel);
	    
	    if ($model->hasData()) {
	        $this->view->classes      = $model->getHierarchy();
	        $this->view->currentClass = $this->_owApp->selectedClass;
	    } else {
			$this->view->classes = array();
			$this->view->message = 'No matches.';
		}
		
		$content = $this->render('hierarchy');
		
		return $content;
	}
	
	public function getStateId() {
	    $session = OntoWiki::getInstance()->session;
	    
        $id = $this->_owApp->selectedModel->getModelIri()
            . $this->_owApp->selectedClass
            . print_r($session->hierarchyOpen, true);
        
        return $id;
    }
    
    public function shouldShow()
    {
        if ($this->_owApp->selectedModel) {
            return true;
        }
        
        return false;
    }
    
    public function allowCaching()
    {
        // no caching
        // return false;
        return true;
    }
}


