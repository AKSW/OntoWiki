class visualphpunit {
    
    $ow_server_name = $ubuntu::ow_server_name
    $vpu_server_name = "vpuow.${ow_server_name}"
    $vpu_path = '/var/www/vpu'
    
    $ow_test_dir = "/vagrant/application/tests/"
    
    $mysql_root_pw = $ubuntu::mysql_root_pw
    $mysql_user = $ubuntu::mysql_user
    $mysql_pw = $ubuntu::mysql_pw

    exec { "git-clone-vpu":
        cwd => "/var/www",
        command => "/usr/bin/git clone https://github.com/NSinopoli/VisualPHPUnit.git vpu",
        creates => "/var/www/vpu",
        require => [Package["apache2"], Package["git"]]
    }
    
    file { "/var/www/vpu":
        ensure => "directory",
        require => Exec["git-clone-vpu"]
    }
    
    exec { "git-checkout-tag":
        cwd => "/var/www/vpu",
        command => "/usr/bin/git checkout v2.0",
        require => [Exec["git-clone-vpu"], File["/var/www/vpu"]]
    }

    file { "/var/www/vpu/app/config/bootstrap.php":
        ensure => present,
        replace => true,
        owner   => "root",
        group   => "root",
        mode    => "0644",
        content => template("visualphpunit/bootstrap.php"),
        require => [Exec["git-checkout-tag"], File["/var/www/vpu"]]
    }
    
    file { "/var/www/vpu/app/resource/cache":
        ensure => "directory",
        owner   => "root",
        group   => "root",
        mode    => "0777",
        require => [Exec["git-checkout-tag"], File["/var/www/vpu"]]
    }

    exec { "create-vpu_ow-db":
        unless => "/usr/bin/mysql -uroot -p${mysql_root_pw} vpu_ow",
        command => "/usr/bin/mysql -uroot -p${mysql_root_pw} -e \"create database vpu_ow;\"",
        require => [Service["mysql"], Exec["set-mysql-password"]]
    }
    
    exec { "grant-mysql-vpu_ow-db-to-php-user":
        unless => "/usr/bin/mysql -u${$mysql_user} -p${mysql_pw} vpu_ow",
        command => "/usr/bin/mysql -uroot -p${mysql_root_pw} -e \"grant all on vpu_ow.* to ${mysql_user}@'localhost' identified by '${mysql_pw}';\"",
        require => [Service["mysql"], Exec["create-vpu_ow-db"]]
    }
    
    file { "/etc/apache2/sites-available/004-vpu_ow":
        ensure => present,
        content => template("visualphpunit/vhost"),
        replace => true,
        require => Package["apache2"]
    }

    exec { "enable-vpu_ow":
        path => ["/bin", "/usr/bin"],
        command => "/usr/sbin/a2ensite 004-vpu_ow ; /etc/init.d/apache2 restart",
        creates => "/etc/apache2/sites-enabled/004-vpu_ow",
        require => [Package["apache2"], File["/etc/apache2/sites-available/004-vpu_ow"]],
    }
}