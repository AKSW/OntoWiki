In order for the VAD scripts to work:
you need to copy (not link) the vad directory to your virtuoso server root dir:
cp -r vad /opt/virtuoso-opensource/vad 
for example

then do
cd /opt/virtuoso-opensource/vad 
./prepare.sh
./make_vad.sh
