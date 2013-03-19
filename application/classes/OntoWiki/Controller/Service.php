<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2006-2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki controller base class for components.
 *
 * Provide component-specific path variables and Zend settings.
 *
 * @category  OntoWiki
 * @package   OntoWiki_Classes_Controller
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author    Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_Controller_Service extends Zend_Controller_Action
{
    /**
     * The component's file system root directory
     *
     * @var string
     */
    protected $_componentRoot = null;

    /**
     * The components URL base
     *
     * @var string
     */
    protected $_componentUrlBase = null;

    /**
     * The component private config
     *
     * @var array
     */
    protected $_privateConfig = null;

    protected $_owApp = null;
    protected $_config = null;

    /**
     * Constructor
     */
    public function init()
    {
        parent::init();

        $this->_owApp  = OntoWiki::getInstance();
        $this->_config = $this->_owApp->config;

        $cm   = $this->_owApp->extensionManager;
        $name = $this->_request->getControllerName();

        // set private config
        if ($pc = $cm->getPrivateConfig($name)) {
            $this->_privateConfig = $pc;
        }

        // set component root dir
        $this->_componentRoot = $cm->getExtensionPath()
            . $name
            . '/';

        // set component root url
        $this->_componentUrlBase = $this->_config->staticUrlBase
            . $this->_config->extensions->base
            . $name
            . '/';

        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
    }
}
