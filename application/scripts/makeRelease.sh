#!/bin/bash
# @(#) Creates an OntoWiki Release ZIP File

parameter="$1"
if [ "$parameter" == "" ]
then
        echo "No Version Parameter (e.g. 0.9.5)!"
        exit 1;
fi

releaseDirBase="ontowiki-$parameter"
releaseZIP="./$releaseDirBase.zip"
releaseTemp="/tmp/$releaseDirBase.hg"
releaseDir="/tmp/$releaseDirBase"

OWHG="https://ontowiki.googlecode.com/hg/"

if [ -e "$releaseDir" ]
then
  echo "There exists a Directory $releaseDir"
  echo "Delete by hand and start again ..."
  exit 1
fi

if [ ! -e "$releaseTemp" ]
then
  echo "Checkout $OWHG to $releaseTemp"
  hg clone $OWHG $releaseTemp || exit
else
  echo "Try to update $releaseTemp (or exit on failure)"
  hg pull $releaseTemp || exit
fi

cd $releaseTemp
echo "update to OntoWiki-$parameter"
hg update OntoWiki-$parameter || exit

echo "Create the ZIP directory $releaseDir" 
cp -R $releaseTemp/ontowiki/src $releaseDir || exit
cp -R $releaseTemp/erfurt/src/Erfurt $releaseDir/libraries
cp -R $releaseTemp/RDFauthor/ $releaseDir/libraries

echo "Delete unwanted files and directories in $releaseTemp" 
#rm -rf `find $releaseDir -name '.svn'`
cd $releaseDir/extensions/components && rm -rf calendar graphicalquerybuilder querybuilder repositoryservices artistedit dllearner foafedit querybuilding containermanager easyinference freebedit cacheconfiguration dashboard plugins repository skos
cd $releaseDir//extensions/modules && rm -rf containermanager dllearner easyinference keyboard rating skosrelations tabs tagcloud
cd $releaseDir/extensions/plugins && rm -rf breadcrumbs easyinference isressourceeditingallowed sendmail
#cd $releaseDir/extensions/themes && rm -rf flatcarbon
#cd $releaseDir/extensions/wrapper && rm -rf discogs iClient.php lastfm musicbrainz MusicWrapper.php

echo "Create and copy additional files and directories in $releaseTemp" 
cp -R $releaseTemp/ontowiki/CHANGELOG $releaseDir || exit
cp -R $releaseTemp/ontowiki/INSTALL* $releaseDir || exit
cp -R $releaseTemp/ontowiki/LICENSE $releaseDir || exit
cp -R $releaseTemp/ontowiki/TODO $releaseDir || exit
mkdir $releaseDir/cache $releaseDir/logs $releaseDir/uploads 
chmod 777 $releaseDir/cache $releaseDir/logs $releaseDir/uploads
mv $releaseDir/htaccess-dist $releaseDir/.htaccess

echo "Download and unpack a Zend Framework"
cd $releaseDir/libraries
wget -q http://framework.zend.com/releases/ZendFramework-1.9.4/ZendFramework-1.9.4-minimal.tar.gz
tar xzf ZendFramework-1.9.4-minimal.tar.gz
mv ZendFramework-1.9.4-minimal/library/Zend .
rm ZendFramework-1.9.4-minimal.tar.gz
rm -rf ZendFramework-1.9.4-minimal

echo "Create the ZIP ~/$releaseDirBase.zip"
cd $releaseDir/.. && zip -q -9 -r ~/$releaseDirBase.zip $releaseDirBase

echo "Create the ZIP ~/$releaseDirBase-without-Zend.zip"
rm -rf $releaseDir/libraries/Zend/ && cd $releaseDir/.. && zip -q -9 -r ~/$releaseDirBase-without-Zend.zip $releaseDirBase

echo "Delete the release temp and release dir"
rm -rf $releaseDir
rm -rf $releaseTemp

