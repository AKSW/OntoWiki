<?php
class DssnPlugin extends OntoWiki_Plugin
{
    public function init()
    {
        parent::init();
        
        $this->_registerLibrary();
    }
    
    public function onExternalFeedDidChange($event)
    {
        $this->_log('Event onExternalFeedDidChange fired');
        
        $feedObject = DSSN_Activity_Feed_Factory::newFromXml($event->feedData);
        foreach ($feedObject->getActivities() as $activity) {
            $this->_log((string)$activity->toRdf());
        }
    }
    
    private function _log($msg)
    {
        $logger = OntoWiki::getInstance()->getCustomLogger('dssn');
        $logger->debug($msg);        
    }
    
    /*
     * This adds a new path and namespace to the autoloader
     */
    private function _registerLibrary()
    {
        $newIncludePath = ONTOWIKI_ROOT . '/extensions/dssn/libraries/lib-dssn-php';
        set_include_path(get_include_path() . PATH_SEPARATOR . $newIncludePath);
        // see http://framework.zend.com/manual/en/zend.loader.load.html
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace('DSSN_');
        DSSN_Utils::setConstants();
    }
}
