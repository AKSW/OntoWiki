<?php
class ApplicationModule extends OntoWiki_Module
{
    public function init()
    {

    }

    /**
    * Returns the title of the module
    *
    * @return string
    */
    public function getTitle()
    {
        return  'Test';
    }

    public function shouldShow()
    {
        return true;
    }

    /**
    * Returns the menu of the module
    *
    * @return string
    */
    public function getMenu()
    {
        return OntoWiki_Menu_Registry::getInstance()->getMenu('application');
    }

    public function getContents()
    {
    return '';
    }

    public function allowCaching()
    {
        // no caching
        return false;
    }
}


