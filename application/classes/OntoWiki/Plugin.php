<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki plugin base class.
 *
 * Serves as a base class for all OntoWiki plug-ins. An OntoWiki plug-in is a
 * class or object that meets the following requirements:
 * - it consists of a folder residing under OntoWiki's plug-in dir
 * - that folder contains a .php file with the same name
 * - that php file defines a class that is named like the file with the
 *   first letter in upper case and the suffix 'Plugin'
 * - the folder as well contains a 'plugin.ini' config file
 *
 * @category OntoWiki
 * @package  OntoWiki_Classes
 * @author   Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_Plugin extends Erfurt_Plugin
{
    /**
     * The plug-in's view for rendering templates
     * @var OntoWiki_View
     */
    public $view = null;

    /**
     * The plug-in URL base
     * @var string
     */
    protected $_pluginUrlBase = null;

    /**
     * Constructor
     */
    public function __construct($root, $config = null)
    {
        // init view
        if (null === $this->view) {
            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
            if (null === $viewRenderer->view) {
                $viewRenderer->initView();
            }
            $this->view = clone $viewRenderer->view;
            $this->view->clearVars();
        }

        $this->_pluginUrlBase = OntoWiki::getInstance()->getStaticUrlBase()
                              . str_replace(ONTOWIKI_ROOT, '', $root);

        parent::__construct($root, $config);
    }
}


