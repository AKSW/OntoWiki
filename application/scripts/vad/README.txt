needs to be relative to virtuoso dir
so if your virtuoso is in /opt/virtuoso.opensource do:

svn checkout http://ontowiki.googlecode.com/svn/trunk/ontowiki/scripts/vad /opt/virtuoso.opensource/vad

if you didn't do that, best to delete and start over

do:
cd /opt/virtuoso.opensource/vad
./make_vad.sh
./../bin/isql 1111 dba dba 'DB.PACK.VAD.isql'"
./make_clean.sh

the vad will be in 
/opt/virtuoso.opensource/vad/ontowiki_fs.vad
