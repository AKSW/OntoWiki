#!/bin/bash

hgrcfile=".hg/hgrc"
hookstr="[hooks]"
severity=`sed -En 's/^severity = (.)$/\1/p' Makefile`
cspath=`sed -En 's/^cspath = (.*)$/\1/p' Makefile`
filetypes=`sed -En 's/^filetypes = (.*)$/\1/p' Makefile`
precommitstr="precommit.phpcs = hg status -n | grep '\\.$filetypes$' | xargs --no-run-if-empty phpcs --report=summary -n --severity=$severity --standard=$cspath"

cp -n $hgrcfile $hgrcfile.org
if grep 'hooks' $hgrcfile --quiet; then
    if grep 'precommit.phpcs' $hgrcfile --quiet; then
        ./application/tests/CodeSniffer/remove-hook.sh
    fi
    sed -i s!'^\[hooks\]'!'[hooks]\n'"$precommitstr"! $hgrcfile
else
    echo "" >> $hgrcfile
    echo $hookstr >> $hgrcfile
    echo $precommitstr >> $hgrcfile
fi
