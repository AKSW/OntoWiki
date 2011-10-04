<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki Table of Contents view helper
 *
 * returns a Javscript based table of contents
 * this helper is usable as {{toc ...}} markup in combination with
 * ExecuteHelperMarkup
 *
 * @link https://github.com/dcneiner/TableOfContents
 * @category OntoWiki
 * @package  OntoWiki_extensions_components_site
 */
class Site_View_Helper_Toc extends Zend_View_Helper_Abstract implements Site_View_Helper_MarkupInterface
{
    /*
     * current view, injected with setView from Zend
     */
    public $view;

    /*
     * the main toc method, mentioned parameters are:
     * - tag (ol)
     * - startlevel (1)
     * - depth (2)
     *
     * @link https://github.com/dcneiner/TableOfContents
     */
    public function toc($options = array())
    {
        // check for options and assign local vars or default
        $startlevel = (isset($options['startlevel'])) ? $options['startlevel'] : '1';
        $depth      = (isset($options['depth']))      ? $options['depth']      : '2';
        $tag        = (isset($options['tag']))        ? $options['tag']        : 'ul';

        $view        = $this->view;
        $store       = OntoWiki::getInstance()->erfurt->getStore();
        $model       = OntoWiki::getInstance()->selectedModel;
        $titleHelper = new OntoWiki_Model_TitleHelper($model);
        $script      = OntoWiki::getInstance()->getUrlBase() . 'extensions/site/js/jquery.tableofcontents.js';

        $return  = PHP_EOL;
        $return .= '<script type="text/javascript" src="'. $script .'"></script>' . PHP_EOL;
        $return .= '<script type="text/javascript" charset="utf-8">
    $(document).ready(function(){
        $.TableOfContents.defaultOptions.startLevel = "'.$startlevel.'";
        $.TableOfContents.defaultOptions.depth      = "'.$depth.'";
        $("#toc").tableOfContents($("div.content[property=\'site:content\']"));
    })
</script>' . PHP_EOL;
        $return .= '<details><summary>Table of Contents</summary>';
        $return .= '<'.$tag.' id="toc"></'.$tag.'>';
        $return .= '</details>';
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
