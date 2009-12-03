#!/bin/bash

echo "copy ini file"
cp -fv files/config.ini ontowiki/config.ini
mkdir -v ontowiki/logs
chmod 777 ontowiki/logs
mkdir -v ontowiki/cache
chmod 777 ontowiki/cache
cp -v ontowiki/index.php tmp.php
echo "setting $rewriteEngineOn     = false; to true in index.php "
cat tmp.php | sed 's/$rewriteEngineOn = false;/$rewriteEngineOn = true;/' > ontowiki/index.php
echo "CHECK SUCCESS MANUALLY:"
echo "diff tmp.php ontowiki/index.php "
diff tmp.php ontowiki/index.php
rm -v tmp.php


./make_sticker_helper.sh

mkdir -v ../tmp
cp -v files/vad.ini ../vad.ini
mkdir -v vsp
cp -r ontowiki vsp/ontowiki

cd ..
./bin/virtuoso-t  -c vad.ini &

echo "*********************************************"
echo "the virtuoso server was started in the background"
echo "execute the following command manually:"
echo "./../bin/isql 1111 dba dba 'DB.PACK.VAD.isql'"
echo "after that start make_clean.sh"
