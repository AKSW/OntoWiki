<?php

require_once 'OntoWiki/Module.php';

/**
 * OntoWiki module – Explore tags
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_exploretags
 * @author     Atanas Alexandrov <sirakov@gmail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: exploretags.php 4276 2009-10-11 11:38:55Z jonas.brekle@gmail.com $
 */
class ExploretagsModule extends OntoWiki_Module
{
    public function getTitle()
    {
        return 'Explore Tags';
    }    
    
	public function init()
    {
        $this->view->headScript()->appendFile($this->view->moduleUrl . 'exploretags.js');
        $this->view->headLink()->appendStylesheet($this->view->moduleUrl . 'exploretags.css');
    }
    
	/**
     * Returns the menu of the module
     *
     * @return string
     */
    public function getMenu() {        
	// count sub menu
	require_once ('OntoWiki/Menu.php');
	$countMenu = new OntoWiki_Menu();
        $countMenu->setEntry('5', 'javascript:count(5)')
                  ->setEntry('10', 'javascript:count(10)')
                  ->setEntry('20', 'javascript:count(20)');
        
        // sort sub menu
        $sortTagcloud = new OntoWiki_Menu();
        $sortTagcloud->setEntry('by name', 'javascript:sortTagCloud(1)')
        		     ->setEntry('by frequency', 'javascript:sortTagCloud(2)');
        		     
        // view sub menu
        $viewMenu = new OntoWiki_Menu();
        $viewMenu->setEntry('Reset Explore Tags Box', 'javascript:resetExploreTags()')
                 ->setEntry('Reset selected tags', 'javascript:resetSelectedTags()')
                 ->setEntry('Number of showed tags', $countMenu)
                 ->setEntry('Sort', $sortTagcloud);              
        		 
        // build menu out of sub menus
        $taggingMenu = new OntoWiki_Menu();
        $taggingMenu->setEntry('View', $viewMenu);
        
        return $taggingMenu;
    }
    
	/**
	 * Returns the content for the model list.
	 */
	function getContents()
	{						
            $content = $this->render('exploretags');
            return $content;
	}
}


