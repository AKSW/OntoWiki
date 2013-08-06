<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once 'OntoWiki/Plugin.php';

/**
 * Mitigate CSRF issues.
 *
 * @category   OntoWiki
 * @package    Extensions_Requestkey
 */
class RequestkeyPlugin extends OntoWiki_Plugin
{
    protected static $_fieldName = 'requestkey';
    protected static $_testHash;

    public function onAfterInitController($event)
    {
        $this->onAfterInitServiceController($event);
    }

    public function onAfterInitServiceController($event)
    {
        $owApp = OntoWiki::getInstance();

        $controller = $owApp->frontController;
        $request = $controller->getRequest();
        if ($request->isPost()) {
            $hash = $request->getPost(static::$_fieldName);
            if ($hash !== $this->getHash()) {
                throw new OntoWiki_Exception("Something's fishy.");
            }
        }

        $this->view->headScript()->appendScript('
            $(function(){
                $(document).ajaxSend(function(event, jqxhr, settings) {
                    if (settings.type == "POST") {
                        settings.data = (settings.data == null || settings.data.length == 0) ? "" : (settings.data + "&");
                        settings.data += "'.static::$_fieldName.'='.$this->getHash().'";
                    }
                });
            });
        ');
    }

    public function onDisplayPostForm($event)
    {
        $owApp = OntoWiki::getInstance();

        return array($owApp->view->formHidden(static::$_fieldName, $this->getHash()));
    }

    public function onTestRequestHttpPost($event)
    {
        if (static::$_testHash === null) {
            // use static property instead of session for testing
            static::$_testHash = $this->getHash();
        }

        $event->request->setPost(array(static::$_fieldName => $this->getHash()));
    }

    protected function getHash()
    {
        $owApp = OntoWiki::getInstance();

        if (static::$_testHash !== null) {
            return static::$_testHash;
        }

        if (!isset($owApp->session->requestkey)) {
            $this->_generateHash();
        }

        return $owApp->session->requestkey;
    }

    protected function _generateHash()
    {
        $owApp = OntoWiki::getInstance();

        $owApp->session->requestkey = (new Zend_Form_Element_Hash('hash'))->getHash();
    }
}
