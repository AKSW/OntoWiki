<?php

/**
 * OntoWiki module – taggging
 *
 * this is the main tagging action module
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_tagging
 * @author     Atanas Alexandrov <sirakov@gmail.com>
 * @author     Sebastian Dietzold <dietzold@informatik.uni-leipzig.de>
 * @copyright  Copyright (c) 2009, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: tagging.php 4200 2009-09-28 12:08:49Z jonas.brekle@gmail.com $
 */
class TaggingModule extends OntoWiki_Module
{
    public function init() {
        $this->view->headScript()->appendFile($this->view->moduleUrl . 'tagging.js');
        $this->view->headLink()->appendStylesheet($this->view->moduleUrl . 'tagging.css');
    }
    
	
    public function getTitle() {
        return 'Tagging';
    }
    
    /**
     * Returns the content
     */
    public function getContents() {
        $data['resourceUri'] = $this->_owApp->selectedResource->getIri();
        $content = $this->render('tagging', $data, 'data');
        return $content;
    }
	
	public function shouldShow(){
		return true;
	}
}


