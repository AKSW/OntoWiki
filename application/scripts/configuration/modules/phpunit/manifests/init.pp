class phpunit {

  # package
  package { "php-pear": 
    ensure => installed,
    require => Exec["apt-update"]
  }

  exec { "upgrade-pear":
    command => "/usr/bin/pear upgrade PEAR",
    creates => "/usr/bin/phpunit",
    require => Package["php-pear"],
  }

  exec { "pear-config-set-auto-discover":
    command => "/usr/bin/pear config-set auto_discover 1",
    creates => "/usr/bin/phpunit",
    require => [Package["php-pear"], Exec["upgrade-pear"]]
  }

  exec { "pear-install-phpunit":
    command => "/usr/bin/pear install pear.phpunit.de/PHPUnit",
    creates => "/usr/bin/phpunit",
    require => [Exec["upgrade-pear"], Exec["pear-config-set-auto-discover"]]
  }
}