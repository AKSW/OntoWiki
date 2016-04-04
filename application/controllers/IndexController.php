<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2006-2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki index controller.
 *
 * @category   OntoWiki
 * @package    OntoWiki_Controller
 * @author     Norman Heino <norman.heino@gmail.com>
 */
class IndexController extends OntoWiki_Controller_Base
{

    /**
     * Timeout for reading the OntoWiki RSS news feed.
     */
    const NEWS_FEED_TIMEOUT_IN_SECONDS = 3;

    /**
     * Displays the OntoWiki news feed short summary (dashboard part)
     */
    public function newsshortAction()
    {
        // requires zend Feed module
        // number of news
        $feedCount = 3;
        $owFeed = $this->getNews();

        // create new array for data
        $data = array();
        // parse feed items into array
        foreach ($owFeed as $feedItem) {
            // form temporary array with needed data
            $tempdata = array(
                'link'        => $feedItem->link(),
                'title'       => $feedItem->title(),
                'description' =>
                substr($feedItem->description(), 0, strpos($feedItem->description(), ' ', 40)) . ' [...]'
            );
            // append temporary array to data array
            $data[] = $tempdata;

            // take only needed items
            if (count($data) == $feedCount) {
                break;
            }
        }
        // assign data array to view rss data variable
        $this->view->rssData = $data;
    }

    /**
     * Displays messages only without any other content.
     */
    public function messagesAction()
    {
        OntoWiki::getInstance()->getNavigation()->disableNavigation();
        $this->view->placeholder('main.window.title')->set('OntoWiki Messages');
        $this->_helper->viewRenderer->setNoRender();
    }

    /**
     * Displays the OntoWiki news feed
     */
    public function newsAction()
    {
        $this->view->placeholder('main.window.title')->set('News');

        $owFeed = $this->getNews();
        $this->view->feed = $owFeed;
        if ($owFeed instanceof Zend_Feed_Abstract) {
            $this->view->title       = $owFeed->title();
            $this->view->link        = $owFeed->link();
            $this->view->description = $owFeed->description();
        }
        OntoWiki::getInstance()->getNavigation()->disableNavigation();
    }

    /**
     * Default action if called action wasn't found
     */
    public function __call($action, $params)
    {
        OntoWiki::getInstance()->getNavigation()->disableNavigation();
        $this->view->placeholder('main.window.title')->set('Welcome to OntoWiki');

        if ($this->_owApp->hasMessages()) {
            $this->_forward('messages', 'index');
        } else {
            if (!isset($this->_config->index->default->controller)
                || !isset($this->_config->index->default->action)
            ) {
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
        OntoWiki::getInstance()->getNavigation()->disableNavigation();
        // sorry for this hack, but I dont wanted to modify the the main layout too much ...
        $this->view->placeholder('main.window.additionalclasses')->set('hidden');
    }

    /**
     * Reads OntoWiki news from the AKSW RSS feed.
     *
     * @return array|Zend_Feed_Abstract
     */
    public function getNews()
    {
        // get current version
        $version = $this->_config->version;
        // try reading
        try {
            if (isset($this->_config->news) && isset($this->_config->news->feedUrl)) {
                $url = $this->_config->news->feedUrl;
            } else {
                $url = 'http://blog.aksw.org/feed/?cat=5&client='
                    . urlencode($version->label)
                    . '&version='
                    . urlencode($version->number)
                    . '&suffix='
                    . urlencode($version->suffix);
            }
            if ($url) {
                $url = strtr($url, array(
                    '{{version.label}}'  => urlencode($version->label),
                    '{{version.number}}' => urlencode($version->number),
                    '{{version.suffix}}' => urlencode($version->suffix)
                ));

                /* @var $client Zend_Http_Client */
                $client = Zend_Feed::getHttpClient();
                $client->setConfig(array('timeout' => self::NEWS_FEED_TIMEOUT_IN_SECONDS));
                $owFeed = Zend_Feed::import($url);
                return $owFeed;
            } else {
                $this->_owApp->appendMessage(
                    new OntoWiki_Message('Feed disabled in config.ini. You can configure a feed using the "news.feedUrl" key in your config.ini.', OntoWiki_Message::INFO)
                );
                return array();
            }
        } catch (Exception $e) {
            $this->_owApp->appendMessage(
                new OntoWiki_Message('Error loading feed: ' . $url, OntoWiki_Message::WARNING)
            );
            return array();
        }
    }
}
