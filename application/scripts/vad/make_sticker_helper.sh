#!/bin/bash
#~ echo $FILES
FILES=files/
PRE="<file type=\"http\" overwrite=\"yes\" source=\"http\" target_uri=\""
POST="\" makepath=\"yes\" />"
echo "start collecting path information" 

echo "" > ow_vad_sticker.xml

cat $FILES"sticker_header.txt" >> ow_vad_sticker.xml
cat $FILES"sticker_pre-install.txt" >> ow_vad_sticker.xml
cat $FILES"sticker_post-install.txt" >> ow_vad_sticker.xml
cat $FILES"sticker_pre-uninstall.txt" >> ow_vad_sticker.xml
cat $FILES"sticker_post-uninstall.txt" >> ow_vad_sticker.xml

for onefile in `find ontowiki -type f`
do 
echo $PRE$onefile$POST >> ow_vad_sticker.xml
done

cat $FILES"sticker_ending.txt" >> ow_vad_sticker.xml

echo "sticker created"
