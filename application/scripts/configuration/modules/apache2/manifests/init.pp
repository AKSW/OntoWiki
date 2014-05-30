class apache2 {

    $ow_server_name         = $ubuntu::ow_server_name
    $ow_application_env     = $ubuntu::ow_application_env
    $ow_document_root       = "/vagrant"
    
    # apache2
    package { 'apache2':
        ensure => installed
    }

    file { '/etc/apache2/sites-available/001-ontowiki':
        ensure => present,
        content => template("apache2/001-ontowiki"),
        replace => true,
        require => Package["apache2"]
    }
    
    # copy/remove files from default /var/www directory
    file { "/var/www/phpinfo.php":
        ensure => present,
        replace => true,
        owner   => "root",
        group   => "root",
        mode    => "0755",
        source => "puppet:///modules/apache2/phpinfo.php",
        require => Package["apache2"]
    }
    file { "/var/www/odbctest.php":
        ensure => present,
        replace => true,
        owner   => "root",
        group   => "root",
        mode    => "0755",
        source => "puppet:///modules/apache2/odbctest.php",
        require => Package["apache2"]
    }
    file { "/var/www/apc.php":
        ensure => present,
        replace => true,
        owner   => "root",
        group   => "root",
        mode    => "0755",
        source => "puppet:///modules/apache2/apc.php",
        require => Package["apache2"]
    }
    
    # enable modules and sites
    exec { "enable-mod-rewrite":
        command => "/usr/sbin/a2enmod rewrite",
        creates => "/etc/apache2/mods-enabled/rewrite.load",
        require => Package["apache2"],
    }
    #exec { "enable-mod-proxy":
    #    command => "/usr/sbin/a2enmod proxy",
    #    creates => "/etc/apache2/mods-enabled/proxy.load",
    #    require => Package["apache2"],
    #}
    #exec { "enable-mod-proxy_http":
    #    command => "/usr/sbin/a2enmod proxy_http",
    #    creates => "/etc/apache2/mods-enabled/proxy_http.load",
    #    require => Package["apache2"],
    #}
    exec { "enable-ontowiki":
        path => ["/bin", "/usr/bin"],
        command => "/usr/sbin/a2ensite 001-ontowiki ; /etc/init.d/apache2 restart",
        creates => "/etc/apache2/sites-enabled/001-ontowiki",
        require => [Exec["enable-mod-rewrite"], File["/etc/apache2/sites-available/001-ontowiki"]],
    }
    
    # enable apache2 server
    service { "apache2":
        ensure => running,
        enable => true,
        hasrestart => true,
        require => Package["apache2"],
    }
}
