<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki Date view helper
 *
 * Renders a data string according to a format string
 *
 * @category OntoWiki
 * @package  OntoWiki_extensions_components_site
 */
class Site_View_Helper_Date extends Zend_View_Helper_Abstract
{
    public function date($dateString, $formatString = 'j F Y')
    {
        $date = new DateTime($dateString);
        return $date->format($formatString);
    }
}
