class mysql {

  $mysql_root_pw = $ubuntu::mysql_root_pw

  # mysql
  package { "mysql-server": ensure => installed, require => Exec["apt-update"] }
  package { "mysql-client": ensure => installed, require => Exec["apt-update"] }

  # mysql service
  service { "mysql":
    enable => true,
    ensure => running,
    require => Package["mysql-server"],
  }

  # set root password
  exec { "set-mysql-password":
    unless => "mysqladmin -uroot -p${mysql_root_pw} status",
    path => ["/bin", "/usr/bin"],
    command => "mysqladmin -uroot password ${mysql_root_pw}",
    require => Service["mysql"],
  }
}