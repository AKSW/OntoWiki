<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once 'OntoWiki/Component/Helper.php';

/**
 * A helper class for the datagathering component.
 *
 * @category   OntoWiki
 * @package    Extensions_Datagathering
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author     Philipp Frischmuth <pfrischmuth@googlemail.com>
 */
class DatagatheringHelper extends OntoWiki_Component_Helper
{
    public function init()
    {
        $pathBase = $this->_owApp->extensionManager->getComponentUrl('datagathering');
        $this->_owApp->view->headScript()->appendFile($pathBase.'scripts/jquery.autocomplete.js');
        $this->_owApp->view->headScript()->appendFile($pathBase.'datagathering.js');

        $this->_owApp->view->headLink()->appendStylesheet($pathBase.'css/jquery.autocomplete.css');
    }
}
