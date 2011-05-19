<?php

/**
 * OntoWiki module – taggging
 *
 * this is the main tagging action module
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_tagging
 * @author     Atanas Alexandrov <sirakov@gmail.com>
 * @author     Sebastian Tramp <tramp@informatik.uni-leipzig.de>
 * @copyright  Copyright (c) 2009, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class TaggingModule extends OntoWiki_Module
{
    public function init() {
        $this->view->headScript()->appendFile($this->view->moduleUrl . 'tagging.js');
        $this->view->headLink()->appendStylesheet($this->view->moduleUrl . 'tagging.css', 'screen');
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
        // do not show if model is not writeable
        if ( (isset($this->_owApp->selectedModel)) &&
                ($this->_owApp->erfurt->getAc()->isModelAllowed('edit', $this->_owApp->selectedModel) ) &&
                        (isset($this->_owApp->selectedResource)) ) {
            return true;
        } else {
            return false;
        }
    }
}
