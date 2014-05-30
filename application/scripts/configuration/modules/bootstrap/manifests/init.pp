class bootstrap 
{
    # install some useful tools
    # git 
    package { ["htop", "vim", "unzip", "curl", "zsh", "raptor2-utils", "make"]:
        ensure => latest
    }

    exec { "chsh-to-zsh":
        command => "/usr/bin/chsh -s /usr/bin/zsh vagrant",
        require => Package["zsh"]
    }
    
    file { "/home/vagrant/.config/":
        ensure => directory,
        require => Package["zsh"]
    }
    
    file { "/home/vagrant/.config/zsh/":
        ensure => directory,
        require => Package["zsh"]
    }
    
    file { "/home/vagrant/.config/zsh/environment.zsh":
        ensure => present,
        source => "puppet:///modules/bootstrap/environment.zsh",
        require => Package["zsh"]
    }
    
    file { "/home/vagrant/.config/zsh/options.zsh":
        ensure => present,
        source => "puppet:///modules/bootstrap/options.zsh",
        require => Package["zsh"]
    }
    
    file { "/home/vagrant/.config/zsh/prompt.zsh":
        ensure => present,
        source => "puppet:///modules/bootstrap/prompt.zsh",
        require => Package["zsh"]
    }
    
    file { "/home/vagrant/.config/zsh/aliases.zsh":
        ensure => present,
        source => "puppet:///modules/bootstrap/aliases.zsh",
        require => Package["zsh"]
    }
    
    file { "/home/vagrant/.zshrc":
        ensure => present,
        source => "puppet:///modules/bootstrap/zshrc",
        require => Package["zsh"]
    }
}