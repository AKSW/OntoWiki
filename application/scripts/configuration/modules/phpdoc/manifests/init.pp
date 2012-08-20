class phpdoc {

  exec { "pear-install-phpdoc":
    command => "/usr/bin/pear install pear.phpdoc.org/phpDocumentor-alpha",
    creates => "/usr/bin/phpdoc",
    require => Exec["pear-install-phpunit"]
  }
}