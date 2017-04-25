# OntoWiki


[![Travis CI Build Status](https://travis-ci.org/AKSW/OntoWiki.svg)](https://travis-ci.org/AKSW/OntoWiki/) [![Latest Stable Version](https://poser.pugx.org/aksw/ontowiki/v/stable)](https://packagist.org/packages/aksw/ontowiki) [![Total Downloads](https://poser.pugx.org/aksw/ontowiki/downloads)](https://packagist.org/packages/aksw/ontowiki) [![License](https://poser.pugx.org/aksw/ontowiki/license)](https://packagist.org/packages/aksw/ontowiki) [![composer.lock](https://poser.pugx.org/aksw/ontowiki/composerlock)](https://packagist.org/packages/aksw/ontowiki)


[![OntoWiki User Documentation](https://cdn.rawgit.com/AKSW/OntoWiki/develop/application/logo/user_doc.svg)](https://docs.ontowiki.net) [![OntoWiki API Documentation](https://cdn.rawgit.com/AKSW/OntoWiki/develop/application/logo/api_doc.svg)](http://api.ontowiki.net/)


![](https://raw.github.com/wiki/AKSW/OntoWiki/images/owHeader.png)

## Introduction

is a tool providing support for agile, distributed knowledge engineering scenarios.
OntoWiki facilitates the visual presentation of a knowledge base as an information map, with different views on instance data.
It enables intuitive authoring of semantic content.
It fosters social collaboration aspects by keeping track of changes, allowing to comment and discuss every single part of a knowledge base.

Other remarkable features are:

* OntoWiki is a Linked Data Server for you data as well as a Linked Data client to fetch additional data from the web
* OntoWiki is a Semantic Pingback Client in order to receive and send back-linking request as known from the blogosphere.
* OntoWiki is backend independent, which means you can save your data on a MySQL database as well as on a Virtuoso Triple Store.
* OntoWiki is easily extendible by you, since it features a sophisticated Extension System.

## Installation/Update

If you are updating OntoWiki, please don't forget to run `make install`.
If `make install` fails, you might also have to run `make getcomposer` once before run `make deploy` again.

For further installation instructions please have a look at our [wiki](https://docs.ontowiki.net/) (might be outdated in some parts).

## Screenshot / Webinar
Below is a screenshot showing OntoWiki in editing mode.

For a longer visual presentation you can watch our [webinar@youtube](http://www.youtube.com/watch?v=vP1UDKeZsQk)
(thanks to Phil and the Semantic Web company).

![Screenshot](http://lh4.ggpht.com/-kXpKMqBBCIU/Tpx45SUaItI/AAAAAAAAA9w/aPYaNQjcpvo/s800/ontowiki.png)

## Documentation

The Documentation is hosted at https://docs.ontowiki.net/ . If you find errors or think we should add something to it
you are free to fork https://github.com/AKSW/docs.ontowiki.net and send us a pull requst with your changes.

## License

OntoWiki is licensed under the [GNU General Public License Version 2, June 1991](http://www.gnu.org/licenses/gpl-2.0.txt) (license document is in the application subfolder).
