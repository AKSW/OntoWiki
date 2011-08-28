<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki Processtidy view helper
 *
 * checks if php5-tidy is installed, and use it to clean unknown html content
 * TODO: this should be an extension
 *
 * @category OntoWiki
 * @package  OntoWiki_extensions_components_site
 */
class Site_View_Helper_Processtidy extends Zend_View_Helper_Abstract
{
    public function processtidy($string)
    {
        if (function_exists('tidy_repair_string'))
        {
            //*
            $string = tidy_repair_string($string,
                                      array('alt-text'=>'',
                                            'bare'=>true,
                                            'clean'=>true,
                                            'drop-empty-paras'=>true,
                                            'drop-font-tags'=>true,
                                            'drop-proprietary-attributes'=>true,
                                            'enclose-block-text'=>true,
                                            'enclose-text'=>true,
                                            'logical-emphasis'=>true,
                                            'word-2000'=>true,
                                            'show-body-only'=>true,
                                            'output-xhtml'=>true,
                                            'quote-ampersand'=>true
                                            ),
                                      'utf8');
            // */

            return $string;
            return substr($string, strpos($string, '<body>')+6, strpos($string, '</body>')-strpos($string, '<body>')-6);
        }
        else
        {
            return $string; // TODO: some basic fallbacks
        }
    }

}
