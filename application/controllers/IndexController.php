<?php

/**
 * OntoWiki index controller.
 * 
 * @package    application
 * @subpackage mvc
 * @author     Norman Heino <norman.heino@gmail.com>
 * @author     Philipp Frischmuth <pfrischmuth@googlemail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: IndexController.php 4239 2009-10-05 15:07:39Z yamalight $
 */
class IndexController extends OntoWiki_Controller_Base
{
    /**
     * The URL of the OntoWiki news feed.
     * 
     * @var string
     */
    const FEED_URL = 'http://blog.aksw.org/feed/?cat=5';
    
    /**
     * This method is called if a action was specified for this controller that
     * was not found. Depending on the configuration this will forward the 
     * request to a default controller/action pair, to the news action or to
     * the messages action iff at least one message is pending.
     * 
     * @param  string $action
     * @param  array  $params
     * @return void
     */
    public function __call($action, $params)
    {
        // If any messages are pending, forward to the messages action.
        if ($this->_owApp->hasMessages()) {
            $this->_forward('messages', 'index');
        } else {
            // If no default controller AND default action are configured,
            // forward to the news action.
            if ((!isset($this->_config->index->default->controller)) ||
                (!isset($this->_config->index->default->action))) {
                        
                $this->_forward('news', 'index');
            } 
            // Forward to the specified default controller/action pair.
            else {
                $this->_forward(
                    $this->_config->index->default->action, 
                    $this->_config->index->default->controller
                );
            }
        }
    }
    
    /**
     * This action displays simply no main window section and is useful when
     * configured as default action (index.default.controller and 
     * index.default.action). By default OntoWiki displays a newsfeed, when
     * accessed on the top-level. When this action is configured as default,
     * a user will e.g. only see the login box.
     * 
     * @return void
     */
    public function emptyAction()
    {
        // We disable rendering of the main window content and navigation.
        $this->_helper->viewRenderer->setNoRender();
        OntoWiki_Navigation::disableNavigation();        
        
        // Disable rendering of the main window, such it is not contained
        // in the HTML output.
        $this->view->isMainWindowDisabled = true;
    }
    
    /**
     * This action only displays pending messages. No other content is shown.
     * 
     * @return void
     */
    public function messagesAction()
    {
        // We disable rendering of the main window content and navigation.
        $this->_helper->viewRenderer->setNoRender();
        OntoWiki_Navigation::disableNavigation();
        
        // Set the title of the main window.
        $this->view->placeholder('main.window.title')->set('OntoWiki Messages');
        
        // If no message is pending, we add a info message, since we want to
        // avoid an empty main window.
        if (!$this->_owApp->hasMessages()) {
            $this->_owApp->appendMessage(new OntoWiki_Message(
                'No messages', 
                OntoWiki_Message::INFO
            ));
        }
    }
    
    /**
     * This action displays the OntoWiki news feed.
     *
     * @return void
     */
    public function newsAction()
    {
        // We need no navigation here.
        OntoWiki_Navigation::disableNavigation();
        
        // Set the title of the main window.
        $this->view->placeholder('main.window.title')->set('News');
        
        try {
            $owFeed = $this->_importFeed();
            
            $this->view->feed        = $owFeed;
            $this->view->title       = $owFeed->title();
            $this->view->link        = $owFeed->link();
            $this->view->description = $owFeed->description();
        } catch (Exception $e) {
            $this->_owApp->appendMessage(new OntoWiki_Message('Newsfeed not available', OntoWiki_Message::WARNING));
            $this->view->feed = array();
        }
    }
    
    /**
     * This action shows a short summary of the OntoWiki news feed, which is
     * used e.g. by the dashboard.
     *
     * @return void
     */
    public function newsshortAction()
    {
        // We need no navigation here.
        OntoWiki_Navigation::disableNavigation();
        
        // Set the title of the main window.
        $this->view->placeholder('main.window.title')->set('News');
        
        try {
            $owFeed = $this->_importFeed();
            
            $rssData = array();
            foreach ($owFeed as $feedItem) {
                $description = substr($feedItem->description(), 0, strpos($feedItem->description(), ' ', 40)) . '[...]';
                
                $tempData = array(
                    'link'        => $feedItem->link(),
                    'title'       => $feedItem->title(),
                    'description' => $description
                );
                
                $rssData[] = $tempData;

                if(count($rssData) === 3) {
                    break;
                }
            }
            $this->view->rssData = $rssData;
        } catch (Exception $e) {
            $this->_owApp->appendMessage(new OntoWiki_Message('Newsfeed not available', OntoWiki_Message::WARNING));
            $this->view->rssData = array();
        }
    } 
    
    /**
     * Helper method that imports the OntoWiki news feed.
     * 
     * This method does not handle exceptions thrown by Zend_Feed.
     * 
     * @return Zend_Feed
     * 
     */
    private function _importFeed()
    {
        $version = $this->_config->version;
        
        $url = self::FEED_URL
             . (isset($version->label)  ? ('&client=' . $version->label) : '')
             . (isset($version->number) ? ('&version=' . $version->number) : '')
             . (isset($version->suffix) ? ('&suffix=' . $version->suffix) : '');
             
        return Zend_Feed::import($url);
    }
}
