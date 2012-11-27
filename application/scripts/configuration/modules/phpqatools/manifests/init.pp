class phpqatools {

  exec { "pear-install-phpqatools":
    command => "/usr/bin/pear install pear.phpqatools.org/phpqatools",
    creates => "/usr/bin/phpcb",
    require => Exec["pear-install-phpunit"]
  }
}