<?php
class HubNotificationModel
{
    // TODO: If-Modified-Since, If-None-Match
    public function addNotification($data = array())
    {
        $this->_checkData();
        $this->_checkTable();
        
        $url = $data['hub.url'];
        $time = time();
        
        $sql = 'INSERT INTO `ef_pubsub_hubnotification` (`publisher_url`, `notification_time`) VALUES (
        "' . $url . '", ' . $time . ')';
        
        $this->_sqlQuery($sql);
    }
    
    public function hasNotification($data = array())
    {
        $this->_checkData();
        $this->_checkTable();
        
        $url = $data['hub.url'];
        $sql = 'SELECT count(*) FROM `ef_pubsub_hubnotification` WHERE `publisher_url` = "' . $url . '"';
        
        return (boolean)$this->_sqlQuery($sql);
    }
    
    public function deleteNotification($data = array())
    {
        $this->_checkData();
        $this->_checkTable();
        
        $url = $data['hub.url'];
        $sql = 'DELETE FROM `ef_pubsub_hubnotification` WHERE `publisher_url` = "' . $url . '"';
        
        $this->_sqlQuery($sql);
    }
    
    public function updateNotification($data = array())
    {
        $this->_checkData();
        $this->_checkTable();
        
        $url = $data['hub.url'];
        
        $updateKeys = array();
        $updateValues = array();
        if (isset($data['last_fetched'])) {
            $updateKeys[]   = '`last_fetched`';
            $updateValues[] = $data['last_fetched'];
        }
        
        $updatesUnited = array();
        foreach ($updateKeys as $i=>$key) {
            $updatesUnited[] = $key . ' = ' . $updateValues[$i];
        }
        
        $sql = 'UPDATE `ef_pubsub_hubnotification` SET ' . implode(', ', $updatesUnited) . ' WHERE `publisher_url` = "' . $url . '"';
        
        $this->_sqlQuery($sql);        
    }
    
    public function getNotifications()
    {
        $this->_checkTable();
        
        $sql = 'SELECT * FROM `ef_pubsub_hubnotification` ORDER BY `notification_time` ASC';
        
        $retVal = array();
        $result = $this->_sqlQuery($sql);
        foreach ($result as $row) {
            $retRow = array();
            $retRow['hub.url'] = $row['publisher_url'];
            $retRow['last_fetched'] = $row['last_fetched'];
            $retRow['notification_time'] = $row['notification_time'];
            
            $retVal[] = $retRow;
        }
        
        return $retVal;
    }
    
    private function _checkTable()
    {
        $tableSql = 'CREATE TABLE IF NOT EXISTS `ef_pubsub_hubnotification` (
          `id` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT "",
          `publisher_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
          `last_fetched` int DEFAULT 0,
          `notification_time` int DEFAULT 0,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';
        
        $this->_store->sqlQuery($tableSql);
    }
    
    private function _checkData($data)
    {
        if (!isset($data['hub.url'])) {
            throw new Exception('hub.url param required');
        }
    }
    
    private function _sqlQuery($sql)
    {
        return Erfurt_App::getInstance()->getStore()->sqlQuery($sql);
    }
}
