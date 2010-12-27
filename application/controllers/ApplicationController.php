<?php

/**
 * OntoWiki application controller.
 * 
 * @package    application
 * @subpackage mvc
 * @author     Norman Heino <norman.heino@gmail.com>
 * @author     Philipp Frischmuth <pfrischmuth@googlemail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: ApplicationController.php 4313 2009-10-14 21:37:47Z c.riess.dev $
 */
class ApplicationController extends OntoWiki_Controller_Base
{
    /**
     * Displays OntoWiki's about page
     */
    public function aboutAction()
    {
        OntoWiki_Navigation::disableNavigation();
        $this->view->placeholder('main.window.title')->set('About OntoWiki');
        
        $version = $this->_config->version->number;
        if (isset($this->_config->version->suffix)) {
            $version .= ' ' . $this->_config->version->suffix;
        }
        
        $cacheWritable = is_writable($this->_config->cache->path)
                       ? ' <span style="color:#aea">(writable)</span>'
                       : ' <span style="color:#eaa">(not writable!)</span>';
        $logWritable = is_writable($this->_config->log->path)
                     ? ' <span style="color:#aea">(writable)</span>'
                     : ' <span style="color:#eaa">(not writable!)</span>';
        
        $data = array(
            'System' => array(
                'OntoWiki Version' => $version, 
                'PHP Version'      => phpversion(), 
                'Backend'          => $this->_owApp->erfurt->getStore()->getBackendName(), 
                'Debug Mode'       => defined('_OWDEBUG') ? 'enabled' : 'disabled'
            ), 
            'User Interface' => array(
                'Theme'    => rtrim($this->_config->themes->default, '/'), 
                'Language' => $this->_config->languages->locale, 
            ), 
            'Paths' => array(
                'Extensions Path'     => _OWROOT . rtrim($this->_config->extensions->base, '/'),
                'Translations Path' => _OWROOT . rtrim($this->_config->languages->path, '/'), 
                'Themes Path'       => _OWROOT . rtrim($this->_config->themes->path, '/')
            ), 
            'Cache' => array(
                'Path'                => rtrim($this->_config->cache->path, '/') . $cacheWritable, 
                'Module Caching'      => ((bool)$this->_config->cache->modules == true) ? 'enabled' : 'disabled', 
                'Translation Caching' => ((bool)$this->_config->cache->translation == true) ? 'enabled' : 'disabled'
            ), 
            'Logging' => array(
                'Path' => rtrim($this->_config->log->path, '/') . $logWritable, 
                'Level'  => (bool)$this->_config->loglevel ? $this->_config->loglevel : 'disabled'
            )
        );
        
        $this->view->data = $data;
    }
     
    /**
     * Authenticates with Erfurt using the provided credentials.
     */
    public function loginAction()
    {
        $erfurt  = $this->_owApp->erfurt;
        $post    = $this->_request->getPost();
        
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        // If remember option is on make session persistent
		if (!empty($post['login-save']) && $post['login-save'] == 'on') {
			// Make session persistent (for about 23 years)
			Zend_Session::rememberMe(726364800);
		}

        $loginType = $post['logintype'];
        // lokaler Login
        if ($loginType === 'locallogin') {
            $username = $post['username'];
            $password = $post['password'];
            
            $authResult = $erfurt->authenticate($username, $password);
        } 
        // OpenID
        else if ($loginType === 'openidlogin') {
            $username = $post['openid_url'];
            $redirectUrl = $post['redirect-uri'];
            $verifyUrl = $this->_config->urlBase . 'application/verifyopenid';
            
            $authResult = $erfurt->authenticateWithOpenId($username, $verifyUrl, $redirectUrl);
        } 
        // FOAF+SSL
        else if ($loginType === 'webidlogin') {
            $redirectUrl = $this->_config->urlBase . 'application/loginfoafssl';
            $authResult = $erfurt->authenticateWithFoafSsl(null, $redirectUrl);
        } else {
            // Not supported...
            return;
        }
        
        // reload selected model w/ new privileges
        if ($this->_owApp->selectedModel instanceof Erfurt_Rdf_Model) {
            $this->_owApp->selectedModel = $erfurt->getStore()->getModel((string) $this->_owApp->selectedModel);
        }
        
        
        $this->_owApp->authResult = $authResult->getMessages();
    }
    
    public function verifyopenidAction()
    {
        $erfurt = $this->_owApp->erfurt;
        $get = $this->_request->getQuery();
        
        $authResult = $erfurt->verifyOpenIdResult($get);
        
        $this->_owApp->authResult = $authResult->getMessages();
        
        if (isset($get['ow_redirect_url'])) {
            $this->_redirect(urldecode($get['ow_redirect_url']), array('prependBase' => false));
        } else {
            $this->_redirect($this->_config->urlBase, array('prependBase' => false));
        }
    }
    
    public function loginfoafsslAction()
    {
        $erfurt = $this->_owApp->erfurt;
        $get = $this->_request->getQuery();
        //$get['url'] = $this->_request->getHttpHost() . $this->_request->getRequestUri();
        
        $authResult = $erfurt->authenticateWithFoafSsl($get);
        $this->_owApp->authResult = $authResult->getMessages();
        
        $this->_redirect($this->_config->urlBase, array('prependBase' => false));
    }
    
    /**
     * Destroys auth credentials and logs the current agent out.
     */
    public function logoutAction()
    {
        // destroy auth
        Erfurt_Auth::getInstance()->clearIdentity();
        // destroy any selections user has made
        Zend_Session::destroy(true);
                
        $this->_redirect($this->_config->urlBase);
    }
    
    /**
     * Registers a new user
     */
    public function registerAction()
    {
        OntoWiki_Navigation::disableNavigation();
        $this->_helper->viewRenderer->setScriptAction('register');
        
        $this->view->placeholder('main.window.title')->set('Register User');
        
        $this->view->formActionUrl = $this->_config->urlBase . 'application/register';
		$this->view->formMethod    = 'post';
		$this->view->formClass     = 'simple-input input-justify-left';
		$this->view->formName      = 'registeruser';
        $this->view->username      = '';
        $this->view->readonly      = '';
        $this->view->email         = '';
		
		$toolbar = $this->_owApp->toolbar;
		$toolbar->appendButton(OntoWiki_Toolbar::SUBMIT, array('name' => 'Register User'))
		        ->appendButton(OntoWiki_Toolbar::RESET, array('name' => 'Reset Form'));
		$this->view->placeholder('main.window.toolbar')->set($toolbar);
        
        $post = $this->_request->getPost();
        
        $this->_owApp->appendMessage(new OntoWiki_Message(
            'Already own an <span class="openid">OpenID?</span> <a href="' . $this->_config->urlBase . 'application/openidreg">Register here</a>', 
            OntoWiki_Message::INFO, 
            array('escape' => false, 'translate' => false)
            ));
        
        if ($post) {
            $registeredUsernames      = array();
            $registeredEmailAddresses = array();
            
            foreach ($this->_erfurt->getUsers() as $userUri => $userArray) {
                if (array_key_exists('userName', $userArray)) {
                    $registeredUsernames[] = $userArray['userName'];
                }
                
                if (array_key_exists('userEmail', $userArray)) {
                    $registeredEmailAddresses[] = str_replace('mailto:', '', $userArray['userEmail']);
                }
            }
            
            $email     = $post['email'];
            $username  = $post['username'];
            $password  = $post['password'];
            $password2 = $post['password2'];
            
            $emailValidator = new Zend_Validate_EmailAddress();
            
            if (!$this->_erfurt->isActionAllowed('RegisterNewUser') or 
                !($actionConfig = $this->_erfurt->getActionConfig('RegisterNewUser'))) {
                $message    = 'Action not permitted for the current user.';
                $this->_owApp->appendMessage(new OntoWiki_Message($message, OntoWiki_Message::ERROR));
                
            } else if (trim($email) == '') {
                $message    = 'Email address must not be empty.';
                $this->_owApp->appendMessage(new OntoWiki_Message($message, OntoWiki_Message::ERROR));
                
            } else if (in_array($email, $registeredEmailAddresses)) {
                $message    = 'Email address is already registered.';
                $this->_owApp->appendMessage(new OntoWiki_Message($message, OntoWiki_Message::ERROR));
                
            } else if (isset($actionConfig['mailvalidation']) && 
                       $actionConfig['mailvalidation'] == 'yes' && 
                       !$emailValidator->isValid($email)) {
                $message    = 'Email address validation failed.';
                $this->_owApp->appendMessage(new OntoWiki_Message($message, OntoWiki_Message::ERROR));
                
            } else if (in_array($username, $registeredUsernames) or ($username == $this->_owApp->erfurt->getStore()->getDbUser())) {
                $message    = 'Username already registered.';
                $this->_owApp->appendMessage(new OntoWiki_Message($message, OntoWiki_Message::ERROR));
                
            } else if (isset($actionConfig['uidregexp']) && 
                       !preg_match($actionConfig['uidregexp'], $username)) {
                $message    = 'Username contains illegal characters.';
                $this->_owApp->appendMessage(new OntoWiki_Message($message, OntoWiki_Message::ERROR));
                
            } else if ($password !== $password2) {
                $message    = 'Passwords do not match.';
                $this->_owApp->appendMessage(new OntoWiki_Message($message, OntoWiki_Message::ERROR));
                
            } else if (strlen($password) < 5) {
                $message    = 'Password needs at least 5 characters.';
                $this->_owApp->appendMessage(new OntoWiki_Message($message, OntoWiki_Message::ERROR));
                
            } else if (isset($actionConfig['passregexp']) && 
                       $actionConfig['passregexp'] != '' && 
                       !@preg_match($actionConfig['passregexp'], $password)) {
                $message    = 'Password does not match regular expression set in system configuration';
                $this->_owApp->appendMessage(new OntoWiki_Message($message, OntoWiki_Message::ERROR));
                
            } else {
                // give default group?
                if (isset($actionConfig['defaultGroup'])) {
                    $group = $actionConfig['defaultGroup'];
                }
                // add new user
                if ($this->_erfurt->addUser($username, $password, $email, $group)) {
                    $message = 'The user "' . $username . '" has been successfully registered.';
                    $this->_owApp->appendMessage(new OntoWiki_Message($message, OntoWiki_Message::SUCCESS));
                } else {
                    $message    = 'A registration error occured. Please refer to the log entries.';
                    $this->_owApp->appendMessage(new OntoWiki_Message($message, OntoWiki_Message::ERROR));
                }
            }
        }
    }
    
    /**
     * Registers a new user with a given OpenID.
     */
    public function openidregAction()
    {
        OntoWiki_Navigation::disableNavigation();
        
        // We render a template, that is also used for preferences.
        $this->_helper->viewRenderer->setScriptAction('openid');
        
        $this->view->placeholder('main.window.title')->set('Register User with OpenID');
        $this->view->formActionUrl = $this->_config->urlBase . 'application/openidreg';
		$this->view->formMethod    = 'post';
		$this->view->formClass     = 'simple-input input-justify-left';
		$this->view->formName      = 'registeruser';
        
        // Fetch POST and GET of the request. One of them or both will be empty.
        $post = $this->_request->getPost();
        $get  = $this->_request->getQuery();

        if (!empty($post)) {
            // Step 1: User entered data and clicked on 'Check OpenID'
            if ((int)$post['step'] === 1) {
                $openId = $post['openid_url'];
                $label  = $post['label'];
                $email  = $post['email'];
                
                $emailValidator = new Zend_Validate_EmailAddress();
                
                // Is register action allowed for current user?
                if (!$this->_erfurt->isActionAllowed('RegisterNewUser') ||
                    !($actionConfig = $this->_erfurt->getActionConfig('RegisterNewUser'))) {
                    
                    $message = 'Action not permitted for the current user.';
                    $this->_owApp->appendMessage(new OntoWiki_Message($message, OntoWiki_Message::ERROR));
                } 
                // openid_url field must not be empty
                else if (empty($openId)) {
                    $message = 'No OpenID was entered.';
                    $this->_owApp->appendMessage(new OntoWiki_Message($message, OntoWiki_Message::ERROR));
                } 
                // Does user already exist?
                else if (array_key_exists($openId, $this->_erfurt->getUsers())) {
                    $message = 'A user with the given OpenID is already registered.';
                    $this->_owApp->appendMessage(new OntoWiki_Message($message, OntoWiki_Message::ERROR));
                } 
                // If an (optional) email address is given, check whether it is valid.
                else if (!empty($email) && isset($actionConfig['mailvalidation']) && 
                           $actionConfig['mailvalidation'] === 'yes' && !$emailValidator->isValid($email)) {
                    
                    $message    = 'Email address validation failed.';
                    $this->_owApp->appendMessage(new OntoWiki_Message($message, OntoWiki_Message::ERROR));
                } 
                // Everything seems to be OK... Check the OpenID (redirect to the provider).
                else {
                    // We want to verify the OpenID auth response in this action.
                    $verifyUrl = $this->_config->urlBase . 'application/openidreg';
                    
                    // If label and/or email are given, put them at the end of the request url, for
                    // we need them later. 
                    if (!empty($label) && !empty($email)) {
                        $verifyUrl .= '?label=' . urlencode($label) . '&email=' . urlencode($email);
                    } else if (!empty($label)) {
                        $verifyUrl .= '?label=' . urlencode($label);
                    } else if (!empty($email)) {
                        $verifyUrl .= '?email=' . urlencode($email);
                    } 
                    
                    $sReg = new Zend_OpenId_Extension_Sreg(array(
                        'nickname' => false,
                        'email'    => false), null, 1.1);
                    
                    $adapter = new Erfurt_Auth_Adapter_OpenId($openId, $verifyUrl, null, null, $sReg);
                    // We use the adapter directly, for we do not store the identity in session.
                    $result = $adapter->authenticate();
                    
                    // If we reach this point, something went wrong
                    $message = 'OpenID check failed.';
                    $this->_owApp->appendMessage(new OntoWiki_Message($message, OntoWiki_Message::ERROR));
                }
                
                // If we reach this section, something went wrong, so we reset the form and show the message.
                $this->view->openid   = '';
                $this->view->readonly = '';
                $this->view->email    = '';
                $this->view->label    = '';
                $this->view->step     = 1;
                
                $toolbar = $this->_owApp->toolbar;
        		$toolbar->appendButton(OntoWiki_Toolbar::SUBMIT, array('name' => 'Check OpenID'))
        		        ->appendButton(OntoWiki_Toolbar::RESET, array('name' => 'Reset Form'));
        		$this->view->placeholder('main.window.toolbar')->set($toolbar);
            } else if ((int)$post['step'] === 2) {
                // Step 2: OpenID was verified and user clicked on register button.
                $openid = $post['openid_url'];
                $email  = $post['email'];
                $label  = $post['label'];
                
                // Give user default group?
                $actionConfig = $this->_erfurt->getActionConfig('RegisterNewUser');
                $group = null;
                if (isset($actionConfig['defaultGroup'])) {
                    $group = $actionConfig['defaultGroup'];
                }
                // Add the new user.
                if ($this->_erfurt->addOpenIdUser($openid, $email, $label, $group)) {
                    $message = 'The user with the OpenID "' . $openid . '" has been successfully registered.';
                    $this->_owApp->appendMessage(new OntoWiki_Message($message, OntoWiki_Message::SUCCESS));
                } else {
                    $message = 'A registration error occured. Please refer to the log entries.';
                    $this->_owApp->appendMessage(new OntoWiki_Message($message, OntoWiki_Message::ERROR));
                }
                
                // Reset the form...
                $this->view->openid   = '';
                $this->view->readonly = '';
                $this->view->email    = '';
                $this->view->label    = '';
                $this->view->step     = 1;
                
                $toolbar = $this->_owApp->toolbar;
        		$toolbar->appendButton(OntoWiki_Toolbar::SUBMIT, array('name' => 'Check OpenID'))
        		        ->appendButton(OntoWiki_Toolbar::RESET, array('name' => 'Reset Form'));
        		$this->view->placeholder('main.window.toolbar')->set($toolbar);
            }   
        } else if (!empty($get)) {
            // This is the verify request
            $sReg = new Zend_OpenId_Extension_Sreg(array(
                'nickname' => false,
                'email'    => false), null, 1.1);
            
            $adapter = new Erfurt_Auth_Adapter_OpenId(null, null, null, $get, $sReg);
            // We use the adapter directly, for we do not store the identity in session.
            $result = $adapter->authenticate();
            
            if (!$result->isValid()) {
                // Something went wrong, show a message
                $message = 'OpenID verification failed.';
                $this->_owApp->appendMessage(new OntoWiki_Message($message, OntoWiki_Message::ERROR));
            }
              
            $data = $sReg->getProperties();
            
            // Use the prefilled data from the user (if given) or if not use the data from the provider (if
            // available).
            if (isset($get['email'])) {
                $email = $get['email'];
            } else if (isset($data['email'])) {
                $email = $data['email'];
            } else {
                $email = '';
            }
            if (isset($get['label'])) {
                $label = $get['label'];
            } else if (isset($data['nickname'])) {
                $label = $data['nickname'];
            } else {
                $label = '';
            }
            
            $this->view->openid   = $get['openid_identity'];
            $this->view->readonly = 'readonly="readonly"'; // OpenID must not be changed now.
            $this->view->email    = $email;
            $this->view->label    = $label;
            $this->view->step     = 2;
            $this->view->checked  = true; // We use this to show a green icon for success
            
            $toolbar = $this->_owApp->toolbar;
    		$toolbar->appendButton(OntoWiki_Toolbar::SUBMIT, array('name' => 'Register User'))
    		        ->appendButton(OntoWiki_Toolbar::CANCEL, array('name' => 'Cancel', 'class' => 'openidreg-cancel'));
    		$this->view->placeholder('main.window.toolbar')->set($toolbar);
        } else {
            // No post and get data... This is the initial form...
            $this->view->openid        = '';
            $this->view->readonly      = '';
            $this->view->email         = '';
            $this->view->label         = '';
            $this->view->step          = 1;
            
            $toolbar = $this->_owApp->toolbar;
    		$toolbar->appendButton(OntoWiki_Toolbar::SUBMIT, array('name' => 'Check OpenID'))
    		        ->appendButton(OntoWiki_Toolbar::RESET, array('name' => 'Reset Form'));
    		$this->view->placeholder('main.window.toolbar')->set($toolbar);    
        }
    }
    
    public function webidregAction()
    {
        OntoWiki_Navigation::disableNavigation();
        
        // We render a template, that is also used for preferences.
        $this->_helper->viewRenderer->setScriptAction('webid');
        
        $this->view->placeholder('main.window.title')->set('Register User with FOAF+SSL');
        $this->view->formActionUrl = $this->_config->urlBase . 'application/webidreg';
		$this->view->formMethod    = 'post';
		$this->view->formClass     = 'simple-input input-justify-left';
		$this->view->formName      = 'registeruser';
        
        // Fetch POST and GET of the request. One of them or both will be empty.
        $post = $this->_request->getPost();
        $get  = $this->_request->getQuery();
        
        // Step 1: Fetch the WebID...
        if (empty($post) && empty($get)) {
            $redirectUrl = $this->_config->urlBase . 'application/webidreg';
            
            $adapter = new Erfurt_Auth_Adapter_FoafSsl(null, $redirectUrl);
            $webId   = $adapter->fetchWebId();
            
            // We should not reach this point;
            return;
        } else if (!empty($get)) {
            // Step 2: Check the web id and fetch foaf data
            $get['url'] = $this->_request->getRequestUri();
            
            $adapter = new Erfurt_Auth_Adapter_FoafSsl();
            
            try {
                $valid = $adapter->verifyIdpResult($get);
                
                if ($valid) {
                    $webId = $get['webid'];
                    $foafData = Erfurt_Auth_Adapter_FoafSsl::getFoafData($webId);
                    
                    if ($foafData !== false) {
                        // Try to get a mbox and label...
                        if (isset($foafData[$webId]['http://xmlns.com/foaf/0.1/mbox'])) {
                            $email = $foafData[$webId]['http://xmlns.com/foaf/0.1/mbox'][0]['value'];
                        } else {
                            $email = '';
                        }
                        
                        if (isset($foafData[$webId][EF_RDFS_LABEL])) {
                            $label = $foafData[$webId][EF_RDFS_LABEL][0]['value'];
                        } else {
                            $label = '';
                        }
                    } else {
                        $email = '';
                        $label = '';
                    }
                    
                    $this->view->webid = $webId;
                    if ($webId != '') {
                        $this->view->checked = true;
                    }
                    
                    if (null !== $email) {
                        $this->view->email = $email;
                    } else {
                        $this->view->email = '';
                    }
                    if (null !== $label) {
                        $this->view->label = $label;
                    } else {
                        $this->view->label = '';
                    }
                    
                    $toolbar = $this->_owApp->toolbar;
            		$toolbar->appendButton(OntoWiki_Toolbar::SUBMIT, array('name' => 'Register'));
            		$this->view->placeholder('main.window.toolbar')->set($toolbar);
                    
                    return;
                } else {
                    // TODO Error message
                    $this->view->webid = '';
                    $this->view->email = '';
                    $this->view->label = '';
                    
                    $this->_owApp->appendMessage(
                        new OntoWiki_Message('No valid certificate found.', OntoWiki_Message::ERROR)
                    );
                    
                    return;
                }
            } catch (Exception $e) {
                $this->view->webid = '';
                $this->view->email = '';
                $this->view->label = '';
                
                $this->_owApp->appendMessage(
                    new OntoWiki_Message('Something went wrong: ' . $e->getMessage(), OntoWiki_Message::ERROR)
                );
                return;
            }
        } else if (!empty($post)) {
            $webId = $post['webid_url'];
            $label  = $post['label'];
            $email  = $post['email'];
            
            $emailValidator = new Zend_Validate_EmailAddress();
            
            // Is register action allowed for current user?
            if (!$this->_erfurt->isActionAllowed('RegisterNewUser') ||
                !($actionConfig = $this->_erfurt->getActionConfig('RegisterNewUser'))) {
                
                $message = 'Action not permitted for the current user.';
                $this->_owApp->appendMessage(new OntoWiki_Message($message, OntoWiki_Message::ERROR));
            } 
            // openid_url field must not be empty
            else if (empty($webId)) {
                $message = 'No WebID was entered.';
                $this->_owApp->appendMessage(new OntoWiki_Message($message, OntoWiki_Message::ERROR));
            } 
            // Does user already exist?
            else if (array_key_exists($webId, $this->_erfurt->getUsers())) {
                $message = 'A user with the given WebID is already registered.';
                $this->_owApp->appendMessage(new OntoWiki_Message($message, OntoWiki_Message::ERROR));
            } 
            // If an (optional) email address is given, check whether it is valid.
            else if (!empty($email) && isset($actionConfig['mailvalidation']) && 
                       $actionConfig['mailvalidation'] === 'yes' && !$emailValidator->isValid($email)) {
                
                $message    = 'Email address validation failed.';
                $this->_owApp->appendMessage(new OntoWiki_Message($message, OntoWiki_Message::ERROR));
            } 
            // Everything seems to be OK... 
            else {
                $actionConfig = $this->_erfurt->getActionConfig('RegisterNewUser');
                $group = null;
                if (isset($actionConfig['defaultGroup'])) {
                    $group = $actionConfig['defaultGroup'];
                }
                // Add the new user.
                if ($this->_erfurt->addOpenIdUser($webId, $email, $label, $group)) {
                    $message = 'The user with the WebID "' . $webId . '" has been successfully registered.';
                    $this->_owApp->appendMessage(new OntoWiki_Message($message, OntoWiki_Message::SUCCESS));
                } else {
                    $message = 'A registration error occured. Please refer to the log entries.';
                    $this->_owApp->appendMessage(new OntoWiki_Message($message, OntoWiki_Message::ERROR));
                }
            }
            
            // If we reach this section, something went wrong, so we reset the form and show the message.
            $this->view->webid   = '';
            $this->view->email   = '';
            $this->view->label   = '';
        }
    }
    
    /**
     * Edits user preferences
     */
    public function preferencesAction()
    {
        $this->view->placeholder('main.window.title')->set('User Preferences');
        $this->addModuleContext('main.window.preferences');
        
        $user = $this->_owApp->getUser();
        
        // Anonymous and Db-User have no prefs.
        if ($user->isAnonymousUser() || $user->isDbUser()) {
            $this->_redirect($this->_config->urlBase, array('prependBase' => false));
        }
        
        $post = $this->_request->getPost();
        if ($post) {
            // We catch all exceptions here, for we do not want the user to see ow crash if something unexpected
            // happens.
            try {
                if (isset($post['openid'])) {
                    $this->_updateOpenIdUser($post);
                } else {
                    $this->_updateUser($post);
                }
            } catch (Exception $e) {
                $this->_owApp->appendMessage(
                    new OntoWiki_Message('Something went wrong: ' . $e->getMessage(), OntoWiki_Message::ERROR)
                );
            }
            
            if (!$this->_owApp->hasMessages()) {
                $message = 'Changes saved.';
                $this->_owApp->appendMessage(new OntoWiki_Message($message, OntoWiki_Message::SUCCESS));
            }   
        } 
            
        $this->view->isOpenIdUser = ($user->isOpenId() || $user->isWebId());
        if ($user->isOpenId() || $user->isWebId()) {
            $this->view->openid = $user->getUri();

            $usernameReadonly = '';
        } else {
            $usernameReadonly = 'readonly="readonly"';
        }

        $email = $user->getEmail();
        if (substr($email, 0, 7) === 'mailto:') {
            $email = substr($email, 7);
        }

        $username = $user->getUsername();

        $this->view->formActionUrl = $this->_config->urlBase . 'application/preferences';
		$this->view->formMethod    = 'post';
		$this->view->formClass     = 'simple-input input-justify-left';
		$this->view->formName      = 'registeruser';
        $this->view->username      = $username;
        $this->view->userReadonly  = $usernameReadonly;
        $this->view->email         = $email;
        $this->view->submitText    = 'Save Changes';

        $toolbar = $this->_owApp->toolbar;
		$toolbar->appendButton(OntoWiki_Toolbar::SUBMIT, array('name' => 'Save Changes', 'id' => 'registeruser'))
		        ->appendButton(OntoWiki_Toolbar::RESET, array('name' => 'Reset Form'));
		$this->view->placeholder('main.window.toolbar')->set($toolbar);

        OntoWiki_Navigation::disableNavigation();

        $this->_helper->viewRenderer->setScriptAction('userdetails');
    }
    
    protected function _updateEmailAddress($newEmail)
    {
        try {
            $this->_erfurt->getAuth()->setEmail($newEmail);
        } catch (Erfurt_Auth_Identity_Exception $e) {
            $this->_owApp->appendMessage(new OntoWiki_Message($e->getMessage(), OntoWiki_Message::ERROR));
            return false;
        }
        
        return true;
    }
    
    protected function _updateUsername($newUsername)
    {
        try {
            $this->_erfurt->getAuth()->setUsername($newUsername);
        } catch (Erfurt_Auth_Identity_Exception $e) {
            $this->_owApp->appendMessage(new OntoWiki_Message($e->getMessage(), OntoWiki_Message::ERROR));
            return false;
        }
        
        return true;
    }
    
    protected function _updatePassword($password1, $password2)
    {
        if ($password1 !== $password2) {
            $message = 'Passwords do not match.';
            $this->_owApp->appendMessage(new OntoWiki_Message($message, OntoWiki_Message::ERROR));
            return false;
        }
        
        try {
            $this->_erfurt->getAuth()->getIdentity()->setPassword($password1);
        } catch (Erfurt_Auth_Identity_Exception $e) {
            $this->_owApp->appendMessage(new OntoWiki_Message($e->getMessage(), OntoWiki_Message::ERROR));
            return false;
        }
        
        return true;
    }
    
    protected function _updateOpenIdUser($post)
    { 
        if ($this->_updateUsername($post['username'])) {
            if ($this->_updateEmailAddress($post['email'])) {
                if (isset($post['changepassword']) && $post['changepassword'] === '1') {
                    return $this->_updatePassword($post['password1'], $post['password2']);
                } else {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    protected function _updateUser($post)
    {
        if ($this->_updateEmailAddress($post['email'])) {
            if (isset($post['changepassword']) && $post['changepassword'] === '1') {
                return $this->_updatePassword($post['password1'], $post['password2']);
            } else {
                return true;
            }
        }
        
        return false;
    }
          
    /**
     * Handles search requests
     */
    public function searchAction()
    {

        $title = $this->_owApp->translate->_('Resource Search');
        $this->view->placeholder('main.window.title')->set($title);
        OntoWiki_Navigation::disableNavigation();
        
        $store = $this->_erfurt->getStore();
        
        if (isset($this->_owApp->selectedModel) and null !== $this->_owApp->selectedModel) {
            $modelUri = $this->_owApp->selectedModel->getModelIri();
        } else {
            $modelUri = null;
        }
        
        if ($this->_request->getParam('searchtext-input') !== null) {
            $searchText = trim($this->getParam('searchtext-input'));
        }

        $error = false;
        $errorMsg = '';

        // check for very short searches (that barely make sense)
        if (strlen($searchText ) < 3) {
            
            $error = true;
            
            $this->_owApp->appendMessage(new OntoWiki_Message(
                $this->_owApp->translate->_('Too Short or empty. (length < 3 )'),
                OntoWiki_Message::ERROR
            ));
            
            $errorMsg .= $this->_owApp->translate->_(
    			'The given search string is either empty or too short: ' .
    			'For searches to make sense they need a minimum of expressiveness.'
            );

        } 
        
        // check if search is already errorenous
        if (!$error) {
            
            // try sparql query pre search check (with limit to 1)
            $elements = $store->getSearchPattern($searchText,$modelUri);
            $query = new Erfurt_Sparql_Query2();
            $query->addElements($elements);
            $query->setLimit(1);
            $query->addFrom($modelUri);
            
            try {
                
                $store->sparqlQuery($query);
                
            } catch (Exception $e) {
                
                // build error message
                $this->_owApp->appendMessage(new OntoWiki_Message(
                    $this->_owApp->translate->_('search failed'),
                    OntoWiki_Message::ERROR
                ));
                
                $error     = true;
                $errorMsg .= 'Message details: ';
                $errorMsg .= str_replace('LIMIT 1', '', $e->getMessage());
                
            }

        }
        
        // if error occured set output for error page
        if ($error) {
                
            $this->view->errorMsg = $errorMsg;
                
        } else {
            // set redirect to effective search controller
            $url = new OntoWiki_Url( array('controller' => 'list'), array());
            $url->setParam('s', $searchText);
            $url->setParam('init', '1');
            $this->_redirect($url);
                
        }
        
    }
    
    public function testAction()
    {
        OntoWiki_Navigation::disableNavigation();
        $this->_helper->viewRenderer->setNoRender();
        $this->view->placeholder('main.window.title')->set('Test');
        
        $testModel = new OntoWiki_ModelTestResource($this->_owApp->erfurt->getStore(), $this->_owApp->selectedModel);
        
        // var_dump((string)$testModel->getQuery());
        if ($result = $testModel->getQueryResult()) {
            $had = array();
            foreach ((array)$result['bindings'] as $resultRow) {
                if (!array_key_exists($resultRow['class']['value'], $had)) {
                    $had[$resultRow['class']['value']] = $resultRow['class']['value'];
                    var_dump($resultRow['class']['value'], $testModel->getTitle($resultRow['class']['value'], 'en'));
                }
            }
        }
    }
}
