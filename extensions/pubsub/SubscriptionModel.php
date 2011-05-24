<?php
class SubscriptionModel implements Zend_Feed_Pubsubhubbub_Model_SubscriptionInterface
{
    protected $_store = null;
    
    public function __construct()
    {
        $this->_store = Erfurt_App::getInstance()->getStore();
    }
    
    public function setSubscription(array $data)
    {
        $sql = 'INSERT INTO `ef_pubsub_subscription` (topic_url, hub_url, created_time, lease_seconds, verify_token, secret, expiration_time, subscription_state) VALUES (
            
        )';
    }
    
    public function getSubscription($key)
    {
        
    }
    
    public function hasSubscription($key)
    {
        
    }
    
    public function deleteSubscription($key)
    {
        
    }
    
    private function _checkTable()
    {
        $tableSql = 'CREATE TABLE IF NOT EXISTS `ef_pubsub_subscription` (
          `id` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
          `topic_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `hub_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `created_time` datetime DEFAULT NULL,
          `lease_seconds` bigint(20) DEFAULT NULL,
          `verify_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `secret` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `expiration_time` datetime DEFAULT NULL,
          `subscription_state` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';
        
        $this->_store->sqlQuery($tableSql);
    }
}