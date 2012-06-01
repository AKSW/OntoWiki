<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

/**
 * OntoWiki debug controller.
 *
 * @package    OntoWiki_Controller
 * @author     Norman Heino <norman.heino@gmail.com>
 * @copyright  Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license    http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
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
        
        $this->_redirect($_SERVER['HTTP_REFERER'] , array('code' => 302));
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
        
        $this->_redirect($_SERVER['HTTP_REFERER'] , array('code' => 302));
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
        
        $this->_redirect($_SERVER['HTTP_REFERER'] , array('code' => 302));
        
    }
}
