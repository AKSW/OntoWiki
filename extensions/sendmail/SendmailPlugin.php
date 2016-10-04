<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011-2016, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once 'OntoWiki/Plugin.php';
require_once 'Zend/Mail.php';

/**
 * This class includes a plugin to send mails via an event from different places
 * within ontowiki.
 *
 * Long description for class (if any) ...
 *
 * @category   OntoWiki
 * @package    Extensions_Sendmail
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @author     Christian Maier <christianmaier83@gmail.com>
 * @author     Michael Niederstätter <michael.niederstaetter@gmail.com>
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class SendmailPlugin extends OntoWiki_Plugin
{
    private $_transport = null;

    public function init()
    {
        $smtpServer = $this->_privateConfig->smtp->server;

        $config = $this->_privateConfig->smtp->config->toArray();

        $this->_transport = new Zend_Mail_Transport_Smtp($smtpServer, $config = null);
    }

    public function onEmailsend($event)
    {
        if (isset($event->receiver)) {
            foreach ($event->receiver as $receiver) {

                $mail = new Zend_Mail();
                $mail->setDefaultTransport($this->_transport);

                $mail->addTo($receiver);

                $mail->setFrom($event->sender);
                $mail->setSubject($event->subject);

                if ($event->type == 'text') {
                    $mail->setBodyText($event->content);
                } elseif ($event->type == 'html') {
                    $mail->setBodyHtml($event->content);
                }

                $mail->send($this->_transport);
            }

            return true;

        } else {
            return false;
        }
    }
}

