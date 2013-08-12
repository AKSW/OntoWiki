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

    const REQUESTKEY_FIELD_NAME = 'requestkey';
    public static $testRequestKey;

    /**
     * Constructor
     */
    public function init()
    {
        parent::init();

        // init controller variables
        $this->_owApp  = OntoWiki::getInstance();
        $this->_config = $this->_owApp->config;

        // request key
        $request = $this->getRequest();
        if ($request->isPost()) {
            $hash = $request->getPost(static::REQUESTKEY_FIELD_NAME);
            if ($hash !== static::getRequestKey()) {
                throw new OntoWiki_Exception("Something's fishy.");
            }
        }

        $this->view->headScript()->appendScript('
            $(function(){
                $(document).ajaxSend(function(event, jqxhr, settings) {
                    if (settings.type == "POST") {
                        settings.data = (settings.data == null || settings.data.length == 0) ? "" : (settings.data + "&");
                        settings.data += "'.implode('=', static::getRequestKeyFormData()).'";
                    }
                });
            });
        ');
    }

    public static function getRequestKey()
    {
        $owApp = OntoWiki::getInstance();

        if (static::$testRequestKey !== null) {
            return static::$testRequestKey;
        }

        if (!isset($owApp->session->requestkey)) {
            $zendHash = new Zend_Form_Element_Hash('hash');
            $owApp->session->requestkey = $zendHash->getHash();
        }

        return $owApp->session->requestkey;
    }

    public static function getRequestKeyFormData()
    {
        return array(static::REQUESTKEY_FIELD_NAME,
                     static::getRequestKey());
    }

    public static function getRequestKeyFormContent()
    {
        $owApp = OntoWiki::getInstance();

        return call_user_func_array(array($owApp->view, 'formHidden'),
                                    static::getRequestKeyFormData());
    }
}

