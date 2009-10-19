<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @category ontowiki
 * @package view
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version $Id: Curie.php 4211 2009-10-01 15:39:39Z norman.heino $
 */

require_once 'Zend/View/Helper/Abstract.php';

/**
 * OntoWiki CURIE view helper
 *
 * Builds a CURIE conforming to {@link http://www.w3.org/TR/curie/} 
 * out of a given full URI.
 *
 * @category ontowiki
 * @package view
 * @copyright Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_View_Helper_Curie extends Zend_View_Helper_Abstract
{
    public function curie($uri)
    {
        require_once 'OntoWiki/Utils.php';
        $curi = OntoWiki_Utils::compactUri($uri);
        
        return $curi;
    }
}
