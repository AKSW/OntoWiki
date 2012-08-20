class virtuoso {

  # virtuoso
  package { "virtuoso-opensource": ensure => installed, require => Exec["apt-update"] }

  # copy ODBC config files
  file { "/etc/odbc.ini":
    ensure => present,
    replace => true,
    owner   => "root",
    group   => "root",
    mode    => "0755",
    source => "puppet:///modules/virtuoso/odbc.ini",
    require => Package["virtuoso-opensource"]
  }

  # copy init.d scripts
  file { "/etc/init.d/ontowiki-dev":
    ensure => present,
    replace => true,
    owner   => "root",
    group   => "root",
    mode    => "0755",
    source => "puppet:///modules/virtuoso/ontowiki-dev",
    require => Package["virtuoso-opensource"]
  }
  file { "/etc/init.d/ontowiki-test":
    ensure => present,
    replace => true,
    owner   => "root",
    group   => "root",
    mode    => "0755",
    source => "puppet:///modules/virtuoso/ontowiki-test",
    require => Package["virtuoso-opensource"]
  }
  
  # copy database ini files
  file { ["/var/lib/virtuoso", "/var/lib/virtuoso/ontowiki-dev", "/var/lib/virtuoso/ontowiki-test"]:
    ensure => "directory"
  }
  file { "/var/lib/virtuoso/ontowiki-dev/ontowiki-dev.ini":
    ensure => present,
    replace => true,
    owner   => "root",
    group   => "root",
    mode    => "0644",
    source => "puppet:///modules/virtuoso/ontowiki-dev.ini",
    require => [Package["virtuoso-opensource"], File["/var/lib/virtuoso/ontowiki-dev"]]
  }
  file { "/var/lib/virtuoso/ontowiki-test/ontowiki-test.ini":
    ensure => present,
    replace => true,
    owner   => "root",
    group   => "root",
    mode    => "0644",
    source => "puppet:///modules/virtuoso/ontowiki-test.ini",
    require => [Package["virtuoso-opensource"], File["/var/lib/virtuoso/ontowiki-test"]]
  }

  service { "ontowiki-dev":
    enable => true,
    ensure => running,
    hasrestart => true,
    require => [Package["virtuoso-opensource"], File["/etc/init.d/ontowiki-dev"], File["/var/lib/virtuoso/ontowiki-dev/ontowiki-dev.ini"]],
  }
  service { "ontowiki-test":
    enable => true,
    ensure => running,
    hasrestart => true,
    require => [Package["virtuoso-opensource"], File["/etc/init.d/ontowiki-test"], File["/var/lib/virtuoso/ontowiki-test/ontowiki-test.ini"]],
  }
}