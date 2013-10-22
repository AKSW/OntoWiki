<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * This class includes a mail send job
 *
 * @category   OntoWiki
 * @package    Extensions_Mail
 */
class Mail_Job_Mail extends Erfurt_Worker_Job_Abstract
{
    private $_owApp     = null;
    private $_config    = null;
    private $_transport = null;

    public function __init()
    {
        $this->_owApp   = OntoWiki::getInstance();
        $this->_config  = $this->_owApp->config;
    }

    public function run($workload)
    {
        $smtpServer = $this->options['server'];
        $config = array();
        if ($this->options['auth']) {
            $config['auth']      = $this->options['auth'];
            $config['username']  = $this->options['username'];
            $config['password']  = $this->options['password'];
        }
        $this->_transport = new Zend_Mail_Transport_Smtp($smtpServer, $config);

        if (is_object($workload)) {
            $mail = new Zend_Mail();
            $mail->setDefaultTransport($this->_transport);
            $mail->addTo($workload->receiver);
            $mail->setSubject($workload->subject);
            $mail->setBodyText($workload->body);
            $mail->setFrom($workload->sender);
            $mail->send($this->_transport);
            $this->logSuccess('Mail "'.$workload->subject.'" sent to '.$workload->receiver);
        }
    }
}
