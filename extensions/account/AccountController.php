<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * Component controller for user account related stuff
 *
 * @category   OntoWiki
 * @package    Extensions_Account
 * @author     Christoph Rieß <c.riess.dev@googlemail.com>, Norman Heino <norman.heino@gmail.com>
 */
class AccountController extends OntoWiki_Controller_Component
{
    /**
     * Handles identity recovery operations
     */
    public function recoverAction()
    {
        OntoWiki::getInstance()->getNavigation()->disableNavigation();
        $config = Erfurt_App::getInstance()->getConfig();
        $translate = $this->_owApp->translate;

        // start in phase 0
        $phase = 0;
        $success = true;
        $translate = $this->_owApp->translate;

        // check available params
        // phase 1 is generation of hash taking recovery measures (mailing etc...)
        $params['for'] = $this->getParam('for');
        if ( empty($params['for']) ) {
            unset($params['for']);
        } else {
            $this->view->identity = $params['for'];
            $phase = 1;
        }

        // phase 2 for entering new password
        $params['hash'] = $this->getParam('hash');
        if ( empty($params['hash']) ) {
            unset($params['hash']);
        } else {
            $this->view->hash = $params['hash'];
            $phase = 2;
        }

        // phase 3 for cleanup and password change in ow system
        $params['password_o']   = $this->getParam('password_o');
        $params['password_r']   = $this->getParam('password_r');
        if ( empty($params['hash']) || empty($params['password_o']) || empty ($params['password_r']) ) {
            unset($params['password_o']);
            unset($params['password_r']);
        } else {
            $phase = 3;
        }

        $title = sprintf($translate->_('Account Recovery Stage %s'), ($phase + 1) .' / 4');

        $this->view->placeholder('main.window.title')->set($title);
        $this->view->phase = $phase;

        require_once 'Erfurt/Auth/Identity/Recovery.php';

        $recoveryObject = new Erfurt_Auth_Identity_Recovery();

        try {

            switch ($phase) {
                case 0:
                    break;
                case 1:
                    $userInfo = $recoveryObject->validateUser($params['for']);

                    $tplDir   = $this->_componentRoot . 'templates/';

                    $userUri = $userInfo['userUri'];
                    $template = array();

                    $template['mailSubject'] = $translate->_('OntoWiki Account Recovery');
                    $template['mailTo'] = $userInfo[$config->ac->user->mail];
                    $template['mailUser'] = $userInfo[$config->ac->user->name];

                    $url = new OntoWiki_Url();
                    $url->setParam('controller', 'account');
                    $url->setParam('action', 'recover');
                    $url->setParam('hash', $userInfo['hash']);
                    $url->setParam('for', null);

                    $this->view->recoveryUrl = (string) $url;

                    $this->view->username = $userInfo[$config->ac->user->name];

                    $txtFile  = 'mail/text/' . $this->_owApp->translate->getLocale() . '.txt';
                    if (file_exists($tplDir . $txtFile) ) {
                        $template['contentText'] = $this->view->render($txtFile);
                    } else {
                        $template['contentText'] = $this->view->render('mail/text/default.txt');
                    }

                    $htmlFile  = 'mail/html/' . $this->_owApp->translate->getLocale() . '.phtml';
                    if (file_exists($tplDir . $htmlFile) ) {
                        $template['contentHtml'] = $this->view->render($htmlFile);
                    } else {
                        $template['contentHtml'] = $this->view->render('mail/html/default.phtml');
                    }

                    $recoveryObject->setTemplate($template);

                    $success  = $recoveryObject->recoverWithIdentity($userUri);
                    break;
                case 2:
                    $success = $recoveryObject->validateHash($params['hash']);
                    break;
                case 3:
                    $success = $recoveryObject->resetPassword(
                        $params['hash'],
                        $params['password_o'],
                        $params['password_r']
                    );
                    break;
                default:
                    break;
            }

        } catch (Erfurt_Exception $e) {
            $success = false;
            $message = $translate->_($e->getMessage());
            $this->_owApp->appendMessage(
                new OntoWiki_Message($message, OntoWiki_Message::ERROR)
            );
        }

        // show toolbar if not in last phase and no errors occured
        if ($success && $phase < 3) {
            $toolbar = $this->_owApp->toolbar;
            $toolbar->appendButton(OntoWiki_Toolbar::SUBMIT, array('name' => 'Submit'));
            $this->view->placeholder('main.window.toolbar')->set($toolbar);
        }

        if (!$success) {
            $title = $translate->_('Account Recovery Error');
            $this->view->placeholder('main.window.title')->set($title);
        }

        $this->view->success       = $success;
        $this->view->formActionUrl = $this->_config->urlBase . 'account/recover';
        $this->view->formEncoding  = 'multipart/form-data';
        $this->view->formClass     = 'simple-input input-justify-left';
        $this->view->formMethod    = 'post';
        $this->view->formName      = 'accountrecovery';
    }
}
