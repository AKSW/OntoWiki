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
        if(isset($this->_owApp->instances)) {
            $this->_owApp->logger->info('minimap/getContent session: rdf_type => ' . var_export($this->_owApp->selectedClass, true));
            return $this->render('minimap');
        } 
    }

    public function shouldShow()
    {
        $this->_owApp->logger->info('minimap/shouldShow session: rdf_type => ' . var_export($this->_owApp->selectedClass, true));
        //        require_once 'extensions/components/MapHelper.php';
        if(class_exists('MapHelper')) {
            $helper = new MapHelper($this->_owApp->componentManager);
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


