<?php

/**
 * OntoWiki module – Feeds
 *
 * presents a merged news feed of the current resource, which is based on
 * configured feed properties
 *
 * @category   OntoWiki
 * @package    extensions_modules_feeds
 * @copyright  Copyright (c) 2010, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class FeedsModule extends OntoWiki_Module
{
    /*
     * The CBD of the selected resource
     */
    protected $description = null;

    /*
     * at the end, this should be an merged and sorted array of entry arrays ...
     */
    protected $entries = array();

    /*
     * The rendered content of the module
     */
    protected $content = null;


    public function init() {
        if (!isset(OntoWiki::getInstance()->selectedResource)) {
            return;
        }

        // get the CBD description
        $this->description = OntoWiki::getInstance()->selectedResource->getDescription();
        $this->description = $this->description[(string) OntoWiki::getInstance()->selectedResource];

        // look for configure feed properties
        if (isset($this->_privateConfig->properties)) {
            $properties = $this->_privateConfig->properties;
            if (is_string($properties)) {
                $property = $properties;
                $properties = array();
                $properties[] = $property;
            }
            // ask for values for every feed property
            foreach ($properties as $key => $property) {
                if (isset($this->description[$property])) {
                    foreach ($this->description[$property] as $feedObject) {
                        // load the feed content
                        $this->loadFeed($feedObject['value']);
                    }
                }
            }

        }

        // sort entries according to time (taken from http://devzone.zend.com/article/3208)
        usort ($this->entries, array('FeedsModule', 'compareEntries'));

        // slice entries to show only X configured entries
        if (isset($this->_privateConfig->maxentries)) {
            $this->entries = array_slice($this->entries, 0, $this->_privateConfig->maxentries);
        }

        // provide content only if there is at least one entry
        if (count($this->entries) > 0) {
            $data = array('entries' => $this->entries);
            $this->content = $this->render('feeds', $data);
        }
    }


    /**
     * Returns the content
     */
    public function getContents() {

        // scripts and css only if module is visible
        #$this->view->headScript()->appendFile($this->view->moduleUrl . 'feeds.js');
        #$this->view->headLink()->appendStylesheet($this->view->moduleUrl . 'feeds.css');
        
        if ($this->content != null) {
            return $this->content;
        } else {
            return "";
        }

    }

    /*
     * display the module only if there is content
     */
    public function shouldShow() {
        if ($this->content != null) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * Loads a feed silently and use the OntoWiki cache dir for caching
     */
    private function loadFeed ($url) {
        // check then feed uri
        if (!Erfurt_Uri::check($url)) {
            return;
        }

        try {
            // get the ontowiki master config
            $config = OntoWiki::getInstance()->getConfig();

            // use the ontowiki cache directory if writeable
            // http://framework.zend.com/manual/en/zend.feed.reader.html#zend.feed.reader.cache-request.cache
            if ((isset($config->cache->path)) && (is_writable($config->cache->path)) ) {
                
                if (isset($this->_privateConfig->livetime)) {
                    $livetime = $this->_privateConfig->livetime;
                } else {
                    $livetime = 86400; // default livetime is one day
                }

                // prepare and assign the cache
                $cacheFrontendOptions = array(
                    'lifetime' => $livetime,
                    'automatic_serialization' => true
                );
                $cacheBackendOptions = array('cache_dir' => $config->cache->path);
                Zend_Feed_Reader::setCache( Zend_Cache::factory(
                    'Core', 'File', $cacheFrontendOptions, $cacheBackendOptions
                ));

                // this uses 304 http codes to speed up retrieval
                Zend_Feed_Reader::useHttpConditionalGet();
            }

            // try to load the feed from uri
            $feed = Zend_Feed_Reader::import($url);
            
        } catch (Exception $e) {
            // feed import failed
            return;
        }

        // collect entries of the feed
        foreach ($feed as $entry) {
            $newEntry = array (
                'title'        => $entry->getTitle(),
                'description'  => $entry->getDescription(),
                'dateModified' => $entry->getDateModified(),
                'authors'      => $entry->getAuthors(),
                'link'         => $entry->getLink(),
                'content'      => $entry->getContent()
            );
            $this->entries[$entry->getLink()] = $newEntry;
        }

        return;
    }


    /*
     * compare two entries according to time
     * taken from http://devzone.zend.com/article/3208
     */
    static function compareEntries ($a , $b) {
        $a_time = $a['dateModified'];
        $b_time = $b['dateModified'];

        if ($a_time == $b_time) {
            return 0;
        }
        return ($a_time > $b_time) ? -1 : 1;
    }

}


