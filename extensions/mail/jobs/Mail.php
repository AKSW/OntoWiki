<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright   Copyright (c) 2013, {@link http://aksw.org AKSW}
 * @license     http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * This class includes a mail send job.
 *
 * @category    OntoWiki
 * @package     Extensions_Mail
 * @copyright   Copyright (c) 2013, {@link http://aksw.org AKSW}
 * @author      Christian Würker <christian.wuerker@ceusmedia.de>
 * @license     http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class Mail_Job_Mail extends Erfurt_Worker_Job_Abstract
{
    /**
     *  Send mail.
     *
     *  @access     public
     *  @param      object      $workload       Object with mail sender, receiver, subject and body
     *  @return     void
     *  @throws     InvalidArgumentException    if workload object has no sender
     *  @throws     InvalidArgumentException    if workload object has no receiver
     *  @throws     InvalidArgumentException    if workload object has no subject
     *  @throws     InvalidArgumentException    if workload object has no body
     */
    public function run($workload)
    {
        $smtpServer = $this->options['server'];
        $config     = array();
        if ($this->options['auth']) {
            $config['auth']      = $this->options['auth'];
            $config['username']  = $this->options['username'];
            $config['password']  = $this->options['password'];
        }
        $transport = new Zend_Mail_Transport_Smtp($smtpServer, $config);

        if (is_object($workload)) {
            if (empty($workload->sender)){
                throw new InvalidArgumentException('Workload is missing sender');
            }
            if (empty($workload->receiver)){
                throw new InvalidArgumentException('Workload is missing receiver');
            }
            if (empty($workload->subject)){
                throw new InvalidArgumentException('Workload is missing subject');
            }
            if (empty($workload->body)){
                throw new InvalidArgumentException('Workload is missing body');
            }
            $mail = new Zend_Mail();
            $mail->setDefaultTransport($transport);
            $mail->addTo($workload->receiver);
            $mail->setSubject($workload->subject);
            $mail->setBodyText($workload->body);
            $mail->setFrom($workload->sender);
            $mail->send($transport);
            $this->logSuccess('Mail "'.$workload->subject.'" sent to '.$workload->receiver);
        }
    }
}
