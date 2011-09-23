<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki module – Feeds
 *
 * presents a merged news feed of the current resource, which is based on
 * configured feed properties
 *
 * @category   OntoWiki
 * @package    extensions_modules_feeds
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

    /*
     * indicates that the module should be shown or not
     */
    public $shouldShow = false;

    public function init()
    {
        if (!isset(OntoWiki::getInstance()->selectedResource)) {
            return;
        }

        // get the CBD description
        $this->description = OntoWiki::getInstance()->selectedResource->getDescription();
        $this->description = $this->description[(string) OntoWiki::getInstance()->selectedResource];

        // look for configure feed properties
        if (isset($this->_privateConfig->properties)) {
            $properties = (array)$this->_privateConfig->properties->toArray();

            // ask for values for every feed property
            foreach ($properties as $key => $property) {
                if (isset($this->description[$property])) {
                    foreach ($this->description[$property] as $feedObject) {
                        // load the feed content
                        $this->_loadFeed($feedObject['value']);
                    }
                }
            }
        }

        // load feeds on relevant resources
        if (isset($this->_privateConfig->relevant)) {
            $this->_loadRelevantFeeds();
        }

        // sort entries according to time (taken from http://devzone.zend.com/article/3208)
        usort ($this->entries, array('FeedsModule', 'compareEntries'));

        // slice entries to show only X configured entries
        if (isset($this->_privateConfig->maxentries)) {
            $this->entries = array_slice($this->entries, 0, $this->_privateConfig->maxentries);
        }

        // turn visibility on if we have something to show
        if (count($this->entries) > 0) {
            $this->shouldShow = true;
        }
    }


    /**
     * Returns the content
     */
    public function getContents()
    {
        // provide content only if there is at least one entry (maybe double checked)
        if (count($this->entries) > 0) {
            // generate the template data
            $data = array('entries' => $this->entries);
            // render the content
            if (isset($this->_options->template)) {
                // use a custom template if given with the setOptions config
                $this->content = $this->render($this->_options->template, $data);
            } else {
                // use the default feeds template
                $this->content = $this->render('feeds', $data);
            }
            return $this->content;
        } else {
            return "";
        }
    }

    /*
     * the title of the module window
     */
    public function getTitle()
    {
        return 'Feeds';
    }

    /*
     * display the module only if there is content
     */
    public function shouldShow()
    {
        return $this->shouldShow;
    }

    /**
     * Loads feeds from configured relevant resources
     */
    private function _loadRelevantFeeds()
    {
        if ($this->_owApp->selectedModel && $this->_owApp->selectedResource) {
            $relevants  = is_string($this->_privateConfig->relevant)
                        ? (array)$this->_privateConfig->relevant
                        : $this->_privateConfig->relevant->toArray();
            $properties = is_string($this->_privateConfig->properties)
                        ? (array)$this->_privateConfig->properties
                        : $this->_privateConfig->properties->toArray();

            $relevantQuery = "
                SELECT DISTINCT ?f
                FROM <{$this->_owApp->selectedModel}>
                WHERE {
                    ?s <{$relevants[0]}> <{$this->_owApp->selectedResource}> .
                    ?s <{$properties[0]}> ?f .
                }";

            if ($result = $this->_erfurt->getStore()->sparqlQuery($relevantQuery)) {
                foreach ($result as $row) {
                    $this->_loadFeed($row['f']);
                }
            }
        }
    }

    /*
     * Loads a feed silently and use the OntoWiki cache dir for caching
     */
    private function _loadFeed($url) {
        // check then feed uri
        if (!Erfurt_Uri::check($url)) {
            return;
        }

        try {
            // get the ontowiki master config
            $config = OntoWiki::getInstance()->getConfig();

            // use the ontowiki cache directory if writeable
            // http://framework.zend.com/manual/en/zend.feed.reader.html#zend.feed.reader.cache-request.cache
            if ((isset($config->cache->path)) && (is_writable($config->cache->path))) {

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
                Zend_Feed_Reader::setCache(Zend_Cache::factory(
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


