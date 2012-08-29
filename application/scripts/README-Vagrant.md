Getting Started
---------------

1. Install VirtualBox [1] + Vagrant [2]
2. Install vbguest plugin for Vagrant: `vagrant gem install vagrant-vbguest`
3. Add the following to your `/etc/hosts` file

    192.168.33.10 ontowiki.local
    192.168.33.10 phpmyadmin.ontowiki.local

4. Run `make vagrant` (only the first time) afterwards use `vagrant up`

- [1] https://www.virtualbox.org
- [2] http://vagrantup.com

5. Just type `http://192.168.33.10` into your browser

