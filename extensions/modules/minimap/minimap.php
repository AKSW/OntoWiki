<?php
// vim: sw=4:sts=4:expandtab
require_once 'OntoWiki/Module.php';

/**
 * OntoWiki module – minimap
 *
 * display a minimap of the currently visible resources (if any)
 *
 * @package    ontowiki
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: minimap.php 4241 2009-10-05 22:33:25Z arndtn $
 */
class MinimapModule extends OntoWiki_Module
{
    public function init()
    {
    }

    public function getContents()
    {        
        if(isset($this->_owApp->session->instances)) {
            if($this->_request->getControllerName() == 'resource' && $this->_request->getActionName() == 'properties') {
                $this->view->context = 'single_instance';
            }
            return $this->render('minimap');
        } else {
            $this->view->message = 'No Instances object in session.';
            $this->_owApp->logger->debug('minimap: error: this->_session->instances is not set!');
            return $this->render('error');
        } 
    }

    public function shouldShow()
    {
        if(class_exists('MapHelper')) {
            $helper = $this->_owApp->componentManager->getComponentHelper('map');
           // $helper = new MapHelper($this->_owApp->componentManager);
            return $helper->shouldShow();
        } else {
            return false;
        }
    }


    public function getStateId()
    {
        $id = $this->_owApp->selectedModel
            . $this->_owApp->selectedResource;
        
        return $id;
    }
}


