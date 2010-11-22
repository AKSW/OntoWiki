<?php

/**
 * OntoWiki module – login
 *
 * Provides the OntoWiki application menu and a search field
 *
 * @category   OntoWiki
 * @package    OntoWiki_extensions_modules_login
 * @author     Norman Heino <norman.heino@gmail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: login.php 4282 2009-10-12 11:20:57Z c.riess.dev $
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
            $cm = $this->_owApp->componentManager;

            $message = $translate->translate($authResult[0]);
            // check if account recovery is possible and display link then
            if ( $cm->isComponentRegistered('account') ) {
                $message .= '<a href="' . $this->view->urlBase . 'account/recover"> '
                         . $translate->translate('Forgot your password?') 
                         . ' </a>';
            }
            
            // create messagebox for loginbox (no escape for html code)
            $message = new OntoWiki_Message($message, OntoWiki_Message::ERROR, array('escape' => false, 'translate' => false) );
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

        $content = array(
            'Local' => $this->render('templates/local', $data),
            'OpenID' => $this->render('templates/openid', $data),
            'FOAFSSL' => $this->render('templates/webid', $data)
        );

        return $content;
    }

    public function shouldShow()
    {
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
}


