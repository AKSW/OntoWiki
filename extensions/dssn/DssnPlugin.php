<?php
class DssnPlugin extends OntoWiki_Plugin
{
    private $_externalActivitiesModel = null;
    
    /**
     *
     * @var Erfurt_Store
     */
    private $_store = null;
    
    public function init()
    {
        parent::init();
        
        $this->_registerLibrary();
        
        $ow = OntoWiki::getInstance();
        $this->_store = $ow->erfurt->getStore();
        $modelUri = $this->_privateConfig->plugin->externalActivitiesModel;
        
        if ($this->_store->isModelAvailable($modelUri)) {
            $this->_externalActivitiesModel = $this->_store->getModel($modelUri);
        } else {
            $this->_externalActivitiesModel = $this->_store->getNewModel($modelUri, '', Erfurt_Store::MODEL_TYPE_OWL, false);
        }
    }
    
    public function onExternalFeedDidChange($event)
    {
        $this->_log('Event onExternalFeedDidChange fired');
        
        $feedObject = DSSN_Activity_Feed_Factory::newFromXml($event->feedData);
        foreach ($feedObject->getActivities() as $activity) {
            // ob_start();
            //             print_r($activity->toRDF());
            //             $result = ob_get_clean();
            //             $this->_log('RDF: '. $result);
            
            try {
                $this->_store->addMultipleStatements((string)$this->_externalActivitiesModel, $activity->toRDF());
                
                $this->_log('Added external activity: ' . $activity);
            } catch (Exception $e) {
                $this->_log('Failed adding external activity: ' . $activity);
            }
        }
    }
    
    public function onPingReceived($event)
    {
        //TODO add a user confirm dialog here
        if($event->p == DSSN_FOAF_knows){
            DssnController::handleNewFriend($event->o, $event->s, $this->_store, $this->_store->getModel($event->o));
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
