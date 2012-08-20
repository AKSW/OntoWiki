class jenkins {

    # jenkins
    package { "jenkins": ensure => installed, require => Exec["apt-update"] }

     # install plugins
    exec { "install-jenkins-plugins":
        command => "/usr/bin/jenkins-cli -s http://localhost:8080 install-plugin checkstyle cloverphp dry htmlpublisher jdepend plot pmd violations xunit filesystem_scm",
        creates => "/var/lib/jenkins/plugins/checkstyle.hpi",
        require => Service["jenkins"],
    }

    # copy configs
    file { ["/var/lib/jenkins/jobs/OntoWiki", "/var/lib/jenkins/jobs/Erfurt"]:
        ensure => "directory",
        owner   => "jenkins",
        group   => "jenkins",
        mode    => "0755",
        require => Service["jenkins"]
    }
    file { "/var/lib/jenkins/jobs/OntoWiki/config.xml":
        ensure => present,
        replace => true,
        owner   => "jenkins",
        group   => "jenkins",
        mode    => "0644",
        source => "puppet:///modules/jenkins/ontowiki.xml",
        require => [Service["jenkins"], File["/var/lib/jenkins/jobs/OntoWiki"]]
    }
    file { "/var/lib/jenkins/jobs/Erfurt/config.xml":
        ensure => present,
        replace => true,
        owner   => "jenkins",
        group   => "jenkins",
        mode    => "0644",
        source => "puppet:///modules/jenkins/erfurt.xml",
        require => [Service["jenkins"], File["/var/lib/jenkins/jobs/Erfurt"]]
    }

    exec { "jenkins-safe-restart":
        command => "/usr/bin/jenkins-cli -s http://localhost:8080 safe-restart",
        require => [Exec["install-jenkins-plugins"], File["/var/lib/jenkins/jobs/OntoWiki/config.xml"], File["/var/lib/jenkins/jobs/Erfurt/config.xml"], Exec["install-jenkins-plugins"]],
    }

    # jenkins service
    service { "jenkins":
        enable => true,
        ensure => running,
        hasrestart => true,
        require => Package["jenkins"],
    }
}
