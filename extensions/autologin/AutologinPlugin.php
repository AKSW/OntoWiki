<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once 'OntoWiki/Plugin.php';

/**
 * OntoWiki FOAF+SSL autologin plug-in
 *
 * @category   OntoWiki
 * @package    Extensions_Autologin
 * @author     Philipp Frischmuth <pfrischmuth@googlemail.com>
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */
class AutologinPlugin extends OntoWiki_Plugin
{
    public function onRouteShutdown($event)
    {      
        if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on' && extension_loaded('openssl')) {
            $app = Erfurt_App::getInstance();
            
            if ($app->getAuth()->getIdentity()->isAnonymousUser()) {
                $result = $app->authenticateWithFoafSsl();

                if ($result->isValid()) {
                    // Redirect to referer page...
                    require_once 'Zend/Controller/Front.php';
                    $front = Zend_Controller_Front::getInstance()->getResponse()->setRedirect($_SERVER['HTTP_REFERER']);
                }
            }
        } 
    }
}

