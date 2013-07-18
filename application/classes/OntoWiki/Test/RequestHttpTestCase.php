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
            $event          = new Erfurt_Event('onTestRequestHttpPost');
            $event->request = $this;
            $event->trigger();
        }

        return parent::setMethod($type);
    }
}
