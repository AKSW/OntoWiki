<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once 'OntoWiki/Controller/Component.php';

/**
 * Controller class for auth component. 
 *
 * @category   OntoWiki
 * @package    Extensions_Auth
 * @copyright  Copyright (c) 2012 {@link http://aksw.org aksw}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author     Philipp Frischmuth <pfrischmuth@googlemail.com>
 */
class AuthController extends OntoWiki_Controller_Component
{
    public function certAction()
    {
        $translate = $this->_owApp->translate;
        OntoWiki::getInstance()->getNavigation()->disableNavigation();
        
        $this->_helper->viewRenderer->setScriptAction('cert1');
        $this->view->placeholder('main.window.title')->set($translate->_('Create Certificate - Step 1'));
        
        require_once 'Erfurt/Auth/Adapter/FoafSsl.php';
        if (!Erfurt_Auth_Adapter_FoafSsl::canCreateCertificates()) {
            $this->view->errorFlag = true;
            require_once 'OntoWiki/Message.php';
            $this->_owApp->appendMessage(new OntoWiki_Message(
                $translate->_('The creation of self signed certificates is not supported.'), 
                OntoWiki_Message::ERROR
            ));
            return;
        }
    
        $this->view->formActionUrl = $this->_config->urlBase . 'auth/cert';
		$this->view->formMethod    = 'post';
		$this->view->formClass     = 'simple-input input-justify-left';
		$this->view->formName      = 'createcert';
        
        $get  = $this->_request->getQuery();
        $post = $this->_request->getPost();
        
        if (empty($get) && empty($post)) {
            // Initial request... check whether a valid cert is already given and show message if yes.
            
            $info = Erfurt_Auth_Adapter_FoafSsl::getCertificateInfo();
            // If $info is false, we have no cert, so we can create one.
            if ($info !== false) {
                if (isset($info['foafPublicKey'])) {
                    // We have a valid id here... we need no cert.
                    $this->view->errorFlag = true;
                    require_once 'OntoWiki/Message.php';
                    $this->_owApp->appendMessage(new OntoWiki_Message(
                        sprintf(
                        $translate->_(
                        'You already have a valid identity that you can use to sign in. Your WebID is: <b>%1$s</b>'), 
                        $info['webId']),
                        OntoWiki_Message::INFO,
                        array(
                            'escape' => false
                    )));
                    return;
                } else {
                    // We have a valid cert, but the foaf data does not contain the public key info... so show it.
                    $this->view->errorFlag = true;
                    
                    $message = '<span>' . 
                        sprintf(
                            $translate->_('You already have a valid certificate, but the FOAF data behind your WebID <b>%1&s</b> does not contain the right public key infos.<br /> You should add the following infos to your FOAF profile: <br /><br /> Modulus <pre>%2$s</pre><br /> Exponent <pre>%3$s</pre>', 
                            $info['webId'], 
                            $info['certPublicKey']['modulus'],
                            hexdec($info['certPublicKey']['exponent'])
                            )
                        ) . '</span>';
                    
                    require_once 'OntoWiki/Message.php';
                    $this->_owApp->appendMessage(new OntoWiki_Message(
                        $message, 
                        OntoWiki_Message::INFO,
                        array(
                            'escape' => false
                    )));
                    return;
                }
            }
            
            // If we reach this, we can show the initial step, where the user enters a webid or generates one.
            $toolbar = $this->_owApp->toolbar;
    		$toolbar->appendButton(OntoWiki_Toolbar::SUBMIT, array('name' => $translate->_('Check WebID')));
    		$this->view->placeholder('main.window.toolbar')->set($toolbar);
            return;
        }
        
        if (!empty($post)) {
            if (isset($post['checkwebid'])) {
                // Step 1: Check the WebID or create one...
                $webId = $post['webid-input'];
                if (trim($webId) === '') {
                    $this->view->name = '';
                    $this->view->email = '';
                } else { 
                    // Check for metadata
                    $foafData = Erfurt_Auth_Adapter_FoafSsl::getFoafData($webId);
                    
                    if (isset($foafData[$webId]['http://xmlns.com/foaf/0.1/name'][0]['value'])) {
                        $this->view->name = $foafData[$webId]['http://xmlns.com/foaf/0.1/name'][0]['value'];
                    } else {
                        $this->view->name = '';
                    }
                    
                    if (isset($foafData[$webId]['http://xmlns.com/foaf/0.1/mbox'][0]['value'])) {
                        $this->view->email = $foafData[$webId]['http://xmlns.com/foaf/0.1/mbox'][0]['value'];
                    } else {
                        $this->view->email = '';
                    }
                    
                    if (isset($foafData[$webId]['http://xmlns.com/foaf/0.1/depiction'][0]['value'])) {
                        $this->view->depiction = $foafData[$webId]['http://xmlns.com/foaf/0.1/depiction'][0]['value'];
                    }
                    
                    $this->view->webid = $webId;
                }
                
                // Show step 2
                $this->_helper->viewRenderer->setScriptAction('cert2');
                $this->view->placeholder('main.window.title')->set($translate->_('Create Certificate - Step 2'));

                $toolbar = $this->_owApp->toolbar;
        		$toolbar->appendButton(OntoWiki_Toolbar::SUBMIT, array('name' => htmlspecialchars($translate->_('Create Certificate & Register'))));
        		$this->view->placeholder('main.window.toolbar')->set($toolbar);
        		
        		// Message to inform the user that after cert creation he needs to reload
        		$message = $translate->_('Please note that you need to return to the start page after certificate creation.');
                     
                require_once 'OntoWiki/Message.php';
                $this->_owApp->appendMessage(new OntoWiki_Message($message, OntoWiki_Message::INFO));
                return;
            } if (isset($post['createcert'])) {
                // Step2: Create the cert...
                $name = $post['name-input'];
                if (trim($name) === '') {
                    // We need a name!
                    $this->view->errorFlag = true;
                    require_once 'OntoWiki/Message.php';
                    $this->_owApp->appendMessage(new OntoWiki_Message(
                        $translate->_('The name field must not be empty.'), 
                        OntoWiki_Message::ERROR
                    ));
                    return;
                }
                
                if (isset($post['webid-input'])) {
                    // WebId given
                    $webId = $post['webid-input'];
                } else {
                    // Autogenerate WebId
                    $webId = $this->_generateWebId(str_replace(' ', '', $name));
                }
                
                
                $email = trim($post['email-input']);
                if ($email !== '' && substr($email, 0, 7) !== 'mailto:') {
                    $email = 'mailto:' . $email;
                }
                
                $cert = Erfurt_Auth_Adapter_FoafSsl::createCertificate(
                    $webId,
                    $name,
                    $email,
                    $post['pubkey']
                );
                
                
                
                // Add the user... 
                $auth = new Erfurt_Auth_Adapter_FoafSsl();
                $success = $auth->addUser($webId);
                
                if ($success !== false) {
                    $store = Erfurt_App::getInstance()->getStore();
                    $bnodePrefix = '_:'.md5($webId);
                    $node1 = $bnodePrefix . '_1';
                    $node2 = $bnodePrefix . '_2';
                    $node3 = $bnodePrefix . '_3';
                    $stmtArray = array(
                        $node1 => array(
                            EF_RDF_TYPE => array(array(
                                'type'  => 'uri',
                                'value' => 'http://www.w3.org/ns/auth/rsa#RSAPublicKey' 
                            )),
                            'http://www.w3.org/ns/auth/cert#identity' => array(array(
                                'type'  => 'uri',
                                'value' => $webId
                            )),
                            'http://www.w3.org/ns/auth/rsa#public_exponent' => array(array(
                                'type'  => 'bnode',
                                'value' => $node2
                            )),
                            'http://www.w3.org/ns/auth/rsa#modulus' => array(array(
                                'type'  => 'bnode',
                                'value' => $node3
                            ))
                        ),
                        $node2 => array(
                            'http://www.w3.org/ns/auth/cert#decimal' => array(array(
                                'type'  => 'literal',
                                'value' => $cert['exponent']
                            ))
                        ),
                        $node3 => array(
                            'http://www.w3.org/ns/auth/cert#hex' => array(array(
                                'type'  => 'literal',
                                'value' => $cert['modulus']
                            ))
                        )
                    );

                    $store->addMultipleStatements('http://localhost/OntoWiki/Config/', $stmtArray, false);
                }
                
                header("Content-Type: application/x-x509-user-cert");
                echo $cert['certData'];
                return;
            }
        }
        
        $config = $this->_config;

        $this->view->formActionUrl = $this->_config->urlBase . 'auth/cert';
		$this->view->formMethod    = 'post';
		$this->view->formClass     = 'simple-input input-justify-left';
		$this->view->formName      = 'createcert';
        $this->view->username      = '';
        $this->view->readonly      = '';
        $this->view->email         = '';
		
		$toolbar = $this->_owApp->toolbar;
		$toolbar->appendButton(OntoWiki_Toolbar::SUBMIT, array('name' => $translate->_('Create Certificate')))
		        ->appendButton(OntoWiki_Toolbar::RESET, array('name' => $translate->_('Reset Form')));
		$this->view->placeholder('main.window.toolbar')->set($toolbar);
		
		
    }
    
    public function usersAction()
    {
// TODO Make sure that no sensilbe information (pw) is exported...
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        
        if (null === $this->_request->id) {
            echo '"id" parameter is missing.';
            return;
        }
        
        $id = $this->_config->urlBase . 'auth/users/id/' . $this->_request->id;
        $modelUri = 'http://localhost/OntoWiki/Config/';    
        $store = $this->_erfurt->getStore();
        
        require_once 'Erfurt/Syntax/RdfSerializer.php';
        $serializer = Erfurt_Syntax_RdfSerializer::rdfSerializerWithFormat('rdfxml');
        echo $serializer->serializeResourceToString($id, $modelUri, false, false);
        
        $response = $this->getResponse();
        $response->setHeader('Content-Type', 'application/rdf+xml', true);
        return;
    }
    
    public function agentAction()
    {
// TODO Do this in a more dynamic way...      

        echo '<rdf:RDF xmlns:cert="http://www.w3.org/ns/auth/cert#"
                 xmlns:foaf="http://xmlns.com/foaf/0.1/"
                 xmlns:rsa="http://www.w3.org/ns/auth/rsa#"
                 xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
        	<foaf:Agent rdf:about="' . $this->_privateConfig->auth->agentId . '" />
        	<rsa:RSAPublicKey>
        		<cert:identity rdf:resource="' . $this->_privateConfig->auth->agentId . '" />
        		<rsa:public_exponent cert:decimal="' . $this->_privateConfig->auth->exponent . '"/>
        		<rsa:modulus cert:hex="' . $this->_privateConfig->auth->modulus . '" />
        	</rsa:RSAPublicKey>
        </rdf:RDF>';
        
        $response = $this->getResponse();
        $response->setHeader('Content-Type', 'application/rdf+xml', true);
        return;
    }
    
    
    private function _generateWebId($suffix = '')
    {
        $base = $this->_config->urlBase . 'auth/users/id/';
        $users = Erfurt_App::getInstance()->getUsers();
        
        $url = $base . $suffix;
        if (!isset($users[$url])) {
            return $url;
        } else {
            $i = 0;
            $url2 = $url . $i;
            
            while (true) {
                if (!isset($users[$url2])) {
                    return $url2;
                } else {
                    $url2 = $url . ++$i;
                }
            }
        }
    }
}
