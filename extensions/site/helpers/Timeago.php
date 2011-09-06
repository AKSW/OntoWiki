<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki timeago view helper
 *
 * returns a Javscript based timeage abbrv tag
 * this helper is usable as {{timeago ...}} markup in combination with
 * ExecuteHelperMarkup
 *
 * @link http://timeago.yarp.com/
 * @category OntoWiki
 * @package  OntoWiki_extensions_components_site
 */
class Site_View_Helper_Timeago extends Zend_View_Helper_Abstract
{
    /*
     * current view, injected with setView from Zend
     */
    public $view;

    /*
     * the main toc method, mentioned parameters are:
     * - tag (abbr)
     * - time (null) - time string in ISO8601 format
     */
    public function timeago($options = array())
    {
        // check for options and assign local vars or default
        $tag   = (isset($options['tag']))   ? $options['tag']   : 'abbr';
        $time  = (isset($options['time']))  ? $options['time']  : null;

        $view   = $this->view;
        $script = OntoWiki::getInstance()->getUrlBase() . 'extensions/site/js/jquery.timeago.js';

        $return  = PHP_EOL;
        $return .= '<script type="text/javascript" src="'. $script .'"></script>' . PHP_EOL;
        $return .= '<script type="text/javascript" charset="utf-8">
    $(document).ready(function(){
        $("'.$tag.'.timeago").timeago();
    })
</script>' . PHP_EOL;
        $return .= '<'.$tag.' class="timeago" title="'.$time.'">'.$time.'</'.$tag.'>';
        return $return;
    }

    /*
     * view setter (dev zone article: http://devzone.zend.com/article/3412)
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }
}
