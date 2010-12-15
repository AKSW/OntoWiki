<?php
class PreviewModule extends OntoWiki_Module
{    
    /**
     * Constructor
     */
    public function init()
    {
        $this->view->headScript()->appendFile($this->view->moduleUrl . 'preview.js');
    }
    
    public function shouldShow()
    {
        return true;
    }

    public function getContents()
    {
        return '<div id="selected-resource-preview">none</div>';
    }
}
