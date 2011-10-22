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
 * presents a merged news feed of the current resource which is based on
 * configured feed properties
 *
 * @category   OntoWiki
 * @package    extensions_modules_feeds
 */
class FeedsModule extends OntoWiki_Module
{
    /*
     * the selected resource
     */
    private $_selectedResource = null;

    /*
     * The memory model of the selected resource
     */
    private $_description = null;

    /*
     * the array of found feed URLs
     */
    private $_feeds = array();

    /*
     * at the end, this should be an merged and sorted array of entry arrays ...
     */
    private $_entries = array();

    /*
     * The rendered content of the module
     */
    private $_content = null;

    /*
     * create memmodel, read config properties and find feed URLs
     */
    public function init()
    {
        // do we have a selected resource?
        if (!isset(OntoWiki::getInstance()->selectedResource)) {
            // do not show and do not fetch anything
            return;
        } else {
            $this->_selectedResource = OntoWiki::getInstance()->selectedResource;
        }

        // create the memory model
        $this->_description = $this->_selectedResource->getMemoryModel();

        // look for configure feed properties
        if (isset($this->_privateConfig->properties)) {
            $properties = (array) $this->_privateConfig->properties->toArray();

            // fetch values for every feed property
            foreach ($properties as $key => $property) {
                $subject = (string) $this->_selectedResource;
                $feeds   = $this->_description->getValues($subject, $property);
                // check values for URL-ness
                foreach ($feeds as $key => $feedObject) {
                    $this->_addFeed($feedObject['value']);
                }
            }
        }

        // add feeds on relevant resources
        if (isset($this->_privateConfig->relevant)) {
            $this->_addRelevantFeeds();
        }

    }


    /**
     * Returns the content
     */
    public function getContents()
    {
        // finally try to load the feeds
        foreach ($this->_feeds as $feed) {
            $this->_loadFeed($feed);
        }

        // provide content only if there is at least one entry (maybe double checked)
        if (count($this->_entries) > 0) {
            // sort entries according to time
            // taken from http://devzone.zend.com/article/3208
            usort($this->_entries, array('FeedsModule', 'compareEntries'));

            // slice entries to show only X configured entries
            if (isset($this->_privateConfig->maxentries)) {
                $this->_entries = array_slice(
                    $this->_entries,
                    0,
                    $this->_privateConfig->maxentries
                );
            }

            // generate the template data
            $data = array('entries' => $this->_entries);

            // render the content
            if (isset($this->_options->template)) {
                // use a custom template if given with the setOptions config
                $this->_content = $this->render($this->_options->template, $data);
            } else {
                // use the default feeds template
                $this->_content = $this->render('feeds', $data);
            }
            return $this->_content;
        } else {
            return '<div class="message info">No recent news available.</div>';
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
        // add a fallback feed URL if available and needed
        $this->_addFallbackFeed();

        // turn visibility on if we have some feed to show
        if (count($this->_feeds) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * add a fallback feed URL if available and needed
     */
    private function _addFallbackFeed()
    {
        // add fallback feed if needed (runtime option overwrites config)
        if (count($this->_feeds) == 0) {
            $fallback = null;
            // check for configured fallback
            if (isset($this->_privateConfig->fallbackFeed)) {
                $fallback = $this->_privateConfig->fallbackFeed;
            }
            // check for runtime option
            if (isset($this->_options->fallbackFeed)) {
                $fallback = $this->_options->fallbackFeed;
            }
            if ($fallback != null) {
                $this->_addFeed($fallback);
            }
        }
    }

    /**
     * add feeds from configured relevant resources
     */
    private function _addRelevantFeeds()
    {
        if ($this->_owApp->selectedModel && $this->_owApp->selectedResource) {
            $relevants  = is_string($this->_privateConfig->relevant)
                        ? (array) $this->_privateConfig->relevant
                        : $this->_privateConfig->relevant->toArray();
            $properties = is_string($this->_privateConfig->properties)
                        ? (array) $this->_privateConfig->properties
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
                    $this->_addFeed($row['f']);
                }
            }
        }
    }


    /*
     * add a feed url string to the feed array (after check)
     */
    private function _addFeed($url)
    {
        if (Erfurt_Uri::check($url)) {
            // add valid feeds to our good-list
            $this->_feeds[] = $url;
        }
    }

    /*
     * Loads a feed silently into _entries
     * (uses the OntoWiki cache dir for caching)
     */
    private function _loadFeed($url)
    {
        try {
            // get the ontowiki master config
            $config = OntoWiki::getInstance()->getConfig();

            // this uses 304 http codes to speed up retrieval
            Zend_Feed_Reader::useHttpConditionalGet();

            // use the ontowiki cache directory if writeable
            // http://framework.zend.com/manual/en/zend.feed.reader.html#zend.feed.reader.cache-request.cache
            if ((isset($config->cache->path)) && (is_writable($config->cache->path))) {
                // set livetime for cached XML documents
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
                $cache = Zend_Cache::factory(
                    'Core', 'File', $cacheFrontendOptions, $cacheBackendOptions
                );
                Zend_Feed_Reader::setCache($cache);

                // look for cached feed to avoid unneeded traffic
                $cacheKey  = 'Zend_Feed_Reader_' . md5($url);
                $cachedXml = $cache->load($cacheKey);
            }

            if ($cachedXml != false) {
                // use the cached XML
                $feed = Zend_Feed_Reader::importString($cachedXml);
            } else {
                // try to load the feed from uri whithout cache support
                $feed = Zend_Feed_Reader::import($url);
            }

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
            $this->_entries[$entry->getLink()] = $newEntry;
        }

        return;
    }


    /*
     * compare two entries according to time
     * taken from http://devzone.zend.com/article/3208
     */
    static function compareEntries($a , $b)
    {
        $aTime = $a['dateModified'];
        $btime = $b['dateModified'];

        if ($aTime == $bTime) {
            return 0;
        }
        return ($aTime > $bTime) ? -1 : 1;
    }

}


