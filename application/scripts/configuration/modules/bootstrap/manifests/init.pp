class bootstrap {

    # create puppet group
    # TODO: Is this needed?
    #group { puppet:
    #    ensure => present,
    #}
    
    # update apt
    exec { "apt-update":
        command => "/usr/bin/apt-get update",
    }
    
    # install some useful applications
    package { ["htop", "vim", "unzip", "graphviz", "git", "curl"]:
        ensure => installed,
        require => Exec["apt-update"]
    }
}
