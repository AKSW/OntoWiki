<?php
class Mail_Job_Mail implements Erfurt_Worker_Job_Interface{

    private $_transport = null;
    private $_privateConfig = null;

    public function __construct($config = NULL)
    {
        $this->_owApp           = OntoWiki::getInstance();
        $this->_config          = $this->_owApp->config;
        $this->_privateConfig   = $config;
    }
    
    public function run(GearmanJob $job){
        $smtpServer = $this->_privateConfig->smtp->server;
        $config = array();
        if ($this->_privateConfig->smtp->auth){
            $config['auth']      = $this->_privateConfig->smtp->auth;
            $config['username']  = $this->_privateConfig->smtp->username;
            $config['password']  = $this->_privateConfig->smtp->password;
        }
        $this->_transport = new Zend_Mail_Transport_Smtp($smtpServer, $config);

        $workload   = json_decode($job->workload());
        if (is_object($workload)){
            $mail = new Zend_Mail();
            $mail->setDefaultTransport($this->_transport);
            $mail->addTo($workload->receiver);
            $mail->setSubject($workload->subject);
            $mail->setBodyText($workload->body);
            $mail->setFrom($workload->sender);
            $mail->send($this->_transport);
            print("Sent mail to ".$workload->subject.".\n");
        }
    }
}