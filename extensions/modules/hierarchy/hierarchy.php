<?php
require_once 'Erfurt/Sparql/SimpleQuery.php';

require_once 'OntoWiki/Module.php';
require_once 'OntoWiki/Model/Hierarchy.php';
require_once 'OntoWiki/Url.php';
require_once 'OntoWiki/Utils.php';

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
	
    // public function init()
    // {
    //     $this->_model = $this->_owApp->selectedModel;
    // }
	
/*	public function getContextMenu()
	{
	    $contextMenu = new OntoWiki_Menu();
	    $contextMenu->setEntry('Create Class', '#')
                    ->setEntry(OntoWiki_Menu::SEPARATOR)
	                ->setEntry('Show Hidden Classes', '#')
	                ->setEntry('Show Empty Classes', '#')
	                ->setEntry('Show System Classes', '#')
	                ->setEntry(OntoWiki_Menu::SEPARATOR)
	                ->setEntry('Help', '#');
	    
	    return $contextMenu;
	}
*/
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
	    $session = OntoWiki_Application::getInstance()->session;
	    
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


