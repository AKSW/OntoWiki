#!/bin/bash

BUILDPATH=/tmp/ontowiki
VIRTTMP=/tmp/virtuoso
STICKERBEGIN=ow_vad_sticker_template.xml
STICKER=ow_vad_sticker.xml
RESULTFILE=ontowiki_fs.vad
ISQLFILE=DB.PACK.VAD.isql
VIRTUOSO_T="/opt/vos-6.1.2/bin/virtuoso-t -c vad.ini -f"

echo "deleting "$BUILDPATH
rm -r $BUILDPATH
echo "deleting "$VIRTTMP
rm -r $VIRTTMP



echo "checking out ontowiki"
hg clone https://ontowiki.googlecode.com/hg/ $BUILDPATH
CURRENT=$PWD
cd $BUILDPATH
make zend
cd $CURRENT

echo "setting options in "$BUILDPATH/config.ini":"

echo "[private]
store.backend =  virtuoso
store.virtuoso.dsn = Local Virtuoso 
store.virtuoso.username    = dba
store.virtuoso.password    = dba
languages.locale = \"en\"
debug = on
cache.query.enable = 0
" > $BUILDPATH/config.ini

echo "*****************verify:"
cat $BUILDPATH/config.ini
echo "*****************"

mkdir -v $BUILDPATH/logs
chmod 777 $BUILDPATH/logs
mkdir -v $BUILDPATH/cache
chmod 777 $BUILDPATH/cache


echo "vad sticker is assembled from "$STICKERBEGIN
cat $STICKERBEGIN >$STICKER
find $BUILDPATH -name ".hg" | xargs rm -r
for onefile in `find $BUILDPATH -type f | grep -v ' '`
do 
echo "<file type=\"http\" overwrite=\"yes\" source=\"http\" target_uri=\""$onefile"\" makepath=\"yes\" />" >> $STICKER
done
echo "</resources><registry /></sticker>" >> $STICKER
echo "sticker created at "$STICKER

echo "copying ontowiki where virtuoso expects it"

mkdir $VIRTTMP
mkdir $VIRTTMP/vad
mkdir $VIRTTMP/vad/vsp
mkdir $VIRTTMP/vad/vsp/tmp
cp -r $BUILDPATH $VIRTTMP/vad/vsp/tmp
cp vad.ini $VIRTTMP
cd $VIRTTMP

#Error 42VAD: [Virtuoso Driver][Virtuoso Server]Inexistent file resource (./vad/vsp//tmp/ontowiki/favicon.png)


echo "DB.DBA.VAD_PACK ('"$PWD/$STICKER"'  , ''  ,  '"$PWD"/"$RESULTFILE"'  );
shutdown;  " > $ISQLFILE

$VIRTUOSO_T
cd $CURRENT
