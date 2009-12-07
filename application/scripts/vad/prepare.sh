#!/bin/bash
echo "checking out ontowiki"
hg clone https://ontowiki.googlecode.com/hg/ /tmp/ontowiki.src
mkdir -v /tmp/ontowiki
mv -v /tmp/ontowiki.src/ontowiki/src/* /tmp/ontowiki
mv -v /tmp/ontowiki.src/erfurt/src/Erfurt /tmp/ontowiki/libraries/Erfurt
mv -v /tmp/ontowiki.src/RDFauthor /tmp/ontowiki/libraries/RDFauthor

cd /tmp/ontowiki/libraries/
wget http://framework.zend.com/releases/ZendFramework-1.9.4/ZendFramework-1.9.4-minimal.tar.gz
tar xvzf ZendFramework-1.9.4-minimal.tar.gz
ln -s ZendFramework-1.9.4-minimal/library/Zend .
rm -v ZendFramework-1.9.4-minimal.tar.gz

cp -rv /tmp/ontowiki /tmp/ontowikiprepared
echo "move ow files manually to relative path vad/ontowiki"
echo "a copy has been saved at /tmp/ontowikiprepared"
echo "mv /tmp/ontowiki ontowiki"

exit

