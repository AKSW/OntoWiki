<?php
/**
 * distributed semantic social network client
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_components_dssn
 * @copyright  Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class DssnHelper extends OntoWiki_Component_Helper {

    private $_me = null; 

    public function init() {
        parent::init();
    }

    public function getMe()
    {
        if (null === $this->_me) {
            $this->_me = new DSSN_Foaf_Person($this->_privateConfig->defaults->webId);
        } 
        return $this->_me;
    }

} 

