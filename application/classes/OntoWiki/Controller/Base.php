<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki controller base class.
 *
 * @category OntoWiki
 * @package  OntoWiki_Classes_Controller
 * @author Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_Controller_Base extends Zend_Controller_Action
{
    /**
     * OntoWiki Application
     * @var OntoWiki
     */
    protected $_owApp = null;

    /**
     * OntoWiki Application config
     * @var Zend_Config
     */
    protected $_config = null;

    /**
     * The session store
     * @var Zend_Session
     */
    protected $_session = null;

    /**
     * Erfurt App
     * @var Erfurt_App
     */
    protected $_erfurt = null;

    /**
     * The Erfurt event dispatcher
     * @var Erfurt_Event_Dispatcher
     */
    protected $_eventDispatcher = null;

    /**
     * Time before the conroller is launched
     * @var float
     */
    private $_preController;

    /**
     * Constructor
     */
    public function init()
    {
        /**
         * @trigger onBeforeInitController
         * Triggered before a controller of class OntoWiki_Controller_Base (or derived)
         * is initialized.
         */
        $event = new Erfurt_Event('onBeforeInitController');
        $eventResult = $event->trigger();

        // init controller variables
        $this->_owApp            = OntoWiki::getInstance();
        $this->_config           = $this->_owApp->config;
        $this->_session          = $this->_owApp->session;
        $this->_erfurt           = $this->_owApp->erfurt;
        $this->_eventDispatcher  = Erfurt_Event_Dispatcher::getInstance();

        // set important script variables
        $this->view->themeUrlBase   = $this->_config->themeUrlBase;
        $this->view->urlBase        = $this->_config->urlBase;
        $this->view->staticUrlBase  = $this->_config->staticUrlBase;
        $this->view->libraryUrlBase = $this->_config->staticUrlBase . 'libraries/';

        $graph = $this->_owApp->selectedModel;
        if ($graph instanceof Erfurt_Rdf_Model) {
            if ($graph->isEditable()) {
                $this->view->placeholder('update')->set(
                    array(
                        'defaultGraph'   => $graph->getModelIri(),
                        'queryEndpoint'  => $this->_config->urlBase . 'sparql/',
                        'updateEndpoint' => $this->_config->urlBase . 'update/'
                    )
                );
            }
        }
        
        // check config for additional styles
        if ($styles_extra = $this->_config->themes->styles) {
            $this->view->themeExtraStyles = $styles_extra->toArray();
        }
        else {
            $this->view->themeExtraStyles = array();
        }

        // disable layout for Ajax requests
        if ($this->_request->isXmlHttpRequest()) {
            $this->_helper->layout()->disableLayout();
        }

        // initialize view helpers
        $this->view->headTitle($this->_config->title->prefix, 'SET');
        $this->view->headTitle()->setSeparator($this->_config->title->separator);
        $this->view->headMeta()->setHttpEquiv('Content-Type', 'text/html; charset=' . $this->_config->encoding);
        $this->view->headMeta()->setName('generator', 'OntoWiki — Collaborative Knowledge Engineering');

        // RDFauthor view configuration
        $viewMode = isset($this->_config->rdfauthor->viewmode)
                  ? $this->_config->rdfauthor->viewmode
                  : 'inline';

        // inject JSON variables into view
        $this->view->jsonVars = '
            var urlBase = "' . $this->_config->urlBase . '";
            var themeUrlBase = "' . $this->_config->themeUrlBase . '";
            var _OWSESSION = "' . _OWSESSION . '";
            var RDFAUTHOR_BASE = "' . $this->_config->staticUrlBase . 'libraries/RDFauthor/";
            var RDFAUTHOR_VIEW_MODE = "' . $viewMode . '";' . PHP_EOL;

        if (defined('_OWDEBUG')) {
            $this->view->jsonVars .= '            var RDFAUTHOR_DEBUG = 1;';
        }

        if ($this->_owApp->selectedModel) {
            $this->view->jsonVars .= '
            var selectedGraph = {
                URI: "' . (string)$this->_owApp->selectedModel . '",
                title: ' . json_encode((string)$this->_owApp->selectedModel->getTitle()) . ',
                editable: ' . ($this->_owApp->selectedModel->isEditable() ? 'true' : 'false') . '
            };
            var RDFAUTHOR_DEFAULT_GRAPH = "' . (string)$this->_owApp->selectedModel . '";' . PHP_EOL;
        }

        if ($this->_owApp->selectedResource) {
            $this->view->jsonVars .= '
            var selectedResource = {
                URI: "' . (string)$this->_owApp->selectedResource . '",
                title: ' . json_encode((string)$this->_owApp->selectedResource->getTitle()) . ',
                graphURI: "' . (string)$this->_owApp->selectedModel . '"
            };
            var RDFAUTHOR_DEFAULT_SUBJECT = "' . (string)$this->_owApp->selectedResource . '";' . PHP_EOL;
        }

        // set ratio between left bar and main window
        if (isset($this->_session->sectionRation)) {
            $this->view->headScript()->appendScript(
                'var sectionRatio = ' . $this->_session->sectionRation . ';'
            );
        }

        if (isset($this->_config->meta)) {
            if (isset($this->_config->meta->Keywords)) {
                $this->view->metaKeywords = $this->_config->meta->Keywords;
            }
            if (isset($this->_config->meta->Description)) {
                $this->view->metaDescription = $this->_config->meta->Description;
            }
        }

        /**
         * @trigger onAfterInitController
         * Triggered after a controller from class OntoWiki_Controller_Base (or derived)
         * has been initialized.
         */
        $event = new Erfurt_Event('onAfterInitController');
        $event->response = $this->_response;
        $eventResult = $event->trigger();
    }

    /**
     * Zend pre-dispatch hook.
     *
     * Executed before dispatching takes place.
     */
    public function preDispatch()
    {
        // log time before dispatch
        $this->_preController = microtime(true);

        // render main modules
        if (!$this->view->has('main.sidewindows') && !$this->_request->isXmlHttpRequest()) {
            $this->view->placeholder('main.sidewindows')->append($this->view->modules('main.sidewindows'));
        }
    }

    /**
     * Zend post-dispatch hook.
     *
     * Executed after dispatching has taken place.
     */
    public function postDispatch()
    {
        // log dispatch time
        $this->_owApp->logger->info(
            sprintf(
                'Dispatching %s/%s: %d ms',
                $this->_request->getControllerName(),
                $this->_request->getActionName(),
                (microtime(true) - $this->_preController) * 1000
            )
        );

        // catch redirect
        if ($this->_request->has('redirect-uri')) {
            $redirectUri = urldecode($this->_request->getParam('redirect-uri'));
            $front = Zend_Controller_Front::getInstance();
            $options = array(
                'prependBase' => (false === strpos($redirectUri, $front->getBaseUrl()))
            );

            $this->_redirect($redirectUri, $options);
        }

        if (strlen($this->view->placeholder('main.window.title')->toString()) > 0) {
            $this->view->headTitle($this->view->placeholder('main.window.title')->toString());
        }
    }

    /**
     * Returns a parameter from the current request and expands its URI
     * using the local namespace table. It also strips slashes if
     * magic_quotes_gpc is turned on in PHP.
     *
     * @param string $name the name of the parameter
     * @param boolean $expandNamespace Whether to expand the namespace or not
     * @deprecated 0.9.5, use OntoWiki_Request::getParam() instead
     *
     * @return mixed the parameter or null if not found
     */
    public function getParam($name, $expandNamespace = false)
    {
        $value = $this->_request->getParam($name);

        if ($expandNamespace) {
            $value = OntoWiki_Utils::expandNamespace($value);
        }

        if (get_magic_quotes_gpc()) {
            $value = stripslashes($value);
        }

        return $value;
    }

    /**
     * Adds a module context which the controller provides
     *
     * @param string $moduleContext The context name
     */
    public function addModuleContext($moduleContext)
    {
        if (!$this->_request->isXmlHttpRequest()) {
            $moduleContent = $this->view->modules($moduleContext);
            $this->view->placeholder('main.window.innerwindows')->append($moduleContent);
        }
    }

    /**
     * Clones the current view object and returns a new view
     * object with the same configuration but all variables cleared.
     *
     * @return OntoWiki_View
     */
    public function cloneView()
    {
        $view = clone $this->view;
        $view->clearVars();

        return $view;
    }
}


