<?php

/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * HTTP Authentication plug-in
 *
 * Provides authentication via HTTP simple method.
 *
 * @category OntoWiki
 * @package OntoWiki_Classes_Controller_Plugin
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @author Norman Heino <norman.heino@gmail.com>
 */
class OntoWiki_Controller_Plugin_HttpAuth extends Zend_Controller_Plugin_Abstract
{
    /**
     * Retieves user credentials from the current request and tries to 
     * authenticate the user with Erfurt.
     *
     * @param Zend_Controller_Request_Abstract $request The current request object
     */
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        if ($credentials = $this->_getAuthHeaderCredentials($request)) {
            switch ($credentials['type']) {
                case 'basic':
                    $erfurt = OntoWiki::getInstance()->erfurt;
                    $logger = OntoWiki::getInstance()->logger;
                    // authenticate
                    $authResult = $erfurt->authenticate($credentials['username'], $credentials['password']);
                    if ($authResult->isValid()) {
                        $logger = OntoWiki::getInstance()->logger;
                        $logger->info("User '$credentials[username]' authenticated via HTTP.");
                    } else {
                        // if authentication attempt fails, send appropriate headers
                        $front    = Zend_Controller_Front::getInstance();
                        $response = $front->getResponse();
                        $response->setRawHeader('HTTP/1.1 401 Unauthorized');
                        echo 'HTTP/1.1 401 Unauthorized';
                        return;
                    }
                    break;
                case 'foaf+ssl':
                    $adapter = new Erfurt_Auth_Adapter_FoafSsl();
                    
                    $authResult = $adapter->authenticateWithCredentials($credentials['creds']);
                    Erfurt_App::getInstance()->getAuth()->setIdentity($authResult);
                    
                    if ($authResult->isValid()) {
                        $logger = OntoWiki::getInstance()->logger;
                        $logger->info("User authenticated with FOAF+SSL via HTTPS.");
                    }
                    break;
            }
        }
    }
    
    /**
     * Fetches authentication credentials from the current request
     *
     * @param Zend_Controller_Request_Abstract $request The current request object
     * @return array
     */
    private function _getAuthHeaderCredentials(Zend_Controller_Request_Abstract $request)
    {
        $authHeader  = $request->getHeader('Authorization');
		if (is_string($authHeader) && strlen($authHeader) > 0) {
		    if (strtolower(substr($authHeader, 0, 8)) === 'foaf+ssl') {
		        $auth  = base64_decode(substr($authHeader, 9));
		        $creds = explode('=', $auth);
		        foreach ($creds as &$c) {
		            if (substr($c, 0, 1) === '"') {
		                $c = substr($c, 1, -1);
		            }
		        }
		        
		        if (count($creds) > 0) {
		            return array(
		                'type'  => 'foaf+ssl',
		                'creds' => $creds
		            );
		        }
		    } else if (strtolower(substr($authHeader, 0, 5)) === 'basic') {
		        $auth  = base64_decode(substr($authHeader, 6));
        		$creds = array_filter(explode(':', $auth));
        		if (count($creds) > 0) {
        		    return array(
        		        'type'     => 'basic',
            		    'username' => $creds[0], 
            		    'password' => isset($creds[1]) ? $creds[1] : ''
            		);
        		}
		    }
		}	
    }
}


