<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki error controller.
 * Fetched by default through the Zend_Controller_Plugin_ErrorHandler
 *
 * @package    OntoWiki_Controller
 * @author     Norman Heino <norman.heino@gmail.com>
 */
class ErrorController extends Zend_Controller_Action
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
     * Constructor
     */
    public function init()
    {
        // init controller variables
        $this->_owApp   = OntoWiki::getInstance();
        $this->_config  = $this->_owApp->config;
        $this->_session = $this->_owApp->session;
        $this->_erfurt  = $this->_owApp->erfurt;
    }

    /**
     * Default action that is triggered when an error occures
     * during the dispatch process.
     */
    public function errorAction()
    {
        if (defined('_OWDEBUG')) {
            $this->_debugError();
        } else {
            $this->_gracefulError();
        }

        // we provide a complete page
        $this->_helper->layout()->disableLayout();
    }

    /*
     * the debug error output has a stacktrace and other debug information
     */
    protected function _debugError()
    {
        if ($this->_request->has('error_handler')) {
            // get errors passed by error handler plug-in
            $errors    = $this->_getParam('error_handler');
            $exception = $errors->exception;

            // check error type and send headers accordingly
            switch ($errors->type) {
                case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
                case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                    $this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
                    break;
                default:
                    // don't change headers
            }

            switch (true) {
                case ($exception instanceof OntoWiki_Http_Exception):
                    $this->_helper->layout()->disableLayout();
                    $this->_helper->viewRenderer->setNoRender();

                    $response = $this->getResponse();
                    $response->setHttpResponseCode($exception->getResponseCode());
                    $response->setBody($exception->getResponseMessage());
                    return;
            }

            // exception code determines whether error or info
            // see erfurt developer documentation
            if (($exception->getCode() > 0) && ($exception->getCode() < 2000) and false) {
                $this->view->heading   = 'OntoWiki Info Notice';
                $this->view->errorType = 'info';
                $this->view->code      = $exception->getCode();
            } else {
                $this->view->heading   = 'OntoWiki Error';
                $this->view->errorType = 'error';

                if ($exception->getCode() !== 0) {
                    $this->view->code      = $exception->getCode();
                }
            }

            $errorString = $exception->getMessage();

            $this->view->exceptionType = get_class($exception);
            $this->view->exceptionFile = $exception->getFile() . '@' . $exception->getLine();

            $stacktrace = $exception->getTrace();
            $stacktraceString = '';
            foreach ($stacktrace as $i=>$spec) {
                $lineStr = isset($spec['file']) ?
                    ('@'.$spec['file'] . (isset($spec['line']) ? ':'.$spec['line'] : '') ) :
                    '';
                $stacktraceString .= '#' . $i . ': ' .(isset($spec['class']) ? $spec['class'] : '') .
                    (isset($spec['type']) ?$spec['type'] : '') . $spec['function'] .
                    $lineStr . '<br />';

                // foreach ($spec['args'] as $arg) {
                //                     if (is_string($arg)) {
                //                         $stacktraceString .= '    - ' . $arg . '<br />';
                //                     } else if (is_object($arg)) {
                //                         $stacktraceString .= '    - ' . get_class($arg) . '<br />';
                //                     } else {
                //                         $stacktraceString .= '    - ' . (string)$arg . '<br />';
                //                     }
                //                 }
            }

            $this->view->stacktrace = $stacktraceString;
        } else {
            $this->view->heading   = 'OntoWiki Error';
            $this->view->errorType = 'error';

            $message = current(OntoWiki::getInstance()->drawMessages());
            if ($message instanceof OntoWiki_Message) {
                $errorString = $message->getText();
            } else {
                // No message, redirect to index page
                $this->_redirect($this->config->urlBase, array('code' => 302));
            }
        }

        $this->view->urlBase = $this->_config->urlBase;
        $this->view->errorText = $errorString;
    }

    /*
     * the graceful error output tries to be as nice as possible
     */
    protected function _gracefulError()
    {
        $requestExtra = str_replace(
            $this->getRequest()->getBaseUrl(),
            '',
            $this->getRequest()->getRequestUri()
        );
        $requestedUri = OntoWiki::getInstance()->config->urlBase . ltrim($requestExtra, '/');

        $createUrl = new OntoWiki_Url(array(), array());
        $createUrl->controller = 'resource';
        $createUrl->action = 'new';
        $createUrl->setParam('r', $requestedUri);
        $this->view->requestedUrl = (string) $requestedUri;
        $this->view->createUrl    = (string) $createUrl;
        $this->view->urlBase      = OntoWiki::getInstance()->config->urlBase;

        $exception = null;
        $exceptionType = null;
        if ($this->_request->has('error_handler')) {
            // get errors passed by error handler plug-in
            $errors        = $this->_getParam('error_handler');
            $exception     = $errors->exception;
            $exceptionType = get_class($exception);
            $errorString   = $exception->getMessage();
            OntoWiki::getInstance()->logger->emerg(
                $exceptionType . ': ' . $errorString . ' -> ' .
                $exception->getFile() . '@' . $exception->getLine()
            );
        }

        // Zend_Controller_Dispatcher_Exception means invalid controller
        // -> resource not found
        if (
            ($this->_request->has('error_handler')) &&
            ($exceptionType != 'Zend_Controller_Dispatcher_Exception')
        ) {
            if (
                (null !== $exception) &&
                (method_exists($exception, 'getResponseCode')) &&
                (null !== $exception->getResponseCode())
            ) {
                $this->getResponse()->setHttpResponseCode($exception->getResponseCode());
                $this->_helper->viewRenderer->setScriptAction('error');
            } else {
                $this->getResponse()->setHttpResponseCode(500);
                $this->_helper->viewRenderer->setScriptAction('500');
            }
        } else {
            $this->getResponse()->setHttpResponseCode(404);
            $this->_helper->viewRenderer->setScriptAction('404');
        }
    }
}

