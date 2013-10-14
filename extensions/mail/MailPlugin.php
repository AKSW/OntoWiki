<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
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
class MailPlugin extends OntoWiki_Plugin
{
    public function onAnnounceWorker($event)
    {
        $event->registry->registerJob(
            "testMail",                                                         //  job key name
            "extensions/mail/jobs/Mail.php",                                    //  job class file
            "Mail_Job_Mail",                                                    //  job class name
            $this->_privateConfig,                                              //  extension configuration
            NULL                                                                //  further options
        );
    }
}

