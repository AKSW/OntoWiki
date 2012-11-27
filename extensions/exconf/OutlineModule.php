<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki module – Extension Navigation
 *
 * @category   OntoWiki
 * @package    Extensions_Exconf
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class OutlineModule extends OntoWiki_Module
{
    public function init() {
    }
    	
    public function getTitle() {
        return 'Extension Outline';
    }
    
    /**
     * Returns the content
     */
    public function getContents() {
        #$data['resourceUri'] = $this->_owApp->selectedResource->getIri();
        #$content = $this->render('tagging', $data, 'data');
        $this->view->headScript()->appendFile($this->view->moduleUrl . '/resources/outline.js');
        $content = '<div class="outline" />';
        return $content;
    }
	
    public function shouldShow(){
        return true;
    }
}
