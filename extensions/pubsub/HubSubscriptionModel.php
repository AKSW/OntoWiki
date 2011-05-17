<?php
class HubSubscriptionModel
{
    const MAX_RETRIES = 10;
    
    static $testMode = false;
    static $testData = array();
    
    public function addSubscription($data = array())
    {
        $this->_checkData($data);
        $this->_checkTable();
        
        $created = time();
        
        $expirationTime = null;
        if (isset($data['hub.lease_seconds'])) {
            $expirationTime = $created + $data['hub.lease_seconds'];
        } else {
            $expirationTime = $created + PubsubController::DEFAULT_LEASE_SECONDS;
        }
        
        $sql = 'INSERT INTO `ef_pubsub_hubsubscription` (`topic_url`, `callback_url`, `created_time`, `lease_seconds`,
        `verify_token`, `secret`, `expiration_time`, `subscription_state`, `challenge`, `verify_type`, 
        `number_of_retries`) VALUES (
            "' . $data['hub.topic'] . '",
            "' . $data['hub.callback'] . '",
            '  . $created . ',
            '  . (isset($data['hub.lease_seconds']) ? $data['hub.lease_seconds'] : 'NULL' ) . ',
            "' . (isset($data['hub.verify_token']) ? $data['hub.verify_token'] : 'NULL' ). '",
            "' . (isset($data['hub.secret']) ? $data['hub.secret'] : 'NULL' ). '",
            '  . $expirationTime . ',
            "pending",
            "' . $data['hub.challenge'] . '",
            "' . $data['hub.verify'] . '",
            0
        );';
        
        if (!self::$testMode) {
            $this->_sqlQuery($sql);
        } else {
            $data['created_time'] = $created;
            $data['expiration_time'] = $expirationTime;
            $data['subscription_state'] = 'pending';
            
            self::$testData[] = $data;
        }
    }
    
    public function hasSubscription($data = array())
    {
        if (!isset($data['hub.topic'])) {
            throw new Exception('hub.topic parameter is missing');
        }
        $topic = $data['hub.topic'];
        
        if (!isset($data['hub.callback'])) {
            throw new Exception('hub.callback parameter is missing');
        }
        $callback = $data['hub.callback'];
        
        $this->_checkTable();
        
        $sql = 'SELECT count(*) FROM `ef_pubsub_hubsubscription` WHERE `topic_url` = "' . $topic . '" AND 
        `callback_url` = "' . $callback . '"';
        
        if (!self::$testMode) {
            return (boolean)$this->_sqlQuery($sql);
        } else {
            foreach (self::$testData as $i=>$spec) {
                if (($spec['hub.topic'] === $data['hub.topic']) && ($spec['hub.callback'] === $data['hub.callback'])) {
                    return true;
                }
            }
            
            return false;
        }
    }
    
    public function deleteSubscription($data = array())
    {
        if (!isset($data['hub.topic'])) {
            throw new Exception('hub.topic parameter is missing');
        }
        $topic = $data['hub.topic'];
        
        if (!isset($data['hub.callback'])) {
            throw new Exception('hub.callback parameter is missing');
        }
        $callback = $data['hub.callback'];
        
        $this->_checkTable();
        
        $sql = 'DELETE FROM `ef_pubsub_hubsubscription` WHERE `topic_url` = "' . $topic . '" AND 
        `callback_url` = "' . $callback . '"';
        
        if (!self::$testMode) {
            $this->_sqlQuery($sql);
        } else {
            foreach (self::$testData as $i=>$spec) {
                if (($spec['hub.topic'] === $data['hub.topic']) && ($spec['hub.callback'] === $data['hub.callback'])) {
                    unset(self::$testData[$i]);
                    break;
                }
            }
        }
    }
    
    public function updateSubscription($data = array())
    {
        if (!isset($data['hub.topic'])) {
            throw new Exception('hub.topic parameter is missing');
        }
        $topic = $data['hub.topic'];
        
        if (!isset($data['hub.callback'])) {
            throw new Exception('hub.callback parameter is missing');
        }
        $callback = $data['hub.callback'];
        
        $updateKeys = array();
        $updateValues = array();
        if (isset($data['subscription_state'])) {
            $updateKeys[]   = '`subscription_state`';
            $updateValues[] = '"' . $data['subscription_state'] . '"';
        }
        if (isset($data['number_of_retries'])) {
            $updateKeys[]   = '`number_of_retries`';
            $updateValues[] = '`number_of_retries` + 1';
        }
        
        $updatesUnited = array();
        foreach ($updateKeys as $i=>$key) {
            $updatesUnited[] = $key . ' = ' . $updateValues[$i];
        }
        
        $this->_checkTable();
        
        $sql = 'UPDATE `ef_pubsub_hubsubscription` SET ' . implode(', ', $updatesUnited) . ' WHERE `topic_url` = "' . $topic . '" AND `callback_url` = "' . $callback . '"';
        
        if (!self::$testMode) {
            $this->_sqlQuery($sql);
        } else {
            foreach (self::$testData as $i=>$spec) {
                if (($spec['hub.topic'] === $data['hub.topic']) && ($spec['hub.callback'] === $data['hub.callback'])) {
                    $matchingData = self::$testData[$i];
                    foreach ($updateKeys as $j=>$key) {
                        if ($key === 'number_of_retries') {
                            $matchingData[$key] = $matchingData[$key] + 1;
                        }
                        
                        
                    }
                    break;
                }
            }
        }
    }
    
    public function getSubscriptionsForTopic($topicUrl)
    {
        $this->_checkTable();
        
        $sql = 'SELECT * FROM `ef_pubsub_hubsubscription`
        WHERE `subscription_state` = "active" AND `topic_url` = "' . $topicUrl . '"';
        
        if (self::$testMode) {
            $result = array();
            foreach (self::$testData as $i=>$spec) {
                if (($spec['hub.topic'] === $data['hub.topic']) && ($spec['subscription_state'] === 'active')) {
                    $result[] = $spec;
                }
            }
            
            return $result;
        }
        
        $retVal = array();
        $result = $this->_sqlQuery($sql);
        foreach ($result as $row) {
            $retRow = array();
            $retRow['hub.topic'] = $row['topic_url'];
            $retRow['hub.callback'] = $row['callback_url'];
            $retRow['hub.lease_seconds'] = $row['lease_seconds'];
            $retRow['hub.verify_token'] = $row['verify_token'];
            $retRow['hub.secret'] = $row['secret'];
            $retRow['hub.challenge'] = $row['challenge'];
            
            $retVal[] = $retRow;
        }
        
        return $retVal;
    }
    
    public function getPendingAsyncVerifications()
    {
        $this->_checkTable();
        
        $sql = 'SELECT * FROM `ef_pubsub_hubsubscription` 
        WHERE `verify_token` = "async" AND `subscription_state` = "pending" ORDER BY `number_of_retries` ASC';
        
        if (self::$testMode) {
            $result = array();
            foreach (self::$testData as $i=>$spec) {
                if (($spec['verify_token'] === 'async') && ($spec['subscription_state'] === 'pending')) {
                    $result[] = $spec;
                }
            }
            
            return $result;
        }
        
        $retVal = array();
        $result = $this->_sqlQuery($sql);
        foreach ($result as $row) {
            $retRow = array();
            $retRow['hub.topic'] = $row['topic_url'];
            $retRow['hub.callback'] = $row['callback_url'];
            $retRow['hub.lease_seconds'] = $row['lease_seconds'];
            $retRow['hub.verify_token'] = $row['verify_token'];
            $retRow['hub.secret'] = $row['secret'];
            $retRow['hub.challenge'] = $row['challenge'];
            
            $retVal[] = $retRow;
        }
        
        return $retVal;
    }
    
    public function removeTimedOutPendingSubscriptions()
    {
        $this->_checkTable();
        
        $sql = 'DELETE FROM  `ef_pubsub_hubsubscription`
        WHERE `subscription_state` = "pending" AND `number_of_retries` >= ' . self::MAX_RETRIES;
        
        if (!self::$testMode) {
            $this->_sqlQuery($sql);
        } else {
            foreach (self::$testData as $i=>$spec) {
                if (($spec['number_of_retries'] >= self::MAX_RETRIES) && ($spec['subscription_state'] === 'pending')) {
                    unset(self::$testData[$i]);
                }
            }
        }
    }
    
    private function _checkTable()
    {
        $tableSql = 'CREATE TABLE IF NOT EXISTS `ef_pubsub_hubsubscription` (
          `id` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
          `topic_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `callback_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `created_time` datetime DEFAULT NULL,
          `lease_seconds` bigint(20) DEFAULT NULL,
          `verify_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `secret` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `expiration_time` datetime DEFAULT NULL,
          `subscription_state` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL,
          `challenge` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `verify_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `number_of_retries` bigint(20) DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';
        
        if (!self::$testMode) {
            $this->_store->sqlQuery($tableSql);
        }
    }
    
    private function _checkData($data)
    {
        if (!isset($data['hub.topic'])) {
            throw new Exception('hub.topic parameter is missing');
        }
        if (!isset($data['hub.callback'])) {
            throw new Exception('hub.callback parameter is missing');
        }
        if (!isset($data['hub.challenge'])) {
            throw new Exception('hub.challenge parameter is missing');
        }
        if (!isset($data['hub.verify'])) {
            throw new Exception('hub.verify parameter is missing');
        }
    }
    
    private function _sqlQuery($sql)
    {
        return Erfurt_App::getInstance()->getStore()->sqlQuery($sql);
    }
}
