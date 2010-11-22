<?php

/**
 * OntoWiki index controller.
 * 
 * @package    application
 * @subpackage mvc
 * @author     Norman Heino <norman.heino@gmail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: IndexController.php 4239 2009-10-05 15:07:39Z yamalight $
 */
class IndexController extends OntoWiki_Controller_Base
{
	/**
     * Displays the OntoWiki news feed short summary (dashboard part)
     */
    public function newsshortAction()
    {
        // requires zend Feed module
		// number of news
		$feed_count = 3;
        // create empty var for feed
        $owFeed  = null;
        // get current version
        $version = $this->_config->version;
        // try reading
        try {
            $url = 'http://blog.aksw.org/feed/?cat=5&client='
                . $version->label
                . '&version='
                . $version->number
                . '&suffix='
                . $version->suffix;

            $owFeed = Zend_Feed::import($url);
        } catch (Exception $e) {
            $owFeed = $e;
        }

        // create new array for data
        $data = array();
        // parse feed items into array
        foreach ($owFeed as $feedItem) {
        // form temporary array with needed data
            $tempdata = array(
                'link' => $feedItem->link(),
                'title' => $feedItem->title(),
                'description' => substr($feedItem->description(),0, strpos($feedItem->description()," ",40) )." [...]"
            );
            // append temporary array to data array
            $data[] = $tempdata;

            // take only needed items
            if(count($data) == $feed_count) break;
        }
        // assign data array to view rss data variable
        $this->view->rssData = $data;
    } 
	
	

    /**
     * Displays messages only without any othe content.
     */
    public function messagesAction()
    {
        OntoWiki_Navigation::disableNavigation();
        $this->view->placeholder('main.window.title')->set('OntoWiki Messages');
        $this->_helper->viewRenderer->setNoRender();
    }
    
    /**
     * Displays the OntoWiki news feed
     */
    public function newsAction()
    {
        $owFeed  = null;
        $version = $this->_config->version;
        
        $this->view->placeholder('main.window.title')->set('News');
        
        try {
            $url = 'http://blog.aksw.org/feed/?cat=5&client=' 
                 . $version->label 
                 . '&version=' 
                 . $version->number 
                 . '&suffix=' 
                 . $version->suffix;
            
            $owFeed = Zend_Feed::import($url);
            
            $this->view->feed        = $owFeed;
            $this->view->title       = $owFeed->title();
            $this->view->link        = $owFeed->link();
            $this->view->description = $owFeed->description();
            
        } catch (Exception $e) {
            $this->view->messages = array(new OntoWiki_Message('Error loading feed: ' . $url, OntoWiki_Message::ERROR));
            $this->view->feed     = array();
            // var_dump($url);
            // exit;
        }
        
        OntoWiki_Navigation::disableNavigation();
    }
    
    /**
     * Default action if called action wasn't found
     */
    public function __call($action, $params)
    {
        OntoWiki_Navigation::disableNavigation();
        $this->view->placeholder('main.window.title')->set('Welcome to OntoWiki');

        if ($this->_owApp->hasMessages()) {
            $this->_forward('messages', 'index');
        } else {
            if ((!isset($this->_config->index->default->controller)) || (!isset($this->_config->index->default->action))) {
                $this->_forward('news', 'index');
            } else {
                $this->_forward($this->_config->index->default->action, $this->_config->index->default->controller);
            }

        }
    }
    
    /**
     * This action display simply no main window section and is useful
     * in combination with index.default.controller and index.default.action
     */
    public function emptyAction()
    {
        // service controller needs no view renderer
        $this->_helper->viewRenderer->setNoRender();
        OntoWiki_Navigation::disableNavigation();
        // sorry for this hack, but I dont wanted to modify the the main layout too much ...
        $this->view->placeholder('main.window.additionalclasses')->set('hidden');
    }    
}


