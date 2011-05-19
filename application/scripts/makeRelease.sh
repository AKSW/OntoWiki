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
releaseDir="/tmp/$releaseDirBase"

OWHG="https://ontowiki.googlecode.com/hg/"

if [ -e "$releaseDir" ]
then
  echo "There exists a Directory $releaseDir"
  echo "Delete by hand and start again ..."
  exit 1
fi

if [ ! -e "$releaseDir" ]
then
  echo "Checkout $OWHG to $releaseDir"
  hg --config web.cacerts= clone $OWHG $releaseDir || exit
else
  echo "Try to update $releaseDir (or exit on failure)"
  hg --config web.cacerts= pull $releaseDir || exit
fi

cd $releaseDir
echo "update to OntoWiki-$parameter"
hg --config web.cacerts= update OntoWiki-$parameter || exit
make update

echo "Delete unwanted files and directories in $releaseDir"
#rm -rf `find $releaseDir -name '.svn'`
rm -rf $releaseDir/.hg
rm -rf $releaseDir/extensions.ext

echo "Create and copy additional files and directories in $releaseDir"
mkdir $releaseDir/cache $releaseDir/logs $releaseDir/uploads
chmod 777 $releaseDir/cache $releaseDir/logs $releaseDir/uploads
chmod 777 extensions

echo "Download and unpack a Zend Framework"
cd $releaseDir && make zend

echo "Create the ZIP ~/$releaseDirBase.zip"
cd $releaseDir/.. && zip -q -9 -r ~/$releaseDirBase.zip $releaseDirBase

echo "Create the tar.gz ~/$releaseDirBase.tar.gz"
cd $releaseDir/.. && tar czf ~/$releaseDirBase.tar.gz $releaseDirBase

echo "Create the 7zip ~/$releaseDirBase.7z"
cd $releaseDir/.. && 7zr a -t7z -m0=lzma -mx=9 -mfb=64 -md=32m -ms=on ~/$releaseDirBase.7z $releaseDirBase >/dev/null

echo "Create the ZIP ~/$releaseDirBase-without-Zend.zip"
rm -rf $releaseDir/libraries/Zend/ && cd $releaseDir/.. && zip -q -9 -r ~/$releaseDirBase-without-Zend.zip $releaseDirBase

echo "Delete the release dir"
rm -rf $releaseDir

