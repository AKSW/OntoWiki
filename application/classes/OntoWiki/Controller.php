<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2006-2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki controller base class.
 *
 * @category OntoWiki
 * @package  OntoWiki_Classes_Controller
 */
class OntoWiki_Controller extends Zend_Controller_Action
{
    /**
     * OntoWiki Application
     *
     * @var OntoWiki
     */
    protected $_owApp = null;

    /**
     * OntoWiki Application config
     *
     * @var Zend_Config
     */
    protected $_config = null;

    /**
     * Constructor
     */
    public function init()
    {
        parent::init();

        // init controller variables
        $this->_owApp  = OntoWiki::getInstance();
        $this->_config = $this->_owApp->config;
    }
}

