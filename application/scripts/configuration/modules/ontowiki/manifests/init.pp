class ontowiki {

  $mysql_root_pw = $ubuntu::mysql_root_pw
  $mysql_user = $ubuntu::mysql_user
  $mysql_pw = $ubuntu::mysql_pw
  
  exec { "create-mysql-ontowiki-db":
        unless => "/usr/bin/mysql -uroot -p${mysql_root_pw} ontowiki",
        command => "/usr/bin/mysql -uroot -p${mysql_root_pw} -e \"create database ontowiki;\"",
        require => [Service["mysql"], Exec["set-mysql-password"]]
  }
  
  exec { "create-mysql-ow-test-db":
        unless => "/usr/bin/mysql -uroot -p${mysql_root_pw} ow_TEST",
        command => "/usr/bin/mysql -uroot -p${mysql_root_pw} -e \"create database ow_TEST;\"",
        require => [Service["mysql"], Exec["set-mysql-password"]]
  }
  
  exec { "create-mysql-erfurt-test-db":
        unless => "/usr/bin/mysql -uroot -p${mysql_root_pw} erfurt_TEST",
        command => "/usr/bin/mysql -uroot -p${mysql_root_pw} -e \"create database erfurt_TEST;\"",
        require => [Service["mysql"], Exec["set-mysql-password"]]
  }

  exec { "grant-mysql-ontowiki-db-to-php-user":
        unless => "/usr/bin/mysql -u${$mysql_user} -p${mysql_pw} ontowiki",
        command => "/usr/bin/mysql -uroot -p${mysql_root_pw} -e \"grant all on ontowiki.* to ${mysql_user}@'localhost' identified by '${mysql_pw}';\"",
        require => [Service["mysql"], Exec["create-mysql-ontowiki-db"]]
  }
  
  exec { "grant-mysql-ow-test-db-to-php-user":
        unless => "/usr/bin/mysql -u${$mysql_user} -p${mysql_pw} ow_TEST",
        command => "/usr/bin/mysql -uroot -p${mysql_root_pw} -e \"grant all on ow_TEST.* to ${mysql_user}@'localhost' identified by '${mysql_pw}';\"",
        require => [Service["mysql"], Exec["create-mysql-ow-test-db"]]
  }
  
  exec { "grant-mysql-erfurt-test-db-to-php-user":
        unless => "/usr/bin/mysql -u${$mysql_user} -p${mysql_pw} erfurt_TEST",
        command => "/usr/bin/mysql -uroot -p${mysql_root_pw} -e \"grant all on erfurt_TEST.* to ${mysql_user}@'localhost' identified by '${mysql_pw}';\"",
        require => [Service["mysql"], Exec["create-mysql-erfurt-test-db"]]
  }
}
