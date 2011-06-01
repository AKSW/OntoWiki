#!/bin/bash

hgrcfile=".hg/hgrc"
hookstr="[hooks]"
precommitstr="precommit.phpcs = hg status -n -0 --include='*.php' | xargs --null phpcs --report=summary -n --extensions=php --severity=7 --standard=application\/tests\/CodeSniffer\/Standards\/Ontowiki"

cp -n $hgrcfile $hgrcfile.org
if grep 'hooks' $hgrcfile --quiet; then
    if grep 'precommit.phpcs' $hgrcfile --quiet; then
        ./application/tests/CodeSniffer/remove-hook.sh
    fi
    sed -i s/'^\[hooks\]'/'[hooks]\n'"$precommitstr"/ $hgrcfile
else
    echo "" >> $hgrcfile
    echo $hookstr >> $hgrcfile
    echo $precommitstr >> $hgrcfile
fi
