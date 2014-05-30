stage { 'preinstall':
  before => Stage['main']
}
 
class apt_get_update {
  exec { '/usr/bin/apt-get -y update': }
}
 
class { 'apt_get_update':
  stage => preinstall
}

class ubuntu 
{
    $ow_server_name     = 'ontowiki.local'
    $ow_application_env = 'development'

    $webserver_user = 'vagrant'
    $webserver_group = 'vagrant'

    $mysql_user = 'php'
    $mysql_pw = 'php'
    $mysql_root_pw = 'ontowiki123'

    $zend_version = '1.11.7'

    include bootstrap
    include apache2
    include php5
    include mysql
    include virtuoso
    include phpunit
    include phpqatools
    include zend
    include ontowiki
}

include ubuntu
