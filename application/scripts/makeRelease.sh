#!/bin/bash
# @(#) Creates an OntoWiki Release ZIP File
# @(#) $Id: makeRelease.sh 3887 2009-08-01 23:56:27Z sebastian.dietzold $

parameter="$1"
if [ "$parameter" == "" ]
then
        echo "No Version Parameter!"
        exit 1;
fi

releaseDirBase="ontowiki-$parameter"
releaseZIP="./$releaseDirBase.zip"
releaseTemp="/tmp/$releaseDirBase.svn"
releaseDir="/tmp/$releaseDirBase"

releaseSVN="http://ontowiki.googlecode.com/svn/branches/$releaseDirBase"

if [ -e "$releaseDir" ]
then
  echo "There exists a Directory $releaseDir"
  echo "Delete by hand and start again ..."
  exit 1
fi

if [ ! -e "$releaseTemp" ]
then
  echo "Checkout $releaseSVN to $releaseTemp"
  svn co -q $releaseSVN $releaseTemp || exit
else
  echo "Try to update $releaseTemp (or exit on failure)"
  svn update -q $releaseTemp || exit
fi

echo "Create the ZIP directory $releaseDir" 
cp -R $releaseTemp $releaseDir || exit

echo "Delete unwanted files and directories in $releaseTemp" 
rm -rf `find $releaseDir -name '.svn'`
#cd $releaseDir/extensions/components && rm -rf calendar containermanager foafedit plugins querybuilding repositoryservices tagging filter graphicalquerybuilder querybuilder repository syncml
#cd $releaseDir/extensions/modules && rm -rf containermanager exploretags filter keyboard tagcloud
#cd $releaseDir/extensions/plugins && rm -rf dllearner breadcrumbs sortproperties
#cd $releaseDir/extensions/themes && rm -rf flatcarbon
#cd $releaseDir/extensions/wrapper && rm -rf discogs iClient.php lastfm musicbrainz MusicWrapper.php

echo "Create and export additional files and directories in $releaseTemp" 
#svn export -q $releaseSVNBase/INSTALL $releaseTemp/INSTALL
#svn export -q $releaseSVNBase/CHANGELOG $releaseTemp/CHANGELOG
#svn export -q $releaseSVNBase/LICENSE $releaseTemp/LICENSE
mkdir $releaseDir/cache $releaseDir/uploads
chmod 777 $releaseDir/cache $releaseDir/logs $releaseDir/uploads

echo "Create the ZIP ~/$releaseDirBase.zip"
cd $releaseDir/.. && zip -q -9 -r ~/$releaseDirBase.zip $releaseDirBase

