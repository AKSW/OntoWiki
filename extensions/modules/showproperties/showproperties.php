<?php

/**
 * OntoWiki module – showproperties
 *
 * Add instance properties to the list view
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_showproperties
 * @author     Norman Heino <norman.heino@gmail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: showproperties.php 4222 2009-10-02 10:54:38Z sebastian.dietzold $
 */
class ShowpropertiesModule extends OntoWiki_Module
{
    public function init()
    {
        // load js
        $this->view->headScript()->appendFile($this->view->moduleUrl . 'showproperties.js');
        
        $session = $this->_owApp->session;
        
        $shownProperties = (is_array($session->shownProperties) 
                         ? json_encode($session->shownProperties) 
                         : '[]');
        
        $shownInverseProperties = (is_array($session->shownInverseProperties) 
                                ? json_encode($session->shownInverseProperties) 
                                : '[]');
        
        $this->view->headScript()->appendScript('\
            var shownProperties = ' . $shownProperties.';\
            var shownInverseProperties = ' . $shownInverseProperties . ';');
    }
    
    public function getContents()
    {
        if (isset($this->_owApp->instances)) {
            $this->view->properties = $this->_owApp->instances->getAllProperties();
            $this->view->reverseProperties = $this->_owApp->instances->getAllReverseProperties();
            
            return $this->render('showproperties');
        }
        
        return 'No instances object';
    }
    
    public function getStateId()
    {
        $id = $this->_owApp->selectedModel
            . $this->_owApp->selectedResource;
        
        return $id;
    }
}


