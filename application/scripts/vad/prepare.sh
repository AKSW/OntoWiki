#!/bin/bash


VIRTTMP=/tmp/virtuoso
STICKERBEGIN=ow_vad_sticker_template.xml
STICKER=ow_vad_sticker.xml
RESULTFILE=ontowiki_fs.vad
ISQLFILE=DB.PACK.VAD.isql
VIRTUOSO_T="/opt/vos-6.1.2/bin/virtuoso-t -c vad.ini -f"
CURRENT=$PWD
#do not change this below:
BUILDPATH=/tmp/ontowiki

echo "DB.DBA.VAD_PACK ('"$PWD/$STICKER"'  , ''  ,  '"$PWD"/"$RESULTFILE"'  );
shutdown;  " > $ISQLFILE

echo "deleting "$BUILDPATH
rm -r $BUILDPATH
echo "deleting "$VIRTTMP
rm -r $VIRTTMP



echo "checking out ontowiki"
hg clone https://ontowiki.googlecode.com/hg/ $BUILDPATH

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
cd /tmp
for onefile in `find ontowiki -type f | grep -v ' '`
do 
echo "<file type=\"http\" overwrite=\"yes\" source=\"http\" target_uri=\""$onefile"\" makepath=\"yes\" />" >> $CURRENT/$STICKER
done
cd $CURRENT
echo "</resources><registry /></sticker>" >> $STICKER
echo "sticker created at "$STICKER

echo "copying ontowiki where virtuoso expects it"

mkdir $VIRTTMP
mkdir $VIRTTMP/vad
mkdir $VIRTTMP/vad/vsp
cp -r $BUILDPATH $VIRTTMP/vad/vsp
cp vad.ini $VIRTTMP
cd $VIRTTMP

#Error 42VAD: [Virtuoso Driver][Virtuoso Server]Inexistent file resource (./vad/vsp//tmp/ontowiki/favicon.png)




$VIRTUOSO_T
cd $CURRENT
