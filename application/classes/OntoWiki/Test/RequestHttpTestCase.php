<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

class OntoWiki_Test_RequestHttpTestCase extends Zend_Controller_Request_HttpTestCase
{
    /**
     * Set request method
     *
     * @param  string $type
     * @return Zend_Controller_Request_HttpTestCase
     */
    public function setMethod($type)
    {
        if (strtoupper($type) === 'POST') {
            if (OntoWiki_Controller::$testRequestKey === null) {
                // use static property instead of session for testing
                OntoWiki_Controller::$testRequestKey = OntoWiki_Controller::getRequestKey();
            }

            call_user_func_array(array($this, 'setPost'), OntoWiki_Controller::getRequestKeyFormData());
        }

        return parent::setMethod($type);
    }
}
