#!/bin/bash

echo "make sure you are in the correct path, that is the vad dir!!!"
echo $PWD

cd ..
echo "deleting "$PWD"/tmp"
rm -rI tmp
echo "deleting "$PWD"vad.ini"
rm -rI vad.ini
echo "deleting "$PWD"/vad/ontowiki"
rm -rI vad/ontowiki
echo "deleting "$PWD"/vad/vsp"
rm -rI vad/vsp
echo "deleting "$PWD"/vad/ow_vad_sticker.xml"
rm -rI vad/ow_vad_sticker.xml


