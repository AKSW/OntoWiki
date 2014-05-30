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
        command => "/usr/bin/wget https://packages.zendframework.com/releases/ZendFramework-$version/ZendFramework-$version-minimal.zip",
        creates => "$zend_path/ZendFramework-$version-minimal.zip",
        require => [File[$zend_path]]
    }

    exec { "unzip-zend":
        cwd => $zend_path,
        creates => "$zend_path/ZendFramework-$version-minimal",
        command => "/usr/bin/unzip -u ZendFramework-$version-minimal.zip",
        require => [Exec["download-zend"]]
    }

    exec { "symlink-zend":
        cwd => $zend_path,
        command => "/bin/ln -sfn $zend_path/ZendFramework-$version-minimal $current",
        require => [Exec["unzip-zend"]]
    }
}
