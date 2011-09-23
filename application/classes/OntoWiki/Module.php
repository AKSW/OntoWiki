<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki module base class.
 *
 * Serves as a base class for all OntoWiki modules.
 *
 * @category OntoWiki
 * @package  Module
 * @author Norman Heino <norman.heino@gmail.com>
 */
abstract class OntoWiki_Module
{
    /**
     * Default value for caching enabled
     * @var boolean
     */
    const MODULE_CACHING_DEFAULT = true;

    /**
     * OntoWiki Application config
     * @var Zend_Config
     */
    protected $_config = null;

    /**
     * The current module context thats loaded this module
     */
    protected $_context = null;

    /**
     * Erfurt framework entry
     * @var Erfurt_App
     */
    protected $_erfurt = null;

    /**
     * Currently selected language
     * @var string
     */
    protected $_lang = null;

    /**
     * The module name
     * @var string
     */
    protected $_name = null;

    /**
     * OntoWiki Application object
     * @var OntoWiki
     */
    protected $_owApp = null;

    /**
     * The module private config ([private] section from module.ini file)
     * @var Zend_Config
     */
    public $_privateConfig = null;

    /**
     * The module runtime options from the view's module method's second
     * parameter (merged with default module options)
     * injected with setOptions from the view
     * @var Zend_Config
     */
    public $_options = null;

    /**
     * The current request object
     * @var Zend_Controller_Request_Abstract
     */
    protected $_request = null;

    /**
     * Erfurt store tab
     * @var Erfurt_Store
     */
    protected $_store = null;

    /**
     * File extension for template files
     * @var string
     */
    protected $_templateSuffix = 'phtml';

    /**
     * The module view
     * @var Zend_View_Interface
     */
    public $view = null;

    /**
     * Constructor
     */
    public function __construct($name, $context, $config)
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

        $this->_templateSuffix = '.' . ltrim($this->_templateSuffix, '.');

        $this->_owApp   = OntoWiki::getInstance();
        $this->_erfurt  = $this->_owApp->erfurt;
        $this->_store   = $this->_erfurt->getStore();
        $this->_config  = $this->_owApp->config;
        $this->_lang    = $this->_config->languages->locale;
        $this->_request = Zend_Controller_Front::getInstance()->getRequest();

        $this->_name = $name;

        // set important script variables
        $this->view->themeUrlBase = $this->_config->themeUrlBase;
        $this->view->urlBase      = $this->_config->urlBase;
        $this->view->moduleUrl    = $this->_config->staticUrlBase
                                  . $this->_config->extensions->base
                                  . $config->extensionName . '/';

        // set the config
        $this->_privateConfig = $config->private;

        // set the context
        $this->setContext($context);

        // allow custom module initialization
        $this->init();
    }

    /**
     * Returns the current context or the default context if none has been set.
     *
     * @return string
     */
    public function getContext()
    {
        if (null != $this->_context) {
            return $this->_context;
        }

        return OntoWiki_Module_Registry::DEFAULT_CONTEXT;
    }

    /**
     * Renders the module content with the module template.
     *
     * @return string
     */
    public function render($template, $vars = array(), $spec = null)
    {
        $template = $template
                  . $this->_templateSuffix;

        if (null === $spec) {
            $this->view->assign($vars);
        } else {
            $this->view->assign($spec, $vars);
        }

        return $this->view->render($template);
    }

    /**
     * Sets the current context so the module can perform different actions
     * depending on the context.
     *
     * @param string $context
     */
    public function setContext($context)
    {
        $this->_context = $context
                        ? (string)$context
                        : OntoWiki_Module_Registry::DEFAULT_CONTEXT;
    }

    /**
     * Returns the rendered module.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getContents();
    }

    /**
     * Returns whether the module wants its content to be cached.
     *
     * The base clase implementation returns the value set in the module.ini
     * config file or the default value if not set.
     *
     * @return boolean
     */
    public function allowCaching()
    {
        if (isset($this->caching)) {
            return (boolean)$this->caching;
        }

        // return default
        return self::MODULE_CACHING_DEFAULT;
    }

    /**
     * Returns wheter the module should be displayd in the current
     * application state.
     *
     * @return boolean
     */
    public function shouldShow()
    {
        return true;
    }

    /**
     * Returns a string is unique to the module's state and can be used for
     * cache identification.
     *
     * @return string
     */
    public function getCacheId() {
        $id = $this->_config->host
            . $this->_name
            . $this->getStateId();

        return $id;
    }

    /**
     * Returns the number of seconds after which this module's content
     * cache should be renewed
     *
     * @return int
     */
    public function getCacheLivetime()
    {
        return 600;
    }

    /**
     * Returns an OntoWiki_Message object that should be displayed on top of all
     * module content.
     *
     * @return OntoWiki_Message|null
     */
    public function getMessage()
    {
        return null;
    }

    /**
     * Returns a string that contains the string representation
     * of all variable this module's state (content) depends on.
     *
     * @return string
     */
    public function getStateId()
    {
    }


    /**
     * Allows for custom module initialization
     */
    public function init()
    {
    }

    /**
     * Returns the contents this module provides.
     *
     * Only provide the real content. About surrounding markup
     * is taken care by OntoWiki in order to provide consistent
     * look & feel.
     *
     * If you want to provide tabs in your module window, return
     * an array whose keys are translatable names of the tabs and
     * will be used as anchor ids in HTML code.
     *
     * @return string|array
     */
    public function getContents()
    {
    }

    /**
     * Returns the title of the module
     *
     * If no title is provided, the title is used from the module config.
     *
     * @return string
     */
    public function getTitle()
    {
        if (isset($this->title)) {
            return $this->title;
        }
    }

    /*
     * setter method for options
     */
    public function setOptions(Zend_Config $options = null)
    {
        if ($options) {
            $this->_options = $options;
        }
    }
}


