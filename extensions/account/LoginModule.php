<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki login module
 *
 * Provides the OntoWiki application menu and a search field
 *
 * @category   OntoWiki
 * @package    Extensions_Account
 * @author     Norman Heino <norman.heino@gmail.com>
 */
class LoginModule extends OntoWiki_Module
{
    /**
     * Returns the message of the module
     *
     * @return array
     */
    public function getMessage()
    {
        if ($authResult = $this->_owApp->authResult) {

            // Translate partial messages before creation of message box
            $translate = OntoWiki::getInstance()->translate;

            $message = $translate->translate($authResult[0]);

            $message .= '<a href="' . $this->view->urlBase . 'account/recover"> '
                     . $translate->translate('Forgot your password?')
                     . ' </a>';

            // create messagebox for loginbox (no escape for html code)
            $message = new OntoWiki_Message(
                $message,
                OntoWiki_Message::ERROR,
                array('escape' => false, 'translate' => false)
            );
            unset($this->_owApp->authResult);

            return $message;
        }
    }

    /**
     * Returns the content for the model list.
     */
    public function getContents()
    {
        $request = $this->_owApp->request;
        $url     = $request->getServer('REQUEST_URI');

        $data = array(
            'actionUrl'   => $this->_config->urlBase . 'application/login',
            'redirectUri' => urlencode((string) $url)
        );

        if ($this->_erfurt->getAc()->isActionAllowed('RegisterNewUser')) {
            $data['showRegisterButton'] = true;
            $data['registerActionUrl'] = $this->_config->urlBase . 'application/register';
            $data['openIdRegisterActionUrl'] = $this->_config->urlBase . 'application/openidreg';
            $data['webIdRegisterActionUrl'] = $this->_config->urlBase . 'application/webidreg';
        } else {
            $data['showRegisterButton'] = false;
        }

        // insert subtemplates according to the allow array in the config
        $content = array();
        foreach ($this->_privateConfig->allow as $template => $value) {
            if ($value == 1) {
                $content[$template] = $this->render('templates/'.$template, $data);
            }
        }

        return $content;
    }

    public function shouldShow()
    {
        if ($this->_owApp->erfurt->getAc() instanceof Erfurt_Ac_None) {
            return false;
        }
        
        if (!$this->_owApp->user || $this->_owApp->user->isAnonymousUser()) {
            return true;
        }

        return false;
    }

    public function allowCaching()
    {
        // no caching
        return false;
    }

    public function getTitle()
    {
        return "Login";
    }
}
