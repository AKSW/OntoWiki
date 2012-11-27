class apache2 {

    $ow_server_name         = $ubuntu::ow_server_name
    $ow_application_env     = $ubuntu::ow_application_env
    $ow_document_root       = "/vagrant"
    $phpmyadmin_server_name = "phpmyadmin.${ow_server_name}"
    $jenkins_server_name    = "jenkins.${ow_server_name}"

    # apache2
    package { ["apache2"]:
        ensure => installed,
        require => Exec["apt-update"]
    }

    # copy vhost configurations
    file { "/etc/apache2/sites-available/default":
        ensure => present,
        content => template("apache2/default"),
        replace => true;
    }
    file { "/etc/apache2/sites-available/001-ontowiki":
        ensure => present,
        content => template("apache2/001-ontowiki"),
        replace => true;
    }
    file { "/etc/apache2/sites-available/002-phpmyadmin":
        ensure => present,
        content => template("apache2/002-phpmyadmin"),
        replace => true;
    }
    #file { "/etc/apache2/sites-available/003-jenkins":
    #    ensure => present,
    #    content => template("apache2/003-jenkins"),
    #    replace => true;
    #}

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
    file { "/var/www/index.html":
        ensure => present,
        replace => true,
        owner   => "root",
        group   => "root",
        mode    => "0755",
        source => "puppet:///modules/apache2/index.html",
        require => Package["apache2"]
    }

    
    # enable modules and sites
    exec { "enable-mod-rewrite":
        command => "/usr/sbin/a2enmod rewrite ; /etc/init.d/apache2 restart",
        creates => "/etc/apache2/mods-enabled/rewrite.load",
        require => Package["apache2"],
    }
    exec { "enable-mod-proxy":
        command => "/usr/sbin/a2enmod proxy ; /etc/init.d/apache2 restart",
        creates => "/etc/apache2/mods-enabled/proxy.load",
        require => Package["apache2"],
    }
    exec { "enable-mod-proxy_http":
        command => "/usr/sbin/a2enmod proxy_http ; /etc/init.d/apache2 restart",
        creates => "/etc/apache2/mods-enabled/proxy_http.load",
        require => Package["apache2"],
    }
    exec { "enable-ontowiki":
        path => ["/bin", "/usr/bin"],
        command => "/usr/sbin/a2ensite 001-ontowiki ; /etc/init.d/apache2 restart",
        creates => "/etc/apache2/sites-enabled/001-ontowiki",
        require => [Package["apache2"], File["/etc/apache2/sites-available/001-ontowiki"]],
    }
    exec { "enable-phpmyadmin":
        path => ["/bin", "/usr/bin"],
        command => "/usr/sbin/a2ensite 002-phpmyadmin ; /etc/init.d/apache2 restart",
        creates => "/etc/apache2/sites-enabled/002-phpmyadmin",
        require => [Package["apache2"], Package["phpmyadmin"], File["/etc/apache2/sites-available/002-phpmyadmin"]],
    }
    #exec { "enable-jenkins":
    #    path => ["/bin", "/usr/bin"],
    #    command => "/usr/sbin/a2ensite 003-jenkins ; /etc/init.d/apache2 restart",
    #    creates => "/etc/apache2/sites-enabled/003-jenkins",
    #    require => [Package["apache2"], Package["jenkins"], File["/etc/apache2/sites-available/003-jenkins"],Exec["enable-mod-proxy"], Exec["enable-mod-proxy_http"]],
    #}

    # enable apache2 server
    service { "apache2":
        ensure => running,
        enable => true,
        hasrestart => true,
        require => Package["apache2"],
    }
}
