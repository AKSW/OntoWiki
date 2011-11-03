<?php

/**
 * OntoWiki debug controller.
 * 
 * @package    application
 * @subpackage mvc
 * @author     Norman Heino <norman.heino@gmail.com>
 * @copyright  Copyright (c) 2008, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @version    $Id: DebugController.php 3687 2009-07-16 07:32:29Z c.riess.dev $
 */
class DebugController extends OntoWiki_Controller_Base
{
    /**
     * Clears the module cache
     *
     * @return void
     */
    public function clearmodulecacheAction()
    {
        $this->view->clearModuleCache();
        
        $this->_redirect($this->_config->urlBase);
    }
    
    /**
     * Clears the translation cache
     *
     * @return void
     */
    public function cleartranslationcacheAction()
    {
        if (Zend_Translate::hasCache()) {
            Zend_Translate::clearCache();
        }
        
        $this->_redirect($this->_config->urlBase);
    }
    
    /**
     * Destroys the current session
     *
     * @return void
     */
    public function destroysessionAction()
    {
        Zend_Session::destroy(true);
        
        $this->_redirect($this->_config->urlBase);
    }

    /**
     * Destroys complete query cache and object cache
     *
     * @return void
     */
    public function clearquerycacheAction()
    {
        $queryCache = $this->_erfurt->getQueryCache();
        $queryCacheReturnValue = $queryCache->cleanUpCache( array('mode' => 'uninstall') );

        $objectCache = $this->_erfurt->getCache();
        $objectCacheReturnValue = $objectCache->clean();
        
        $this->_redirect($this->_config->urlBase);
        
    }
}
