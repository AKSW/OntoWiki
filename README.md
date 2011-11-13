# OntoWiki

is a tool providing support for agile, distributed knowledge engineering scenarios.
OntoWiki facilitates the visual presentation of a knowledge base as an information map, with different views on instance data.
It enables intuitive authoring of semantic content.
It fosters social collaboration aspects by keeping track of changes, allowing to comment and discuss every single part of a knowledge base.

Other remarkable features are:

* OntoWiki is a Linked Data Server for you data as well as a Linked Data client to fetch additional data from the web
* OntoWiki is a Semantic Pingback Client in order to receive and send back-linking request as known from the blogosphere.
* OntoWiki is backend independent, which means you can save your data on a MySQL database as well as on a Virtuoso Triple Store.
* OntoWiki is easily extendible by you, since it features a sophisticated Extension System.

OntoWiki is licensed under the [GNU General Public License Version 2, June 1991](http://www.gnu.org/licenses/gpl-2.0.txt) (license document is in the application subfolder).

## Installation for Users

### via github repository

* clone or [download](https://github.com/AKSW/OntoWiki/zipball/develop) the repository into your web folder (e.g. `/var/www/ontowiki`)
* enable Apaches rewrite  module (e.g. `a2enmod rewrite`)
* run `make deploy` to download Zend, Erfurt and RDFauthor as well as to create log and cache dir
* copy `config.ini-dist` to `config.ini` and modify it according to your store
* open your browser, go to your ontowiki URL, login as `Admin` without pass and change the password

### via Debian package

* install the LOD2 repository by downloading and adding the [lod2repository
  package](http://stack.lod2.eu/lod2repository_current_all.deb)
* update you package database (`sudo apt-get update`)
* install `ontowiki-mysql` or `ontowiki-virtuoso` (`sudo apt-get ontowiki-virtuoso`)
* open your browser, go to [your ontowiki URL](http://localhost/ontowiki/), login as `Admin` without pass and change the password

## Installation for Developer

* optional: fork the repository
* clone the repository into your web folder (e.g. `/var/www/ontowiki`)
* enable Apaches rewrite  module (e.g. `a2enmod rewrite`)
* run `make install` to download Zend, init git submodules as well as to create log and cache dir
* copy `config.ini-dist` to `config.ini` and modify it according to your store
* open your browser, go to your ontowiki URL, login as `Admin` without pass and change the password
* make sure you create your own feature branch
