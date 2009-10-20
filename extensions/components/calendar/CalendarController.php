<?php

/**
 * Google map component controller.
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_calendar
 */
class CalendarController extends OntoWiki_Controller_Component
{
    public function __call($method, $args)
    {
        $this->view->placeholder('main.window.title')->set('OntoWiki – Collaborative Knowledge Engineering');
        echo 'calendar component working! :)';
        $this->_helper->viewRenderer->setNoRender();
    }
}


