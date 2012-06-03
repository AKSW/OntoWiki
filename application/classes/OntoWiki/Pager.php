<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki pager class.
 *
 * @category OntoWiki
 * @package OntoWiki_Classes
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_Pager
{   
    /** @var string */
    public static $firstHtml = '&laquo;&nbsp;';
    
    /** @var string */
    public static $prevHtml  = '&lsaquo;&nbsp;';
    
    /** @var string */
    public static $nextHtml  = '&nbsp;&rsaquo;';
    
    /** @var string */
    public static $lastHtml  = '&nbsp;&raquo;';
    
    /** @var array */
    protected static $_options = array(
        'default_limit'   => 10, 
        'show_first_last' => true, 
        'max_page_links'  => 5, 
        'page_param'      => 'p'
    );
    
    /** @var OntoWiki_Url */
    protected static $_url = null;
    
    /**
     * Sets pager options statically.
     *
     * @param array $options
     */
    public static function setOptions(array $options) {
        self::$_options = array_merge(self::$_options,$options);
    }
    
    /**
     * Returns pagination links for the current URL with $count and $limit items.
     *
     * @param $count the total number of items
     * @param $limit the number of items per page
     */
    public static function get($count, $limit = null, $itemsOnPage = null, $page = null, $listName = null, $otherParams = array())
    {
        if (null != $limit) {
            self::$_options['default_limit'] = $limit;
        }
        
        if (Erfurt_Store::COUNT_NOT_SUPPORTED == $count) {
            self::$_options['show_first_last'] = false;
            //self::$_options['max_page_links']  = 0;
        }
        
        // get URL with params p (page number) and limit (not used atm)
        $paramsToKeep = array_merge($otherParams, array('p', 'limit', 'r', 'm'));
        self::$_url = new OntoWiki_Url(array(), $paramsToKeep);
        self::$_url->setParam("list", $listName);
        
        $limit = isset(self::$_url->limit)
               ? self::$_url->limit 
               : self::$_options['default_limit'];

        if($limit == 0){
            // means no limit
            // no pager needed
            return "";
        }
        
        $page = isset(self::$_url->{self::$_options['page_param']})
              ? self::$_url->{self::$_options['page_param']}
              : ($page != null ? $page : 1);
        
        // self::$_url->limit = $limit;
        $pagerLinks = array();
        
        // translation helper
        $translate = OntoWiki::getInstance()->translate;
        
        // pagination necessary
        if (($count > $limit) || ($count == Erfurt_Store::COUNT_NOT_SUPPORTED)) {
            // previous page exists
            if ($page > 1) {
                if (self::$_options['show_first_last']) {
                    self::$_url->{self::$_options['page_param']} = 1;
                    $pagerLinks[] = sprintf('<a class="minibutton" href="%s">%s</a>', self::$_url, self::$firstHtml . $translate->_('First'));
                }

                self::$_url->{self::$_options['page_param']} = $page - 1;
                $pagerLinks[] = sprintf('<a class="minibutton" href="%s">%s</a>', self::$_url, self::$prevHtml . $translate->_('Previous'));
            } else {
                if (self::$_options['show_first_last']) {
                    $pagerLinks[] = sprintf('<a class="disabled minibutton">%s</a>', self::$firstHtml . $translate->_('First'));
                }
                $pagerLinks[] = sprintf('<a class="disabled minibutton">%s</a>', self::$prevHtml . $translate->_('Previous'));
            }
            
            // individual page links
            if ($count != null) {
                if (self::$_options['show_first_last']) {
                    $maxLinksAsym = floor(self::$_options['max_page_links'] / 2);
                    $offset = 0;
                } else {
                    // first and last links are disabled, so always show first and last individual page
                    $maxLinksAsym = floor((self::$_options['max_page_links'] - 2) / 2);
                    self::$_url->{self::$_options['page_param']} = 1;
                    if ($page == 1) {
                        $pagerLinks[] = '<a class="selected minibutton">1</a>';
                    } else {
                        $pagerLinks[] = '<a class="minibutton" href="' . self::$_url . '">1</a>';
                    }
                    $offset = 1;

                    // if there is a gap, show dots
                    if (($page - $maxLinksAsym) > 2) {
                        $pagerLinks[] = '&hellip;';
                    }
                }

                if ($count === Erfurt_Store::COUNT_NOT_SUPPORTED) {
                    for ($i=max(1+$offset, $page-$maxLinksAsym); $i <= $page; ++$i) {
                        self::$_url->{self::$_options['page_param']} = $i;
                        if ($page == $i) {
                            $pagerLinks[] = '<a class="selected minibutton">' . $i . '</a>';
                        } else {
                            $pagerLinks[] = '<a class="minibutton" href="' . self::$_url . '">' . $i . '</a>';
                        }
                    }
                } else {
                    for ($i = max(1 + $offset, $page - $maxLinksAsym); $i <= min(ceil($count / $limit) - $offset, $page + $maxLinksAsym); ++$i) {
                        self::$_url->{self::$_options['page_param']} = $i;
                        
                        if ($page == $i) {
                            $pagerLinks[] = '<a class="selected minibutton">' . $i . '</a>';
                        } else {
                            $pagerLinks[] = '<a class="minibutton" href="' . self::$_url . '">' . $i . '</a>';
                        }
                    }
                }

                if (!self::$_options['show_first_last'] && ($count !== Erfurt_Store::COUNT_NOT_SUPPORTED)) {
                    self::$_url->{self::$_options['page_param']} = (int) ceil($count / $limit);
                    if ((self::$_url->{self::$_options['page_param']} - $page) > 2) {
                        $pagerLinks[] = '&hellip;';
                    }
                    if ($page == self::$_url->{self::$_options['page_param']}) {
                        $pagerLinks[] = '<a class="selected minibutton">' . self::$_url->{self::$_options['page_param']} . '</a>';
                    } else {
                        $pagerLinks[] = '<a class="minibutton" href="' . self::$_url . '">' . self::$_url->{self::$_options['page_param']} . '</a>';
                    }
                }
            }

            // next page exists
            if (($count > $page * $limit) || ($count == Erfurt_Store::COUNT_NOT_SUPPORTED && $itemsOnPage === $limit)) {
                self::$_url->{self::$_options['page_param']} = $page + 1;
                $pagerLinks[] = sprintf('<a class="minibutton" href="%s">%s</a>', self::$_url, $translate->_('Next') . self::$nextHtml);
                
                if (self::$_options['show_first_last']) {
                    self::$_url->{self::$_options['page_param']} = (int) ceil($count / $limit);
                    $pagerLinks[] = sprintf('<a class="minibutton" href="%s">%s</a>', self::$_url, $translate->_('Last') . self::$lastHtml);
                }
            } else {
                $pagerLinks[] = sprintf('<a class="disabled minibutton">%s</a>', $translate->_('Next') . self::$nextHtml);
                if (self::$_options['show_first_last']) {
                    $pagerLinks[] = sprintf('<a class="disabled minibutton">%s</a>', $translate->_('Last') . self::$lastHtml);
                }
            }
            
            $ret = '<ul>';
            foreach ($pagerLinks as $link) {
                $ret .= '<li>' . $link . '</li>';
            }
            $ret .= '</ul>';
            
            
            return $ret;
        }
    }
}


