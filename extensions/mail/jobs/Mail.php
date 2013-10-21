<?php
class Mail_Job_Mail implements Erfurt_Worker_Job_Interface{

    private $_transport = null;
    private $_options   = null;

    public function __construct($options = NULL)
    {
        $this->_owApp       = OntoWiki::getInstance();
        $this->_config      = $this->_owApp->config;
        $this->_options     = $options;
    }

    public function run(GearmanJob $job){
        $smtpServer = $this->_options['server'];
        $config = array();
        if ($this->_options['auth']){
            $config['auth']      = $this->_options['auth'];
            $config['username']  = $this->_options['username'];
            $config['password']  = $this->_options['password'];
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