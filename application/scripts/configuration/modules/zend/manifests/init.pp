class zend {
    $version = $ubuntu::zend_version
    $current = '/usr/local/zend/share/ZendFramework/current'
    $zend_path = '/usr/local/zend/share/ZendFramework'

    file {['/usr/local/zend', '/usr/local/zend/share', '/usr/local/zend/share/ZendFramework']:
      ensure => "directory"
    }
    
    exec { "download-zend":
        unless => "ls $zend_path/ZendFramework-$version.zip",
        path => ["/bin", "/usr/bin"],
        cwd => $zend_path,
        command => "/usr/bin/wget http://framework.zend.com/releases/ZendFramework-$version/ZendFramework-$version.zip",
        creates => "$zend_path/ZendFramework-$version.zip",
        require => [File[$zend_path]]
    }

    exec { "unzip-zend":
        cwd => $zend_path,
        creates => "$zend_path/ZendFramework-$version",
        command => "/usr/bin/unzip -u ZendFramework-$version.zip",
        require => [Exec["download-zend"]]
    }

    exec { "symlink-zend":
        cwd => $zend_path,
        command => "/bin/ln -sfn $zend_path/ZendFramework-$version $current",
        require => [Exec["unzip-zend"]]
    }
}
