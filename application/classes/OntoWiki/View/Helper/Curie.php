<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki CURIE view helper
 *
 * Builds a CURIE conforming to {@link http://www.w3.org/TR/curie/} 
 * out of a given full URI.
 *
 * @category OntoWiki
 * @package OntoWiki_Classes_View_Helper
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_View_Helper_Curie extends Zend_View_Helper_Abstract
{
    public function curie($uri)
    {
        $curi = OntoWiki_Utils::compactUri($uri);
        
        return $curi;
    }
}
