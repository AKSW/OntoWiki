<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2013, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * This class includes a plugin to send mails via an event from different places
 * within ontowiki.
 *
 * @category   OntoWiki
 * @package    Extensions_Mail
 */
class MailPlugin extends OntoWiki_Plugin
{
    public function onAnnounceWorker($event)
    {
        // key name, class file, class name, config
        $event->registry->registerJob(
            'testMail',
            'extensions/mail/jobs/Mail.php',
            'Mail_Job_Mail',
            $this->_privateConfig->smtp->toArray()
        );
    }
}

