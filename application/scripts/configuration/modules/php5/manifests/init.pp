class php5 {

    # php related packages
    package { ["php5", "libapache2-mod-php5", "php5-mysql", "php5-cli", "php5-common",  "php5-tidy", "php5-xdebug", "php5-xsl", "php5-xmlrpc", "php5-odbc", "php5-gd", "php-apc"]:
        ensure => installed,
        require => Exec["apt-update"]
    }
    # "php5-suhosin", "php5-imap", "php5-mcrypt", "php5-memcache"
    package { ["phpmyadmin"]:
        ensure => installed,
        require => Package["php5"]
    }

    # xdebug
    file { "/etc/php5/conf.d/xdebug.ini":
        ensure => present,
        replace => true,
        owner   => "root",
        group   => "root",
        mode    => "0644",
        source => "puppet:///modules/php5/xdebug.ini",
        require => Package["php5-xdebug"]
    }
  
    # zend
    file { "/etc/php5/conf.d/custom_settings.ini":
        ensure => present,
        replace => true,
        owner   => "root",
        group   => "root",
        mode    => "0644",
        source => "puppet:///modules/php5/custom_settings.ini",
        require => Package["php5"]
    }
    
    # apc
    file { "/etc/php5/conf.d/apc.ini":
        ensure => present,
        replace => true,
        owner   => "root",
        group   => "root",
        mode    => "0644",
        source => "puppet:///modules/php5/apc.ini",
        require => Package["php5"]
    }
    
    # disable php-fpm since not used yet
    service { "php5-fpm":
        ensure => stopped,
        enable => false,
        require => Package["php5"],
    }
}
